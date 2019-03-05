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


if((e107::wysiwyg(null, true) === 'tinymce4' && check_class($pref['post_html'])) || strpos(e_SELF,"tinymce4/admin_config.php") )
{
	if(e_PAGE != 'image.php')
	{
	//	e107::js('footer', "https://tinymce.cachefly.net/4.3/tinymce.min.js");

	//	e107::js('footer', '//cdn.tinymce.com/4/tinymce.min.js');

	//	e107::js('footer', 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.5.3/tinymce.min.js');

	//	e107::js('footer', 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.11/tinymce.min.js');
		/**
		 * tinymce 4.7.10 and newer do not work. 
		 * Looks like an issue introduced with 4.7.10
		 * Reverting back to 4.7.9 makes everything work in e107
		 * Issue #3136
		 */
		e107::js('footer', 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.7.9/tinymce.min.js');


	//	e107::js('footer', "//cdn.tinymce.com/4/tinymce.min.js");

		$mceScript = e_PLUGIN.'tinymce4/wysiwyg.php';

		if(defined('e_TINYMCE_TEMPLATE'))
		{
			$mceScript .= '?config='.e_TINYMCE_TEMPLATE.'&other=';
		}

		e107::js('footer',$mceScript,'jquery',5);

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
//	else
	{
	//	e107::js('tinymce4','plugins/compat3x/tiny_mce_popup.js');
	//	e107::js('tinymce','tiny_mce_popup.js','jquery');
	}
	
	if(ADMIN)
	{
	    $insert = "$('#'+id).after('<div>";


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
				".SWITCH_TO_BB."
		     	$('#bbcode-panel-'+id+'--preview').hide();

			});
			
			$('#media-manager-submit-buttons').show();


			$('.tinyInsert').click(function() {

                var val = $(this).attr('data-value');
                top.tinymce.activeEditor.execCommand('mceInsertContent',0,val);
                return false;
            });


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
		//		alert(p);

				if(html === undefined)
				{
					return;
				}

			//	tinyMCE.execCommand('mceInsertContent',false,html);
				top.tinymce.activeEditor.execCommand('mceInsertRawHTML',false,html);
				top.tinymce.activeEditor.windowManager.close();

			});

			$('.e-dialog-cancel').click(function(){

				top.tinymce.activeEditor.windowManager.close();

			});






	});




	","jquery", 1);


}

