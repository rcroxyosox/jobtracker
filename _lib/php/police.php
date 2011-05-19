<?php
session_start();
require('db.class.php/db.class.php');
$DB = new DB();


function getAddress(){
  /*** check for https ***/
  $protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
  /*** return the full address ***/
  return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}


if(isset($_REQUEST['logout'])){
	unset($_SESSION['loggedin']);
}

if(!isset($_SESSION['loggedin']) || strlen(trim($_SESSION['loggedin'])) == 0){
	$_SESSION['referer'] = getAddress();
	header('Location: /riot/jobtracker/login.php');
}

?>