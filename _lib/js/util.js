$(document).ready(function(){


	// get url hash
	$.extend({
		getUrlVars: function(){
			var vars = [], hash;
			var hashes = window.location.href.slice(window.location.href.indexOf('#') + 1).split('&');
			for(var i = 0; i < hashes.length; i++)
			{
				hash = hashes[i].split('=');
				vars.push(hash[0]);
				vars[hash[0]] = hash[1];
			}
			return vars;
		},
		getUrlVar: function(name){
			return $.getUrlVars()[name];
		}
	});

	// replace null with a charactor
	nullToStr = function(val, str){
		if($.trim(val) == ""){
			return str;
		}else{
			return val;
		}
	}
	
	
	// get a javascrot url (ie. #23)
	getJURL = function(jurlvar){
		str = document.location.href;
		return str.substring(str.lastIndexOf(jurlvar+"=")+(jurlvar+"=").length);		
	}	

	// fix the borders
	$.each($('#innertable tr:not(tr eq(0))'), function(){
		$('td:first',$(this)).css({'border-left':'none'});
		$('td:last',$(this)).css({'border-right':'none'});
	});

	//add the hover effect to all table headings
	$('thead th:not(#thtitle)').hover(function(){
		$(this).addClass('tablebthover');							 
	},function(){
		$(this).removeClass('tablebthover');
	});
	
	
	// get the tagname
	$.fn.tagName = function() {
		return this.get(0).tagName;
	}


	// the estimated functions
	disable_est_high = function(){
		$('#estimated_high').css({'opacity':0.4}).attr({'readonly':'readonly'}).val('');
	}
	enable_est_high = function(){
		$('#estimated_high').css({'opacity':1}).attr({'readonly':false});
	}
	
	$('#estimated_low').keypress(function(){
		if($(this).val().length > 0 && !isNaN($(this).val())){
			enable_est_high();
		}else{
			disable_est_high();
		}
	})
	
	checkEst = function($which){
		tv = $which.val(); 
		okay = true;
		if(tv.length > 0){	
			if(isNaN(tv)){
				$which.val('');
				okay = false;
			}else{
				$which.val(parseFloat(tv).toFixed(2));	
			}
			
			
			lv = parseFloat($('#estimated_low').val());
			hv = parseFloat($('#estimated_high').val());
			
			if(lv > hv || hv < lv){
				alert("low and high value range doesn't make sense");
				$which.val('');
				okay = false;
			}
			
		}else{
			okay = false;	
		}
		return okay;
	}
	
	
	$('#estimated_low').blur(function(){
		checkEst($(this));
	});
	
	$('#estimated_high').blur(function(){
		if(!checkEst($(this))){
			disable_est_high();	
		};
		
	}).click(function(){
		$('#estimated_low').trigger('keypress');
	});
	
	$('#estimated_low, #estimated_high').trigger('blur');
	

});// JavaScript Document