<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(isset($_GET['configure']))
{
	//Switch to Front-end
	$_GET['configure'] = preg_replace('[^a-z0-9_-]','',$_GET['configure']);
	
	define("USER_AREA", true);
	define('ADMIN_AREA', false);
//	define('ADMIN_AREA', false);
	//Switch to desired layout
	define('THEME_LAYOUT', $_GET['configure']);

	if(empty($_GET['debug']))
	{
		define('e_DEBUG', false);
		define('E107_DEBUG_LEVEL', 0);
		if(function_exists('xdebug_disable'))
		{
			xdebug_disable();
		}
		@ini_set('display_errors', 0);
		error_reporting(0);
	}
	define('e_MENUMANAGER_ACTIVE', true);


}
else
{
	if(!defined('e_ADMIN_AREA'))
	{
		define('e_ADMIN_AREA', true);
		define("USER_AREA", false);
		define('ADMIN_AREA', true);
	}

	define('e_MENUMANAGER_ACTIVE', false);
}

require_once(__DIR__.'/../class2.php');


if(e_MENUMANAGER_ACTIVE === false )
{
	e107::library('load', 'bootstrap.switch');
	e107::js('footer', '{e_WEB}js/bootstrap.switch.init.js', 'jquery', 5);

	if(empty($_GET['debug']))
	{
		e107::getJs()->inlineCSS('
			body { overflow:hidden }
		');
	}
//	else
/*	{
		e107::js('footer-inline',"
			$('#menu_iframe').attr('scrolling','no');
			$('#menu_iframe').load(function() {
				var height = this.contentWindow.document.body.offsetHeight + 400 + 'px';
				$(this).css('height',height);
			});
		");
	}*/

	e107::getJs()->inlineCSS("
		.admin-left-panel { width: 315px !important; } 
		.admin-right-panel { width: 100% !important; } 
		span.sidebar-toggle-switch { display: none !important } 
		.sidebar-toggle-panel { display: initial !important } 
		.menu-manager-items          { padding-right:15px}
		.menu-manager-items div.item { padding:5px; margin:5px 0; border:1px solid rgba(255,255,255,0.3); border-radius:3px; cursor: move }
		.menu-manager-sticky {
			position: fixed;
		padding-left: 15px;
		padding-right: 15px;
		left: 0;
		top: 60px;
			z-index: 100;
			border-top: 0;
		    -moz-transition: fadeIn .4s;
		    -o-transition: fadeIn .4s;
		    -webkit-transition: fadeIn .4s;
		    transition: fadeIn .4s;
		}

		iframe#menu_iframe { overflow-x:hidden; width: 100%; height: 90vh; border-width: 3px; padding:0 }

			.menu-selector ul li {
				background-color: rgba(255,255,255,0.1);
				padding: 5px 30px;
				padding-right:2px;
				margin-bottom:2px;
			}

			.menu-selector ul li:nth-child(odd){ background-color:rgba(0,0,0,0.2) }

			.menu-selector { /*height:330px; */ display:block; padding-bottom:50px; /*overflow-y:scroll;*/ margin-bottom:10px }

			.menu-selector input:checked + span {  color: white; }

			@media all and (min-height: 1000px) {

				/*.menu-selector { height:200px }*/
			}

			@media all and (max-height: 800px) {

				/*.menu-selector { height:250px }*/
				iframe#menu_iframe { height: 87vh }
				/*.menu-selector ul li { font-size: 0.8em }*/
			}

			ul.dropdown-menu.e-mm-selector { padding: 10px; margin-top: -2px; margin-right:-2px; }
		");
}


if (!getperms("2"))
{
	e107::redirect('admin');
	exit;
}


e107::coreLan('menus', true);
e107::coreLan('admin', true);


if(e_MENUMANAGER_ACTIVE === true || vartrue($_GET['enc']))
{
	e107::callMethod('theme', 'init'); // v2.3.0+ new theme

	$JSMODAL = <<<TEMPL
	$(function() {
		$('.e-modal-menumanager').on('click', function(e)
		{
			e.preventDefault();

            if($(this).attr('data-cache') == 'false')
            {
                window.parent.$('#uiModal').on('shown.bs.modal', function () {
                    $(this).removeData('bs.modal');
                });
            }

			var url 		= $(this).attr('href');
			var caption  	= $(this).attr('data-modal-caption');
			var height 		= 600;

            if(caption === undefined)
            {
                caption = '';
            }

    		window.parent.$('.modal-body').html('<div class="well"><iframe id="e-modal-iframe" width="100%" height="'+height+'px" frameborder="0" scrolling="auto" style="display:block;background-color:transparent" allowtransparency="true" src="' + url + '"></iframe></div>');
    		window.parent.$('.modal-caption').html(caption + ' <i id="e-modal-loading" class="fa fa-spin fa-spinner"></i>');
    		window.parent.$('.modal').modal('show');

    		window.parent.$("#e-modal-iframe").on("load", function () {
				 window.parent.$('#e-modal-loading').hide();
			});
    	});

    });
TEMPL;


	e107::getJs()->footerInline( $JSMODAL );

	e107::getJs()->footerInline("
		$(function() {
			// Visibility/Instance Options etc.
			$('.e-menumanager-option').on('click', function(){
				var link = $(this).attr('href');
				var caption = $(this).attr('data-modal-caption');

				window.parent.$('#uiModal .modal-caption').text(caption);	

				window.parent.$('#uiModal .modal-body').load(link, function(){
					window.parent.$('.modal-body :input').on('change', function(){
						var target 	= window.parent.$('#e-save-form').attr('action');
						var data 	= window.parent.$('#e-save-form').serialize();
		
						$.post(target, data ,function(ret)
						{
							if(ret == '')
							{
								return false;
							}
		
							var a = $.parseJSON(ret);
		
							if(a.error)
							{
								alert(a.msg);
							}
						});
					});

					// Attach all registered behaviors to the new content.
					window.parent.e107.attachBehaviors();
				});
	
				window.parent.$('#uiModal').modal('show');
					
				return false;		
			});	

			// Delete Button (Remove Menu) Function
			$('.e-menumanager-delete').on('click', function(e) {
				e.preventDefault();

				var area = 'remove';
				var remove = $(this).attr('id');
				var opt = remove.split('-');
				var hidem = '#block-' + opt[1] +'-' + opt[2];

				$(hidem).hide('slow');

				$.ajax({
					type: 'POST',
					url: 'menus.php',
					data: {
						removeid: remove, 
						area: area, 
						mode: 'delete'
					}
				}).done(function(data) {
					var a = $.parseJSON(data);
					
					if(a.error)
					{
						alert(a.msg);
					}
				});		
			});

	  	});
	");


	e107::getJs()->inlineCSS("	.column { width:100%;  padding-bottom: 100px; }
	

	#core-menumanager-main th {color: silver; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;  font-size:14px; font-weight: bold; line-height:24px; background-color:#2F2F2F }
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; cursor:move }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 7px; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }

	i.S16 {
    background: url(".e_THEME."bootstrap3/images/adminicons_16.webp) no-repeat top left;
 	display:inline-block;  	width:17px;  	height:16px;
 	*margin-right: .3em;
	line-height: 14px;
	vertical-align: text-top;
	}

	i.e-search-16 { background-position: -1344px 0; width: 16px; height: 16px; }
	i.e-delete-16 { background-position: -525px 0; width: 16px; height: 16px; }
	i.e-configure-16 { background-position: -378px 0; width: 16px; height: 16px; }
	i.e-edit-16 { background-position: -609px 0; width: 16px; height: 16px; }

	.e-mm-icon-search {
	    display: inline-block;
	    width: 14px;
	    height: 14px;
	    margin-top: 1px;
	    line-height: 14px;
	    vertical-align: text-top;
	    background-image: url('".e_THEME."bootstrap3/images/glyphicons-halflings.png');
	    background-position: 14px 14px;
	    background-repeat: no-repeat;
	}
	
	.e-mm-icon-search {
	  background-position: -48px 0;
	}

	/*
	.e-mm-icon-align-justify {
	  background-position: -336px -48px;
	}
	*/
	
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
	
	.btn-mini {
    padding: 0px 2px;
    font-size: 10.5px;
    border-radius: 3px 3px 3px 3px;
	}
	
	
	.tbox { text-align:left }
	
	.menuOptions {
    	padding-top: 7px;
    	padding-right: 5px;
    	text-align: left;
		opacity: 0;
		transition: opacity .25s ease-in-out;
   		-moz-transition: opacity .25s ease-in-out;
   		-webkit-transition: opacity .25s ease-in-out;
   
	}
	
	.menuOptions:hover {
		opacity: 1;
		
	}
	
	.menuOptions > select { max-width:100% }
	
	.menu-options-buttons	{ display:block; text-align:right; }
	select.menu-btn { text-align:left; display:block; width:100%; margin-left:3px }
	#menu-manage-actions		{ width:50%; vertical-align:top; text-align:center; padding:15px }
	

	label 										{  font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; color:black;  line-height:14px }
	label.input									{ margin-right:10px;  }
	
	#core-menumanager-main 						{ width:100%; margin-left:auto; margin-right:auto }
	
	
	table.table 								{ width: 95% ; margin-left:auto; margin-right:auto; }
	
	label.selection-row  						{ padding:6px ; cursor: pointer; width:90%}
	
	table.table tbody > tr >td 					{  }
	
	
	table.table tbody > tr > td label {
    display: block;
    cursor: pointer;
    font-size: 14px;
    line-height: 2em;
    padding-left: 15px;
	font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; 
	color:black;  
	
}
	
	
	table.table tbody > tr >td label > input	{ margin-left:5px; margin-right: 10px; float: left; }
	
	
	.table-striped tbody > tr:nth-child(2n+1) > td, .table-striped tbody > tr:nth-child(2n+1) > th {
    	background-color: rgb(249, 249, 249);
	}
	
	.menu-panel {
    min-height: 20px;
    padding: 19px;
    margin-bottom: 20px;
    background-color: rgb(245, 245, 245);
    border: 1px solid rgb(227, 227, 227);
    border-radius: 5px;
    box-shadow: 0px 1px 1px rgba(0, 0, 0, 0.05) inset;
	color: #2F2F2F;
	font-size: 13px;
	font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; 
	}

	.menu-panel-header
	 {
    	display: block;
	    padding: 10px;
	    font-size: 13px;
	    font-weight: bold;
		font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif; 
	    line-height: 20px;

		border-radius: 5px;
	    text-transform: uppercase;
		margin-bottom:10px;
		background-color: rgb(0, 136, 204);
		color: white;
	
	}
	 
	ul.unstyled, ol.unstyled {
   		 margin-left: 0px;
    	list-style: none outside none;
	}
	
	.pull-right { float: right }
	.pull-left { float: left }
	
	.menuOption { opacity:0.2 }	 
	.menuOption:hover { opacity:1 }
	
	.sortable li {  border-radius: 4px }
	.sortable li:hover { background-color: silver; box-shadow:3px 3px 3px silver }
	
	.regularMenu {  border-bottom:1px dotted silver; margin-bottom:6px; padding-left:3px; padding-right:3px; padding-top:10px; padding-bottom:10px;background-color: #E0EBF1; border-radius: 5px; }
	.regularMenu span {padding:3px; font-weight:bold; color:#2F2F2F;text-align:left; }
	.ui-draggable	{ min-width:100px;}

	.regularMenu:hover { background-color: #B1D7EA; }


	",'jquery');
	
	
//	e107::js('footer',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js");
//	e107::css('url', "http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css");

	e107::getJs()->footerInline('
			 $(function()
			 {
			 		// post the form back to this script.
			 		var saveData = function(areaid)
			 		{

			 			var formid 	= "#form-" + areaid;
						var form 	= $(formid);
						var data 	= form.serialize();

						$.ajax({
						  type: "POST",
						  url: "menus.php",
						  data: data
						}).done(function( msg )
						{
							$(".menuOption").show();
						//	 alert("POSTED: "+ msg );
						});

			 		}



/*

				 	$(".sortable").sortable({
				 		connectWith: $("#area-1,#area-2,#area-3,#area-4,#area-5"),
						revert: true,
						cursor: "move",
						distance: 20,
					//	containment: $(".sortable"),
						update: function(ev,ui)
				        {
				        	var areaid = $(this).attr("id");
							saveData(areaid);
				        }
					});




				$( ".draggable", window.top.document).click(function()
					{
						alert("hi there");
					});



				// http://jsfiddle.net/DT764/2/


					$(".draggable", window.top.document).draggable({
					//	connectToSortable: ".sortable",
						helper: "clone",
					//	appendTo: $(this), // ".sortable", // "#area-1", //FIXME Needs to be a specific area.
					//	revert: "invalid",
						containment: "document",
					//	delay: 0,
					//	revertDuration: 100,
						cursor: "move",
						iframeFix: true,
					//	containment: false,
						stop: function(e, ui) {  //TODO Rename layout and area in the hidden fields to that of the where the menu was dropped.
                        	// Figure out positioning magic to determine if e.ui.position is in the iframe
                      	//	var what = $(this).parent().attr("id");
						//	$(".sortable").draggable( "disable" );
                       	alert(what);
                    	}

					});
*/
				//	$( "ul, li", window.top.document ).disableSelection();


					$( ".deleteMenu").on("click", function()
					{
						var deleteId = $(this).attr("data-delete");
						var area 	= $(this).attr("data-area");
						$("#"+deleteId).hide("slow", function(){
							 $("#"+deleteId).remove();
						});


					//	$("#"+deleteId).remove();
					//	alert(deleteId + " " + area);


						saveData(area);
					});




			 });
		 ');

	
}
/*
else
{


		e107::js('footer-inline', "


		$(document).ready(function() {

   	    var stickyNavTop = $('.e-scroll-fixed').offset().top - 60; // grab the initial top offset of the navigation

    	var stickyNav = function(){

		    var scrollTop = $(window).scrollTop(); // our current vertical position from the top

		    if (scrollTop > stickyNavTop) {
	            $('.e-scroll-fixed').addClass('menu-manager-sticky visible col-lg-2 col-md-3');
		    } else {
	            $('.e-scroll-fixed').removeClass('menu-manager-sticky visible col-lg-2 col-md-3');
		    }
		};

	stickyNav();

	$(window).scroll(function() { // and run it again every time you scroll
		stickyNav();
	});






	});






		");







}
*/
	



/*
if($_SERVER['E_DEV_MENU'] == 'true')
{
	if(isset($_GET['configure']) || isset($_GET['iframe']))
	{
		//No layout parse when in iframe mod
		define('e_IFRAME', true);
	}
	$mn = new e_layout;
	//e107::js('core','jquery.scoped.js','jquery');
//	e107::css('url',e_THEME.'jayya/style.css');
	require_once("auth.php");
	require_once("footer.php");
	exit;
}*/

// if($_SERVER['E_DEV_MENU'] == 'true')	
//{


if(!function_exists('e_help'))
{
	function e_help()
	{
		if(deftrue("e_DEBUG_MENUMANAGER"))
		{
			return null;
		}
			
	
		return e_mm_layout::menuSelector();

	}
}
//}



//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);





if (!empty($pref['e_header_list']) && is_array($pref['e_header_list']))
{
	foreach($pref['e_header_list'] as $val)
	{
		// no checks fore existing file - performance
		e107_include_once(e_PLUGIN.$val."/e_header.php");
	}
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
	
	if(!empty($_GET['enc']))
	{
		$string = base64_decode($_GET['enc']);
		parse_str($string,$_GET);

	}
//	print_a($_POST);
	
	if(!empty($_GET['vis']))
	{
		$text = $men->menuVisibilityOptions();
	}
	
	// print_a($_GET);
	
	if(!empty($_GET['parmsId']))
	{
		$text = $men->menuInstanceParameters();
	}
		
	if(!empty($_POST['mode']))
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
	e107::getCache()->clear_sys("menus_");
}


		//FIXME still used in e_HANDLER.menumanager_class.php
		
		
		
		
		
		
		

		
		
		
		if (vartrue($message) != "")
		{
			echo $ns -> tablerender('Updated', "<div style='text-align:center'><b>".$message."</b></div><br /><br />");
		}

		//BC - configure and dot delimiter deprecated
		if (!isset($_GET['configure']))
		{

//			$men->menuScanMenus();   // - Runs 2x - Is already called by menuModify() in menumanager_class.php
            $text = $men->menuRenderMessage();
         //   $text .= $men->menuSelectLayout();
			$text .= $men->menuVisibilityOptions();
			$text .= $men->menuInstanceParameters();
            $text .= $men->menuRenderIframe();
            echo $text;
         //   $ns -> tablerender(ADLAN_6.SEP.LAN_MENULAYOUT, e107::getMessage()->render(). $text, 'menus_config');
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

	// See admin_shortcodes.php -  sc_admin_menumanager()
	// required there so it can be shared by plugins.

}


