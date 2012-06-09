<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }

if(e_WYSIWYG || strpos(e_SELF,"tinymce/admin_config.php") )
{
	if(e_PAGE != 'image.php')
	{
		e107::js('tinymce','tiny_mce.js','jquery');
		e107::js('tinymce','wysiwyg.php','jquery');
	}
	else
	{
		e107::js('tinymce','tiny_mce_popup.js','jquery');
	}
	
	// <div><a href='#' class='e-wysiwyg-switch' onclick=\"tinyMCE.execCommand('mceToggleEditor',false,'".$tinyMceID."');expandit('".$toggleID."');\">Toggle WYSIWYG</a></div>
	e107::js('inline',"
	
	$(function() {
		
			$('.e-wysiwyg').each(function() {
													
				var id = $(this).attr('id'); // 'e-wysiwyg';
				$('#'+id).after('<div><a href=\"#\" id=\"' + id + '\" class=\"e-wysiwyg-toggle\">Switch to BBCODE</a></div>');
		    //	alert(id);
		     	$('#bbcode-panel-'+id+'--preview').hide();
		       			
			});
				
				
		 	$('a.e-wysiwyg-toggle').toggle(function(){
		 			var id = $(this).attr('id'); // eg. news-body	
		 			$('#bbcode-panel-'+id+'--preview').show();
		 			$(this).text('Switch to WYSIWYG');
		           tinyMCE.execCommand('mceRemoveControl', false, id);
			}, function () {
					 var id = $(this).attr('id');
					 $('#bbcode-panel-'+id+'--preview').hide();
					 $(this).text('Switch to BBCODE');
		            tinyMCE.execCommand('mceAddControl', false, id);
			});	
			
			$('.e-dialog-save').click(function(){
				
				var html = $('#html_holder').val();	
			//	tinyMCE.execCommand('mceInsertContent',false,html);
				tinyMCE.execCommand('mceInsertRawHTML',false,html);
				tinyMCEPopup.close();
			});
							
					
				
			
	});
	
	
	
	
	","jquery");
	
	e107::css('inline',".e-wysiwyg { width:100% }","jquery");
	
	
  //	require_once(e_PLUGIN."tinymce/wysiwyg.php");
	if(deftrue('TINYMCE_CONFIG'))
	{
	//	$wy = new wysiwyg(TINYMCE_CONFIG);
	}
	else
	{
	//	$wy = new wysiwyg();
	}
	
	if(!strpos(e_SELF,e_ADMIN_ABS."image.php"))
	{
	//	$wy -> render();	
	}
	
}


?>