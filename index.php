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
    
    // Load Arabic text shaping library
    require_once __DIR__ . '/arabic_glyphs.php';

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

    // Function to fix Arabic text (proper RTL handling with shaping)
    function fixArabicText($text) {
        // Check if text contains Arabic
        if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            return $text; // Not Arabic, return as is
        }
        
        // Use Arabic Glyphs library for proper shaping
        return ArabicGlyphs::utf8Glyphs($text);
    }

    // Function to write centered text with UTF-8 support
    function writeCenteredText($image, $fontSize, $y, $text, $color, $fontPath, $width, $isArabic = false) {
        // Convert text to UTF-8 if needed
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        // Fix Arabic text (reshape and reverse)
        if ($isArabic) {
            $text = fixArabicText($text);
        }
        
        if ($fontPath && file_exists($fontPath)) {
            // Get text bounding box
            $bbox = @imagettfbbox($fontSize, 0, $fontPath, $text);
            if ($bbox) {
                $textWidth = abs($bbox[4] - $bbox[0]);
                $x = ($width - $textWidth) / 2;
                
                // Use imagettftext which supports Unicode properly
                @imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
                return true;
            }
        }
        
        // Fallback: use imagestring for ASCII only
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            // Arabic text without font - show warning
            $placeholderWidth = imagefontwidth(5) * 15;
            $x = ($width - $placeholderWidth) / 2;
            imagestring($image, 5, $x, $y, '[Need Arabic Font]', $color);
        } else {
            $textWidth = imagefontwidth(5) * strlen($text);
            $x = ($width - $textWidth) / 2;
            imagestring($image, 5, $x, $y, $text, $color);
        }
        return false;
    }

    // Function to write text at specific position with UTF-8 support
    function writeText($image, $fontSize, $x, $y, $text, $color, $fontPath, $isArabic = false) {
        // Convert text to UTF-8 if needed
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        // Fix Arabic text (reshape and reverse)
        if ($isArabic) {
            $text = fixArabicText($text);
        }
        
        if ($fontPath && file_exists($fontPath)) {
            // Use imagettftext for proper Unicode rendering
            @imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $text);
            return true;
        }
        
        // Fallback for non-Arabic text
        if (!preg_match('/[\x{0600}-\x{06FF}]/u', $text)) {
            imagestring($image, 3, $x, $y, $text, $color);
        }
        return false;
    }

    // Add text to certificate using configuration
    $positions = $config['positions'];
    
    // Allow custom positions from request (for testing)
    if (isset($data['positions']) && is_array($data['positions'])) {
        $positions = array_replace_recursive($positions, $data['positions']);
    }
    
    // Determine if Arabic
    $isArabic = ($language === 'ar');
    
    // Get language-specific positions
    $langPositions = $isArabic ? 
        (isset($config['positions_ar']) ? $config['positions_ar'] : $positions) : 
        (isset($config['positions_en']) ? $config['positions_en'] : $positions);

    // Student Name
    if ($langPositions['student_name']['centered']) {
        $y = $langPositions['student_name']['y'] * $scale;
        $fontSize = $langPositions['student_name']['font_size'] * $scale;
        writeCenteredText($image, $fontSize, $y, $studentName, $white, $fontPath, $width, $isArabic);
    }

    // Course Name
    if ($langPositions['course_name']['centered']) {
        $y = $langPositions['course_name']['y'] * $scale;
        $fontSize = $langPositions['course_name']['font_size'] * $scale;
        writeCenteredText($image, $fontSize, $y, $courseName, $white, $fontPath, $width, $isArabic);
    }

    // Date
    $dateX = $langPositions['date']['x'] * $scale;
    $dateY = $height - ($langPositions['date']['y_from_bottom'] * $scale);
    $dateFontSize = $langPositions['date']['font_size'] * $scale;
    writeText($image, $dateFontSize, $dateX, $dateY, $date, $white, $fontPath, $isArabic);

    // Instructor Name
    $instructorFontSize = $langPositions['instructor']['font_size'] * $scale;
    $instructorY = $height - ($langPositions['instructor']['y_from_bottom'] * $scale);

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
    $instructorX = $width - ($langPositions['instructor']['x_from_right'] * $scale) - $textWidth;
    writeText($image, $instructorFontSize, $instructorX, $instructorY, $instructorName, $white, $fontPath, $isArabic);

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
