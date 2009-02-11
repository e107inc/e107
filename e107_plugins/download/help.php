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
|     $Revision: 1.2 $
|     $Date: 2009-02-11 21:41:54 $
|     $Author: bugrain $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if (e_QUERY)
{
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$subAction = $tmp[1];
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
      switch($subAction) {
         case 'duplicates': {
            $text = DOWLAN_HELP_5_1;
            break;
         }
         case 'orphans': {
            $text = DOWLAN_HELP_5_2;
            break;
         }
         case 'missing': {
            $text = DOWLAN_HELP_5_3;
            break;
         }
         case 'inactive': {
            $text = DOWLAN_HELP_5_4;
            break;
         }
         case 'nocategory': {
            $text = DOWLAN_HELP_5_5;
            break;
         }
         case 'filesize': {
            $text = DOWLAN_HELP_5_6;
            break;
         }
         case 'log': {
            $text = DOWLAN_HELP_5_7;
            break;
         }
         default: {
            $text = DOWLAN_HELP_5;
            break;
         }
      }
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