<?php
require_once __DIR__ . '/vendor/autoload.php';

use Services\CloudinaryService;

// Load env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$service = new CloudinaryService();

echo "1. Creating dummy file...\n";
$tmpFile = __DIR__ . '/test_temp_img.png';
$pngData = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==");
if (file_put_contents($tmpFile, $pngData) === false) {
    die("Failed to write to $tmpFile\n");
}
echo "File created at $tmpFile\n";

$fileArray = [
    'tmp_name' => $tmpFile,
    'name' => 'test_1x1.png',
    'type' => 'image/png',
    'error' => 0,
    'size' => strlen($pngData)
];

echo "2. Uploading...\n";
try {
    $url = $service->upload($fileArray);
    echo "Uploaded URL: $url\n";
} catch (Exception $e) {
    die("Upload failed: " . $e->getMessage() . "\n");
}

echo "3. Extracting Public ID...\n";
$path = parse_url($url, PHP_URL_PATH);
$publicId = null;
if (preg_match('/upload\/(?:v\d+\/)?(.+)\.[a-zA-Z0-9]+$/', $path, $matches)) {
    $publicId = $matches[1];
    echo "Extracted Public ID: $publicId\n";
} else {
    die("Failed to extract ID from $path\n");
}

echo "4. Deleting...\n";
$result = $service->delete($publicId);
print_r($result);

if (isset($result['result']) && $result['result'] == 'ok') {
    echo "SUCCESS: Image deleted.\n";
} else {
    echo "FAILURE: Could not delete.\n";
}

// Cleanup
unlink($tmpFile);
