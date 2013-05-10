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




e107::coreLan('menus', true);
e107::coreLan('admin', true);



if(strpos(e_QUERY, 'configure') !== FALSE || vartrue($_GET['enc']))
{
	
	
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
				var caption = $(this).attr('data-modal-caption');
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
	

	.regularMenu { border-bottom:1px dotted silver; margin-bottom:6px; padding-left:3px; padding-right:3px; padding-bottom:20px }
	#core-menumanager-main th {color: silver; font-family: 'Helvetica Neue',Helvetica,Arial,sans-serif;  font-size:14px; font-weight: bold; line-height:24px; background-color:#2F2F2F }
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; cursor:move }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 7px; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
	
	[class^='icon-'], [class*=' icon-'] {
	    display: inline-block;
	    width: 14px;
	    height: 14px;
	    margin-top: 1px;
	    line-height: 14px;
	    vertical-align: text-top;
	    background-image: url('".e_JS."bootstrap/img/glyphicons-halflings.png');
	    background-position: 14px 14px;
	    background-repeat: no-repeat;
	}
	
	.icon-search {
	  background-position: -48px 0;
	}
	
	
	
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
		opacity: 0;
		transition: opacity .25s ease-in-out;
   		-moz-transition: opacity .25s ease-in-out;
   		-webkit-transition: opacity .25s ease-in-out;
   
	}
	
	.menuOptions:hover {
		opacity: 1;
		
	}
	
	.menuOptions > select { max-width:100% }
	
	.menu-options-buttons	{  }
	#menu-manage-actions		{ width:50%; vertical-align:top; text-align:center; padding:15px }
	
	select.menu-btn { text-align:left }

	
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
	
	",'jquery');
	
	
}




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
}

// if($_SERVER['E_DEV_MENU'] == 'true')	
//{
	function e_help()
	{
		if($_SERVER['E_DEV_MENU'] != 'true')	
		{
			return false;		
		}	
			
	
		
		$p = e107::getPref('e_menu_list');	// new storage for xxxxx_menu.php list. 
		$sql = e107::getDb();

		$text = '
			<ul class="nav nav-tabs">
				<li class="active"><a href="#plugins" data-toggle="tab">Plugins</a></li>	
				<li><a href="#custom" data-toggle="tab">Custom</a></li>	
			</ul>
			<div class="tab-content">';	
		
				$text .= "
				<div class='active tab-pane' id='plugins'>
				<ul>";
				
				$c = 500; // start high to prevent overwriting of keys after we drag and drop. 
				
				foreach($p as $menu => $folder)
				{
					$text .= "<li id='{$menu}' class='draggable regularMenu' style='cursor:move'>";
				//	$text .= str_replace("_menu","",$menu);
				
					$defaults = array(
						'name'	=> $menu,
						'path'	=> $folder,
						'class'	=> '0'
					);
					
					$text .= e_layout::renderMenuOptions($defaults,'layout','area',$c);
					
					$text .= "</li>";
					$c++;
					
				}
				
				$text .= "</ul>
				</div>
				
				<div class='tab-pane' id='custom'>";
	
				if($sql->select('page','*',"menu_name !='' ORDER BY menu_name"))
				{
					$text .= "<ul>";
					while($row = $sql->fetch())
					{
						$text .= "<li id='".$row['page_id']."' class='draggable regularMenu' style='cursor:move'>";
					//	$text .= $row['menu_name'];
						
						$text .= e_layout::renderMenuOptions($row);
						
						$text .= "</li>";	
					}
						
					$text .= "</ul>";			
				}
					
				$text .= "</div>
				
			</div>";

		return array('caption'=>'Menu Items','text'=>$text); 
	}
//}


// XXX Menu Manager Re-Write with drag and drop and multi-dimensional array as storage. ($pref)
// TODO Get Drag & Drop Working with the iFrame
// TODO Sorting, visibility, parameters and delete. 
// TODO Get THIS http://jsbin.com/odiqi3  working with iFrames!! XXX XXX 

class e_layout
{
	private $menuData = array();
	private	$iframe = false;
	
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
			
			exit;
			
		}

		
		if(vartrue($_GET['configure'])) //ie Inside the IFRAME. 
		{
				
			e107::js('url',"http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/jquery-ui.min.js");
			e107::js('url',	"http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.1/themes/base/jquery-ui.css");
			
		 	
			e107::js('inline','
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
							// alert("POSTED: "+ msg );
						});			
			 			
			 		}
			 				
			 				
			 		
			 	
					
				 	$(".sortable").sortable({
				 		
						revert: true,
						cursor: "move",
						distance: 20,
						containment: "parent",
						update: function(ev,ui)
				        {
				        	var areaid = $(this).attr("id");
							saveData(areaid);
				        }
					});
					
					
					
					
			//		$( ".draggable", window.top.document).click(function()
			//		{
			//			alert("hi there");	
			//		});
				
				// http://jsfiddle.net/DT764/2/	
					
			
					$( ".draggable", window.top.document).draggable({
						connectToSortable: ".sortable",
						helper: "clone",
						appendTo: ".sortable", // "#area-1", //FIXME Needs to be a specific area. 
						revert: true,
						containment: "parent",
						delay: 0,
						revertDuration: 100,
						cursor: "move",
						iframeFix: true,
						containment: false,
						stop: function(e, ui) {  //TODO Rename layout and area in the hidden fields to that of the where the menu was dropped. 
                        	// Figure out positioning magic to determine if e.ui.position is in the iframe
                      	//	var what = $(this).parent().attr("id");
							
                        //	alert(what);
                    	}
			       
					});
				
				//	$( "ul, li", window.top.document ).disableSelection();
				
				
					$( ".deleteMenu").on("click", function()
					{
						var deleteId = $(this).attr("data-delete");
						var area 	= $(this).attr("data-area");
						$("#"+deleteId).hide("slow");
						$("#"+deleteId).remove();
					//	alert(deleteId + " " + area);
						saveData(area);
					});
					
				
					
				
			 });
		 ');
		 	
			
		 /*
		
			e107::js('inline', "
			
			win = document.getElementById('menu_iframe').contentWindow;
			win.jQuery(dragelement,parent.document).draggable({
				connectToSortable : $('#sortable')
			});
			
			",'jquery');	
		
			
		*/
			
			
			
			$this->curLayout = varsettrue($_GET['configure'], $pref['sitetheme_deflayout']);
			$this->renderLayout($this->curLayout);	
		}
		else // Parent - ie. main admin page. 
		{
			
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
	


	protected function processPost()
	{
		$cnf = e107::getConfig('core');
		$existing = $cnf->get('menu_layouts');
		
			//	print_r($existing);
			//	$data = array_merge($existing,$_POST['data']);
			
		$data = $_POST['data'];
		
		$layout = $_POST['layout'];
		$area	= $_POST['area'];
		
		$save = array();
		foreach($_POST['data']['layout']['area'] as $v) // reset key values. 
		{
			$save[$area][] = $v;	
		}
		
	//	$save[$layout][$area] = $_POST['data']['layout']['area'];		
		
		print_r($save);
		// return;
		
		e107::getConfig('core')->setPref('menu_layouts/'.$layout, $save)->save(); 	
	//	e107::getConfig('core')->set('menu_layouts', $save)->save(); //TODO Save directly into multi-dimensional array. ie. $layout / $area / array. 
		
	}



	
	/**
	 * Substitute all {MENU=X} and Render output. 
	 */
	private function renderLayout($layout='')
	{

		global $HEADER,$FOOTER,$style; // included by theme file in class2. 
		
		$tp = e107::getParser();
		
		$head = preg_replace_callback("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", array($this, 'renderMenuArea'), $HEADER[THEME_LAYOUT]);
		$foot = preg_replace_callback("/\{MENU=([\d]{1,3})(:[\w\d]*)?\}/", array($this, 'renderMenuArea'), $FOOTER[THEME_LAYOUT]);
	
		echo $tp->parsetemplate($head);
	//	echo "<div>MAIN CONTENT</div>";
		echo $tp->parsetemplate($foot);

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
		$text .= "<div class='menu-panel-header' title=\"".MENLAN_34."\">Area ".$area."</div>\n";
		$text .= $frm->open('form-area-'.$area,'post',e_SELF);
		
		$count = 0;
		if(vartrue($this->menuData[THEME_LAYOUT]) && is_array($this->menuData[THEME_LAYOUT][$area]))
		{
			$text .= "<ul id='area-".$area."' class='sortable unstyled'>";
			
			foreach($this->menuData[THEME_LAYOUT][$area] as $val)
			{
				$text .= $this->renderMenu($val, THEME_LAYOUT, $area,$count);	
				$count++;
			}	
			
			$text .= "</ul>";
		}
		else // Empty Menu. 
		{
			$text .= "<ul id='area-".$area."' class='sortable unstyled'>
			<li>&nbsp;</li>
			</ul>";	
			
		}
		
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
		$uniqueId = "menu_".$row['path'].'_'.$count;
	
		$TEMPLATE = '<li class="regularMenu" id="'.$uniqueId.'"> '.$this->renderMenuOptions($row, $layout, $area,$count,$uniqueId).' </li>
		'; // TODO perhaps a simple counter for the id 
	
		return $TEMPLATE;	
		
	}
	



	/**
	 * @param $row (array of data from $pref['menu_layouts'] 
	 * @param $layout . eg. 'default' or 'home'
	 * @param number $area as in {MENU=x}
	 * @param incrementor number. 
	 */
	public function renderMenuOptions($row, $layout, $area, $c, $uniqueId)
	{
		$frm = e107::getForm();
		
		$text = str_replace("_menu","",$row['name']);
	//	$layout = 'layout';
	//	$area = 'area';
		//TODO Delete, Config etc. 
		
		//$data[$layout][$location][] = array('name'=>$row['menu_name'],'class'=>$row['menu_class'],'path'=>$row['menu_path'],'pages'=>$row['menu_pages'],'parms'=>$row['menu_parms']);	
	//	$area = 'area_'.$area;
		
		// 'layout' and 'area' will later be substituted. 
		$text .= $frm->hidden('data[layout][area]['.$c.'][name]',$row['name'] );
		$text .= $frm->hidden('data[layout][area]['.$c.'][class]',$row['class'] );	
		$text .= $frm->hidden('data[layout][area]['.$c.'][path]',$row['path'] );
		$text .= $frm->hidden('data[layout][area]['.$c.'][pages]',$row['pages'] );		
		$text .= $frm->hidden('data[layout][area]['.$c.'][parms]',$row['parms'] );	


		if(vartrue($_GET['configure'])) // Iframe Mode. 
		{
			$text .= "<a href='#' class='menu-btn btn-mini menu-btn-danger deleteMenu pull-right' data-area='area-".$area."' data-delete='".$uniqueId."'>&times;</a>"; // $('.hello').remove();
		}

		
			
		return $text;
		
	}
	
	
	
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
			$path = trim(str_replace(e_PLUGIN,"",$file['path']),"/");
			
		//	 if(e107::isInstalled($path) )
			{
				$fname = str_replace(".php","",$file['fname']);
				$data[$fname] = $path;
			}
		}
		
		$config = e107::getConfig('core');
		$config->set('e_menu_list',$data);
		$config->save();
		
	}	
	



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
		
		$ns->tablerender("Menu Layout",$text);		
	}
	
	
	
	
	
	
	private function getHeadFoot($_MLAYOUT=null)
	{
		$theme = e107::getPref('sitetheme');		
		
		$H = array();
		$F = array();
		
		require(e_THEME.$theme."/theme.php");
		
		
		if(is_string($HEADER))
		{			
			$H['default'] = $HEADER;
			$F['default'] = $FOOTER;	
		}
		else
		{
			$H = $HEADER;
			$F = $FOOTER;	
		}
		
		
	      //   0.6 / 0.7-1.x
	    if(isset($CUSTOMHEADER) && isset($CUSTOMHEADER))
		{
	         if(!is_array($CUSTOMHEADER))
			 {
					$H['legacyCustom'] = $CUSTOMHEADER;
	            	$F['legacyCustom'] = $CUSTOMFOOTER;
			 }
			 else 
			 {
					foreach($CUSTOMHEADER as $k=>$v)
					{
						$H[$k] = $v;
						$F[$k] = $v;			
					}		 
			 }
		}
		
		if($_MLAYOUT)
		{
	//		return array('HEADER'=>$H[$_MLAYOUT], 'FOOTER'=>$F[$_MLAYOUT]);	
		}
		
		
		return array('HEADER'=>$H, 'FOOTER'=>$F);
	}
	
	//$ns = e107::getRender();
	
}




//include_lan(e_LANGUAGEDIR.e_LANGUAGE.'/admin/lan_'.e_PAGE);


// FIXME - quick temporarry fix for missing icons on menu administration. We need different core style to be included (forced) here - e.g. e107_web/css/admin/sprite.css
if(e_IFRAME) //<-- Check config and delete buttons if modifying
{

//e107::js('core','bootstrap/js/bootstrap.min.js');
//e107::css('core','bootstrap/css/bootstrap.min.css');
	e107::css('url','{e_THEME}/bootstrap/admin_style.css');

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