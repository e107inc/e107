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
|     $Source: /cvs_backup/e107_0.7/e107_admin/banlist.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-01-05 16:57:36 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("4")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;

if(e_QUERY){
        $tmp = explode("-", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        $id = $tmp[2];
        unset($tmp);
}

if(IsSet($_POST['add_ban'])){
        $aj = new textparse;
        $bd = $_POST['ban_ip'];
        $_POST['ban_reason'] = $aj -> formtpa($_POST['ban_reason'], "admin");
        $sql -> db_Insert("banlist", "'$bd', '".ADMINID."', '".$_POST['ban_reason']."' ");
        unset($ban_ip);
}

if(IsSet($_POST['update_ban'])){
        $aj = new textparse;
        $bd = $_POST['ban_ip'];
        $_POST['ban_reason'] = $aj -> formtpa($_POST['ban_reason'], "admin");
        $sql -> db_Delete("banlist", "banlist_ip='".$_POST['old_ip']."'");
		    $sql -> db_Insert("banlist", "'".$bd."', '".ADMINID."', '".$_POST['ban_reason']."' ");
        $message = BANLAN_14;
        unset($ban_ip);
}

if($action == "remove" && isset($_POST['ban_secure'])){
        $sql -> db_Delete("banlist", "banlist_ip='$sub_action'");
        $message = BANLAN_1;
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}
if($action != "edit"){
        $text = $rs -> form_open("post", e_SELF, "ban_form").$rs -> form_hidden("ban_secure","1")."<div style='text-align:center'><div style='padding : 1px; ".ADMIN_WIDTH."; height : 400px; overflow : auto; margin-left: auto; margin-right: auto;'>\n";
        if(!$ban_total = $sql -> db_Select("banlist")){
                        $text .= "<div style='text-align:center'>".BANLAN_2."</div>";
        }else{
                        $text .= "<table class='fborder' style='width:99%;'>
                        <tr>
                        <td style='width:70%' class='forumheader'>".BANLAN_10."</td>
        <td style='width:30%' class='forumheader'>".BANLAN_11."</td>
        </tr>";
                        $count =0;
                        while($row = $sql -> db_Fetch()){
                                        extract($row);
                                        $text .= "<tr><td style='width:70%' class='forumheader3'>$banlist_ip<br />".BANLAN_7.": $banlist_reason</td>
                                        <td style='width:30%; text-align:center' class='forumheader3'>".$rs -> form_button("submit", "main_edit_$count", BANLAN_12, "onclick=\"document.getElementById('ban_form').action='".e_SELF."?edit-$banlist_ip'\"").$rs -> form_button("submit", "main_delete_$count", BANLAN_4, "onclick=\"document.getElementById('ban_form').action='".e_SELF."?remove-$banlist_ip'\"")."</td>\n</tr>";
                        $count++;
                        }
                        $text .= "</table>\n";
        }
        $text .= "</div></div>".$rs -> form_close();
        $ns -> tablerender(BANLAN_3, $text);
}

if($action == "edit"){
  $sql2 -> db_Select("banlist", "*", "banlist_ip='$sub_action'");
        $row = $sql2 ->db_Fetch();
        extract($row);
}else{
        unset($banlist_ip, $banlist_reason);
        if(e_QUERY && strpos($_SERVER["HTTP_REFERER"],"userinfo")){$banlist_ip=$action;}
}
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='".ADMIN_WIDTH."' class='fborder'>

<tr>
<td style='width:30%' class='forumheader3'>".BANLAN_5.": </td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='ban_ip' size='40' value='".$banlist_ip."' maxlength='200' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".BANLAN_7.": </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='ban_reason' cols='50' rows='4'>$banlist_reason</textarea>
</td>
</tr>

<tr style='vertical-align:top'>
<td colspan='2' style='text-align:center' class='forumheader'>".

($action == "edit" ? "<input type='hidden' name='old_ip' value='$banlist_ip' /><input class='button' type='submit' name='update_ban' value='".BANLAN_13."' />" : "<input class='button' type='submit' name='add_ban' value='".BANLAN_8."' />")."

</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender(BANLAN_9, $text);

require_once("footer.php");
?>