<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Administration Icons, should handle all icons in the future (somehow)
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/admin_icons_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/
/*
	define("ADMIN_TRUE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/true_16.png' alt='' />");
	define("ADMIN_TRUE_ICON_PATH", e_IMAGE_ABS."admin_images/true_16.png");

	define("ADMIN_FALSE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/false_16.png' alt='' />");
	define("ADMIN_FALSE_ICON_PATH", e_IMAGE_ABS."admin_images/false_16.png");

	define("ADMIN_EDIT_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/edit_16.png' alt='' title='".LAN_EDIT."' />");
	define("ADMIN_EDIT_ICON_PATH", e_IMAGE_ABS."admin_images/edit_16.png");

	define("ADMIN_DELETE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/delete_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DELETE_ICON_PATH", e_IMAGE_ABS."admin_images/delete_16.png");

	define("ADMIN_UP_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/up_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_UP_ICON_PATH", e_IMAGE_ABS."admin_images/up_16.png");

	define("ADMIN_DOWN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/down_16.png' alt='' title='".LAN_DELETE."' />");
	define("ADMIN_DOWN_ICON_PATH", e_IMAGE_ABS."admin_images/down_16.png");

	define("ADMIN_WARNING_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/warning_16.png' alt='' />");
	define("ADMIN_WARNING_ICON_PATH", e_IMAGE_ABS."admin_images/warning_16.png");

	define("ADMIN_INFO_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/info_16.png' alt='' />");
	define("ADMIN_INFO_ICON_PATH", e_IMAGE_ABS."admin_images/info_16.png");

	define("ADMIN_CONFIGURE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/configure_16.png' alt='' />");
	define("ADMIN_CONFIGURE_ICON_PATH", e_IMAGE_ABS."admin_images/configure_16.png");

	define("ADMIN_ADD_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/add_16.png' alt='' />");
	define("ADMIN_ADD_ICON_PATH", e_IMAGE_ABS."admin_images/add_16.png");

	define("ADMIN_VIEW_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/search_16.png' alt='' />");
	define("ADMIN_VIEW_ICON_PATH", e_IMAGE_ABS."admin_images/admin_images/search_16.png");

	define("ADMIN_URL_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/forums_16.png' alt='' />");
	define("ADMIN_URL_ICON_PATH", e_IMAGE_ABS."admin_images/forums_16.png");

	define("ADMIN_INSTALLPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/plugin_install_16.png' alt='' />");
	define("ADMIN_INSTALLPLUGIN_ICON_PATH", e_IMAGE_ABS."admin_images/plugin_install_16.png");

	define("ADMIN_UNINSTALLPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/plugin_uninstall_16.png' alt='' />");
	define("ADMIN_UNINSTALLPLUGIN_ICON_PATH", e_IMAGE_ABS."admin_images/plugin_unstall_16.png");

	define("ADMIN_UPGRADEPLUGIN_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/up_16.png' alt='' />");
	define("ADMIN_UPGRADEPLUGIN_ICON_PATH", e_IMAGE_ABS."admin_images/up_16.png");
	
	define("ADMIN_EXECUTE_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/execute_16.png' alt='' title='Run' />");//TODO LAN
	define("ADMIN_EXECUTE_ICON_PATH", e_IMAGE."admin_images/execute_16.png");
	
	define("ADMIN_SORT_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/sort_16.png' alt='' title='Sort' />"); //TODO LAN
	define("ADMIN_SORT_ICON_PATH", e_IMAGE."admin_images/sort_16.png");
 
 	define("E_32_TRUE", "<i class='S32 e-true-32'></i>");

	
*/




	//XXX Do NOT use 'title' attributes - these should go in the <a> 


	
	if(!defined('ADMIN_EDIT_ICON') && !defined('ADMIN_TRUE_ICON'))
	{
		define("ADMIN_TRUE_ICON", 			"<i class='S16 e-true-16'></i>");
		define("ADMIN_FALSE_ICON", 			"<i class='S16 e-false-16'></i>");
		define("ADMIN_EDIT_ICON", 			"<i class='S16 e-edit-16' ></i>"); 	
		define("ADMIN_DELETE_ICON", 		"<i class='S16 e-delete-16'></i>");	
		define("ADMIN_UP_ICON", 			"<i class='S16 e-up-16'></i>"); 
		define("ADMIN_DOWN_ICON", 			"<i class='S16 e-down-16'></i>"); 
		define("ADMIN_WARNING_ICON", 		"<i class='S16 e-warning-16'></i>");	
		define("ADMIN_INFO_ICON", 			"<i class='S16 e-info-16'></i>");	
		define("ADMIN_CONFIGURE_ICON", 		"<i class='S16 e-configure-16'></i>");	
		define("ADMIN_ADD_ICON", 			"<i class='S16 e-add-16'></i>");	
		define("ADMIN_VIEW_ICON", 			"<i class='S16 e-search-16'></i>");
		define("ADMIN_URL_ICON", 			"<i class='S16 e-forums-16'></i>");
		define("ADMIN_INSTALLPLUGIN_ICON", 	"<i class='S16 e-plugin_install-16'></i>");	
		define("ADMIN_UNINSTALLPLUGIN_ICON","<i class='S16 e-plugin_uninstall-16'></i>");	
		define("ADMIN_UPGRADEPLUGIN_ICON", 	"<i class='S16 e-up-16'></i>");
		define("ADMIN_EXECUTE_ICON",  		"<i class='S16 e-execute-16'></i>");
		define("ADMIN_SORT_ICON", 			"<i class='S16 e-sort'></i>"); 
		
		define("ADMIN_TRUE_ICON_PATH", e_IMAGE_ABS."admin_images/true_16.png"); 		//XXX DEPRECATED but used in v1.x
		define("ADMIN_FALSE_ICON_PATH", e_IMAGE_ABS."admin_images/false_16.png"); 		//XXX DEPRECATED but used in v1.x
		define("ADMIN_EDIT_ICON_PATH", e_IMAGE_ABS."admin_images/edit_16.png");			//XXX DEPRECATED but used in v1.x
		define("ADMIN_DELETE_ICON_PATH", e_IMAGE_ABS."admin_images/delete_16.png"); 	//XXX DEPRECATED but used in v1.x
		define("ADMIN_WARNING_ICON_PATH", e_IMAGE_ABS."admin_images/warning_16.png"); 	//XXX DEPRECATED but used in v1.x
	}
	


			
				

	
	
	
	

//	define("ADMIN_UP_ICON_PATH", e_IMAGE_ABS."admin_images/up_16.png"); //XXX DEPRECATED

//	define("ADMIN_DOWN_ICON_PATH", e_IMAGE_ABS."admin_images/down_16.png"); //XXX DEPRECATED
//	define("ADMIN_INFO_ICON_PATH", e_IMAGE_ABS."admin_images/info_16.png"); //XXX DEPRECATED
//	define("ADMIN_CONFIGURE_ICON_PATH", e_IMAGE_ABS."admin_images/configure_16.png"); //XXX DEPRECATED
//	define("ADMIN_ADD_ICON_PATH", e_IMAGE_ABS."admin_images/add_16.png"); //XXX DEPRECATED
//	define("ADMIN_VIEW_ICON_PATH", e_IMAGE_ABS."admin_images/admin_images/search_16.png"); //XXX DEPRECATED
//	define("ADMIN_URL_ICON_PATH", e_IMAGE_ABS."admin_images/forums_16.png"); //XXX DEPRECATED
//	define("ADMIN_INSTALLPLUGIN_ICON_PATH", e_IMAGE_ABS."admin_images/plugin_install_16.png"); //XXX DEPRECATED
//	define("ADMIN_UNINSTALLPLUGIN_ICON_PATH", e_IMAGE_ABS."admin_images/plugin_unstall_16.png"); //XXX DEPRECATED
//	define("ADMIN_UPGRADEPLUGIN_ICON_PATH", e_IMAGE_ABS."admin_images/up_16.png"); //XXX DEPRECATED
//	define("ADMIN_EXECUTE_ICON",  "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/execute_16.png' alt='' title='Run' />");
//	define("ADMIN_EXECUTE_ICON_PATH", e_IMAGE."admin_images/execute_16.png");  //XXX DEPRECATED
//	define("ADMIN_SORT_ICON", "<img class='icon action S16' src='".e_IMAGE_ABS."admin_images/sort_16.png' alt='' title='Sort' />");

	