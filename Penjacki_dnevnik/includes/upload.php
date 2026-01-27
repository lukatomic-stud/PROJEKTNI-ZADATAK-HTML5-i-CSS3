<?php

function save_uploaded_image(array $file, string $targetDir = "uploads"): ?string {
    if (!isset($file["tmp_name"]) || $file["error"] !== UPLOAD_ERR_OK) return null;

    $allowed = ["image/jpeg"=>"jpg","image/png"=>"png","image/webp"=>"webp"];
    $mime = mime_content_type($file["tmp_name"]);
    if (!isset($allowed[$mime])) return null;

    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(12)) . "." . $ext;
    $path = $targetDir . "/" . $name;

    if (move_uploaded_file($file["tmp_name"], $path)) {
        return $path;
    }
    return null;
}
