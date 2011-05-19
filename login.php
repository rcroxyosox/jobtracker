<?php 
session_start();
include("_lib/php/version.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Riot Creative Imaging - Login</title>
<link href="_lib/css/style.css" rel="stylesheet" type="text/css" />
<style>

body{
	background: #f2f2f2;
	margin-top: 5%;
}
#title{
	padding: 0px;
	margin: 0px;
	height: 25px;
	padding-top: 20px;
	float: right;
	margin-top: 10px;
}
#logo{
	float: left;	
}
#logingtitle{
	padding-bottom: 8px;
	padding-top: 40px;
	border-bottom: 0px solid #dfdfdf;
	margin-bottom: 15px;
	width: 80%;
}
#content{
	width: 511px;
	text-align: left;
}
#innercontent{
	height: 314px;
	background-image: url(images/loginbg.jpg);
	background-position: center;
	background-repeat: no-repeat;
	padding: 10px;
	text-align: left;
}
#login{
	margin-top: 10px; 	
}
#loading{
	display: none;	
}
.oragehead{
	padding-right: 20px;
}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	
	// always focus first
	$('input[type=text]:eq(0)').focus();					   
						   
	
	// the loging
	$('#login').click(function(){
		// check for an associated download file
		//currurl = document.location.href;
		pagereferer = "<?php print $_SESSION['referer']; ?>";
		
		$.post('_lib/php/ajax_login.php', $("#loginform").serialize() , function(data){
			if(data.substring(0,1) == 1){
				//alert(pagereferer);
				document.location.href="index.php";
			}else{
				alert(data.substring(2));
			}
		});
		
	});

	// the loading feature
	$('#loading').ajaxStart(function(){
		$(this).show();	
		$('#login').hide();
	}).ajaxStop(function(){
		$(this).hide();	
		$('#login').show();
	});
	
	
	// detect the enter key
	$('input').focus(function(){
							  
		$(document).keypress(function(e){
			if(e.keyCode == 13){
				$('#login').trigger('click');
			}
		})					  
							  
	});
	

});
</script><script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"> </script>
</head>

<body>

<div id="content">
	
    <div id="logo"><img src="images/riotlogo.png" /></div>
    
	<div id="title" class="oragehead"><?php print $version['name'];?> 
  <span class="version">v<?php print $version['version'];?></span></div> 
	<div class="c"></div>


	<div id="innercontent">   
    <div id="logingtitle"><span class="lggreyhead">Login</span></div>
    
    <form name="loginform" id="loginform">
        <table width="80%" border="0" cellspacing="0" cellpadding="0">
          <tr>
            <td>
            <label>username<br />
            <input name="username" id="username" type="text" class="input" /></label>
            </td>
          </tr>
          <tr>
            <td>
            <label>password<br />
            <input name="password" id="password" type="password" class="input" /></label>
            </td>
          </tr>
          <tr>
            <td>
            
            <a class="navbt" href="#" id="login"><span>login</span></a>
            <div id="loading"><img src="images/ajax-loader.gif" width="24" height="24" /></div>
            
            </td>
          </tr>
        </table>
    </form>
    
    </div>
</div>

</body>
</html>