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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/forum.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-01-18 16:11:36 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("5")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
$aj = new textparse;

if(e_QUERY){
        $qs = explode(".", e_QUERY);
        $action = $qs[0];
        $forum_id = $qs[1];
        $forum_order = $qs[2];
}

$sql2 = new db;

if(isset($_POST['prune'])){
        if(!$_POST['prune_days']){
                $message = FORLAN_1;
        }else{

                $text = "<div style='text-align:center'>
                <form method='post' action='".e_SELF."'>\n
                <table style='width:85%' class='fborder'>
                <tr>
                <td style='width:75%' class='forumheader3'>
                ".FORLAN_106."<br />
                <span class='smalltext'>".FORLAN_2."</span>
                </td>
                <td style='width:25%' class='forumheader2' style='text-align:right'>
                <input type='radio' name='prune_type' value='delete'> ".FORLAN_3."<br />
                <input type='radio' name='prune_type' value='unactivatate' checked> ".FORLAN_4."
                </td>
                </tr>
                <tr>
                <td colspan='2'  style='text-align:center' class='forumheader'>
                <input class='button' type='submit' name='doprune' value='".FORLAN_5."' />
                <input class='button' type='submit' name='cancelprune' value='".FORLAN_6."' />
                <input type='hidden' name='prune_days' value='".$_POST['prune_days']."'>
                </td>
                </tr>

                </table>
                </form>
                </div>";
                $ns -> tablerender(FORLAN_7, $text);
                require_once("footer.php");
        }
}

if(isset($_POST['doprune'])){
        if($_POST['prune_type'] == "delete"){
                $prunedate = time() - ($_POST['prune_days']*86400);
                if($sql -> db_Select("forum_t", "*", "thread_lastpost<$prunedate AND thread_parent=0 AND thread_s!=1")){
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $sql2 -> db_Delete("forum_t", "thread_parent='$thread_id' ");        // delete replies
                                $sql2 -> db_Delete("forum_t", "thread_id='$thread_id' ");                // delete thread
                        }

                        $sql -> db_Select("forum", "*", "forum_parent!=0");
                        while($row = $sql -> db_Fetch()){
                                extract($row);
                                $threads = $sql2 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forum_id AND thread_parent=0");
                                $replies = $sql2 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forum_id AND thread_parent!=0");
                                $sql2 -> db_Update("forum", "forum_threads='$threads', forum_replies='$replies' WHERE forum_id='$forum_id' ");
                        }
                        $message = FORLAN_8;
                }else{
                        $message = FORLAN_9;
                }
        }else{
                $prunedate = time() - ($_POST['prune_days']*86400);
                $sql -> db_Update("forum_t", "thread_active=0 WHERE thread_lastpost<$prunedate AND thread_parent=0 ");
                $message = FORLAN_8;
        }
}

/*
if($action == "dec"){
        $sql -> db_Update("forum", "forum_order=forum_order-1 WHERE forum_order='".($forum_order+1)."' ");
        $sql -> db_Update("forum", "forum_order=forum_order+1 WHERE forum_id='$forum_id' ");
        header("location: ".e_SELF);
        exit;
}

if($action == "inc"){
        $sql -> db_Update("forum", "forum_order=forum_order+1 WHERE forum_order='".($forum_order-1)."' ");
        $sql -> db_Update("forum", "forum_order=forum_order-1 WHERE forum_id='$forum_id' ");
        header("location: ".e_SELF);
        exit;
}
*/

if(isset($_POST['update_order'])){
        extract($_POST);
        while(list($key, $id) = each($forum_order_)){
                $tmp = explode(".", $id);
                $sql -> db_Update("forum", "forum_order=".$tmp[1]." WHERE forum_id=".$tmp[0]);
                echo "forum_order=".$tmp[1]." WHERE forum_id=".$tmp[0]."<br />";
        }
        $message = FORLAN_73;
}



$sql -> db_Select("forum", "forum_id, forum_order", "forum_parent=0 ORDER BY forum_order ASC");
$c=1;
while($row = $sql -> db_Fetch()){
        extract($row);
        $sql2 -> db_Update("forum", "forum_order='$c' WHERE forum_id='$forum_id' ");
        $c++;
}
$sql -> db_Select("forum", "forum_id, forum_order", "forum_parent!=0 ORDER BY forum_order ASC");
while($row = $sql -> db_Fetch()){
        extract($row);
        $sql2 -> db_Update("forum", "forum_order='$c' WHERE forum_id='$forum_id' ");
        $c++;
}

if(isset($_POST['updateoptions'])){
        $pref['email_notify'] = $_POST['email_notify'];
        $pref['forum_poll'] = $_POST['forum_poll'];
        $pref['forum_popular'] = $_POST['forum_popular'];
        $pref['forum_track'] = $_POST['forum_track'];
        $pref['forum_eprefix'] = $_POST['forum_eprefix'];
        $pref['forum_enclose'] = $_POST['forum_enclose'];
        $pref['forum_title'] = $_POST['forum_title'];
        $pref['forum_postspage'] = $_POST['forum_postspage'];
        $pref['forum_levels'] = $_POST['forum_levels'];
        $pref['image_post'] = $_POST['image_post'];
        $pref['html_post'] = $_POST['html_post'];
        $pref['forum_attach'] = $_POST['forum_attach'];
        save_prefs();
        $message = FORLAN_10;
}

If(isset($_POST['submit'])){

        $c = 0;
        while($_POST['mod'][$c]){
                $mods .= $_POST['mod'][$c].", ";
                $c++;
        }
        $mods = ereg_replace(", $", ".", $mods);

        $sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
        $row = $sql -> db_Fetch();
        $forum_parent = $row['forum_id'];
        $_POST['forum_name'] = $aj -> formtpa($_POST['forum_name'], "admin");
        $_POST['forum_description'] = $aj -> formtpa($_POST['forum_description'], "admin");

        $sql -> db_Insert("forum", "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$forum_parent."', '".time()."', '".$mods."', 0, 0, 0, '".$_POST['forum_class']."', 0 ");
        unset($forum_name, $forum_description, $forum_parent);
        $message = FORLAN_11;
}

If(isset($_POST['update'])){

        $c = 0;
        while($_POST['mod'][$c]){
                $mods .= $_POST['mod'][$c].", ";
                $c++;
        }
        $mods = ereg_replace(", $", ".", $mods);
        $sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
        $row = $sql -> db_Fetch();
        $_POST['forum_name'] = $aj -> formtpa($_POST['forum_name'], "admin");
        $_POST['forum_description'] = $aj -> formtpa($_POST['forum_description'], "admin");
        $forum_parent = $row['forum_id'];
        $sql -> db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$forum_parent."', forum_moderators='".$mods."', forum_class='".$_POST['forum_class']."' WHERE forum_id='".$_POST['forum_id']."' ");
        unset($forum_name, $forum_description, $forum_parent, $forum_active);
        $message = FORLAN_12;
}

If(isset($_POST['psubmit'])){
        $_POST['parent'] = $aj -> formtpa($_POST['parent'], "admin");
        $sql -> db_Insert("forum", "0, '".$_POST['parent']."', '', '', '".time()."', '0', '0', '0', '', '".$_POST['parent_class']."', 0 ");
        unset($parent);
        $message = FORLAN_13;
}

If(isset($_POST['pupdate'])){
        $_POST['parent'] = $aj -> formtpa($_POST['parent'], "admin");
        $sql -> db_Update("forum", "forum_name='".$_POST['parent']."', forum_class='".$_POST['parent_class']."' WHERE forum_id='".$_POST['existing']."' ");
        unset($parent);
        $message = FORLAN_14;
}

If(isset($_POST['pedit'])){
        $sql -> db_Select("forum", "*", "forum_id='".$_POST['existing']."' ");
        list($forum_id, $parent, $forum_description, $forum_parent, $forum_datestamp, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost, $parent_class) = $sql-> db_Fetch();
        $parent = stripslashes($parent);
}

If(isset($_POST['edit'])){
        $sql -> db_Select("forum", "*", "forum_id='".$_POST['existing']."' ");
        list($forum_id, $forum_name, $forum_description, $forum_parent, $forum_datestamp, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost, $forum_class) = $sql-> db_Fetch();
        $parent = stripslashes($parent);
}

If(isset($_POST['delete'])){
        if($_POST['confirm']){
                $sql -> db_Select("forum", "forum_id, forum_parent", "forum_id='".$_POST['existing']."' ");
                $row = $sql -> db_Fetch();
                extract($row);
                $tt = ($forum_parent ? "" : "parent");
                $sql -> db_Delete("forum", "forum_id='".$_POST['existing']."' ");
                $message = FORLAN_107.$tt.FORLAN_108;
        }else{
                $message = FORLAN_15;
        }
}


if(isset($message)){
        $ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$ns -> tablerender("<div style='text-align:center'>".FORLAN_16."</div>", $text);

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

$forum_parent_total = $sql -> db_Select("forum", "*", "forum_parent='0' ");
if($forum_parent_total == 0){
        $text .= "<span class='defaulttext'>".FORLAN_17."</span>";
}else{
        $text .= "<span class='defaulttext'>".FORLAN_18.": </span>
<select name='existing' class='tbox'>";
        $c = 0;
        while(list($forum_id_, $forum_parent_) = $sql-> db_Fetch()){
                $parents[$c] = $forum_parent_;
                $parents_id[$c] = $forum_id_;
                $text .= "<option value='$forum_id_'>".$parents[$c]."</option>";
                $c++;
        }
        $text .= "</select>
<input class='button' type='submit' name='pedit' value='".FORLAN_19."' />
<input class='button' type='submit' name='delete' value='".FORLAN_20."' />
<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> ".FORLAN_21."</span>
";
}
$text .= "
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><u>".FORLAN_22."</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='parent' size='60' value='$parent' maxlength='250' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
<td style='width:80%' class='forumheader3'>".r_userclass("parent_class",$parent_class);

$text .= "<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>";

if(isset($_POST['pedit'])){
        $text .= "<input class='button' type='submit' name='pupdate' value='".FORLAN_25."' />
<input type='hidden' name='existing' value='".$_POST['existing']."'>";
}else{
        $text .= "<input class='button' type='submit' name='psubmit' value='".FORLAN_26."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("Parents", $text);

if($forum_parent_total == 0){
        $text = "<div style='text-align:center'>".FORLAN_27."</div>";
        $ns -> tablerender(FORLAN_28, $text);
        require_once("footer.php");
        exit;
}


$forum_total = $sql -> db_Select("forum", "*", "forum_parent!='0' ");

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

if($forum_total == "0"){
        $text .= "<span class='defaulttext'>".FORLAN_29."</span>";
}else{
        $text .= "<span class='defaulttext'>".FORLAN_30.": </span>
        <select name='existing' class='tbox'>";
        while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()){
                $text .= "<option value='$forum_id_'>".$forum_name_."</option>";
        }
        $text .= "</select>
        <input class='button' type='submit' name='edit' value='".FORLAN_19."' />
        <input class='button' type='submit' name='delete' value='".FORLAN_20."' />
        <input type='checkbox' name='confirm' value='1'><span class='smalltext'> ".FORLAN_21."</span>
        ";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><u>".FORLAN_22."</u>:</td>
<td style='width:80%' class='forumheader3'>
<select name='parentforum' class='tbox'>";
$c = 0;
        while($parents[$c]){
                if($parents_id[$c] == $forum_parent){
                        $text .= "<option selected>".$parents[$c]."</option>";
                }else{
                        $text .= "<option>".$parents[$c]."</option>";
                }
                $c++;
        }
$text .= "</select>
</td>
</tr>



<tr>
<td style='width:20%' class='forumheader3'><u>".FORLAN_31."</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='100' />
</td>
</tr>
<tr>

<td style='width:20%' class='forumheader3'><u>".FORLAN_32."</u>: </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='forum_description' cols='50' rows='5'>$forum_description</textarea>
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
<td style='width:80%' class='forumheader3'>".r_userclass("forum_class", $forum_class, "on")."
</td></tr><tr>

<td style='width:20%' class='forumheader3'>".FORLAN_33.":<br /><span class='smalltext'>(".FORLAN_34.")</span></td>
<td style='width:80%' class='forumheader3'>";
$admin_no = $sql -> db_Select("user", "*", "user_admin='1' AND user_perms REGEXP('A.') OR user_perms='0' ");
while($row = $sql-> db_Fetch()){
        extract($row);
        $text .= "<input type='checkbox' name='mod[]' value='".$user_name ."'";
        if(preg_match('/'.preg_quote($user_name).'/', $forum_moderators)){
                $text .= " checked";
        }
        $text .= "> ".$user_name ."<br />";
}

$text .= "</td>
</tr>
<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>";


If(isset($_POST['edit'])){
        $text .= "<input class='button' type='submit' name='update' value='".FORLAN_35."' />
        <input type='hidden' name='forum_id' value='".$forum_id."'>";
}else{
        $text .= "<input class='button' type='submit' name='submit' value='".FORLAN_36."' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("Forums", $text);


$text = "<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<form method='post' action='".e_SELF."'>
<tr>
<td colspan='2' style='width:70%; text-align:center' class='fcaption'>".FORLAN_28."</td>
<td style='width:30%; text-align:center' class='fcaption'>".FORLAN_37."</td>
</tr>";

if(!$parent_amount = $sql -> db_Select("forum", "*", "forum_parent='0' ORDER BY forum_order ASC")){
        $text .= "<tr><td class='forumheader3' style='text-align:center' colspan='3'>".FORLAN_29."</td></tr>";
}else{
        $sql2 = new db; $sql3 = new db;
        while($row = $sql-> db_Fetch()){
                extract($row);
                if($forum_class==255){
                        $text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_38.")</td>";
                }else if($forum_class==254){
                        $text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_39.")</td>";
                }else if($forum_class){
                        $text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_40.")</td>";
                }else{
                        $text .= "<tr><td colspan='2' class='forumheader'>".$forum_name."</td>";
                }

                $text .= "<td class='forumheader' style='text-align:center'>\n<select name='activate' class='tbox'>\n";

                for($a=1; $a<= $parent_amount; $a++){
                        $text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
                }

                $text .= "</select></td></tr>";





//                $text .= "<td class='forumheader' style='text-align:center'>\n<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>\n<option value='forum.php' selected></option>\n<option value='forum.php?inc.".$forum_id.".".$forum_order."'>move up!!</option>\n<option value='forum.php?dec.".$forum_id.".".$forum_order."'>move down</option>\n</select>\n</td></tr>";



                $forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ORDER BY forum_order ASC");
                if(!$forums){
                        $text .= "<td colspan='4' style='text-align:center' class='forumheader3'>".FORLAN_29."</td>";
                }else{
                        while($row = $sql2-> db_Fetch()){
                                extract($row);

                                $text .= "<tr><td style='width:5%; text-align:center' class='forumheader2'><img src='".e_IMAGE."forum/new.png' alt='' /></td>\n<td style='width:55%' class='forumheader2'><a href='".e_BASE."forum_viewforum.php?".$forum_id."'>".$forum_name."</a><br /><span class='smallblacktext'>".$forum_description."</span></td>
                                <td colspan='2' class='forumheader3' style='text-align:center'>\n<select name='forum_order_[]' class='tbox'>\n";
                                for($a=1; $a<= $forums; $a++){
                                        $text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
                                }

                                $text .= "</select>\n</td>\n</tr>";


                                /*
                                $text .= "<tr><td style='width:5%; text-align:center' class='forumheader2'><img src='".e_IMAGE."forum/new.png' alt='' /></td>\n<td style='width:55%' class='forumheader2'><a href='".e_BASE."forum_viewforum.php?".$forum_id."'>".$forum_name."</a><br /><span class='smallblacktext'>".$forum_description."</span></td>
                                <td colspan='2' class='forumheader3' style='text-align:center'>\n<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>\n<option value='forum.php' selected></option>\n<option value='forum.php?inc.".$forum_id.".".$forum_order."'>".FORLAN_41."</option>\n<option value='forum.php?dec.".$forum_id.".".$forum_order."'>".FORLAN_42."</option>\n</select>\n</td>\n</tr>";
                                */
                        }
                }
        }
}

$text .= "<tr>
<td colspan='4' style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='update_order' value='".FORLAN_72."' />
</td>
</tr>

</table></form></div>";
$ns -> tablerender(FORLAN_43, $text);


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_44."<br />
<span class='smalltext'>".FORLAN_45."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>".
($pref['forum_enclose'] ? "<input type='checkbox' name='forum_enclose' value='1' checked>" : "<input type='checkbox' name='forum_enclose' value='1'>")."
</td>
</tr>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_65."<br />
<span class='smalltext'>".FORLAN_46."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>
<input class='tbox' type='text' name='forum_title' size='15' value='".$pref['forum_title']."' maxlength='20' />
</td>
</tr>


<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_47."<br />
<span class='smalltext'>".FORLAN_48."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>".
($pref['email_notify'] ? "<input type='checkbox' name='email_notify' value='1' checked>" : "<input type='checkbox' name='email_notify' value='1'>")."
</td>
</tr>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_68."<br />
<span class='smalltext'>".FORLAN_69."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>".
($pref['html_post'] ? "<input type='checkbox' name='html_post' value='1' checked>" : "<input type='checkbox' name='html_post' value='1'>")."
</td>
</tr>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_49."<br />
<span class='smalltext'>".FORLAN_50."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>".
($pref['forum_poll'] ? "<input type='checkbox' name='forum_poll' value='1' checked>" : "<input type='checkbox' name='forum_poll' value='1'>")."
</tr>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_70."<br />
<span class='smalltext'>".FORLAN_71."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>".
($pref['forum_attach'] ? "<input type='checkbox' name='forum_attach' value='1' checked>" : "<input type='checkbox' name='forum_attach' value='1'>")."
</td>
</tr>




<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_51."<br />
<span class='smalltext'>".FORLAN_52."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>".
($pref['forum_track'] ? "<input type='checkbox' name='forum_track' value='1' checked>" : "<input type='checkbox' name='forum_track' value='1'>")."
</tr>



<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_53."<br />
<span class='smalltext'>".FORLAN_54."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>
<input class='tbox' type='text' name='forum_eprefix' size='15' value='".$pref['forum_eprefix']."' maxlength='20' />
</tr>



<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_55."<br />
<span class='smalltext'>".FORLAN_56."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>
<input class='tbox' type='text' name='forum_popular' size='3' value='".$pref['forum_popular']."' maxlength='3' />
</tr>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_57."<br />
<span class='smalltext'>".FORLAN_58."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>
<input class='tbox' type='text' name='forum_postspage' size='3' value='".$pref['forum_postspage']."' maxlength='3' />
</tr>

<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_63."<br />
<span class='smalltext'>".FORLAN_64."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>
<textarea class='tbox' name='forum_levels' cols='15' rows='5'>".$pref['forum_levels']."</textarea>
</td>
</tr>


<tr>
<td style='width:75%' class='forumheader3'>
".FORLAN_59."<br />
<span class='smalltext'>".FORLAN_60."</span>
</td>
<td style='width:25%' class='forumheader2' style='text-align:center'>
<span class='smalltext'>".FORLAN_109."</span> <input class='tbox' type='text' name='prune_days' size='3' value='' maxlength='3' />
 <input class='button' type='submit' name='prune' value='".FORLAN_110."' />
</tr>


<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updateoptions' value='".FORLAN_61."' />
</td>
</tr>

</table>
</form>
</div>";
$ns -> tablerender(FORLAN_62, $text);

require_once("footer.php");
?>