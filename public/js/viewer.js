const statusElement = document.getElementById("viewerStatus");
const emptyStateElement = document.getElementById("emptyState");
const arRootElement = document.getElementById("arRoot");

const setStatus = (message, tone = "info") => {
  statusElement.className = `status-banner ${tone}`;
  statusElement.textContent = message;
};

const fetchMappings = async () => {
  const response = await fetch("/api/mappings");
  const payload = await response.json();

  if (!response.ok) {
    throw new Error(payload.message || "Unable to load AR mappings.");
  }

  return payload;
};

const waitForMetadata = (video) =>
  new Promise((resolve) => {
    if (video.readyState >= 1) {
      resolve();
      return;
    }

    video.addEventListener("loadedmetadata", () => resolve(), { once: true });
    video.addEventListener("error", () => resolve(), { once: true });
  });

const createSceneMarkup = async (mappings, targetBundleUrl) => {
  const dimensions = await Promise.all(
    mappings.map(
      (mapping) =>
        new Promise((resolve) => {
          const image = new Image();
          image.onload = () =>
            resolve({
              width: image.naturalWidth || 1,
              height: image.naturalHeight || 1,
            });
          image.onerror = () => resolve({ width: 1, height: 1 });
          image.src = mapping.imageUrl;
        }),
    ),
  );

  const assetsMarkup = mappings
    .map(
      (mapping, index) => `
        <video
          id="video-${index}"
          src="${mapping.videoUrl}"
          preload="auto"
          autoplay
          loop
          muted
          playsinline
          webkit-playsinline
          crossorigin="anonymous"
        ></video>
      `,
    )
    .join("");

  const targetsMarkup = mappings
    .map((mapping, index) => {
      const ratio = dimensions[index].height / dimensions[index].width || 0.5625;
      const height = Math.max(0.45, Math.min(1.6, Number(ratio.toFixed(4))));

      return `
        <a-entity mindar-image-target="targetIndex: ${index}" data-name="${mapping.name}">
          <a-video
            id="overlay-${index}"
            src="#video-${index}"
            position="0 0 0"
            rotation="0 0 0"
            width="1"
            height="${height}"
          ></a-video>
        </a-entity>
      `;
    })
    .join("");

  return `
    <a-scene
      id="arScene"
      mindar-image="imageTargetSrc: ${targetBundleUrl}; autoStart: false; uiScanning: false; uiLoading: false;"
      color-space="sRGB"
      renderer="colorManagement: true, physicallyCorrectLights"
      vr-mode-ui="enabled: false"
      device-orientation-permission-ui="enabled: false"
    >
      <a-assets>${assetsMarkup}</a-assets>
      <a-camera position="0 0 0" look-controls="enabled: false"></a-camera>
      ${targetsMarkup}
    </a-scene>
  `;
};

const wireTargetEvents = async (sceneEl, mappings) => {
  const videoElements = await Promise.all(
    mappings.map(async (_mapping, index) => {
      const video = sceneEl.querySelector(`#video-${index}`);
      await waitForMetadata(video);
      return video;
    }),
  );

  const targetEntities = Array.from(sceneEl.querySelectorAll("[mindar-image-target]"));

  targetEntities.forEach((targetEntity, index) => {
    const video = videoElements[index];
    const label = mappings[index].name;

    targetEntity.addEventListener("targetFound", async () => {
      setStatus(`Target found: ${label}. Video overlay is playing.`, "success");

      try {
        video.currentTime = 0;
        await video.play();
      } catch {
        setStatus("Target found, but your browser blocked autoplay. Tap once, then rescan the image.", "warning");
      }
    });

    targetEntity.addEventListener("targetLost", () => {
      video.pause();
      setStatus("Target lost. Point the camera back at the image to continue playback.", "info");
    });
  });
};

const startMindAr = async (sceneEl) => {
  const system = sceneEl.systems?.["mindar-image-system"];
  if (system?.start) {
    await system.start();
    return;
  }

  await new Promise((resolve) => {
    sceneEl.addEventListener(
      "loaded",
      async () => {
        await sceneEl.systems["mindar-image-system"].start();
        resolve();
      },
      { once: true },
    );
  });
};

const startViewer = async () => {
  try {
    const { mappings, targetBundle } = await fetchMappings();

    if (!mappings.length || !targetBundle?.url) {
      emptyStateElement.hidden = false;
      setStatus("No compiled targets available yet.", "warning");
      return;
    }

    const sceneMarkup = await createSceneMarkup(mappings, targetBundle.url);
    arRootElement.innerHTML = sceneMarkup;
    const sceneEl = document.getElementById("arScene");

    sceneEl.addEventListener("renderstart", () => {
      setStatus("Camera ready. Scan one of your uploaded images to trigger its video.", "info");
    });

    sceneEl.addEventListener("arReady", () => {
      setStatus("AR engine loaded. Looking for image targets...", "info");
    });

    sceneEl.addEventListener("arError", () => {
      setStatus("The AR session failed to start. Check camera permissions and HTTPS/localhost access.", "error");
    });

    await wireTargetEvents(sceneEl, mappings);
    await startMindAr(sceneEl);
  } catch (error) {
    setStatus(error.message || "Unable to open the AR viewer.", "error");
  }
};

window.addEventListener("DOMContentLoaded", startViewer);
