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

$membership_open = 1;


require_once("class2.php");
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
// if(file_exists(e_BASE."e107_themes/leap of elohimblue/style.css")){
 echo "\n<link rel='stylesheet' href='".THEME."style.css' />\n";

 // }
?>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo $pref['meta_tag'][1]."\n";
if(eregi("forum_post.php", e_SELF) && ($_POST['reply'] || $_POST['newthread'])){
        $tmp = explode(".", e_QUERY);
//        echo "<meta http-equiv=\"refresh\" content=\"5;url='".e_HTTP."forum_viewforum.php?".$tmp[1]."'>\n";
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
 //       echo "<div style='text-align:center'><img src='".e_BASE."e107_themes/elohimnet/images/elohimnet_login.png' alt='' style='width:439px; height:62px'/></div><br />";
        echo "<div style='text-align:center'>";



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

$login_message = "<center>Protected server | ".SITENAME." | please enter your details to gain access</center>";
echo "<div style='text-align:center'><div align='center' style='text-align:center; width:75%'>";
$ns -> tablerender($login_message, $text);
echo "</div></div>";

        if($membership_open == 1){
        echo "<div style='text-align:center'><a href='".e_SIGNUP."'>Click here to Sign-Up</a></div>";
        }else{
        echo "<div style='text-align:center'>Not accepting new members at this time.</div>";
        }

}else{
        header("location:".e_BASE."news.php");
        exit;
}


echo "</body>
</html>";

$sql -> db_Close();
?>