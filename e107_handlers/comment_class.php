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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/comment_class.php,v $
|     $Revision: 1.32 $
|     $Date: 2005-06-23 15:44:16 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_comment.php");
@include_once(e_LANGUAGEDIR."English/lan_comment.php");
require_once(e_FILE."shortcode/batch/comment_shortcodes.php");
class comment {
	function form_comment($action, $table, $id, $subject, $content_type, $return=FALSE, $rating=FALSE) {
		//rating	: boolean, to show rating system in comment
		global $pref, $sql, $tp;
		require_once(e_HANDLER."ren_help.php");
		if (ANON == TRUE || USER == TRUE) {
			$itemid = $id;
			$ns = new e107table;
			if ($action == "reply" && substr($subject, 0, 4) != "Re: ") {
				$subject = COMLAN_5.' '.$subject;
			}
			$text = "\n<div style='text-align:center'><form method='post' action='".e_SELF."?".e_QUERY."' id='dataform' >\n<table style='width:100%'>";
			if ($pref['nested_comments']) {
				$text .= "<tr>\n<td style='width:20%'>".COMLAN_4."</td>\n<td style='width:80%'>\n<input class='tbox' type='text' name='subject' size='66' value='$subject' maxlength='100' />\n</td>\n</tr>";
				$text2 = "";
			} else {
				$text2 = "<input type='hidden' name='subject' value='$subject'  />\n";
			}

			if(strstr(e_QUERY, "edit"))
			{
				$eaction = "edit";
				$tmp = explode(".", e_QUERY);
				$count = 0;
				foreach($tmp as $t)
				{
					if($t == "edit")
					{
						$id = $tmp[($count+1)];
						break;
					}
					$count++;
				}
			}

			if(isset($eaction) && $eaction == "edit")
			{
				$id=intval($id);
				$sql -> db_Select("comments", "*", "comment_id='$id' ");
				$ecom = $sql -> db_Fetch();
				list($prid, $pname) = explode(".", $ecom['comment_author']);

				if($prid != USERID || !USER)
				{
					echo "<div style='text-align: center;'>Unauthorized</div>";
					require_once(FOOTERF);
					exit;
				}
				$caption = LAN_318;
				$comval = $tp -> toFORM($ecom['comment_comment']);
				$comval = preg_replace("#\[ ".LAN_319.".*\]#si", "", $comval);
			}
			else
			{
				$caption = LAN_9;
				$comval = "";
			}

			//add the rating select box/result ?
			if($rating == TRUE){
				global $rater;
				require_once(e_HANDLER."rate_class.php");
				if(!is_object($rater)){ $rater = new rater; }
				$rate = $rater -> composerating($table, $itemid, $enter=TRUE, USERID, TRUE);
				$rate = "<tr><td style='width:20%; vertical-align:top;'>Rating:</td>\n<td style='width:80%;'>".$rate."</td></tr>\n";
			}
			//end rating area

			if (ANON == TRUE && USER == FALSE) {
				$text .= "<tr>\n<td style='width:20%; vertical-align:top;'>".LAN_16."</td>\n<td style='width:80%'>\n<input class='tbox' type='text' name='author_name' size='60' value='$author_name' maxlength='100' />\n</td>\n</tr>";
			}
			$text .= $rate."<tr> \n
			<td style='width:20%; vertical-align:top;'>".LAN_8.":</td>\n<td id='commentform' style='width:80%;'>\n<textarea style='width:80%' class='tbox' name='comment' cols='1' rows='7' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$comval</textarea>\n<br />
			<input class='helpbox' type='text' name='helpb' style='width:80%' /><br />".ren_help(1, 'addtext', 'help')."</td></tr>\n<tr style='vertical-align:top'> \n<td style='width:20%'>".$text2."</td>\n<td id='commentformbutton' style='width:80%;'>\n". (isset($action) && $action == "reply" ? "<input type='hidden' name='pid' value='$id' />" : '').(isset($eaction) && $eaction == "edit" ? "<input type='hidden' name='editpid' value='$id' />" : "").(isset($content_type) && $content_type ? "<input type='hidden' name='content_type' value='$content_type' />" : ''). "<input class='button' type='submit' name='".$action."submit' value='".(isset($eaction) && $eaction == "edit" ? LAN_320 : LAN_9)."' />\n</td>\n</tr>\n</table>\n</form></div>";
			if($return)
			{
				return $text;
			}
			else
			{
				$ns->tablerender($caption, $text);
			}
			} else {
			echo "<br /><div style='text-align:center'><b>".LAN_6." <a href='".e_SIGNUP."'>".COMLAN_1."</a> ".COMLAN_2."</b></div>";
		}
	}
	function render_comment($row, $table, $action, $id, $width, $subject, $addrating=FALSE) {
		//rating	: boolean, to show rating system in rendered comment
		global $sc_style, $comment_shortcodes, $COMMENTSTYLE, $rater, $gen;
		global $pref, $comrow, $tp, $NEWIMAGE, $USERNAME, $RATING, $datestamp;
		global $thisaction, $thistable, $thisid, $row2;
		
		$comrow		= $row;
		$thistable	= $table;
		$thisid		= $id;
		$thisaction	= $action;
		
		if($addrating===TRUE){
			global $rater;
			require_once(e_HANDLER."rate_class.php");
			if(!is_object($rater)){ $rater = new rater; }
		}

		require_once(e_HANDLER."level_handler.php");
		if (!$width) {
			$width = 0;
		}
		if(!defined("IMAGE_nonew_comments")){
			define("IMAGE_nonew_comments", (file_exists(THEME."generic/nonew_comments.png") ? "<img src='".THEME."generic/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/nonew_comments.png' alt=''  />"));
		}
		if(!defined("IMAGE_new_comments")){
			define("IMAGE_new_comments", (file_exists(THEME."generic/new_comments.png") ? "<img src='".THEME."generic/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE."generic/".IMODE."/new_comments.png' alt=''  /> "));
		}
		$sql		= new db;
		$ns			= new e107table;
		$gen		= new convert;

		$url		= e_PAGE."?".e_QUERY;
		$unblock	= "[<a href='".e_BASE.e_ADMIN."comment.php?unblock-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".LAN_1."</a>] ";
		$block		= "[<a href='".e_BASE.e_ADMIN."comment.php?block-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".LAN_2."</a>] ";
		$delete		= "[<a href='".e_BASE.e_ADMIN."comment.php?delete-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".LAN_3."</a>] ";
		$userinfo	= "[<a href='".e_BASE.e_ADMIN."userinfo.php?".$comrow['comment_ip']."'>".LAN_4."</a>]";
		
		if (!$COMMENTSTYLE) {
			global $THEMES_DIRECTORY;
			$COMMENTSTYLE = "";
			if (file_exists(THEME."comment_template.php")) {
				require_once(THEME."comment_template.php");
			} else {
				require_once(e_BASE.$THEMES_DIRECTORY."templates/comment_template.php");
			}
		}
		if ($pref['nested_comments']) {
			$width2 = 100 - $width;
			$total_width = (isset($pref['standards_mode']) && $pref['standards_mode'] ? "98%" : "95%");
			if($width){
				$renderstyle = "
				<table style='width:".$total_width."' border='0'>
				<tr>
				<td style='width:".$width."%' ></td>
				<td style='width:".$width2."%'>" .$COMMENTSTYLE. "
				</td>
				</tr>
				</table>";
			}else{
				$renderstyle = $COMMENTSTYLE;
			}
			if($pref['comments_icon']) {
				if ($comrow['comment_datestamp'] > USERLV ) {
					$NEWIMAGE = IMAGE_new_comments;
				} else {
					$NEWIMAGE = IMAGE_nonew_comments;
				}
			} else {
				$NEWIMAGE = "";
			}
		} else {
			$renderstyle = $COMMENTSTYLE;
		}

		$highlight_search = FALSE;
		if (isset($_POST['highlight_search'])) {
			$highlight_search = TRUE;
		}

		if(!defined("IMAGE_rank_main_admin_image")){
			define("IMAGE_rank_main_admin_image", (isset($pref['rank_main_admin_image']) && $pref['rank_main_admin_image'] && file_exists(THEME."forum/".$pref['rank_main_admin_image']) ? "<img src='".THEME."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/main_admin.png' alt='' />"));
		}
		if(!defined("IMAGE_rank_moderator_image")){
			define("IMAGE_rank_moderator_image", (isset($pref['rank_moderator_image']) && $pref['rank_moderator_image'] && file_exists(THEME."forum/".$pref['rank_moderator_image']) ? "<img src='".THEME."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
		}
		if(!defined("IMAGE_rank_admin_image")){
			define("IMAGE_rank_admin_image", (isset($pref['rank_admin_image']) && $pref['rank_admin_image'] && file_exists(THEME."forum/".$pref['rank_admin_image']) ? "<img src='".THEME."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_IMAGE."forum/admin.png' alt='' />"));
		}

		$RATING = ($addrating==TRUE && $comrow['user_id'] ? $rater->composerating($thistable, $thisid, FALSE, $comrow['user_id']) : "");
		$text = $tp -> parseTemplate($renderstyle, FALSE, $comment_shortcodes);

		if ($action == "comment" && $pref['nested_comments']) {
			$sub_query = "
			SELECT #comments.*, user_id, user_name, user_admin, user_image, user_signature, user_join, user_comments, user_location, user_forums, user_chats, user_visits, user_perms 
			FROM #comments 
			LEFT JOIN #user ON #comments.comment_author = #user.user_id 
			WHERE comment_item_id='".$thisid."' AND comment_type='".$thistable."' AND comment_pid='".$comrow['comment_id']."' 
			ORDER BY comment_datestamp
			";
			$sql2 = new db;
			if ($sub_total = $sql2->db_Select_gen($sub_query)) {
				while ($row2 = $sql2->db_Fetch()) {
					if ($pref['nested_comments']) {
						$width = $width + 3;
						if ($width > 80) {
							$width = 80;
						}
					}
					$text .= $this->render_comment($row2, $table, $action, $id, $width, $subject, $addrating);
					unset($width);
				}
				$total ++;
			}
		}
		return stripslashes($text);
	}
	function enter_comment($author_name, $comment, $table, $id, $pid, $subject, $rateindex=FALSE) {
		//rateindex	: the posted value from the rateselect box (without the urljump) (see function rateselect())
		global $sql, $tp, $e107cache, $e_event, $e107, $pref, $rater;


		if(strstr(e_QUERY, "edit"))
		{
			$eaction = "edit";
			$tmp = explode(".", e_QUERY);
			$count = 0;
			foreach($tmp as $t)
			{
				if($t == "edit")
				{
					$editpid = $tmp[($count+1)];
					break;
				}
				$count++;
			}
		}

		switch($table) {
			case "news":
			$type = 0;
			break;
			case "content" :
			$type = 1;
			break;
			case "download" :
			$type = 2;
			break;
			case "faq" :
			$type = 3;
			break;
			case "poll" :
			$type = 4;
			break;
			case "docs" :
			$type = 5;
			break;
			case "bugtrack" :
			$type = 6;
			break;
			/****************************************
			Add your comment type here in same format as above, ie ...
			case "your_comment_type"; $type = your_type_id; break;
			****************************************/
		}
		if (!Isset($type)) {
			$type = $table;
		}
		$comment = $tp->toDB($comment, "public");
		$subject = $tp->toDB($subject, "public");
		if (!$sql->db_Select("comments", "*", "comment_comment='".$comment."' AND comment_item_id='$id' AND comment_type='$type' ")) {
			if ($_POST['comment']) {
				if (USER == TRUE) {
					$nick = USERID.".".USERNAME;
				} else if($_POST['author_name'] == '') {
					$nick = "0.Anonymous";
				} else {
					$sql2 = new db;
					if ($sql2->db_Select("user", "*", "user_name='".$_POST['author_name']."' ")) {
						if ($sql2->db_Select("user", "*", "user_name='".$_POST['author_name']."' AND user_ip='$ip' ")) {
							list($cuser_id, $cuser_name) = $sql2->db_Fetch();
							$nick = $cuser_id.".".$cuser_name;
						} else {
							define("emessage", LAN_310);
						}
					} else {
						$nick = "0.".$tp->toDB($author_name, "public");
					}
				}
				
				if (!defined("emessage"))
				{
					$ip = $e107->getip();
					require_once(e_HANDLER."encrypt_handler.php");
					$ip = encode_ip($ip);
					$_t = time();

					if($editpid)
					{
						$comment .= "\n[ ".LAN_319." [time=short]".time()."[/time] ]";
						$sql -> db_Update("comments", "comment_comment='$comment' WHERE comment_id='$editpid' ");
						$e107cache->clear("comment");
						return;
					}

					if (!$sql->db_Insert("comments", "0, '$pid', '$id', '$subject', '$nick', '', '".$_t."', '$comment', '0', '$ip', '$type' "))
					{
						echo "<b>".COMLAN_3."</b> ".LAN_11;
					}
					else
					{
						if (USER == TRUE) {
							$sql -> db_Update("user", "user_comments=user_comments+1, user_lastpost='".time()."' WHERE user_id='".USERID."' ");
						}
						$edata_li = array("comment_type" => $type, "comment_subject" => $subject, "comment_item_id" => $id, "comment_nick" => $nick, "comment_time" => $_t, "comment_comment" => $comment);
						$e_event->trigger("postcomment", $edata_li);
						$e107cache->clear("comment");
						if(!$type || $type == "news")
						{
							$sql->db_Update("news", "news_comment_total=news_comment_total+1 WHERE news_id=$id");
						}
					}
				}
			}
		}
		else
		{
			define("emessage", LAN_312);
		}
		//if rateindex is posted, enter the rating from this user
		if($rateindex){
			$rater -> enterrating($rateindex);
		}
	}
}

?>
