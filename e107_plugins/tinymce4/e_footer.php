<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

$pref = e107::getPref();

if((e107::wysiwyg() === true && check_class($pref['post_html'])) || strpos(e_SELF,"tinymce4/admin_config.php") )
{
	if(e_PAGE != 'image.php')
	{
		//e107::js('tinymce','tiny_mce.js','jquery');
		//e107::js('tinymce','wysiwyg.php','jquery',5);
		
		e107::js('footer', "https://tinymce.cachefly.net/4.0/tinymce.min.js"); // 4.1 and 4.2 have issues with saving under Firefox.  http://www.tinymce.com/develop/bugtracker_view.php?id=7655
		e107::js('footer',e_PLUGIN.'tinymce4/wysiwyg.php','jquery',5);

		// Add to e107_config.php to view hidden content when TinyMce not saving correctly
		if(deftrue('e_TINYMCE_DEBUG'))
		{
			e107::js('footer-inline', '


				window.onload = function () {

				$("textarea.e-wysiwyg").css("display","block");
				$("textarea.e-wysiwyg").css("visibility","inherit");

				}
			');
		}
		
	}
	else
	{
		e107::js('tinymce4','plugins/compat3x/tiny_mce_popup.js');
	//	e107::js('tinymce','tiny_mce_popup.js','jquery');
	}
	
	if(ADMIN)
	{
	    $insert = "$('#'+id).after('<div>";
	    $insert .= "<a href=\"#\" id=\"' + id + '\" class=\"e-wysiwyg-toggle btn btn-xs btn-default btn-inverse btn-mini\">Switch to bbcode<\/a>";
        
	     if(e_PAGE == 'mailout.php')
        {
            $insert .= "&nbsp;&nbsp;<a href=\"#\" class=\"btn btn-mini tinyInsert\" data-value=\"|USERNAME|\" >".LAN_MAILOUT_16."<\/a>";
            $insert .= "<a href=\"#\" class=\"btn btn-mini tinyInsert\"     data-value=\"|DISPLAYNAME|\" >".LAN_MAILOUT_14."<\/a>";
            $insert .= "<a href=\"#\" class=\"btn btn-mini tinyInsert\"     data-value=\"|SIGNUP_LINK|\" >".LAN_MAILOUT_17."<\/a>";
            $insert .= "<a href=\"#\" class=\"btn btn-mini tinyInsert\"     data-value=\"|USERID|\" >".LAN_MAILOUT_18."<\/a>";           
        }
        
	    $insert .= "</div>');";
        
		define("SWITCH_TO_BB",$insert);	
	
    }
	else 
	{
		define("SWITCH_TO_BB","");
	}
    	
//	print_a($_POST);
	
	// <div><a href='#' class='e-wysiwyg-switch' onclick=\"tinyMCE.execCommand('mceToggleEditor',false,'".$tinyMceID."');expandit('".$toggleID."');\">Toggle WYSIWYG</a></div>
	

	e107::js('footer-inline',"

	<!---- TinyMCE Footer Inline Code -->

	$(function() {


			$('.e-wysiwyg').each(function() {

				var id = $(this).attr('id'); // 'e-wysiwyg';
				console.log(id);
				".SWITCH_TO_BB."
		    //	alert(id);
		     	$('#bbcode-panel-'+id+'--preview').hide();

			});

			$('.tinyInsert').click(function() {

                var val = $(this).attr('data-value');
                top.tinymce.activeEditor.execCommand('mceInsertContent',0,val);
                return false;
            });



         /*
            $('img.tinyInsertEmote').live('click',function() {

                         var src = $(this).attr('src');
                  //         alert(src);
                     //  var html = '<img src=\''+src +'\' alt=\'emote\' />';
                       tinyMCE.execCommand('mceInsertRawHTML',false, 'hi there');
                       ;
                       $('.mceContentBody', window.top.document).tinymce().execCommand('mceInsertContent',false,src);

                      //   tinyMCE.selectedInstance.execCommand('mceInsertContent',0,src);

                         $('#uiModal').modal('hide');
                         return true;
                     });
            */




			// When new tab is added - convert textarea to TinyMce.
			$('.e-tabs-add').on('click',function(){

				alert('New Page Added'); // added for delay - quick and dirty work-around. XXX fixme

				var idt = $(this).attr('data-target'); // eg. news-body
				var ct = parseInt($('#e-tab-count').val());
				var id = idt + '-' + ct;
				$('#bbcode-panel-'+id+'--preview').hide();
				".SWITCH_TO_BB."
				top.tinymce.activeEditor.execCommand('mceAddControl', false, id);
			});


		 	$('a.e-wysiwyg-toggle').toggle(function(){

		 			var id = $(this).attr('id'); // eg. news-body

		 			$('#bbcode-panel-'+id+'--preview').show();
		 			$(this).text('Switch to wysiwyg');

		             tinymce.EditorManager.execCommand('mceRemoveEditor',true, id); //v4.x

		         //	tinymce.remove('#'+id);
		        //   tinymce.activeEditor.execCommand('mceRemoveControl', false, id);
		         //  $('#'+id).tinymce().remove();

			}, function () {
					 var id = $(this).attr('id');
					 $('#bbcode-panel-'+id+'--preview').hide();
					 $(this).text('Switch to bbcode');
					 tinymce.EditorManager.execCommand('mceAddEditor',true, id); //v4.x
				//	 tinymce.remove('#'+id);
		        //    tinymce.activeEditor.execCommand('mceAddControl', false, id);
			});


			 $(document).on('click','.e-dialog-save', function(){
			//	var html = $('#html_holder').val();

				var s = $('#bbcode_holder').val();

			//	alert(s);

				var p = $.ajax({
					type: 'POST',
					url: '".e_PLUGIN_ABS. "tinymce4/plugins/e107/parser.php', // parse bbcode value
					data: { content: s, mode: 'tohtml' },
					async       : false,

					dataType: 'html',
					success: function(html) {
				      return html;
				    }


				}).responseText;

				html = p;
				//		alert(s);

				if(html === undefined)
				{
					return;
				}

			//	tinyMCE.execCommand('mceInsertContent',false,html);
				top.tinymce.activeEditor.execCommand('mceInsertRawHTML',false,html);
				top.tinymce.activeEditor.windowManager.close();

			});

			$('.e-dialog-close').click(function(){

		//	top.tinymce.activeEditor.windowManager.close();
			});






	});




	","jquery", 1);


}

?>