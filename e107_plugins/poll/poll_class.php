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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/poll/poll_class.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-03-20 22:19:36 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
@include(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
@include(e_PLUGIN."poll/languages/English.php");
define("POLLCLASS", TRUE);
class poll
{
	 
	function delete_poll($existing)
	{
		global $sql;
		if ($sql -> db_Delete("polls", " poll_id='".$existing."' "))
		{
			return "Poll deleted.";
		}
	}
	 
	function submit_poll($mode=1)
	{
		
		/*
		$mode = 1 :: poll is main poll
		$mode = 2 :: poll is forum poll
		*/

		global $tp, $sql;
		extract($_POST);

		$poll_title = $tp -> toDB($poll_title);
		$active_start = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$active_end = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$poll_options = "";
		foreach($poll_option as $key => $value)
		{
			$poll_options .= $tp -> toDB($poll_option[$key]).chr(1);
		}

		if(POLLACTION == "edit")
		{
			$sql -> db_Update("polls", "poll_title='$poll_title', poll_options='$poll_options', poll_type=$mode, poll_comment='$poll_comment', poll_allow_multiple=$multipleChoice, poll_result_type=$showResults, poll_vote_userclass=$pollUserclass, poll_storage_method=$storageMethod WHERE poll_id=".POLLID);
			$message = POLLAN_27;
		} else {

			$votes = "";
			for($a=1; $a<=count($_POST['poll_option']); $a++)
			{
				$votes .= "0".chr(1);
			}

			if($mode == 1)
			{
				/* deactivate other polls */
				if($sql -> db_Select("polls", "*", "poll_type=1 AND poll_vote_userclass!=255"))
				{
					$deacArray = $sql -> db_getList();
					foreach($deacArray as $deacpoll)
					{
						$sql -> db_Update("polls", "poll_end_datestamp=".time().", poll_vote_userclass=255 WHERE poll_id=".$deacpoll['poll_id']);
					}
				}
				$sql -> db_Insert("polls", "'0', ".time().", $active_start, $active_end, ".ADMINID.", '$poll_title', '$poll_options', '$votes', '', 1, $poll_comment, $multipleChoice, $showResults, $pollUserclass, $storageMethod");
			}
			else
			{
				$sql -> db_Insert("polls", "'0', ".$_POST['iid'].", 0, 0, ".USERID.", '$poll_title', '$poll_options', '$votes', '', 2, 0, $multipleChoice, 0, 0, 0");
			}
		}
		return $message;
	}
	 
	function render_poll($pollArray, $type = "menu", $POLLMODE, $returnMethod=FALSE)
	{
		global $POLLSTYLE, $sql, $tp, $ns;

		$barl = (file_exists(THEME."images/barl.png") ? THEME."images/barl.png" : e_IMAGE."generic/barl.png");
		$barr = (file_exists(THEME."images/barr.png") ? THEME."images/barr.png" : e_IMAGE."generic/barr.png");
		$bar = (file_exists(THEME."images/bar.png") ? THEME."images/bar.png" : e_IMAGE."generic/bar.png");

		if($type == "preview")
		{
			/* load lan file */
			@include_once(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
			@include_once(e_PLUGIN."poll/languages/English.php");
			$optionArray = $pollArray['poll_option'];
			$voteArray = array();
			$voteArray = array_pad($voteArray, count($optionArray), 0);
			$pollArray['poll_allow_multiple'] = $pollArray['multipleChoice'];
		}
		else if($type == "forum")
		{
			@include_once(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
			@include_once(e_PLUGIN."poll/languages/English.php");
			$optionArray = explode(chr(1), $pollArray['poll_options']);
			$optionArray = array_slice($optionArray, 0, -1);      
			$voteArray = explode(chr(1), $pollArray['poll_votes']);
			$voteArray = array_slice($voteArray, 0, -1);
		}
		else
		{
			$optionArray = explode(chr(1), $pollArray['poll_options']);
			$optionArray = array_slice($optionArray, 0, -1);      
			$voteArray = explode(chr(1), $pollArray['poll_votes']);
			$voteArray = array_slice($voteArray, 0, -1);
		}

		$voteTotal = array_sum($voteArray);

		$percentage = array();
		foreach($voteArray as $votes)
		{
			$percentage[] = round(($votes/$voteTotal) * 100, 2);
		}
 
		/* get template */
		if (file_exists(THEME."poll_template.php"))
		{
			require(THEME."poll_template.php");
		}
		else if(!$POLL_NOTVOTED_START)
		{
			require(e_PLUGIN."poll/templates/poll_template.php");
		}
		 
		if ($type == "preview")
		{
			$POLLMODE = "notvoted";
		}
		else if($type == "forum") {
			$preview = TRUE;
		}

		$comment_total = $sql->db_Select("comments", "*", "comment_item_id='".$pollArray['poll_id']."' AND comment_type=4");

		$QUESTION = $tp -> toHTML($pollArray['poll_title']);
		$VOTE_TOTAL = POLLAN_26.": ".$voteTotal;
		$COMMENTS = ($pollArray['poll_comment'] ? " <a href='".e_BASE."comment.php?comment.poll.".$pollArray['poll_id']."'>".POLLAN_27.": ".$comment_total."</a>" : "");
		$OLDPOLLS = ($type == "menu" ? "<a href='".e_PLUGIN."poll/oldpolls.php'>".POLLAN_28."</a>" : "");
		$AUTHOR = POLLAN_29." ".($type == "preview" || $type == "forum" ? USERNAME : "<a href='".e_BASE."user.php?id.".$pollArray['poll_admin_id']."'>".$pollArray['user_name']."</a>");

		switch ($POLLMODE)
		{
			case "notvoted":
				$text = "<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>\n<p>\n".preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_START : $POLL_NOTVOTED_START));
				$count = 1;
				foreach($optionArray as $option)
				{
					$MODE = $mode;		/* debug */
					$OPTIONBUTTON = ($pollArray['poll_allow_multiple'] ? "<input type='checkbox' name='votea[]' value='$count' />" : "<input type='radio' name='votea' value='$count' />");
					$OPTION = $option;
					$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_LOOP : $POLL_NOTVOTED_LOOP));
					$count ++;
				}
				$SUBMITBUTTON = "<input class='button' type='submit' name='pollvote' value='".POLLAN_30."' onclick='setcook(\"".$pollArray['poll_id']."\");' />";
				
				$text .= "</p>\n".preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_END : $POLL_NOTVOTED_END))."</p>\n</form>";
			break;

			case "voted":

				if($pollArray['poll_result_type'] && !strstr(e_SELF, "comment.php"))
				{
					$text = "<div style='text-align: center;'><br /><br />".POLLAN_39."<br /><br /><a href='".e_BASE."comment.php?comment.poll.".$pollArray['poll_id']."'>".POLLAN_40."</a></div><br /><br />";
					
				}
				else
				{


					$text = preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_VOTED_START : $POLL_VOTED_START));
					$count = 0;
					foreach($optionArray as $option)
					{
						$MODE = $mode;		/* debug */
						$OPTION = $option;

						$BAR = ($percentage[$count] ? "<div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div><div style='background-image: url($bar); width: ".$percentage[$count]."%; height: 14px; float: left;'></div><div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div>" : "");

						$PERCENTAGE = $percentage[$count]."%";
						$VOTES = POLLAN_31.": ".$voteArray[$count];
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_VOTED_LOOP : $POLL_VOTED_LOOP));
						$count ++;
					}
				}
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_VOTED_END : $POLL_VOTED_END));
				
			break;

			case "disallowed":
				$text = preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_START);
				foreach($optionArray as $option)
				{
					$MODE = $mode;		/* debug */
					$OPTION = $option;
					$text .= preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_LOOP);
					$count ++;
				}
				if($pollArray['poll_vote_userclass'] == 253)
				{
					$DISALLOWMESSAGE = POLL_502.POLL_503;
				}
				else if($pollArray['poll_vote_userclass'] == 254)
				{
					$DISALLOWMESSAGE = POLL_502.POLL_504;
				}
				else
				{
					$DISALLOWMESSAGE = POLL_501;
				}
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_END);
			break;
		}

		define("POLLRENDERED", TRUE);
		$caption = (file_exists(THEME."images/poll_menu.png") ? "<img src='".THEME."images/poll_menu.png' alt='' /> ".POLLAN_MENU_CAPTION : POLLAN_MENU_CAPTION);
		if($type == "preview")
		{
			$caption = POLLAN_23;
			$text = "<div style='text-align:center; margin-left: auto; margin-right: auto;'>\n<table style='width:350px' class='fborder'>\n<tr>\n<td>\n$text\n</td></tr></table></div>";
		}
		else if($type == "forum")
		{
			$caption = POLL_505;
		}

		

		if($returnMethod)
		{
			return $text;
		}
		else
		{
			$ns->tablerender($caption, $text);
		}
	}


	function renderPollForm($mode="admin")
	{
		/*
		$mode = "admin" :: called from admin_config.php
		$mode = "forum" :: called from forum_post.php
		*/

		if($mode == "forum")
		{
			global $tp;
			$text = "<tr>
			<td colspan='2' class='nforumcaption2'>".LAN_4."</td>
			</tr>
			<tr>
			<td colspan='2' class='forumheader3'>
			<span class='smalltext'>".LAN_386."
			</td>
			</tr>
			<tr><td style='width:20%' class='forumheader3'><div class='normaltext'>".LAN_5."</div></td><td style='width:80%'class='forumheader3'><input class='tbox' type='text' name='poll_title' size='70' value='".$tp->toDB($_POST['poll_title'])."' maxlength='200' /></td></tr>";

			$option_count = (count($_POST['poll_option']) ? count($_POST['poll_option']) : 1);
			$text .= "<tr>
			<td style='width:20%' class='forumheader3'>".LAN_391."</td>
			<td style='width:80%' class='forumheader3'>
			<div id='pollsection'>";

			for($count = 1; $count <= $option_count; $count++) {
				if($count != 1 && $_POST['poll_option'][($count-1)] ==""){
					break;
				}
				$opt = ($count==1) ? "id='pollopt'" : "";
				$text .="<span $opt><input  class='tbox' type='text' name='poll_option[]' size='40' value=\"".$_POST['poll_option'][($count-1)]."\" maxlength='200' />";
				$text .= "</span><br />";
			}

			$text .="</div><input class='button' type='button' name='addoption' value='".LAN_6."' onclick=\"duplicateHTML('pollopt','pollsection')\" /><br />
			</td></tr>


			<tr>
			<td style='width:20%' class='forumheader3'>".POLL_506."</td>
			<td style='width:80%' class='forumheader3'>
			<input type='radio' name='multipleChoice' value='1'".($_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLL_507."&nbsp;&nbsp;
			<input type='radio' name='multipleChoice' value='0'".(!$_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLL_508."
			</td>
			</tr>";

			
			return $text;
		}

		$formgo = e_SELF.(e_QUERY && !defined("RESET") ? "?".e_QUERY : ""); 

		$text = "<div style='text-align:center'>
		<form method='post' action='$formgo'>
		<table style='".ADMIN_WIDTH."' class='fborder'>
		<tr>
		<td style='width:30%' class='forumheader3'><div class='normaltext'>".POLLAN_3.":</div></td>
		<td style='width:70%'class='forumheader3'>
		<input class='tbox' type='text' name='poll_title' size='70' value='".$_POST['poll_title']."' maxlength='200' />";

		$option_count = (count($_POST['poll_option']) ? count($_POST['poll_option']) : 1);

		$text .= "<tr>
		<td style='width:30%;vertical-align:top' class='forumheader3'>".LAN_OPTIONS." :</td>
		<td style='width:70%' class='forumheader3'>
		<div id='pollsection'>";

		for($count = 1; $count <= $option_count; $count++) {
			if($count != 1 && $_POST['poll_option'][($count-1)] ==""){
				break;
			}
			$opt = ($count==1) ? "id='pollopt'" : "";
			$text .="<span $opt><input  class='tbox' type='text' name='poll_option[]' size='40' value=\"".$_POST['poll_option'][($count-1)]."\" maxlength='200' />";
			$text .= "</span><br />";
		}

		$text .="</div><input class='button' type='button' name='addoption' value='".POLLAN_8."' onclick=\"duplicateHTML('pollopt','pollsection')\" /><br />
		</td></tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".POLLAN_9."</td>
		<td style='width:70%' class='forumheader3'>
		<input type='radio' name='multipleChoice' value='1'".($_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLLAN_10."&nbsp;&nbsp;
		<input type='radio' name='multipleChoice' value='0'".(!$_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLLAN_11."
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".POLLAN_12."</td>
		<td style='width:70%' class='forumheader3'>
		<input type='radio' name='showResults' value='0'".(!$_POST['showResults'] ? " checked='checked'" : "")." /> ".POLLAN_13."<br />
		<input type='radio' name='showResults' value='1'".($_POST['showResults'] ? " checked='checked'" : "")." /> ".POLLAN_14."
		</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".POLLAN_15."</td>
		<td class='forumheader3'>".r_userclass("pollUserclass", $_POST['pollUserclass'], "admin")."</td>
		</tr>

		<tr>
		<td style='width:30%' class='forumheader3'>".POLLAN_16."</td>
		<td class='forumheader3'>
		<input type='radio' name='storageMethod' value='0'".(!$_POST['storageMethod'] ? " checked='checked'" : "")." /> ".POLLAN_17."<br />
		<input type='radio' name='storageMethod' value='1'".($_POST['storageMethod'] == 1 ? " checked='checked'" : "")." /> ".POLLAN_18."<br />
		<input type='radio' name='storageMethod' value='2'".($_POST['storageMethod'] ==2 ? " checked='checked'" : "")." /> ".POLLAN_19."
		</tr>


		

		<tr>
		<td class='forumheader3'>".POLLAN_20.": </td><td class='forumheader3'>
		<input type='radio' name='poll_comment' value='1'".($_POST['poll_comment'] ? " checked='checked'" : "")." /> ".POLLAN_10."
		<input type='radio' name='poll_comment' value='0'".(!$_POST['poll_comment'] ? " checked='checked'" : "")." /> ".POLLAN_11."
		</td>
		</tr>
		<tr style='vertical-align:top'>
		<td colspan='2'  style='text-align:center' class='forumheader'>";

		if (isset($_POST['preview'])) {
			$text .= "<input class='button' type='submit' name='preview' value='".POLLAN_21."' /> ";
			if (POLLACTION == "edit") {
				$text .= "<input class='button' type='submit' name='submit' value='".POLLAN_22."' /> ";
			} else {
				$text .= "<input class='button' type='submit' name='submit' value='".POLLAN_23."' /> ";
			}
		} else {
			$text .= "<input class='button' type='submit' name='preview' value='".POLLAN_24."' /> ";
		}
		if (POLLID) {
			$text .= "<input class='button' type='submit' name='reset' value='".POLLAN_25."' /> ";
		}

		$text .= "</td></tr></table>
		</form>
		</div>";

		return $text;
	}
}

echo '<script type="text/javascript">
<!--
function setcook(pollid){
	cookitem="poll_"+pollid;
	var expireDate = new Date;
	expireDate.setMinutes(expireDate.getMinutes()+100000);
	document.cookie = "" + cookitem + "=" + pollid + "; expires=" + expireDate.toGMTString();
}
//-->
</script>
';

?>