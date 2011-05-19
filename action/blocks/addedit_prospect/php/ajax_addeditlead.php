<?php

session_start();
require('../../../../_lib/php/util.php');

$uploadDir = '../../../../quotes/';

// the file
$tempfile = $uploadDir.stripslashes($_REQUEST['hfname']);
$i = pathinfo($tempfile);
$coname = $_REQUEST['company'];
$coname = stripslashes(str_replace(' ','',$coname)); // remove whitespace chars
$newname = $coname.'_'.date('mdy').'_'.date('His', time()).'.'.$i['extension'];

//die($tempfile);


// change the file name
function changeFileName(){

	global $newname;
	global $tempfile;
	global $uploadDir;
	
	//die($tempfile."---".$uploadDir.$newname);
	
	
	if(!isset($_SESSION['startedfile'])){
		return '';
	}
	
	$ret = '';
	
	if(is_file($tempfile)){
		
			rename($tempfile, $uploadDir.$newname) or die('0:could not rename file');
			return $newname;

	}else{
		die('0:'.$tempfile.' is not a file');
	}
	
}

// the query
function addLead(){
	
	global $DB;
	global $newname;
		
	// if its new
	if(!isset($_REQUEST['e'])){
	
		// the created by
		$createdby = 'createdby_repid = '.$_SESSION['loggedin'].', ';

		// created on
		$createdon = 'createdon = CURDATE(), ';
		
		// update & where
		$insertOrUpdate = 'INSERT INTO leads SET ';
		$where = '';
		
	}else{
		
		// insert & where
		$insertOrUpdate = 'UPDATE leads SET ';
		$where = ' WHERE id = '.$_REQUEST['e'];
		
		$createdby = '';
		$createdon = '';
	}
	
	$reason_repid = isset($_REQUEST['reason_repid'])?$DB->safe($_REQUEST['reason_repid']):0;


	$sql = $insertOrUpdate.
		   "jobname = '".$DB->safe($_REQUEST['jobname'])."',
		   	quotefile = '".$DB->safe(addslashes(changeFileName()))."', 
				company = '".$DB->safe($_REQUEST['company'])."',
				customer = '".$DB->safe($_REQUEST['customer'])."',
				targetdate = '".$DB->safe(dateFixSQL($_REQUEST['targetdate']))."',
				dateclosed = '".$DB->safe(dateFixSQL($_REQUEST['dateclosed']))."',
				rep = '".$DB->safe($_REQUEST['rep'])."',
				estimated = '".$DB->safe($_REQUEST['estimated'])."',
				quotedby_repid = ".$DB->safe($_REQUEST['quotedby_repid']).",
				status = '".$DB->safe($_REQUEST['status'])."',
				reason = '".$_REQUEST['reason']."',
				reason_repid = ".$reason_repid.",
				lastupdated = CURDATE(),
				lastupdatedby_repid = ".$_SESSION['loggedin'].",
			".$createdby."
			".$createdon."
			comments = '".$DB->safe($_REQUEST['comments'])."'"
			.$where;
	
	if($DB->query($sql)){
		
		$editIt = (isset($_REQUEST['e']))?$_REQUEST['e']:$DB->lastInsertedId();
		
		unset($_SESSION['startedfile']);
		print '1:'.$editIt;
		/*
		print '<pre>';
		print_r($_REQUEST);
		print '</pre>';
		*/
	}else{
		print '0:'.mysql_error();	
	}
}
addLead();

?>