<?php
/**
 * Certificate Generator Configuration
 * عدل هذه الإعدادات حسب احتياجك
 */

return [
    // Text positions (scaled automatically based on image size)
    'positions' => [
        'student_name' => [
            'y' => 105,           // Y position from top
            'font_size' => 30,    // Font size
            'centered' => true,   // Center horizontally
        ],
        'course_name' => [
            'y' => 148,
            'font_size' => 16,
            'centered' => true,
        ],
        'date' => [
            'x' => 75,            // X position from left
            'y_from_bottom' => 35, // Y position from bottom
            'font_size' => 14,
            'centered' => false,
        ],
        'instructor' => [
            'x_from_right' => 70, // X position from right
            'y_from_bottom' => 35,
            'font_size' => 14,
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
