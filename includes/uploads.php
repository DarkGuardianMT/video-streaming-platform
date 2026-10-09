<?php
// The client-provided MIME value is deliberately ignored.
function validate_upload(array $file, bool $thumbnail = false): string {
    if (!isset($file['error']) || !is_int($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload mislukt of bestand te groot.');
    }
    $tmp = $file['tmp_name'] ?? null;
    $name = $file['name'] ?? null;
    if (!is_string($tmp) || !is_string($name) || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Ongeldige upload.');
    }
    $size = filesize($tmp);
    $limit = $thumbnail ? 2 * 1024 * 1024 : 50 * 1024 * 1024;
    if ($size === false || $size < 1 || $size > $limit) {
        throw new RuntimeException('Bestand te groot of leeg (video: 50 MiB; thumbnail: 2 MiB).');
    }
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $types = $thumbnail
        ? ['jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp']]
        : ['mp4' => ['video/mp4'], 'webm' => ['video/webm'], 'ogg' => ['video/ogg', 'application/ogg']];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    if (!isset($types[$ext]) || !in_array($mime, $types[$ext], true)) {
        throw new RuntimeException('Bestandstype komt niet overeen met een toegestaan formaat.');
    }
    if ($thumbnail) {
        $image = @getimagesize($tmp);
        if (!$image || $image[0] > 4096 || $image[1] > 4096 || $image[0] * $image[1] > 16000000
            || !in_array($image['mime'], $types[$ext], true)) {
            throw new RuntimeException('Ongeldige thumbnail of te grote afmetingen.');
        }
    }
    return $ext;
}

function upload_filename(string $label, string $extension): string {
    $base = trim(preg_replace('/[^a-z0-9_-]+/', '-', strtolower($label)), '-');
    return substr($base ?: 'upload', 0, 60) . '-' . bin2hex(random_bytes(16)) . '.' . $extension;
}
