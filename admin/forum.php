<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/forum.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the	
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("5")){ header("location:".e_HTTP."index.php"); exit; }
require_once("auth.php");


if(e_QUERY){
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$forum_id = $qs[1];
	$forum_order = $qs[2];
}

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

$sql2 = new db;
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

if(IsSet($_POST['updateoptions'])){
	$pref['email_notify'][1] = $_POST['email_notify'];
	$pref['forum_poll'][1] = $_POST['forum_poll'];
	$pref['forum_popular'][1] = $_POST['forum_popular'];
	$pref['forum_track'][1] = $_POST['forum_track'];
	$pref['forum_eprefix'][1] = $_POST['forum_eprefix'];




	save_prefs();
	$message = "Options Saved";
}

If(IsSet($_POST['submit'])){
	if($_POST['forum_all']){
		$forum_class_s = "";
	}else{
		$count = 0; unset($forum_class_s);
		while($_POST['forum_class'][$count]){
			$forum_class_s .= $_POST['forum_class'][$count]."|";
			$count++;
		}
		if(substr($forum_class_s, -1) == "|"){
			$forum_class_s = substr($forum_class_s, 0, -1);
		}
	}
	$forum_active = ($_POST['forum_active'] ? 0 : 1);
	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);

	$sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
	$row = $sql -> db_Fetch();
	$forum_parent = $row['forum_id']; 

	$sql -> db_Insert("forum", "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$forum_parent."', '".time()."', '$forum_active', '".$mods."', 0, 0, 0, '$forum_class_s', 0 ");
	unset($forum_name, $forum_description, $forum_parent);
	$message = "Forum added to database.";
}

If(IsSet($_POST['update'])){
	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);

	if($_POST['forum_all']){
		$forum_class_s = "";
	}else{
		$count = 0; unset($forum_class_s);
		while($_POST['forum_class'][$count]){
			$forum_class_s .= $_POST['forum_class'][$count]."|";
			$count++;
		}

		if(substr($forum_class_s, -1) == "|"){
			$forum_class_s = substr($forum_class_s, 0, -1);
		}
	}

	$forum_active = ($_POST['forum_active'] ? 0 : 1);
	$sql -> db_Select("forum", "*", "forum_name='".$_POST['parentforum']."' ");
	$row = $sql -> db_Fetch();
	$forum_parent = $row['forum_id'];
	$parent = addslashes($parent);
	$sql -> db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$forum_parent."', forum_active='$forum_active', forum_moderators='".$mods."', forum_class='$forum_class_s' WHERE forum_id='".$_POST['forum_id']."' ");
	unset($forum_name, $forum_description, $forum_parent, $forum_active);
	$message = "Forum parent updated in database.";
}

If(IsSet($_POST['psubmit'])){
	$parent_active = ($_POST['parent_active'] ? 0 : 1);
	if($_POST['parent_all']){
		$parent_class_s = "";
	}else{
		$count = 0; unset($parent_class_s);
		while($_POST['parent_class'][$count]){
			$parent_class_s .= $_POST['parent_class'][$count]."|";
			$count++;
		}

		if(substr($parent_class_s, -1) == "|"){
			$parent_class_s = substr($parent_class_s, 0, -1);
		}
	}
	$sql -> db_Insert("forum", "0, '".$_POST['parent']."', '', '', '".time()."', '$parent_active', '0', '0', '0', '', '$parent_class_s', 0 ");
	unset($parent);
	$message = "Parent added to database.";
}

If(IsSet($_POST['pedit'])){
	$sql -> db_Select("forum", "*", "forum_id='".$_POST['existing']."' ");
	list($forum_id, $parent, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost, $forum_class) = $sql-> db_Fetch();
	$parent = stripslashes($parent);
}

If(IsSet($_POST['edit'])){
	$sql -> db_Select("forum", "*", "forum_id='".$_POST['existing']."' ");
	list($forum_id, $forum_name, $forum_description, $forum_parent, $forum_datestamp, $forum_active, $forum_moderators, $forum_threads, $forum_replies, $forum_lastpost, $forum_class) = $sql-> db_Fetch();
	$parent = stripslashes($parent);
}

If(IsSet($_POST['pupdate'])){
	$parent_active = ($_POST['parent_active'] ? 0 : 1);
	if($_POST['parent_all']){
		$parent_class_s = "";
	}else{
		$count = 0; unset($parent_class_s);
		while($_POST['parent_class'][$count]){
			$parent_class_s .= $_POST['parent_class'][$count]."|";
			$count++;
		}
		if(substr($parent_class_s, -1) == "|"){
			$parent_class_s = substr($parent_class_s, 0, -1);
		}
	}
	$parent = addslashes($parent);
	$sql -> db_Update("forum", "forum_name='".$_POST['parent']."', forum_active='$parent_active', forum_class='$parent_class_s' WHERE forum_id='".$_POST['existing']."' ");
	unset($parent);
	$message = "Forum parent updated in database.";
}

If(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$sql -> db_Select("forum", "forum_id, forum_parent", "forum_id='".$_POST['existing']."' ");
		$row = $sql -> db_Fetch();
		extract($row);
		$tt = ($forum_parent ? "" : "parent"); 
		$sql -> db_Delete("forum", "forum_id='".$_POST['existing']."' ");
		$message = "Forum ".$tt." deleted.";
	}else{
		$message = "Please tick the confirm box to delete the forum";
	}
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$ns -> tablerender("<div style='text-align:center'>Forums</div>", $text);

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>
<tr>
<td colspan='2' class='forumheader' style='text-align:center'>";

$forum_parent_total = $sql -> db_Select("forum", "*", "forum_parent='0' ");
if($forum_parent_total == 0){
	$text .= "<span class='defaulttext'>No parents yet</span>";
}else{
	$text .= "<span class='defaulttext'>Existing Parents: </span>
<select name='existing' class='tbox'>";
	$c = 0;
	while(list($forum_id_, $forum_parent_) = $sql-> db_Fetch()){
		$parents[$c] = $forum_parent_;
		$parents_id[$c] = $forum_id_;
		$text .= "<option value='$forum_id_'>".$parents[$c]."</option>";
		$c++;
	}
	$text .= "</select>
<input class='button' type='submit' name='pedit' value='Edit' /> 
<input class='button' type='submit' name='delete' value='Delete' />
<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> tick to confirm</span>
";
}
$text .= "
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><u>Parent</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='parent' size='60' value='$parent' maxlength='250' />
</td>
</tr>

<tr> 
<td style='width:20%' class='forumheader3'>Accessable to?:<br /><span class='smalltext'>(tick to make accessable to users in the ticked class)</span></td>
<td style='width:80%' class='forumheader3'>";

if($forum_active == 0 && $_POST['pedit']){
	$text .= "<input type='checkbox' name='parent_active' value='1' checked>No-one (inactive)<br />";
}else{
	$text .= "<input type='checkbox' name='parent_active' value='1'>No-one (inactive)<br />";
}

if(!$forum_class && $_POST['pedit']){
	$text .= "<input type='checkbox' name='parent_all' value='1' checked>Everyone (public)<br /><span class='smalltext'>(ticking this box will override the classes below)</span><br />";
}else{
	$text .= "<input type='checkbox' name='parent_all' value='1'>Everyone (public) <span class='smalltext'>(ticking this box will override the classes below)</span><br />";
}
if($sql -> db_Select("userclass_classes")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if($forum_class && eregi($forum_class, $userclass_id)){
			$text .= "<input type='checkbox' name='parent_class[]' value='$userclass_id' checked>".$userclass_name ."<br />";
		}else{
			$text .= "<input type='checkbox' name='parent_class[]' value='$userclass_id'>".$userclass_name ."<br />";
		}
	}
}

$text .= "<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>";

if(IsSet($_POST['pedit'])){
	$text .= "<input class='button' type='submit' name='pupdate' value='Update Parent' />
<input type='hidden' name='existing' value='".$_POST['existing']."'>";
}else{
	$text .= "<input class='button' type='submit' name='psubmit' value='Create Parent' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("Parents", $text);

if($forum_parent_total == 0){
	$text = "<div style='text-align:center'>You need to define at least one forum parent before creating a forum.</div>";
	$ns -> tablerender("Forums", $text);
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
	$text .= "<span class='defaulttext'>No forums yet.</span>";
}else{
	$text .= "<span class='defaulttext'>Existing Forums: </span>
	<select name='existing' class='tbox'>";
	while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()){
		$text .= "<option value='$forum_id_'>".$forum_name_."</option>";
	}
	$text .= "</select> 
	<input class='button' type='submit' name='edit' value='Edit' /> 
	<input class='button' type='submit' name='delete' value='Delete' />
	<input type=\"checkbox\" name=\"confirm\" value=\"1\"><span class=\"smalltext\"> tick to confirm</span>
	";
}

$text .= "
</td>
</tr>
<tr>
<td style='width:20%' class='forumheader3'><u>Parent</u>:</td>
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
<td style='width:20%' class='forumheader3'><u>Name</u>:</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='100' />
</td>
</tr>
<tr>

<td style='width:20%' class='forumheader3'><u>Description</u>: </td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='forum_description' cols='50' rows='5'>$forum_description</textarea>
</td>
</tr>

<tr> 
<td style='width:20%' class='forumheader3'>Accessable to?:<br /><span class='smalltext'>(tick to make accessable to users in the ticked class)</span></td>
<td style='width:80%' class='forumheader3'>";

if($forum_active == 0 && $_POST['edit']){
	$text .= "<input type='checkbox' name='forum_active' value='0' checked>No-one (inactive)<br />";
}else{
	$text .= "<input type='checkbox' name='forum_active' value='0'>No-one (inactive)<br />";
}

if(!$forum_class && $_POST['edit']){
	$text .= "<input type='checkbox' name='forum_all' value='1' checked>Everyone (public) <span class='smalltext'>(ticking this box will override the classes below)</span><br />";
}else{
	$text .= "<input type='checkbox' name='forum_all' value='1'>Everyone (public) <span class='smalltext'>(ticking this box will override the classes below)</span><br />";
}

if($sql -> db_Select("userclass_classes")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		if($forum_class && eregi($forum_class, $userclass_id)){
			$text .= "<input type='checkbox' name='forum_class[]' value='$userclass_id' checked>".$userclass_name ."<br />";
		}else{
			$text .= "<input type='checkbox' name='forum_class[]' value='$userclass_id'>".$userclass_name ."<br />";
		}
	}
}
	

$text .= "
</td></tr><tr>

<td style='width:20%' class='forumheader3'>Moderators:<br /><span class='smalltext'>(tick to make active on this forum)</span></td>
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


If(IsSet($_POST['edit'])){
	$text .= "<input class='button' type='submit' name='update' value='Update Forum' />
	<input type='hidden' name='forum_id' value='".$forum_id."'>";
}else{
	$text .= "<input class='button' type='submit' name='submit' value='Create Forum' />";
}

$text .= "</td>
</tr>
</table>
</form>
</div>";
$ns -> tablerender("Forums", $text);


$text = "<div style='text-align:center'>
<table style='width:95%' class='fborder'>
<tr>
<td colspan='2' style='width:70%; text-align:center' class='fcaption'>".LAN_46."</td>
<td style='width:30%; text-align:center' class='fcaption'>Order</td>
</tr>";

if(!$sql -> db_Select("forum", "*", "forum_parent='0' ORDER BY forum_order ASC")){
	$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='3'>No forums yet</td></tr>";
}else{
	$sql2 = new db; $sql3 = new db;
	while($row = $sql-> db_Fetch()){
		extract($row);
		if(!$forum_active){
			$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (Closed)</td>";
		}else{
			if($forum_class){
				$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (Restricted)</td>";
			}else{
				$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name."</td>";
			}
		}
		$text .= "<td class='forumheader' style='text-align:center'>\n<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>\n<option value='forum.php' selected></option>\n<option value='forum.php?inc.".$forum_id.".".$forum_order."'>move up</option>\n<option value='forum.php?dec.".$forum_id.".".$forum_order."'>move down</option>\n</select>\n</td></tr>";
		$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ORDER BY forum_order ASC");
		if($forums == 0){
			$text .= "<td colspan='4' style='text-align:center' class='forumheader3'>".LAN_52."</td>";
		}else{
			while($row = $sql2-> db_Fetch()){
				extract($row);
				$text .= "<tr><td style='width:5%; text-align:center' class='forumheader2'><img src='".e_BASE."themes/shared/forum/new.png' alt='' /></td>\n<td style='width:55%' class='forumheader2'><a href='".e_BASE."forum_viewforum.php?".$forum_id."'>".$forum_name."</a><br /><span class='smallblacktext'>".$forum_description."</span></td>
				<td colspan='2' class='forumheader3' style='text-align:center'>\n<select name='activate' onChange='urljump(this.options[selectedIndex].value)' class='tbox'>\n<option value='forum.php' selected></option>\n<option value='forum.php?inc.".$forum_id.".".$forum_order."'>move up</option>\n<option value='forum.php?dec.".$forum_id.".".$forum_order."'>move down</option>\n</select>\n</td>\n</tr>";
			}
		}
	}
}
$text .= "</table></div>";
$ns -> tablerender("Preview / Forum Order", $text);


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>
<tr>
<td style='width:90%' class='forumheader3'>
Enable email nofication<br />
<span class='smalltext'>Tick this to allow your users to have the option of receiving an email when somebody replies to their post</span>
</td>
<td style='width:10%' class='forumheader2' style='text-align:center'>".
($pref['email_notify'][1] ? "<input type='checkbox' name='email_notify' value='1' checked>" : "<input type='checkbox' name='email_notify' value='1'>")."
</td>
</tr>

<tr>
<td style='width:90%' class='forumheader3'>
Enable polls<br />
<span class='smalltext'>Tick this to allow your users to set polls in the forums</span>
</td>
<td style='width:10%' class='forumheader2' style='text-align:center'>".
($pref['forum_poll'][1] ? "<input type='checkbox' name='forum_poll' value='1' checked>" : "<input type='checkbox' name='forum_poll' value='1'>")."
</tr>

<tr>
<td style='width:90%' class='forumheader3'>
Enable tracking<br />
<span class='smalltext'>Tick this to allow your users to track threads and be emailed when the thread is replied to</span>
</td>
<td style='width:10%' class='forumheader2' style='text-align:center'>".
($pref['forum_track'][1] ? "<input type='checkbox' name='forum_track' value='1' checked>" : "<input type='checkbox' name='forum_track' value='1'>")."
</tr>



<tr>
<td style='width:90%' class='forumheader3'>
Email prefix<br />
<span class='smalltext'>The text you enter will prefix the subject on any emails sent through the forum</span>
</td>
<td style='width:10%' class='forumheader2' style='text-align:center'>
<input class='tbox' type='text' name='forum_eprefix' size='5' value='".$pref['forum_eprefix'][1]."' maxlength='20' />
</tr>



<tr>
<td style='width:90%' class='forumheader3'>
Popular thread threshold<br />
<span class='smalltext'>Number of posts made to a thread before it is marked as popular</span>
</td>
<td style='width:10%' class='forumheader2' style='text-align:center'>
<input class='tbox' type='text' name='forum_popular' size='3' value='".$pref['forum_popular'][1]."' maxlength='3' />
</tr>


<tr style='vertical-align:top'> 
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updateoptions' value='Update Options' />
</td>
</tr>

</table>
</form>
</div>";
$ns -> tablerender("Forum Options", $text);

require_once("footer.php");
?>	