<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/login_menu/login_menu_template.php,v $
|     $Revision: 1.3 $
|     $Date: 2006-01-25 20:17:09 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
$sc_style['LM_SIGNUP_LINK']['pre'] = "<br />[ ";
$sc_style['LM_SIGNUP_LINK']['post'] = " ]";
if (!isset($LOGIN_MENU_FORM)){

	$LOGIN_MENU_FORM = "
	<div style='text-align: center'>".
    LOGIN_MENU_L1."
	<br />\n
	{LM_USERNAME_INPUT}
	<br />".
	LOGIN_MENU_L2."
	<br />\n
    {LM_PASSWORD_INPUT}
	<br />\n
  {LM_IMAGECODE}
	{LM_LOGINBUTTON}
  {LM_REMEMBERME}
	<br />
	{LM_SIGNUP_LINK}
	<br />
	[ {LM_FPW_LINK} ]
	</div>
	";
}
?>