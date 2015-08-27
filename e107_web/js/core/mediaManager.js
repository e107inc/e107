$(document).ready(function()
{
	
	
	
	
	$(".e-media-attribute").keyup(function () {  
		
		eMediaAttribute();	
	});
	
	$("#float").change(function () {  
		
		eMediaAttribute();	
	});
	
	
	
	
	function eMediaAttribute(e, bbcode)
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

									
		if(margin_right !='' && margin_right !== undefined)
		{				
			style  = style + 'margin-right:' + margin_right + 'px;';	
		}
		
		if(margin_left !='' && margin_left !== undefined)
		{				
			style  = style + 'margin-left:' + margin_left + 'px;';	
		}
		
		if(margin_top !='' && margin_top !== undefined)
		{				
			style  = style + 'margin-top:' + margin_top + 'px;';	
		}
		
		if(margin_bottom !='' && margin_bottom !== undefined)
		{				
			style  = style + 'margin-bottom:' + margin_bottom + 'px;';	
		}

		if(_float =='left' || _float =='right')
		{				
			style  = style + 'float:' + _float + ';';	
		}
		
		if(width === undefined)
		{
			width = '';	
		}
		
		if(height === undefined)
		{
			height = '';	
		}
		
		// Set the Html / Wysiwyg Value.
		var html = '<img class="img-rounded" style=\"' + style + '\" src=\"'+ src +'\" alt=\"\" width=\"' + width + '\" height=\"' + height + '\" />'; 
		$('#html_holder').val(html);  
		
		
		// Only Do width/height styling on bbcodes --
		if(width !='' && width !== undefined)
		{				
			style  = style + 'width:' + width + 'px;';	
		}

		if(height !='' && height !== undefined)
		{				
			style  = style + 'height:' + height + 'px;';	
		}	
		
		if(bbcode != 'video')
		{
			bb = '[img';
			
			if(style !='')
			{
				bb = bb + ' style='+style;			
			}
			
			bb = bb + ']';
			bb = bb + path;
			bb = bb + '[/img]';
			$('#bbcode_holder').val(bb); // Set the BBcode Value. 
		}		
		
		
				
			//	var html = '<img style=\"' + style + '\" src=\"'+ src +'\" />'; 

	}
	
		
		
		
		
				// $(".e-media-select").click(function () {  
    $(document).on("click", ".e-media-select", function(){
  	 		
  	 	
    		//	console.log(this);
    	

				var id			= $(this).attr('data-id'); // id of the mm item
				var target 		= $(this).attr('data-target');
				var path		= $(this).attr('data-path'); // path of the mm item
				var preview 	= $(this).attr('data-preview');
				var src			= $(this).attr('data-src');
				var bbcode		= $(this).attr('data-bbcode'); // TinyMce/Textarea insert mode
				var name		= $(this).attr('data-name'); // title of the mm item
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
				//	alert(bbpath);	//FIXME bbcode -  Insert into correct caret in text-area. 
					return;	
			//		$('input#' + target, window.top.document).attr('value',path);	// set new value	
			//		$('textarea#' + target, window.top.document).attr('value',bbpath);	
				}
				
			//	if(bbcode == 'wysiwyg')
				{
					//alert('hello');
				}
				
				if(bbcode == "video" || bbcode == 'glyph')
				{
					
					bbpath = '['+bbcode+']'+ path + '[/' + bbcode + ']';
					$('#bbcode_holder').val(bbpath);	
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
					preview = "<span class='" + src + "'>&nbsp;</span>";
					$('#html_holder').val(preview) + '&nbsp;';
					$('#path').attr('value',path);		
				}	
				else if(type == 'file')
				{
					preview = name;	
				}
				else // image and video
				{
					eMediaAttribute(this,bbcode);	
					preview = $('#html_holder').val();
				}
				
				
				$('div#' + target + "_prev", window.top.document).html(preview); // set new value
				$('span#' + target + "_prev", window.top.document).html(preview); // set new value
							
				// @see $frm->filepicker()
				if(target !='')
				{
					if($('input#' + target)!== undefined)
					{
						$('input#' + target , window.top.document).attr('value',path); // set new value	
					}
					
					
					// array mode : 
					var pathTarget = target + '-path';
					var nameTarget = target + '-name';
					var idTarget = target + '-id';
					
					
					if($('input#' + pathTarget)!== undefined)
					{
				    	$('input#' + pathTarget , window.top.document).attr('value',path); // set new value	   
					}
					
					if($('input#' + nameTarget)!== undefined)
					{
				    	$('input#' + nameTarget , window.top.document).attr('value',name); // set new value	   
					}
					
					if($('input#' + idTarget)!== undefined)
					{
				        $('input#' + idTarget , window.top.document).attr('value',id); // set new value	   
					}
					
				}
				
			
			
			
			//	$(this).parent('#src').attr('value',preview); // set new value
			//	$(this).parent('#preview').attr('src',preview);	 // set new value

			return false;
				
	}); 	
	
	
	// Must be defined  after e-media-select
    $(document).on("click", ".e-dialog-save", function(){// FIXME TODO missing caret , text selection overwrite etc.
					
		var newval 	= $('#bbcode_holder').val();
		var target 	= $(this).attr('data-target');
		var bbcode	= $(this).attr('data-bbcode'); // TinyMce/Textarea insert mode
		var close 	= $(this).attr('data-close');
					
		if(!target || !bbcode){ return true; }
		
		$('#' + target, window.top.document).atCaret('insert', newval); // http://code.google.com/p/jquery-at-caret/wiki/GettingStarted
		
		if(close == 'true')
		{
			parent.$('.modal').modal('hide');	
		}
		
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
		
		
		// Ajax keyup search. Used by media-browser. 
		
		var delay = (function(){
		  var timer = 0;
		  return function(callback, ms){
		    clearTimeout (timer);
		    timer = setTimeout(callback, ms);
		  };
		})();
		
		
		$(".e-ajax-keyup").keyup(function(){
			
			var id 		= $(this).attr("data-target");
			var src 	= $(this).attr("data-src");
			var search 	= $(this).val();

			if(search !== null)
  			{
  			    search  = search.replace('https://','url:');
                search  = search.replace('http://','url:');
        		src     = src + '&search=' + encodeURIComponent(search);
  			}

  		//	alert(src);
  		
  			  $('#'+id).fadeOut('fast');
  		
  			 delay(function(){
     					  
			//	if((search.length) >= 3) {
					$('#'+id).load(src,function() {
		  				// alert(src);
		  				
		    			 $('#'+id).fadeIn('fast'); // .slideLeft();
					});
			//	}

   			 }, 300 );
  		
  			
		});
		
	
	
		function mediaNav(e,navid)
		{
			var id 			= $(e).attr("href");
  			
  			var target 		= $(e).attr("data-target"); // support for input buttons etc. 
  			var loading 	= $(e).attr('data-loading'); // image to show loading.
  			var search 		= $('#media-search').val(); // image to show loading.  
			var nav			= $(e).attr('data-nav-inc');
			var dir         = $(e).attr('data-nav-dir');
            var curTotal    = $('#admin-ui-media-select-count-hidden').attr('data-media-select-current-limit');
            var total       = $(e).attr('data-nav-total');



  			if(nav !== null && navid !==null)
  			{
  				eNav(e,navid);	
  			}

            if(dir == 'down' && curTotal == 20)
            {
              //  $('#admin-ui-media-nav-down').prop("disabled",false);
                return false;
            }

            if(dir == 'up' && curTotal == total)
            {
            // $('#admin-ui-media-nav-up').prop("disabled",false);
                return false;
            }

  			if(target !==  null)
  			{			
  				id = '#' + target; 
  			}

  			if(loading != null)
  			{
  				$(id).html("<img src='"+loading+"' alt='' />");
  			}

            var src = $(e).attr("data-src"); // heep here.
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

  			if(dir == 'down')
            {
                outDir = 'right';
                inDir = 'left';

            }
            else
            {

                outDir = 'left';
                inDir = 'right';
            }

            $('#e-modal-loading', window.parent.document).show();
            $('iframe', window.parent.document).attr('scrolling', 'no'); // hide the scrollbar. 



            $.get(src, function( data ) {

                $(id).hide('slide', { direction: outDir }, 1200, function(){

                    //   alert('done');
                    $(id ).html( data );
                    newVal = $('#admin-ui-media-select-count-hidden').text();
                    $('#admin-ui-media-select-count').text(newVal).fadeIn();

                    $(id).show('slide', { direction: inDir },1200,function(){
                        $('#e-modal-loading', window.parent.document).hide();


                    });

                });



            });


            $('iframe', window.parent.document).attr('scrolling', 'auto');

            return false;
            

            /*

  			//TODO Animate. 
  			$(id).load(src,function() {
  				// alert(src);
  			//	 $(id).fadeIn('fast');
                $(id).show('slow');
    			// $(this).show('slow'); // ;
			});
				
			*/
			
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