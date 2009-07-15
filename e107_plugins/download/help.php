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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/download/help.php,v $
|     $Revision: 1.3 $
|     $Date: 2009-07-15 00:15:01 $
|     $Author: bugrain $
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
      $text = "Please upload your files into the ".e_DOWNLOAD." folder, your images into the ".e_FILE."downloadimages folder and thumbnail images into the ".e_FILE."downloadthumbs folder.
      <br /><br />
      To submit a download, first create a parent, then create a category under that parent, you will then be able to make the download available.";
   }
}
$ns -> tablerender(DOWLAN_HELP_1, $text);
?>