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
class comment{

	function form_comment(){
		if(ANON == TRUE || USER == TRUE){
			$ns = new table;
			$text = "\n<form method='post' action='".e_SELF."?".e_QUERY."'>\n<table style='width:95%'>";
			if(ANON == TRUE && USER == FALSE){
				$text .= "<tr>\n<td style='width:20%'>".LAN_16."</td>\n<td style='width:80%'>\n<input class='tbox' type='text' name='author_name' size='60' value='$author_name' maxlength='100' />\n</td>\n</tr>";
			}
			$text .= "<tr> \n<td style='width:20%'>".LAN_8.":</td>\n<td style='width:80%'>\n<textarea class='tbox' name='comment' cols='70' rows='10'></textarea>\n</td></tr>\n<tr style='vertical-align:top'> \n<td style='width:20%'></td>\n<td style='width:80%'>\n<input class='button' type='submit' name='commentsubmit' value='".LAN_9."' />\n<br /><br />\n<span class='smalltext'>\n".LAN_10."\n</span>\n</td>\n</tr>\n</table>\n</form>";
			$ns -> tablerender(LAN_9, $text);
		}else{
			echo "<br /><div style='text-align:center'><b>".LAN_6."</b></div>";
		}
	}

	function render_comment($row){
		global $COMMENTSTYLE;
		$sql = new db;
		$ns = new table;
		extract($row);
		$comment_author = eregi_replace("[0-9]+\.", "", $comment_author);
		$gen = new convert; $datestamp = $gen->convert_date($comment_datestamp, "short");
		if($sql -> db_Select("user", "*", "user_name='$comment_author'")){
			$row = $sql -> db_Fetch();
			extract($row);
			if($user_image != ""){
				if(ereg("avatar_", $user_image)){
					$avatarlist[0] = "";
					$handle=opendir("themes/shared/avatars/");
					while ($file = readdir($handle)){
						if($file != "." && $file != ".."){
							$avatarlist[] = $file;
						}
					}
					closedir($handle);
					$user_image = "themes/shared/avatars/".$avatarlist[substr(strrchr($user_image, "_"), 1)];
				}
			}
		}else{
			$user_id = 0;
			$user_name = $comment_author;
		}
			
		$user_join = $gen->convert_date($user_join, "short");

		$unblock = "[<a href='".e_BASE.e_ADMIN."comment_conf.php?unblock-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."'>".LAN_1."</a>]";
		$block = "[<a href='".e_BASE.e_ADMIN."comment_conf.php?block-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."'>".LAN_2."</a>] ";
		$delete = "[<a href='".e_BASE.e_ADMIN."comment_conf.php?delete-".$comment_id."-".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']."'>".LAN_3."</a>] ";
		$userinfo = "[<a href='".e_BASE.e_ADMIN."userinfo.php?".$comment_ip."'>".LAN_4."</a>]";
	
		if(!$COMMENTSTYLE){
			$COMMENTSTYLE = "<table style='width:95%'>
			<tr>
			<td style='width:30%; vertical-align=top'>
			<img src='".THEME."images/bullet2.gif' alt='bullet' /> 
			<span class='defaulttext'><i>
			{USERNAME}
			</i></span>
			<br />
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
			<td style='text-align:right' class='smalltext'>
			{ADMINOPTIONS}
			</td></tr></table><br />";
		}

		$aj = new textparse;

		$search[0] = "/\{USERNAME\}(.*?)/si";
		$replace[0] = ($user_id ? "<a href='user.php?id.".$user_id."'>".$user_name."</a>\n" : $user_name."\n");

		$search[1] = "/\{TIMEDATE\}(.*?)/si";
		$replace[1] = $datestamp;

		$search[2] = "/\{AVATAR\}(.*?)/si";
		$replace[2] = ($user_image ? "<img src='".$user_image."' alt='' />" : "");
		
		$search[3] = "/\{COMMENTS\}(.*?)/si";
		$replace[3] = ($user_id ? LAN_99.": ".$user_comments : LAN_194);

		$search[4] = "/\{COMMENT\}(.*?)/si";
		$replace[4] = ($comment_blocked ? LAN_0 : $aj -> tpa($comment_comment));

		$search[5] = "/\{SIGNATURE\}(.*?)/si";
		if($user_signature){
			$user_signature = preg_replace("/\[img\](.*?)\[\/img\]/si", "\\1", $user_signature);
			$user_signature = "[ ".$aj -> tpa($user_signature)." ]";
		}
		$replace[5] = $user_signature;

		$search[6] = "/\{JOINED\}(.*?)/si";
		if($user_admin && $user_perms == "0"){ 
			$replace[6] = "Main site administrator"; 
		}else if($user_admin){
			$replace[6] = "Administrator";
		}else{
			$replace[6] = ($user_join != "01 Jan : 00:00" ? LAN_145.$user_join : "");
		}
		$search[7] = "/\{ADMINOPTIONS\}(.*?)/si";
		if(ADMIN == TRUE && getperms("B")){
			if($comment_blocked == 1){
				$tmp = $unblock;
			}else{
				$tmp = $block;
			}
			$tmp .= $delete.$userinfo;
		}
		$replace[8] = $tmp;

		return preg_replace($search, $replace, $COMMENTSTYLE);
	}
	
	function enter_comment($author_name, $comment, $table, $id){
		$fp = new floodprotect;
		if($fp -> flood("comments", "comment_datestamp") == FALSE){
			header("location:index.php");
			die();
		}
		$aj = new textparse;
		$comment = $aj -> tp($_POST['comment'], "off");
		$sql = new db;

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


		if(!$sql -> db_Select("comments", "*", "comment_comment='".$comment."' AND comment_item_id='$id' AND comment_type='$type' ")){
			if($_POST['comment'] != ""){
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
							//$nick = "0.Anonymous";
							define("emessage", LAN_310);
						}
					}else{
						$nick = "0.".$author_name;
					}
				}
				if(!defined("emessage")){
					if(!$sql -> db_Insert("comments", "0, '$id', '$nick', '', '".time()."', '$comment', '0', '$ip', '$type' ")){
						echo  "<b>Error!</b> Was unable to enter your comment into the database - please retype leaving out any non-standard characters.";
					}
				}
			}
		}else{
			define("emessage", LAN_312);
		}
	}
}
?>