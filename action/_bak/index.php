<?php
session_start();
print $_SESSION['startedfile'];
//print '<pre>';print_r($_REQUEST);print '</pre>';
require_once("../_lib/php/police.php");

require_once('../_lib/php/db.class.php/db.class.php');
$DB = new DB();
$uploadDir = '../quotes/';

//unset($_SESSION['startedfile']);
$uploadDir = '../quotes/';
$title = (isset($_REQUEST['e']))?'edit prospect':'add prospect';


function getStatusDropArr(){
	global $DB;
    $enum_array = array();
    $sql = 'SHOW COLUMNS FROM leads LIKE "status"';
    $res = $DB->query($sql);
    $r = mysql_fetch_array($res);
    preg_match_all('/\'(.*?)\'/', $r[1], $enum_array);
    if(!empty($enum_array[1])) {
        // Shift array keys to match original enumerated index in MySQL (allows for use of index values instead of strings)
        foreach($enum_array[1] as $mkey => $mval) $enum_fields[$mkey+1] = $mval;
        return $enum_fields;
    }
    else return array(); // Return an empty array to avoid possible errors/warnings 
						 //if array is passed to foreach() without first being checked with !empty().
}


// get the stauses in a dropdown menu
function getStatusDrop(){
	$choices = getStatusDropArr();	
	
	$ret = '<select class="input" name="status" id="status">';
	foreach($choices as $k=>$v){
		$selected = ($v == getVal('status'))?'selected="selected"':'';
		$ret .= '<option value="'.$v.'" '.$selected.'>'.$v.'</option>';
	}
	$ret .= '</select>';
	return $ret;
}

// get the reps in a dropdown menu
function getRepsDrop(){
	global $DB;
	$sql = "SELECT * FROM reps WHERE 1 ORDER BY firstname";
	$res = $DB->query($sql);
	
	$ret = '<select name="rep" id="rep" class="input">';
	while($r = $DB->fetchNextObject($res)){
		
		if($r->id == $_SESSION['loggedin'] && !isset($_REQUEST['e'])){ // if its from the logged in user
			$selected = 'selected="selected"';
		
		}elseif($r->id == getVal('rep')){ // if its from the db of the global vars
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

// gets the val of a field either from the db
// or from the $_REQUEST global vals
function getVal($field){
	global $DB;
	
	if(isset($_REQUEST['e'])){
		$sql = 'SELECT '.$field.' FROM leads WHERE id = '.$_REQUEST['e'];
		$ret = $DB->queryUniqueValue($sql);
	}else{
		$ret = 	$_REQUEST[$field];
	}
	return stripslashes($ret);
}

if(strlen(getVal('quotefile')) > 0){
	$_SESSION['startedfile'] = getVal('quotefile');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Riot Creative Imaging - Action</title>
<link href="../_lib/css/style.css" rel="stylesheet" type="text/css" />
<link href="../_lib/js/jquery-ui-1.8.5.custom/css/smoothness/jquery-ui-1.8.5.custom.css" rel="stylesheet" type="text/css" />
<style>
body{
	background-color: #333;	
}

#maintable th#backtohome{
	width: 10%;	
	padding: 0px;
}
#maintable th#thtitle{
	background-image: none;
	background: #f2f2f2;
	cursor: auto;
	text-align: left;
}
#maintable tbody td{
	background: #f2f2f2;
	border: none;
	border-right: 1px solid #dfdfdf;
	border-left: 1px solid white;
}
#firstrow td{
	border-top: 1px solid white;
	padding: 10px;
}
#innertable, #innertable td{
	border: none;	
}
#uploadbox, .uploadinner{

}
#uploadbox{
	border-top: none;
}
.uploadinner{
	padding: 10px;
	padding-bottom: 10px;
}
#comments{
	height: 100px;
	width: 96.6%;
	font-family: inherit;
	font-size: 14px;
}
#reason{
	height: 70px;
	width: 90%;
	font-family: inherit;
	font-size: 14px;
}
.check{
	border: 3px solid #dfdfdf;
	padding: 3px;
	background-color: white;
}
#response{
	padding: 5px;
}
#setfile, #replace{
	color: #333;
	border: 3px solid #dfdfdf;
	-moz-border-radius: 3px;
	text-shadow: 0 1px 1px white;
}
#setfile{
	padding: 15px 15px 15px 30px;
	background-image: url(../images/table.png);
	background-repeat: no-repeat;
	background-position: 10px center;
}
#replace{
	padding: 15px 18px;
}
#setfile:hover, #replace:hover{
	background-color: white;	
}
#fileinput{
	float: left;
	width: 100px;
	overflow: visible;
}
#fileoptions{
	padding-bottom: 10px;	
}
.loadimgs{
	display: none;	
}
#addmessage{
	border: 3px solid #88c476;
	background-color: #eef6ea;
	color: #88c476;
	font-size: 14px;
	font-weight: bold;
	text-align: center;
	padding: 20px;
	display: none;
}
.greena{
	color: #eef6ea;
	background-color: #88c476;
	-moz-border-radius: 3px;
	padding: 5px 7px;
	font-size: 11px;
	border: 3px solid #88c476;
}
.greena:hover{
	background-color: #eef6ea;
	color: #88c476;
	border: 3px solid #88c476; 
}
.hidestatusoption{
	display: none;	
}
#joanfield{
	
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"> </script>
<script type="text/javascript" src="../_lib/js/jquery-ui-1.8.5.custom/js/jquery-ui-1.8.5.custom.min.js"> </script>
<script type="text/javascript" src="../_lib/js/jquery.upload-1.0.2.min.js"> </script>
<script type="text/javascript" src="../_lib/js/util.js"> </script>
<script type="text/javascript">

$(document).ready(function(){
						   
	var uploadsDir = "<?php print $uploadDir; ?>";
	
	$('#targetdate, #dateopened, #dateclosed').datepicker();

	// fix the borders
	$.each($('tr:not(tr eq(0))'), function(){
		$('td:first',$(this)).css({'border-left':'none'});
		$('td:last',$(this)).css({'border-right':'none'});
	});
	
	// reset the input fields
	reset_inputs = function(){
		
		$container = $('#fileinput');
		$newinput = $('<input />').attr({'name':'quotefile', 'id':'quotefile', 'type':'file','class':'fileinputs'})
		.change(function(){
			changeImage($(this));			 
		});
		
		$('.fileinputs').remove();
		$container.append($newinput);
	}
	
	reset_inputs();
	
	changeImage = function($thisobj){
		
			$thisobj.hide();
			$('#loadimg').show(); 
			
			var urlstr = '';
			cval = $('#company').val();
			urlstr += (cval.length > 0)?'company='+cval:'';					 
			editval = "<?php print (isset($_REQUEST['e']))?$_REQUEST['e']:''; ?>";
			urlstr += (editval.length > 0)?'&e='+editval:'';
			urlstr = (urlstr.length > 0 )?'?'+urlstr:'';
			var filename = '';
			
			$thisobj.upload('../_lib/php/ajax_uploadquote.php'+urlstr, function(res) {            
			
			//alert(res);  
			  
			if(res.substring(0,1) == '1'){
				
			   file = res.substring(2);	
				
			   $('#fileinput').hide();
			   $('#fileoptions').show();
			   $('#setfile').attr({'href':uploadsDir+file});
			   filename = file;
				
			}else{
				alert('Oops, there was a problem with this upload:'+"\n\n"+res.substring(2));
				reset_inputs();
			   $('#fileinput').hide();
			}
			
			// populate the fields
			$.getJSON('../_lib/php/ajax_excelread.php?file='+filename, function(data) {

				for (var field in data) {
					if (data.hasOwnProperty(field)) {
						$('#'+field).val(data[field]);
						//alert(data[field]);
					}
				}
	
			});

			
			
			if(res.length > 0){
				$thisobj.show();
				$('#loadimg').hide();				
			}
			 
			 
           });
    };
	
	// remove the uploaded file
	$('#replace').click(function(){
								 
		var uploadedfile =  $('#setfile').attr('href');
		//alert(uploadedfile);
		
		uploadedfile = uploadedfile.substring(uploadedfile.lastIndexOf('quotes/')+7);
		
		e = "<?php print (isset($_REQUEST['e']))?$_REQUEST['e']:''?>";
		
		$.post("../_lib/php/ajax_removequote.php", { 'uploadedfile':uploadedfile, 'e':e } , function(data){
   			if(data == '1'){
				$('#fileoptions').hide();
				$('#fileinput').show();
			}else{
				alert(data);	
			}

 		});
		
		reset_inputs();
		return false;
	});
	
	// show and hide depending on what is set
	<?php 
	if(isset($_SESSION['startedfile'])){
	?>
	
	$('#fileoptions').show();
	$('#fileinput').hide();

	<?php	
	}else{
	?>

	$('#fileoptions').hide();
	$('#fileinput').show();
	
	<?php	
	}
	?>

	//get the file extensions
	getExts = function(){
		$.get('../_lib/php/ajax_uploadquote.php', { 'getexts':1 } , function(data){
   			$('#extensionsload').html(data);
 		});			
		
	}
	getExts();


	//check the form
	checkForm = function(){
		
		var errors = '';
		var $reqTextInputs = $('.req input[type=text]');
		
		//check that a date is entered if the staus is closed
		if($('#status').val() == 'closed' && $.trim($('#dateclosed').val()) == ''){
			errors += 'If the lead is closed, a closed date must be enetered'+"\n";
		}
		
		//check the req fields
		$.each($reqTextInputs, function(i){
			//alert(reqTextInput[i]);						  
			fieldval = $(this).val();
			if($.trim(fieldval) == ''){
				errors += $.trim($(this).parent().text())+' is a required field'+"\n";
			}
		});
		
		if(errors.length > 0){
			alert(errors);
			return false;
		}else{
			return true;	
		}
	};


	// send off the entire form
	$('#addeditbt').click(function(){

		if(!checkForm()){
			return false;	
		}


		sf = $('#setfile').attr('href');
		sf = sf.substring(uploadsDir.length);
   		
		$("#loadimgsend").show();
		$('#addeditbt').hide();		
		
		$('#hfname').val(sf);		
		
		$.post("../_lib/php/ajax_addeditlead.php", $("#addleadform").serialize(), function(data){
			
			
			if(data.length > 0){
				$("#loadimgsend").hide();
			}
			
			datacode = data.substring(0,1);
			if(datacode == 1){
				
				insertedId = data.substring(2);
				
				$('#addeditbt').fadeOut('fast');
				$('#addmessage').fadeIn('fast');
				$('input, textarea, select').attr('disabled','disabled');
				$('#fileoptions').hide();
				
				// so that they can edit the newly added or edited lead
				if(insertedId.length > 0){
					$('#editp').attr({'href':'index.php?e='+insertedId});	
				}else{
					$('#editp').hide();
				}
				
			}else{
				alert(data);	
			}
			
		});
		return false;
	});

	//get rid of the doller sign
	$('#estimated').blur(function(){
		str = $(this).val();
		$(this).val(str.replace("$",""));
	});

	
	// the staus drop functions
	$('#status').change(function(){
		selectedStatus = $(this).val();
		$optionDiv = $('#option_'+selectedStatus);
		$('.hidestatusoption').hide();
		
		// clear the closed if it is not selected
		if(selectedStatus != 'closed'){
			$('#dateclosed').val('');
		}
		
		if($('label', $optionDiv).attr('class') == 'reasonholder'){
			$reason = $('<textarea></textarea>').attr({"name":"reason", "id":"reason", "class":"input"});
			$reason.val('<?php print getVal('reason'); ?>');
			exsitingText = $('#reason').val();
			$('#reason').remove();
			$reason.val(exsitingText);
			$('label', $optionDiv).append($reason);
		}
		
		$optionDiv.show();
	});
	$('#status').trigger('change');
	
	
	// debug
	// ****************** //
	
	fillAll = function(){
	
	t = ['dog','posicle', 'boob', 'bike', 'cat', 'cathy', 'me', 'fred', 'strawberry', 'cow', 'fried', 'friend', 'book', 'makeup'];
	d = ['02/13/81', '04/13/77', '08/08/57', '01/08/80'];
	m = ['123.23', '432.09', '1234.34', '8347.98', '8172.23'];
	
	$.each($('input:[readonly=readonly]'), function(){
		rk = Math.floor(Math.random()*d.length)
		$(this).val(d[rk]);								   
	});
	
	$.each($('input[type=text]:not(input:[readonly=readonly]), textarea'), function(){
		rk = Math.floor(Math.random()*t.length)
		$(this).val(t[rk]);								   
	});
	
	rm = Math.floor(Math.random()*m.length)
	$('#estimated').val(m[rm]);
	
	}
	
	//fillAll();
	
	
	// ******************* //

	

});
</script>
</head>

<body>
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="maintable">
  
  <thead>
  <tr>
    <th id="backtohome" class="pseudobt"><a href="../">&larr; home</a></th>
    <th id="thtitle"><span class="oragehead"><?php print $title; ?></span></th>
  </tr>
  </thead>
  <tbody>
  <tr id="firstrow">
    <td colspan="2" style="padding: 20px 50px;">
	
	<form name="addleadform" id="addleadform" method="post" action="">
<table width="100%" border="0" cellspacing="0" cellpadding="0" id="innertable" align="center">
  <tr>
    <td width="33%">
           <div class="lggreyhead">attach quote file</div>
           <div class="smallgrey">(<span id="extensionsload"></span>)</div>
           <div id="response"></div>
           
           <div id="fileoptions">
           <a href="<?php print $uploadDir.$_SESSION['startedfile']; ?>" id="setfile" target="_blank">set file</a> <a href="#" id="replace">X</a>
           </div>
           
           <div id="fileinput">
           <img src="../images/ajax-loader.gif" width="24" height="24" id="loadimg" class="loadimgs" />
           <input name="quotefile" id="quotefile" type="file" class="fileinputs">
           </div>
           
            <div class="c"></div>     
            </div>
    </td>
    <td valign="bottom" width="33%">
	<label class="req">    
    job name<br />
    <input name="jobname" id="jobname" type="text" class="input" value="<?php print getVal('jobname');?>" />
    </label>
    
    </td>
    <td valign="bottom">
	<label class="req">    
    company name<br />
    <input name="company" id="company" type="text" class="input" value="<?php print getVal('company');?>" />
    </label>
    </td>
  </tr>
  <tr>
    <td valign="middle">
    <label class="req">
    customer name<br />
    <input name="customer" id="customer" type="text" class="input" value="<?php print getVal('customer');?>" />    
    </label>
      </td>
    <td valign="middle">
    
    
    <label class="req">
estimated $<br />
      <input name="estimated" id="estimated" type="text" class="input"  value="<?php print getVal('estimated');?>" />
    </label>
       
    </td>
    <td valign="top">
    <label >
    status<br />
	<?php print getStatusDrop(); ?>
    </label>
   
    
    
    </td>
  </tr>
  <tr>
    <td rowspan="2" valign="top" >

     <label >
      target date<br />
      <input name="targetdate" id="targetdate" type="text" class="input" 
      readonly="readonly" value="<?php print dateFix(getVal('targetdate'));?>" />
    </label>    
    
    </td>
    <td valign="top">
    <label >
    rep<br />
      <?php print getRepsDrop(); ?>
    </label>
      <td rowspan="2" valign="top">
      
    <div class="hidestatusoption" id="option_closed">
    <label >closed date<br />
      <input name="dateclosed" id="dateclosed" type="text" class="input" 
      readonly="readonly" value="<?php print dateFix(getVal('dateclosed'));?>" />
	</label>  
    </div>
    
    <div class="hidestatusoption" id="option_pending">
    <label class="reasonholder">reason for pending status<br />
		
	</label>  
    </div>

    
    <div class="hidestatusoption" id="option_lost">
    <label class="reasonholder">reason for lost status<br />

	</label>  
    </div> 
    
    
          
      </td>
  </tr>
  <tr>
    <td valign="top">
      <label id="joanfield">
        Joan lead?
        <input name="joanlead" type="checkbox" class="check" id="joanlead" <?php print intToStr(getVal('joanlead')); ?> />
        </label>        
    </tr>
  <tr>
    <td colspan="3">
    <label>
    	comments<br />
    	<textarea name="comments" id="comments" cols="" rows="" class="input"><?php print getVal('comments');?></textarea>
    </label>
    </td>
    </tr>
  <tr>
    <td colspan="3">
    
    <input type="hidden" name="hfname" id="hfname" />
    
    <?php
    if(isset($_REQUEST['e'])){
    	print '<input type="hidden" name="e" value="'.$_REQUEST['e'].'" />';
	}
	?>
    
    <img src="../images/ajax-loader.gif" width="24" height="24" id="loadimgsend" class="loadimgs" style="float: right" />
    <a class="navbt" href="#" style="float: right; margin-right: 90px;" id="addeditbt"><span>save prospect</span></a>
    
    <div id="addmessage">Success! Database changed
     
    <a href="index.php" class="greena">add a new prospect</a>  
    <a href="#" class="greena" id="editp">edit this prospect</a>
    <a href="../" class="greena">go back to the propect list</a>
    
    </div>
    
    </td>
  </tr>
    </table>
</form>
 
 

 
    
    </td>
  </tr>
  </tbody>
<tfoot>  
  <tr>
    <td colspan="2">
    <div id="footshadow">
		
        <div id="footertext">&copy; <?php print date('Y');?> Riot Creative Imaging - Sunnyvale. Lead Tracker Created by Robert Cox</div>
        <div class="c"></div>
        
    </div>
    </td>
  </tr>
</tfoot>
</table>
</body>
</html>