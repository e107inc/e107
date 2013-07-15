$(document).ready(function()
{
	
	$(".e-dialog-save").live("click", function(){// FIXME TODO missing caret , text selection overwrite etc. 
					
		var newval 	= $('#bbcode_holder').val();
		var target 	= $(this).attr('data-target');
		var bbcode	= $(this).attr('data-bbcode'); // TinyMce/Textarea insert mode
			
		if(!target || !bbcode){ return true; }

		$('#' + target, window.top.document).atCaret('insert', newval); // http://code.google.com/p/jquery-at-caret/wiki/GettingStarted
		
		
		//var cursorIndex = $('#' + target, window.top.document).attr("selectionStart");
		//var lStr =  $('#' + target, window.top.document).attr('value').substr(0,cursorIndex) + " " + newval + " ";
		//var rStr = $('#' + target, window.top.document).attr('value').substr(cursorIndex);
	
		//$('#' + target, window.top.document).attr('value',lStr+rStr);
		//$('#' + target, window.top.document).attr("selectionStart",lStr.length);  
				
		//('#' + target, window.top.document).insertAtCaret(newVal);
		
	//	$('#' + target, window.parent.document).append(newval);	//FIXME caret!!
	//	var t = $('#' + target, window.parent.document).text();
		
		//$('#' + target, window.top.document).attr('value',newval);	// set new value
		// inserttext(newval,target);
		// alert(newval);
	});
	
	
	$(".e-media-attribute").keyup(function () {  
		
		eMediaAttribute();	
	});
	$("#float").change(function () {  
		
		eMediaAttribute();	
	});
	
	
	
	
	function eMediaAttribute(e)
	{		
		var style 			= '';
		var bb 				= '';
			
		var src 			= $('#src').attr('value'); // working old
		var path 			= $('#path').attr('value'); // working old
		var preview 		= $('#preview').attr('value'); // working old
		
		var width 			= $('#width').val();	
		var height			= $('#height').val();	
				
		var margin_top 		= $('#margin-top').val();				
		var margin_bottom 	= $('#margin-bottom').val();	
		var margin_right 	= $('#margin-right').val();	
		var margin_left 	= $('#margin-left').val();	
		var _float 			= $('#float').val();	

									
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

		if(_float =='left' || _float =='right')
		{				
			style  = style + 'float:' + _float + ';';	
		}
		
		
		
		// Set the Html / Wysiwyg Value.
		var html = '<img class="img-rounded" style=\"' + style + '\" src=\"'+ src +'\" alt=\"\" width=\"' + width + '\" height=\"' + height + '\" />'; 
		$('#html_holder').val(html);  
		
		
		// Only Do width/height styling on bbcodes --
		if(width !='')
		{				
			style  = style + 'width:' + width + 'px;';	
		}

		if(height !='')
		{				
			style  = style + 'height:' + height + 'px;';	
		}	
		

		bb = '[img';
		
		if(style !='')
		{
			bb = bb + ' style='+style;			
		}
		
		bb = bb + ']';
		bb = bb + path;
		bb = bb + '[/img]';
				
		$('#bbcode_holder').val(bb); // Set the BBcode Value. 
				
			//	var html = '<img style=\"' + style + '\" src=\"'+ src +'\" />'; 

	}
	
		
		
		
		
				// $(".e-media-select").click(function () {  
		$(".e-media-select").live("click", function(){
  	 		
    		//	console.log(this);

				var id			= $(this).attr('data-id');
				var target 		= $(this).attr('data-target');
				var path		= $(this).attr('data-path');
				var preview 	= $(this).attr('data-preview');
				var src			= $(this).attr('data-src');
				var bbcode		= $(this).attr('data-bbcode'); // TinyMce/Textarea insert mode
				var name		= $(this).attr('data-name');
				var width		= $(this).attr('data-width');
				var height		= ''; // disable for now - will be updated by bb parser. // $(this).attr('data-height');		
				var type		= $(this).attr('data-type');
			
			//	return;
			//	alert(width);			
						
				$(this).addClass("media-select-active");
				$(this).closest("img").addClass("active");			
				
				if(bbcode == "file") // not needed for Tinymce
				{						
					bbpath = '[file='+ id +']'+ name + '[/file]';	
					$('#bbcode_holder').val(bbpath);		
					alert(bbpath);	//FIXME bbcode -  Insert into correct caret in text-area. 
					return;	
			//		$('input#' + target, window.top.document).attr('value',path);	// set new value	
			//		$('textarea#' + target, window.top.document).attr('value',bbpath);	
				}
				
			//	if(bbcode == 'wysiwyg')
				{
					//alert('hello');
				}
				
				if(bbcode == "img")
				{

					// bbpath = '['+bbcode+']'+ path + '[/' + bbcode + ']';
					//alert(bbpath);		
				}
				
				


				$('#src').attr('value',src); // working old
				$('#preview').attr('src',preview);	// working old
				
				$('#path').attr('value',path); // working old
				$('#src').attr('src',src);	// working old
				
				$('#width').val(width);	
				$('#height').val(height);		
				
			
			
				
				
				$('img#' + target + "_prev", window.top.document).attr('src',preview); // set new value
					
					
				if(type == 'glyph')
				{
					preview = "<i class='" + src + "'></i>";
					$('#html_holder').val(preview);
					$('#path').attr('value',path);		
				}	
				else
				{
					eMediaAttribute(this);	
					preview = $('#html_holder').val();
				}
				
				
				$('div#' + target + "_prev", window.top.document).html(preview); // set new value
				$('span#' + target + "_prev", window.top.document).html(preview); // set new value
							
				// see $frm->filepicker()
				if(target !='')
				{
					$('input#' + target , window.top.document).attr('value',path); // set new value	
				}
				
			
			
			
			//	$(this).parent('#src').attr('value',preview); // set new value
			//	$(this).parent('#preview').attr('src',preview);	 // set new value

			return false;
				
	}); 	
	
	

	
	
	
	$(".e-media-nav").click(function(){
			
			return mediaNav(this,'.e-media-nav');
			/*
  			var id = $(this).attr("href");
  			var src = $(this).attr("data-src");
  			var target = $(this).attr("data-target"); // support for input buttons etc. 
  			var loading = $(this).attr('data-loading'); // image to show loading.
  			var search = $('#media-search').val(); // image to show loading.  
				
  			if(target != null)
  			{			
  				id = '#' + target; 
  			}
  						
  			if(loading != null)
  			{
  				$(id).html("<img src='"+loading+"' alt='' />");
  			}
  					
  			if(src === null) // old way - href='myscript.php#id-to-target
  			{
  				var tmp = src.split('#');
  				id = tmp[1];
  				src = tmp[0];	
  			}
  			
  			if(search !== null)
  			{
  				src = src + '&search='+search;	
  			}
  		//	var effect = $(this).attr("data-effect");
  		//	alert(src);
  			
  			$(id).load(src,function() {
  				// alert(src);
  			//	$(this).hide();
    		//	 $(this).SlideUp();
			});
			
			*/
		});
	
		$("#media-search").keyup(function(){
			mediaNav(this,null);
				
		});
	
	
		function mediaNav(e,navid)
		{
			var id 			= $(e).attr("href");
  			
  			var target 		= $(e).attr("data-target"); // support for input buttons etc. 
  			var loading 	= $(e).attr('data-loading'); // image to show loading.
  			var search 		= $('#media-search').val(); // image to show loading.  
			var nav			= $(e).attr('data-nav-inc');
  			
  			if(nav !== null && navid !==null)
  			{
  				eNav(e,navid);	
  			}
  			
  			var src 		= $(e).attr("data-src");
  				
  			if(target != null)
  			{			
  				id = '#' + target; 
  			}
  						
  			if(loading != null)
  			{
  				$(id).html("<img src='"+loading+"' alt='' />");
  			}
  					
  			if(src === null) // old way - href='myscript.php#id-to-target
  			{
  				var tmp = src.split('#');
  				id = tmp[1];
  				src = tmp[0];	
  			}
  			
  			if(search !== null)
  			{
  				src = src + '&search='+search;	
  			}
  			
  		
  			
  			//TODO Animate. 
  			$(id).load(src,function() {
  				// alert(src);
  				// $(this).hide();
    			// $(this).show('slow'); // .slideLeft();
			});
				
			
			
		}
	
	
		// ----------------- Upload --------------------------------------
		
		var upath = $("#uploader").attr("rel"),
			extImg = $("#uploader").attr("extimg"),
			extArchive = $("#uploader").attr("extarch"),
			extDoc = $("#uploader").attr("extdoc");
	
		$("#uploader").pluploadQueue({
	        // General settings
		        runtimes : "html5,html4",
		        url : upath,
		        max_file_size : "10mb",
		        chunk_size : "1mb",
		        unique_names : false,
		 
		        // Resize images on clientside if we can
		 //       resize : {width : 320, height : 240, quality : 90},
		 
		        // Specify what files to browse for
		        filters : [
		            {title : "Image files", extensions : extImg || "jpg,gif,png,jpeg"},
		            {title : "Zip files", extensions : extArchive || "zip,gz"},
		            {title : "Document files", extensions : extDoc || "pdf,doc,docx,xls,xlsm"}
		        ],
		        preinit : {
            		Init: function(up, info) {
		                //log('[Init]', 'Info:', info, 'Features:', up.features);
	            	}
	       		},
		        init : {
		        	
		        	FilesAdded: function(up, files) {
               						
	            	},
	            	FileUploaded: function(up, file, info) {  // Called when a file has finished uploading
	                	//log('[FileUploaded] File:', file, "Info:", info);
		            },
		            UploadProgress: function(up, file) {  // Called while a file is being uploaded
	               
	               		// console.log(up.total);
	                	// console.log('[UploadProgress]', 'File:', file, "Total:", up.total);
	            	},	
	            	UploadComplete: function(up, files){
	            		document.location.reload(); // refresh the page. 	
	            		
	            	}, 
		            ChunkUploaded: function(up, file, info) { // Called when a file chunk has finished uploading
		                
		                //log('[ChunkUploaded] File:', file, "Info:", info);
		               // console.log(info);
		            },	 
		            Error: function(up, args) { // Called when a error has occured
		                alert('There was an error');
		              // console.log(args);
		            }	
		        	
		        }
		    });
		    

		// -----------------------------------------------------------------


	
	
	
			
});	