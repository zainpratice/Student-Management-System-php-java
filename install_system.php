<?php
// Boost PHP execution time and memory limits
ini_set('max_execution_time', 300); // 5 minutes
ini_set('memory_limit', '512M');

// Log script start time
$start_time = microtime(true);

// Debug log: start
error_log("install_system.php: Installation script started.");

// Include the installer logic
include "script/install/install.php";

// Create an installer instance
$install = new install();

// Determine the installation step
$step = $install->step_install();

// Debug log: step determined
error_log("install_system.php: step_install() returned " . $step);

if ($step == 1) {
    // Show the installer page (HTML page for user interaction)
    include 'page/install/install_system.php';
} else {
    // If already installed, redirect to login page
    header("Location: login.php");
    exit();
}

// Debug log: end
$end_time = microtime(true);
$total_time = round($end_time - $start_time, 2);
error_log("install_system.php: Installation script finished. Total time: {$total_time} seconds.");
?>
