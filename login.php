<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /customlogin.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/


require_once("class2.php");
if(isset($_POST['fpw'])){header("Location:fpw.php"); exit; }
if(isset($_POST['signup'])){header("Location:signup.php"); exit; }

$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
if($use_imagecode){
	require_once(e_HANDLER."secure_img_handler.php");
	$sec_img = new secure_image;
}

echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<!--
<style type="text/css">

img {
	behavior:        url("pngbehavior.htc");
}

</style>
-->
<title><?php echo SITENAME; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<!-- <link rel="stylesheet" href="<?php echo e_BASE."e107_files/"; ?>e107.css" />-->
<?php
echo "\n<link rel='stylesheet' href='".THEME."style.css' />\n";
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo $pref['meta_tag'][1]."\n";
if(eregi("forum_post.php", e_SELF) && ($_POST['reply'] || $_POST['newthread'])){
	$tmp = explode(".", e_QUERY);
}
?>

<script type="text/javascript" src="e107_files/e107.js"></script>
<?php
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>";}
if(file_exists(e_BASE."e107_files/user.js")){echo "<script type='text/javascript' src='".e_BASE."e107_files/user.js'></script>\n";}
?>
</head>
<body>
<br />
<?php

if(!USER){

	echo "<div style='text-align:center' align='center'>";
	if(file_exists(THEME."images/login_logo.png")){
		echo "<DIV STYLE=\"width:100%; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(
		src='".THEME."images/login_logo.png', sizingMethod='image');\" ></DIV>\n";
	} else{
	echo "<DIV STYLE=\"width:100%; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(
	src='".$IMAGES_DIRECTORY."logo.png', sizingMethod='image');\" ></DIV>\n";
}
echo "</div><br />";
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$text = "";
if(LOGINMESSAGE != ""){
	$text = "<div style='text-align:center'>".LOGINMESSAGE."</div>";
}
$text .= "
<div style='text-align:center'>".
$rs -> form_open("post", e_SELF)."
<table class='fborder' style='width:30%'>
<tr>
<td class='forumheader3' style='width:30%'>
".LAN_LOGIN_1."
</td>
<td class='forumheader3' style='width:70%; text-align:right'>".
$rs -> form_text("username", 40, "", 100)."
</td>
</tr>
<td class='forumheader3' style='width:30%'>
".LAN_LOGIN_2."
</td>
<td class='forumheader3' style='width:70%; text-align:right'>".
$rs -> form_password("userpass", 40, "", 100)."
</td>
</tr>";

		if($use_imagecode){
			$text .= "<tr><td class='forumheader3'>".LAN_LOGIN_7."</td><td class='forumheader3'><input type='hidden' name='rand_num' value='".$sec_img -> random_number."'>";
			$text .= $sec_img -> r_image();
			$text .= "<br /><input class='tbox' type='text' name='code_verify' size='15' maxlength='20'><br /></td></tr>";
		}

$text .= "<tr>
<td class='forumheader' colspan='2' style='text-align:center'>".
$rs -> form_button("submit", "userlogin", "Log In", "", "Click to login")."
</td>
</tr><tr><td class='forumheader3' colspan='2'>
	<div style='text-align:center'>";

if($pref['user_reg']){
	$text .= 
	$rs -> form_button("submit", "signup", "Signup", "", "Click to signup")."
	";
}
$text .= $rs -> form_button("submit", "fpw", "Forgot Password", "", "Click to signup")."
</div>
	</td></tr></table>".
$rs -> form_close()."
</div>";

$login_message = "<center>".LAN_LOGIN_3." | ".SITENAME." | ".LAN_LOGIN_4."</center>";

echo "<div style='text-align:center'><div align='center' style='text-align:center; width:75%'>";

$ns -> tablerender($login_message, $text);
echo "</div></div>";


}else{
header("location:".e_BASE."index.php");
exit;
}

echo "</body>
</html>";

$sql -> db_Close();
?>