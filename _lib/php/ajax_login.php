<?php
session_start();
require('db.class.php/db.class.php');
$DB = new DB();

function login(){
global $DB;
$u = trim($_REQUEST['username']);
$p = trim($_REQUEST['password']);

$sql = "SELECT id FROM reps 
				WHERE username = '".$DB->safe($u)."' 
				AND password = '".$DB->safe($p)."'";

	$res = $DB->query($sql);
	
	if($DB->numRows($res) && strlen($p) && strlen($u)){
		
		$id = $id = $DB->queryUniqueValue($sql);
		$_SESSION['loggedin'] = $id;
		print '1';
	}else{
		print '0:Bad credentials. Please try again.';
	}

}

if(isset($_REQUEST['username']) && isset($_REQUEST['password'])){
	login();
}else{
	print '0:Fill out all fields';	
}

?>