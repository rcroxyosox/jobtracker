$(document).ready(function(){
						   
	/**********************************/
	/********* add prospect ***********/
	/**********************************/
	
	$('#targetdate, #dateopened, #dateclosed').datepicker();

	
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
			urlstr += (eid.length > 0)?'&e='+eid:'';
			urlstr = (urlstr.length > 0 )?'?'+urlstr:'';
			var filename = '';
			
			
			$thisobj.upload('blocks/addedit_prospect/php/ajax_uploadquote.php'+urlstr, function(res) {            
			
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
			$.getJSON('blocks/addedit_prospect/php/ajax_excelread.php?file='+filename, function(data) {

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
		
		$.post("blocks/addedit_prospect/php/ajax_removequote.php", { 'uploadedfile':uploadedfile, 'e':eid } , function(data){
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
	if(startedfile.length > 0){
		$('#fileoptions').show();
		$('#fileinput').hide();
	}else{
		$('#fileoptions').hide();
		$('#fileinput').show();
	}

	//get the file extensions
	getExts = function(){
		$.get('blocks/addedit_prospect/php/ajax_uploadquote.php', { 'getexts':1 } , function(data){
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
		
		$.post("blocks/addedit_prospect/php/ajax_addeditlead.php", $("#addleadform").serialize(), function(data){
			
			
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
					$('#editp').attr({'href':'index.php?action=edit&e='+insertedId+'&title=edit prospect'});	
				}else{
					$('#editp').hide();
				}
				
			}else{
				alert(data);	
				$('#addeditbt').show();
			}
			
		});
		return false;
	});

	//fix the estimated field
	$('#estimated').blur(function(){
		str = $(this).val();
		$(this).val(str.replace("$",""));
		
		if(isNaN(str)){
			$(this).val('');
			alert('Only numbers are allowed in this field');
		}
		
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
			$reason.val($('#hiddenreason').val());
			$reason.keyup(function(){
				if(loggedin.length > 0){
					$('#reason_repid').val(loggedin);
				}						   
			});
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


	/**********************************/
	/**********************************/
	/**********************************/
	
	
});