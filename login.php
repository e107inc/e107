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
|     $Source: /cvs_backup/e107_0.7/login.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-14 12:57:05 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

require_once("class2.php");
$HEADER = "<div>&nbsp;</div>";
require_once(HEADERF);
$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
if($use_imagecode){
        require_once(e_HANDLER."secure_img_handler.php");
        $sec_img = new secure_image;
}

if(!USER){
	require_once(e_HANDLER."form_handler.php");
	$rs = new form;
	$text = "";

	$LOGIN_TABLE_LOGINMESSAGE = LOGINMESSAGE;
	$LOGIN_TABLE_USERNAME = $rs -> form_text("username", 40, "", 100);
	$LOGIN_TABLE_PASSWORD = $rs -> form_password("userpass", 40, "", 100);
	if($use_imagecode){
			$LOGIN_TABLE_LAN = LAN_LOGIN2;
			$LOGIN_TABLE_HIDDEN = "<input type='hidden' name='rand_num' value='".$sec_img -> random_number."'>";
			$LOGIN_TABLE_SECIMG = $sec_img -> r_image();
			$LOGIN_TABLE_TEXTBOC = "<input class='tbox' type='text' name='code_verify' size='15' maxlength='20'>";
	}
	$LOGIN_TABLE_AUTOLOGIN = $rs -> form_checkbox("autologin", "1");
	$LOGIN_TABLE_AUTOLOGIN_LAN = LAN_LOGIN_8;
	$LOGIN_TABLE_SUBMIT = $rs -> form_button("submit", "userlogin", LAN_LOGIN_9, "", LAN_LOGIN_10);

	if(!$LOGIN_TABLE){
		if(file_exists(THEME."login_template.php")){
			require_once(THEME."login_template.php");
		}else{
			require_once(e_BASE.$THEMES_DIRECTORY."templates/login_template.php");
		}
	}
	$text = preg_replace("/\{(.*?)\}/e", '$\1', $LOGIN_TABLE);

	echo preg_replace("/\{(.*?)\}/e", '$\1', $LOGIN_TABLE_HEADER);

	$login_message = LAN_LOGIN_3." | ".SITENAME;
	$ns -> tablerender($login_message, $text);

	if($pref['user_reg']){
		$LOGIN_TABLE_FOOTER_USERREG = "<a href='".e_SIGNUP."'>".LAN_LOGIN_11."</a>";
	}
	echo preg_replace("/\{(.*?)\}/e", '$\1', $LOGIN_TABLE_FOOTER);

}else{
	header("location:".e_BASE."index.php");
	exit;
}

echo "</body></html>";

$sql -> db_Close();

?>