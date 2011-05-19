    
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
           <img src="../../images/ajax-loader.gif" width="24" height="24" id="loadimg" class="loadimgs" />
           <input name="quotefile" id="quotefile" type="file" class="fileinputs">
           </div>
           
            <div class="c"></div>     
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
    contact name<br />
    <input name="customer" id="customer" type="text" class="input" value="<?php print getVal('customer');?>" />    
    </label>
      </td>
    <td valign="middle">
    
    
    
      <label>
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
    </td>
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
    <input type="hidden" name="hiddenreason" id="hiddenreason" value="<?php print getVal('reason'); ?>" />
    <input type="hidden" name="reason_repid" id="reason_repid" 
    		value="<?php print (getVal('reason_repid'))?getVal('reason_repid'):$_SESSION['loggedin']; ?>" />
    </div>

    
    <div class="hidestatusoption" id="option_lost">
    <label class="reasonholder">reason for lost status<br />

	</label>  
    </div>
    
  
          
      </td>
  </tr>
  <tr>
    <td valign="top"><label >quoted by<br />
      <?php print getRepsDrop('quotedby_repid'); ?> </label></td>      
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
    <input type="hidden" name="hiddenuploaddir" id="hiddenuploaddir" value="<?php print $uploadDir;?>" />
    <?php
    if(isset($_REQUEST['e'])){
    	print '<input type="hidden" name="e" value="'.$_REQUEST['e'].'" />';
	}
	?>
    
    <img src="../../images/ajax-loader.gif" width="24" height="24" id="loadimgsend" class="loadimgs" style="float: right" />
    <a class="navbt" href="#" style="float: right; margin-right: 90px;" id="addeditbt"><span>save prospect</span></a>
    
    <div id="addmessage">Success! Database changed
     
    <a href="../add_prospect/index.php?action=add&amp;title=add prospect" class="greena">add a new prospect</a>  
    <a href="#" class="greena" id="editp">edit this prospect</a>
    <a href="../index.php" class="greena">go back to the propect list</a>
    
    </div>

    </td>
  </tr>
    </table>
</form>