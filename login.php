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
$HEADER = "<br>";
require_once(HEADERF);
$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
if($use_imagecode){
        require_once(e_HANDLER."secure_img_handler.php");
        $sec_img = new secure_image;
}


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
</tr></table>".
$rs -> form_close()."
</div>";

$login_message = "<center>".LAN_LOGIN_3." | ".SITENAME." | ".LAN_LOGIN_4."</center>";

echo "<div style='text-align:center'><div align='center' style='text-align:center; width:75%'>";

$ns -> tablerender($login_message, $text);
echo "</div></div>
<div style='text-align:center'><br>";

if($pref['user_reg']){
        echo "<a href='".e_SIGNUP."'>Signup</a>";
}
echo "&nbsp;&nbsp;&nbsp;<a href='fpw.php'>Forgot Password</a></div>";

}else{
header("location:".e_BASE."index.php");
exit;
}

echo "</body>
</html>";

$sql -> db_Close();
?>