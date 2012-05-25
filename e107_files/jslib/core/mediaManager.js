$(document).ready(function()
{
	
	$(".e-dialog-save").click(function () { // FIXME TODO missing caret , text selection overwrite etc. 
		
		var newval = $('#bbcode_holder').val();
		var target 	= $(this).attr('data-target');
		('#' + target, window.top.document).insertAtCaret(newVal);
		
	//	$('#' + target, window.parent.document).append(newval);	//FIXME caret!!
	//	var t = $('#' + target, window.parent.document).text();
	//	addtext(newval);
		// alert(newval);
	});
	
	
	$(".e-media-attribute").keyup(function () {  
		
		eMediaAttribute();	
	});
	
	function eMediaAttribute(e)
	{		
		var style 		= '';
		var bb 			= '';
		
		var target 		= $(e).attr('data-target');
	//	var path		= $(e).attr('data-path');
	//	var preview 	= $(e).attr('data-preview');
	//	var src			= $(e).attr('data-src');
		
		var src 			= $('#src').attr('value'); // working old
		var path 			= $('#path').attr('value'); // working old
		var preview 		= $('#preview').attr('value'); // working old
		
		var width 			= $('#width').val();	
		var height			= $('#height').val();			
		var margin_top 		= $('#margin-top').val();				
		var margin_bottom 	= $('#margin-bottom').val();	
		var margin_right 	= $('#margin-right').val();	
		var margin_left 	= $('#margin-left').val();	
						
		if(width !='')
		{				
			style  = style + 'width:' + width + 'px;';	
		}

		if(height !='')
		{				
			style  = style + 'height:' + height + 'px;';	
		}				
					
		if(margin_right !='')
		{				
			style  = style + 'margin-right:' + margin_right + 'px;';	
		}
		
		if(margin_left !='')
		{				
			style  = style + 'margin-left:' + margin_left + 'px;';	
		}
		
		if(margin_top !='')
		{				
			style  = style + 'margin-top:' + margin_top + 'px;';	
		}
		
		if(margin_bottom !='')
		{				
			style  = style + 'margin-bottom:' + margin_bottom + 'px;';	
		}
		
		bb = '[img';
		
		if(style !='')
		{
			bb = bb + ' style='+style;			
		}
		
		bb = bb + ']';
		bb = bb + path;
		bb = bb + '[/img]';
				
		$('#bbcode_holder').val(bb);
		//	document.getElementById('bbcode_holder').value = bb;
				
			//	var html = '<img style=\"' + style + '\" src=\"'+ src +'\" />'; 
		var html = '<img style=\"' + style + '\" src=\"'+ src +'\" alt=\"\" width=\"' + width + '\" height=\"' + height + '\"/>'; 

		$('#html_holder').val(html);
	}
	
		
		
		
		
				// $(".e-media-select").click(function () {  
		$(".e-media-select").live("click", function(){
  	
    	
    		//	console.log(this);
    			
		
				
				var target 		= $(this).attr('data-target');
				var path		= $(this).attr('data-path');
				var preview 	= $(this).attr('data-preview');
				var src			= $(this).attr('data-src');
						
				$(this).addClass("media-select-active");
				$(this).closest("img").addClass("active");
									
				$('#' + target, window.top.document).attr('value',path);	// set new value
				$('#' + target + "_prev", window.top.document).attr('src',preview); // set new value
								
				$('#src').attr('value',src); // working old
				$('#preview').attr('src',preview);	// working old
				
				$('#path').attr('value',path); // working old
				$('#src').attr('src',src);	// working old
				
				
				eMediaAttribute(this);	
			//	$(this).parent('#src').attr('value',preview); // set new value
			//	$(this).parent('#preview').attr('src',preview);	 // set new value

			return true;
				
	}); 			
});	