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
	define('e_ADMIN_AREA', true);
	define('e_MENUMANAGER_ACTIVE', false);
}

require_once("../class2.php");


if(e_MENUMANAGER_ACTIVE === false )
{
	e107::library('load', 'bootstrap.switch');
	e107::js('footer', '{e_WEB}js/bootstrap.switch.init.js', 'jquery', 5);

//	if(!deftrue("e_DEBUG"))
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
    background: url(".e_THEME."bootstrap3/images/adminicons_16.png) no-repeat top left;
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

else
{
/*

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


*/




}
		
	



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



	function e_help()
	{
		if(deftrue("e_DEBUG_MENUMANAGER"))
		{
			return null;
		}
			
	
		return e_menu_layout::menuSelector();

/*
		$text = '
			<ul class="nav nav-tabs">
				<li class="active"><a href="#plugins" data-toggle="tab">'.ADLAN_CL_7.'</a></li>	
				<li><a href="#custom" data-toggle="tab">'.LAN_CUSTOM.'</a></li>	
			</ul>
			<div class="tab-content">';	
		
				$text .= "
				<div class='active tab-pane' id='plugins'>
				<div id='menu-manager-item-list' class='menu-manager-items' style='height:400px;overflow-y:scroll'>";
				
				$c = 500; // start high to prevent overwriting of keys after we drag and drop. 
				
				foreach($p as $menu => $folder)
				{
					$text .= "<div id='{$menu}' class='item draggable regularMenu' style='cursor:move'>";
				//	$text .= str_replace("_menu","",$menu);
				
					$defaults = array(
						'name'	=> $menu,
						'path'	=> $folder,
						'class'	=> '0'
					);
					
					$text .= e_layout::renderMenuOptions($defaults,'layout','area',$c);
					
					$text .= "</div>";
					$c++;
					
				}
				
				$text .= "</div>
				</div>
				
				<div class='tab-pane' id='custom'>";
	
				if($sql->select('page','*',"menu_name !='' ORDER BY menu_name"))
				{
					$text .= "<div id='menu-manager-item-list' class='menu-manager-items' style='height:400px;overflow-y:scroll'>";
					while($row = $sql->fetch())
					{
						$text .= "<div id='".$row['page_id']."' class='item draggable regularMenu' style='cursor:move'>";
					//	$text .= $row['menu_name'];
						
						$defaults = array(
							'name'	=> $row['menu_name'],
							'path'	=> $row['page_id'],
							'class'	=> '0'
						);
						
						$text .= e_layout::renderMenuOptions($defaults,'layout','area',$c);
						
						$text .= "</div>";
					}
						
					$text .= "</div>";
				}
					
				$text .= "</div>
				
			</div>";

		return array('caption'=>MENLAN_57,'text'=>$text);*/
	}
//}

// new v2.1.4
class e_menu_layout
{
	function __construct()
	{

	}

	static function getLayouts($theme=null)
	{
		if(empty($theme))
		{
			$theme = e107::pref('core','sitetheme');
		}

		$sql = e107::getDb();
		$tp = e107::getParser();

		$HEADER         = null;
		$FOOTER         = null;
		$LAYOUT         = null;
		$CUSTOMHEADER   = null;
		$CUSTOMFOOTER   = null;


		$file = e_THEME.$theme."/theme.php";

		if(!is_readable($file))
		{
			return false;
		}

		e107::set('css_enabled',false);
		e107::set('js_enabled',false);

		$themeFileContent = file_get_contents($file);

		$srch = array('<?php','?>');

		$themeFileContent = preg_replace('/\(\s?THEME\s?\./', '( e_THEME. "'.$theme.'/" .', str_replace($srch, '', $themeFileContent));

		try
		{
		   @eval($themeFileContent);
		}
		catch (ParseError $e)
		{
			echo "<div class='alert alert-danger'>Couldn't parse theme.php: ". $e->getMessage()." </div>";
		}


	//	@eval($themeFileContent);

		e107::set('css_enabled',true);
		e107::set('js_enabled',true);

		$head = array();
		$foot = array();

		if(isset($LAYOUT) && (isset($HEADER) || isset($FOOTER)))
		{
			$fallbackLan = "This theme is using deprecated elements. All [x]HEADER and [x]FOOTER variables should be removed from theme.php."; // DO NOT TRANSLATE!
			$warningLan = $tp->lanVars(deftrue('MENLAN_60',$fallbackLan),'$');
			echo "<div class='alert alert-danger'>".$warningLan."</div>";

		}



		if(isset($LAYOUT) && is_array($LAYOUT)) // $LAYOUT is a combined $HEADER,$FOOTER.
		{
			foreach($LAYOUT as $key=>$template)
			{
				if($key == '_header_' || $key == '_footer_' || $key == '_modal_')
				{
					continue;
				}

				if(strpos($template,'{---}') !==false)
				{
					list($hd,$ft) = explode("{---}",$template);
					$head[$key] = isset($LAYOUT['_header_']) ? $LAYOUT['_header_'] . $hd : $hd;
					$foot[$key] = isset($LAYOUT['_footer_']) ? $ft . $LAYOUT['_footer_'] : $ft ;
				}
				else
				{
					e107::getMessage()->addDebug('Missing "{---}" in $LAYOUT["'.$key.'"] ');
				}
			}
			unset($hd,$ft);
		}


        if(is_string($CUSTOMHEADER))
        {
			$head['legacyCustom'] = $CUSTOMHEADER;
        }
        elseif(is_array($CUSTOMHEADER))
        {
            foreach($CUSTOMHEADER as $k=>$v)
            {
                $head[$k] = $v;
            }
        }

        if(is_string($HEADER))
        {
			$head['legacyDefault'] = $HEADER;
        }
        elseif(is_array($HEADER))
        {
			 foreach($HEADER as $k=>$v)
            {
                $head[$k] = $v;
            }

        }

		if(is_string($CUSTOMFOOTER))
        {
			$foot['legacyCustom'] = $CUSTOMFOOTER;
        }
        elseif(is_array($CUSTOMFOOTER))
        {
	        foreach($CUSTOMFOOTER as $k=>$v)
            {
                $foot[$k] = $v;
            }
        }


        if(is_string($FOOTER))
        {
			$foot['legacyDefault'] = $FOOTER;
        }
        elseif(is_array($FOOTER))
        {
	        foreach($FOOTER as $k=>$v)
            {
                $foot[$k] = $v;
            }
        }

		$layout = array();

		foreach($head as $k=>$v)
		{
			$template = $head[$k]."\n{---}".$foot[$k];
			$layout['templates'][$k] = $template;
			$layout['menus'][$k] = self::countMenus($template, $k);
		}


		return $layout;


	}


	private static function countMenus($template, $name)
	{
		if(preg_match_all("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", $template, $matches))
		{
			sort($matches[1]);
			return $matches[1];
		}

		e107::getDebug()->log("No Menus Found in Template:".$name." with strlen: ".strlen($template));

		return array();
	}



	static function menuSelector()
	{

	//	$p = e107::getPref('e_menu_list');	// new storage for xxxxx_menu.php list.
		$sql = e107::getDb();
		$frm = e107::getForm();

		$done = array();

		$pageMenu = array();
		$pluginMenu = array();

		$sql->select("menus", "menu_name, menu_id, menu_pages, menu_path", "1 ORDER BY menu_name ASC");
		while ($row = $sql->fetch())
		{

			if(in_array($row['menu_name'],$done))
			{
				continue;
			}

			$done[] = $row['menu_name'];

			if(is_numeric($row['menu_path']))
			{
				$pageMenu[] = $row;
			}
			else
			{
				$pluginMenu[] = $row;
			}

		}

		$tab1 = '<div class="menu-selector"><ul class="list-unstyled">';

		foreach($pageMenu as $row)
		{
			$menuInf = (!is_numeric($row['menu_path'])) ? ' ('.substr($row['menu_path'],0,-1).')' : " (#".$row['menu_path'].")";
			$tab1 .= "<li>".$frm->checkbox('menuselect[]',$row['menu_id'],'',array('label'=>"<span>".$row['menu_name']."<small>".$menuInf."</small></span>"))."</li>";
		}

		$tab1 .= '</ul></div>';

		$tab2 = '<div class="menu-selector"><ul class=" list-unstyled">';
		foreach($pluginMenu as $row)
		{
			$menuInf = (!is_numeric($row['menu_path'])) ? ' ('.substr($row['menu_path'],0,-1).')' : " (#".$row['menu_path'].")";
			$tab2 .= "<li>".$frm->checkbox('menuselect[]',$row['menu_id'],'',array('label'=>"<span>".$row['menu_name']."<small>".$menuInf."</small></span>"))."</li>";
		}

		$tab2 .= '</ul></div>';

		$tabs = array(
			'custom' => array('caption'=>'<i title="'.MENLAN_49.'" class="S16 e-custom-16"></i>', 'text'=>$tab1),
			'plugin' => array('caption'=>'<i title="'.ADLAN_CL_7.'" class="S16 e-plugins-16"></i>', 'text'=>$tab2)

		);


		$defLayout =e107::getRegistry('core/e107/menu-manager/curLayout');;

		$text = '<form id="e-mm-selector" action="'.e_ADMIN_ABS.'menus.php?configure='.$defLayout.'" method="post" target="e-mm-iframe">';

		$text .= "<input type='hidden' id='curLayout' value='".$defLayout."' />";


		//TODO FIXME parse the theme file (or store it somewhere) to get the number of menu areas for each layout. ie. $menu_areas below.

		$layouts = self::getLayouts();
		$tp = e107::getParser();

	//	$text .= print_a($layouts['menus'],true);


		$text .= '

		    <div class="dropdown pull-right e-mm-selector-container">

		        <a class="btn btn-default btn-secondary btn-sm e-mm-selector " title="'.LAN_ACTIVATE.'">'.LAN_GO." ".e107::getParser()->toGlyph('fa-chevron-right').'</a>';

				$menuButtonLabel = defset("MENLAN_59", "Area [x]");

		        foreach($layouts['menus'] as $name=>$areas)
		        {
					$text .= '<ul class="dropdown-menu e-mm-selector '.$name.'" >
					<li><div>';

					foreach ($areas as $menu_act)
					{
						$text .= "<input type='submit' class='btn btn-sm btn-primary col-xs-6'  name='menuActivate[".trim($menu_act)."]' value=\"".$tp->lanVars($menuButtonLabel,trim($menu_act))."\" />\n";
					}

					$text .= '</div></li></ul>';

		        }

		        $text .= '

		    </div>';


		$text .= $frm->tabs($tabs);





		$text .= '</form>';

		$tp = e107::getParser();

		$caption = MENLAN_22;

		;




		return array('caption'=>$caption,'text'=>$text);






	}



}



// XXX Menu Manager Re-Write with drag and drop and multi-dimensional array as storage. ($pref)
// TODO Get Drag & Drop Working with the iFrame
// TODO Sorting, visibility, parameters and delete. 
// TODO Get THIS http://jsbin.com/odiqi3  working with iFrames!! XXX XXX 

class e_layout
{
	private $menuData = array();
	private	$iframe = false;
	private $cnt = 0;
	
	function __construct()
	{
		$pref = e107::getPref();
		$ns = e107::getRender();
	//	$this->convertMenuTable();
	
		$this->menuData = e107::getPref('menu_layouts');
		
		if(e_AJAX_REQUEST)
		{

			if(varset($_POST['data']))
			{
				$this->processPost();	
			}
			
			
			if(vartrue($_GET['enc']))
			{
				$string = base64_decode($_GET['enc']);
				parse_str($string,$_GET);	
			}
			
			if(vartrue($_GET['vis']))
			{
				$text = $this->renderVisibilityOptions();
			}
			
			// print_a($_GET);
			
			if(vartrue($_GET['parmsId']))
			{
				$text = $this->renderInstanceParameters();
			}
				
			if(vartrue($_POST['mode']))
			{
			//	print_r($_POST);
			//	$men->setMenuId($this->menuId);
				$text = $this->menuSaveAjax($_POST['mode']);
			}
		
	
		
			echo $text;
			
			
			
			
			exit;
			
		}


		if(vartrue($_GET['configure'])) //ie Inside the IFRAME. 
		{
									
			global $HEADER,$FOOTER,$CUSTOMHEADER,$CUSTOMFOOTER,$style;
			
			$this->HEADER 		= $HEADER;
			$this->FOOTER 		= $FOOTER;
			$this->CUSTOMHEADER = $CUSTOMHEADER;
			$this->CUSTOMFOOTER = $CUSTOMFOOTER;
			$this->style		= $style;


			unset($HEADER,$FOOTER,$CUSTOMHEADER,$CUSTOMFOOTER,$style);
			
			require_once(e_CORE."templates/admin_icons_template.php");


			
		 /*
		
			e107::js('inline', "
			
			win = document.getElementById('menu_iframe').contentWindow;
			win.jQuery(dragelement,parent.document).draggable({
				connectToSortable : $('#sortable')
			});
			
			",'jquery');	
		
			
		*/
						
			$this->curLayout = vartrue($_GET['configure'], $pref['sitetheme_deflayout']);
			$this->renderLayout($this->curLayout);
			
		
			
			
		}
		else // Parent - ie. main admin page. 
		{
			e107::css('inline',"
				.menuOption { display: none }
			
			");
			
			
			$theme = e107::getPref('sitetheme');		
			require_once(e_THEME.$theme."/theme.php");
			
			$this->HEADER 		= $HEADER;
			$this->FOOTER 		= $FOOTER;
			$this->CUSTOMHEADER = $CUSTOMHEADER;
			$this->CUSTOMFOOTER = $CUSTOMFOOTER;
			$this->style		= $style;
			
				// XXX HELP _ i don't work with iFrames. 
		//	$("#sortable")
		//$("iframe").contents().find(".sortable")	
		
		/*
		e107::js('inline','
		 $(function() 
		 {
			$( ".sortable" ).sortable({
				revert: true
			});
			
			
		
			$("iframe").load(function(){
				
				var frameid = $("#iframe-default").contents().find(".sortable").attr("id")
				
				$( ".draggable" ).draggable({
					connectToSortable: "#" + frameid,
					helper: "clone",
					revert: "invalid",
					cursor: "move",
					iframeFix: true
			        
			       
				});
				
			});
			
		 	//	$( "ul, li" ).disableSelection();

			
		});
		
		
		','jquery');
		*/
			
		 
			$this->scanForNew();
			
			$this->renderInterface();	
		}	
	}
	

	/**
	 * Save Menu Pref 
	 */
	protected function processPost()
	{
		$cnf 		= e107::getConfig('core');
		$existing 	= $cnf->get('menu_layouts');
		
		$data 	= $_POST['data'];
		$layout = $_POST['layout'];
		$area	= $_POST['area'];
		
		$save = array();
		
		
		foreach($_POST['data']['layout']['area'] as $v) // reset key values. 
		{
			$save[] = $v;	
		}
		
	//	$save[$layout][$area] = $_POST['data']['layout']['area'];		
		echo "\nLAYOUT=".$layout."\n";
		echo "AREA=".$area."\n";
		//print_r($save);
		
		e107::getConfig('core')->setPref('menu_layouts/'.$layout."/".$area, $save)->save(); 	
			
	}



	
	/**
	 * Substitute all {MENU=X} and Render output. 
	 */
	private function renderLayout($layout='')
	{		
		$ALL = $this->getHeadFoot();
		
		$HEADER = $ALL['HEADER'];
		$FOOTER = $ALL['FOOTER'];
		
		$tp = e107::getParser();
			
		$head = preg_replace_callback("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", array($this, 'renderMenuArea'), $HEADER[THEME_LAYOUT]);
		$foot = preg_replace_callback("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", array($this, 'renderMenuArea'), $FOOTER[THEME_LAYOUT]);
	
		global $style;
		
		$style = $this->style;

		echo $tp->parseTemplate($head);
	//	echo "<div>MAIN CONTENT</div>";
		echo $tp->parseTemplate($foot);

	}

	
	
	


	/**
	 * Render {MENU=X} 
	 */
	private function renderMenuArea($matches)
	{
		$frm = e107::getForm();
		$area = $matches[1];
		
		// return print_a($this->menuData,true);
		$text = "<div class='menu-panel'>";
		$text .= "<div class='menu-panel-header' title=\"".MENLAN_34."\">".MENLAN_14." ".$area."</div>\n";
		$text .= $frm->open('form-area-'.$area,'post',e_SELF);
		$text .= "<ul id='area-".$area."' class='sortable unstyled list-unstyled'>
			<li>&nbsp;</li>";
		
		if(vartrue($this->menuData[THEME_LAYOUT]) && is_array($this->menuData[THEME_LAYOUT][$area]))
		{
			
			foreach($this->menuData[THEME_LAYOUT][$area] as $val)
			{
				$text .= $this->renderMenu($val, THEME_LAYOUT, $area,$count);	
				$this->cnt++;
			}	

		}
		
		$text .= "</ul>";
		$text .= "</div>";
		
	//	$text .= $frm->button('submit','submit','submit','submit');
					
		$text .= $frm->hidden('layout',THEME_LAYOUT);
		$text .= $frm->hidden('area',$area);	
		$text .= $frm->close();
		
		return $text;
	}
	
	
	
	
	private function renderMenu($row, $layout, $area, $count)
	{
	//	return print_a($row,true);
		$frm = e107::getForm();
		$uniqueId = "menu_".$frm->name2id($row['path']).'_'.$this->cnt;
	
		$TEMPLATE = '<li class="regularMenu" id="'.$uniqueId.'"> '.$this->renderMenuOptions($row, $layout, $area, $this->cnt, $uniqueId).' </li>
		'; // TODO perhaps a simple counter for the id 
	
		return $TEMPLATE;	
		
	}
	



	/**
	 * @param $row (array of data from $pref['menu_layouts'] 
	 * @param $layout . eg. 'default' or 'home'
	 * @param number $area as in {MENU=x}
	 * @param incrementor number. 
	 */
	public function renderMenuOptions($row, $layout, $area, $c , $uniqueId='xxx')
	{
		$frm = e107::getForm();
		
	//	$text = "<i class='icon-align-justify'></i> ";
		$text = str_replace("_menu","",$row['name']);
	
	//	$layout = 'layout';
	//	$area = 'area';
		//TODO Delete, Config etc. 
		
		//$data[$layout][$location][] = array('name'=>$row['menu_name'],'class'=>$row['menu_class'],'path'=>$row['menu_path'],'pages'=>$row['menu_pages'],'parms'=>$row['menu_parms']);	
	//	$area = 'area_'.$area;
		
		// 'layout' and 'area' will later be substituted. 
		
		
		
		$text .= $frm->hidden('data[layout][area]['.$c.'][name]',$row['name'],array('id'=>'name-'.$area.'-'.$c) );
		$text .= $frm->hidden('data[layout][area]['.$c.'][class]',$row['class'], array('id'=>'class-'.$area.'-'.$c)  );	
		$text .= $frm->hidden('data[layout][area]['.$c.'][path]',$row['path'], array('id'=>'path-'.$area.'-'.$c)  );
		$text .= $frm->hidden('data[layout][area]['.$c.'][pages]',$row['pages'], array('id'=>'pages-'.$area.'-'.$c)  );		
		$text .= $frm->hidden('data[layout][area]['.$c.'][parms]',$row['parms'], array('id'=>'parms-'.$area.'-'.$c)  );	

		$visibilityLink = e_SELF."?enc=".base64_encode('lay='.$layout.'&vis='.$area.'-'.$c.'&iframe=1&class='.$row['class'].'&pages='.$row['pages']);
		
		
		$text .= "<a href='#'  class='menuOption menu-btn menu-btn-mini menu-btn-danger deleteMenu pull-right' data-area='area-".$area."' data-delete='".$uniqueId."'>&times;</a>"; // $('.hello').remove();
		
		$text .= '<a class="menuOption e-menumanager-option menu-btn pull-right" data-modal-caption="'.LAN_VISIBILITY.'" href="'.$visibilityLink.'" title="'.LAN_VISIBILITY.'"><i class="icon-search"></i></a>';
		
		/*
				
				
		$text .= '<span class="menu-options-buttons">
		<a class="e-menumanager-option menu-btn" data-modal-caption="'.LAN_VISIBILITY.'" href="'.$visibilityLink.'" title="'.LAN_VISIBILITY.'"><i class="S16 e-search-16"></i></a>';

		if($conf)
		{
			$text .= '<a class="menu-btn" target="_top" href="'.e_SELF.'?lay='.$layout.'&amp;mode=conf&amp;path='.urlencode($conf).'&amp;id='.$menu_id.'" 
			title="Configure menu"><i class="S16 e-configure-16"></i></a>';
		}
		
		$editLink = e_SELF."?enc=".base64_encode('lay='.$layout.'&parmsId='.$menu_id.'&iframe=1');
		$text .= '<a data-modal-caption="Configure parameters" class="e-menumanager-option menu-btn e-tip" target="_top" href="'.$editLink.'" title="Configure parameters"><i class="S16 e-edit-16" ></i></a>';

		$text .= '<a title="'.LAN_DELETE.'" id="remove-'.$menu_id.'-'.$menu_location.'" class="e-tip delete e-menumanager-delete menu-btn" href="'.e_SELF.'?configure='.$layout.'&amp;mode=deac&amp;id='.$menu_id.'"><i class="S16 e-delete-16"></i></a>
		
		</span>';
		*/
		
		
		
		
		
		
		
		
		
		
		
		return $text;
		
	}
	
/*
	function menuSaveAjax($mode = null)
	{

		if($mode == 'visibility')
		{
		
			$ret = $this->menuSaveVisibility();	
		//	echo json_encode($ret);
			return;		
		}		
			
		
		if($mode == 'parms') 
		{
		//	echo "hi there";
			$ret =  array('msg'=>'hi there','error'=>true);
		//	$ret = $this->menuSaveParameters();
			echo json_encode($ret);
			return;
		}
		
		
		
     //	print_r($_POST);
		return;
	

	}	
*/
	/**
	 * Scan Plugin folders for new _menu files. 
	 */
	private function scanForNew()
	{
		$fl 			= e107::getFile();
		$fl->dirFilter 	= array('/', 'CVS', '.svn', 'languages');
		$files 			= $fl->get_files(e_PLUGIN,"_menu\.php$",'standard',2);	
		
		$data = array();
		
		foreach($files as $file)
		{

			if($file == 'e_menu.php')
			{
				continue;
			}

			$valid_menu = false;
			
			if (file_exists($file['path'].'/plugin.xml') || file_exists($file['path'].'/plugin.php'))
			{
			//	if (e107::isInstalled($file['path'])) //FIXME need a check that doesn't exlude page, news and others that don't require installation.
				{  
					$valid_menu = TRUE;		// Whether new or existing, include in list
				}
			}
			else  // Just add the menu anyway
			{
				$valid_menu = TRUE;
			}
			
			$path = trim(str_replace(e_PLUGIN,"",$file['path']),"/");

			if($valid_menu)
			{
				$fname = str_replace(".php","",$file['fname']);
				$data[$fname] = $path;
			}
		}
		
		$config = e107::getConfig('core');
		$config->set('e_menu_list',$data);
		$config->save();
		
	}	
	
	private function renderVisibilityOptions()
	{
		if(!vartrue($_GET['vis'])) return;
		
	//	print_a($_GET);
		
		$tp = e107::getParser();
		$sql = e107::getDb();
		$ns = e107::getRender();
		$frm = e107::getForm();
		
		require_once(e_HANDLER."userclass_class.php");
		
	/*
		if(!$sql->select("menus", "*", "menu_id=".intval($_GET['vis'])))
		{
        	$this->menuAddMessage("Couldn't Load Menu",E_MESSAGE_ERROR);
            return;
		}
		
		$row = $sql->fetch();
	*/
		
		
		$listtype 	= substr($_GET['pages'], 0, 1);
		$menu_pages = substr($_GET['pages'], 2);
		$menu_pages = str_replace("|", "\n", $menu_pages);

		$text = "<div>
			<form class='form-horizontal' id='e-save-form' method='post' action='".e_SELF."?lay=".$this->curLayout."&amp;iframe=1'>
	        <fieldset>
			<legend>". MENLAN_7." ".$row['menu_name']."</legend>
	        <table class='table adminform'>
			<tr>
			<td>
			".LAN_VISIBLE_TO." ".
			r_userclass('menu_class', intval($_GET['class']), "off", "public,member,guest,admin,main,classes,nobody")."
			</td>
			</tr>
			<tr><td><div class='radio'>
		";
		
		$checked = ($listtype == 1) ? " checked='checked' " : "";
		
		$text .= $frm->radio('listtype', 1, $checked, array('label'=> $tp->toHtml(MENLAN_26,true), 'class'=> 'e-save'));
		$text .= "<br />";
	//	$text .= "<input type='radio' class='e-save' {$checked} name='listtype' value='1' /> ".MENLAN_26."<br />";
		$checked = ($listtype == 2) ? " checked='checked' " : "";
		
		$text .= $frm->radio('listtype', 2, $checked, array('label'=>  $tp->toHtml(MENLAN_27,true), 'class'=> 'e-save'));
		
		
		// $text .= "<input type='radio' class='e-save' {$checked} name='listtype' value='2' /> ".MENLAN_27."<br />";
		
		$text .= "</div>
		<div class='row' style='padding:10px'>
			
			<div class='pull-left span3' >
		
				<textarea name='pagelist' class='e-save span3' cols='60' rows='8' class='tbox'>".$menu_pages."</textarea>
			</div>
			<div class='  span4 col-md-4'><small>".MENLAN_28."</small></div>
		</div></td></tr>
		</table>";
		
		$text .= $frm->hidden('mode','visibility'); 
		$text .= $frm->hidden('menu_id',$_GET['vis']); // is NOT an integer
		
		/*
		$text .= "
		<div class='buttons-bar center'>";
        $text .= $frm->admin_button('class_submit', MENLAN_6, 'update');

		
		</div>";
		 */ 
		$text .= "
		</fieldset>
		</form>
		</div>";
	
		
		return $text;
		//$caption = MENLAN_7." ".$row['menu_name'];
		//$ns->tablerender($caption, $text);
		//echo $text;
	}




	/**
	 * This one will be greatly extended, allowing menus to offer UI and us 
	 * settings per instance later ($parm variable available for menus - same as shortcode's $parm)
	 * @see menuInstanceParameters() in menumanager_class.php
	 */
/*
	private function renderInstanceParameters()
	{
		if(!vartrue($_GET['parmsId'])) return;
		$id = intval($_GET['parmsId']);
		$frm = e107::getForm();
		$sql = e107::getDb();
		
		if(!$sql->select("menus", "*", "menu_id=".$id))
		{
        	$this->menuAddMessage("Couldn't Load Menu",E_MESSAGE_ERROR);
            return;
		};
		$row = $sql->fetch();

		$text = "<div style='text-align:center;'>
		<form  id='e-save-form' method='post' action='".e_SELF."?lay=".$this->curLayout."'>
        <fieldset id='core-menus-parametersform'>
		<legend>".MENLAN_44." ".$row['menu_name']."</legend>
        <table class='table adminform'>
		<tr>
		<td>
		".MENLAN_45."</td><td>
		".$frm->text('menu_parms', $row['menu_parms'], 900, 'class=e-save ')."
		</td>
		</tr>
		</table>";

		
		//	$text .= "
		//	<div class='buttons-bar center'>";
		//	$text .= $frm->admin_button('parms_submit', LAN_SAVE, 'update');
		//	$text .= "<input type='hidden' name='menu_id' value='".$id."' />
		//	</div>";
			

		$text .= $frm->hidden('mode','parms');
		$text .= $frm->hidden('menu_id',$id);
		$text .= "
		</fieldset>
		</form>
		</div>";
		
		return $text;
	
	}
*/

	/**
	 * Render the main area with TABS and iframes. 
	 */
	private function renderInterface()
	{
		$ns = e107::getRender();
		$tp = e107::getParser();
		$frm = e107::getForm();	
		
		$TEMPL = $this->getHeadFoot();	
		
			
		$layouts = array_keys($TEMPL['HEADER']);
		
		e107::js('inline','
		 $(function() 
		 {
			$(".draggable").draggable({
					connectToSortable: $(".sortable"),
					helper: "clone",
					revert: "invalid",
					cursor: "move",
					iframeFix: true,
			        refreshPositions: true
			       
				});
		 })'
		 );
		

		
		
		$text = '<ul class="nav nav-tabs">';
	
		$active = ' class="active" ';
		
		foreach($layouts as $title)
		{
			$text .= '<li '.$active.'><a href="#'.$title.'" data-toggle="tab">'.$title.'</a></li>';	
			$active = '';
		}
				
		$text .= '</ul>';
		$active = 'active';
	
		$text .= '		
		<div class="tab-content">';	
		
			foreach($layouts as $title)
			{
				$text .= '
					<div class="tab-pane '.$active.'" id="'.$title.'">
					<iframe id="iframe-'.$frm->name2id($title).'" class="well" width="100%" scrolling="no" style="width: 100%; height: 6933px; border: 0px none;" src="'.e_ADMIN_ABS.'menus.php?configure='.$title.'"></iframe>
					</div>';	
					
				$active = '';
			}
		
		$text .= '</div>';
		
	//	$ns->frontend = false;
		
		$ns->tablerender(MENLAN_55,$text);		
	}
	
	
	
	
	
	
	private function getHeadFoot()
	{
	
		$H = array();
		$F = array();
		
		if(is_string($this->HEADER))
		{			
			$H['default'] = $this->HEADER;
			$F['default'] = $this->FOOTER;	
		}
		else
		{
			$H = $this->HEADER;
			$F = $this->FOOTER;	
		}
		

		
	      //   0.6 / 0.7-1.x
	    if(isset($this->CUSTOMHEADER) && isset($this->CUSTOMHEADER))
		{
	         if(!is_array($this->CUSTOMHEADER))
			 {
					$H['legacyCustom'] = $this->CUSTOMHEADER;
	            	$F['legacyCustom'] = $this->CUSTOMFOOTER;
			 }
			 else 
			 {
					foreach($this->CUSTOMHEADER as $k=>$v)
					{
						$H[$k] = $v;	
					}
					foreach($this->CUSTOMFOOTER as $k=>$v)
					{
						$F[$k] = $v;	
					}				 
			 }
		}

		
		
		return array('HEADER'=>$H, 'FOOTER'=>$F);
	}
	
	//$ns = e107::getRender();
	
}




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

?>
