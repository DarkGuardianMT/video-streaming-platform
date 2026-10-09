# Focused security review — 9 October 2026

This review addresses critical issues in the school project's existing flows; it is not a production security certification.

## Changes

- Shared bootstrap initializes sessions before output on every protected endpoint, including deletion. Strict session IDs, cookie-only transport, HttpOnly, SameSite=Lax, and Secure cookies on HTTPS are enabled. Login regenerates the session ID and clears the pre-login session.
- Admin endpoints check authentication before rendering and recheck the current database role on each request, including after a role is revoked. Profile redirects also occur before rendering.
- Login, registration, upload, edit, delete, and logout validate random session CSRF tokens with constant-time comparison. Delete and logout require POST; logout links are now forms.
- Uploads check PHP upload errors, actual uploaded-file origin, actual file size, extension/MIME agreement with `fileinfo`, and thumbnail structure/dimensions. Limits are 50 MiB per video and 2 MiB per thumbnail. Random server filenames prevent client-controlled paths and collisions. Both files are validated before moving; a failed move or database insert removes only files created by that request.
- Edited paths must name existing files in the appropriate media folder or explicitly known legacy demo aliases. Traversal and cross-folder paths are rejected. Playback declares the resolved video's MIME type.
- Apache rules deny configuration, schema, audit, and test files, hidden paths, directory listing, and non-media/script filenames in upload folders. Responses receive `X-Content-Type-Options: nosniff` when `mod_headers` is enabled.
- PDO uses UTF-8 and native prepared statements. Local credentials and uploads are ignored by Git. Connection/query/uncaught errors no longer expose database exceptions to visitors. Password hashing remains in place; new passwords have length limits and registration cannot assign an admin role.

## Verification

- PHP 8.2.12 syntax checks: all 18 PHP files passed; `git diff --check` passed.
- `tests/security_smoke.py`: passed with PHP's output buffering disabled in a temporary application copy and disposable PDO SQLite database. Covered guest/user/admin authorization, missing/invalid CSRF, registration/password limits, redirects, session regeneration, role revocation, legacy video/thumbnail references, edit path validation, real multipart upload rejection (fake content, executable extension, MIME mismatch, empty/missing files, and size limits), successful upload/playback, failed-insert file cleanup, record deletion, and logout. No real MySQL connection was made.
- Temporary Apache 2.4.58/PHP instance bound to localhost: configuration syntax passed; direct config/schema/docs/tests/media-script access and directory URLs returned 403. Both MP4s supported HTTP 206 range requests with correct Content-Range and MIME; JPEG MIME, nosniff, HttpOnly and SameSite cookies were verified. The temporary server was stopped.
- FFprobe confirmed both demos are 320 × 180 H.264/yuv420p with AAC, exactly 12 and 10 seconds. FFmpeg decoded both entire clips without errors. This verifies compatible media and HTTP delivery; browser playback was not visually exercised.

## Limits

The real database was neither inspected nor changed. The fresh-database MySQL schema and MySQL-specific integration were not executed; existing installations may have different column sizes. No automatic schema import or account promotion occurs. The fixture replaces only its copied connection file.

MIME/container checks do not guarantee every uploaded video codec is playable and do not replace transcoding or malware scanning. Rate limiting, account recovery, email verification, and production deployment hardening remain outside this focused cleanup. PHP's development server ignores `.htaccess`; the documented Apache configuration is needed for those protections.
