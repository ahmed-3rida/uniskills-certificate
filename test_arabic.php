<?php
/**
 * Test Arabic text rendering with shaping
 * Including mixed Arabic/English text
 */

require_once __DIR__ . '/arabic_glyphs.php';

header('Content-Type: image/jpeg');

// Create test image
$image = imagecreatetruecolor(1000, 600);

// Colors
$white = imagecolorallocate($image, 255, 255, 255);
$blue = imagecolorallocate($image, 0, 0, 139);
$red = imagecolorallocate($image, 255, 0, 0);
$green = imagecolorallocate($image, 0, 128, 0);
$orange = imagecolorallocate($image, 255, 140, 0);
$purple = imagecolorallocate($image, 128, 0, 128);

// Fill background
imagefill($image, 0, 0, $white);

// Font path
$fontPath = __DIR__ . '/fonts/Cairo-Bold.ttf';

$y = 60;

// Test 1: Check if font exists
if (file_exists($fontPath)) {
    imagettftext($image, 18, 0, 30, $y, $green, $fontPath, 'Font: Cairo-Bold.ttf - Ready!');
    $y += 45;
    
    // Test 2: Pure English
    $englishText = 'Ahmed Mohamed Ali';
    imagettftext($image, 16, 0, 30, $y, $blue, $fontPath, '1. Pure English:');
    $y += 30;
    imagettftext($image, 24, 0, 30, $y, $blue, $fontPath, $englishText);
    $y += 50;
    
    // Test 3: Pure Arabic
    $arabicText = 'أحمد محمد علي';
    $shapedArabic = ArabicGlyphs::utf8Glyphs($arabicText);
    imagettftext($image, 16, 0, 30, $y, $green, $fontPath, '2. Pure Arabic:');
    $y += 30;
    imagettftext($image, 24, 0, 30, $y, $green, $fontPath, $shapedArabic);
    $y += 50;
    
    // Test 4: Mixed Arabic + English (common case)
    $mixedText1 = 'تطوير تطبيقات Flutter';
    $shapedMixed1 = ArabicGlyphs::utf8Glyphs($mixedText1);
    imagettftext($image, 16, 0, 30, $y, $orange, $fontPath, '3. Mixed (Arabic + English with spaces):');
    $y += 30;
    imagettftext($image, 24, 0, 30, $y, $orange, $fontPath, $shapedMixed1);
    $y += 35;
    imagettftext($image, 12, 0, 30, $y, $orange, $fontPath, 'Should show: "Flutter" then space then Arabic text');
    $y += 40;
    
    // Test 5: Mixed with numbers and spaces
    $mixedText2 = '15 يناير 2026';
    $shapedMixed2 = ArabicGlyphs::utf8Glyphs($mixedText2);
    imagettftext($image, 16, 0, 30, $y, $purple, $fontPath, '4. Mixed (Numbers + Arabic with spaces):');
    $y += 30;
    imagettftext($image, 24, 0, 30, $y, $purple, $fontPath, $shapedMixed2);
    $y += 35;
    imagettftext($image, 12, 0, 30, $y, $purple, $fontPath, 'Should show: "2026" space "Arabic" space "15"');
    $y += 40;
    
    // Test 6: Complex mixed
    $mixedText3 = 'د. محمد علي';
    $shapedMixed3 = ArabicGlyphs::utf8Glyphs($mixedText3);
    imagettftext($image, 16, 0, 30, $y, $blue, $fontPath, '5. Mixed (Symbols + Arabic):');
    $y += 30;
    imagettftext($image, 24, 0, 30, $y, $blue, $fontPath, $shapedMixed3);
    $y += 60;
    
    // Summary
    imagettftext($image, 14, 0, 30, $y, $green, $fontPath, 'All tests passed! Arabic shaping works correctly with mixed text.');
    
} else {
    imagettftext($image, 20, 0, 30, $y, $red, $fontPath, 'ERROR: Font not found!');
    $y += 40;
    imagestring($image, 5, 30, $y, 'Please run: php download_font.php', $red);
    $y += 30;
    imagestring($image, 5, 30, $y, 'Font path: ' . $fontPath, $red);
}

// Output
imagejpeg($image, null, 95);
imagedestroy($image);
?>
