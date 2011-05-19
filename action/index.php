<?php
session_start();
//print $_SESSION['startedfile'];
//print '<pre>';print_r($_SESSION);print '</pre>';
require_once("../_lib/php/police.php");
require_once("../_lib/php/util.php");
include("../_lib/php/version.php");

//unset($_SESSION['startedfile']);
$uploadDir = '../quotes/';
$title = $_REQUEST['title'];


$actions = array(
	'add' => array('blockfolder'=>'blocks/addedit_prospect/','pagetitle'=>'add prospect'),
	'edit' => array('blockfolder'=>'blocks/addedit_prospect/','pagetitle'=>'edit prospect'),
	'error' => array('blockfolder'=>'','pagetitle'=>'page error')
				
);

$actionsKey = (isset($_REQUEST['action']) && in_array($_REQUEST['action'], array_keys($actions)))
			  ?$_REQUEST['action']
			  :'error';

$blockfolder = $actions[$actionsKey]['blockfolder'];

if(is_file($blockfolder.'php/functions.php')){
	include($blockfolder.'php/functions.php');
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Riot Creative Imaging - <?php print $actions[$actionsKey]['pagetitle']; ?></title>
<link href="../_lib/css/style.css" rel="stylesheet" type="text/css" />
<link href="../_lib/js/jquery-ui-1.8.5.custom/css/smoothness/jquery-ui-1.8.5.custom.css" rel="stylesheet" type="text/css" />
<style>
body{
	background-color: #333;	
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
#estimated_low,
#estimated_high{
	width: 35%;	
}
table{
	width: 900px;
}
#homebt{
	background-image:url(../images/houseicon.png);
	background-repeat: no-repeat;
	background-position: center center;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"> </script>
<script type="text/javascript" src="../_lib/js/jquery-ui-1.8.5.custom/js/jquery-ui-1.8.5.custom.min.js"> </script>
<script type="text/javascript" src="../_lib/js/jquery.upload-1.0.2.min.js"> </script>
<script type="text/javascript" src="../_lib/js/util.js"> </script>
<script type="text/javascript">

<?php
if(is_file($blockfolder.'php/phptojsvars.php')){
include($blockfolder.'php/phptojsvars.php');
}
?>

</script>

<?php
if(is_file($blockfolder.'js/js.js')){
	print '<script type="text/javascript" src="'.$blockfolder.'js/js.js'.'"> </script>';
}
?>

</head>

<body>
<div id="subpagecontent">

<table width="100%" border="0" cellspacing="0" cellpadding="0" id="maintable">
  
  <thead>
  <tr>
    <th id="backtohome" class="pseudobt"><a href="../" id="homebt"></a></th>
    <th id="thtitle"><span class="oragehead"><?php print $actions[$actionsKey]['pagetitle']; ?></span></th>
  </tr>
  </thead>
  
  <tbody>
  <tr id="firstrow">
    <td colspan="2">
	
	<?php
    
    if(is_file($blockfolder.'php/body.php')){
        include($blockfolder.'php/body.php');
    }
    
    ?>
    
    </td>
  </tr>
  </tbody>
<tfoot>  
  <tr>
    <td colspan="2">
    <div id="footshadow">
		
        <div id="footertext">&copy; <?php print date('Y');?> <?php print $version['company']; ?></div>
        <div class="c"></div>
        
    </div>
    </td>
  </tr>
</tfoot>
</table>
</div>
</body>
</html>