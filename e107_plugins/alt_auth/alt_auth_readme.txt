/* 
|  Copyright (C) 2003 Thom Michelbrink
|
|  Author:  Thom Michelbrink     mcfly@e107.org
|
*/

Purpose:

  This is a plugin for the E107 CMS system (e107.org).
  This plugin will enable Alternate authorization functionality to your site.
    
Requirements:

  This plugin requires e107 Verion 0.600+

############## INSTALLATION ####################### 

1) Upload all files to your e107_plugins directory on your server, retaining directory structure.
2) Go to the admin section of the website, go the to plugin manager and install the Alt auth.
3) Go to the admin section of the website and configure the Alternate Authorization setting.

Until this is integrated into the e107 core. the following lines need to be added to the e107_handlers\login.php file.  They need to be inserted into the userlogin() function, just after the 'global $pref;' line:

		if($pref['auth_method'] && $pref['auth_method'] != "e107"){
			$auth_file=e_PLUGIN."alt_auth/".$pref['auth_method']."_auth.php";
			if(file_exists($auth_file)){
				require_once(e_PLUGIN."alt_auth/alt_auth_login_class.php");
				$result = new alt_login($pref['auth_method'],$username, $userpass);
			}
		}


--- AUTHORIZATION TYPES --
This version currently supports Active Directory and LDAP authorization types.  Others could easily 
be added though.

The requirements to add a new auth type are:

xxx_auth.php - Actual file the performs the authorization based on user input of uname / passwd.
xxx_conf.php - The file used to edit any configuration option for your auth type.

The xxx_auth.php must contain a class named auth_login(), the class must contain a function named login($uname,$passwd).  The login() function must return values of:
AUTH_SUCCESS - valid login
AUTH_NOUSER - User not found
AUTH_BADPASSWORD - Password is incorrect
-----------------------------------------------------------------------------

Version history:

11/11/2003 - Initial beta release 
 
