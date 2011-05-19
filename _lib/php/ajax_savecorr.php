<?php
session_start();
require_once('db.class.php/db.class.php');
require_once('PHPMailer_v5.1/class.phpmailer.php');
require_once('version.php');
require_once('util.php');
$quotesFolder = '../../quotes/';

$DB = new DB();
$mail = new phpmailer();
$mail->IsHTML(true);
$mail->FromName = getRep($_SESSION['loggedin'], true); 
$mail->Subject =  getRep($_SESSION['loggedin'], true)." sent you a message from the ".$version['name'];
$mail->Host = "relay-hosting.secureserver.net";

function attachQuoteFile(){
	global $quotesFolder;
	global $mail;
	$quotefile = $quotesFolder.getLeadField($_REQUEST['leadid'],'quotefile');
	if(is_file($quotefile) && $_REQUEST['attachments'] == "1"){
		$mail->AddAttachment($quotefile);
	}else{
		print 'No attachment:'.$quotefile;
	}
}


function getCorrField($field, $cccorrid){
	global $DB;
	$fv = $DB->queryUniqueValue("SELECT ".$field." FROM corr WHERE id = ".$cccorrid);
	return $fv;
}

function sendEmails($torepid, $cccorrid){

	global $mail;	
	global $version;
	
	$server = ($_SERVER['REMOTE_ADDR'] == '::1')
							?'http://localhost/riot/jobtracker/'
							:'http://rcone.net46.net/riot/jobtracker/'; 
	
	$body = '<body topmargin="5%">';
	
	$body .= '<table width="440" align="center" cellpadding="14" cellspacing="0">';
	$body .= '<tr><td align="left">';
	$body .= '<font color="#ef3f24" size="5" face="Arial, Helvetica, sans-serif">
						<b>'.$version['name'].'</b>
						</font>';
	$body .= '</td></tr>';
	$body .= '</table>';	
	
	$body .= '<table width="440" align="center" cellpadding="14" cellspacing="0"">';
	$body .= '<tr><td align="left" style="border: 4px solid #f1f1f1;">';
	$body .= '<font color="#80c779" size="3" face="Arial, Helvetica, sans-serif">
						<b>You have a new message from 
						'.getRep($_SESSION['loggedin'], true).'</b><br /> 
						</font>
						<font color="#000000" size="3" face="Arial, Helvetica, sans-serif">
						<br />"'.stripslashes(getCorrField('message', $cccorrid)).'"
						</font>
						<font color="#000000" size="2" face="Arial, Helvetica, sans-serif">
						<br /><br />
						Click the link below to view and respond to your message from 
						'.getRep($_SESSION['loggedin'], true).'. 
						All correspondance reguarding this job should take place within the '.$version['name'].'</font>';
	$body .= '<br /><br /><br />';
	$body .= '<font face="Arial, Helvetica, sans-serif" size="1">';
	$body .= '<a href="'.$server.'index.php
						?bypassid='.$torepid.'
						&leadid='.$_REQUEST['leadid'].'
						&repcorr='.$_SESSION['loggedin'].'
						&remoteentry=1" style="color: #FFFFFF; 
																	 background-color: #ef3f24; 
																	 padding:10px 30px; 
																	 text-decoration: none">Click here to respond</a>';
	$body .= '</font>';
	$body .= '<br /><br />';
	$body .= '</td></tr>';
	$body .= '<tr><td>';
	$body .= '<font size="1" color="#dedede"  face="Arial, Helvetica, sans-serif">
						NOTICE: This e-mail and any attachment contain confidential information that may be legally privileged. If you are not the intended recipient, you must not review, retransmit, print, copy use or disseminate it. Please immediately notify us by return e-mail and delete it. Riot Creative Imaging is a division of ARC
						</font>';
	$body .= '</td></tr>';
	$body .= '</table>';
	$body .= '</body>';
	
	$mail->Body = $body;
	

	attachQuoteFile();
	
	//debug
	//$mail->AddAddress("rob@tridig.com","Robert Cox");	
	$mail->AddAddress(getRepField($torepid, "email"),getRep($torepid));
	
	if(!$mail->Send()){
		print '0:Could not send email to '.$torepid;
	}else{
		print '1:Email sent to '.getRep($torepid, true).' ('.getRepField($torepid, "email").')';
	}
	
	$mail->ClearAddresses();
}


/**/
function saveCorr($torepid, $cccorrid = 0){
	global $DB;
	
	$message = nonl($_REQUEST['message']);
	
	$sql = "INSERT INTO corr SET 
						message = '".$DB->safe($message)."',
						postdate = CURRENT_TIMESTAMP,
						fromrepid = ".$DB->safe($_SESSION['loggedin']).",
						torepid = ".$DB->safe($torepid).",
						leadid = ".$DB->safe($_REQUEST['leadid']).",
						cccorrid = ".$cccorrid;

	if($DB->query($sql)){
		print "1:saved";
	}else{
		print("0:Oops, there was an error saving this ".mysql_error());
	}

}

saveCorr($_REQUEST['torepid']);
$parentid = $DB->lastInsertedId();
sendEmails($_REQUEST['torepid'], $parentid);

// get the rep ids
$repids = array();
$reps = explode(", ", $_REQUEST['repnames']);
foreach($reps as $v){
	$name_e = explode(" ", $v);
	$repid = $DB->queryUniqueValue("SELECT id FROM reps 
					   				WHERE firstname = '".$name_e[0]."' 
					   				AND lastname = '".$name_e[1]."'");
	if(strlen(trim($repid))) $repids[] = $repid;
}

print_r($repids);

if(count($repids) > 0){
	
	foreach($repids as $v){
		saveCorr($v, $parentid);
		sendEmails($v, $parentid);
	}
}


?>