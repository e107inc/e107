$(document).ready(function()
    {
			var path = $("#uploader").attr("rel");
	
			$("#uploader").pluploadQueue({
	        // General settings
		        runtimes : "html5,html4",
		        url : path,
		        max_file_size : "10mb",
		        chunk_size : "1mb",
		        unique_names : false,
		 
		        // Resize images on clientside if we can
		        resize : {width : 320, height : 240, quality : 90},
		 
		        // Specify what files to browse for
		        filters : [
		            {title : "Image files", extensions : "jpg,gif,png"},
		            {title : "Zip files", extensions : "zip,gz"}
		        ]
		        ,
		         // Error reporting etc
    			preinit: attachError, 
    			setup: attachCallbacks
		    });
	
		
		
		
		
		// Attach widget callbacks
		function attachError(Uploader) {
		    Uploader.bind("FileUploaded", function(up, file, response) {
		        var data = $.parseJSON(response.response);
		        console.log("[FileUploaded] Response - " + response.response);
		        
		        if (data.error == 1) {
		            up.trigger("Error", {message: "'" + data.message + "'", file: file});
		            console.log("[Error] " + file.id + " : " + data.message);
		            return false;
		        }
		    });
		}
		
		function attachCallbacks(uploader) {
			
		    uploader.bind("Init", function(up) {
		        up.settings.multipart_params = {
		            "upload_type" : $("#uploadQueue").attr("data-uploadType"), 
		            "xref_id" : $("#uploadQueue").attr("data-xrefID"), 
		            "image_category" : $("#uploadQueue").attr("data-imageCategory")
		        };
		    });
		
		    uploader.bind("UploadComplete", function(up, files) {
		        console.log("[UploadComplete]");
			
				$(".plupload_buttons").css("display", "inline");
            	$(".plupload_upload_status").css("display", "inline");
            	$(".plupload_start").addClass("plupload_disabled");
					alert("it worked");
				up.refresh();
		   });
		   
		   
		   uploader.bind('FilesAdded', function(up, files) {
	        for (var i in files) {
	        	alert(files[i].id + ' hello');
	        	//$('#' + files[i].id).append('hello');
	        	$('#' + files[i].id).prepend("WOWWs");
	          // $('#' + files[i].id).html('<div style="height:75px;border-bottom:1px solid #666" id="' + files[i].id + '"> <small>Fichier :' + files[i].name + ' (' + plupload.formatSize(files[i].size) + ')</small><b></b><i></i><div><label>Titre <input id="' + files[i].id + 'Titre" type="text" size="25" name="media[' + files[i].id + '][titre]" value="" /></label><label>Légende <input id="' + files[i].id + 'Legende" type="text" size="25" name="media[' + files[i].id + '][legende]" value="" /></label> <label><input id="' + files[i].id + 'Marquage" type="checkbox" checked="checked" name="media[' + files[i].id + '][marquage]" value="1"/><small>Copyright Journal</small></label><input type="hidden" name="media[' + files[i].id + '][uploaded]" value="0" /></div></div>');
	        }
	      //  $('#uploadfiles').removeAttr('disabled');
	       // $('.uploadInfos .total').html(parseInt($('.uploadInfos .total').html()) + files.length );
	    	});
	    	
	    	
	    	  uploader.bind('UploadFile', function(up, file) {
		        $.extend(up.settings.multipart_params, { legende : $('#' + file.id + 'Legende').val(), titre : $('#' + file.id + 'Titre').val(), marquage :  $('#' + file.id + 'Marquage').is(':checked')});
		                
		        $('.uploadInfos .envois').html(parseInt($('.uploadInfos .envois').html()) + 1 );
		        $('#' + file.id).prepend('<img style="float:left;margin-right:5px" src="/wp-admin/images/wpspin_light.gif" alt="chargement en cours"/>');
		    });
		    
		    uploader.bind('FileUploaded', function(up, file, response) {
		        resJ = $.parseJSON(response.response);
		        $('#' + file.id + ' i').html(' ' + resJ.reduceSize + 'Kb');        
		        if (resJ.url != '' && resJ.filetype == 'image') {
		            $('#' + file.id + ' img').attr('src', resJ.url);
		            $('#' + file.id + ' img').attr('alt', 'aperçus image');
		        }
		        else if (resJ.url && resJ.filetype == 'audio') {
		            //$('#' + file.id + ' img').after('Some text <b>and bold!</b>').remove();
		        }
		        else{
		            //$('#' + file.id + ' img').attr('src', '');
		            //$('#' + file.id + ' img').attr('alt', 'vignette indisponible');
		        }
		
		        if ($("#tab-gallery").html() == null) {
		            $("#tab-type").after('<li id="tab-gallery"><a href="/wp-admin/media-upload.php?post_id={$this->_postId}&tab=gallery">Galerie de l\'article</a></type>');
		        }
		        $('#attachments-count').html(parseInt($('#attachments-count').html()) + 1);
		        up.refresh();
		    });
		
		    uploader.bind('UploadProgress', function(up, file) {
		        $('#' + file.id + ' b').html(file.percent + "%");
		    });
		    
	
		    uploader.bind('Error', function(up, err) {
		        $('#filelist').append("<div>Error: " + err.code +
		                ", Message: " + err.message +
		                (err.file ? ", File: " + err.file.name : "") +
		                "</div>"
		        ); 
		        up.refresh(); // Reposition Flash/Silverlight
		    });
	    	
	    	
		   
		}
		
});	
