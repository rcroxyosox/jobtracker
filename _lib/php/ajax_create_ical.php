<?php
session_start();

require_once('iCalcreator-2.6/iCalcreator.class.php');
require_once('db.class.php/db.class.php');
$DB = new DB();

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

// get rep info give a rep id number
function getRepInfo($field, $repid){
	global $DB;
	$sql = "SELECT ".$field." FROM reps WHERE id = ".$repid;
	$res = $DB->query($sql);
	$r = mysql_fetch_row($res);
	return $r[0];
}

//get info about the lead
function getLeadInfo($field, $leadid = ''){
	global $DB;
	
	$leadid = (strlen($leadid) == 0)?$_REQUEST['id']:$leadid;
	
	$sql = "SELECT ".$field." FROM leads WHERE id = ".$leadid;
	$res = $DB->query($sql);
	$r = mysql_fetch_row($res);
	return stripslashes($r[0]);	
}


// get the item description
function getItemDesc(){
	global $DB;
	$sql = "SELECT * FROM leads WHERE id = ".$_REQUEST['id'];
	$res = $DB->queryUniqueObject($sql);
	
	$ret = "Prospect info:\n\n";
	
	$ret .= 'jobname: '.stripslashes($res->jobname)."\n";
	$ret .= 'company: '.stripslashes($res->company)."\n";
	$ret .= 'customer name: '.stripslashes($res->customer)."\n";
	$ret .= 'target close date: '.dateFix($res->targetdate)."\n";
	$ret .= 'date closed: '.dateFix($res->dateclosed)."\n";
	$ret .= 'joan lead: '.$res->joanlead."\n";
	$ret .= 'rep: '.getRepInfo('CONCAT(firstname, \' \', lastname)', $res->rep).'('.getRepInfo('email', $res->rep).')'."\n";
	$ret .= 'estimated $: $'.round($res->estimated, 2)."\n";
	
	return $ret;
	
}


function getLoggedEmail(){
	global $DB;
	$sql = "SELECT email FROM reps WHERE id = ".$_SESSION['loggedin'];
	$email = $DB->queryUniqueValue($csql);
	return $email;
}



$v = new vcalendar();
  // create a new calendar instance
$v->setConfig( 'unique_id', 'icaldomain.com' );
  // set Your unique id

$v->setProperty( 'method', 'PUBLISH' );
  // required of some calendar software
$v->setProperty( "x-wr-calname", "Calendar Sample" );
  // required of some calendar software
$v->setProperty( "X-WR-CALDESC", "Calendar Description" );
  // required of some calendar software
$v->setProperty( "X-WR-TIMEZONE", "Europe/Stockholm" );
  // required of some calendar software

/**/
$vevent = new vevent();
  // create an event calendar component
$start = array( 'year'=>getLeadInfo("YEAR(targetdate)"), 'month'=>getLeadInfo("MONTH(targetdate)"), 'day'=>getLeadInfo("DAY(targetdate)"));
$vevent->setProperty( 'dtstart', $start );
//$end = array( 'year'=>2007, 'month'=>4, 'day'=>1, 'hour'=>22, 'min'=>30, 'sec'=>0 );
//$vevent->setProperty( 'dtend', $end );
$vevent->setProperty( 'LOCATION', 'Central Placa' );
  // property name - case independent
$vevent->setProperty( 'summary', getLeadInfo("jobname") );
$vevent->setProperty( 'description', getItemDesc() );
$vevent->setProperty( 'comment', 'This \n\n is a comment' );
//$vevent->setProperty( 'attendee', 'attendee1@icaldomain.net' );
$v->setComponent ( $vevent );
  // add event to calendar


/*
$vevent = new vevent();
$vevent->setProperty( 'dtstart', '20070401', array('VALUE' => 'DATE'));
  // alt. date format, now for an all-day event
$vevent->setProperty( "organizer" , 'boss@icaldomain.com' );
$vevent->setProperty( 'summary', 'ALL-DAY event' );
$vevent->setProperty( 'description', 'This is a description for an all-day event' );
$vevent->setProperty( 'resources', 'COMPUTER PROJECTOR' );
$vevent->setProperty( 'rrule', array( 'FREQ' => 'WEEKLY', 'count' => 4));
  // weekly, four occasions
$vevent->parse( 'LOCATION:1CP Conference Room 4350' );
  // supporting parse of strict rfc2445 formatted text
$v->setComponent ( $vevent );
  // add event to calendar
*/


  // all calendar components are described in rfc2445
  // a complete iCalcreator function list (ex. setProperty) in iCalcreator manual

$v->returnCalendar();
  // redirect calendar file to browser


?>