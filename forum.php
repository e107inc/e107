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
require_once(HEADERF);

$gen = new convert;
$total_topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent='0' ");
$total_replies = $sql -> db_Count("forum_t", "(*)", " WHERE thread_parent!='0' ");
$total_members = $sql -> db_Count("user");
$newest_member = $sql -> db_Select("user", "*", "ORDER BY user_join DESC LIMIT 0,1", $mode="no_where");
list($nuser_id, $nuser_name)  = $sql -> db_Fetch();

echo "
<table style=\"width:100%\" class=\"fborder\">
<tr>
<td colspan=\"2\" style=\"width:60%; text-align:center\" class=\"fcaption\">".LAN_46."</td>
<td style=\"width:10%; text-align:center\" class=\"fcaption\">".LAN_47."</td>
<td style=\"width:10%; text-align:center\" class=\"fcaption\">".LAN_48."</td>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">".LAN_49."</td>
</tr>";

if(!$sql -> db_Select("forum", "*", "forum_parent='0' ")){
	$text .= "<tr><td>".LAN_51."</td></tr>";
}else{
	$sql2 = new db; $sql3 = new db;

	while($row = $sql-> db_Fetch()){
		extract($row);
		if(!$forum_active){
			$text .= "<tr><td colspan=\"5\" class=\"forumheader\">".$forum_name." (Closed)</td></tr>";
			$parent_status == "closed";
		}else{
			if($forum_class){
				if(check_class($forum_class)){
					$text .= "<tr><td colspan=\"5\" class=\"forumheader\">".$forum_name." (Restricted)</td></tr>";
					$parent_status == "open";
				}else{
					break;
				}
			}else{
				$text .= "<tr><td colspan=\"5\" class=\"forumheader\">".$forum_name."</td></tr>";
				$parent_status == "open";
			}
		}

		$forums = $sql2 -> db_Select("forum", "*", "forum_parent='".$forum_id."' ");
		if($forums == 0){
			$text .= "<td colspan=\"5\" align=\"center\">".LAN_52."<br /><br /></td>";
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
							$text .= "<tr><td style=\"width:5%; text-align:center\" class=\"forumheader2\"><img src=\"themes/shared/forum/new.png\" alt=\"\" /></td>";
						}else{
							$text .= "<tr><td style=\"width:5%; text-align:center\" class=\"forumheader2\"><img src=\"themes/shared/forum/nonew.png\" alt=\"\" /></td>";
						}

						$text .= "<td style=\"width:55%\" class=\"forumheader2\"><a href=\"forum_viewforum.php?".$forum_id."\">".$forum_name."</a><br /><span class=\"smallblacktext\">".$forum_description."</span></td>
						<td style=\"width:10%; text-align:center\" class=\"forumheader3\">".$forum_threads."</td>
						<td style=\"width:10%; text-align:center\" class=\"forumheader3\">".$forum_replies."</td>
						<td style=\"width:20%; text-align:center\" class=\"forumheader3\"><span class=\"smallblacktext\">";

						if($forum_threads == 0 && $forum_replies == 0){
							$text .= "No posts yet</td>";
						}else{
							$sql3 -> db_Select("forum_t", "*", "thread_forum_id='$forum_id' ORDER BY thread_datestamp DESC LIMIT 0,1");
							$row = $sql3 -> db_Fetch();
							extract($row);
							$tmp = explode(".", $thread_user);
							$lastpost_author_id = $tmp[0];
							$lastpost_author_name = $tmp[1];
							$lastpost_datestamp = $gen->convert_date($thread_datestamp, "forum");
							$text .= $lastpost_datestamp."<br /><a href=\"user.php?id.".$lastpost_author_id."\">".$lastpost_author_name."</a> ";

							if($thread_parent){
								$text .= "&nbsp;&nbsp;<a href=\"".e_HTTP."forum_viewtopic.php?".$forum_id.".".$thread_parent."\"><img src=\"themes/shared/forum/post.png\" alt=\"\" style=\"border:0\" /></a></span></td>";
							}else{
								$text .= "&nbsp;&nbsp;<a href=\"".e_HTTP."forum_viewtopic.php?".$forum_id.".".$thread_id."\"><img src=\"themes/shared/forum/post.png\" alt=\"\" style=\"border:0\" /></a></span></td>";
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

$text = "<br /><table style=\"width:100%\" class=\"fborder\">
<tr>
<td colspan=\"2\" style=\"width:60%\" class=\"fcaption\">".LAN_191."</td>
</tr>
<tr>
<td rowspan=\"2\" style=\"width:5%; text-align:center\" class=\"forumheader3\"><img src=\"themes/shared/forum/e.png\" alt=\"\" /></td>
";

if(USER == TRUE){

	$total_new_threads = $sql -> db_Count("forum_t", "(*)", "WHERE thread_datestamp>'".USERLV."' ");
	if(USERVIEWED != ""){
		$tmp = explode("..", USERVIEWED);
		$total_read_threads = count($tmp);
	}else{
		$total_read_threads = 0;
	}

	$text .= "<td style=\"width:95%\" class=\"forumheader3\">
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
	$text .= "<td style=\"width:95%\" class=\"forumheader3\">";
	if(ANON == TRUE && USER == FALSE){
		$text .= LAN_43;
	}else if(ANON == TRUE){
		$text .= LAN_44;
	}else if(USER == FALSE){
		$text .= LAN_45;
	}
}

if(!$total_new_threads == $total_read_threads && $total_new_threads !=0){
	$text .= "<br /><a href=\"forum.php?maar\">".LAN_199."</a>";
}
$text .= "</td></tr><tr><td style=\"width:95%\" class=\"forumheader3\">

".LAN_192.($total_topics+$total_replies)." ".strtolower(LAN_100).".<br />".LAN_42.": ".$total_members."<br />".LAN_41."<a href=\"user.php?id.".$nuser_id."\">".$nuser_name."</a>.<br />

</td>
</tr>
</table>";
echo $text;

echo "<br />";

$text = "<div style=\"text-align:center\">
<table style=\"width:100%\" class=\"fborder\">
<tr>
<td style=\"width:100%; vertical-align:top\">
<table style=\"width:100%\">
<tr>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/new.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\" class=\"smallblacktext\"> ".LAN_79."</td>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/nonew.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\" class=\"smallblacktext\"> ".LAN_80."</td>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/sticky.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\" class=\"smallblacktext\"> ".LAN_202."</td>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/stickyclosed.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\" class=\"smallblacktext\"> ".LAN_203."</td>
<td style=\"vertical-align:center; width:3%\"><img src=\"themes/shared/forum/closed.png\" alt=\"\" /></td>
<td style=\"vertical-align:center\" class=\"smallblacktext\"> ".LAN_81."</td>
</tr>
</table>
</td>
</tr>
<tr>
<td style=\"width:100%; text-align:center; vertical-align:top\" class=\"smallblacktext\">";

if(USER == TRUE || ANON == TRUE){
	$text .= LAN_204." - ".LAN_206." - ".LAN_208;
}else{
	$text .= LAN_205." - ".LAN_207." - ".LAN_209;
}

$text .= "</td></tr>
<tr>
<td style=\"text-align:center\" colspan=\"2\">
<form method=\"post\" action=\"search.php\">
<p>
<input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"\" maxlength=\"50\" />
<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"".LAN_180."\" />
</p>
</form>
</td>
</tr>
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