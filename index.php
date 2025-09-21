<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>System Diagnostics</h2>";

// Check if the target file exists
$dashboard_path = __DIR__ . '/php-app/dashboard.php';
$config_path = __DIR__ . '/php-app/config.php';

echo "<h3>File System Check:</h3>";
echo "Dashboard path: " . $dashboard_path . "<br>";
echo "Dashboard exists: " . (file_exists($dashboard_path) ? 'Yes' : 'No') . "<br>";
echo "Config path: " . $config_path . "<br>";
echo "Config exists: " . (file_exists($config_path) ? 'Yes' : 'No') . "<br>";

if (!file_exists($dashboard_path)) {
    die("Error: Dashboard file not found at: " . $dashboard_path . "<br>Please check if the file exists and the path is correct.");
}

// Check PHP syntax
echo "<h3>PHP Syntax Check:</h3>";
if (file_exists($dashboard_path)) {
    $output = shell_exec("php -l " . escapeshellarg($dashboard_path) . " 2>&1");
    echo "Dashboard syntax: <pre>" . htmlspecialchars($output) . "</pre>";
}

if (file_exists($config_path)) {
    $output = shell_exec("php -l " . escapeshellarg($config_path) . " 2>&1");
    echo "Config syntax: <pre>" . htmlspecialchars($output) . "</pre>";
}

// Test database connection
echo "<h3>Database Connection Test:</h3>";
try {
    include_once $config_path;
    echo "Database connection: <span style='color: green;'>SUCCESS</span><br>";
    echo "Database host: " . $host . "<br>";
    echo "Database name: " . $db . "<br>";
} catch (Exception $e) {
    echo "Database connection: <span style='color: red;'>FAILED</span><br>";
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
}

echo "<h3>Navigation:</h3>";
echo "<a href='php-app/dashboard.php' style='padding: 10px; background: #007cba; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a><br><br>";

echo "Auto-redirect in 10 seconds...<br>";
echo '<meta http-equiv="refresh" content="10;url=php-app/dashboard.php">';
?>