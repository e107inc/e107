<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/download/e_help.php,v $
|     $Revision: 1.1 $
|     $Date: 2009-07-19 09:31:05 $
|     $Author: marj_nl_fr $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
}

switch($action) {
   case 'create' :{
      $text = DOWLAN_HELP_2;
      break;
   }
   case 'cat' :{
      $text = DOWLAN_HELP_3;
      break;
   }
   case 'opt' :{
      $text = DOWLAN_HELP_4;
      break;
   }
   case 'maint' :{
      $text = DOWLAN_HELP_5;
      break;
   }
   case 'limits' :{
      $text = DOWLAN_HELP_6;
      break;
   }
   case 'mirror' :{
      $text = DOWLAN_HELP_7;
      break;
   }
   default : {
      $text = "<p>Please upload your files into the ".e_DOWNLOAD." folder, your images into the ".e_FILE."downloadimages folder and thumbnail images into the ".e_FILE."downloadthumbs folder.</p>
         <p>To submit a download, first create a parent, then create a category under that parent, you will then be able to make the download available.</p>";
      $text .= "<div>";
      $text .= DOWLAN_21."<br/>";
      $text .= " <img src='".ADMIN_TRUE_ICON_PATH."' title='".DOWLAN_123."' alt='' style='cursor:help'/> ".DOWLAN_123."<br/>";
      $text .= " <img src='".ADMIN_WARNING_ICON_PATH."' title='".DOWLAN_124."' alt='' style='cursor:help'/> ".DOWLAN_124."<br/>";
      $text .= " <img src='".ADMIN_FALSE_ICON_PATH."' title='".DOWLAN_122."' alt='' style='cursor:help'/> ".DOWLAN_122."<br/>";
      $text .= "</div>";
   }
}
$ns -> tablerender(DOWLAN_HELP_1, $text);
?>