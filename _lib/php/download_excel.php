<?php
require_once('db.class.php/db.class.php');

$DB = new DB();

// Export to CSV
	$server = ($_SERVER['REMOTE_ADDR'] == '::1')
							?'http://localhost/riot/jobtracker/'
							:'http://rcone.net46.net/riot/jobtracker/'; 	
	
	$where = (isset($_REQUEST['where']) && strlen($_REQUEST['where']) > 0)
			 	?stripslashes($_REQUEST['where'])
			 	:"1";
		
	$orderby = (isset($_REQUEST['orderby']) && strlen($_REQUEST['orderby']) > 0)
				?' ORDER BY leads.'.$_REQUEST['orderby'].' '
				:'';
				
	$sql = "SELECT leads.jobname, 
				   leads.company, 
				   leads.customer, 
				   leads.targetdate,
				   leads.dateclosed,
				   leads.joanlead,
				   CONCAT(reps.firstname, ' ', reps.lastname),
				   CONCAT('$',estimated) as estimated, 
				   CONCAT('".$server."quotes/',leads.quotefile)
			FROM leads 
			LEFT JOIN reps ON leads.rep = reps.id  
			WHERE "
			.$where
			.$orderby;
			
	$res = $DB->query($sql);
	
	$out = '';
	$fields = array('Job Name', 
					'Company Name',
					'Customer Name',
					'Target Close Date',
					'Date Closed',
					'Joan?',
					'Rep',
					'Estimated $',
					'Quotefile');
	$columns = count($fields);
	
	// Put the name of all fields
	for ($i = 0; $i < $columns; $i++) {
	$l= $fields[$i];
	$out .= '"'.$l.'",';
	}
	$out .="\n";
	
	// Add all values in the table
	while ($r = mysql_fetch_row($res)) {
		for ($i = 0; $i < $columns; $i++) {
			$out .='"'.stripslashes($r["$i"]).'",';
		}
		$out .="\n";
	}
	
	//die($sql);
	//print $out;
	/**/
	// Output to browser with appropriate mime type, you choose ;)
	header("Content-type: text/x-csv");
	//header("Content-type: text/csv");
	//header("Content-type: application/csv");
	header("Content-Disposition: attachment; filename=ppqt_export_".date('mdY').".csv");
	echo $out;
	exit;
	

?>