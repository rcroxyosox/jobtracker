<?php
session_start();
require_once("db.class.php/db.class.php");
require_once("util.php");
$DB = new DB();

/* debug 
$fp = array(
	'leadid' => 25,
	'loggedid' => 7,
	'torepid' => 10
);
*/

$fp = array(
	'leadid' => $_REQUEST['leadid'],
	'loggedid' => $_SESSION['loggedin'],
	'torepid' => $_REQUEST['torepid']
);

// get who was copied
function getCopied($cccorrid, $corrid){
	global $DB;
	global $fp;
	
	if($cccorrid > 0){
		
		$sql = "SELECT torepid FROM corr 
						WHERE cccorrid = ".$cccorrid;
		$sqlto = "SELECT torepid FROM corr WHERE id = ".$cccorrid;
		
	}else{
		$sql = "SELECT torepid FROM corr 
						WHERE cccorrid = ".$corrid; 
		$sqlto = "SELECT torepid FROM corr WHERE id = ".$corrid;
	}
	
	$res = $DB->query($sql);
	$resto = $DB->queryUniqueValue($sqlto);
	$ret['to'] = getRep($resto, true);
	
	while($r = $DB->fetchNextObject($res)){
		$ret['cc'][]= "CC ".getRep($r->torepid, true);
	}
	
	if(count($ret['cc']) > 0){
		asort($ret['cc']);
	}
	return $ret;
}


// get the comments
function getCorrAsJSON(){
	
	global $DB;
	global $fp;
	
	// mark as read
	$DB->query("UPDATE corr SET 
						  hasbeenread = 1 
							WHERE torepid = ".$fp['loggedid']."
							AND fromrepid = ".$fp['torepid']."
							AND leadid=".$fp['leadid']);
	
	$ret = '{corr:[';
	$sql = "SELECT *,
					DATE(postdate) as postdate,
					DATE_FORMAT(postdate, '%l:%i %p') as posttime
					FROM corr 
					WHERE leadid=".$fp['leadid']." 
					AND (fromrepid = ".$fp['loggedid']." OR torepid = ".$fp['loggedid'].")
					AND (fromrepid = ".$fp['torepid']." OR torepid = ".$fp['torepid'].")
					ORDER BY DATE(postdate) DESC, TIME(postdate) DESC";
					
	$res = $DB->query($sql);
	$e = mysql_error();
	
	$retarr = array();
	
	if(mysql_num_rows($res)){
		while($r = $DB->fetchNextObject($res)){
			
			$date = ($r->postdate == date('Y-m-d'))?'':dateFix($r->postdate);
			$class = ($r->fromrepid == $_SESSION['loggedin'])?"loggedinposts":"";
			$tofrom = getCopied($r->cccorrid, $r->id);
			$ccd = (count($tofrom['cc']))?", ".implode(", ",$tofrom['cc']):'';
			
			$retarr []= '{"id":"'.$r->id.'", 
										"addclass":"'.$class.'",
										"message":"'.$r->message.'",
										"postdate":"'.$date.'",
										"posttime":"'.$r->posttime.'",
										"whotowho":"'.who($r->fromrepid).' said to '.$tofrom['to'].$ccd.'",
										"e":"'.$e.'"
									 }';
		}
	}else{
			/**/
			$retarr []= '{"id":"0", 
										"addclass":"nomessages",
										"message":"no messages",
										"postdate":"",
										"posttime":"",
										"whotowho":"",
										"e":""
									 }';
									 
			
	}
	
	$ret .= implode(",",$retarr);
	$ret .= ']}';
	
	print $ret;
}

getCorrAsJSON();

?>