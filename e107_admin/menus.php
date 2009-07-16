<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (c) e107 Inc. 2001-2009
|     http://e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_admin/menus.php,v $
|     $Revision: 1.30 $
|     $Date: 2009-07-16 02:55:18 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if (!getperms("2"))
{
	header("location:".e_BASE."index.php");
	exit;
}
$e_sub_cat = 'menus';


require_once(e_HANDLER."file_class.php");
require_once(e_HANDLER."form_handler.php");
require_once (e_HANDLER.'message_handler.php');
require_once(e_HANDLER."menu_class.php");


	$rs = new form;
	$frm = new e_form();
	$men = new menuManager();   // use 1 for dragdrop.


  if(isset($_GET['ajax']))
  {
  	$men->menuSaveAjax();
	exit;
  }

require_once("auth.php");






if($_POST)
{
 //	print_a($_POST);
//	exit;
	$e107cache->clear_sys("menus_");
}



		if ($message != "")
		{
			echo $ns -> tablerender('Updated', "<div style='text-align:center'><b>".$message."</b></div><br /><br />");
		}


		if (strpos(e_QUERY, 'configure') === FALSE)
		{
            $text .= $men->menuRenderMessage();
            $text .= $men->menuSelectLayout();
			$text .= $men->menuVisibilityOptions();
            $text .= $men->menuRenderIframe();
            $ns -> tablerender(ADLAN_6." :: ".LAN_MENULAYOUT, $text, 'menus_config');
		  //	$text .= "<iframe name='menu_iframe' id='menu_iframe' src='".e_SELF."?configure.$curLayout' width='100%' style='width: 100%; height: ".(($cnt*90)+600)."px; border: 0px' frameborder='0' scrolling='auto' ></iframe>";

		}
		else // Within the IFrame.
		{

/*        		echo "<div>
                e_QUERY = ".e_QUERY."<br />
				curLayout = ".$men->curLayout."<br />
				dbLayout   = ".$men->dbLayout."<br />";

				print_a($_POST);
				echo "
				</div>";*/



		  	$men->menuRenderPage();

		}

// -----------------------------------------------------------------------------

require_once("footer.php");

 // -----------------------------------------------------------------------

function headerjs()
{
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
			  'saveurl' : '".e_SELF."?ajax=',
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