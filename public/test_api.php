<?php
echo "<h2>Checking API folder location</h2>";
echo "Current directory: " . __DIR__ . "<br><br>";

// Check if api folder exists in public
if (is_dir(__DIR__ . '/api')) {
    echo "✓ API folder EXISTS in public<br>";
    echo "Location: " . __DIR__ . '/api<br><br>';
    
    // List files in api folder
    echo "Files in api folder:<br>";
    $files = scandir(__DIR__ . '/api');
    echo "<pre>";
    print_r($files);
    echo "</pre>";
} else {
    echo "✗ API folder does NOT exist in public<br>";
    echo "Expected location: " . __DIR__ . '/api<br><br>';
}

// Check parent directory
if (is_dir(dirname(__DIR__) . '/api')) {
    echo "✓ API folder found in parent directory<br>";
    echo "Location: " . dirname(__DIR__) . '/api<br><br>';
    
    // List files
    echo "Files in parent api folder:<br>";
    $files = scandir(dirname(__DIR__) . '/api');
    echo "<pre>";
    print_r($files);
    echo "</pre>";
}
?>