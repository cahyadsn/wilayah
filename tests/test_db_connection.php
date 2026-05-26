<?php
// Test for database connection failure handling in db.php

$original_file = __DIR__ . '/../apps/inc/db.php';

// Set environment variables to force a connection failure
putenv('DB_USER=invalid_test_user');
putenv('DB_PASS=invalid_test_pass');

// Capture output
ob_start();
include $original_file;
$output = ob_get_clean();

// Reset environment variables
putenv('DB_USER');
putenv('DB_PASS');

// Assert
if (strpos($output, 'Connection failed:') !== false) {
    echo "PASS: Database connection failure caught and handled correctly.\n";
    exit(0);
} else {
    echo "FAIL: Expected error message not found. Output was: \n" . $output . "\n";
    exit(1);
}
