<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load configuration
$config = require __DIR__ . '/config.php';

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['studentName', 'courseName', 'instructorName', 'date', 'language'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

$studentName = $data['studentName'];
$courseName = $data['courseName'];
$instructorName = $data['instructorName'];
$date = $data['date'];
$language = $data['language']; // 'ar' or 'en'

// Load the certificate template
$templatePath = $config['templates_dir'] . "/{$language}.jpg";
if (!file_exists($templatePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Certificate template not found']);
    exit();
}

// Create image from template
$image = imagecreatefromjpeg($templatePath);
if (!$image) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load certificate template']);
    exit();
}

// Set up text color (white)
$white = imagecolorallocate($image, 255, 255, 255);

// Font path
$fontPath = $config['font']['path'];
if (!file_exists($fontPath)) {
    $fontPath = null;
}

// Get image dimensions
$width = imagesx($image);
$height = imagesy($image);

// Calculate scale factor
$scale = $width / $config['image']['base_width'];

// Function to write centered text
function writeCenteredText($image, $fontSize, $y, $text, $color, $fontPath, $width) {
    if ($fontPath && file_exists($fontPath)) {
        // Get text bounding box
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = abs($bbox[4] - $bbox[0]);
        $x = ($width - $textWidth) / 2;
        imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
    } else {
        // Fallback to built-in font
        $textWidth = imagefontwidth(5) * strlen($text);
        $x = ($width - $textWidth) / 2;
        imagestring($image, 5, $x, $y, $text, $color);
    }
}

// Function to write text at specific position
function writeText($image, $fontSize, $x, $y, $text, $color, $fontPath) {
    if ($fontPath && file_exists($fontPath)) {
        imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
    } else {
        imagestring($image, 3, $x, $y, $text, $color);
    }
}

// Add text to certificate using configuration
$positions = $config['positions'];

// Student Name
if ($positions['student_name']['centered']) {
    $y = $positions['student_name']['y'] * $scale;
    $fontSize = $positions['student_name']['font_size'] * $scale;
    writeCenteredText($image, $fontSize, $y, $studentName, $white, $fontPath, $width);
}

// Course Name
if ($positions['course_name']['centered']) {
    $y = $positions['course_name']['y'] * $scale;
    $fontSize = $positions['course_name']['font_size'] * $scale;
    writeCenteredText($image, $fontSize, $y, $courseName, $white, $fontPath, $width);
}

// Date
$dateX = $positions['date']['x'] * $scale;
$dateY = $height - ($positions['date']['y_from_bottom'] * $scale);
$dateFontSize = $positions['date']['font_size'] * $scale;
writeText($image, $dateFontSize, $dateX, $dateY, $date, $white, $fontPath);

// Instructor Name
$instructorFontSize = $positions['instructor']['font_size'] * $scale;
$instructorY = $height - ($positions['instructor']['y_from_bottom'] * $scale);

// Calculate instructor X position (from right)
if ($fontPath && file_exists($fontPath)) {
    $bbox = imagettfbbox($instructorFontSize, 0, $fontPath, $instructorName);
    $textWidth = abs($bbox[4] - $bbox[0]);
} else {
    $textWidth = imagefontwidth(3) * strlen($instructorName);
}
$instructorX = $width - ($positions['instructor']['x_from_right'] * $scale) - $textWidth;
writeText($image, $instructorFontSize, $instructorX, $instructorY, $instructorName, $white, $fontPath);

// Output image as base64
ob_start();
imagejpeg($image, null, $config['image']['quality']);
$imageData = ob_get_clean();
$base64 = base64_encode($imageData);

// Clean up
imagedestroy($image);

// Return response
echo json_encode([
    'success' => true,
    'image' => 'data:image/jpeg;base64,' . $base64
]);
?>
