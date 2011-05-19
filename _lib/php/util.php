<?php

session_start();
require_once('db.class.php/db.class.php');
$DB = new DB();

// get rid of new lines
function nonl($str, $addbr = true){
	$str = ($addbr)?nl2br($str):$str;
	return str_replace(array("\n","\t","\r"), "", $str);
}


// allow the ability to bypass the login
function byPassLoginOkay(){
	global $DB;
	$ret = false;
	
	if(isset($_REQUEST['bypassid'])){
			global $DB;
			
			$sql = "SELECT id FROM reps WHERE id = ".$_REQUEST['bypassid'];
			
			$res = $DB->query($sql);
				
			if($DB->numRows($res)){
				$id = $id = $DB->queryUniqueValue($sql);
				$_SESSION['loggedin'] = $id;
				$ret = true;
			}
		
	}
	return $ret;
}
byPassLoginOkay();

// add whitespce if there is not a val
function ws($val){
	return (strlen($val) > 0)?$val:'&nbsp;';
}

// get a field from the reps table
function getRepField($repid, $field, $noval = '<span class="noval">n/a</span>'){
	global $DB;
	return ($repid)?$DB->queryUniqueValue("SELECT ".$field." FROM reps WHERE id = ".$repid):$noval;
}

// get a field from the leads table
function getLeadField($leadid, $field){
	global $DB;
	return $DB->queryUniqueValue("SELECT ".$field." FROM leads WHERE id = ".$leadid);
}

// get the rep name
// get the rep and include a link to info
function getRep($repid, $onlyfirst = false){
	global $DB;
	
	$rep =($onlyfirst)
				?getRepField($repid, "firstname")
				:getRepField($repid, "CONCAT(firstname,' ', lastname)");
	
	return $rep;
}

// replace your own id with the word you
function who($repid){
	if($repid == $_SESSION['loggedin']){
		return "You";
	}else{
		return getRep($repid, true);
	}
}


// gets the val of a field either from the db
// or from the $_REQUEST global vals
function getVal($field){
	global $DB;
	
	if(isset($_REQUEST['e'])){
		$sql = 'SELECT '.$field.' FROM leads WHERE id = '.$_REQUEST['e'];
		$ret = $DB->queryUniqueValue($sql);
	}else{
		$ret = 	(isset($_SESSION[$field]))?$_SESSION[$field]:$_REQUEST[$field];
	}
	$ret = str_replace('"','\'', $ret);
	return stripslashes($ret);
}


// get the reps in a dropdown menu
function getRepsDrop($id = 'rep', $notpersonlogged = false){
	global $DB;
	
	$where = ($notpersonlogged)?'id != '.$_SESSION['loggedin'].' AND userlevel != ""':'1';
	
	
	$sql = "SELECT * FROM reps WHERE ".$where." ORDER BY firstname";
	$res = $DB->query($sql);
	
	$ret = '<select name="'.$id.'" id="'.$id.'" class="input">';
	while($r = $DB->fetchNextObject($res)){
		
		if($r->id == getVal($id)){ // if its from the logged in user
			$selected = 'selected="selected"';
		
		}elseif($r->id == $_SESSION['loggedin'] && !isset($_REQUEST['e'])){ // if its from the db of the global vars
			$selected = 'selected="selected"';
		
		}elseif($_REQUEST[$id] == $r->id){ // if its from the global vars
			$selected = 'selected="selected"';
		
		}else{
			$selected = '';	
		}
			
		$ret .= '<option value="'.$r->id.'" '.$selected.'>'.$r->firstname.' '.$r->lastname.'</option>';
		
	}
	$ret .= '</select>';
	return $ret;
}

// for the check boxes
function intToStr($int){
	$ret = '';
	if($int == '1'){
		$ret = 'checked="checked"';
	}
	return $ret;
}


// for the check boxes
function strToInt($val){
	$ret = 0;
	if($val == 'on' || $val == 'yes' || $val === true || $val == 'y'){
		$ret = 1;
	}
	return $ret;
}

// for the joan X
function intToChar($int){
	return ($int == '1')?'<span class="inttochar">Y</span>':'';
}

// for the dates
function dateFix($baddate){
	
	if($baddate == '0000-00-00' || $baddate == '' || $baddate == NULL){
		$good = '';
	}else{
		$bad = strtotime($baddate);
    	$good = date('m/d/Y', $bad);
	}
	
	return $good;
}

// for the dates going into SQL
function dateFixSQL($baddate){
	
	if($baddate == '0000-00-00' || $baddate == '' || $baddate == NULL){
		$good = '0000-00-00';
	}else{
		$bad = strtotime($baddate);
    	$good = date('Y-m-d', $bad);
	}
	
	return $good;
}
?>