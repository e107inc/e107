<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/forum_viewforum.php
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

if(!e_QUERY){
	header("Location:".e_HTTP."forum.php");
}else{
	$tmp = explode(".", e_QUERY);
	$forum_id = $tmp[0]; $from = $tmp[1];
	if(!$from){ $from = 0; }
}

$view=15;
$captionlinkcolour = "#fff";
	
$sql -> db_Select("forum", "*", "forum_id='".$forum_id."' ");
$row = $sql-> db_Fetch(); extract($row);

if(!$forum_active || $forum_class && !check_class($forum_class)){ header("Location:".e_HTTP."forum.php"); }

require_once(HEADERF);

$topics = $sql -> db_Count("forum_t", "(*)", " WHERE thread_forum_id='".$forum_id."' AND thread_parent='0' ");

if($topics > $view){
	$a = $topics/$view;
	$r = explode(".", $a);
	if($r[1] != 0 ? $pages = ($r[0]+1) : $pages = $r[0]);
}else{
	$pages = FALSE;
}

$text = "<table style=\"width:100%\" class=\"fborder\">
<tr>
<td  colspan=\"2\" class=\"fcaption\">
<a style=\"color:$captionlinkcolour\" href=\"index.php\">".SITENAME."</a> >> <a style=\"color:$captionlinkcolour\" href=\"forum.php\">Forums</a> >> <b>".$forum_name."</b>
</td>
</tr>
<tr>
<td style=\"width:80%; vertical-align:bottom\">".LAN_321.$forum_moderators;
	
if($pages){
	$text .= "<br />".LAN_316;
	for($c=0; $c < $pages; $c++){
		if($view*$c == $from ? $text .= "<u>".($c+1)."</u> " : $text .= "<a href=\"".e_SELF."?".$forum_id.".".($view*$c)."\">".($c+1)."</a> ");
	}
}
	
$text .= "</td>
<td style=\"width:20%; text-align:right\">";

if(ANON || USER){
	$text .= "<a href=\"".e_HTTP."forum_post.php?nt.".$forum_id."\"><img src=\"".e_HTTP."themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
}
$text .= "</td></tr><tr>
<td colspan=\"2\">";

echo $text;

echo "
<table style=\"width:100%\" class=\"fborder\">
<tr>
<td style=\"width:3%\" class=\"fcaption\">&nbsp;</td>
<td style=\"width:47%\" class=\"fcaption\">".LAN_53."</td>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">".LAN_54."</td>
<td style=\"width:5%; text-align:center\" class=\"fcaption\">".LAN_55."</td>
<td style=\"width:5%; text-align:center\" class=\"fcaption\">".LAN_56."</td>
<td style=\"width:20%; text-align:center\" class=\"fcaption\">".LAN_57."</td>
</tr>
";

if(!$topics){
	echo "<td colspan=\"6\" style=\"text-align:center\" class=\"forumheader2\">".LAN_58."</td>";
}else{
	$sql -> db_Select("forum_t", "*",  "thread_forum_id='".$forum_id."' AND thread_parent='0' ORDER BY thread_s DESC, thread_lastpost DESC, thread_datestamp DESC LIMIT $from, $view");
	$sql2 = new db; $sql3 = new db; $gen = new convert;
	while($row= $sql -> db_Fetch()){
		extract($row);
			
		$replies = $sql2 -> db_Count("forum_t", "(*)", " WHERE thread_parent='$thread_id' ");
		if($replies){
			$sql2 -> db_Select("forum_t", "*", "thread_parent='$thread_id' ORDER BY thread_datestamp DESC");
			list($null, $null, $null, $null, $r_datestamp, $null, $r_user) = $sql2 -> db_Fetch();
			$r_id = substr($r_user, 0, strpos($r_user, "."));
			$r_name = substr($r_user, (strpos($r_user, ".")+1));
			$r_datestamp = $gen->convert_date($r_datestamp, "forum");
			if(!$r_id ? $lastreply = $r_datestamp."<br />".$r_name : $lastreply = $r_datestamp."<br /><a href=\"user.php?id.".$r_id."\">".$r_name."</a>");
		}else{
			$replies = LAN_317;
			$lastreply = " - ";
		}

		$post_author_id = substr($thread_user, 0, strpos($thread_user, "."));
		$post_author_name = substr($thread_user, (strpos($thread_user, ".")+1));

		echo "<tr>
		<td style=\"vertical-align:center; width:3%\" class=\"forumheader3\">";

		$newflag = FALSE;
		if($thread_datestamp > USERLV && (!ereg("\.".$thread_id."\.", USERVIEWED))){
			$newflag = TRUE;
		}else if($sql3 -> db_SELECT("forum_t", "*", "thread_parent='$thread_id' AND thread_datestamp > '".USERLV."' ")){
			while(list($nthread_id) = $sql3 -> db_Fetch()){
				if(!ereg("\.".$nthread_id."\.", USERVIEWED)){
					$newflag = TRUE;
				}
			}
		}

		$thread_datestamp = $gen->convert_date($thread_datestamp, "forum");
		if($thread_s){
			if(!$thread_active){
				echo "<img src=\"themes/shared/forum/stickyclosed.png\" alt=\"\" />";
			}else{
				echo "<img src=\"themes/shared/forum/sticky.png\" alt=\"\" />";
			}
		}else if(!$thread_active){
			echo "<img src=\"themes/shared/forum/closed.png\" alt=\"\" />";
		}else if($newflag){
			echo "<img src=\"themes/shared/forum/new.png\" alt=\"\" />";
		}else{
			echo "<img src=\"themes/shared/forum/nonew.png\" alt=\"\" />";
		}

		echo "</td><td style=\"vertical-align:center; text-align:left; width:47%\"  class=\"forumheader3\"><span class=\"mediumtext\"><a href=\"forum_viewtopic.php?".$forum_id.".".$thread_id."\">".$thread_name."</a></span>";

		if(eregi(ADMINNAME, $forum_moderators)){
			echo "<div class=\"smalltext\" style=\"text-align:right\">".LAN_318;
			if($thread_s){
				echo "[ <a href=\"admin/forum_conf.php?unstick.".$forum_id.".".$thread_id."\">".LAN_319."</a> ]";
				if($thread_active){
					echo "[ <a href=\"admin/forum_conf.php?close.".$forum_id.".".$thread_id."\">".LAN_200."</a> ]";
				}else{
					echo "[ <a href=\"admin/forum_conf.php?open.".$forum_id.".".$thread_id."\">".LAN_201."</a> ]";
				}
			}else{
				echo "[ <a href=\"admin/forum_conf.php?stick.".$forum_id.".".$thread_id."\">".LAN_320."</a> ]";
				if($thread_active){
					echo "[ <a href=\"admin/forum_conf.php?close.".$forum_id.".".$thread_id."\">".LAN_200."</a> ]";
				}else{
					echo "[ <a href=\"admin/forum_conf.php?open.".$forum_id.".".$thread_id."\">".LAN_201."</a> ]";
				}
				echo "</div>";
			}
		}
			
		echo "</td>
<td style=\"vertical-align:top; text-align:center; width:20%\" class=\"forumheader3\">".$thread_datestamp."<br />";
echo (!$post_author_id ? $post_author_name :  "<a href=\"user.php?id.".$post_author_id."\">".$post_author_name."</a>");
echo "</td><td style=\"vertical-align:center; text-align:center; width:5%\" class=\"forumheader3\">$replies</td>
<td style=\"vertical-align:center; text-align:center; width:5%\" class=\"forumheader3\">$thread_views</td>
<td style=\"vertical-align:top; text-align:center; width:20%\" class=\"forumheader3\">$lastreply</td>
</tr>";
	}
	echo "</table>";
}

$text = "<table style=\"width:100%\">
<tr>
<td style=\"width:80%\">";
if($pages){
$text .= LAN_316;
	for($c=0; $c < $pages; $c++){
		if($view*$c == $from ? $text .= "<u>".($c+1)."</u> " : $text .= "<a href=\"".e_SELF."?".$forum_id.".".($view*$c)."\">".($c+1)."</a> ");
	}
	$text .= "<br />";
}
$text .= forumjump();
$text .= "</td>
<td style=\"width:20%; text-align:right\">";

if(ANON || USER){
	$text .= "<a href=\"forum_post.php?nt.".$forum_id."\"><img src=\"themes/shared/forum/newthread.png\" alt=\"\" style=\"border:0\" /></a>";
}else{
	$text .= LAN_59;
}
$text .= "</td>
</tr>
</table>";

echo $text;
echo "</td></tr></table><br />";

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
require_once(FOOTERF);

function forumjump(){
	$sql = new db;
	$sql -> db_Select("forum", "*", "forum_parent !=0 AND forum_active='1'");
	$c=0;
	$text .= "<form method=\"post\" action=\"".e_SELF."\"><p>Jump: <select name=\"forumjump\" class=\"tbox\">";
	while($row = $sql -> db_Fetch()){
		extract($row);
		if(!$forum_class || check_class($forum_class)){
			$text .= "\n<option>".$forum_name."</option>";
		}
	}
	$text .= "</select><input class=\"button\" type=\"submit\" name=\"fjsubmit\" value=\"Go\" />&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"".e_SELF."?".$_SERVER['QUERY_STRING']."#top\">Back to top</a></p></form>";
	return $text;
}

?>