<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/administrator.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("3")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        unset($tmp);
}

if(IsSet($_POST['add_admin'])){
        if($_POST['ac'] == md5(ADMINPWCHANGE)){
                if(!$_POST['ad_name'] || !$_POST['a_password']){
                        $message = ADMSLAN_55;
                }else{
                        for ($i=0; $i<=count($_POST['perms']); $i++){
                                if($_POST['perms'][$i]){
                                        $perm .= $_POST['perms'][$i].".";
                                }
                        }

                        if(!$sql -> db_Select("user", "*", "user_name='".$_POST['ad_name']."' ")){
                                $sql -> db_Insert("user", "0, '".$_POST['ad_name']."', '', '".md5($_POST['a_password'])."', '', '".$_POST['ad_email']."',         '".$_POST['website']."', '".$_POST['icq']."', '".$_POST['aim']."', '".$_POST['msn']."', '".$_POST['location']."', '".$_POST['birthday']."', '".$_POST['signature']."', '".$_POST['image']."', '".$_POST['timezone']."', '".$_POST['hideemail']."', '".time()."', '0', '".time()."', '0', '0', '0', '0', '".$ip."', '0', '0', '', '', '', '1', '', '', '$perm', '', '' ");
                                $message = ADMSLAN_0." ".$_POST['ad_name']."<br />";
                        }else{
                                $sql -> db_Update("user", "user_admin='1', user_perms='$perm' WHERE user_name='".$_POST['ad_name']."' ");
                        }
                        $message = $_POST['ad_name']." ".ADMSLAN_1."<br />";
                }
        }
}

if(IsSet($_POST['update_admin'])){
        $sql -> db_Select("user", "*", "user_id='".$_POST['a_id']."' ");
        $row = $sql -> db_Fetch();
        $a_name = $row['user_name'];
        if($_POST['a_password'] == ""){
                $admin_password = $row['user_password'];
        }else{
                $admin_password = md5($_POST['a_password']);
        }

       for ($i=0; $i<=count($_POST['perms']); $i++){
                if($_POST['perms'][$i]){
                        $perm .= $_POST['perms'][$i].".";
                }
        }
        $sql -> db_Update("user", "user_password='$admin_password', user_perms='$perm' WHERE user_name='$a_name' ");
        unset($ad_name, $a_password, $a_perms);
        $message = ADMSLAN_56." ".$_POST['ad_name']." ".ADMSLAN_2."<br />";
}

if($action == "edit"){
        $sql -> db_Select("user", "*", "user_id=$sub_action");
        $row = $sql-> db_Fetch();
        extract($row);
        $a_id = $user_id; $ad_name = $user_name; $a_perms = $user_perms;
        if($a_perms == "0"){
                $text = "<div style='text-align:center'>$ad_name ".ADMSLAN_3."
                <br /><br />
                <a href='administrator.php'>".ADMSLAN_4."</a></div>";
                $ns -> tablerender("<div style='text-align:center'>".ADMSLAN_5."</div>", $text);
                require_once("footer.php");
                exit;
        }
}

if($action == "delete"){
        $sql -> db_Select("user", "*", "user_id=$sub_action");
        $row = $sql-> db_Fetch();
        extract($row);
        if($user_perms == "0"){
                $text = "<div style='text-align:center'>$user_name ".ADMSLAN_6."
                <br /><br />
                <a href='administrator.php'>".ADMSLAN_4."</a>";
                $ns -> tablerender("<div style='text-align:center'>".ADMSLAN_5."</div>", $text);
                require_once("footer.php");
                exit;
        }
        $sql -> db_Update("user", "user_admin=0, user_perms='' WHERE user_id=$sub_action");
        $message = ADMSLAN_61;
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$sql -> db_Select("user", "*", "user_admin='1'");


$text = "<div style='text-align:center'><div style='border : solid 1px #000; padding : 4px; width : auto; height : 100px; overflow : auto; '>
<table class='fborder' style='width:100%'>
<tr>
<td style='width:5%' class='forumheader2'>ID</td>
<td style='width:30%' class='forumheader2'>".ADMSLAN_56."</td>
<td style='width:35%' class='forumheader2'>".ADMSLAN_18."</td>
<td style='width:30%' class='forumheader2'>".ADMSLAN_57."</td>
</tr>";

while($row = $sql -> db_Fetch()){
        extract($row);
        $text .= "<tr>
<td style='width:5%' class='forumheader3'>$user_id</td>
<td style='width:30%' class='forumheader3'>$user_name</td>
<td style='width:35%' class='forumheader3'>".($user_perms == "0" ? ADMSLAN_58 : ($user_perms ? str_replace(".", "", $user_perms) : "&nbsp;"))."</td>
<td style='width:30%; text-align:center' class='forumheader3'>".
($user_perms == "0" ? "&nbsp;" :
$rs -> form_button("submit", "main_edit", ADMSLAN_15, "onclick=\"document.location='".e_SELF."?edit.$user_id'\"").
$rs -> form_button("submit", "main_delete", ADMSLAN_59, "onclick=\"confirm_($user_id, '$user_name')\""))."</td>
</tr>";
}

$text .= "</table>\n</div>\n</div>";

$ns -> tablerender(ADMSLAN_13, $text);


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' id='myform' >
<table style='width:95%' class='fborder'>
<tr>
<td style='width:30%' class='forumheader3'>".ADMSLAN_16.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='ad_name' size='60' value='$ad_name' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".ADMSLAN_17.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='a_password' size='60' value='$a_password' maxlength='100' />
</td>
</tr>

<tr>
<td style='width:30%' class='forumheader3'>".ADMSLAN_18.": <br /></td>
<td style='width:70%' class='forumheader3'>";

function checkb($arg, $perms){
        if(getperms($arg, $perms)){
                $par = "<input type='checkbox' name='perms[]' value='$arg' checked='checked' />\n";
        }else{
                $par = "<input type='checkbox' name='perms[]' value='$arg' />\n";
        }
        return $par;
}

$text .= checkb("1", $a_perms).ADMSLAN_19."<br />";
$text .= checkb("2", $a_perms).ADMSLAN_20."<br />";
$text .= checkb("3", $a_perms).ADMSLAN_21."<br />";
$text .= checkb("4", $a_perms).ADMSLAN_22."<br />";
$text .= checkb("5", $a_perms).ADMSLAN_23."<br />";
$text .= checkb("Q", $a_perms).ADMSLAN_24."<br />";
$text .= checkb("6", $a_perms).ADMSLAN_25."<br />";
$text .= checkb("7", $a_perms).ADMSLAN_26."<br />";
$text .= checkb("8", $a_perms).ADMSLAN_27."<br />";
$text .= checkb("9", $a_perms).ADMSLAN_28."<br /><br />";

$text .= checkb("D", $a_perms).ADMSLAN_29."<br />";
$text .= checkb("E", $a_perms).ADMSLAN_30."<br />";
$text .= checkb("F", $a_perms).ADMSLAN_31."<br />";
$text .= checkb("G", $a_perms).ADMSLAN_32."<br />";
$text .= checkb("S", $a_perms).ADMSLAN_33."<br />";
$text .= checkb("T", $a_perms).ADMSLAN_34."<br />";
$text .= checkb("V", $a_perms).ADMSLAN_35."<br />";

$text .= checkb("A", $a_perms).ADMSLAN_36."<br />";
$text .= checkb("B", $a_perms).ADMSLAN_37."<br />";
$text .= checkb("C", $a_perms).ADMSLAN_38."<br /><br />";

$text .= checkb("H", $a_perms).ADMSLAN_39."<br />";
$text .= checkb("I", $a_perms).ADMSLAN_40."<br />";
$text .= checkb("J", $a_perms).ADMSLAN_41."<br />";
$text .= checkb("K", $a_perms).ADMSLAN_42."<br />";
$text .= checkb("L", $a_perms).ADMSLAN_43."<br />";
$text .= checkb("R", $a_perms).ADMSLAN_44."<br />";
$text .= checkb("U", $a_perms).ADMSLAN_45."<br />";
$text .= checkb("M", $a_perms).ADMSLAN_46."<br />";
$text .= checkb("N", $a_perms).ADMSLAN_47."<br /><br />";


$text .= checkb("Z", $a_perms).ADMSLAN_62."<br /><br />";

$sql -> db_Select("plugin", "*", "plugin_installflag='1'");
while($row = $sql-> db_Fetch()){
extract($row);
$text .= checkb("P".$plugin_id, $a_perms).ADMSLAN_63." - ".$plugin_name."<br />";
}




$text .= "
<br />
<a href='".e_SELF."?checkall=1' onclick=\"setCheckboxes('myform', true); return false;\">".ADMSLAN_49."</a> -
<a href='".e_SELF."' onclick=\"setCheckboxes('myform', false); return false;\">".ADMSLAN_51."</a>

</td>
</tr>";

$text .= "<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>";

if($action == "edit"){
        $text .= "<input class='button' type='submit' name='update_admin' value='".ADMSLAN_52."' />
        <input type='hidden' name='a_id' value='$a_id' />";
}else{
        $text .= "<input class='button' type='submit' name='add_admin' value='".ADMSLAN_53."' />";
}
$text .= "</td>
</tr>
</table>
<div><input type='hidden' name='ac' value='".md5(ADMINPWCHANGE)."' /></div>
</form>
</div>";

$ns -> tablerender("<div style='text-align:center'>".ADMSLAN_54."</div>", $text);

require_once("footer.php");

function headerjs(){
$script = "<script type=\"text/javascript\">
function confirm_(user_id, user_name){
        var x=confirm(\"".ADMSLAN_60." \" + user_name + \"\");
        if(x){
                window.location='".e_SELF."?delete.' + user_id;
        }
}
</script>";
return $script;
}

?>