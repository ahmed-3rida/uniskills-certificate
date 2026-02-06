<?php
/**
 * Script to download Cairo font automatically
 * Run this: php download_font.php
 */

echo "🔽 تحميل خط Cairo العربي...\n\n";

$fontsDir = __DIR__ . '/fonts';
if (!is_dir($fontsDir)) {
    mkdir($fontsDir, 0755, true);
    echo "✅ تم إنشاء مجلد fonts\n";
}

// Try multiple sources for Cairo font
$sources = [
    [
        'name' => 'Cairo Bold (Google Fonts)',
        'url' => 'https://github.com/google/fonts/raw/main/ofl/cairo/static/Cairo-Bold.ttf',
        'filename' => 'Cairo-Bold.ttf'
    ],
    [
        'name' => 'Cairo Regular (Fallback)',
        'url' => 'https://github.com/google/fonts/raw/main/ofl/cairo/static/Cairo-Regular.ttf',
        'filename' => 'Cairo-Regular.ttf'
    ]
];

$success = false;

foreach ($sources as $source) {
    echo "📥 محاولة تحميل: {$source['name']}...\n";
    
    $fontPath = $fontsDir . '/' . $source['filename'];
    
    // Try to download
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: PHP Script',
            'timeout' => 30
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ]);
    
    $fontData = @file_get_contents($source['url'], false, $context);
    
    if ($fontData !== false && strlen($fontData) > 1000) {
        file_put_contents($fontPath, $fontData);
        $size = filesize($fontPath);
        echo "✅ تم التحميل بنجاح: {$source['filename']}\n";
        echo "   الحجم: " . number_format($size / 1024, 2) . " KB\n\n";
        $success = true;
        
        // If this is Bold, we're done
        if ($source['filename'] === 'Cairo-Bold.ttf') {
            break;
        }
    } else {
        echo "❌ فشل التحميل من هذا المصدر\n\n";
    }
}

if ($success) {
    echo "🎉 تم! الخط جاهز للاستخدام\n";
    echo "📁 المسار: $fontsDir\n";
} else {
    echo "❌ فشل تحميل الخط من جميع المصادر\n\n";
    echo "📝 يمكنك تحميله يدوياً:\n";
    echo "1. اذهب إلى: https://fonts.google.com/specimen/Cairo\n";
    echo "2. حمّل الخط\n";
    echo "3. ضع ملف Cairo-Bold.ttf في مجلد fonts/\n";
}
?>
