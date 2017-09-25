<?php
ob_start();
session_start();
//  This header.php file is included in the 3 main web pages 
//  Sets up all the <head> information including
//  meta tags, bootstrap links, and local js/css/img files

echo "
<!DOCTYPE html>
<html lang='en'>
<head>";

//  find out if user is logged in or out
//  if logged in the header will include users name and a log out button
//  if looged out it will include a log-in area

require_once 'functions.php';
$link_address1 = 'index.php';

$userstr = ' (Guest)';
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $loggedin = TRUE;
    $userstr = " ($user)";
} else {
	$loggedin = FALSE;
}	

echo "
<title>Football Predictor$userstr</title>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<meta name='description' content='Predict premier league scores. Create your own league and challenge friends'>
<meta name='keywords' content='Football, Predictions, Premier League'>
<meta name='author' content='Mkoijn'>
<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>
<script src='https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js'></script>
<script type='text/javascript' src='js/safariValidation.js'></script>
<link rel='stylesheet' type='text/css' href='//fonts.googleapis.com/css?family=Ubuntu'>
<link href='css/football.css' rel='stylesheet' type='text/css'>
<link rel='icon' href='img/f2.png' sizes='16x16' type='image/png'>
</head>
<body>
<!--Header for index, member and leagues pages-->";

if ($loggedin)
echo "
<div class='container-fluid'>
  <div class='row' style='background-color:#1a1a1a; color:white; padding:10px 70px 10px 70px'>
    <div class='col-sm-6 text-left'>
	  <h1><a href='".$link_address1."' style='text-decoration:none; color:white'>Premier League Predictor</a></h1>
    </div>
    <div class='col-sm-6 text-right' style='margin-top:40px'>
      <form method='post' action='logout.php' class='form-inline'>
	    Hello $user&nbsp;&nbsp;
        <button type='submit' class='btn btn-primary'>Log Out</button>
      </form>
    </div>
  </div>
</div>";
else
echo "
<div class='container-fluid'>
  <div class='row' style='background-color:#1a1a1a; color:white; padding:10px 70px 10px 70px;'>   
    <div class='col-sm-6 text-left'>
	  <h1><a href='".$link_address1."' style='text-decoration:none; color:white'>Premier League Predictor</a></h1>
    </div>    
    <div class='col-sm-6 text-right' style='margin-top:40px'>
	  <form method='post' action='index.php' id='formID' class='form-inline'>
        <div class='form-group'>
          <input type='text' class='form-control' size='10' name='userlog' placeholder='Username' required>
        </div>
	    <div class='form-group'>
          <input type='password' class='form-control' size='10' name='passlog' placeholder='Password' required>
        </div>     
          <button type='submit' class='btn btn-primary'>Log In</button>
      </form>
    </div>     
  </div>
</div>
	";
?>