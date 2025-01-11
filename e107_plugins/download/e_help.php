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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/e_help.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$action = vartrue($_GET['action']);


switch(vartrue($action)) {
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
   case 'ulist' :{
      $text = DOWLAN_HELP_8;
      break;
   }
   case 'utype' :{
      $text = DOWLAN_HELP_9;
      break;
   }
   case 'uopt' :{
      $text = DOWLAN_HELP_10;
      break;
   }
   default : {
   	/*
      $text = "<p>Please upload your files into the ".e_DOWNLOAD." folder, your images into the ".e_FILE."downloadimages folder and thumbnail images into the ".e_FILE."downloadthumbs folder.</p>
         <p>To submit a download, first create a parent, then create a category under that parent, you will then be able to make the download available.</p>";
      $text .= "<div>";
      $text .= DOWLAN_21."<br/>";
      $text .= " <img src='".ADMIN_TRUE_ICON_PATH."' title='".DOWLAN_123."' alt='' style='cursor:help'/> ".DOWLAN_123."<br/>";
      $text .= " <img src='".ADMIN_WARNING_ICON_PATH."' title='".DOWLAN_124."' alt='' style='cursor:help'/> ".DOWLAN_124."<br/>";
      $text .= " <img src='".ADMIN_FALSE_ICON_PATH."' title='".DOWLAN_122."' alt='' style='cursor:help'/> ".DOWLAN_122."<br/>";
      $text .= "</div>";
	 
	 */
    $text = "";
   }
}
if($text)
{
	$ns -> tablerender(DOWLAN_HELP_1, $text);	
}

