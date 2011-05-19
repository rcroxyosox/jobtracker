/***
@title:
Fixed Center

@version:
1.2

@author:
David Tang

@date:
2010-06-27 - updated plugin to use fixed positioning instead of absolute
2010-06-17 - released version 1 of the plugin

@url
www.david-tang.net

@copyright:
2010 David Tang

@requires:
jquery

@does:
This plugin centers an element on the page using fixed positioning and keeps the element centered 
if you scroll horizontally or vertically. Tested in IE7, IE8, Chrome, Safari, and Firefox

@howto:
jQuery('#my-element').fixedCenter(); would center the element with ID 'my-element' using fixed positioning 

*/

jQuery.fn.fixedCenter = function(){
	return this.each(function(){
		var element = jQuery(this);
		centerElement();
		jQuery(window).bind('resize',function(){
			centerElement();
		});
			
		function centerElement(){
			var elementWidth = jQuery(element).outerWidth();
			var elementHeight = jQuery(element).outerHeight();
			var windowWidth = jQuery(window).width();
			var windowHeight = jQuery(window).height();	
			
			
			// if the element is bigger than the window
			addToTop = (elementHeight > windowHeight)?elementHeight - windowHeight:0;
			
			var X2 = windowWidth/2 - elementWidth/2;
			var Y2 = windowHeight/2 - elementHeight/2;
	 		
			jQuery(element).css({
				'left':X2,
				'top':(Y2+addToTop),
				'position':'absolute'
				//'position':'fixed'
			});						
		} //end of centerElement function
					
	}); //end of return this.each
}


jQuery.fn.center = function (options) {
	
		var defaults = {
			addtotop : 0
		};
		
		var options = $.extend(defaults, options); 
	
    this.css("position","absolute");
    this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + options.addtotop + "px");
    this.css("left", "50%");
		this.css("margin-left","-"+((this.width())/2)+"px");
		
		fromtop = parseInt(this.css('top'));
		if(fromtop < 0){
			this.css({'top':40});
		}		
		
    return this;
}
