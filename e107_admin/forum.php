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
if(!getperms("5")){ header("location:".e_BASE."index.php"); exit; }
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
$forum = new forum;
$aj = new textparse;

if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}

If(IsSet($_POST['submit_parent'])){
	$_POST['forum_name'] = $aj -> formtpa($_POST['forum_name'], "admin");
	$sql -> db_Insert("forum", "0, '".$_POST['forum_name']."', '', '', '".time()."', '0', '0', '0', '', '".$_POST['forum_class']."', 0");
	$forum -> show_message(FORLAN_13);
}

If(IsSet($_POST['update_parent'])){
	$_POST['forum_name'] = $aj -> formtpa($_POST['forum_name'], "admin");
	$sql -> db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_class='".$_POST['forum_class']."' WHERE forum_id=$id");
	$forum -> show_message(FORLAN_14);
	$action = "main";
}

If(IsSet($_POST['submit_forum'])){
	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);
	$_POST['forum_name'] = $aj -> formtpa($_POST['forum_name'], "admin");
	$_POST['forum_description'] = $aj -> formtpa($_POST['forum_description'], "admin");

	$sql -> db_Insert("forum", "0, '".$_POST['forum_name']."', '".$_POST['forum_description']."', '".$_POST['forum_parent']."', '".time()."', '".$mods."', 0, 0, 0, '".$_POST['forum_class']."', 0");
	$forum -> show_message(FORLAN_11);
}

If(IsSet($_POST['update_forum'])){

	$c = 0;
	while($_POST['mod'][$c]){
		$mods .= $_POST['mod'][$c].", ";
		$c++;
	}
	$mods = ereg_replace(", $", ".", $mods);
	$_POST['forum_name'] = $aj -> formtpa($_POST['forum_name'], "admin");
	$_POST['forum_description'] = $aj -> formtpa($_POST['forum_description'], "admin");
	$forum_parent = $row['forum_id'];
	$sql -> db_Update("forum", "forum_name='".$_POST['forum_name']."', forum_description='".$_POST['forum_description']."', forum_parent='".$_POST['forum_parent']."', forum_moderators='".$mods."', forum_class='".$_POST['forum_class']."' WHERE forum_id=$id");
	$forum -> show_message(FORLAN_12);
	$action = "main";
}

if(IsSet($_POST['update_order'])){
	extract($_POST);
	while(list($key, $id) = each($forum_order)){
		$tmp = explode(".", $id);
		$sql -> db_Update("forum", "forum_order=".$tmp[1]." WHERE forum_id=".$tmp[0]);
	}
	$forum -> show_message(FORLAN_73);
}

if(IsSet($_POST['updateoptions'])){
	$pref['email_notify'] = $_POST['email_notify'];
	$pref['forum_poll'] = $_POST['forum_poll'];
	$pref['forum_popular'] = $_POST['forum_popular'];
	$pref['forum_track'] = $_POST['forum_track'];
	$pref['forum_eprefix'] = $_POST['forum_eprefix'];
	$pref['forum_enclose'] = $_POST['forum_enclose'];
	$pref['forum_title'] = $_POST['forum_title'];
	$pref['forum_postspage'] = $_POST['forum_postspage'];
	$pref['forum_levels'] = $_POST['forum_levels'];
	$pref['html_post'] = $_POST['html_post'];
	$pref['forum_attach'] = $_POST['forum_attach'];
	$pref['forum_redirect'] = $_POST['forum_redirect'];
	$pref['forum_user_customtitle'] = $_POST['forum_user_customtitle'];
	$pref['reported_post_email'] = $_POST['reported_post_email'];
	save_prefs();
	$forum -> show_message(FORLAN_10);
}

if(IsSet($_POST['do_prune'])){
	$sql2 = new db;
	if($_POST['prune_type'] == "delete"){
		$prunedate = time() - ($_POST['prune_days']*86400);
		if($sql -> db_Select("forum_t", "*", "thread_lastpost<$prunedate AND thread_parent=0 AND thread_s!=1")){
			while($row = $sql -> db_Fetch()){
				extract($row);
				$sql2 -> db_Delete("forum_t", "thread_parent='$thread_id' ");	// delete replies
				$sql2 -> db_Delete("forum_t", "thread_id='$thread_id' ");		// delete thread
			}

			$sql -> db_Select("forum", "*", "forum_parent!=0");
			while($row = $sql -> db_Fetch()){
				extract($row);
				$threads += $sql2 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forum_id AND thread_parent=0");
				$replies += $sql2 -> db_Count("forum_t", "(*)", "WHERE thread_forum_id=$forum_id AND thread_parent!=0");
				$sql2 -> db_Update("forum", "forum_threads='$threads', forum_replies='$replies' WHERE forum_id='$forum_id' ");
			}
			$forum -> show_message(FORLAN_8." ( ".$threads." ".FORLAN_92.", ".$replies." ".FORLAN_93." )");
		}else{
			$forum -> show_message(FORLAN_9);
		}
	}else{
		$prunedate = time() - ($_POST['prune_days']*86400);
		$pruned = $sql -> db_Update("forum_t", "thread_active=0 WHERE thread_lastpost<$prunedate AND thread_parent=0 ");
		$forum -> show_message(FORLAN_8." ".$pruned." ".FORLAN_91);
	}
	$action = "main";
}

if(IsSet($_POST['set_ranks'])){
	extract($_POST);
	for($a=0; $a<=9; $a++){
		$r_names .= $aj -> formtpa($rank_names[$a], "admin").",";
		$r_thresholds .= $aj -> formtpa($rank_thresholds[$a], "admin").",";
		$r_images .= $aj -> formtpa($rank_images[$a], "admin").",";
	}
	$pref['rank_main_admin'] = $_POST['rank_main_admin'];
	$pref['rank_main_admin_image'] = $_POST['rank_main_admin_image'];
	$pref['rank_admin'] = $_POST['rank_admin'];
	$pref['rank_admin_image'] = $_POST['rank_admin_image'];
	$pref['rank_moderator_image'] = $_POST['rank_moderator_image'];
	$pref['forum_levels'] = $r_names;
	$pref['forum_thresholds'] = $r_thresholds;
	$pref['forum_images'] = $r_images;
	save_prefs();
	$forum -> show_message(FORLAN_95);
}

if($action == "main" && $sub_action == "confirm"){
	if($sql -> db_Delete("forum", "forum_id='$id' ")){
		$forum -> show_message(FORLAN_96);
	}
}

if($action == "create"){	
	if($sql -> db_Select("forum", "*", "forum_parent='0' ")){
		$forum -> create_forums($sub_action, $id);
	}else{
		header("location:".e_ADMIN."forum.php"); 
		exit; 
	}
}

if($action == "cat"){
	if($sub_action == "confirm"){
		if($sql -> db_Delete("forum", "forum_id='$id' ")){
			$sql -> db_Delete("forum", "forum_parent='$id' ");
			$forum -> show_message(FORLAN_97);
			$action = "main";
		}
	}else{
		$forum -> create_parents($sub_action, $id);
	}
}

if($action == "order"){
	$forum -> show_existing_forums($sub_action, $id, TRUE);
}

if($action == "opt"){
	$forum -> show_prefs();
}

if($action == "prune"){
	$forum -> show_prune();
}

if($action == "rank"){
	$forum -> show_levels();
}

if($action == "sr"){
	if($sub_action == "confirm"){
		$sql -> db_Delete("tmp", "tmp_time='$id' ");
			$forum -> show_message(FORLAN_118);
	}
	$forum -> show_reported();
}


if(!e_QUERY || $action == "main"){
	$forum -> show_existing_forums($sub_action, $id);
}







//$forum -> show_options($action);
require_once("footer.php");

echo "<script type=\"text/javascript\">
function confirm_(mode, forum_id, forum_name){
	if(mode == 'sr'){
		var x=confirm(\"".FORLAN_117."\");
	}else if(mode == 'parent'){
		var x=confirm(\"".FORLAN_81." [ID: \" + forum_name + \"]\");
	}else{
		var x=confirm(\"".FORLAN_82." [ID: \" + forum_name + \"]\");
	}
if(x)
	if(mode == 'sr'){
		window.location='".e_SELF."?sr.confirm.' + forum_id;
	}else if(mode == 'parent'){
		window.location='".e_SELF."?cat.confirm.' + forum_id;
	}else{
		window.location='".e_SELF."?main.confirm.' + forum_id;
	}
}
</script>";

exit;

class forum{

	function show_options($action){
		global $sql;
		if($action==""){$action="main";}
		// ##### Display options ---------------------------------------------------------------------------------------------------------
		$var['main']['text']=FORLAN_76;
		$var['main']['link']=e_SELF;
		$var['cat']['text']=FORLAN_83;
		$var['cat']['link']=e_SELF."?cat";
		if($sql -> db_Select("forum", "*", "forum_parent='0' ")){
			$var['create']['text']=FORLAN_77;
			$var['create']['link']=e_SELF."?create";
		}
		$var['order']['text']=FORLAN_78;
		$var['order']['link']=e_SELF."?order";
		$var['opt']['text']=FORLAN_79;
		$var['opt']['link']=e_SELF."?opt";
		$var['prune']['text']=FORLAN_59;
		$var['prune']['link']=e_SELF."?prune";
		$var['rank']['text']=FORLAN_63;
		$var['rank']['link']=e_SELF."?rank";
		if($sql -> db_Select("tmp", "*", "tmp_ip='reported_post' ")){
			$var['sr']['text']=FORLAN_116;
			$var['sr']['link']=e_SELF."?sr";
		}
		show_admin_menu(FORLAN_7,$action,$var);
	}
	function show_existing_forums($sub_action, $id, $mode=FALSE){
		global $sql, $rs, $ns, $sql2, $sql3;
		if(!is_object($sql2)){
			$sql2 = new db;
		}
		if(!is_object($sql3)){
			$sql3 = new db;
		}
		if(!$mode){
			$text = "<div style='border : solid 1px #000; padding : 4px; width : auto; height : 200px; overflow : auto; '>";
		}else{
			$text = "<form method='post' action='".e_SELF."?".e_QUERY."'>";
		}
		$text .= "
		<table style='width:100%' class='fborder'>
		<tr>
		<td colspan='2' style='width:70%; text-align:center' class='fcaption'>".FORLAN_28."</td>
		<td style='width:30%; text-align:center' class='fcaption'>".FORLAN_80."</td>
		</tr>";

		if(!$parent_amount = $sql -> db_Select("forum", "*", "forum_parent='0' ORDER BY forum_order ASC")){
			$text .= "<tr><td class='forumheader3' style='text-align:center' colspan='3'>".FORLAN_29."</td></tr>";
		}else{
			$sql2 = new db; $sql3 = new db;
			while($row = $sql-> db_Fetch()){
				extract($row);
				
				if($forum_class==e_UC_MEMBER){
					$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_84.")</td>";
				}else if($forum_class==e_UC_NOBODY){
					$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_38.")</td>";
				}else if($forum_class==e_UC_ADMIN){
					$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_39.")</td>";
				}else if($forum_class){
					$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name." (".FORLAN_40.")</td>";
				}else{
					$text .= "<tr><td colspan='2' class='forumheader'>".$forum_name."</td>";
				}

				$text .= "<td class='forumheader' style='text-align:center'>";

				if($mode){
					$text .= "<select name='forum_order[]' class='tbox'>\n";
					for($a=1; $a<= $parent_amount; $a++){
						$text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
					}
					$text .= "</select>";
				}else{
					$text .= $rs -> form_button("submit", "main_edit", FORLAN_19, "onClick=\"document.location='".e_SELF."?cat.edit.$forum_id'\"")."
					".$rs -> form_button("submit", "main_delete", FORLAN_20, "onClick=\"confirm_('parent', $forum_id, '$forum_name')\"");
				}
				$text .= "</td></tr>";

				$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ORDER BY forum_order ASC");
				if(!$forums){
					$text .= "<td colspan='4' style='text-align:center' class='forumheader3'>".FORLAN_29."</td>";
				}else{
					while($row = $sql2-> db_Fetch()){
						extract($row);

						$text .= "<tr><td style='width:5%; text-align:center' class='forumheader2'><img src='".e_IMAGE."forum/new.png' alt='' /></td>\n<td style='width:55%' class='forumheader2'><a href='".e_BASE."forum_viewforum.php?".$forum_id."'>".$forum_name."</a>" ;
						
						if($forum_class==e_UC_MEMBER){
							$text .= "(".FORLAN_84.")";
						}else if($forum_class==e_UC_NOBODY){
							$text .= "(".FORLAN_38.")";
						}else if($forum_class == e_UC_READONLY){
							$text .= "(".FORLAN_85.")";
						}else if($forum_class == e_UC_ADMIN){
							$text .= "(".FORLAN_86.")";
						}else if($forum_class){
							$text .= "(".FORLAN_40.")";
						}
						
						$text .= "<br /><span class='smallblacktext'>".$forum_description."</span></td>
						<td colspan='2' class='forumheader3' style='text-align:center'>";
				
						if($mode){
							$text .= "<select name='forum_order[]' class='tbox'>\n";
							for($a=1; $a<= $forums; $a++){
								$text .= ($forum_order == $a ? "<option value='$forum_id.$a' selected>$a</option>\n" : "<option value='$forum_id.$a'>$a</option>\n");
							}
							$text .= "</select>";
						}else{

							$text .= $rs -> form_button("submit", "main_edit", FORLAN_19, "onClick=\"document.location='".e_SELF."?create.edit.$forum_id'\"")."
							".$rs -> form_button("submit", "main_delete", FORLAN_20, "onClick=\"confirm_('forum', $forum_id, '$forum_name')\"");
						}
						$text .= "</td>\n</tr>";
					}
				}
			}
		}

		if(!$mode){
			$text .= "</table></div>";
			$ns -> tablerender(FORLAN_30, $text);
		}else{
			$text .= "<tr>\n<td colspan='4' style='text-align:center' class='forumheader'>\n<input class='button' type='submit' name='update_order' value='".FORLAN_72."' />\n</td>\n</tr>\n</table>\n</form>";
			$ns -> tablerender(FORLAN_37, $text);
		}
		
	}

	function create_parents($sub_action, $id){
		global $sql, $ns;

		if($sub_action == "edit" && !$_POST['update_parent']){
			if($sql -> db_Select("forum", "*", "forum_id=$id")){
				$row = $sql -> db_Fetch(); extract($row);
			}
		}
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='width:auto' class='fborder'>

		<tr>
		<td style='width:40%' class='forumheader3'><u>".FORLAN_31."</u>:</td>
		<td style='width:60%' class='forumheader3'>
		<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='250' />
		</td>
		</tr>

		<tr> 
		<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
		<td style='width:60%' class='forumheader3'>".r_userclass("forum_class",$forum_class);

		$text .= "<tr style='vertical-align:top'> 
		<td colspan='2'  style='text-align:center' class='forumheader'>";

		if($sub_action == "edit"){
			$text .= "<input class='button' type='submit' name='update_parent' value='".FORLAN_25."' />";
		}else{
			$text .= "<input class='button' type='submit' name='submit_parent' value='".FORLAN_26."' />";
		}

		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";

		$ns -> tablerender(FORLAN_75, $text);

	}

	function create_forums($sub_action, $id){
		global $sql, $ns;

		if($sub_action == "edit" && !$_POST['update_forum']){
			if($sql -> db_Select("forum", "*", "forum_id=$id")){
				$row = $sql -> db_Fetch(); extract($row);
			}
		}

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='width:auto' class='fborder'>
		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_22.":</td>
		<td style='width:60%' class='forumheader3'>";

		$sql -> db_Select("forum", "*", "forum_parent=0");
		$text .= "<select name='forum_parent' class='tbox'>\n";
		while(list($forum_id_, $forum_name_) = $sql-> db_Fetch()){
			extract($row);
			if($forum_id_ == $forum_parent){
				$text .= "<option value='$forum_id_' selected>".$forum_name_."</option>\n";
			}else{
				$text .= "<option value='$forum_id_'>".$forum_name_."</option>\n";
			}
		}
		$text .= "</select>
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'><u>".FORLAN_31."</u>:</td>
		<td style='width:60%' class='forumheader3'>
		<input class='tbox' type='text' name='forum_name' size='60' value='$forum_name' maxlength='250' />
		</td>
		</tr>

		<td style='width:40%' class='forumheader3'><u>".FORLAN_32."</u>: </td>
		<td style='width:60%' class='forumheader3'>
		<textarea class='tbox' name='forum_description' cols='50' rows='5'>$forum_description</textarea>
		</td>
		</tr>

		<tr>
		<td style='width:40%' class='forumheader3'>".FORLAN_33.":<br /><span class='smalltext'>(".FORLAN_34.")</span></td>
		<td style='width:60%' class='forumheader3'>";
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
		<tr> 
		<td style='width:40%' class='forumheader3'>".FORLAN_23.":<br /><span class='smalltext'>(".FORLAN_24.")</span></td>
		<td style='width:60%' class='forumheader3'>".r_userclass("forum_class",$forum_class, "on");
		$text .= "<tr style='vertical-align:top'> 
		<td colspan='2'  style='text-align:center' class='forumheader'>";
		if($sub_action == "edit"){
			$text .= "<input class='button' type='submit' name='update_forum' value='".FORLAN_35."' />";
		}else{
			$text .= "<input class='button' type='submit' name='submit_forum' value='".FORLAN_36."' />";
		}
		$text .= "</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns -> tablerender(FORLAN_28, $text);
	}

	function show_message($message){
		global $ns;
		$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
	}

	function show_prefs(){
		global $pref, $ns;
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='width:auto' class='fborder'>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_44."<br /><span class='smalltext'>".FORLAN_45."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['forum_enclose'] ? "<input type='checkbox' name='forum_enclose' value='1' checked>" : "<input type='checkbox' name='forum_enclose' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_65."<br /><span class='smalltext'>".FORLAN_46."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'><input class='tbox' type='text' name='forum_title' size='15' value='".$pref['forum_title']."' maxlength='100' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_47."<br /><span class='smalltext'>".FORLAN_48."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['email_notify'] ? "<input type='checkbox' name='email_notify' value='1' checked>" : "<input type='checkbox' name='email_notify' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_68."<br /><span class='smalltext'>".FORLAN_69."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['html_post'] ? "<input type='checkbox' name='html_post' value='1' checked>" : "<input type='checkbox' name='html_post' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_49."<br /><span class='smalltext'>".FORLAN_50."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['forum_poll'] ? "<input type='checkbox' name='forum_poll' value='1' checked>" : "<input type='checkbox' name='forum_poll' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_70."<br /><span class='smalltext'>".FORLAN_71."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['forum_attach'] ? "<input type='checkbox' name='forum_attach' value='1' checked>" : "<input type='checkbox' name='forum_attach' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_51."<br /><span class='smalltext'>".FORLAN_52."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['forum_track'] ? "<input type='checkbox' name='forum_track' value='1' checked>" : "<input type='checkbox' name='forum_track' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_112."<br /><span class='smalltext'>".FORLAN_113."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['forum_redirect'] ? "<input type='checkbox' name='forum_redirect' value='1' checked>" : "<input type='checkbox' name='forum_redirect' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_114."<br /><span class='smalltext'>".FORLAN_115."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['forum_user_customtitle'] ? "<input type='checkbox' name='forum_user_customtitle' value='1' checked>" : "<input type='checkbox' name='forum_user_customtitle' value='1'>")."</td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_116."<br /><span class='smalltext'>".FORLAN_122."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'>".($pref['reported_post_email'] ? "<input type='checkbox' name='reported_post_email' value='1' checked>" : "<input type='checkbox' name='reported_post_email' value='1'>")."</td>
		</tr>


		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_53."<br /><span class='smalltext'>".FORLAN_54."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'><input class='tbox' type='text' name='forum_eprefix' size='15' value='".$pref['forum_eprefix']."' maxlength='20' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_55."<br /><span class='smalltext'>".FORLAN_56."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'><input class='tbox' type='text' name='forum_popular' size='3' value='".$pref['forum_popular']."' maxlength='3' /></td>
		</tr>

		<tr>
		<td style='width:75%' class='forumheader3'>".FORLAN_57."<br /><span class='smalltext'>".FORLAN_58."</span></td>
		<td style='width:25%' class='forumheader2' style='text-align:center'><input class='tbox' type='text' name='forum_postspage' size='3' value='".$pref['forum_postspage']."' maxlength='3' /></td>
		</tr>

		<tr> 
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='updateoptions' value='".FORLAN_61."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns -> tablerender(FORLAN_62, $text);
	}

	function show_reported ($sub_action, $id){
			global $sql, $rs, $ns, $aj;
			$text = "<div style='border : solid 1px #000; padding : 4px; width :auto; height : 200px; overflow : auto; '>\n";
			if($reported_total = $sql -> db_Select("tmp", "*", "tmp_ip='reported_post' ")){
				$text .= "<table class='fborder' style='width:100%'>
				<tr>
				<td style='width:80%' class='forumheader2'>".FORLAN_119."</td>
				<td style='width:20%; text-align:center' class='forumheader2'>".FORLAN_80."</td>
				</tr>";
				while($row = $sql -> db_Fetch()){
					extract($row);
					$reported = explode("^", $tmp_info);
					$text .= "<tr>
					<td style='width:80%' class='forumheader3'><a href='".e_BASE."forum_viewtopic.php?".$reported[0].".".$reported[1]."#".$reported[2]."' rel='external'>".$reported[3]."</a></td>
					<td style='width:20%; text-align:center; vertical-align:top' class='forumheader3'>
					".$rs -> form_button("submit", "reported_delete", FORLAN_20, "onClick=\"confirm_('sr', $tmp_time);\"")."
					</td>
					</tr>\n";
				}
				$text .= "</table>";
			}else{
				$text .= "<div style='text-align:center'>".FORLAN_121."</div>";
			}
			$text .= "</div>";
			$ns -> tablerender(FORLAN_116, $text);
		}

	function show_prune(){
		global $ns;
		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='width:auto' class='fborder'>
		<tr>
		<td style='text-align:center' class='forumheader2'>".FORLAN_60."</td>
		</tr>
		<tr>

		<td style='text-align:center' class='forumheader2'>".FORLAN_87."
		<input class='tbox' type='text' name='prune_days' size='6' value='' maxlength='3' />
		</td>
		</tr>

		<tr>
		<td style='text-align:center' class='forumheader3'>".FORLAN_2."<br />
		".FORLAN_89." <input type='radio' name='prune_type' value='".FORLAN_3."'>&nbsp;&nbsp;&nbsp;
		".FORLAN_90." <input type='radio' name='prune_type' value='".FORLAN_111."' checked>
		</td>
		</tr>

		<tr> 
		<td colspan='2'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='do_prune' value='".FORLAN_5."' />
		</td>
		</tr>
		</table>
		</form>
		</div>";
		$ns -> tablerender(FORLAN_59, $text);
	}

	function show_levels(){
		global $sql, $pref, $ns, $rs;

		$rank_names = explode(",", $pref['forum_levels']);
		$rank_thresholds = ($pref['forum_thresholds'] ? explode(",", $pref['forum_thresholds']) : array(20, 100, 250, 410, 580, 760, 950, 1150, 1370, 1600));
		$rank_images = ($pref['forum_images'] ? explode(",", $pref['forum_images']) : array("lev1.png", "lev2.png", "lev3.png", "lev4.png", "lev5.png", "lev6.png", "lev7.png", "lev8.png", "lev9.png", "lev10.png"));

		$text = "<div style='text-align:center'>
		<form method='post' action='".e_SELF."?".e_QUERY."'>\n
		<table style='width:auto' class='fborder'>

		<tr>
		<td class='forumheader2' style='width:40%'>".FORLAN_98."</td>
		<td class='forumheader2' style='width:20%'>".FORLAN_102."<br /><span class='smalltext'>".FORLAN_99."</span></td>
		<td class='forumheader2' style='width:40%'>".FORLAN_104."<br /><span class='smalltext'>".FORLAN_100."</span></td>
		</tr>";

		$text .= "<tr>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_main_admin' size='30' value='".($pref['rank_main_admin'] ? $pref['rank_main_admin'] : FORLAN_101)."' maxlength='30' /></td>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_main_admin_image' size='30' value='".($pref['rank_main_admin_image'] ? $pref['rank_main_admin_image'] : "main_admin.png")."' maxlength='30' /></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin' size='30' value='".($pref['rank_admin'] ? $pref['rank_admin'] : FORLAN_103)."' maxlength='30' /></td>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin_image' size='30' value='".($pref['rank_admin_image'] ? $pref['rank_admin_image'] : "admin.png")."' maxlength='30' /></td>
		</tr>

		<tr>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_admin' size='30' value='".($pref['rank_moderator'] ? $pref['rank_moderator'] : FORLAN_105)."' maxlength='30' /></td>
		<td class='forumheader3' style='width:40%'>&nbsp;</td>
		<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_moderator_image' size='30' value='".($pref['rank_moderator_image'] ? $pref['rank_moderator_image'] : "moderator.png")."' maxlength='30' /></td>
		</tr>";

		for($a=0; $a<=9; $a++){
			$text .= "<tr>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_names[]' size='30' value='".($rank_names[$a] ? $rank_names[$a] : "")."' maxlength='30' /></td>
			<td class='forumheader3' style='width:20%; text-align:center'><input class='tbox' type='text' name='rank_thresholds[]' size='10' value='".$rank_thresholds[$a]."' maxlength='5' /></td>
			<td class='forumheader3' style='width:40%; text-align:center'><input class='tbox' type='text' name='rank_images[]' size='30' value='".($rank_images[$a] ? $rank_images[$a] : "")."' maxlength='30' /></td>
			</tr>";
		}

		$text .= "<tr> 
		<td colspan='3'  style='text-align:center' class='forumheader'>
		<input class='button' type='submit' name='set_ranks' value='".FORLAN_94."' />
		</td>
		</tr>
		</table>\n</form>\n</div>";
		$ns -> tablerender("Ranks", $text);
	}
}

function forum_adminmenu(){
	global $forum;
	global $action;
	$forum -> show_options($action);
}

?>