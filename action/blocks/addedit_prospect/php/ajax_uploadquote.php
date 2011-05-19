<?php
session_start();

require('../../../../_lib/php/fileUpload.php');
require_once('../../../../_lib/php/db.class.php/db.class.php');
$DB = new DB();

$uploadDir = '../../../../quotes/';

$name_prefix = (isset($_REQUEST['company']))?$_REQUEST['company']:'riotquote';
$name_prefix = stripslashes(str_replace(' ','',$name_prefix)); // remove whitespace chars

$rename = $name_prefix.'_'.date('mdy').'_'.date('His', time());


$fileUpload = new FileUpload();
$fileUpload->setUploadedFile($_FILES['quotefile']);
$fileUpload->setUploadDir($uploadDir);
$fileUpload->setRenameFileTo($rename);
$fileUpload->setAllowedExts(array('docx', 'doc', 'txt', 'xls', 'xlsx', 'pdf'));
$fileUpload->uploadFile();
$link = $fileUpload->getRenameFileTo();

if(isset($_REQUEST['e']) && isset($_REQUEST['company'])){
	$sql = "UPDATE leads SET quotefile = '".addslashes($link)."' WHERE id = ".$_REQUEST['e'];
	$DB->query($sql);
}

function getAllowedExts(){
	global $fileUpload;
	$ret = '';
	$ret = $fileUpload->getImploded(', ', $fileUpload->getallowedExts());
	print $ret;
}

function uploadFile(){
	global $fileUpload;
	global $uploadDir;
	global $link;
	
	if(!$fileUpload->hasErrors()){
	
	
		if(isset($_FILES['quotefile']) && is_file($uploadDir.$link)){
			
			$_SESSION['startedfile'] = $link;
			print '1:'.$link;
		}else{
			print '0: Your file size may be too big';	
		}
	
	}else{
		print '0:'.implode(', ', $fileUpload->getErrors());
		
		/*
		print '<pre>';
		print_r($fileUpload->uploadErrors());
		print '</pre>';
		*/
	}
}

if(isset($_REQUEST['getexts'])){
	getAllowedExts();
}else{
	uploadFile();
}



?>