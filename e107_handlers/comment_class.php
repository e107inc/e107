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
|     $Revision: 1.60 $
|     $Date: 2006-06-02 15:53:14 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_comment.php");
@include_once(e_LANGUAGEDIR."English/lan_comment.php");
require_once(e_FILE."shortcode/batch/comment_shortcodes.php");

/**
 * Enter description here...
 *
 */
class comment {

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $action
	 * @param unknown_type $table
	 * @param unknown_type $id
	 * @param unknown_type $subject
	 * @param unknown_type $content_type
	 * @param unknown_type $return
	 * @param unknown_type $rating
	 * @return unknown
	 */
	function form_comment($action, $table, $id, $subject, $content_type, $return=FALSE, $rating=FALSE, $tablerender=TRUE)
	{
		//rating	: boolean, to show rating system in comment
		global $pref, $sql, $tp;
		if(isset($pref['comments_disabled']) && $pref['comments_disabled'] == TRUE){
        	return;
		}

		require_once(e_HANDLER."ren_help.php");
		if (ANON == TRUE || USER == TRUE)
		{
			$itemid = $id;
			$ns = new e107table;
			if ($action == "reply" && substr($subject, 0, 4) != "Re: ")
			{
				$subject = COMLAN_325.' '.$subject;
			}

			$text = "\n<div style='text-align:center'><form method='post' action='".e_SELF."?".e_QUERY."' id='dataform' >\n<table style='width:100%'>";

			if ($pref['nested_comments'])
			{
				$text .= "<tr>\n<td style='width:20%'>".COMLAN_324."</td>\n<td style='width:80%'>\n<input class='tbox' type='text' name='subject' size='61' value='".$tp -> toForm($subject)."' maxlength='100' />\n</td>\n</tr>";
				$text2 = "";
			}
			else
			{
				$text2 = "<input type='hidden' name='subject' value='".$tp -> toForm($subject)."'  />\n";
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
					echo "<div style='text-align: center;'>".COMLAN_329."</div>";
					require_once(FOOTERF);
					exit;
				}

				$caption = COMLAN_318;
				$comval = $tp -> toFORM($ecom['comment_comment']);
				$comval = preg_replace("#\[ ".COMLAN_319.".*\]#si", "", $comval);
			}
			else
			{
				$caption = COMLAN_9;
				$comval = "";
			}

			//add the rating select box/result ?
			$rate = "";
			if($rating == TRUE && !(ANON == TRUE && USER == FALSE) )
			{
				global $rater;
				require_once(e_HANDLER."rate_class.php");
				if(!is_object($rater)){ $rater = new rater; }
				$rate = $rater -> composerating($table, $itemid, $enter=TRUE, USERID, TRUE);
				$rate = "<tr><td style='width:20%; vertical-align:top;'>".COMLAN_327.":</td>\n<td style='width:80%;'>".$rate."</td></tr>\n";
			}
			//end rating area

			if (ANON == TRUE && USER == FALSE)
			{
				$text .= "<tr>\n<td style='width:20%; vertical-align:top;'>".COMLAN_16."</td>\n<td style='width:80%'>\n<input class='tbox' type='text' name='author_name' size='61' value='$author_name' maxlength='100' />\n</td>\n</tr>";
			}
			$text .= $rate."<tr> \n
			<td style='width:20%; vertical-align:top;'>".COMLAN_8.":</td>\n<td id='commentform' style='width:80%;'>\n<textarea class='tbox' name='comment' cols='62' rows='7' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$comval</textarea>\n<br />
			<input class='helpbox' type='text' name='helpb' style='width:80%' /><br />".ren_help(1, 'addtext', 'help')."</td></tr>\n<tr style='vertical-align:top'> \n<td style='width:20%'>".$text2."</td>\n<td id='commentformbutton' style='width:80%;'>\n". (isset($action) && $action == "reply" ? "<input type='hidden' name='pid' value='$id' />" : '').(isset($eaction) && $eaction == "edit" ? "<input type='hidden' name='editpid' value='$id' />" : "").(isset($content_type) && $content_type ? "<input type='hidden' name='content_type' value='$content_type' />" : ''). "<input class='button' type='submit' name='".$action."submit' value='".(isset($eaction) && $eaction == "edit" ? COMLAN_320 : COMLAN_9)."' />\n</td>\n</tr>\n</table>\n</form></div>";

			if($tablerender)
			{
				$text = $ns->tablerender($caption, $text, '', TRUE);
			}

			if($return)
			{
				return $text;
			}
			else
			{
				echo $text;
			}
		}
		else
		{
			echo "<br /><div style='text-align:center'><b>".COMLAN_6." <a href='".e_SIGNUP."'>".COMLAN_321."</a> ".COMLAN_322."</b></div>";
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $row
	 * @param unknown_type $table
	 * @param unknown_type $action
	 * @param unknown_type $id
	 * @param unknown_type $width
	 * @param unknown_type $subject
	 * @param unknown_type $addrating
	 * @return unknown
	 */
	function render_comment($row, $table, $action, $id, $width, $subject, $addrating=FALSE) {
		//addrating	: boolean, to show rating system in rendered comment
		global $sql, $sc_style, $comment_shortcodes, $COMMENTSTYLE, $rater, $gen;
		global $pref, $comrow, $tp, $NEWIMAGE, $USERNAME, $RATING, $datestamp;
		global $thisaction, $thistable, $thisid;

		if(isset($pref['comments_disabled']) && $pref['comments_disabled'] == TRUE){
        	return;
		}


		$comrow				= $row;
		$thistable			= $table;
		$thisid				= $id;
		$thisaction			= $action;

		if($addrating===TRUE){
			require_once(e_HANDLER."rate_class.php");
			if(!$rater || !is_object($rater)){ $rater = new rater; }
		}

		require_once(e_HANDLER."level_handler.php");
		if (!$width) {
			$width = 0;
		}
		if(!defined("IMAGE_nonew_comments")){
			define("IMAGE_nonew_comments", (file_exists(THEME."images/nonew_comments.png") ? "<img src='".THEME_ABS."images/nonew_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/nonew_comments.png' alt=''  />"));
		}
		if(!defined("IMAGE_new_comments")){
			define("IMAGE_new_comments", (file_exists(THEME."images/new_comments.png") ? "<img src='".THEME_ABS."images/new_comments.png' alt=''  /> " : "<img src='".e_IMAGE_ABS."generic/".IMODE."/new_comments.png' alt=''  /> "));
		}
		$ns			= new e107table;
		if(!$gen || !is_object($gen)){ $gen = new convert; }
		$url		= e_PAGE."?".e_QUERY;
		$unblock	= "[<a href='".e_ADMIN_ABS."comment.php?unblock-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".COMLAN_1."</a>] ";
		$block		= "[<a href='".e_ADMIN_ABS."comment.php?block-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".COMLAN_2."</a>] ";
		$delete		= "[<a href='".e_ADMIN_ABS."comment.php?delete-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".COMLAN_3."</a>] ";
		$userinfo	= "[<a href='".e_ADMIN_ABS."userinfo.php?".$comrow['comment_ip']."'>".COMLAN_4."</a>]";

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
			define("IMAGE_rank_main_admin_image", (isset($pref['rank_main_admin_image']) && $pref['rank_main_admin_image'] && file_exists(THEME."forum/".$pref['rank_main_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_main_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/main_admin.png' alt='' />"));
		}
		if(!defined("IMAGE_rank_moderator_image")){
			define("IMAGE_rank_moderator_image", (isset($pref['rank_moderator_image']) && $pref['rank_moderator_image'] && file_exists(THEME."forum/".$pref['rank_moderator_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_moderator_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/admin.png' alt='' />"));
		}
		if(!defined("IMAGE_rank_admin_image")){
			define("IMAGE_rank_admin_image", (isset($pref['rank_admin_image']) && $pref['rank_admin_image'] && file_exists(THEME."forum/".$pref['rank_admin_image']) ? "<img src='".THEME_ABS."forum/".$pref['rank_admin_image']."' alt='' />" : "<img src='".e_PLUGIN_ABS."forum/images/".IMODE."/admin.png' alt='' />"));
		}

		$RATING = ($addrating==TRUE && $comrow['user_id'] ? $rater->composerating($thistable, $thisid, FALSE, $comrow['user_id']) : "");

		$text = $tp -> parseTemplate($renderstyle, TRUE, $comment_shortcodes);

		if ($action == "comment" && $pref['nested_comments']) {

			$type = $this -> getCommentType($thistable);
			$sub_query = "
			SELECT c.*, u.*, ue.*
			FROM #comments AS c
			LEFT JOIN #user AS u ON c.comment_author = u.user_id
			LEFT JOIN #user_extended AS ue ON c.comment_author = ue.user_extended_id
			WHERE comment_item_id='".intval($thisid)."' AND comment_type='".$tp -> toDB($type, true)."' AND comment_pid='".intval($comrow['comment_id'])."'
			ORDER BY comment_datestamp
			";

			$sql2 = new db;	/* a new db must be created here, for nested comment  */
			if ($sub_total = $sql2->db_Select_gen($sub_query)) {
				while ($row1 = $sql2->db_Fetch()) {
					if ($pref['nested_comments']) {
						$width = $width + 3;
						if ($width > 80) {
							$width = 80;
						}
					}
					$text .= $this->render_comment($row1, $table, $action, $id, $width, $subject, $addrating);
					unset($width);
				}
			}
		}
		//echo $text;
		return stripslashes($text);
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $author_name
	 * @param unknown_type $comment
	 * @param unknown_type $table
	 * @param unknown_type $id
	 * @param unknown_type $pid
	 * @param unknown_type $subject
	 * @param unknown_type $rateindex
	 */
	function enter_comment($author_name, $comment, $table, $id, $pid, $subject, $rateindex=FALSE) {
		//rateindex	: the posted value from the rateselect box (without the urljump) (see function rateselect())
		global $sql, $sql2, $tp, $e107cache, $e_event, $e107, $pref, $rater;

		if(isset($pref['comments_disabled']) && $pref['comments_disabled'] == TRUE){
        	return;
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
					$editpid = $tmp[($count+1)];
					break;
				}
				$count++;
			}
		}

		$type = $this -> getCommentType($table);

		$comment = $tp->toDB($comment);
		$subject = $tp->toDB($subject);
		if (!$sql->db_Select("comments", "*", "comment_comment='".$comment."' AND comment_item_id='".intval($id)."' AND comment_type='".$tp -> toDB($type, true)."' ")) {
			if ($_POST['comment']) {
				if (USER == TRUE) {
					$nick = USERID.".".USERNAME;
				} else if($_POST['author_name'] == '') {
					$nick = "0.Anonymous";
				} else {
					if ($sql2->db_Select("user", "*", "user_name='".$tp -> toDB($_POST['author_name'])."' ")) {
						if ($sql2->db_Select("user", "*", "user_name='".$tp -> toDB($_POST['author_name'])."' AND user_ip='".$tp -> toDB($ip, true)."' ")) {
							list($cuser_id, $cuser_name) = $sql2->db_Fetch();
							$nick = $cuser_id.".".$cuser_name;
						} else {
							define("emessage", COMLAN_310);
						}
					} else {
						$nick = "0.".$tp->toDB($author_name);
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
						$comment .= "\n[ ".COMLAN_319." [time=short]".time()."[/time] ]";
						$sql -> db_Update("comments", "comment_comment='$comment' WHERE comment_id='".intval($editpid)."' ");
						$e107cache->clear("comment");
						return;
					}

					if (!$sql->db_Insert("comments", "0, '".intval($pid)."', '".intval($id)."', '$subject', '$nick', '', '".$_t."', '$comment', '0', '$ip', '".$tp -> toDB($type, true)."', '0' "))
					{
						echo "<b>".COMLAN_323."</b> ".COMLAN_11;
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
							$sql->db_Update("news", "news_comment_total=news_comment_total+1 WHERE news_id=".intval($id));
						}
					}
				}
			}
		}
		else
		{
			define("emessage", COMLAN_312);
		}
		//if rateindex is posted, enter the rating from this user
		if($rateindex){
			$rater -> enterrating($rateindex);
		}

		if(defined("emessage"))
		{
			message_handler("ALERT", emessage);
		}
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $table
	 * @return unknown
	 */
	function getCommentType($table){
			switch($table) {
				case "news"		: $type = 0; break;
				case "content"	: $type = 1; break;
				case "download"	: $type = 2; break;
				case "faq"		: $type = 3; break;
				case "poll"		: $type = 4; break;
				case "docs"		: $type = 5; break;
				case "bugtrack"	: $type = 6; break;
				/****************************************
				Add your comment type here in same format as above, ie ...
				case "your_comment_type"; $type = your_type_id; break;
				****************************************/
			}
			if (!isset($type)) {
				$type = $table;
			}
			return $type;
	}

	/**
	 * Enter description here...
	 *
	 * @param unknown_type $table
	 * @param unknown_type $id
	 * @return unknown
	 */
	function count_comments($table, $id){
		global $sql, $tp;

		if(is_numeric($table)){
			$type = $table;
		}else{
			$type = $this -> getCommentType($table);
		}
		$count_comments = $sql -> db_Count("comments", "(*)", "WHERE comment_item_id='".intval($id)."' AND comment_type='".$tp -> toDB($type, true)."' ORDER BY comment_item_id");
		return $count_comments;
	}


	/**
	 * Enter description here...
	 *
	 * @param unknown_type $table
	 * @param unknown_type $action
	 * @param unknown_type $id
	 * @param unknown_type $width
	 * @param unknown_type $subject
	 * @param unknown_type $rate
	 */
	function compose_comment($table, $action, $id, $width, $subject, $rate=FALSE, $return=FALSE, $tablerender=TRUE){
		//compose comment	: single call function will render the existing comments and show the form_comment
		//rate				: boolean, to show/hide rating system in comment, default FALSE
		global $pref, $sql, $ns, $e107cache, $tp, $totcc;

		if(isset($pref['comments_disabled']) && $pref['comments_disabled'] == TRUE){
        	return;
		}

		$count_comments = $this -> count_comments($table, $id, $pid=FALSE);

		$type = $this -> getCommentType($table);

		$query = $pref['nested_comments'] ?
		"SELECT c.*, u.*, ue.* FROM #comments AS c
		LEFT JOIN #user AS u ON c.comment_author = u.user_id
		LEFT JOIN #user_extended AS ue ON c.comment_author = ue.user_extended_id
		WHERE c.comment_item_id='".intval($id)."' AND c.comment_type='".$tp -> toDB($type, true)."' AND c.comment_pid='0' ORDER BY c.comment_datestamp"
		:
		"SELECT c.*, u.*, ue.* FROM #comments AS c
		LEFT JOIN #user AS u ON c.comment_author = u.user_id
		LEFT JOIN #user_extended AS ue ON c.comment_author = ue.user_extended_id
		WHERE c.comment_item_id='".intval($id)."' AND c.comment_type='".$tp -> toDB($type, true)."' ORDER BY c.comment_datestamp";

		$text = "";

		if ($comment_total = $sql->db_Select_gen($query))
		{
			$width = 0;
			while ($row = $sql->db_Fetch())
			{
				$lock = $row['comment_lock'];
				// $subject = $tp->toHTML($subject);
				if ($pref['nested_comments'])
				{
					$text .= $this->render_comment($row, $table , $action, $id, $width, $tp->toHTML($subject), $rate);
				}
				else
				{
					$text .= $this->render_comment($row, $table , $action, $id, $width, $tp->toHTML($subject), $rate);
				}
			}

			if ($tablerender)
			{
				$text = $ns->tablerender(COMLAN_99, $text, '', TRUE);
			}

			if (!$return)
			{
				echo $text;
            }
            else
            {
				$ret['comment'] = $text;
			}

			if (ADMIN && getperms("B"))
			{
				$modcomment =  "<div style='text-align:right'><a href='".e_ADMIN_ABS."modcomment.php?$table.$id'>".COMLAN_314."</a></div><br />";
			}
		}

		if ($lock != "1")
		{
		   	$comment =	$this->form_comment($action, $table, $id, $subject, "", TRUE, $rate, $tablerender);
		}
		else
		{
			$comment = "<br /><div style='text-align:center'><b>".COMLAN_328."</b></div>";
		}

		if (!$return)
		{
          	echo $modcomment.$comment;
		}

		$ret['comment'] .= $modcomment;
		$ret['comment_form'] = $comment;
		$ret['caption'] = COMLAN_99;

		return (!$return) ? "" : $ret;
	}
	
	function delete_comments($table, $id)
	{
		global $sql, $tp;

		if(is_numeric($table))
		{
			$type = $table;
		}
		else
		{
			$type = $this -> getCommentType($table);
		}
		$type = $tp -> toDB($type, true);
		$id = intval($id);
		$num_deleted = $sql -> db_Delete("comments", "comment_item_id='{$id}' AND comment_type='{$type}'");
		return $num_deleted;
	}
	
}

?>