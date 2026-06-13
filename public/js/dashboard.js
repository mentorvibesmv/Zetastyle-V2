const state = {
  mappings: [],
  targetBundle: null,
  isEditing: false,
};

const elements = {
  form: document.getElementById("mappingForm"),
  mappingId: document.getElementById("mappingId"),
  name: document.getElementById("name"),
  image: document.getElementById("image"),
  video: document.getElementById("video"),
  imagePreview: document.getElementById("imagePreview"),
  imageEmpty: document.getElementById("imageEmpty"),
  videoPreview: document.getElementById("videoPreview"),
  videoEmpty: document.getElementById("videoEmpty"),
  submitButton: document.getElementById("submitButton"),
  cancelEditButton: document.getElementById("cancelEditButton"),
  mappingsList: document.getElementById("mappingsList"),
  statusBanner: document.getElementById("statusBanner"),
  rebuildTargetsButton: document.getElementById("rebuildTargetsButton"),
  formTitle: document.getElementById("formTitle"),
};

const setStatus = (message, tone = "info") => {
  elements.statusBanner.className = `status-banner ${tone}`;
  elements.statusBanner.textContent = message;
};

const toAbsoluteUrl = (relativeUrl) => new URL(relativeUrl, window.location.origin).href;

const showPreview = (file, previewElement, emptyElement) => {
  if (!file) {
    previewElement.hidden = true;
    previewElement.removeAttribute("src");
    emptyElement.hidden = false;
    return;
  }

  const objectUrl = URL.createObjectURL(file);
  previewElement.src = objectUrl;
  previewElement.hidden = false;
  emptyElement.hidden = true;
  previewElement.dataset.objectUrl = objectUrl;
};

const clearPreviewObjectUrl = (previewElement) => {
  if (previewElement.dataset.objectUrl) {
    URL.revokeObjectURL(previewElement.dataset.objectUrl);
    delete previewElement.dataset.objectUrl;
  }
};

const resetForm = () => {
  state.isEditing = false;
  elements.form.reset();
  elements.mappingId.value = "";
  elements.formTitle.textContent = "Create a New Mapping";
  elements.submitButton.textContent = "Save Mapping";
  elements.cancelEditButton.hidden = true;
  clearPreviewObjectUrl(elements.imagePreview);
  clearPreviewObjectUrl(elements.videoPreview);
  elements.imagePreview.hidden = true;
  elements.videoPreview.hidden = true;
  elements.imageEmpty.hidden = false;
  elements.videoEmpty.hidden = false;
};

const formatDate = (value) => new Date(value).toLocaleString();

const renderMappings = () => {
  if (state.mappings.length === 0) {
    elements.mappingsList.innerHTML = `
      <div class="mapping-item">
        <div class="mapping-meta">
          <h3>No mappings yet</h3>
          <p>Add your first image-video pair to generate an AR target library.</p>
        </div>
      </div>
    `;
    return;
  }

  elements.mappingsList.innerHTML = state.mappings
    .map(
      (mapping) => `
        <article class="mapping-item">
          <div class="mapping-meta">
            <span class="mapping-badge">Target #${mapping.targetIndex + 1}</span>
            <h3>${mapping.name}</h3>
            <p>Updated: ${formatDate(mapping.updatedAt)}</p>
            <p>Image: ${mapping.imageFilename}</p>
            <p>Video: ${mapping.videoFilename}</p>
            <p>Target bundle: ${mapping.targetFileRef ? "Ready" : "Needs rebuild"}</p>
            <div class="mapping-actions">
              <button class="button secondary" type="button" data-action="edit" data-id="${mapping.id}">Edit</button>
              <button class="button ghost" type="button" data-action="delete" data-id="${mapping.id}">Delete</button>
            </div>
          </div>

          <div class="mapping-media">
            <img src="${mapping.imageUrl}" alt="${mapping.name} target image preview" />
            <video src="${mapping.videoUrl}" muted controls playsinline preload="metadata"></video>
          </div>
        </article>
      `,
    )
    .join("");
};

const fetchMappings = async () => {
  const response = await fetch("/api/mappings");
  const payload = await response.json();

  if (!response.ok) {
    throw new Error(payload.message || "Unable to load mappings.");
  }

  state.mappings = payload.mappings;
  state.targetBundle = payload.targetBundle;

  renderMappings();

  if (state.mappings.length === 0) {
    setStatus("Upload at least one mapping, then compile the `.mind` target file before scanning.", "info");
    return;
  }

  if (state.targetBundle?.url) {
    setStatus(
      `Target file ready for ${state.targetBundle.mappingCount} image target${state.targetBundle.mappingCount > 1 ? "s" : ""}.`,
      "success",
    );
    return;
  }

  setStatus("Mappings changed. Rebuild the target file so the AR viewer can scan the latest images.", "warning");
};

const buildTargetBundle = async () => {
  if (state.mappings.length === 0) {
    setStatus("Create at least one mapping before rebuilding the target file.", "warning");
    return;
  }

  if (!window.MINDAR?.Compiler) {
    throw new Error("MindAR compiler failed to load in the browser.");
  }

  elements.rebuildTargetsButton.disabled = true;
  setStatus("Compiling image targets in your browser. This can take a moment on mobile devices...", "info");

  try {
    const images = await Promise.all(
      state.mappings.map((mapping) =>
        new Promise((resolve, reject) => {
          const image = new Image();
          image.crossOrigin = "anonymous";
          image.onload = () => resolve(image);
          image.onerror = () => reject(new Error(`Unable to load target image: ${mapping.name}`));
          image.src = toAbsoluteUrl(mapping.imageUrl);
        }),
      ),
    );

    const compiler = new window.MINDAR.Compiler();
    await compiler.compileImageTargets(images, (progress) => {
      const percent = Math.round(progress * 100);
      setStatus(`Compiling target file... ${percent}%`, "info");
    });

    const exportedBuffer = await compiler.exportData();
    const blob = new Blob([exportedBuffer], { type: "application/octet-stream" });
    const formData = new FormData();

    formData.append("targetFile", blob, "current.mind");
    formData.append(
      "mappingIds",
      JSON.stringify(state.mappings.map((mapping) => mapping.id)),
    );

    const response = await fetch("/api/targets", {
      method: "POST",
      body: formData,
    });
    const payload = await response.json();

    if (!response.ok) {
      throw new Error(payload.message || "Failed to save compiled target file.");
    }

    state.mappings = payload.mappings;
    state.targetBundle = payload.targetBundle;
    renderMappings();
    setStatus("Target file rebuilt successfully. The viewer is ready to scan your uploaded images.", "success");
  } finally {
    elements.rebuildTargetsButton.disabled = false;
  }
};

const fillFormForEdit = (mapping) => {
  state.isEditing = true;
  elements.mappingId.value = mapping.id;
  elements.name.value = mapping.name;
  elements.formTitle.textContent = `Edit Mapping: ${mapping.name}`;
  elements.submitButton.textContent = "Update Mapping";
  elements.cancelEditButton.hidden = false;
  elements.imagePreview.src = mapping.imageUrl;
  elements.imagePreview.hidden = false;
  elements.imageEmpty.hidden = true;
  elements.videoPreview.src = mapping.videoUrl;
  elements.videoPreview.hidden = false;
  elements.videoEmpty.hidden = true;
  window.scrollTo({ top: 0, behavior: "smooth" });
};

const saveMapping = async (event) => {
  event.preventDefault();

  const mappingId = elements.mappingId.value.trim();
  const formData = new FormData();
  const imageFile = elements.image.files[0];
  const videoFile = elements.video.files[0];

  if (!mappingId && (!imageFile || !videoFile)) {
    setStatus("Select both an image and an MP4 video before saving.", "error");
    return;
  }

  if (imageFile) {
    formData.append("image", imageFile);
  }

  if (videoFile) {
    formData.append("video", videoFile);
  }

  formData.append("name", elements.name.value.trim());

  elements.submitButton.disabled = true;

  try {
    const method = mappingId ? "PUT" : "POST";
    const endpoint = mappingId ? `/api/mappings/${mappingId}` : "/api/mappings";
    const response = await fetch(endpoint, {
      method,
      body: formData,
    });
    const payload = await response.json();

    if (!response.ok) {
      throw new Error(payload.message || "Unable to save mapping.");
    }

    resetForm();
    await fetchMappings();
    await buildTargetBundle();
  } catch (error) {
    setStatus(error.message, "error");
  } finally {
    elements.submitButton.disabled = false;
  }
};

const deleteMapping = async (mappingId) => {
  const confirmed = window.confirm("Delete this image-video mapping?");
  if (!confirmed) {
    return;
  }

  try {
    const response = await fetch(`/api/mappings/${mappingId}`, {
      method: "DELETE",
    });
    const payload = await response.json();

    if (!response.ok) {
      throw new Error(payload.message || "Unable to delete mapping.");
    }

    resetForm();
    await fetchMappings();

    if (state.mappings.length > 0) {
      await buildTargetBundle();
    } else {
      setStatus("All mappings removed. Add new items to rebuild the target file.", "info");
    }
  } catch (error) {
    setStatus(error.message, "error");
  }
};

elements.image.addEventListener("change", () => {
  clearPreviewObjectUrl(elements.imagePreview);
  showPreview(elements.image.files[0], elements.imagePreview, elements.imageEmpty);
});

elements.video.addEventListener("change", () => {
  clearPreviewObjectUrl(elements.videoPreview);
  showPreview(elements.video.files[0], elements.videoPreview, elements.videoEmpty);
});

elements.form.addEventListener("submit", saveMapping);
elements.cancelEditButton.addEventListener("click", resetForm);

elements.rebuildTargetsButton.addEventListener("click", async () => {
  try {
    await fetchMappings();
    await buildTargetBundle();
  } catch (error) {
    setStatus(error.message, "error");
  }
});

elements.mappingsList.addEventListener("click", (event) => {
  const trigger = event.target.closest("button[data-action]");
  if (!trigger) {
    return;
  }

  const { action, id } = trigger.dataset;
  const mapping = state.mappings.find((item) => item.id === id);

  if (!mapping) {
    setStatus("That mapping could not be found.", "error");
    return;
  }

  if (action === "edit") {
    fillFormForEdit(mapping);
    return;
  }

  if (action === "delete") {
    deleteMapping(id);
  }
});

window.addEventListener("DOMContentLoaded", async () => {
  try {
    await fetchMappings();
  } catch (error) {
    setStatus(error.message, "error");
  }
});
