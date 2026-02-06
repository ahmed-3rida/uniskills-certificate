<?php
/**
 * Certificate Generator Configuration
 * عدل هذه الإعدادات حسب احتياجك
 */

return [
    // Text positions (scaled automatically based on image size)
    'positions' => [
    'student_name' => [
        'y' => 132,
        'font_size' => 11,
        'centered' => true,
    ],
    'course_name' => [
        'y' => 173,
        'font_size' => 10,
        'centered' => true,
    ],
    'date' => [
        'x' => 83,
        'y_from_bottom' => 40,
        'font_size' => 5,
        'centered' => false,
    ],
    'instructor' => [
        'x_from_right' => 83,
        'y_from_bottom' => 40,
        'font_size' => 5,
        'centered' => false,
    ],
],
    
    // Image settings
    'image' => [
        'quality' => 95,          // JPEG quality (1-100)
        'base_width' => 400,      // Base width for scaling calculations
    ],
    
    // Font settings
    'font' => [
        'path' => __DIR__ . '/fonts/Cairo-Bold.ttf',
        'fallback' => true,       // Use built-in font if custom font not found
    ],
    
    // Security
    'allowed_origins' => ['*'],   // CORS allowed origins (* = all)
    
    // Paths
    'templates_dir' => __DIR__ . '/templates',
];
