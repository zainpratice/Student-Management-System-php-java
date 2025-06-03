<?php
// Database configuration from docker-compose.yml
define('db_host', 'mysql');  // Correct: 'mysql' service name from Docker Compose
define('db_user', 'smsuser'); // Correct: 'smsuser' as per your docker-compose.yml
define('db_pass', 'smspassword'); // Correct: 'smspassword' as per your docker-compose.yml
define('db_name', 'student_management'); // Correct: 'student_management' as per your docker-compose.yml
define('db_port', '3306'); // MySQL port
?>
