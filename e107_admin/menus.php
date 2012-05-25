<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
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

if(strpos(e_QUERY, 'configure') !== FALSE )
{
	
	e107::js('core', 	'colorbox/jquery.colorbox-min.js', 'jquery', 2);
	e107::css('core', 	'colorbox/colorbox.css', 'jquery');
	
	e107::js('core', 	'core/jquery.elastic.source.js', 'jquery', 2);
	
	e107::js('core', 	'plupload/plupload.full.js', 'jquery', 2);
	e107::css('core', 	'plupload/jquery.plupload.queue/css/jquery.plupload.queue.css', 'jquery');
	e107::js('core', 	'plupload/jquery.plupload.queue/jquery.plupload.queue.js', 'jquery', 2);
	
	e107::css('core', 	'chosen/chosen.css', 'jquery');
	e107::js('core', 	'chosen/chosen.jquery.min.js', 'jquery', 2);
	
	e107::css('core', 	'password/style.css', 'jquery');
	e107::js('core', 	'password/jquery.pwdMeter.js', 'jquery', 2);
	// 
	e107::js("core",	"plupload/customUpload.js","jquery",3);
	
	e107::js("core",	"core/mediaManager.js","jquery",3);
	
	
	e107::css('core', 	'core/admin.css', 'jquery');
	e107::js('core', 	'core/admin.jquery.js', 'jquery', 4);
	
	
	e107::css('inline',"	.column { width: 170px; float: left; padding-bottom: 100px; }
	.portlet { margin: 0 1em 1em 0; }
	.portlet-header { margin: 0.3em; padding-bottom: 4px; padding-left: 0.2em; cursor:move }
	.portlet-header .ui-icon { float: right; }
	.portlet-content { padding: 10px; }
	.ui-sortable-placeholder { border: 1px dotted black; visibility: visible !important; height: 50px !important; }
	.ui-sortable-placeholder * { visibility: hidden; }
	",'jquery');
	
	
}





$e_sub_cat = 'menus';

require_once(e_HANDLER."file_class.php");
require_once(e_HANDLER."form_handler.php");
require_once (e_HANDLER.'message_handler.php');
require_once(e_HANDLER."menumanager_class.php");


	$rs = new form;
	$frm = new e_form();
	$men = new e_menuManager(1);   // use 1 for dragdrop.


if(e_AJAX_REQUEST)
{
	$men->menuSaveAjax();
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



		if ($message != "")
		{
			echo $ns -> tablerender('Updated', "<div style='text-align:center'><b>".$message."</b></div><br /><br />");
		}


		//BC - configure and dot delimiter deprecated
		if (!isset($_GET['configure']))
		{
			$men->menuScanMenus();
            $text .= $men->menuRenderMessage();
            $text .= $men->menuSelectLayout();
			$text .= $men->menuVisibilityOptions();
			$text .= $men->menuInstanceParameters();
            $text .= $men->menuRenderIframe();
            $ns -> tablerender(ADLAN_6." :: ".LAN_MENULAYOUT, $text, 'menus_config');
		}
		else // Within the IFrame.
		{
		  	$men->menuRenderPage();

		}

// -----------------------------------------------------------------------------

require_once("footer.php");

 // -----------------------------------------------------------------------

function headerjs()
{
	return;
	
	global $sql,$pref,$men;

    if(!$men->dragDrop)
	{
    	return;
	}

     if(isset($_POST['custom_select']))
	{
		$curLayout =  $_POST['custom_select'];
	}
	else
	{
    	$tmp = explode('.', e_QUERY);
		$curLayout = ($tmp[1]) ? $tmp[1] : $pref['sitetheme_deflayout'];
	}
	$dbLayout = ($curLayout !=$pref['sitetheme_deflayout']) ? $curLayout : "";



    if(strpos(e_QUERY, 'configure') !== FALSE )
	{

		//FIXME - proto/scripty already loaded, create and jsmanager handler
   		$ret = "

		<!-- load prototype and scriptaculous -->
		<script type=\"text/javascript\" src=\"http://www.google.com/jsapi\"></script>
		<script type=\"text/javascript\">
  			google.load(\"prototype\", \"1.6.0.3\");
  			google.load(\"scriptaculous\", \"1.8.2\");
		</script>

		<!-- load the portal script -->
		<script type=\"text/javascript\" src=\"".e_FILE_ABS."jslib/portal/portal.js\"></script>
	  	<link href=\"".e_FILE_ABS."jslib/portal/portal.css\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />

		<!-- initiate the portal -->
		<script type=\"text/javascript\">
			var settings = {

            ";


                     //   ," menu_location !=0 AND menu_layout = '{$curLayout}' ORDER BY menu_location,menu_order"
			$qry = "SELECT * FROM #menus WHERE menu_location !=0 AND menu_layout = '".$dbLayout."' ORDER BY menu_location,menu_order";
            $sql -> db_Select_gen($qry);
            while($row = $sql-> db_Fetch())
            {

				$portal[$row['menu_location']][] = "'block-".$row['menu_id']."--".$dbLayout."'";
        	}

			 foreach($portal as $col=>$val)
			 {
             	$ret .= "                  \n'portal-column-".$col."':[".implode(",",$val)."],";
			 }

      	$ret .= "
			 };
			var options = {
			 editorEnabled : true,
			  'saveurl' : '".e_SELF."?ajax_used=1',
			  hoverclass: 'block-hover'
			 };

			var data = {  };

			var portal;

			Event.observe(window, 'load', function() {
				portal = new Portal(settings, options, data);
			}, false);

		</script>";
	}


  /*  	this.options = {
			editorEnabled 	: false,
			portal			: 'portal',
			column			: 'portal-column',
			block			: 'block',
			content			: 'content',
			configElement	: 'config',
			configSave		: 'save-button',
			configCancel	: 'cancel-button',
			handle			: 'handle',
			hoverclass		: false,
			remove			: 'block-remove',
			config			: 'block-config',
			blocklist		: 'portal-column-block-list',
			blocklistlink	: 'portal-block-list-link',
			blocklisthandle : 'block-list-handle',
			saveurl			: false
		}
*/

	return $ret;
}


function menus_adminmenu()
{

	// See admin_shortcodes_class.php - get_admin_menumanager()
	// required there so it can be shared by plugins.

}

?>