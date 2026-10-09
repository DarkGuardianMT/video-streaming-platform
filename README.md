# NETFISH

An early PHP/MySQL school project for browsing a video catalogue and playing local videos in the browser. The interface is mainly in Dutch.

## Features

- Video grid, title/category search, and HTML5 video playback.
- Registration with hashed passwords, login/logout, and a profile page.
- Admin dashboard to upload, edit, and delete video records. Deletion keeps the media files.

Built with PHP 8.2+, PDO, MySQL/MariaDB, HTML, CSS, and JavaScript; developed locally with XAMPP. There is no transcoding service, subscription system, or adaptive streaming.

## Local setup

1. Place the folder at `xampp/htdocs/video-streaming-platform/`. Start Apache and MySQL.
2. Enable PHP `pdo_mysql` and `fileinfo`. Set `upload_max_filesize = 50M` and `post_max_size = 55M`, then restart Apache. Thumbnails are limited to 2 MiB.
3. For a **new installation**, create an empty `netfish` database in phpMyAdmin and manually import [database/schema.sql](database/schema.sql). Do not import this into an existing database; no automatic migrations run.
4. Copy `includes/config.example.php` to `includes/config.local.php` and set local credentials. This file is ignored by Git. Alternatively set `NETFISH_DB_HOST`, `NETFISH_DB_NAME`, `NETFISH_DB_USER`, and `NETFISH_DB_PASSWORD` in the server environment.
5. Open `http://localhost/video-streaming-platform/` and register an account. Registration always creates a normal user. To try the admin pages, deliberately set that user's `isAdmin` to `1` in your disposable local database using phpMyAdmin.

Apache must permit the supplied `.htaccess` directives (`AllowOverride FileInfo AuthConfig Options`) and load `mod_authz_core`, `mod_mime`, and `mod_rewrite`. They prevent directory listing, direct access to configuration and database files, and script execution in media directories. PHP's development server ignores these rules; bind it only to localhost. Use a restricted database account outside local XAMPP development.

## Demo media and status

Two small *Big Buck Bunny* excerpts and extracted thumbnails are included under **CC BY 3.0**, with attribution in the application and [MEDIA_LICENSES.md](MEDIA_LICENSES.md). Removed legacy paths resolve to the demo at render time; existing database rows and titles stay intact. The playback page identifies substituted footage.

This is a learning project, not a production service. Session, authorization, CSRF, and upload checks are included; uploads are not transcoded or malware-scanned, and account endpoints have no rate limiting. Historical unverified media remains in older commits: see [the media audit](docs/media-audit.md). No repository-wide code license has been selected.

## Checks

Run `php -l path/to/file.php` for syntax checks. Run `python tests/security_smoke.py` with PHP on PATH, PHP `pdo_sqlite` enabled, and Python 3 installed for HTTP security and playback-reference checks. The suite uses a temporary project copy and disposable SQLite fixtures and never connects to your MySQL database; it does not replace MySQL integration testing.
