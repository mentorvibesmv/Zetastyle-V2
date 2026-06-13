import cors from "cors";
import { randomUUID } from "crypto";
import express from "express";
import fs from "fs/promises";
import multer from "multer";
import path from "path";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;

const PUBLIC_DIR = path.join(__dirname, "public");
const DATA_DIR = path.join(__dirname, "data");
const IMAGE_DIR = path.join(PUBLIC_DIR, "uploads", "images");
const VIDEO_DIR = path.join(PUBLIC_DIR, "uploads", "videos");
const TARGET_DIR = path.join(PUBLIC_DIR, "uploads", "targets");
const DB_FILE = path.join(DATA_DIR, "mappings.json");
const CURRENT_TARGET_FILE = path.join(TARGET_DIR, "current.mind");

const MAX_IMAGE_SIZE = 10 * 1024 * 1024;
const MAX_VIDEO_SIZE = 80 * 1024 * 1024;
const IMAGE_MIME_TYPES = new Set(["image/jpeg", "image/png"]);
const VIDEO_MIME_TYPES = new Set(["video/mp4"]);

app.use(cors());
app.use(express.json({ limit: "5mb" }));
app.use(express.urlencoded({ extended: true }));
app.use(express.static(PUBLIC_DIR));

const ensureAppDirectories = async () => {
  await fs.mkdir(DATA_DIR, { recursive: true });
  await fs.mkdir(IMAGE_DIR, { recursive: true });
  await fs.mkdir(VIDEO_DIR, { recursive: true });
  await fs.mkdir(TARGET_DIR, { recursive: true });

  try {
    await fs.access(DB_FILE);
  } catch {
    await writeDb({ mappings: [], targetBundle: null });
  }
};

const readDb = async () => {
  const raw = await fs.readFile(DB_FILE, "utf8");
  return JSON.parse(raw);
};

const writeDb = async (data) => {
  await fs.writeFile(DB_FILE, JSON.stringify(data, null, 2), "utf8");
};

const safeUnlink = async (filePath) => {
  try {
    await fs.unlink(filePath);
  } catch (error) {
    if (error.code !== "ENOENT") {
      throw error;
    }
  }
};

const fileUrlFromPath = (absoluteFilePath) => {
  const relativePath = path.relative(PUBLIC_DIR, absoluteFilePath).replaceAll("\\", "/");
  return `/${relativePath}`;
};

const createStorage = (destination) =>
  multer.diskStorage({
    destination,
    filename: (_req, file, cb) => {
      const extension = path.extname(file.originalname).toLowerCase();
      const sanitizedBase = path
        .basename(file.originalname, extension)
        .replace(/[^a-z0-9-_]+/gi, "-")
        .replace(/^-+|-+$/g, "")
        .slice(0, 48) || "asset";

      cb(null, `${Date.now()}-${sanitizedBase}${extension}`);
    },
  });

const uploadMappingAssets = multer({
  storage: multer.diskStorage({
    destination: (_req, file, cb) => {
      if (file.fieldname === "image") {
        cb(null, IMAGE_DIR);
        return;
      }

      cb(null, VIDEO_DIR);
    },
    filename: (_req, file, cb) => {
      const extension = path.extname(file.originalname).toLowerCase();
      const sanitizedBase = path
        .basename(file.originalname, extension)
        .replace(/[^a-z0-9-_]+/gi, "-")
        .replace(/^-+|-+$/g, "")
        .slice(0, 48) || "asset";

      cb(null, `${Date.now()}-${sanitizedBase}${extension}`);
    },
  }),
  limits: {
    fileSize: MAX_VIDEO_SIZE,
    files: 2,
  },
  fileFilter: (_req, file, cb) => {
    if (file.fieldname === "image" && IMAGE_MIME_TYPES.has(file.mimetype)) {
      cb(null, true);
      return;
    }

    if (file.fieldname === "video" && VIDEO_MIME_TYPES.has(file.mimetype)) {
      cb(null, true);
      return;
    }

    cb(new Error("Unsupported file type. Use JPG/PNG for images and MP4 for video."));
  },
});

const uploadTargetBundle = multer({
  storage: createStorage(TARGET_DIR),
  limits: {
    fileSize: 25 * 1024 * 1024,
    files: 1,
  },
  fileFilter: (_req, file, cb) => {
    if (
      file.fieldname === "targetFile" &&
      (file.mimetype === "application/octet-stream" || file.originalname.toLowerCase().endsWith(".mind"))
    ) {
      cb(null, true);
      return;
    }

    cb(new Error("Target file must be a .mind file."));
  },
});

const sortMappings = (mappings) =>
  mappings
    .slice()
    .sort((a, b) => (a.targetIndex ?? Number.MAX_SAFE_INTEGER) - (b.targetIndex ?? Number.MAX_SAFE_INTEGER));

const deleteUploadedFiles = async (files = []) => {
  await Promise.all(
    files
      .filter(Boolean)
      .map((file) => safeUnlink(file.path)),
  );
};

app.get("/api/health", (_req, res) => {
  res.json({ ok: true });
});

app.get("/api/mappings", async (_req, res, next) => {
  try {
    const db = await readDb();
    res.json({
      mappings: sortMappings(db.mappings),
      targetBundle: db.targetBundle,
    });
  } catch (error) {
    next(error);
  }
});

app.post(
  "/api/mappings",
  uploadMappingAssets.fields([
    { name: "image", maxCount: 1 },
    { name: "video", maxCount: 1 },
  ]),
  async (req, res, next) => {
    try {
      const image = req.files?.image?.[0];
      const video = req.files?.video?.[0];

      if (!image || !video) {
        await deleteUploadedFiles([image, video]);
        res.status(400).json({ message: "Both image and video files are required." });
        return;
      }

      if (image.size > MAX_IMAGE_SIZE) {
        await deleteUploadedFiles([image, video]);
        res.status(400).json({ message: "Image must be 10MB or smaller." });
        return;
      }

      const db = await readDb();
      const nextIndex = db.mappings.length;
      const now = new Date().toISOString();

      const mapping = {
        id: randomUUID(),
        name: (req.body.name || path.parse(image.originalname).name || "Untitled target").trim(),
        imageUrl: fileUrlFromPath(image.path),
        imageFilename: image.filename,
        videoUrl: fileUrlFromPath(video.path),
        videoFilename: video.filename,
        targetFileRef: db.targetBundle?.url ?? null,
        targetIndex: nextIndex,
        createdAt: now,
        updatedAt: now,
      };

      db.mappings.push(mapping);
      db.targetBundle = null;

      await writeDb(db);

      res.status(201).json({
        message: "Mapping created. Rebuild the target file before scanning.",
        mapping,
      });
    } catch (error) {
      await deleteUploadedFiles([req.files?.image?.[0], req.files?.video?.[0]]);
      next(error);
    }
  },
);

app.put(
  "/api/mappings/:id",
  uploadMappingAssets.fields([
    { name: "image", maxCount: 1 },
    { name: "video", maxCount: 1 },
  ]),
  async (req, res, next) => {
    try {
      const db = await readDb();
      const mapping = db.mappings.find((item) => item.id === req.params.id);

      if (!mapping) {
        await deleteUploadedFiles([req.files?.image?.[0], req.files?.video?.[0]]);
        res.status(404).json({ message: "Mapping not found." });
        return;
      }

      const newImage = req.files?.image?.[0];
      const newVideo = req.files?.video?.[0];

      if (newImage && newImage.size > MAX_IMAGE_SIZE) {
        await deleteUploadedFiles([newImage, newVideo]);
        res.status(400).json({ message: "Image must be 10MB or smaller." });
        return;
      }

      if (typeof req.body.name === "string" && req.body.name.trim()) {
        mapping.name = req.body.name.trim();
      }

      if (newImage) {
        await safeUnlink(path.join(IMAGE_DIR, mapping.imageFilename));
        mapping.imageUrl = fileUrlFromPath(newImage.path);
        mapping.imageFilename = newImage.filename;
      }

      if (newVideo) {
        await safeUnlink(path.join(VIDEO_DIR, mapping.videoFilename));
        mapping.videoUrl = fileUrlFromPath(newVideo.path);
        mapping.videoFilename = newVideo.filename;
      }

      mapping.updatedAt = new Date().toISOString();
      db.targetBundle = null;

      await writeDb(db);

      res.json({
        message: "Mapping updated. Rebuild the target file before scanning.",
        mapping,
      });
    } catch (error) {
      await deleteUploadedFiles([req.files?.image?.[0], req.files?.video?.[0]]);
      next(error);
    }
  },
);

app.delete("/api/mappings/:id", async (req, res, next) => {
  try {
    const db = await readDb();
    const mappingIndex = db.mappings.findIndex((item) => item.id === req.params.id);

    if (mappingIndex === -1) {
      res.status(404).json({ message: "Mapping not found." });
      return;
    }

    const [mapping] = db.mappings.splice(mappingIndex, 1);

    await safeUnlink(path.join(IMAGE_DIR, mapping.imageFilename));
    await safeUnlink(path.join(VIDEO_DIR, mapping.videoFilename));

    db.mappings = db.mappings.map((item, index) => ({
      ...item,
      targetIndex: index,
    }));
    db.targetBundle = null;

    await writeDb(db);

    res.json({ message: "Mapping deleted. Rebuild the target file before scanning." });
  } catch (error) {
    next(error);
  }
});

app.post("/api/targets", uploadTargetBundle.single("targetFile"), async (req, res, next) => {
  try {
    const targetFile = req.file;

    if (!targetFile) {
      res.status(400).json({ message: "Compiled target file is required." });
      return;
    }

    const rawIds = req.body.mappingIds;
    const mappingIds = rawIds ? JSON.parse(rawIds) : null;

    if (!Array.isArray(mappingIds) || mappingIds.length === 0) {
      await safeUnlink(targetFile.path);
      res.status(400).json({ message: "Mapping ID order is required to store the target bundle." });
      return;
    }

    await fs.copyFile(targetFile.path, CURRENT_TARGET_FILE);
    await safeUnlink(targetFile.path);

    const db = await readDb();
    const bundleVersion = Date.now();
    const bundleUrl = `/uploads/targets/current.mind?v=${bundleVersion}`;

    db.mappings = mappingIds
      .map((id, index) => {
        const found = db.mappings.find((item) => item.id === id);

        if (!found) {
          return null;
        }

        return {
          ...found,
          targetFileRef: bundleUrl,
          targetIndex: index,
          updatedAt: new Date().toISOString(),
        };
      })
      .filter(Boolean);

    db.targetBundle = {
      url: bundleUrl,
      filename: "current.mind",
      generatedAt: new Date().toISOString(),
      mappingCount: db.mappings.length,
    };

    await writeDb(db);

    res.json({
      message: "Target file saved successfully.",
      targetBundle: db.targetBundle,
      mappings: db.mappings,
    });
  } catch (error) {
    await safeUnlink(req.file?.path);
    next(error);
  }
});

app.use((req, res) => {
  res.status(404).json({ message: `Route not found: ${req.method} ${req.originalUrl}` });
});

app.use((error, _req, res, _next) => {
  const isMulter = error instanceof multer.MulterError;
  const statusCode = isMulter || error.message?.includes("Unsupported file type") ? 400 : 500;
  const message = isMulter ? "Upload failed. Please check file size and type." : error.message || "Unexpected server error.";

  res.status(statusCode).json({ message });
});

await ensureAppDirectories();

app.listen(PORT, () => {
  console.log(`AR Images server running at http://localhost:${PORT}`);
});
