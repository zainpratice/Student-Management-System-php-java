<?php
// Start output buffering
ob_start();

// Includes and setup
include "config/config.php";
include "layout/header_lib.php";
$db = new database();

// If you have any login/redirect logic that uses header(), put it here BEFORE any HTML:
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login || <?php echo $db->site_name; ?></title>
  <link rel="stylesheet" type="text/css" href="page/login/style/style.css">
  <script type="text/javascript" src="page/login/js/script.js"></script>
  <script type="text/javascript" src="page/login/js/ajax.js"></script>
</head>

<body style="background-size: 100%;">
<div class="container" style="width: 100%">

  <div class="row">
    <div class="col-md-4  col-sm-12"></div>
    <div class="col-md-4 col-sm-12">
      <div id="login-box" style="margin-top: 45px;">
        <div class="header_box">Login Your ID</div>
        <div class="logo">
          <h1 class="logo-caption"><?php echo $db->site_name; ?></h1>
        </div>
        <div id="loader_area" style="display: none;"><?php loader(); ?></div>
        <div class="controls" id="login_body">
          <div id="error_msg" class="error_msg" style="color: #F64343; display: none;">
            <span class="glyphicon glyphicon-remove error_icon"></span><br/>
            <span id="error_msg_text"></span>
          </div>

          <div class="input-container">
            <i class="fa fa-user icon"></i>
            <input class="input-field" autocomplete="off" type="text" placeholder="Username" id="uname" name="uname">
          </div>
          <div class="input-container">
            <i class="fa fa-key icon"></i>
            <input class="input-field" type="password" id="pass" name="pass" placeholder="Password">
          </div>

          <button type="submit" style="font-size: 16px;" id="login_btn" onclick="login()" name="login" class="btn btn-default btn-block btn-custom">Login</button> 
        </div>
        <div class="footer_login" style="">
          Developed By: 
          <a href="https://github.com/amirhamza05/Student-Management-System">
            <font style="font-size: 19px; font-weight: bold; color: #F64343">Zain Rauf</font>
          </a>
        </div>
      </div>
    </div>
  </div>
  <div id="particles-js"></div>
</div>

<?php
// Loader function
function loader() {
?>
<center>  
  <div class="lds-css ng-scope">
    <div style="width:100%;height:100%" class="lds-ellipsis">
      <div><div></div></div>
      <div><div></div></div>
      <div><div></div></div>
      <div><div></div></div>
      <div><div></div></div>
    </div>
  </div>
</center>
<?php
}
?>

<style type="text/css">
  /* Loader styles here */
  /* (same CSS content as before) */
  @keyframes lds-ellipsis3 {
    0%, 25% { left: 32px; transform: scale(0); }
    50% { left: 32px; transform: scale(1); }
    75% { left: 100px; }
    100% { left: 168px; transform: scale(1); }
  }
  /* (repeat for -webkit prefixes and other keyframes) */
  .lds-ellipsis { position: relative; }
  .lds-ellipsis > div { position: absolute; transform: translate(-50%, -50%); width: 44px; height: 44px; }
  .lds-ellipsis div > div {
    width: 44px; height: 44px; border-radius: 50%; background: #f00; position: absolute; top: 100px; left: 32px;
    animation: lds-ellipsis 2s cubic-bezier(0, 0.5, 0.5, 1) infinite forwards;
  }
  /* (rest of CSS same as before) */
</style>

<?php
// End output buffering
ob_end_flush();
?>
