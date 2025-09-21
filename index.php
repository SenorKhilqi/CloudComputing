<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the target file exists
$dashboard_path = __DIR__ . '/php-app/dashboard.php';

if (!file_exists($dashboard_path)) {
    die("Error: Dashboard file not found at: " . $dashboard_path . "<br>Please check if the file exists and the path is correct.");
}

// Check if the directory is readable
if (!is_readable(dirname($dashboard_path))) {
    die("Error: Cannot read directory: " . dirname($dashboard_path));
}

// Redirect to dashboard with absolute path
$redirect_url = 'php-app/dashboard.php';

// Optional: Add debug information (remove in production)
echo "Redirecting to: " . $redirect_url . "<br>";
echo "Full path: " . $dashboard_path . "<br>";
echo "File exists: " . (file_exists($dashboard_path) ? 'Yes' : 'No') . "<br>";
echo "<br>If you see this message, redirect will happen in 3 seconds...";

// Use meta refresh as backup
echo '<meta http-equiv="refresh" content="3;url=' . $redirect_url . '">';

// Primary redirect
header('Location: ' . $redirect_url);
exit();
?>