<?php

session_start();
unset($_SESSION['startedfile']);

require_once("_lib/php/police.php");
require_once("_lib/php/util.php");
include("_lib/php/version.php");
include("_lib/php/ajax_get_rights.php");

$numCols = 12;
$statuses = array('pending','lost','closed');


if(isset($_REQUEST['remoteentry'])){
	header('Location: index.php?repcorr='.$_REQUEST['repcorr'].'#corrid='.$_REQUEST['leadid']);
}

/*
print '<pre>';
print_r($_REQUEST);
print '</pre>';
*/

// rights
// 1 = full, 2, 3 = none
$rights = $DB->queryUniqueValue("SELECT userlevel FROM reps WHERE id = ".$_SESSION['loggedin']);
$rightsfilter = "rep LIKE '%'";
if($rights == "3"){
	$rightsfilter = "rep = ".$_SESSION['loggedin'];
}

// get a JSON list of reps 
function getRepsCheckList($notloggedin = true){
	global $DB;
	$notid = (isset($_REQUEST['notid']))?$_REQUEST['notid']:'0';
	$where = ($notloggedin)?"id != ".$_SESSION['loggedin']." AND ":"";
	$sql = "SELECT id, firstname FROM reps WHERE ".$where." userlevel > 0 ORDER BY firstname";
	$res = $DB->query($sql);
	$ret = '<ul id="repchecklist">';
	$ret .= '<li><b>CC: </b></li>';
	while($r = $DB->fetchNextObject($res)){
		$ret .= '<li id="repcheck'.$r->id.'">
						 <label>
						 <input type="checkbox" class="repchecks" value="'.$r->id.'" /> '.$r->firstname.'
						 </label></li>';
	}
	$ret .= '</ul>';
	
	return $ret;
}



// get the green 
function getPotentialDollers(){
	global $DB;
	global $rightsfilter;
	
	$searchCriteria = (strlen(getCriteria()))?' AND '.getCriteria():'';
	$sql 	= "SELECT SUM(estimated) as est FROM leads WHERE status = 'pending' AND ".$rightsfilter.$searchCriteria;
	$r = $DB->queryUniqueObject($sql);
	$potential = ($r->est);
	setlocale(LC_MONETARY, 'en_US');
	return "$".number_format(round($potential));
}


// the the conditional statement to the sql query
function getCriteria(){
	global $numCols;
	$search_fields = array();
	

	
	// jobname
	if(strlen($_REQUEST['search_jobname']) > 0){
		$search_fields []='jobname LIKE \'%'.$_REQUEST['search_jobname'].'%\'';
	}
	
	// company
	if(strlen($_REQUEST['search_company']) > 0){
		$search_fields []= 'company LIKE \'%'.$_REQUEST['search_company'].'%\'';
	}
	
	// company
	if(strlen($_REQUEST['search_customer']) > 0){
		$search_fields []= 'customer LIKE \'%'.$_REQUEST['search_customer'].'%\'';
	}

	// rep
	if(strlen($_REQUEST['rep']) > 0 && $_REQUEST['rep_check']){
		$search_fields []= 'rep = '.$_REQUEST['rep'];
	}
	
	// status
	if(strlen($_REQUEST['search_status']) > 0){
		if($_REQUEST['search_status'] != "0"){
			$search_fields []= 'status = \''.$_REQUEST['search_status'].'\'';
		}else{
			$search_fields []= 'status != "***"';
		}
	}	
	
	// target date
	if(strlen($_REQUEST['search_target_before']) > 0 && strlen($_REQUEST['search_target_after']) > 0){
	
		$search_fields []= '(targetdate >= \''.dateFixSQL($_REQUEST['search_target_after']).'\' 
						 AND targetdate <= \''.dateFixSQL($_REQUEST['search_target_before']).'\')';
	
	}else{
		
		// target date before
		if(strlen($_REQUEST['search_target_before']) > 0){
			$search_fields []= 'targetdate <= \''.$_REQUEST['search_target_before'].'\'';
		}
		
		// target dates after
		if(strlen($_REQUEST['search_target_after']) > 0){
			$search_fields []= 'targetdate >= \''.$_REQUEST['search_target_after'].'\'';
		}		
		
	}
	
	//estimated low & high
	if(strlen($_REQUEST['estimated_low']) > 0 && strlen($_REQUEST['estimated_high']) > 0){
		
		$search_fields []= '(estimated >= '.$_REQUEST['estimated_low'].' 
							 AND estimated <= '.$_REQUEST['estimated_high'].')';
	
	}else{
		
		if(strlen($_REQUEST['estimated_low']) > 0){
			$search_fields []= 'estimated = '.$_REQUEST['estimated_low'];
		}
		
		if(strlen($_REQUEST['estimated_high']) > 0){
			$search_fields []= 'estimated = '.$_REQUEST['estimated_high'];
		}	
	}
	
	
	$sql_criteria = '';
	foreach($search_fields as $k => $v){
		$type = ($_REQUEST['reqtype'] == 'all')?' AND ':' OR ';
		$and = ($k > 0)?$type:'';
		$sql_criteria .= $and.$v;
	}
	
	return $sql_criteria;
}

// get the meat
function getTbody(){

global $DB;
global $numCols;
global $rightsfilter;

$where = (strlen(getCriteria()))
					?getCriteria()
					:' status = "pending" ';

$where .= ' AND '.$rightsfilter;			

$sql = "SELECT *, 
				CONCAT('$',estimated) as estimated
				FROM leads WHERE ".$where." 
				ORDER BY company, customer";

//print $sql;

$res = $DB->query($sql); 	
$ret = '<tbody id="mtb">'."\n";

while($r = $DB->fetchNextObject($res)){
	
	$ret .= "\t";
	$ret .= '<tr id="row'.$r->id.'">';

	$ret .= '<td align="center">';
	$ret .= stripslashes(ws($r->id));
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= stripslashes(ws($r->jobname));
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= stripslashes(ws($r->company));
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= stripslashes(ws($r->customer));
	$ret .= '</td>';
	
	$ret .= '<td>';
	$ret .= ws(dateFix($r->targetdate));
	$ret .= '</td>';

	
	// the date closed field
	if($r->status == 'closed'){
		
		$statushtml = dateFix($r->dateclosed);
		$hideclass = 'class="hideclosed"';
	
	}else{
		
		if(strlen($r->reason) > 0){
			
			$repreason = ($r->reason_repid)
			?'<i><small><br /><br />-'.getRep($r->reason_repid).'-</small></i>'
			:'';
			
			$reason = $r->reason."\n\n";
			$statushtml = '<a href="#" rel="'.$reason.'" class="moreinfo" title="'.$reason.$repreason.'">'.$r->status.'</a>';
		
		}else{
			$statushtml = $r->status;
		}
		$hideclass = '';
	}


	$ret .= '<td '.$hideclass.'>';
	$ret .= ws($statushtml);
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= ws(dateFix($r->createdon));
	$ret .= '</td>';

	$ret .= '<td align="center">';
	$ret .= ws(getRepField($r->quotedby_repid, 'LOWER(CONCAT(SUBSTRING(firstname, 1, 1),
																							SUBSTRING(lastname, 1, 1)))'));
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= ($r->rep == 0)?'n/a':getRep($r->rep);
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= $r->estimated;
	$ret .= '</td>';

	$ret .= '<td>';
	$ret .= (strlen($r->quotefile) > 0)
			?'<a href="_lib/php/forcedownload.php?filename='.stripslashes($r->quotefile).'">
			  <img src="images/quote_icon.png" width="16" height="16" border="0" title="view attachment" />
			  </a>'
			:ws('');
			
	$ret .= '</td>';

	$ret .= '<td id="'.$r->id.'" class="actionbt">action';
	$ret .= '<input id="comments_'.$r->id.'" type="hidden" value="'.$r->comments.'" />'; // get the comments
	
	// get the project info
	$info = '';
	$info .= 'created by: '.getRep($r->createdby_repid)."\n";
	$info .= 'created on: '.dateFix($r->createdon)."\n\n";
	$info .= 'last updated by: '.getRep($r->lastupdatedby_repid)."\n";
	$info .= 'last updated on: '.dateFix($r->lastupdated)."\n\n";
	
	$ret .= '<input id="info_'.$r->id.'" type="hidden" value="'.$info.'" />'; // get the prospect info		 
	$ret .=	'</td>';
	$ret .= '</tr>'."\n";

}
	
	// show a row with no results
	if(($DB->numRows($res) < 1)){
		$ret .= '<tr id="nores">';
		$ret.='<td colspan="'.$numCols.'" >No results found</td>';	
		$ret .='</tr>';
	}

	$ret .= '</tbody>';
	return $ret;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Riot Creative Imaging - <?php print $version['name']; ?></title>
<link href="_lib/css/style.css" rel="stylesheet" type="text/css" />
<link href="_lib/js/jquery-autocomplete/jquery.autocomplete.css" rel="stylesheet" type="text/css" />
<link href="_lib/js/jquery-ui-1.8.5.custom/css/smoothness/jquery-ui-1.8.5.custom.css" rel="stylesheet" type="text/css" />
<style>
.shade{
	background-image: url(images/maintabletpbgexp.png);
	background-position: top;
	background-repeat: repeat-x;	
}
#actionnav{
	border: 4px solid #dfdfdf;
	background-color: white;
	-moz-border-radius: 15px 0em 15px 15px;
	border-radius: 15px 0em 15px 15px;
	padding: 10px;
	width: 150px;
	height: auto;
	position: absolute;
	left: 50px;
	top: 100px;
	z-index: 50;
	display: none;
}
#actionnav ul{
	margin: 0px;
	padding: 0px;
	list-style: none;
}
#actionnav ul li{
	display: block;	
}
#actionnav ul li a{
	display: block;
	padding: 9px 0px;
	border-bottom: 1px solid #dfdfdf;
	color: #333;
	padding-left: 10px;
	text-decoration: none;
}
#actionnav ul li a:hover{
	color: #ef3f24;
	background-color: #f4f4f4;
}
.actionactivity{
	background-image: url(images/ajax-loader_sm.gif);
	background-repeat: no-repeat;
	background-position: right center;	
}
.moreinfo{
	color: #ef3f24;
	text-decoration: underline;
}
.moreinfo:hover{
	text-decoration: none;	
}
#nores{
	text-align: center;
	color: #CCC;
	padding: 50px 0px;
}


#popupbg{
	width: 100%;
	height: 100%;
	background-color: #323131;
  	display: none;
	position: absolute;
	z-index: 100;
	left: 0;
	top:0;
	padding-bottom: 20px;
}
.lggreyhead{
	padding-top: 20px;	
}
#popup{
	background-image: url(../../images/popupbg.png);
	width: 398px;
	height: auto;
	position: absolute;
	display: none;
	z-index: 101;
}
#popuptable thead th{
	border: 4px solid #dfdfdf;	
	cursor: move;
}
#popuptable tfoot td{
	border-top: 4px solid #dfdfdf;
}
#popuptable tbody td{
	background-color: white;	
	background-color: #f5f6f6;
	padding: 0px 20px 15px 30px;
}
#search_target_before 
,#search_target_after
,#estimated_low
,#estimated_high{
	width: 32%;	
}
.addsearchinputs{
	display: none;	
}
#clearsearch{
	text-align: center;
}
#clearsearch a{
	display: block;
	border: 3px solid #ef3f24;
	color: #ef3f24;
	padding: 15px;
	margin-bottom: 10px;
	background-color: #fef2ee;
}
#clearsearch a:hover{
	background-color:  white;	
}
#options{
	padding: 10px 0px;
	margin: 10px 0px;
	border: 4px solid #f1f1f1;
	border-left: none;
	border-right: none;
	color: #ccc;
	text-align: right;
}
.tooltip {
	display:none;
	background:transparent url(images/tooltip/black_riot.png);
	font-size:12px;
	height:117px;
	width:163px;
	padding:25px;
	color:#fff;	
}
#potentialmoney{
	float: left;
	font-size: 28px;
	color: #80c779;
	/* green color */
}
#messmess{
	margin-top: 30px;
	float: right;
}
#potentialtext{
	font-size: 10px;
	font-weight: bold;
	display: none;
}
#tabletopnav{
	float: right;
	padding-top: 10px;
}
.tbodies{
	display: none;
}
#correspondbody tr td{
	padding-top: 30px;
}
#corrleftcol{
	float: left;
	width: 150px;
}
#corrrightcol{
	float: left;
	margin-left: 40px;
}
#jobinfoul{
	margin: 0px;
	padding: 0px;
	padding-bottom: 20px;
	list-style: none;
}
#leftlist{
	height: 200px;
	overflow: hidden;
}
#jobinfoul li{
	display: block;
	padding: 10px 0px;
	border-bottom: 1px solid #dfdfdf;
	/*border-top: 1px solid #ffffff;*/
}
#jobinfoul li a{
	color: black;
}
.jobinfolabels{
	color: #999;
	font-size: 11px;
}
.jobinfofieldval{
	color: black;
	font-size: 11px;
	font-weight: bold;
	margin-top: 4px;
}
#corrcontainer{
	width: 620px;
	border-top: 5px solid #dfdfdf;
	margin-top: 0px;
	padding: 20px 0px;
	padding-bottom: 0px;
	clear: both;
}
.loadingmessages{
	display: none;
	float: left;
	position: relative;
	top: 20px;
	left: 40px;
}
.corritem{
	padding-bottom: 10px;
	margin-bottom: 10px;
	border-bottom: 1px solid #dfdfdf;
	padding: 15px;
  -moz-border-radius: 20px;
 	-webkit-border-radius: 20px;
  -khtml-border-radius: 20px;
  border-radius: 20px;

}
.loggedinposts{
	background-color: #ededee;
	border-bottom: none;
}
#withrep{
	float: right;
	width: 300px;
}
#rep{
	margin-bottom: 15px;
}
.corrhead{
	color: #80c779;
	font-size: 16px;
	font-weight: bold;
}
#messmess{
	color: #80c779;
	font-size: 15px;
	font-weight: bold;	
}
.corrwhotowho{
	font-size: 14px;
	font-weight: bold;
	float: left;
}
.corrdate{
	font-size: 12px;
	float: right;
}
.corrmess{
	padding-top: 15px;
	clear: both;
	line-height: 20px;
}
#emailicon{
	color: #29AAE1;
	font-weight: bold;
	position:relative;
	padding: 5px;
	padding-left: 20px;
	width: 16px;
	background-image: url(images/emailicon.png);
	background-repeat: no-repeat;
	background-position: left center;
}
#showmoreinfo{
	color: #ef3f24;
	text-decoration: underline;
	padding: 5px 0px;
	margin-bottom: 40px;
	display: block;
}
#showmoreinfo:hover{
	text-decoration: none;
}
#newmessage{
	width: 600px;
	font-family: inherit;
	color: inherit;
	height: 140px;
}
#repnames{
	width: 567px;
	padding-left: 40px;	
}
#cc{
	position: absolute;
	margin-top: 23px;
	margin-left: 12px;
	font-weight: bold;	
}
#repchecklist{
	margin: 0px;
	padding: 0px;
	list-stlye: none;
}
#repchecklist li{
	padding: 5px;
	list-style: none;
	display: inline;
}
#bglggrey{
	padding-bottom: 15px;
	float: left;
}
#sendmesscont{
	padding: 10px 0px;
}
.nomessages{
	border-bottom: none;
	color: #f1f1f1;
	font-size: 60px;
	padding: 0px;
}
.debug{
	position: fixed;
	background-color: white;
	padding: 10px;
	color: red;
	border: 3px solid red;
	display: none;
}
/* orange color #ef3f24*/
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"> </script>
<script type="text/javascript" src="_lib/js/jquery.tablesorter.min.js"> </script>
<script type="text/javascript" src="_lib/js/jquery.fixedCenter.js"> </script>
<script type="text/javascript" src="_lib/js/util.js"> </script>
<script type="text/javascript" src="_lib/js/jquery-ui-1.8.5.custom/js/jquery-ui-1.8.5.custom.min.js"> </script>
<script type="text/javascript" src="_lib/js/jquery.tooltip.min.js"> </script>
<script type="text/javascript" src="_lib/js/jquery-autocomplete/jquery.autocomplete.min.js"></script>
<script type="text/javascript">


$(document).ready(function(){				
	
	// turn on auto complete for the rep names
	
	$("#repnames").autocomplete('_lib/php/ajax_get_reps_autocompleted.php', {
		width: 300,
		multiple: true,
		matchContains: true
	})
	//.blur(function(){alert($('#repids').val())});
	
	$("#repnames").result(function(event, data, formatted) {
		var $hidden = $('#repids');
		$hidden.val( ($hidden.val() ? $hidden.val() + ";" : $hidden.val()) + data[1]);
	});
						
	// make it sortable
	if($('#maintable tbody tr').length > 1){
		$("#maintable").tablesorter({sortList: [[0,0], [1,0]]}); 
	}

	// chose the dates
	$('#search_target_before').datepicker();
	$('#search_target_after').datepicker();
	
	// config the sort order for xl download
	var sortOrder;
	
	$('#maintable').bind("sortEnd",function() {
											
		sortOrder = '';									
											
        arr = this.config.sortList;
		
		$.each(arr, function(i){
			colid = arr[i][0];
			sorder = arr[i][1];
			
			sorder = (sorder == 0)?'ASC':'DESC';
			colname = $('#maintable thead th').eq(colid).attr('abbr');
			
			sortOrder += colname+' '+sorder;
			sortOrder += (i<arr.length-1)?', ':'';
			
		});
	
	
		thishref = $('a[rel=dowloadexcel]').attr('href');
		$('a[rel=dowloadexcel]').attr('href',thishref+'&orderby='+sortOrder);
    
	});

	//stripe the rows
	stripe = function(){ 
		$("#maintable tbody tr").removeClass('stripe');
		$v = $('#maintable tbody tr:visible:odd').addClass('stripe');
	}
	stripe();
	
	// restripe after sort
	$("#maintable").bind("sortEnd",function() { 
		stripe(); 
    }); 

	// style the first col with a border
	$('#maintable tbody tr td:first-child').addClass('firstcol');					   
	
	// style the last col (the action buttons)
	$('#maintable tbody tr td:last-child:not(#nores td)').addClass('actioncol');
	
	// get rid of the last line on the action sub nav
	$('#actionnav ul li a:last').css('border','none');
	
	// add some padding to the first tr
	$('#maintable tbody tr:first td').css({"padding-top":"10px"})
	
	// action click
	$('.actioncol').click(function(){
	// action hover
	var orgclass;
	}).hover(
	function(){
		orgclass = $(this).parent().attr('class');
		$(this).addClass('tablebthover');
		$(this).parent().removeClass(orgclass).addClass('rowcoloraction');
	},
	function(){
		$(this).removeClass('tablebthover');	
		$(this).parent().removeClass('rowcoloraction').addClass(orgclass);
	});
	
	
	// create the drop shadow on the action nav
	makeShadow = function(){
		$('.shadowdub').remove();
		$dup = $('#actionnav').clone();
		l = $('#actionnav').css('left');
		t = $('#actionnav').css('top');
		$dup.addClass('shadowdub').css({'left':(parseInt(l)-3), 
										'top':(parseInt(t)+3), 
										'z-index':'49', 'border-color':'#333333', 
										'opacity':'0.1'}).appendTo('body');
	}
	
	
	// hide the nav
	hideActionNav = function(){
		$('#actionnav, .shadowdub').fadeOut('fast');	
	}

	$('*:not(.actionnav, #actionnav)').click(function(){
		//$('.actionoverstate').trigger('click');
	});

	// ************** action **************** //
	// click on the action buttons
	// ************************************** //
	
	$('.actionbt').toggle(function(){
		
		
		// reset everything
		id = $(this).attr('id');
		$('#downloadquote').css({'text-decoration':'none'}).attr({'href':'#'});
		$('.owner_admin_rights_only').show();
		
		var errmessage = '';
		var quotelink = '';
		
		
		// set the links for correspondance
		$('#emailrep').attr({'rel':id});
		// change the selected to the rep assigned
		$.getJSON('_lib/php/ajax_getlead_info.php', {'leadid':$('#emailrep').attr('rel')}, function(data){
					$('#repcorr option[value='+data.repid+']').attr({'selected':true});	
		});		
		
		
		// get rights to perform actions
		$.get('_lib/php/ajax_get_rights.php', { 'id':id } , function(data){
			
						
			if(data.substring(0,1) == "1"){
				rights = data.substring(2);
				if(rights == "restricted"){
					$('.owner_admin_rights_only').hide();
				}
				
			}else{
				errmessage = data.substring(2);
			}
		});
		
		
		// check for an associated download file
		$.get('_lib/php/ajax_checkfordownload.php', { 'id':id } , function(data){
			if(data.substring(0,1) == "1"){
				quotelink = data.substring(2);
				$('#downloadquote').attr({'href':'_lib/php/forcedownload.php?filename='+quotelink});
			}else{
				$('#downloadquote').css({'text-decoration':'line-through'});
				errmessage = data.substring(2);
			}
		});

		//check for comments
		setComments = $('#comments_'+id).val();
		if($.trim(setComments).length < 1){
			$('#readcomments').css({'text-decoration':'line-through'})
		}
		
		
		// do the math on the drop shadow
		p = $(this).position();
		sh = $('#actionnav').outerHeight();
		sw = $('#actionnav').outerWidth();
		w = $(this).outerWidth();
		h = $(this).outerHeight();
	
		// make sure there are no leftover click states
		$('.actionoverstate:not(this)').trigger('click');
		$('.actionoverstate').removeClass('actionoverstate');
		
		$(this).addClass('actionoverstate');
		
		$('#actionnav').fadeIn('fast').css({'left':(p.left - sw + w), 'top':(p.top + h)});
		makeShadow();

		// use esc key to hide menu
		$(document).keypress(function(e){
			if(e.keyCode == 27){
				$('.actionoverstate').trigger('click');
			}
		})
		
		$("*:not(.actionbt, #actionnav)").click(function(){
				$('.actionoverstate').trigger('click');
		});
		
		// hide if anything is clicked on 
		/*
		$('*:not(this, .actionbt, #actionnav, #actionnav a)').click(function(){
			$('.actionoverstate').trigger('click');			   
		});
		*/
	
	},function(){
		$(this).removeClass('actionoverstate');
		hideActionNav();
	});
	
	
	
	// edit a project
	$('#editprospect').click(function(){
	  id = $('.actionoverstate').attr('id');
		document.location.href = "action/?action=edit&e="+id;								  
	});
	
	
	// read the comments
	$('#readcomments').click(function(){
		
		var id = $('.actionoverstate').attr('id');
		setCommnets = $('#comments_'+id).val();
		if($.trim(setComments).length > 0){
			alert(setCommnets);
		}
		return false;
	});
	
	// add to calendar
	$('#addtocal').click(function(){
		var id = $('.actionoverstate').attr('id');
		document.location.href="_lib/php/ajax_create_ical.php?id="+id;
	});
	
	
	// get the prospect info
	$('#prospectinfo').click(function(){
		
		var id = $('.actionoverstate').attr('id');
		setInfo = $('#info_'+id).val();
		if($.trim(setInfo).length > 0){
			alert(setInfo);
		}
		hideActionNav();
		return false;
	});
	
	// remove a prospect
	$('#removepropect').click(function(){
		
		var id = $('.actionoverstate').attr('id');
		
		$(this).addClass('actionactivity');
		
		if(!confirm('Are you sure that you would' 
				   +'like to remove this prospect?')){
			$(this).removeClass('actionactivity');
			return false;
		}
		

		
		
		$.get('_lib/php/ajax_removelead.php', { 'id':id } , function(data){

		
			if(data.substring(0,1) == 1){		
				$('#row'+id).fadeOut('fast', function(){
			
					$('.actionoverstate').trigger('click'); // hide the action
					$('#removepropect').removeClass('actionactivity'); // hide the loader
					$(this).remove(); // fade out and remove the row		
					stripe(); // restripe the rows	
				});
				
			}else{
				alert(data);
				$('.actionoverstate').trigger('click'); // hide the action
				$('#removepropect').removeClass('actionactivity'); // hide the loader
				
			}
	
 		});
		
		return false;
	});
	
	
	// get the reaosn for a particular status
	
	var orgclasss;
	$('.moreinfo').click(function(){
		//alert($(this).attr('rel'));	
		return false;
	})
	.tooltip()
	.hover(function(){
		orgclasss = $(this).parent().parent().attr('class');
		$(this).parent().parent().removeClass(orgclasss).addClass('rowcoloraction');				
	},function(){
		$(this).parent().parent().removeClass('rowcoloraction').addClass(orgclasss);
	})
	

	// copy the top nav to the bottom nav
	$.each($('#nav ul a'), function(){							
									
		$('<a></a>')
			.attr({'href':$(this).attr('href'),
				   'rel':$(this).attr('rel')})
			.text($(this).text())
			.appendTo('#btnav span');
	});	
	
	// fix the picpes in the btnav
	$('#btnav a:first').css('border-left','none');
	$('#btnav a:last').css('border-right','none');	

	
	// the popup functions
	var $bg = $('#popupbg');
 	var $popup = $('#popup');
	var orgWidth = $popup.width();

	// close popup
	closePop = function($bg, $popup){
		$popup.hide().css({'width':orgWidth});
		$bg.hide();
		$('.tbodies').hide();
		window.location.hash = '';
	}
	
	// resize the popupbg if the popup is bigger
	resizepopupbg = function(){
		wh = $(window).height();
		dh = $(document).height();
		ph = $popup.outerHeight();
		//alert(wh+":"+ph);
		if(ph > wh){
			$bg.height(dh+50);
		}else{
			$bg.height(dh);
		}
	}
	
	// open popup
	popupDiv = function($bg, $popup){
	
			dh = $(document).height();
			wh = $(window).height();
			
			$bg.css({'opacity':0.0}).show()
				 .click(function(){
					closePop($bg, $popup);			   
				}).fadeTo('fast',0.8, function(){
					$popup
					.show()
					.center()
					.draggable({handle:'#popuptable thead th'});
					resizepopupbg();
				})
		
		$('#close').click(function(){closePop($bg, $popup);return false;})
		$(document).keypress(function(e){
			if(e.keyCode == 27){
				closePop($bg, $popup);	
			}
		})
	
	}
	
	
	// close the popup
	$('a[rel=cancelbt]').click(function(){
		closePop($bg, $popup);						
	});
	
	
	// when search is clicked
	$('a[rel=searchfilter]').click(function(){

			// put the text in the header
			$('#searchtbody').show();
			$('#searchlggrey').text($(this).text());
			
			popupDiv($bg, $popup);
	
	});
	
	
	// load the correspondance
	loadCorr = function(){
			
			torepidselected = $('#repcorr option:selected').val();
			
			// hide the list items to copy only those not in the to field
			$('#repchecklist li').show();

			$('#repcheck'+torepidselected).hide().find('input').attr({'checked':false});
			
			$('.corritem').remove();
			
			$.getJSON('_lib/php/ajax_getcorr.php', 
						
						//send			
						{'leadid':$.getUrlVar('corrid'),
						 'torepid':torepidselected},
						
						
						//receive
						function(data) {
						
							var h = '';
							$.each(data.corr, function(i){
								$corritem = $('<div></div>').addClass('corritem').hide().addClass(data.corr[i].addclass);
								
								$corrhead = $('<div></div>').addClass('corrhead').appendTo($corritem);
								
								w = data.corr[i].whotowho;
								$corrwhotowho = $('<div></div>').addClass('corrwhotowho').appendTo($corrhead).text(w);
								d = data.corr[i].postdate;
								pt = data.corr[i].posttime;
								$corrdate = $('<div></div>').addClass('corrdate').appendTo($corrhead).text(d+" "+pt);
								
								m = data.corr[i].message;
								$message = $('<div></div>').addClass('corrmess').appendTo($corritem).html(m);
								
								$corritem.appendTo('#corrcontainer').fadeIn('slow');
							
							});
							
						});
	}
	$('#repcorr').change(function(){
			loadCorr();																					
	});
	
	
	// fill the left column of the corr window with job info
	var defaultShow = 6;
	fillJobInfo = function(resultsToShow){
		$('#jobinfoul li').remove();
		$('#attachmentsbox').hide();
		
		$.getJSON('_lib/php/ajax_getlead_info.php', {'leadid':selectedrow}, function(data){
			
				
			// show the attachment option if there is a file
			if(data.quotefilestr == '1'){
				$('#attachmentsbox').show();
			}			
			
			var i = 0;
			for(var property in data){
				if(resultsToShow != "undefined"){
					if(i+1 > resultsToShow){ return; }
				}
				
				// if its a property you want to show
				if(property != 'repid' && property != 'quotefilestr'){
				
					$labelText = $('<span class="jobinfolabels"></span>').text(property+": ");
					fieldval = data[property];
					$field = $('<div class="jobinfofieldval"></div>').html(nullToStr(fieldval,"---"));
					$li = $('<li></li>').append($labelText).append($field).appendTo('#jobinfoul');
					i++;
			
				}
			
			}
		});
	}
	
	// the show more button
	var defaulttext = '';
	$("#showmoreinfo").toggle(
	function(){
			fillJobInfo();	
			defaulttext = $(this).text();
			$(this).text('- show less info').addClass('isopen');
	},
	function(){
			fillJobInfo(defaultShow);		
			$(this).text(defaulttext).removeClass('isopen');
	});
	
	
	//when correspondance is clicked
	$('#emailrep').click(function(){	
						
			leadid = $(this).attr('rel');
			
			if(leadid.length > 0){
				hash = "corrid="+leadid;
				window.location.hash = hash;
			}
			
			// load the corr
			loadCorr();			
			
			$('#jobinfoul').empty();
			
			selectedrow = $.getUrlVar('corrid');
			
			// feed the job infor ul for the rep correspondace info		
			if($('.isopen').length){
				fillJobInfo();		
			}else{
				fillJobInfo(defaultShow);		
			}
			
			// remove the brdr
			$('#jobinfoul li:last').css({'border-bottom':'none'});
			$('#jobinfoul li:first').css({'border-top':'none'});
			
			// put the text in the header
			$('#correspondbody').show();
			$('#toplggrey').text($(this).text());
			
			
			popupDiv($bg, $popup);
			$popup.css({'width':'900px'});
				
			
	return false;
	});
	
	// popup if jurl is active
	
	if($.getUrlVar("corrid") > 0 ){
		$('#emailrep').attr({'rel':$.getUrlVar("corrid")});
		$('#emailrep').trigger('click');
	}
	

	// the show/hide functions of the search inputs
	
	$.each($('#searchtbody').find('label:not(.reqoptions)'), function(i){

		// the hide function
		ctext = 'x';
		$close = $('<a></a>')
				 .attr({'href':'#','rel':'closeclick'})
				 .text(ctext)
				 .addClass('links')
				 .click(function(){
					// alert($(this).parent().find('label').text());
					 $(this).parent().find('*').hide();
					 $(this).parent().find('input[type=hidden]').val('0'); // so that if it hasnt been opened, it wont be searched on
					 $(this).parent().find('a[rel=openclick]').show();
					 return false;
				 });
		$close.appendTo($(this).parent());

		// the show function
		pretext = '+ ';
		text = $.trim($(this).parent().find('.fieldlabel').text());	
		posttext = '... ';
	
		$a = $('<a></a>').attr({'href':'#','rel':'openclick'})
			 .text(pretext+text+posttext)
			 .addClass('links')
			 .click(function(){
				$(this).parent().find('*').show();
				$(this).parent().find('input[type=hidden]').val('1'); // so that if it hasnt been opened, it wont be searched on
				$(this).hide();
				$(this).parent().find('input:eq(0)').focus();
				return false;
			 });
			 
		$(this).parent().find('*').hide();
		$a.appendTo($(this).parent());
	});
	
	$('a[rel=customsearch]').click(function(){
		$('#searchform').submit();
		window.location.hash = '';
		return false;
	});
	
	$('#searchtbody tr td .input').focus(function(){
		$(document).keypress(function(e){
			if(e.keyCode == 13){ // if enter key is pressed
				$('#searchform').trigger('submit');
			}
		});											  
	});

	
	// save the new message
	$('a[rel=sendmessage]').click(function(){
		
		$(this).hide();
		
		mv = $.trim($("#newmessage").val())
		if(mv.length == 0){
			alert("No message entered");
			$(this).show();
			return false;
		}
		
		var torepidselected = $('#repcorr option:selected').val();
		
		// the data to send
		var senddata = {'leadid':$.getUrlVar('corrid'),
			'repnames':$('#repnames').val(),
			'torepid':torepidselected,
			'message':$('#newmessage').val(),
			'attachments':$('#attachments:checked').val(),
			'repchecks[]' : []}
			
		$(".repchecks:checked").each(function(i) {
			senddata['repchecks[]'].push($(this).val());
		});
		
		$.get('_lib/php/ajax_savecorr.php',
					
			//send			
			senddata, 
			
			// recieve
			function(data){
				if(data.substring(0,1) == "1"){
					$('#corrcontainer').css({'height':$('#corrcontainer').height()});
					loadCorr();
					$('#corrcontainer').css({'height':'auto'});
					$('#newmessage').val('');
					//$('#messmess').text('Message Sent!');
				}
			});	
		
		
		return false;
	});
	
	$('#newmessage').focus(function(){
			$('#messmess').text('');
	});
	
	// ajax loading gif
	$('#tploadingimg').ajaxStart(function() {
  	$(this).show();
	}).ajaxStop(function(){
  	$(this).hide();
		$('a[rel=sendmessage]').show();
		resizepopupbg();
	});	



/*
	c = {			"id":"0", 
								"jobname":"test",
								"quotefile":"SutterHealthPAMF_102710_232936.xls",
								"company":"Sutter Health  PAMF",
								"customer":"Susan Pearson",
								"targetdate":"0000-00-00",
								"dateclosed":"0000-00-00",
								"rep":"Robert Cox",
								"estimated":"140.63",
								"joanlead":"",
								"comments":"",
								"reason_repid":"0",
								"status":"pending",
								"reason":"",
								"createdon":"2010-10-26",
								"lastupdated":"2010-10-27",
								"createdby_repid":"7",
								"lastupdatedby_repid":"7"
					}
	//alert(c.corr.id);
	*/
});

</script>
</head>
<body>

<!-- debug -->
<div class="debug"></div>

<!-- the action click submenu -->
<div id="actionnav">
<ul>
	<li class="owner_admin_rights_only"><a href="#" id="removepropect">remove prospect</a></li>
    <li class="owner_admin_rights_only"><a href="#" id="editprospect">edit prospect</a></li>
    <li><a href="#" id="downloadquote">download quote</a></li>
    <li><a href="#" id="emailrep">correspondence</a></li>
    <li><a href="#" id="addtocal">add to calendar</a></li>
	<li><a href="#" id="readcomments">read comments</a></li>
    <li><a href="#" id="prospectinfo">get prospect info</a></li>
    
    </ul>
</div>

<!-- the popup -->
<div id="popupbg"></div>
<div id="popup">
	<div id="popupinner">
    	
        <div>
        
        <table width="100%" border="0" cellspacing="0" cellpadding="0" id="popuptable">
		<thead>
        	<tr>
            	<th id="popuphead">&nbsp;</th>
            </tr>
        </thead>
        
        <!-- for search -->
        <form name="searchform" id="searchform" method="get" action="index.php">
        <tbody id="searchtbody" class="tbodies">
        	<tr>
            	<td>
                <div class="lggreyhead" id="searchlggrey"></div>
                </td>
            </tr>
			<tr>
            	<td>
                <label>
                <span class="fieldlabel">search for jobname like</span><br />
                  <input type="text" name="search_jobname" id="search_jobname" class="input" />
                </label>
                </td>
            </tr>
			<tr>
            	<td>
                <label>
                <span class="fieldlabel">search company name like</span><br />
                  <input type="text" name="search_company" id="search_company" class="input" />
                </label>
            	</td>
            </tr> 
			<tr>
            	<td>
                <label>
                <span class="fieldlabel">search contact name like</span><br />
                  <input type="text" name="search_customer" id="search_customer" class="input" />
                </label>
            	</td>
            </tr>
		<tr>
            	<td>
                <label>
                <span class="fieldlabel">search status like</span><br />
                  <select name="search_status" class="input">
                  			<option value="0">*any*</option>
 											<?php foreach($statuses as $v){ ?>
												<option value="<?php echo $v;?>"><?php echo ucwords($v);?></option>
											<?php } ?>
                  </select>               
                </label>

            	</td>
            </tr> 
 			<tr>
            	<td>
                <label>
                <span class="fieldlabel">search by rep</span><br />
                  <?php print getRepsDrop(); ?>
                </label>
                <input type="hidden" name="rep_check" id="rep_check" value="0" />
            	</td>
            </tr>

			<tr>
            	<td>
                <label><span class="fieldlabel">search estimated $ between</span><br />
                <small>(Only enter first value to look for a specific amount)</small>
                <br />
                </label>
   				<span>
                  $<input type="text" name="estimated_low" id="estimated_low" class="input" />
 				  - $<input type="text" name="estimated_high" id="estimated_high" class="input" />
                </span>
            	</td>
            </tr>
 
			<tr>
            	<td>
                <label><span class="fieldlabel">search target dates</span><br /></label>
   				<span>
                  after <input type="text" name="search_target_after" id="search_target_after" class="input" readonly="readonly" />
 				  before <input type="text" name="search_target_before" id="search_target_before" class="input" readonly="readonly" />
                </span>
            	</td>
            </tr>
          	<tr>
            	<td>
                <div style="padding-bottom: 3px;">
                	<label class="reqoptions">
                    <input name="reqtype" type="radio" value="any" checked="checked" />
                	 require any criteria
                    </label>
                </div>
                <div>
                	<label class="reqoptions">
                    <input name="reqtype" type="radio" value="all" />
                	 require all criteria
                    </label>
                </div>                    
                </td>
            </tr>                 
            <tr>
            	<td>
                <input type="hidden" id="searched" name="searched" value="1" />
                
                <a class="navbt" href="#" rel="customsearch"><span>search</span></a>
                <a class="navbt" href="#" rel="cancelbt"><span>close</span></a>
                
                </td>
            </tr>                               
        </tbody>
        </form>
        <!-- /for search -->


        <!-- for correspondance popup -->
        <tbody id="correspondbody" class="tbodies">
         	
          <tr>
            	<td>
                
                <div id="corrleftcol">
                               
                	<ul id="jobinfoul"></ul>
                  
                  <a href="#" id="showmoreinfo">+ show more info</a>
                
                </div>
                
                <div id="corrrightcol">
                	
                  <div class="lggreyhead" style="float: left;" id="toplggrey"></div>
                  
                  <!-- the loading graphic -->                  
                  <div class="loadingmessages" id="tploadingimg">
                  <img src="images/ajax-loader.gif" width="24" height="24" align="absmiddle" />
                  </div>
                  <!-- / the loading graphic -->
                  
                  <div id="withrep">with: <?php print getRepsDrop('repcorr',true); ?></div>
                 
                  
                  <div id="corrcontainer">
                  
                  </div>
                  
									<div id="checklistcontainer">
                  <div class="lggreyhead" id="bglggrey">new message</div>
                  <div id="messmess"></div>
									<div class="c"></div>                  
									<?php //print getRepsCheckList(); ?>
                                    <div id="cc">CC:</div>
                                    <input class="input" name="repnames" id="repnames" value="" />
                  </div>
                  
                  <div id="newmesscontainer">
                  	<textarea name="newmessage" id="newmessage" cols="" rows="" class="input"></textarea>
                    
                    <div id="sendmesscont">
                    <a class="navbt" href="#" rel="sendmessage"><span>send message</span></a>            
                    <a class="navbt" href="#" rel="cancelbt"><span>close</span></a>
                  	<div style="float:left; display: none;" id="attachmentsbox">
                    	<label>Attach quote file? <input name="attachments" id="attachments" type="checkbox" value="1" /></label>
                      </div>
                    </div>
                    
                  </div>
                  
                </div>
                <div class="c"></div>
             
                
              </td>
          </tr>       	        
        </tbody>        
        <!-- /for correspondance -->

        
        <tfoot>
            <tr>
                <td>
                    <div id="footshadow">
            
                    <div id="footertext">
                    &copy; <?php print date('Y');?> Riot Creative Imaging</div>
                    <div class="c"></div>
            
                    </div>
                </td>
            </tr>
        </tfoot>
		</table>
        
        </div>
        
  </div>
</div>

<!--
<div id="tpnav">
	<div id="tpinnernav">
    	<ul>
        	<li><a href="_lib/php/police.php?logout=1">logout</a> | </li>
            <li><a href="myinfo/">my info</a> | </li>
            <li><a href="#">quote admin</a></li>
            
        </ul>
    </div>
</div>
-->

<div id="header">
	
  <div id="logo">
  <!--<a href="index.php?bypassid=7&leadid=25&repcorr=10&remoteentry=1"><img src="images/riotlogo.png" /></a> -->
  <img src="images/riotlogo.png" width="176" height="64" />
</div>
  
    <div id="title" class="oragehead"><?php print $version['name'];?> <span class="version">V.<?php print $version['version'];?></span></div>
	<div id="nav">
    <ul>
    	<li><a class="navbt" href="action/?action=add"><span>add prospect +</span></a></li>
    	<li><a class="navbt" href="_lib/php/download_excel.php?where=
					<?php 
					
					print (strlen(getCriteria()) > 0)?getCriteria().' AND '.$rightsfilter:$rightsfilter;
					
					?>" rel="dowloadexcel"><span>get csv &darr;</span></a></li>
        <li><a class="navbt" href="#" rel="searchfilter"><span>search / filter &#8853;</span></a></li>
    </ul>
  </div>
    <div class="c"></div>
</div>
<?php if(strlen(getCriteria()) > 0){ ?>
	<div id="clearsearch"><a href="index.php">remove search / filter criteria</a></div>
<?php } ?>
<div id="options">
  <div id="potentialmoney">
    <?php print getPotentialDollers(); ?>
  </div>
  <div id="tabletopnav">
  </div>
	<div class="c"></div>

</div>

<table width="100%" border="0" cellspacing="0" cellpadding="0" id="maintable">
<thead>
  <tr>
  	<th scope="col" id="firstth" abbr="id">#</th>
  	<th scope="col" abbr="jobname">jobname</th>
    <th scope="col" abbr="company">company</th>
    <th scope="col" abbr="customer">contact</th>
    <th scope="col" abbr="targetdate">target</th>
    <th scope="col" abbr="dateclosed">closed</th>
    <th scope="col" abbr="createdon">created</th>
    <th scope="col" abbr="joanlead">qtd</th>
    <th scope="col" abbr="rep">rep</th>
    <th scope="col" abbr="estimated">est. $</th>
    <th scope="col" abbr="quotefile" id="attachmentth"><img src="images/quote_icon.png" width="16" height="16" title="attachment" /></th>
    <th scope="col" id="lastth">&nbsp;</th>
  </tr>
</thead>
<?php print getTbody(); ?>
<tfoot>  
  <tr>
    <td colspan="<?php print $numCols; ?>" id="leftfoot">
    <div id="footshadow">
		
        <div id="footertext">&copy; <?php print date('Y');?> <?php print $version['company']; ?> | 
        Logged in as <?php print getRep($_SESSION['loggedin']); ?> | <a href="_lib/php/police.php?logout=1">logout </a></div>
        
        <div id="btnav">
        <span>
        </span>
        </div>  
        
        <div class="c"></div>
        
    </div>
    </td>
    <td>&nbsp;</td>
  </tr>
</tfoot>
</table>


</body>
</html>