<?php
session_start();
require_once("db.class.php/db.class.php");
require_once("util.php");
$DB = new DB();
$quotesfolder = '../../quotes/';

function checkQuoteFile($qf, $link = false){
	global $quotesfolder;
	$ret = '';
	if(strlen($qf) > 0 && is_file($quotesfolder.$qf)){
		
		if($link){
			$ret = '<a href=\"_lib/php/forcedownload.php?filename='.$qf.'\">Download</a>';
		}else{
			$ret = '1';
		}
	
	}
	return $ret;
}

// get the leadinfo
function getLeadInfo(){
	
	global $DB;
	
	// mark as read
	$r = $DB->queryUniqueObject("SELECT * FROM leads WHERE id = ".$_REQUEST['leadid']);
	$ret = '{			 
								"jobname":"'.$r->jobname.'",
								"company":"'.$r->company.'",
								"customer":"'.$r->customer.'",
								"repid":"'.$r->rep.'",
								"rep":"'.getRep($r->rep).'",
								"created by":"'.getRep($r->createdby_repid).'",
								"est. $":"$'.$r->estimated.'",
								"joanlead":"'.$r->joanload.'",
								"comments":"'.nonl($r->comments).'",
								"targetdate":"'.dateFix($r->targetdate).'",
								"dateclosed":"'.dateFix($r->dateclosed).'",
								"status":"'.$r->status.'",
								"reason":"'.nonl($r->reason).'",
								"reason given by":"'.getRep($r->reason_repid).'",
								"createdon":"'.dateFix($r->createdon).'",
								"lastupdated":"'.dateFix($r->lastupdated).'",
								"last updated by":"'.getRep($r->lastupdatedby_repid).'",
								"quotefilestr":"'.checkQuoteFile($r->quotefile).'",
								"quotefile":"'.checkQuoteFile($r->quotefile, true).'",
								"id":"'.$r->id.'"
					}';
									 
	
	print $ret;
}

getLeadInfo();

?>