<?php
// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, log them instead

// Set headers first
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Error handler function
function sendError($message, $code = 500) {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $message]);
    exit();
}

try {
    // Check if GD library is available
    if (!extension_loaded('gd')) {
        sendError('GD Library is not installed', 500);
    }

    // Load configuration
    $configPath = __DIR__ . '/config.php';
    if (!file_exists($configPath)) {
        sendError('Configuration file not found', 500);
    }
    $config = require $configPath;

    // Get request data
    $rawInput = file_get_contents('php://input');
    $data = json_decode($rawInput, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendError('Invalid JSON data: ' . json_last_error_msg(), 400);
    }

    // Validate required fields
    $required = ['studentName', 'courseName', 'instructorName', 'date', 'language'];
    foreach ($required as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            sendError("Missing or empty required field: $field", 400);
        }
    }

    $studentName = trim($data['studentName']);
    $courseName = trim($data['courseName']);
    $instructorName = trim($data['instructorName']);
    $date = trim($data['date']);
    $language = trim($data['language']);

    // Validate language
    if (!in_array($language, ['ar', 'en'])) {
        sendError('Invalid language. Must be "ar" or "en"', 400);
    }

    // Load the certificate template
    $templatePath = $config['templates_dir'] . "/{$language}.jpg";
    if (!file_exists($templatePath)) {
        sendError("Certificate template not found: {$language}.jpg", 404);
    }

    // Create image from template
    $image = @imagecreatefromjpeg($templatePath);
    if (!$image) {
        sendError('Failed to load certificate template. File may be corrupted.', 500);
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
            $bbox = @imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox) {
                $textWidth = abs($bbox[4] - $bbox[0]);
                $x = ($width - $textWidth) / 2;
                @imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
            }
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
            @imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
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
        $bbox = @imagettfbbox($instructorFontSize, 0, $fontPath, $instructorName);
        if ($bbox) {
            $textWidth = abs($bbox[4] - $bbox[0]);
        } else {
            $textWidth = imagefontwidth(3) * strlen($instructorName);
        }
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

    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'image' => 'data:image/jpeg;base64,' . $base64
    ]);

} catch (Exception $e) {
    // Clean up image if it exists
    if (isset($image) && $image) {
        @imagedestroy($image);
    }
    
    sendError('Server error: ' . $e->getMessage(), 500);
}
?>
