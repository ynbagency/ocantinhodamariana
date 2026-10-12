# Gate 6 — Media pipeline and visual signage

## Delivered
- Authenticated media library for restaurant operators.
- JPEG/PNG/WebP validation with 12 MB upload ceiling.
- Original image preservation.
- Thumbnail, web and signage derivatives; GD resizing when available, safe original-byte fallback otherwise.
- SHA-256 retained for originals and recalculated for packaged signage derivatives.
- Configurable focal point X/Y.
- Editorial content can reference a media asset.
- Signage manifest packages every referenced media file as an authenticated, checksummed asset.
- Raspberry stores nested media files in the same atomic release as `index.html`.
- TV renderer uses local media only after activation, with focal-point-aware cover cropping.
- Failed media/checksum release leaves the previous release active.

## Not claimed as complete
- Visual crop editor with drag-to-focus.
- Video transcoding.
- EXIF stripping when GD is unavailable.
- Physical TV colour/readability acceptance.
- Production storage/CDN configuration.

These are intentionally not represented as completed.
