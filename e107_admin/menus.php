<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(isset($_GET['configure']))
{
	//Switch to Front-end
	define("USER_AREA", true);
	//Switch to desired layout
	define('THEME_LAYOUT', $_GET['configure']);
}

require_once("../class2.php");
if (!getperms("2"))
{
	header("location:".e_BASE."index.php");
	exit;
}

//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);
e107::coreLan('menus', true);
e107::coreLan('admin', true);

// FIXME - quick temporarry fix for missing icons on menu administration. We need different core style to be included (forced) here - e.g. e107_web/css/admin/sprite.css
if(e_IFRAME) //<-- Check config and delete buttons if modifying
{

//e107::js('core','bootstrap/js/bootstrap.min.js');
//e107::css('core','bootstrap/css/bootstrap.min.css');
	e107::css('url','{e_THEME}/bootstrap/admin_style.css');

}

if(strpos(e_QUERY, 'configure') !== FALSE || vartrue($_GET['enc']))
{
	
	//e107::js('core', 	'colorbox/jquery.colorbox-min.js', 'jquery', 2);
	//e107::css('core', 	'colorbox/colorbox.css', 'jquery');
	
	//e107::js('core', 	'core/jquery.elastic.source.js', 'jquery', 2);
	
	//e107::js('core', 	'plupload/plupload.full.js', 'jquery', 2);
	//e107::css('core', 	'plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', 'jquery');
	//e107::js('core', 	'plupload/jquery.plupload.queue/jquery.plupload.queue.js', 'jquery', 2);
	
	//e107::css('core', 	'chosen/chosen.css', 'jquery');
	//e107::js('core', 	'chosen/chosen.jquery.min.js', 'jquery', 2);
	
	//e107::css('core', 	'password/style.css', 'jquery');
	//e107::js('core', 	'password/jquery.pwdMeter.js', 'jquery', 2);
	// 
	//e107::js("core",	"plupload/customUpload.js","jquery",3);
	
	//e107::js("core",	"core/mediaManager.js","jquery",3);

	
	// e107::css('core', 	'core/admin.css', 'jquery');
//	e107::js('core', 	'core/admin.jquery.js', 'jquery', 4);
 e107::js('core','bootstrap/js/bootstrap-tooltip.js');
//	e107::css('core','bootstrap/css/bootstrap.min.css');
	e107::js('inline', "
		$(function() {
						
			// Visibility Options

			$('.e-menumanager-option').on('click', function(){
    			
    			var link = $(this).attr('href');
				var caption = $(this).attr('title');
				window.parent.$('#uiModal .modal-caption').text(caption);	
				window.parent.$('#uiModal .modal-body').load(link, function(){
				 					   
				 	window.parent.$('.modal-body .e-save').on('change', function(){
					
						var target 	= window.parent.$('#e-save-form').attr('action');
						var data 	= window.parent.$('#e-save-form').serialize();
					
						$.post(target, data ,function(ret)
						{
						  	var a = $.parseJSON(ret);
					
							if(a.error)
							{
								alert(a.msg);
							}
						
						});
					
					});					   
				});
					
					
				window.parent.$('#uiModal').modal('show');
						
    			return false;
    			
			}) ;	
				
				
				
			// Delete Button (Remove Menu) Function
				
			$('.e-menumanager-delete').on('click', function(e){
			e.preventDefault();
			var area = 'remove';
			var remove = $(this).attr('id');
			var opt = remove.split('-');
			var hidem = '#block-' + opt[1] +'-' + opt[2];
			$(hidem).hide('slow');
			// alert(hidem);
			$.ajax({
				  type: 'POST',
				  url: 'menus.php',
				  data: { removeid: remove, area: area, mode: 'delete' }
		
				}).done(function( data ) {
					
					var a = $.parseJSON(data);
					
					if(a.error)
					{
						alert(a.msg);
					}
				});		
			});
				
				
							
				
				
	  	});
		
	");
	
	
	e107::css('inline',"	.column { width:100%;  padding-bottom: 100px; }
	.regularMenu { border:1px dotted silver; margin-bottom:6px; padding-left:3px; padding-right:3px }
	
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; cursor:move }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 7px; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
	
	
	
	
	
	
	/* A little bit of bootstrap styling - loading /bootstrap.css could break some themes */
	


	
	.menu-btn {
    display: inline-block;
    padding: 4px 12px;
    margin-bottom: 0px;
    font-size: 14px;
    line-height: 20px;
    color: rgb(51, 51, 51);
    text-align: center;
    text-shadow: 0px 1px 1px rgba(255, 255, 255, 0.75);
    vertical-align: middle;
    cursor: pointer;
    background-color: rgb(245, 245, 245);
    background-image: linear-gradient(to bottom, rgb(255, 255, 255), rgb(230, 230, 230));
    background-repeat: repeat-x;
    border-width: 1px;
    border-style: solid;
    -moz-border-top-colors: none;
    -moz-border-right-colors: none;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    border-image: none;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgb(179, 179, 179);
    border-radius: 4px 4px 4px 4px;
    box-shadow: 0px 1px 0px rgba(255, 255, 255, 0.2) inset, 0px 1px 2px rgba(0, 0, 0, 0.05);
	}
	
	.menu-btn:hover, .menu-btn:focus, .menu-btn:active, .menu-btn.active, .menu-btn.disabled, .menu-btn[disabled] {
    	color: rgb(51, 51, 51);
    	background-color: rgb(230, 230, 230);
	}
	
	.menu-btn:hover, .menu-btn:focus {
    color: rgb(51, 51, 51);
    text-decoration: none;
    background-position: 0px -15px;
    transition: background-position 0.1s linear 0s;
	}
	
	
	.menu-btn-primary {
    color: rgb(255, 255, 255);
    text-shadow: 0px -1px 0px rgba(0, 0, 0, 0.25);
    background-color: rgb(0, 109, 204);
    background-image: linear-gradient(to bottom, rgb(0, 136, 204), rgb(0, 68, 204));
    background-repeat: repeat-x;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
	}
	
	.tbox { text-align:left }
	
	.menuOptions {
    padding-top: 7px;
    padding-right: 5px;
    text-align: left;
    white-space: nowrap;
	}
	
	select.menu-btn { text-align:left }
	
	label { font-size: 12px;  line-height:14px }
	label.input							{margin-right:10px }
	
	#core-menumanager-main 						{ width:100%; margin-left:auto; margin-right:auto }
	
	
	table.table 								{ width: 95% ; margin-left:auto; margin-right:auto; }
	
	label.selection-row  						{ padding:6px ; cursor: pointer; width:90%}
	
	table.table tbody > tr >td 					{  }
	table.table tbody > tr >td label 			{ padding:15px; display:block; cursor: pointer; font-size:14px ;  }
	table.table tbody > tr >td label > input	{ margin-right: 10px; }
	
	
	.table-striped tbody > tr:nth-child(2n+1) > td, .table-striped tbody > tr:nth-child(2n+1) > th {
    	background-color: rgb(249, 249, 249);
	}
	
	.menu-panel {
    min-height: 20px;
    padding: 19px;
    margin-bottom: 20px;
    background-color: rgb(245, 245, 245);
    border: 1px solid rgb(227, 227, 227);
    border-radius: 4px 4px 4px 4px;
    box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.05) inset;
}
	
	.menu-panel-header
	 {
    display: block;
    padding: 3px 15px;
    font-size: 11px;
    font-weight: bold;
    line-height: 20px;
    color: rgb(153, 153, 153);
    text-shadow: 0px 1px 0px rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
	
	}
	
	",'jquery');
	
	
}





$e_sub_cat = 'menus';

require_once(e_HANDLER."file_class.php");
require_once(e_HANDLER."menumanager_class.php");

$rs = new form;
$frm = e107::getForm();
$men = new e_menuManager(0);   // use 1 for dragdrop.
$mes = e107::getMessage();

if(e_AJAX_REQUEST)
{
	
	if(vartrue($_GET['enc']))
	{
		$string = base64_decode($_GET['enc']);
		parse_str($string,$_GET);
		
	//	 print_a($_GET);			
	}
//	print_a($_POST);
	
	if(vartrue($_GET['vis']))
	{
		$text = $men->menuVisibilityOptions();
	}
	
	// print_a($_GET);
	
	if(vartrue($_GET['parmsId']))
	{
		$text = $men->menuInstanceParameters();
	}
		
	if(vartrue($_POST['mode']))
	{
		// print_r($_POST);
	//	$men->setMenuId($this->menuId);
		$text = $men->menuSaveAjax($_POST['mode']);
	}
		
	
		
	echo $text;
	exit;
	
}

if(isset($_GET['configure']) || isset($_GET['iframe']))
{
	//No layout parse when in iframe mod
	define('e_IFRAME', true);
}

require_once("auth.php");

if($_POST)
{
	$e107cache->clear_sys("menus_");
}


		//FIXME still used in e_HANDLER.menumanager_class.php
		if (vartrue($message) != "")
		{
			echo $ns -> tablerender('Updated', "<div style='text-align:center'><b>".$message."</b></div><br /><br />");
		}

		//BC - configure and dot delimiter deprecated
		if (!isset($_GET['configure']))
		{
			$men->menuScanMenus();
            $text = $men->menuRenderMessage();
            $text .= $men->menuSelectLayout();
			$text .= $men->menuVisibilityOptions();
			$text .= $men->menuInstanceParameters();
            $text .= $men->menuRenderIframe();
            $ns -> tablerender(ADLAN_6.SEP.LAN_MENULAYOUT, $text, 'menus_config');
		}
		else // Within the IFrame.
		{
		  	$men->menuRenderPage();

		}

// -----------------------------------------------------------------------------

require_once("footer.php");

 // -----------------------------------------------------------------------


function menus_adminmenu()
{

	// See admin_shortcodes_class.php - get_admin_menumanager()
	// required there so it can be shared by plugins.

}

?>