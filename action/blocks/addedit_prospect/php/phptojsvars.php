<?php

$vars = array(
			  
'uploadsDir' => $uploadDir,
'eid' => $_REQUEST['e'],
'startedfile' => $_SESSION['startedfile'],
'loggedin' => $_SESSION['loggedin']
);

$ret = '';
foreach($vars as $k => $v){
	$ret .= "\t".'var '.$k.' = "'.$v.'";'."\n";
}

print $ret;

?>