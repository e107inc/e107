<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/classes/comment_class.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).	
+---------------------------------------------------------------+
*/
@include(e_LANGUAGEDIR.$language."/lan_comment.php");
@include(e_LANGUAGEDIR."English/lan_comment.php");
class comment{

	function form_comment(){
		require_once(e_HANDLER."ren_help.php");
		if(ANON == TRUE || USER == TRUE){
			$ns = new e107table;
			$text = "\n<form method='post' action='".e_SELF."?".e_QUERY."' name='dataform'>\n<table style='width:95%'>";
			if(ANON == TRUE && USER == FALSE){
				$text .= "<tr>\n<td style='width:20%'>".LAN_16."</td>\n<td style='width:80%'>\n<input class='tbox' type='text' name='author_name' size='60' value='$author_name' maxlength='100' />\n</td>\n</tr>";
			}
			$text .= "<tr> \n<td style='width:20%'>".LAN_8.":</td>\n<td style='width:80%'>\n<textarea class='tbox' name='comment' cols='70' rows='10' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>\n<br />".ren_help(2)."</td></tr>\n<tr style='vertical-align:top'> \n<td style='width:20%'></td>\n<td style='width:80%'>\n<input class='button' type='submit' name='commentsubmit' value='".LAN_9."' />\n</td>\n</tr>\n</table>\n</form>";
			$ns -> tablerender(LAN_9, $text);
		}else{
			echo "<br /><div style='text-align:center'><b>".LAN_6."</b></div>";
		}
	}

	function render_comment($row){
		global $COMMENTSTYLE, $pref;

		require_once(e_HANDLER."level_handler.php");

		$sql = new db;
		$ns = new e107table;
		extract($row);
		$comment_author = eregi_replace("[0-9]+\.", "", $comment_author);
		$gen = new convert; $datestamp = $gen->convert_date($comment_datestamp, "short");
		if($sql -> db_Select("user", "*", "user_name='$comment_author'")){
			$row = $sql -> db_Fetch();
			extract($row);
			if($user_image){
				require_once(e_HANDLER."avatar_handler.php");
				$user_image = avatar($user_image);
			}
		}else{
			$user_id = 0;
			$user_name = $comment_author;
		}
			
		$user_join = $gen->convert_date($user_join, "short");

		$url = e_PAGE."?".e_QUERY;

		$unblock = "[<a href='".e_BASE.e_ADMIN."comment.php?unblock-$comment_id-$url-$comment_item_id'>".LAN_1."</a>] ";
		$block = "[<a href='".e_BASE.e_ADMIN."comment.php?block-$comment_id-$url-$comment_item_id'>".LAN_2."</a>] ";
		$delete = "[<a href='".e_BASE.e_ADMIN."comment.php?delete-$comment_id-$url-$comment_item_id'>".LAN_3."</a>] ";
		$userinfo = "[<a href='".e_BASE.e_ADMIN."userinfo.php?$comment_ip'>".LAN_4."</a>]";
	
		if(!$COMMENTSTYLE){
			$COMMENTSTYLE = "<table style='width:95%'>
			<tr>
			<td style='width:30%; vertical-align=top'>
			<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
			<span class='defaulttext'><i>
			{USERNAME}
			<br />
			</i></span>
			{LEVEL}
			<span class='smalltext'>on 
			{TIMEDATE}
			<br />
			{COMMENTS}
			</span>
			</td>
			<td style='width:70%; vertical-align=top'>
			<span class='mediumtext'>
			{COMMENT}
			</span>
			</td>
			</tr></table><br />";
		}

		$aj = new textparse;

		$search[0] = "/\{USERNAME\}(.*?)/si";
		$replace[0] = ($user_id ? "<a href='".e_BASE."user.php?id.".$user_id."'>".$user_name."</a>\n" : $user_name."\n");

		$search[1] = "/\{TIMEDATE\}(.*?)/si";
		$replace[1] = $datestamp;

		$search[2] = "/\{AVATAR\}(.*?)/si";
		$replace[2] = ($user_image ? "<div class='spacer'><img src='".$user_image."' alt='' /></div><br />" : "");
		
		$search[3] = "/\{COMMENTS\}(.*?)/si";
		$replace[3] = ($user_id ? LAN_99.": ".$user_comments : LAN_194)."<br />";

		$search[4] = "/\{COMMENT\}(.*?)/si";
		$replace[4] = ($comment_blocked ? LAN_0 : preg_quote($aj -> tpa($comment_comment)));

		$search[5] = "/\{SIGNATURE\}(.*?)/si";
		if($user_signature){
//			$user_signature = preg_replace("/\[img\](.*?)\[\/img\]/si", "\\1", $user_signature);
//			$user_signature = "[ ".$aj -> tpa($user_signature)." ]"; // uncomment if you want images removed from signatures
			
			$user_signature = $aj -> tpa($user_signature);
		}
		$replace[5] = $user_signature;

		$search[6] = "/\{JOINED\}(.*?)/si";
		if($user_admin){ 
			$replace[6] = ""; 
		}else{
			$replace[6] = ($user_join != "01 Jan : 00:00" && $user_join != "31 Dec : 19:00" ? LAN_145.$user_join."<br />" : "");
		}
		
		$search[7] = "/\{LOCATION\}(.*?)/si";
		$replace[7] = ($user_location ? LAN_313.": ".$aj -> tpa($user_location) : "");

		$search[8] = "/\{LEVEL\}(.*?)/si";
		define("IMAGE_rank_main_admin_image", ($pref['rank_main_admin_image'] && file_exists(e_IMAGE."forum/".$pref['rank_main_admin_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/main_admin.png' alt='' />"));
		define("IMAGE_rank_moderator_image", ($pref['rank_moderator_image'] && file_exists(e_IMAGE."forum/".$pref['rank_moderator_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
		define("IMAGE_rank_admin_image", ($pref['rank_admin_image'] && file_exists(e_IMAGE."forum/".$pref['rank_admin_image']) ? "<img src='".e_IMAGE."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
		$ldata = get_level($user_id, $user_forums, $user_comments, $user_chats, $user_visits, $user_join, $user_admin, $user_perms, $pref);
		$replace[8] = ($user_admin ? $ldata[0] : $ldata[1]);


		$text = preg_replace($search, $replace, $COMMENTSTYLE);
		return stripslashes($text);
	}
	
	function enter_comment($author_name, $comment, $table, $id){
		global $sql, $aj;
		if(!is_object($aj)){ $aj = new textparse; }
		$fp = new floodprotect;
		if($fp -> flood("comments", "comment_datestamp") == FALSE){
			header("location:".e_BASE."index.php");
			exit;
		}

		switch($table){
			case "news": $type=0; break;
			case "content" : $type=1; break;
			case "download" : $type=2; break;
			case "faq" : $type=3; break;
			case "poll" : $type=4; break;
			case "docs" : $type=5; break;
			case "bugtrack" : $type=6; break;
			/****************************************
			Add your comment type here in same format as above, ie ...
			case "your_comment_type"; $type = your_type_id; break;
			****************************************/
		}
		
		$comment = $aj -> formtpa($comment, "public");
		if(!$sql -> db_Select("comments", "*", "comment_comment='".$comment."' AND comment_item_id='$id' AND comment_type='$type' ")){
			if($_POST['comment']){
				if(USER == TRUE){
					$nick = USERID.".".USERNAME;
					$sql -> db_Update("user", "user_comments=user_comments+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
				}else if($_POST['author_name'] == ""){
					$nick = "0.Anonymous";
				}else{
					$sql2 = new db;
					if($sql2 -> db_Select("user", "*", "user_name='".$_POST['author_name']."' ")){
						$ip = getip();
						if($sql2 -> db_Select("user", "*", "user_name='".$_POST['author_name']."' AND user_ip='$ip' ")){
							list($cuser_id, $cuser_name) = $sql2-> db_Fetch();
							$nick = $cuser_id.".".$cuser_name;
						}else{
							define("emessage", LAN_310);
						}
					}else{
						$nick = "0.".$aj -> formtpa($author_name, "public");
					}
				}
				if(!defined("emessage")){
					if(!$sql -> db_Insert("comments", "0, '$id', '$nick', '', '".time()."', '$comment', '0', '$ip', '$type' ")){
						echo LAN_11;
					}else{
						$sql -> db_Delete("cache", "cache_url='news.php' ");
					}
				}
			}
		}else{
			define("emessage", LAN_312);
		}
	}
}