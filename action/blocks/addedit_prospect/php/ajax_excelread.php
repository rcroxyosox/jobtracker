<?php
require_once ('../../../../_lib/php/excelRead/Excel/reader.php');
$uploadDir = '../../../../quotes/';

$data = new Spreadsheet_Excel_Reader();
$file = $_REQUEST['file'];

$filename = $uploadDir.$file;

// debug
//$filename = 'excelRead/test.xls';

// as to not screw up the folders
function get_alpha($string){
     $new_string = ereg_replace("[^A-Za-z0-9 ]", "", $string);
     return $new_string;
}


if(is_file($filename)){
	$data->read($filename);
	$t = $data->sheets[0]['cells'];
	
	// y axis, x axis
	$customername = $t[2][2];
	$companyname = $t[3][2];
	
	// get the estimated amount
	$total = 0;
	for($i = 1; $i<$data->sheets[0]['numRows']; $i++){
		if($t[$i][5] == "other"){break;}
		$total += $t[$i][8];
	
	}
	$total =  round($total, 2);
	
	$json = '
	{
	  "company": "'.get_alpha($companyname).'",
	  "customer": "'.get_alpha($customername).'",
	  "estimated": "'.$total.'"
	}';
	
	print $json;
}else{
	print'0:File not found';	
}



?>