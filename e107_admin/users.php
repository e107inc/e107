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
|     $Source: /cvs_backup/e107_0.7/e107_admin/users.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-01-07 20:51:34 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit;}

require_once(e_HANDLER."textparse/basic.php");
$etp = new e107_basicparse;

require_once("auth.php");
$user = new users;
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
        $from = ($tmp[3] ? $tmp[3] : 0);
        unset($tmp);
}

$from = ($from ? $from : 0);
$amount = 50;



if(IsSet($_POST['resend_mail'])){
    $id = $_POST['resend_id'];
    $key = $_POST['resend_key'];
    $name = $_POST['resend_name'];
   define("RETURNADDRESS", (substr(SITEURL, -1) == "/" ? SITEURL."signup.php?activate.".$id.".".$key : SITEURL."/signup.php?activate.".$id.".".$key));

   $message = USRLAN_114." ".$_POST['resend_name']."\n\n".USRLAN_122." ".SITENAME."\n".USRLAN_123."\n\n".USRLAN_124."...\n\n";
   $message .= RETURNADDRESS . "\n\n".USRLAN_115."\n\n ".USRLAN_125." ".SITENAME."\n".SITEURL;

   require_once(e_HANDLER."mail.php");
   sendemail($_POST['resend_email'], USRLAN_113." ".SITENAME, $message);
 //  echo str_replace("\n","<br>",$message);
   $user -> show_message("Email Re-sent to: ".$name);
   unset($id,$action,$sub_cation);
}

if(IsSet($_POST['test_mail'])){
   require_once(e_HANDLER."mail.php");
   $text = validatemail($_POST['test_email']);
   $caption = $_POST['test_email']." - ";
   $caption .= ($text[0]==TRUE)?"Successful": "Error";
   $ns -> tablerender($caption, $text[1]);
   unset($id,$action,$sub_cation);
}

if(IsSet($_POST['update_options'])){
        $pref['avatar_upload'] = (FILE_UPLOADS ? $_POST['avatar_upload'] : 0);
        $pref['im_width'] = $_POST['im_width'];
        $pref['im_height'] = $_POST['im_height'];
        $pref['photo_upload'] = (FILE_UPLOADS ? $_POST['photo_upload'] : 0);
                $pref['del_unv'] = $_POST['del_unv'];
        save_prefs();
        $user -> show_message(USRLAN_1);
}

if(IsSet($_POST['prune'])){
        $sql2 = new db;
        $text = USRLAN_56." ";
        if($sql -> db_Select("user", "user_id, user_name", "user_ban=2")){
                while($row = $sql -> db_Fetch()){
                        extract($row);
                        $text .= $user_name." ";
                        $sql2 -> db_Delete("user", "user_id='$user_id' ");
                }
        }
        $ns -> tablerender(USRLAN_57, "<div style='text-align:center'><b>".$text."</b></div>");
        unset($text);
}

if(IsSet($_POST['adduser'])){
        if(!$_POST['ac'] == md5(ADMINPWCHANGE)){
                exit;
        }

        require_once(e_HANDLER."message_handler.php");
        if(strstr($_POST['name'], "#") || strstr($_POST['name'], "=")){
                message_handler("P_ALERT", USRLAN_92);
                $error = TRUE;
        }
        $_POST['name'] = trim(chop(str_replace("&nbsp;", "", $_POST['name'])));
        if($_POST['name'] == "Anonymous"){
                message_handler("P_ALERT", USRLAN_65);
                $error = TRUE;
        }
        if($sql -> db_Select("user", "*", "user_name='".$_POST['name']."' ")){
                message_handler("P_ALERT", USRLAN_66);
                $error = TRUE;
        }
        if($_POST['password1'] != $_POST['password2']){
                message_handler("P_ALERT", USRLAN_67);
                $error = TRUE;
        }

        if($_POST['name'] == "" || $_POST['password1'] =="" || $_POST['password2'] = ""){
                message_handler("P_ALERT", USRLAN_68);
                $error = TRUE;
        }
                if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['email'])){
           message_handler("P_ALERT", USRLAN_69);
           $error = TRUE;
        }
        if(!$error){
                if($sql -> db_Select("user", "*", "user_email='".$_POST['email']."' AND user_ban='1' ")){
                        exit;
                }
                if($sql -> db_Select("banlist", "*", "banlist_ip='".$_POST['email']."'")){
                        exit;
                }

                $username = strip_tags($_POST['name']);
                $ip = getip();
                                extract($_POST);
                            for($a=0; $a<=(count($_POST['userclass'])-1); $a++){
                                                           $svar .= $userclass[$a].".";
                                }
                $sql -> db_Insert("user", "0, '".$username."', '', '".md5($_POST['password1'])."', '$key', '".$_POST['email']."',         '".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '1', '".time()."', '0', '".$time."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '0', '".$_POST['realname']."', '".$svar."', '', '', '' ");
                $user -> show_message(USRLAN_70);
        }
}

if($_POST['useraction'] == 'userinfo')
{
        header('location:'.SITEURL.$ADMIN_DIRECTORY."userinfo.php?{$_POST['userip']}");
        exit;
}

if($_POST['useraction'] == 'usersettings')
{
        header('location:'.SITEURL."usersettings.php?{$_POST['userid']}");
        exit;
}

if($_POST['useraction'] == 'userclass')
{
        header('location:'.SITEURL.$ADMIN_DIRECTORY."userclass.php?{$_POST['userid']}");
        exit;
}


if($_POST['useraction'] == "ban")
{
        $sub_action = $_POST['userid'];
        $sql -> db_Select("user", "*", "user_id='$sub_action'");
        $row = $sql -> db_Fetch(); extract($row);
        if($user_perms == "0")
        {
                $user -> show_message(USRLAN_7);
        } else
        {
                $sql -> db_Update("user", "user_ban='1' WHERE user_id=$sub_action");
                $user -> show_message(USRLAN_8);
        }
        $action = "main";
        $sub_action = "user_id";
}

if($_POST['useraction'] == "unban")
{
        $sub_action=$_POST['userid'];
        $sql -> db_Update("user", "user_ban='0' WHERE user_id='$sub_action' ");
        $user -> show_message(USRLAN_9);
        $action = "main";
        $sub_action = "user_id";
}


if($_POST['useraction'] == 'resend')
{
        $sub_action=$_POST['userid'];
        if($sql -> db_Select("user", "*", "user_id='$sub_action' "))
        {
                $resend = $sql -> db_Fetch();
                $text .= "<form method='post' action='".e_SELF."'><div style='text-align:center'>\n";
                $text .= USRLAN_116." <b>".$resend['user_name']."</b><br /><br />

                <input type='hidden' name='resend_id' value='$sub_action' />\n
                <input type='hidden' name='resend_name' value='".$resend['user_name']."' />\n
                <input type='hidden' name='resend_key' value='".$resend['user_sess']."' />\n
                <input type='hidden' name='resend_email' value='".$resend['user_email']."' />\n
                <input class='button' type='submit' name='resend_mail' value='".USRLAN_112."' />\n</div></form>\n";
                $caption = USRLAN_112;
                $ns -> tablerender($caption, $text);
        }
}

if($_POST['useraction'] == 'test')
{
        $sub_action=$_POST['userid'];
        if($sql -> db_Select("user", "*", "user_id='$sub_action' ")){
                $test = $sql -> db_Fetch();
                $text .= "<form method='post' action='".e_SELF."'><div style='text-align:center'>\n";
                $text .= USRLAN_117." <br /><b>".$test['user_email']."</b><br /><br />
                <input type='hidden' name='test_email' value='".$test['user_email']."' />\n
                <input class='button' type='submit' name='test_mail' value='".USRLAN_118."' />\n</div></form>\n";
                $caption = USRLAN_118;
                $ns -> tablerender($caption, $text);
        }
}

if($_POST['useraction'] == 'deluser')
{
        $sub_action=$_POST['userid'];
        if($_POST['confirm'])
        {
                if($sql -> db_Delete("user", "user_id=$sub_action AND user_perms != '0'"))
                {
                        $user -> show_message(USRLAN_10);
                }
                $sub_action = "user_id";
                $id = "DESC";
        } else
        {
                if($sql -> db_Select("user", "*", "user_id='$sub_action' "))
                {
                        $row = $sql -> db_Fetch();
                        $text .= "<form method='post' action='".e_SELF."'><div style='text-align:center'>\n";
                        $text .= "<div>
                         <input type='hidden' name='useraction' value='deluser' />
                        <input type='hidden' name='userid' value='{$row['user_id']}' /></div>".
                        USRLAN_13."
                        <br /><br /><span class='indent'>#{$row['user_id']} : {$row['user_name']}</span>
                        <br /><br />
                        <input type='submit' class='button' name='confirm' value='".USRLAN_17."' />
                        &nbsp;&nbsp;
                        <input type='submit' class='button' name='cancel' value='".USRLAN_15."' />
                        </div>
                        </form>
                        ";
                        $ns -> tablerender(USRLAN_16,$text);
                        require_once("footer.php");
                        exit;
                }
        }
}

if($_POST['useraction'] == "admin")
{
        $sub_action = $_POST['userid'];
        $sql -> db_Select("user", "*", "user_id='$sub_action'");
        $row = $sql -> db_Fetch(); extract($row);
        $sql -> db_Update("user", "user_admin='1' WHERE user_id=$sub_action");
        $user -> show_message($user_name." ".USRLAN_3." <a href='".e_ADMIN."administrator.php?edit.$sub_action'>".USRLAN_4."</a>");
        $action = "main";
        $sub_action = "user_id";
        $id = "DESC";
}

if($_POST['useraction'] == "unadmin")
{
        $sub_action = $_POST['userid'];
        $sql -> db_Select("user", "*", "user_id='$sub_action'");
        $row = $sql -> db_Fetch(); extract($row);
        if($user_perms == "0"){
                $user -> show_message(USRLAN_5);
        } else
        {
                $sql -> db_Update("user", "user_admin='0' WHERE user_id=$sub_action");
                $user -> show_message($user_name." ".USRLAN_6);
                $action = "main";
                $sub_action = "user_id";
                $id = "DESC";
        }
}

if(IsSet($_POST['add_field'])){
        extract($_POST);
        $sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        // changed by Cameron
        $user_field = str_replace(" ","_",$user_field);
        $user_entended[] = $user_field."|".$user_type."|".$user_value."|".$user_default."|".$user_visible."|".$user_hide;
   //     $user_entended[] = $user_field;
        $tmp = addslashes(serialize($user_entended));
        if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
                $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
        }else{
                $sql -> db_Insert("core", "'user_entended', '$tmp' ");
        }
        $message = USRLAN_2;
}

if(IsSet($_POST['update_field'])){
        extract($_POST);
        $sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
//        unset($user_entended[$sub_action]);
        $user_field = str_replace(" ","_",$user_field);
        $user_entended[$sub_action] = $user_field."|".$user_type."|".$user_value."|".$user_default."|".$user_visible."|".$user_hide;
        $tmp = addslashes(serialize($user_entended));
        if($sql -> db_Select("core", " e107_value", " e107_name='user_entended'")){
                $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
        }else{
                $sql -> db_Insert("core", "'user_entended', '$tmp' ");
        }
        $message = "Updated";
}


if($_POST['eu_action'] == "delext"){
        $sub_action = $_POST['key'];
        $sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        unset($user_entended[$sub_action]);
        $tmp = addslashes(serialize($user_entended));
        $sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='user_entended' ");
        $user -> show_message(USRLAN_83);
//        $action = "extended"; // this line prevents adding another field without resetting.
//        $user -> show_extended();//   this fixes the delete/add problem but causes the showmessage to occur.
}

if($action == "editext"){
        $sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
        $row = $sql -> db_Fetch();
        $user_entended = unserialize($row[0]);
        $tmp = explode("|",$user_entended[$sub_action]);
        $uf_name = $tmp[0];
        $uf_type = $tmp[1];
        $uf_value = $tmp[2];
        $uf_default = $tmp[3];
        $uf_visible = $tmp[4];
        $uf_hide = $tmp[5];
        $user -> show_extended();
}

if($_POST['useraction'] == "verify")
{
        $sub_action = $_POST['userid'];
        if($sql -> db_Update("user", "user_ban='0' WHERE user_id=$sub_action"))
        {
                $user -> show_message(USRLAN_86);
                $action = "main";
                $sub_action = "user_id";
                $id = "DESC";
        }
}

if($action == "main" && is_numeric($sub_action)){
        $user -> show_message(USRLAN_87);
}

if($action == "cu" && is_numeric($sub_action)){
        $user -> show_message(USRLAN_88);
        $action = "main";
        $sub_action = "user_id";
}

if(!e_QUERY || $action == "main"){
        $user -> show_existing_users($action, $sub_action, $id, $from, $amount);
}

if($action == "options"){
        $user -> show_prefs();
}

if($action == "extended"){
        $user -> show_extended();
}

if($action == "prune"){
        $user -> show_prune();
}

if($action == "create"){
        $user -> add_user();
}

//$user -> show_options($action);
require_once("footer.php");
function headerjs(){
global $etp;
$header_js= "<script type=\"text/javascript\">
function confirm_(mode, user_id, user_name){
        if(mode == 'cat'){
                var x=confirm(\"".$etp->unentity(NWSLAN_37)." [ID: \" + user_id + \"]\");
        }else if(mode == 'sn'){
                var x=confirm(\"".$etp->unentity(NWSLAN_38)." [ID: \" + user_id + \"]\");
        }else{
                var x=confirm(\"".$etp->unentity(USRLAN_82)." [".USRLAN_61.": \" + user_name + \"]\");
        }
if(x)
        if(mode == 'cat'){
                window.location='".e_SELF."?cat.confirm.' + user_id;
        }else if(mode == 'sn'){
                window.location='".e_SELF."?sn.confirm.' + user_id;
        }else{
                window.location='".e_SELF."?main.confirm.' + user_id;
        }
}
</script>";
 return $header_js;
}
class users{

        function show_existing_users($action, $sub_action, $id, $from, $amount){
                // ##### Display scrolling list of existing news items ---------------------------------------------------------------------------------------------------------

                global $sql, $rs, $ns, $aj;

                if($sql -> db_Select("userclass_classes")){
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $class[$userclass_id] = $userclass_name;
                        }
                }


                $text = "<div style='text-align:center'><div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>";

                if(IsSet($_POST['searchquery'])){

                        $query = (eregi("@",$_POST['searchquery']))?"user_email REGEXP('".$_POST['searchquery']."') OR ":"";
                        $query .= (eregi(".",$_POST['searchquery']))?"user_ip REGEXP('".$_POST['searchquery']."') OR ":"";
                        $query .= "user_login REGEXP('".$_POST['searchquery']."') OR ";
                        $query .= "user_name REGEXP('".$_POST['searchquery']."') ORDER BY user_id";
                }else{
                        $query = "ORDER BY ".($sub_action ? $sub_action : "user_id")." ".($id ? $id : "DESC")."  LIMIT $from, $amount";
                }


                if($sql -> db_Select("user", "*", $query, ($_POST['searchquery'] ? 0 : "nowhere"))){
                        $text .= "<table class='fborder' style='width: 99%'>
                        <tr>
                        <td style='width:5%' class='forumheader'><a href='".e_SELF."?main.user_id.".($id == "desc" ? "asc" : "desc").".$from'>ID</a></td>
                        <td style='width:10%' class='forumheader'><a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc").".$from'>".USRLAN_79."</a></td>
                        <td style='width:30%' class='forumheader'><a href='".e_SELF."?main.user_name.".($id == "desc" ? "asc" : "desc").".$from'>".USRLAN_78."</a></td>
                        <td style='width:15%' class='forumheader'><a href='".e_SELF."?main.user_class.".($id == "desc" ? "asc" : "desc").".$from'>".USRLAN_91."</a></td>
                        <td style='width:30%' class='forumheader'>".USRLAN_75."</td>
                        </tr>";

                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $text .= "<tr>
                                <td style='width:5%; text-align:center' class='forumheader3'>$user_id</td>
                                <td style='width:10%' class='forumheader3'>";

                                if($user_perms == "0"){
                                        $text .= "<img src='".e_LANIMAGE."/mainadmin.gif' alt='' style='vertical-align:middle' />";
                                }else if($user_admin){
                                        $text .= "<a href='".e_SELF."?main.user_admin.".($id == "desc" ? "asc" : "desc")."'><img src='".e_LANIMAGE."admin.gif' alt='' style='vertical-align:middle; border:0' /></a>";
                                }else if($user_ban == 1){
                                        $text .= "<a href='".e_SELF."?main.user_ban.".($id == "desc" ? "asc" : "desc")."'><img src='".e_LANIMAGE."banned.gif' alt='' style='vertical-align:middle; border:0' /></a>";
                                }else if($user_ban == 2){
                                        $text .= "<img src='".e_LANIMAGE."not_verified.gif' alt='' style='vertical-align:middle' />";
                                }else{
                                        $text .= "&nbsp;";
                                }

                                $text .= "</td>
                                <td style='width:30%' class='forumheader3'><a href='".e_BASE."user.php?id.$user_id'>$user_name</a></td>
                                <td style='width:15%' class='forumheader3'>";

                                if($user_class){
                                        $tmp = explode(".", $user_class);
                                        while(list($key, $class_id) = each($tmp)){
                                                $text .= ($class[$class_id] ? $class[$class_id]."<br />\n" : "");;
                                        }
                                }else{
                                        $text .= "&nbsp;";
                                }


                                $text .= "</td>
                                <td style='width:30%; text-align:center' class='forumheader3'>
                                <form method='post' action='".e_SELF."'>
                                <div>
                                <input type='hidden' name='userid' value='{$user_id}' />
                                <input type='hidden' name='userip' value='{$user_ip}' />
                                <select name='useraction' onchange='this.form.submit()' class='tbox'>
                                <option selected='selected' value=''></option>";

                                if($user_perms != "0"){
                                        $text .= "<option value='userinfo'>".USRLAN_80."</option>
                                        <option value='usersettings'>".USRLAN_81."</option>";

                                        if($user_ban == 1){
                                                $text .= "<option value='unban'>".USRLAN_33."</option>";
                                        }else if($user_ban == 2){
                                                $text .= "<option value='ban'>".USRLAN_30."</option>
                                                <option value='verify'>".USRLAN_32."</option>
                                                <option value='resend'>".USRLAN_112."</option>
                                                <option value='test'>".USRLAN_118."</option>";
                                        }else{
                                                $text .= "<option value='ban'>".USRLAN_30."</option>";
                                        }

                                        if(!$user_admin && !$user_ban && $user_ban != 2){
                                                $text .= "<option value='admin'>".USRLAN_35."</option>";
                                        }else if ($user_admin && $user_perms != "0"){
                                                $text .= "<option value='unadmin'>".USRLAN_34."</option>";
                                        }

                                }       if($user_perms == "0" && !getperms("0")){
                                        $text .="";
                                        } elseif($user_id != USERID || getperms("0") ){
                                        $text .= "<option value='userclass'>".USRLAN_36."</option>";
                                        }

                                if($user_perms != "0")
                                {
                                                $text .= "<option value='deluser'>".USRLAN_29."</option>";
//                                        $text .= $rs -> form_button("submit", "main_$user_id", USRLAN_29, "onclick=\"confirm_('main', '$user_id', '$user_name');\"");
                                }
                                $text .= "</select></div>";
                                 $text .="</form></td></tr>";
                        }
                        $text .= "</table>";
                }
                $text .= "</div>";
                $users = $sql -> db_Count("user");

                if($users > $amount && !$_POST['searchquery']){
                        $a = $users/$amount;
                        $r = explode(".", $a);
                        if($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
                        if($pages){
                                $current = ($from/$amount)+1;
                                $text .= "<br />".USRLAN_89." ";
                                for($a=1; $a<=$pages; $a++){
                                        $text .= ($current == $a ? " <b>[$a]</b>" : " [<a href='".e_SELF."?".(e_QUERY ? "$action.$sub_action.$id." : "main.user_id.desc.").(($a-1)*$amount)."'>$a</a>] ");
                                }
                                $text .= "<br />";
                        }
                }

                $text .= "<br /><form method='post' action='".e_SELF."'>\n<p>\n<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />\n<input class='button' type='submit' name='searchsubmit' value='".USRLAN_90."' />\n</p>\n</form>\n</div>";

                $ns -> tablerender(USRLAN_77, $text);

        }

        function show_options($action){
                // ##### Display options ---------------------------------------------------------------------------------------------------------
                                if($action==""){$action="main";}
                                // ##### Display options ---------------------------------------------------------------------------------------------------------
                                $var['main']['text']=USRLAN_71;
                                $var['main']['link']=e_SELF;

                                $var['create']['text']=USRLAN_72;
                                $var['create']['link']=e_SELF."?create";

                                $var['prune']['text']=USRLAN_73;
                                $var['prune']['link']=e_SELF."?prune";

                                $var['extended']['text']=USRLAN_74;
                                $var['extended']['link']=e_SELF."?extended";

                                $var['options']['text']=USRLAN_75;
                                $var['options']['link']=e_SELF."?options";

                              //  $var['mailing']['text']= USRLAN_121;
                             //   $var['mailing']['link']="mailout.php";
                                show_admin_menu(USRLAN_76,$action,$var);
                   }

        function show_prefs(){
                global $ns, $pref;
                $text = "<div style='text-align:center'>
                <form method='post' action='".e_SELF."?".e_QUERY."'>
                <table style='".ADMIN_WIDTH."' class='fborder'>

                <tr>
                <td style='width:50%' class='forumheader3'>".USRLAN_44.":</td>
                <td style='width:50%' class='forumheader3'>".
                ($pref['avatar_upload'] ? "<input name='avatar_upload' type='radio' value='1' checked='checked' />".USRLAN_45."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' />".USRLAN_46 : "<input name='avatar_upload' type='radio' value='1' />".USRLAN_45."&nbsp;&nbsp;<input name='avatar_upload' type='radio' value='0' checked='checked' />".USRLAN_46).
                (!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
                </td>
                </tr>

                <tr>
                <td style='width:50%' class='forumheader3'>".USRLAN_53.":</td>
                <td style='width:50%' class='forumheader3'>".
                ($pref['photo_upload'] ? "<input name='photo_upload' type='radio' value='1' checked='checked' />".USRLAN_45."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' />".USRLAN_46 : "<input name='photo_upload' type='radio' value='1' />".USRLAN_45."&nbsp;&nbsp;<input name='photo_upload' type='radio' value='0' checked='checked' />".USRLAN_46).
                (!FILE_UPLOADS ? " <span class='smalltext'>(".USRLAN_58.")</span>" : "")."
                </td>
                </tr>

                <tr>
                <td style='width:50%' class='forumheader3'>".USRLAN_47.":</td>
                <td style='width:50%' class='forumheader3'>
                <input class='tbox' type='text' name='im_width' size='10' value='".$pref['im_width']."' maxlength='5' /> (".USRLAN_48.")
                </td></tr>

                <tr>
                <td style='width:50%' class='forumheader3'>".USRLAN_49.":</td>
                <td style='width:50%' class='forumheader3'>
                <input class='tbox' type='text' name='im_height' size='10' value='".$pref['im_height']."' maxlength='5' /> (".USRLAN_50.")
                </td></tr>

                                <tr>
                <td style='width:50%' class='forumheader3'>".USRLAN_93."<br /><span class='smalltext'>".USRLAN_94."</span></td>
                <td style='width:50%' class='forumheader3'>
                <input class='tbox' type='text' name='del_unv' size='10' value='".$pref['del_unv']."' maxlength='5' /> ".USRLAN_95."
               </td></tr>

                <tr>
                <td colspan='2' style='text-align:center' class='forumheader'>
                <input class='button' type='submit' name='update_options' value='".USRLAN_51."' />
                </td></tr>

                </table></form></div>";
                $ns -> tablerender(USRLAN_52, $text);
        }

        function show_message($message){
                global $ns;
                $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
        }

        function show_extended(){
                global $sql, $ns, $uf_name, $uf_type, $uf_value, $uf_default, $uf_visible, $uf_hide;
                                require_once(e_HANDLER."userclass_class.php");

                $sql -> db_Select("core", " e107_value", " e107_name='user_entended'");
                $row = $sql -> db_Fetch();
                $user_entended = unserialize($row[0]);

                $text = "<div style='text-align:center'>";
      //    $text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";

                $text .="
                <table style='".ADMIN_WIDTH."' class='fborder'><tr>
                <td class='fcaption'>".USRLAN_96."</td>
                <td class='fcaption'>".USRLAN_97."</td>
                <td class='fcaption'>".USRLAN_98."</td>
                <td class='fcaption'>".USRLAN_99."</td>
                <td class='fcaption'>".USRLAN_100."</td>
                <td class='fcaption'>".USRLAN_101."</td>
                <td class='fcaption'>".USRLAN_102."</td></tr>
                \n";

                if(!$row[0]){
                        $text .= "<tr>
                        <td colspan='7' class='forumheader3' style='text-align:center'>".USRLAN_40."</td>
                        </tr>";
                }else{
                        $c=0;

                        while(list($key, $u_entended) = each($user_entended)){
                                if($u_entended){
                                // added by cameron..=============================
                                $ut = explode("|",$u_entended);
                                $u_name = ($ut[0] != "" ? str_replace("_"," ",$ut[0]) : $u_entended);
                                $u_type = $ut[1];
                                $u_value = $ut[2];
                                $u_default = $ut[3];
                                $u_visible = $ut[4];
                                $u_hide = $ut[5];
                                                                                $text .= "<tr>
                                        <td class='forumheader3' >".$u_name."&nbsp; </td>
                                        <td class='forumheader3' >".$u_type."&nbsp; </td>
                                        <td class='forumheader3' >".$u_value."&nbsp; </td>
                                        <td class='forumheader3' >".$u_default."&nbsp; </td>
                                        <td class='forumheader3' >".r_userclass_name($u_visible)."&nbsp; </td>
                                        <td class='forumheader3' >".r_userclass_name($u_hide)."&nbsp; </td>
                                        <td class='forumheader3' style='text-align:center;'>
                                        <span class='button' style='height:16px; width:50%;'>
                                        <a style='text-decoration:none' href='".e_SELF."?editext.$key'>".USRLAN_81."</a>
                                        </span>
                                        &nbsp;
                                        <form method='post' action='".e_SELF."?extended' onsubmit='return confirm(\"".USRLAN_16."\")'>
                                        <div>
                                        <input type='hidden' name='eu_action' value='delext' />
                                        <input type='hidden' name='key' value='{$key}' />
                                        <input type='submit' class='button' name='eudel' value='".USRLAN_29."' />
                                        </div>
                                        </form>
                                        </td>
                                        </tr>";
                                        $c++;
                                }
//                                        <a style='text-decoration:none' href='".e_SELF."?delext.$key'>".USRLAN_29."</a>
                        }
                }

                $text .="
                </table>
                <form method='post' action='".e_SELF."?".e_QUERY."'>
                ";
                $text .="<div><br /></div><table style='".ADMIN_WIDTH."' class='fborder'>  ";
                $text .= "<tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_41.":</td>
                <td style='width:70%' class='forumheader3' colspan='3'>
                <input class='tbox' type='text' name='user_field' size='40' value='".$uf_name."' maxlength='50' /></td>
                </tr>";

               $text .="<tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_103."</td>
                <td style='width:70%' class='forumheader3' colspan='3'>
                <select class='tbox' name='user_type'>";

     $typevalue = array("text","radio","dropdown","table");
     $typename = array(USRLAN_108,USRLAN_109,USRLAN_110,USRLAN_111);

     for ($i=0; $i<count($typevalue); $i++) {
     $selected = ($uf_type == $typevalue[$i])? " selected='selected'": "";
     $text .="<option value='".$typevalue[$i]."' $selected>".$typename[$i]."</option>";
     };

                $text .="
                </select></td></tr>";

                $text .= "<tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_98."</td>
                <td style='width:70%' class='forumheader3' colspan='3'>
                <input class='tbox' type='text' name='user_value' size='40' value='$uf_value' /><br />
                <span class='smalltext'>".USRLAN_105."</span>
                </td>
                </tr>

                                <tr>
                                <td style='width:30%' class='forumheader3'>".USRLAN_104."</td>
                <td style='width:70%' class='forumheader3' colspan='3'>
                                <input class='tbox' type='text' name='user_default' size='40' value='$uf_default' />
                                </td>
                </tr>

                                <tr>
                                <td style='width:30%' class='forumheader3'>".USRLAN_100."</td>
                <td style='width:70%' class='forumheader3' colspan='3'>
                                ".r_userclass("user_visible", $uf_visible)."<br /><span class='smalltext'>".USRLAN_106."</span>
                                </td>
                </tr>

                                <tr>
                                <td style='width:30%' class='forumheader3'>".USRLAN_101."</td>
                <td style='width:70%' class='forumheader3' colspan='3'>
                                ".r_userclass("user_hide", $uf_hide)."<br /><span class='smalltext'>".USRLAN_107."</span>
                                </td>
                </tr>";


                $text .="<tr>
                <td colspan='4' style='text-align:center' class='forumheader'>";

                if(!$uf_name){
                $text .="
                <input class='button' type='submit' name='add_field' value='".USRLAN_42."' />
                ";
                }else{
                $text .="
                <input class='button' type='submit' name='update_field' value='".USRLAN_119."' />
                ";
                }
            // ======= end added by Cam.
                $text .="</td>
                </tr>

                </table></form></div>";
                $ns -> tablerender(USRLAN_43, $text);
        }

        function show_prune(){
                global $ns, $sql;

                $unactive = $sql -> db_Select("user", "*", "user_ban=2");
                $text = "<div style='text-align:center'>".USRLAN_84." ".$unactive." ".USRLAN_85."<br /><br />
                <form method='post' action='".e_SELF."'>
                <table style='".ADMIN_WIDTH."' class='fborder'>
                <tr>
                <td class='forumheader3' style='text-align:center'>
                <input class='button' type='submit' name='prune' value='".USRLAN_54."' />
                </td>
                </tr>
                </table>
                </form>
                </div>";
                $ns -> tablerender(USRLAN_55, $text);
        }

        function add_user(){
                global $rs, $ns;
                $text = "<div style='text-align:center'>".
                $rs -> form_open("post", e_SELF, "adduserform")."
                <table style='".ADMIN_WIDTH."' class='fborder'>
                <tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_61."</td>
                <td style='width:70%' class='forumheader3'>
                ".$rs -> form_text("name", 40, "", 30)."
                </td>
                </tr>
                <tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_62."</td>
                <td style='width:70%' class='forumheader3'>
                ".$rs -> form_password("password1", 40, "", 20)."
                </td>
                </tr>
                <tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_63."</td>
                <td style='width:70%' class='forumheader3'>
                ".$rs -> form_password("password2", 40, "", 20)."
                </td>
                </tr>
                <tr>
                <td style='width:30%' class='forumheader3'>".USRLAN_64."</td>
                <td style='width:70%' class='forumheader3'>
                ".$rs -> form_text("email", 60, "", 100)."
                </td>
                </tr>";


                                if(!is_object($sql)) $sql = new db;
                                if($sql -> db_Select("userclass_classes")){
                                    $text .= "<tr style='vertical-align:top'>
                                    <td colspan='2' style='text-align:center' class='forumheader'>
                                                    ".USRLAN_120."
                                    </td>
                                    </tr>";
                                    $c=0;
                                    while($row = $sql -> db_Fetch()){
                                            $class[$c][0] = $row['userclass_id'];
                                            $class[$c][1] = $row['userclass_name'];
                                            $class[$c][2] = $row['userclass_description'];
                                            $c++;
                                    }
                                    for($a=0; $a<= (count($class)-1); $a++){
                                            $text .= "<tr><td style='width:30%' class='forumheader'>
                                            <input type='checkbox' name='userclass[]' value='".$class[$a][0]."' />".$class[$a][1]."
                                            </td><td style='width:70%' class='forumheader3'> ".$class[$a][2]."</td></tr>";
                                    }
                                }
                                $text .= "
                <tr style='vertical-align:top'>
                <td colspan='2' style='text-align:center' class='forumheader'>
                <input class='button' type='submit' name='adduser' value='".USRLAN_60."' />
                <input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' />
                </td>
                </tr>
                </table>
                </form>
                </div>
                ";

                $ns -> tablerender(USRLAN_59, $text);
        }

}
function users_adminmenu(){
        global $user;
        global $action;
        $user -> show_options($action);
}
?>