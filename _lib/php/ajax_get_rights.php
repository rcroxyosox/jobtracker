<?php
session_start();
require_once("db.class.php/db.class.php");
$DB = new DB();

// get the rights of the person logged in
// will return a number 1 - 3 or null
function getRights(){
	global $DB;
	$lsql = "SELECT userlevel FROM reps WHERE id=".$_SESSION['loggedin'];
	$level = $DB->queryUniqueValue($lsql);
	
	$csql = "SELECT createdby_repid FROM leads WHERE id = ".$_REQUEST['id'];
	$created_by = $DB->queryUniqueValue($csql);
	
	//$created_by == $_SESSION['loggedin']
	if($level == "1"){
		$ret = "1:full";
	}else{
		$ret = "1:restricted";	
	}
	return $ret;
}

if(isset($_REQUEST['id'])){
	print getRights();
}

?>