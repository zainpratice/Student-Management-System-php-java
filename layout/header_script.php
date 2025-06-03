<?php
// Start output buffering at the VERY BEGINNING
ob_start();

// Then start the session
session_start();

// Check if user is logged in
if(isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    // Include configuration files
    include "config/config.php";
    
    // Initialize database connection with error handling
    if(!class_exists('database')) {
        die("Database class not found. Check config.php");
    }
    $db = new database();
    
    // Verify database connection
    if(!$db || !method_exists($db, 'query')) {
        die("Failed to initialize database connection");
    }

    // Include all necessary files
    $required_files = [
        'script/program/program.php',
        'script/site_content/site_content.php',
        'script/payment/set_payment.php',
        'tool/vendor/vendor/autoload.php',
        'script/theme/theme.php',
        'script/user/user.php'
    ];
    
    foreach($required_files as $file) {
        if(!file_exists($file)) {
            error_log("Missing required file: $file");
            continue;
        }
        include $file;
    }

    // Initialize user object with validation
    $id = $_SESSION['user'] ?? null;
    if(!$id) {
        header("Location: login.php");
        exit;
    }

    $user_ob = new user($id);
    if(!$user_ob) {
        die("Failed to initialize user object");
    }

    // Get user info with null checks
    $user = $user_ob->get_user_info() ?? [];
    $login_user = $user_ob->get_login_user() ?? ['id' => null, 'permit' => null];

    // Set user variables with defaults
    $user_id = $login_user['id'] ?? null;
    $login_user_id = $login_user['id'] ?? null;
    $user_permit = $login_user['permit'] ?? 'guest';
    $role = $login_user['permit'] ?? 'guest';
    $login_user_role = $role;

    // Track login activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $browser = $user_ob->get_browser($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown');

    if($user_id && method_exists($db, 'set_login_user')) {
        $db->set_login_user($user_id, $ip, $browser);
    }

    // Initialize site content with check
    if(!class_exists('site_content')) {
        die("site_content class not found");
    }
    $site = new site_content();

    // Include and initialize other components with checks
    $components = [
        'subject' => 'script/subject/subject.php',
        'batch' => 'script/batch/batch.php',
        'program' => 'script/program/program.php',
        'student' => 'script/student/student.php',
        'sms' => 'script/sms/sms.php',
        'contest' => 'script/contest/contest.php',
        'payment' => 'script/payment/payment.php',
        'set_payment' => 'script/payment/set_payment.php',
        'id_card' => 'script/id_card/id_card.php',
        'attendence' => 'script/attendence/attendence.php',
        'notice' => 'script/notice/notice.php',
        'theme' => 'script/theme/theme.php',
        'report' => 'script/report/report.php',
        'account' => 'script/account/account.php',
        'site_activity' => 'script/site_activity/site_activity.php',
        'setting' => 'script/setting/setting.php',
        'graph' => 'script/graph/graph.php',
        'chat' => 'script/chat/chat.php',
        'exam' => 'script/exam/exam_category.php'
    ];

    foreach($components as $var => $file) {
        if(file_exists($file)) {
            include $file;
            if($var === 'sms') {
                $$var = new $var($user_id);
            } else {
                $$var = new $var();
            }
        } else {
            error_log("Component file missing: $file");
            $$var = null;
        }
    }

    // End output buffering and flush
    ob_end_flush();
} else {
    // If not logged in, redirect properly
    header("Location: login.php");
    exit;
}
?>