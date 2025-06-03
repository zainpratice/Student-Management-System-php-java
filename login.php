<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<?php


session_start();
if( isset($_SESSION['user'])=="" ){
include "layout/header_lib.php";
include "script/install/install.php";
$install=new install();
$step=$install->step_install();
if($step==2)include 'page/login/login.php';
else header("Location: install_system.php");
}
else{
	header("Location: index.php");
}
?>