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
|     $Source: /cvs_backup/e107_0.7/e107_admin/poll.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-05 16:57:37 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("U")){ header("location:".e_BASE."index.php"); exit;}

require_once(e_HANDLER."textparse/basic.php");
$etp = new e107_basicparse;

require_once("auth.php");
require_once(e_HANDLER."poll_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$poll = new poll; $aj = new textparse;

if(e_QUERY){
        $tmp = explode(".", e_QUERY);
        $action = $tmp[0];
        $sub_action = $tmp[1];
        unset($tmp);
}

if(IsSet($_POST['addoption']) && $_POST['option_count'] < 10){
        $_POST['option_count']++;
        $_POST['poll_title'] = $aj -> formtpa($_POST['poll_title'], "admin");
        $c=0;
        while(list($key, $option) = each($_POST['poll_option'])){
                $_POST['poll_option'][$c] = $aj -> formtpa($option, "admin");
                $c++;
        }
}

if(IsSet($_POST['reset'])){
        unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
}

if($action == "delete" && $_POST['del_poll_confirm']==1){
        $message = $poll -> delete_poll($sub_action);
        unset($poll_id, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate']);
}

if(IsSet($_POST['submit'])){
        $message = $poll -> submit_poll($sub_action, $_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], $_POST['poll_comment']);
        unset($_POST['poll_title'], $_POST['poll_option'], $_POST['activate'], $_POST['poll_comment']);
}

if($action == "edit" && !$_POST['preview']  && !$_POST['addoption'] && !$_POST['submit']){

        if($sql -> db_Select("poll", "*", "poll_id=$sub_action")){
                $row = $sql-> db_Fetch(); extract($row);
                for($a=1; $a<=10; $a++){
                        $var = "poll_option_".$a;
                        if($$var){
                                $_POST['poll_option'][($a-1)] = $$var;
                        }
                }
                $_POST['activate'] = $poll_active;
                $_POST['option_count'] = count($_POST['poll_option']);
                $_POST['poll_title'] = $poll_title;
                                $_POST['poll_comment'] = $poll_comment;
        }
}

if(IsSet($_POST['preview'])){

        $_POST['poll_title'] = $aj -> formtpa($_POST['poll_title'], "admin");
        $c=0;
        while(list($key, $option) = each($_POST['poll_option'])){
                $_POST['poll_option'][$c] = $aj -> formtpa($option, "admin");
                $c++;
        }


        $text = $poll -> render_poll($_POST['existing'], $_POST['poll_title'], $_POST['poll_option'], array($votes), "preview");
        $ns -> tablerender(POLLAN_23, $text);
        $count=0;
        while($_POST['poll_option'][$count]){
                $_POST['poll_option'][$count] = stripslashes($_POST['poll_option'][$count]);
                $count++;
        }
        $_POST['poll_title'] = stripslashes($_POST['poll_title']);
}

if(IsSet($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'><div style='padding : 1px; ".ADMIN_WIDTH."; height : 200px; overflow : auto; margin-left: auto; margin-right: auto;'>
<form action=\"".e_SELF."\" method=\"post\" id=\"del_poll\" >
<input type=\"hidden\" name=\"del_poll_confirm\" id=\"del_poll_confirm\" value=\"1\" />";
if($poll_total = $sql -> db_Select("poll")){
        $text .= "<table class='fborder' style='width:99%'>
        <tr>
        <td style='width:5%' class='forumheader2'>ID</td>
        <td style='width:75%' class='forumheader2'>".POLLAN_7."</td>
        <td style='width:20%' class='forumheader2'>".POLLAN_20."</td>
        </tr>";
        while($row = $sql -> db_Fetch()){
                extract($row);
                $text .= "<tr>
                <td style='width:5%' class='forumheader3'>$poll_id</td>
                <td style='width:75%' class='forumheader3'>$poll_title</td>
                <td style='width:20%; text-align:center' class='forumheader3'>".
                $rs -> form_button("button", "main_edit_{$poll_id}", POLLAN_4, "onclick=\"document.location='".e_SELF."?edit.$poll_id'\"").
                $rs -> form_button("submit", "main_delete_{$poll_id}", POLLAN_5, "onclick=\"confirm_($poll_id)\"")."
                </td>
                </tr>";
        }
        $text .= "</table>";
}else{
        $text .= "<div style='text-align:center'>".POLLAN_22."</div>";
}
$text .= "</form></div></div>";
$ns -> tablerender(POLLAN_3, $text);

$poll_total = $sql -> db_Select("poll");

$act_add = (e_QUERY && !strpos(e_QUERY,"elete")) ? "?".e_QUERY : "";
$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF.$act_add."'>
<table style='".ADMIN_WIDTH."' class='fborder'>
<tr>
<td style='width:30%' class='forumheader3'><div class='normaltext'>".POLLAN_7.":</div></td>
<td style='width:70%'class='forumheader3'>
<input class='tbox' type='text' name='poll_title' size='70' value='".$_POST['poll_title']."' maxlength='200' />";

$option_count = ($_POST['option_count'] ? $_POST['option_count'] : 1);
$text .= "<input type='hidden' name='option_count' value='$option_count' /></td></tr>";

for($count=1; $count<=$option_count; $count++){
        $text .= "<tr>
<td style='width:30%' class='forumheader3'>".POLLAN_8." ".$count.":</td>
<td style='width:70%' class='forumheader3'>
<input class='tbox' type='text' name='poll_option[]' size='40' value=\"".$_POST['poll_option'][($count-1)]."\" maxlength='200' />";
if($option_count == $count){
        $text .= " <input class='button' type='submit' name='addoption' value='".POLLAN_9."' /> ";
}
$text .= "</td></tr>";
}

$text .= "<tr>
<td style='width:30%' class='forumheader3'>".POLLAN_10."?:</td>
<td class='forumheader3'>";
$text .= (!$_POST['activate'] ? "<input name='activate' type='radio' value='0' checked='checked' />".POLLAN_11."<br />" : "<input name='activate' type='radio' value='0' />".POLLAN_11."<br />");
$text .= ($_POST['activate'] == 1 ? "<input name='activate' type='radio' value='1' checked='checked' />".POLLAN_12."<br />" : "<input name='activate' type='radio' value='1' />".POLLAN_12."<br />");
$text .= ($_POST['activate'] == 2 ? "<input name='activate' type='radio' value='2' checked='checked' />".POLLAN_13."<br />" : "<input name='activate' type='radio' value='2' />".POLLAN_13."<br />");

$text .= "</td>
</tr>
<tr>
<td class='forumheader3'>".POLLAN_24.": </td><td class='forumheader3'>".

 ($_POST['poll_comment'] ? "<input name='poll_comment' type='radio' value='1' checked='checked' />".POLLAN_25."&nbsp;&nbsp;<input name='poll_comment' type='radio' value='0' />".POLLAN_26 : "<input name='poll_comment' type='radio' value='1' />".POLLAN_25."&nbsp;&nbsp;<input name='poll_comment' type='radio' value='0' checked='checked' />".POLLAN_26)."
        </td>
        </tr>
<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>";

if(IsSet($_POST['preview'])){
        $text .= "<input class='button' type='submit' name='preview' value='".POLLAN_14."' /> ";
        if($action == "edit"){
                $text .= "<input class='button' type='submit' name='submit' value='".POLLAN_15."' /> ";
        }else{
                $text .= "<input class='button' type='submit' name='submit' value='".POLLAN_16."' /> ";
        }
}else{
        $text .= "<input class='button' type='submit' name='preview' value='".POLLAN_17."' /> ";
}
if(IsSet($poll_id)){
        $text .= "<input class='button' type='submit' name='reset' value='".POLLAN_18."' /> ";
}

$text .= "</td></tr></table>
</form>
</div>";

$ns -> tablerender(POLLAN_19, $text);
require_once("footer.php");
function headerjs(){
global $etp;
$headerjs = "<script type=\"text/javascript\">
function confirm_(poll_id){
        var x=confirm(\"".$etp->unentity(POLLAN_21)." [ID: \" + poll_id + \"]\");
        if(x){
                document.getElementById('del_poll').action='".e_SELF."?delete.' + poll_id;
                document.getElementById('del_poll').submit();
        }
}
</script>";
return $headerjs;
}
?>