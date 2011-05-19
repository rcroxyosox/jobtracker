<?php
session_start();
$uploadDir = '../../../../quotes/';
require_once('../../../../_lib/php/db.class.php/db.class.php');
$DB = new DB();

$file = $uploadDir.$_REQUEST['uploadedfile'];

if(strlen($_REQUEST['e']) > 0){
	
	$sql = "UPDATE leads SET quotefile = '' WHERE id = ".$_REQUEST['e'];
	$DB->query($sql);
	unset($_SESSION['startedfile']);
	
}


if(is_file($file)){
	if(unlink($file)){
		unset($_SESSION['startedfile']);
		print '1';
	}else{
		print '0';	
	}
}else{
	unset($_SESSION['startedfile']);
	print '1';
}

?>