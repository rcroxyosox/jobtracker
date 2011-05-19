<?php
require('db.class.php/db.class.php');
$DB = new DB();
$uploadDir = '../../quotes/';


function removeLead(){
global $DB;
	$sql = 'DELETE FROM leads WHERE id = '.$_REQUEST['id'];
	
	$DB->query($sql);
	if(mysql_affected_rows()){
		print '1:';
	}else{
		print '0:'.mysql_error();	
	}

}


removeLead();

?>