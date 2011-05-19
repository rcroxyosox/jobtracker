<?php

require_once 'Excel/reader.php';
$data = new Spreadsheet_Excel_Reader();

$data->read('test.xls');

error_reporting(E_ALL ^ E_NOTICE);

$t = $data->sheets[0]['cells'];

// x axis, y axis


for($i = 1; $i<$data->sheets[0]['numRows']; $i++){
	
	/*
	for($k = 1; $k<$data->sheets[0]['numCols']; $k++){
		print 'cord: '.$i.'-'.$k.': '.$t[$i][$k].'<br />';	
	}
	*/
	print 'cord: '.$i.': '.$t[$i][8].'<br />';
		$v += $t[$i][8];
	
}
print $v;

$total = $t[10][8];


echo "<table border='1'>";
echo "<tr><th>First Name</th><th>Middle Name</th><th>Last Name</th><th>Email ID</th></tr>";


for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++)
		 {
echo "<tr>";
		
		
echo "<td>";
		echo $data->sheets[0]['cells'][$j+1][1];
echo "</td>";	
echo "<td>";	
		echo $data->sheets[0]['cells'][$j+1][2];
echo "</td>";

echo "<td>";
	
		echo $data->sheets[0]['cells'][$j+1][3];
echo "</td>";

echo "<td>";

		echo $data->sheets[0]['cells'][$j+1][4];
echo "</td>";
		//echo "<br>";

echo "</tr>";
		
		}

echo "</table>";
	
?>
