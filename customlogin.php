<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/customlogin.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo SITENAME; ?></title>
<link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
<link rel="stylesheet" href="<?php echo e_BASE."files/"; ?>e107.css" />
<?php
if(file_exists(e_BASE."files/style.css")){ echo "\n<link rel='stylesheet' href='".e_BASE."files/style.css' />\n"; }
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo $pref['meta_tag'][1]."\n";
if(eregi("forum_post.php", e_SELF) && ($_POST['reply'] || $_POST['newthread'])){
	$tmp = explode(".", e_QUERY);
//	echo "<meta http-equiv=\"refresh\" content=\"5;url='".e_HTTP."forum_viewforum.php?".$tmp[1]."'>\n";
}
?>

<script type="text/javascript" src="files/e107.js"></script>
<?php
if(file_exists(THEME."theme.js")){echo "<script type='text/javascript' src='".THEME."theme.js'></script>";}
if(file_exists(e_BASE."files/user.js")){echo "<script type='text/javascript' src='".e_BASE."files/user.js'></script>\n";}
?>
</head>
<body>
<br />
<?php

if(!USER){
	echo "<div style='text-align:center'><img src='".e_BASE."themes/shared/mlogo.png' alt='' /></div><br />";
	require_once(e_BASE."classes/form_handler.php");
	$rs = new form;
	$text = "
<div style='text-align:center'>".
$rs -> form_open("post", e_SELF)."
<table class='fborder' style='width:30%'>
<tr>
<td class='forumheader3' style='width:30%'>
User name
</td>
<td class='forumheader3' style='width:70%; text-align:right'>".
$rs -> form_text("username", 40, "", 100)."
</td>
</tr>
<td class='forumheader3' style='width:30%'>
User password
</td>
<td class='forumheader3' style='width:70%; text-align:right'>".
$rs -> form_password("userpass", 40, "", 100)."
</td>
</tr>
<tr>
<td class='forumheader' colspan='2' style='text-align:center'>".
$rs -> form_button("submit", "userlogin", "Log In", "", "Click to login")."
</td>
</tr>
</table>".
$rs -> form_close()."
</div>";
	
$ns -> tablerender("Protected server | ".SITENAME." | please enter your details to gain access", $text);	

}else{
	header("location:".e_BASE."news.php");
	exit;
}


echo "</body>
</html>";

$sql -> db_Close();
?>