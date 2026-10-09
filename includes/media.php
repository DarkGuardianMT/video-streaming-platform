<?php
// Compatibility at render time only: existing database rows are never migrated.
function legacy_media_paths(): array {
    return [
        'videos/harry.mp4', 'videos/lion.mp4', 'videos/mars.mp4',
        'videos/ocean-waves.mp4', 'videos/ocean.mp4', 'videos/vikings.mp4',
        'thumbnails/gaming.jpg', 'thumbnails/gta6.jpg', 'thumbnails/harry-potter.jpg',
        'thumbnails/lion.jpg', 'thumbnails/mars.jpg', 'thumbnails/ocean-waves-2.jpg',
        'thumbnails/ocean-waves.jpg', 'thumbnails/planet-earth.jpg',
        'thumbnails/sql.png', 'thumbnails/vikings-season1.jpg',
    ];
}

function is_legacy_media(string $path, bool $thumbnail = false): bool {
    return str_starts_with($path, $thumbnail ? 'thumbnails/' : 'videos/')
        && in_array($path, legacy_media_paths(), true);
}

function safe_media_path(string $path, bool $thumbnail = false): bool {
    $pattern = $thumbnail
        ? '~\Athumbnails/[a-zA-Z0-9_-]+\.(?:jpg|jpeg|png|webp)\z~'
        : '~\Avideos/[a-zA-Z0-9_-]+\.(?:mp4|webm|ogg)\z~';
    if (!preg_match($pattern, $path)) {
        return false;
    }
    return is_file(__DIR__ . '/../' . ($thumbnail ? 'images/' : '') . $path);
}

function resolved_media_path(string $path, bool $thumbnail = false): string {
    if (is_legacy_media($path, $thumbnail) || !safe_media_path($path, $thumbnail)) {
        return $thumbnail ? 'thumbnails/big-buck-bunny-demo.jpg' : 'videos/big-buck-bunny-demo.mp4';
    }
    return $path;
}

function media_url(string $path, bool $thumbnail = false): string {
    return '/video-streaming-platform/' . ($thumbnail ? 'images/' : '') . resolved_media_path($path, $thumbnail);
}

function video_mime(string $path): string {
    return ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'ogg' => 'video/ogg'][pathinfo(resolved_media_path($path), PATHINFO_EXTENSION)];
}
