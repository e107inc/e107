<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/forum.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");

if(ereg("untrack", e_QUERY)){
	$tmp1 = explode(".", e_QUERY);
	print_r($tmp1);
	$tmp = str_replace(".".$tmp1[1].".", "", USERREALM);
	$sql -> db_Update("user", "user_realm='$tmp' WHERE user_id='".USERID."' ");
	header("location:".e_SELF."?track");
	exit;
}

define("FTHEME", (file_exists(THEME."forum/newthread.png")) ? THEME."forum/" : "themes/shared/forum/");

if(e_QUERY && e_QUERY != "track"){
	$forum_id = e_QUERY;
	echo $forum_id;
	if(!is_numeric($forum_id) && $forum_id != "tracked"){
		$sql -> db_Select("forum_t", "thread_id", "thread_datestamp > '".USERLV."' ");
	}else{
		$sql -> db_Select("forum_t", "thread_id", "thread_forum_id='$forum_id' AND thread_datestamp > '".USERLV."' ");
	}
	while($row = $sql -> db_Fetch()){
		extract($row);
		$u_new .= ".".$thread_id.".";
	}
	$u_new .= USERVIEWED;
	$sql -> db_Update("user", "user_viewed='$u_new' WHERE user_id='".USERID."' ");
	header("location:".e_SELF);
	exit;
}

require_once(HEADERF);
$gen = new convert;
$total_topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
$total_replies = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
$total_members = $sql -> db_Count("user");
$newest_member = $sql -> db_Select("user", "*", "ORDER BY user_join DESC LIMIT 0,1", $mode="no_where");
list($nuser_id, $nuser_name)  = $sql -> db_Fetch();

echo "
<table style='width:100%' class='fborder'>
<tr>
<td colspan='2' style='width:60%; text-align:center' class='fcaption'>".LAN_46."</td>
<td style='width:10%; text-align:center' class='fcaption'>".LAN_47."</td>
<td style='width:10%; text-align:center' class='fcaption'>".LAN_48."</td>
<td style='width:20%; text-align:center' class='fcaption'>".LAN_49."</td>
</tr>";

if(!$sql -> db_Select("forum", "*", "forum_parent='0' ORDER BY forum_order ASC")){
	$text .= "<tr><td>".LAN_51."</td></tr>";
}else{
	$sql2 = new db; $sql3 = new db;

	while($row = $sql-> db_Fetch()){
		extract($row);
		if(!$forum_active){
			$text .= "<tr><td colspan='5' class='forumheader'>".$forum_name." (Closed)</td></tr>";
			$parent_status == "closed";
		}else if($forum_class){
			if(check_class($forum_class)){
				$text .= "<tr><td colspan='5' class='forumheader'>".$forum_name." (Restricted)</td></tr>";
				$parent_status == "open";
			}else{
				$parent_status == "closed";
			}
		}else{
			$text .= "<tr><td colspan='5' class='forumheader'>".$forum_name."</td></tr>";
			$parent_status == "open";
		}
		

		$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ORDER BY forum_order ASC ");
		if($forums == 0 && $parent_status == "open"){
			$text .= "<td colspan='5' style='text-align:center' class='forumheader3'>".LAN_52."</td>";
		}else{
			while($row = $sql2-> db_Fetch()){
				extract($row);
				if(!$forum_class || ($forum_class && check_class($forum_class))){
					$newflag = FALSE;
					if($sql3 -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' AND thread_datestamp > '".USERLV."' ")){
						while(list($nthread_id) = $sql3 -> db_Fetch()){
							if(!ereg("\.".$nthread_id."\.", USERVIEWED)){ $newflag = TRUE; }
						}
					}

					if($forum_active == 1 && $parent_inactive == FALSE){

						if($newflag == TRUE){
							$text .= "<tr><td style='width:5%; text-align:center' class='forumheader2'><a href='".e_SELF."?".$forum_id."'><img src='".FTHEME."new.png' alt='mark all posts in this forum as read' style='border:0' /></a></td>";
						}else{
							$text .= "<tr><td style='width:5%; text-align:center' class='forumheader2'><img src='".FTHEME."nonew.png' alt='' /></td>";
						}

						$text .= "<td style='width:55%' class='forumheader2'><a href='forum_viewforum.php?".$forum_id."'>".$forum_name."</a><br /><span class='smallblacktext'>".$forum_description."</span></td>
						<td style='width:10%; text-align:center' class='forumheader3'>".$forum_threads."</td>
						<td style='width:10%; text-align:center' class='forumheader3'>".$forum_replies."</td>
						<td style='width:20%; text-align:center' class='forumheader3'><span class='smallblacktext'>";

						if($forum_threads == 0 && $forum_replies == 0){
							$text .= "No posts yet</span></td>";
						}else{
							$sql3 -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1");
							$row = $sql3 -> db_Fetch();
							extract($row);
							$tmp = explode(".", $thread_user);
							$lastpost_author_id = $tmp[0];
							$lastpost_author_name = $tmp[1];
							$lastpost_datestamp = $gen->convert_date($thread_datestamp, "forum");
							$text .= $lastpost_datestamp."<br />".
							
							($lastpost_author_id ? "<a href='user.php?id.".$lastpost_author_id."'>".$lastpost_author_name."</a> " : $lastpost_author_name);

							if($thread_parent){
								$text .= "&nbsp;&nbsp;<a href='".e_HTTP."forum_viewtopic.php?".$forum_id.".".$thread_parent."'><img src='".FTHEME."post.png' alt='' style='border:0' /></a></span></td>";
							}else{
								$text .= "&nbsp;&nbsp;<a href='".e_HTTP."forum_viewtopic.php?".$forum_id.".".$thread_id."'><img src='".FTHEME."post.png' alt='' style='border:0' /></a></span></td>";
							}
						}

						$text .= "</tr>";
					}
				}
			}
		}
	}
}
$text .= "</table>";
echo $text;


// info bar ...

if(e_QUERY != "track"){

$text = "<br /><table style='width:100%' class='fborder'>
<tr>
<td colspan='2' style='width:60%' class='fcaption'>".LAN_191."</td>
</tr>
<tr>
<td rowspan='2' style='width:5%; text-align:center' class='forumheader3'><img src='".FTHEME."e.png' alt='' /></td>
";

if(USER == TRUE){

	$total_new_threads = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".USERLV."' ");
	if(USERVIEWED != ""){
		$tmp = explode("..", USERVIEWED);
		$total_read_threads = count($tmp);
	}else{
		$total_read_threads = 0;
	}

	$text .= "<td style='width:95%' class='forumheader3'>
	".LAN_30." ".USERNAME."<br />";
	$sql -> db_Select("user", "*",  "user_name='".USERNAME."' ");
	$row = $sql -> db_Fetch();
	extract($row);
	$lastvisit_datestamp = $gen->convert_date($user_lastvisit, "long");
	$datestamp = $gen->convert_date(time(), "long");
	if(!$total_new_threads){
		$text .= LAN_31;
	}else if($total_new_threads == 1){
		$text .= LAN_32;
	}else{
		$text .= LAN_33." ".$total_new_threads." ".LAN_34." ";
	}
	$text .= LAN_35;
	if($total_new_threads == $total_read_threads && $total_new_threads !=0){
		$text .= LAN_198;
	}else if($total_read_threads != 0){
		$text .= " (".LAN_196.$total_read_threads.LAN_197.")";
	}

	if($pref['time_offset'][1] == "0" ? $tmp = "." : $tmp = $pref['time_offset'][1].".");

	$text .= "<br />
	".LAN_36." ".$lastvisit_datestamp."<br />
	".LAN_37." ".$datestamp.LAN_38.$tmp;
}else{
	$text .= "<td style='width:95%' class='forumheader3'>";
	if(ANON == TRUE && USER == FALSE){
		$text .= LAN_43;
	}else if(ANON == TRUE){
		$text .= LAN_44;
	}else if(USER == FALSE){
		$text .= LAN_45;
	}
}

if(USER){
	$text .= "<br /><a href='".e_SELF."?mark.all.as.read'>".LAN_199."</a>";
}

if(USERREALM){
	$text .= "<br /><a href='".e_SELF."?track'>".LAN_393."</a>";
}
$text .= "</td></tr><tr><td style='width:95%' class='forumheader3'>

".LAN_192.($total_topics+$total_replies)." ".strtolower(LAN_100).".<br />".LAN_42.": ".$total_members."<br />".LAN_41."<a href='user.php?id.".$nuser_id."'>".$nuser_name."</a>.<br />


</td>
</tr>
</table>";

// --- tracked items ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

}else{
	$text = "<br /><table style='width:100%' class='fborder'>
<tr>
<td colspan='3' style='width:60%' class='fcaption'>".LAN_397."</td>
</tr>
";


	$tmp = explode(".", USERREALM);

	foreach($tmp as $key => $value){
		if($value){

			$sql -> db_Select("forum_t", "*", "thread_id='".$value."' ");
			$row = $sql -> db_Fetch(); extract($row);
			$icon = "<img src='".FTHEME."nonew_small.png' alt='' />";
			if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
				$icon = "<img src='".FTHEME."new_small.png' alt='' />";
			}else if($sql3 -> db_SELECT("forum_t", "*", "thread_parent='$thread_id' AND thread_datestamp > '".USERLV."' ")){
				while(list($nthread_id) = $sql3 -> db_Fetch()){
					if(!ereg("\.".$nthread_id."\.", USERVIEWED)){
						$icon = "<img src='".FTHEME."new_small.png' alt='' />";
					}
				}
			}




			$sql -> db_Select("forum_t", "*",  "thread_id='".$tmp[$key]."' ORDER BY thread_s DESC, thread_lastpost DESC, thread_datestamp DESC");
			$row = $sql -> db_Fetch(); extract($row);

			$result = preg_split("/\]/", $thread_name);

			$thread_name = ($result[1] ? $result[0]."] <a href='forum_viewtopic.php?".$thread_forum_id.".".$thread_id."'>".ereg_replace("\[.*\]", "", $thread_name)."</a>" : "<a href='forum_viewtopic.php?".$thread_forum_id.".".$thread_id."'>".$thread_name."</a>");

			$text .= "<tr>
			<td style='text-align:center; vertical-align:middle; width:6%'  class='forumheader3'>".$icon."</td>
			<td style='vertical-align:middle; text-align:left; width:80%'  class='forumheader3'><span class='mediumtext'>".$thread_name."</span></td>
			<td style='vertical-align:middle; text-align:center; width:14%'  class='forumheader3'><span class='mediumtext'><a href='".e_SELF."?untrack.".$thread_id."'>".LAN_392."</a></td>
			</tr>";
		}
	}

	$text .= "<tr>
	<td colspan='3' class='forumheader3'>
	<a href='".e_SELF."'>Show information</a>
	</td>
	</tr>
	</table>";

}

// --- tracked items ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------






$text .= "<div class='spacer'>
<table class='fborder' style='width:100%'>
<tr>
<td class='forumheader3' style='text-align:center; width:33%'>
	<table style='width:100%'>
	<tr>

	<td style='width:2%'>
		<img src='".FTHEME."new_small.png' alt='' />
	</td>
	<td style='width:10%'>
		<span class='smallblacktext'>".LAN_79."</span>
	</td>

	<td style='width:2%'>
		<img src='".FTHEME."nonew_small.png' alt='' />
	</td>
	<td style='width:10%'>
		<span class='smallblacktext'>".LAN_80."</span>
	</td>

	<td style='width:2%'>
		<img src='".FTHEME."closed_small.png' alt='' />
	</td>
	<td style='width:10%'>
		<span class='smallblacktext'>".LAN_394."</span>
	</td>
	</tr>
	</table>
</td>

<td style='text-align:center; width:33%' class='forumheader3'>
<form method='post' action='search.php'>
<p>
<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />
<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />
</p>
</form>
</td>


<td style='width:33%; text-align:center; vertical-align:middle' class='forumheader3'>
<span class='smallblacktext'>";

if(USER == TRUE || ANON == TRUE){
	$text .= LAN_204." - ".LAN_206." - ".LAN_208;
}else{
	$text .= LAN_205." - ".LAN_207." - ".LAN_209;
}

$text .= "</span></td></tr>
</table>
</div>";
echo $text;














?>
<script type="text/javascript">
function addtext(sc){
	document.newthread.post.value += sc;
}
function help(help){
	document.newthread.helpb.value = help;
}
</script>
<?php


require_once(FOOTERF);
?>