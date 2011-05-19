<?php


require('db.class.php/db.class.php');
$DB = new DB();
$uploadDir = '../../quotes/';

if(isset($_REQUEST['id'])){
	$sql = "SELECT quotefile FROM leads WHERE id = ".$_REQUEST['id'];
	$res = $DB->queryUniqueValue($sql, $debug = -1);
	if(strlen($res) > 0){
		print '1:'.stripslashes($res);
	}else{
		print '0:No quote file is associated with this prospect';	
	}
}else{
	print '0:ID was not set';	
}

?>