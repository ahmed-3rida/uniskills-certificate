<?php
/**
 * Script to download Cairo font automatically
 * Run this once: php download_font.php
 */

$fontUrl = 'https://github.com/google/fonts/raw/main/ofl/cairo/Cairo%5Bslnt%2Cwght%5D.ttf';
$fontPath = __DIR__ . '/fonts/Cairo-Bold.ttf';

echo "Downloading Cairo font...\n";

$fontData = @file_get_contents($fontUrl);

if ($fontData === false) {
    echo "❌ Failed to download font. You can download it manually from:\n";
    echo "https://fonts.google.com/specimen/Cairo\n";
    exit(1);
}

if (!is_dir(__DIR__ . '/fonts')) {
    mkdir(__DIR__ . '/fonts', 0755, true);
}

file_put_contents($fontPath, $fontData);

echo "✅ Font downloaded successfully to: fonts/Cairo-Bold.ttf\n";
echo "File size: " . number_format(filesize($fontPath) / 1024, 2) . " KB\n";
?>
