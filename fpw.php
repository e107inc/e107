<?php

/*
+---------------------------------------------------------------+
|        e107 website system
|        /fpw.php
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
$use_imagecode = ($pref['logcode'] && extension_loaded("gd"));
if($use_imagecode){
        require_once(e_HANDLER."secure_img_handler.php");
        $sec_img = new secure_image;
}
if($pref['membersonly_enabled']){
        $HEADER = "<div style='width:70%;margin-left:auto;margin-right:auto'><div style='text-align:center;'><br />";
        if(file_exists(THEME."images/login_logo.png")){
        $HEADER .= "<img src='".THEME."images/login_logo.png' alt='' />\n";
        } else{
        $HEADER .= "<img src='".e_IMAGE."logo.png' alt='' />\n";
        }
        $HEADER .= "<br />";
        $FOOTER = "</div></div>";
}

require_once(HEADERF);

function fpw_error($txt){
        global $ns;
        $ns -> tablerender(LAN_03,"<div class='forumheader3' style='text-align:center'>".$txt."</div>");
        require_once(FOOTERF);
        exit;
}

if(e_QUERY){
        $tmp=explode(".",e_QUERY);
        $tmpinfo=preg_replace("#[\W_]#","",$tmp[0]);
        if($sql -> db_Select("tmp","*","tmp_info LIKE '%.{$tmpinfo}' ")){
                $row = $sql -> db_Fetch();
                extract($row);
                $sql -> db_Delete("tmp","tmp_info LIKE '%.{$tmpinfo}' ");
                $newpw="";
                $pwlen = rand(8, 12);
                for($a=0; $a<=$pwlen;$a++){
                        $newpw .= chr(rand(97, 122));
                }
                $mdnewpw = md5($newpw);

                list($username,$md5) = explode(".",$tmp_info);
                $sql -> db_Update("user", "user_password='$mdnewpw', user_viewed='' WHERE user_name='$username' ");
                cookie($pref['cookie_name'], "", (time()-2592000));
                $_SESSION[$pref['cookie_name']] = "";

                $txt = LAN_FPW8." {$username} ".LAN_FPW9." {$newpw}<br /><br />".LAN_FPW10;
                fpw_error($txt);

        } else {
                fpw_error(LAN_FPW7);
        }
}

if(IsSet($_POST['pwsubmit'])){
        require_once(e_HANDLER."mail.php");
        $email = $_POST['email'];

        if($pref['logcode'] && extension_loaded("gd")){
                if(!$sec_img -> verify_code($_POST['rand_num'],$_POST['code_verify'])){
                        fpw_error(LAN_FPW3);
                }
        }

        if($sql -> db_Select("user", "*", "user_email='{$_POST['email']}' AND user_name='{$_POST['username']}' ")){
                $row = $sql -> db_Fetch(); extract($row);

                if($user_admin == 1 && $user_perms == "0"){
                        sendemail($pref['siteadminemail'], ".LAN_06.", "".LAN_07."".getip()." ".LAN_08);
                        echo "<script type='text/javascript'>document.location.href='index.php'</script>\n";
                        die();
                }

                if($sql -> db_Select("tmp","*","tmp_ip = 'pwreset' AND tmp_info LIKE '{$user_name}.%'")){
                        fpw_error(LAN_FPW4);
                        exit;
                }

                mt_srand ((double)microtime()*1000000);
                $maxran = 1000000;
                $rand_num = mt_rand(0, $maxran);
                $datekey = date("r");
                $rcode = md5($_SERVER[HTTP_USER_AGENT] . serialize($pref). $rand_num . $datekey);

                $link = SITEURL."fpw.php?{$rcode}";
                $message = LAN_FPW5."\n\n{$link}";

                $deltime = time()+86400*2;  //Set timestamp two days ahead so it doesn't get auto-deleted
                $sql -> db_Insert("tmp","'pwreset',{$deltime},'{$user_name}.{$rcode}'");

                if(sendemail($_POST['email'], "".LAN_09."".SITENAME, $message)){
                        $text = "<div style='text-align:center'>".LAN_FPW6."</div>";
                }else{
                        $text = "<div style='text-align:center'>".LAN_02."</div>";
                }

                $ns -> tablerender(LAN_03, $text);
                require_once(FOOTERF);
                exit;
        }else{
                $text = LAN_213;
                $ns -> tablerender(LAN_214, "<div style='text-align:center'>".$text."</div>");
        }
}
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>

<tr>
<td class='forumheader3' colspan='2' style='text-align:center'>".LAN_05."</td>
</tr>

<tr>
<td class='forumheader3' style='width:25%'>".LAN_FPW1."</td>
<td class='forumheader3' style='width:75%;text-align:center'>
<input class='tbox' type='text' name='username' size='60' value='' maxlength='100' />
</td>
</tr>

<tr>
<td class='forumheader3' style='width:25%'>".LAN_112."</td>
<td class='forumheader3' style='width:75%; text-align:center'>
<input class='tbox' type='text' name='email' size='60' value='' maxlength='100' />
</td>
</tr>";

if($use_imagecode){
        $text .= "
        <tr>
        <td class='forumheader3' style='width:25%'>".LAN_FPW2."</td>
        <td class='forumheader3' style='width:75%;text-align:left'>
        <input type='hidden' name='rand_num' value='".$sec_img -> random_number."'>";
        $text .= $sec_img -> r_image();
        $text .= "<br /><input class='tbox' type='text' name='code_verify' size='15' maxlength='20'><br />";
        $text .= "</td></tr>";
}

$text .="
<tr style='vertical-align:top'>
<td class='forumheader' colspan='2'  style='text-align:center'>
<input class='button' type='submit' name='pwsubmit' value='".LAN_156."' />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(LAN_03, $text);
require_once(FOOTERF);
?>