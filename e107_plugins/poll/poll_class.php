<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/poll/poll_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN."poll/languages/".e_LANGUAGE.".php");
define("POLLCLASS", TRUE);
define("POLL_MODE_COOKIE", 0);
define("POLL_MODE_IP", 1);
define("POLL_MODE_USERID", 2);

class poll
{

	var $pollRow;
	var $pollmode;

	function delete_poll($existing)
	{
		global $sql, $admin_log;
		if ($sql -> db_Delete("polls", " poll_id='".intval($existing)."' "))
		{
			if(function_exists("admin_purge_related"))
			{
				admin_purge_related("poll", $existing);
			}
			$admin_log->log_event('POLL_01',POLL_ADLAN08.': '.$existing,'');
			return POLL_ADLAN08;
		}
	}

	function submit_poll($mode=1)
	{

		/*
		$mode = 1 :: poll is main poll
		$mode = 2 :: poll is forum poll
		*/

		global $tp, $sql, $admin_log;

		$poll_title = $tp->toDB($_POST['poll_title']);
		$poll_comment= $tp -> toDB($_POST['poll_comment']);
		$multipleChoice = intval($_POST['multipleChoice']);
		$showResults = intval($_POST['showResults']);
		$pollUserclass =intval($_POST['pollUserclass']);
		$storageMethod = intval($_POST['storageMethod']);
		$active_start = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$active_end = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$poll_options = "";

		$_POST['poll_option'] = array_filter($_POST['poll_option']);
		foreach($_POST['poll_option'] as $key => $value)
		{
			$poll_options .= $tp->toDB($value).chr(1);
		}

		if(POLLACTION == "edit" || vartrue($_POST['poll_id']))
		{
		  $sql -> db_Update("polls", "poll_title='{$poll_title}', 
									  poll_options='{$poll_options}', 
									  poll_comment='{$poll_comment}', 
									  poll_type={$mode},
									  poll_allow_multiple={$multipleChoice}, 
									  poll_result_type={$showResults}, 
									  poll_vote_userclass={$pollUserclass}, 
									  poll_storage_method={$storageMethod}
										WHERE poll_id=".intval(POLLID));

			/* update poll results - bugtracker #1124 .... */
			$sql -> db_Select("polls", "poll_votes", "poll_id='".intval(POLLID)."' ");
			$foo = $sql -> db_Fetch();
			$voteA = explode(chr(1), $foo['poll_votes']);

			$opt = count($poll_option) - count($voteA);

			if($opt)
			{
				for($a=0; $a<=$opt; $a++)
				{
					$foo['poll_votes'] .= "0".chr(1);
				}
				$sql -> db_Update("polls", "poll_votes='".$foo['poll_votes']."' WHERE poll_id='".intval(POLLID)."' ");
			}

			$admin_log->log_event('POLL_02','ID: '.POLLID.' - '.$poll_title,'');
			$message = POLLAN_45;
		} 
		else 
		{
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
						$sql -> db_Update("polls", "poll_end_datestamp='".time()."', poll_vote_userclass='255' WHERE poll_id=".$deacpoll['poll_id']);
					}
				}
				$ret = $sql -> db_Insert("polls", "'0', ".time().", ".intval($active_start).", ".intval($active_end).", ".ADMINID.", '{$poll_title}', '{$poll_options}', '{$votes}', '', '1', '".$tp -> toDB($poll_comment)."', '".intval($multipleChoice)."', '".intval($showResults)."', '".intval($pollUserclass)."', '".intval($storageMethod)."'");
				$admin_log->log_event('POLL_03','ID: '.$ret.' - '.$poll_title,'');		// Intentionally only log admin-entered polls
			}
			else
			{
				$sql -> db_Insert("polls", "'0', ".intval($_POST['iid']).", '0', '0', ".USERID.", '$poll_title', '$poll_options', '$votes', '', '2', '0', '".intval($multipleChoice)."', '0', '0', '".intval($storageMethod)."'");
			}
		}
		return $message;
	}


	function get_poll($query)
	{
		global $sql, $e107;
		if ($sql->db_Select_gen($query))
		{
			$pollArray = $sql -> db_Fetch();
			if (!check_class($pollArray['poll_vote_userclass']))
			{
				$POLLMODE = "disallowed";
			}
			else
			{
				switch($pollArray['poll_storage_method'])
				{
					case POLL_MODE_COOKIE:
						$userid = "";
						$cookiename = "poll_".$pollArray['poll_id'];
						if(isset($_COOKIE[$cookiename]))
						{
							$POLLMODE = "voted";
						}
						else
						{
							$POLLMODE = "notvoted";
						}
					break;

					case POLL_MODE_IP:
						$userid = $e107->getip();
						$voted_ids = explode("^", substr($pollArray['poll_ip'], 0, -1));
						if (in_array($userid, $voted_ids))
						{
							$POLLMODE = "voted";
						}
						else
						{
							$POLLMODE = "notvoted";
						}
					break;

					case POLL_MODE_USERID:
						if(!USER)
						{
							$POLLMODE = "disallowed";
						}
						else
						{
							$userid = USERID;
							$voted_ids = explode("^", substr($pollArray['poll_ip'], 0, -1));
							if (in_array($userid, $voted_ids))
							{
								$POLLMODE = "voted";
							}
							else
							{
								$POLLMODE = "notvoted";
							}
						}
					break;
				}
			}
		}
		else
		{
			return FALSE;
		}
		if(isset($_POST['pollvote']) && $POLLMODE == "notvoted" && ($POLLMODE != "disallowed"))
		{
				if ($_POST['votea'])
				{
//					$sql -> db_Select("polls", "*", "poll_vote_userclass!=255 AND poll_type=1 ORDER BY poll_datestamp DESC LIMIT 0,1");
					$row = $pollArray;
					extract($row);
					$votes = explode(chr(1), $poll_votes);
					if(is_array($_POST['votea']))
					{
						/* multiple choice vote */
						foreach($_POST['votea'] as $vote)
						{
							$vote = intval($vote);
							$votes[($vote-1)] ++;
						}
					}
					else
					{
						$votes[($_POST['votea']-1)] ++;
					}
					$optionArray = explode(chr(1), $pollArray['poll_options']);
					$optionArray = array_slice($optionArray, 0, -1);
					foreach($optionArray as $k=>$v)
					{
						if(!$votes[$k])
						{
							$votes[$k] = 0;
						}
					}
					$votep = implode(chr(1), $votes);
					$pollArray['poll_votes'] = $votep;

					$sql->db_Update("polls", "poll_votes = '$votep', poll_ip='".$poll_ip.$userid."^' WHERE poll_id=".$poll_id);
					echo "
				<script type='text/javascript'>
				<!--
				setcook({$poll_id});
				//-->
				</script>
				";
					$POLLMODE = "voted";

			}
		}
		$this->pollRow = $pollArray;
		$this->pollmode = $POLLMODE;
	}


	function render_poll($pollArray = "", $type = "menu", $POLLMODE = "", $returnMethod=FALSE)
	{
		global $POLLSTYLE, $sql, $tp, $ns;
		switch ($POLLMODE)
		{
		  case "query" :	// Show poll, register any vote
			if ($this->get_poll($pollArray) === FALSE)
			{
				return '';		// No display if no poll
			}
			$pollArray = $this->pollRow;
			$POLLMODE = $this->pollmode;
			break;
		  case 'results' :
			if ($sql->db_Select_gen($pollArray))
			{
			  $pollArray = $sql -> db_Fetch();
			}
			break;
		}

		$barl = (file_exists(THEME."images/barl.png") ? THEME_ABS."images/barl.png" : e_PLUGIN_ABS."poll/images/barl.png");
		$barr = (file_exists(THEME."images/barr.png") ? THEME_ABS."images/barr.png" : e_PLUGIN_ABS."poll/images/barr.png");
		$bar = (file_exists(THEME."images/bar.png") ? THEME_ABS."images/bar.png" : e_PLUGIN_ABS."poll/images/bar.png");

		if($type == "preview")
		{
			$optionArray = $pollArray['poll_option'];
			$voteArray = array();
			$voteArray = array_pad($voteArray, count($optionArray), 0);
			$pollArray['poll_allow_multiple'] = $pollArray['multipleChoice'];
		}
		else if($type == "forum")
		{
			if(isset($_POST['fpreview']))
			{
				$pollArray['poll_allow_multiple'] = $pollArray['multipleChoice'];
				$optionArray = $pollArray['poll_option'];
			}
			else
			{
				$optionArray = explode(chr(1), $pollArray['poll_options']);
				$optionArray = array_slice($optionArray, 0, -1);
			}
			$voteArray = explode(chr(1), $pollArray['poll_votes']);
//			$voteArray = array_slice($voteArray, 0, -1);
		}
		else
		{  // Get existing results
			$optionArray = explode(chr(1), $pollArray['poll_options']);
			$optionArray = array_slice($optionArray, 0, -1);
			$voteArray = explode(chr(1), $pollArray['poll_votes']);
//			$voteArray = array_slice($voteArray, 0, -1);
		}

		$voteTotal = array_sum($voteArray);

		$percentage = array();


		if(count($voteArray))
		{
		  foreach($voteArray as $votes)
		  {
		    if ($voteTotal > 0)
			{
			  $percentage[] = round(($votes/$voteTotal) * 100, 2);
			}
			else
			{
			  $percentage[] = 0;
			}
		  }
		}
		/* get template */
		if (file_exists(THEME."poll_template.php"))
		{
			require(THEME."poll_template.php");
		}
		else if(!isset($POLL_NOTVOTED_START))
		{
		   	require(e_PLUGIN."poll/templates/poll_template.php");
		}

		$preview = FALSE;
		if ($type == "preview")
		{
			$POLLMODE = "notvoted";
		}
		elseif($type == "forum") 
		{
			$preview = TRUE;
		}

		$comment_total = 0;
		if ($pollArray['poll_comment'])
		{	// Only get comments if they're allowed on poll. And we only need the count ATM
			$comment_total = $sql->db_Count("comments", "(*)", "WHERE `comment_item_id`='".intval($pollArray['poll_id'])."' AND `comment_type`=4");
		}

		$QUESTION = $tp -> toHTML($pollArray['poll_title'], TRUE,"emotes_off, defs");
		$VOTE_TOTAL = POLLAN_31.": ".$voteTotal;
		$COMMENTS = ($pollArray['poll_comment'] ? " <a href='".e_BASE."comment.php?comment.poll.".$pollArray['poll_id']."'>".POLLAN_27.": ".$comment_total."</a>" : "");
		$OLDPOLLS = ($type == "menu" ? "<a href='".e_PLUGIN_ABS."poll/oldpolls.php'>".POLLAN_28."</a>" : "");
		$AUTHOR = POLLAN_35." ".($type == "preview" || $type == "forum" ? USERNAME : "<a href='".e_BASE."user.php?id.".$pollArray['poll_admin_id']."'>".$pollArray['user_name']."</a>");

		switch ($POLLMODE)
		{
			case "notvoted":
				$text = "<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>\n".preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_START : $POLL_NOTVOTED_START));
				$count = 1;
				$alt = 0; // alternate style.
			foreach($optionArray as $option) 
			{
				//	$MODE = ($mode) ? $mode : "";		/* debug */
					$OPTIONBUTTON = ($pollArray['poll_allow_multiple'] ? "<input type='checkbox' name='votea[]' value='$count' />" : "<input type='radio' name='votea' value='$count' />");
					$OPTION = $tp->toHTML($option, TRUE);
			  if(isset($POLL_NOTVOTED_LOOP_ALT) && $POLL_NOTVOTED_LOOP_ALT && $type != "forum")
			  { // alternating style
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($alt == 0 ? $POLL_NOTVOTED_LOOP : $POLL_NOTVOTED_LOOP_ALT));
						$alt = ($alt ==0) ? 1 : 0;
			  }
			  else
			  {
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_LOOP : $POLL_NOTVOTED_LOOP));
					}
					$count ++;
				}
				$SUBMITBUTTON = "<input class='button' type='submit' name='pollvote' value='".POLLAN_30."' />";
				if(('preview' == $type || $preview == TRUE) && strpos(e_SELF, "viewtopic") === FALSE)
				{
					$SUBMITBUTTON = "[".POLLAN_30."]";
				}

				$text .= "\n".preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_END : $POLL_NOTVOTED_END))."\n</form>";
			break;

			case "voted":
		  case 'results' :
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
						$OPTION = $tp->toHTML($option, TRUE);
						$BAR = ($percentage[$count] ? "<div style='width: 100%'><div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div><div style='background-image: url($bar); width: ".(floor($percentage[$count]) != 100 ? floor($percentage[$count]) : 90)."%; height: 14px; float: left;'></div><div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div></div>" : "");
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
					$OPTION = $tp->toHTML($option, TRUE);
					$text .= preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_LOOP);
					$count ++;
				}
				if($pollArray['poll_vote_userclass'] == 253)
				{
					$DISALLOWMESSAGE = POLLAN_41;
				}
			elseif($pollArray['poll_vote_userclass'] == 254)
				{
					$DISALLOWMESSAGE = POLLAN_42;
				}
				else
				{
					$DISALLOWMESSAGE = POLLAN_43;
				}
				$text .= preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_END);
			break;
		}

		if(!defined("POLLRENDERED")) define("POLLRENDERED", TRUE);
		$caption = (file_exists(THEME."images/poll_menu.png") ? "<img src='".THEME_ABS."images/poll_menu.png' alt='' /> ".POLLAN_MENU_CAPTION : POLLAN_MENU_CAPTION);
		if($type == "preview")
		{
			$caption = POLLAN_23;
			$text = "<div style='text-align:center; margin-left: auto; margin-right: auto;'>\n<table style='width:350px' class='fborder'>\n<tr>\n<td>\n$text\n</td></tr></table></div>";
		}
		else if($type == "forum")
		{
			$caption = LAN_4;
		}



		if($returnMethod)
		{
			return $text;
		}
		else
		{
			$ns->tablerender($caption, $text, 'poll');
		}
	}


	function renderPollForm($mode="admin")
	{
		/*
		$mode = "admin" :: called from admin_config.php
		$mode = "forum" :: called from forum_post.php
		*/
		$tp = e107::getParser();

		//TODO Hardcoded FORUM code needs to be moved somewhere. 

		if($mode == "forum")
		{
			$text = "<tr>
			<td colspan='2' class='nforumcaption2'>".LAN_4."</td>
			</tr>
			<tr>
			<td colspan='2'>
			<span class='smalltext'>".LAN_386."</span>
			</td>
			</tr>
			<tr><td style='width:20%'><div class='normaltext'>".LAN_5."</div></td><td style='width:80%'class='forumheader3'><input class='tbox' type='text' name='poll_title' size='70' value='".$tp->post_toForm($_POST['poll_title'])."' maxlength='200' /></td></tr>";

			$option_count = (count($_POST['poll_option']) ? count($_POST['poll_option']) : 1);
			$text .= "<tr>
			<td style='width:20%'>".LAN_391."</td>
			<td style='width:80%'>
			<div id='pollsection'>";

			for($count = 1; $count <= $option_count; $count++)
			{
				if($count != 1 && $_POST['poll_option'][($count-1)] =="")
				{
					break;
				}
				$opt = ($count==1) ? "id='pollopt'" : "";
				$text .="<span $opt><input  class='tbox' type='text' name='poll_option[]' size='40' value=\"".$_POST['poll_option'][($count-1)]."\" maxlength='200' />";
				$text .= "</span><br />";
			}

			$text .="</div><input class='button' type='button' name='addoption' value='".LAN_6."' onclick=\"duplicateHTML('pollopt','pollsection')\" /><br />
			</td></tr>


			<tr>
			<td style='width:20%'>".POLL_506."</td>
			<td style='width:80%'>
			<input type='radio' name='multipleChoice' value='1'".($_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLL_507."&nbsp;&nbsp;
			<input type='radio' name='multipleChoice' value='0'".(!$_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLL_508."
			</td>
			</tr>

			<tr>
			<td style='width:30%'>".POLLAN_16."</td>
			<td>
			<input type='radio' name='storageMethod' value='0'".(!$_POST['storageMethod'] ? " checked='checked'" : "")." /> ".POLLAN_17."<br />
			<input type='radio' name='storageMethod' value='1'".($_POST['storageMethod'] == 1 ? " checked='checked'" : "")." /> ".POLLAN_18."<br />
			<input type='radio' name='storageMethod' value='2'".($_POST['storageMethod'] ==2 ? " checked='checked'" : "")." /> ".POLLAN_19."
			</td></tr>
			";


			return $text;
		}

		$formgo = e_SELF.(e_QUERY && !defined("RESET") && strpos(e_QUERY, 'delete') === FALSE ? "?".e_QUERY : "");

		$text = "<div style='text-align:center'>
		<form method='post' action='$formgo'>
		<table class='adminform' cellpadding='0' cellspacing='0'>
        <colgroup span='2'>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tr>
		<td style='width:30%'><div class='normaltext'>".POLLAN_3.":</div></td>
		<td style='width:70%'>
		<input class='tbox' type='text' name='poll_title' size='70' value='".$tp -> post_toForm(varset($_POST['poll_title']))."' maxlength='200' />";

		$option_count = (varset($_POST['poll_option']) && count($_POST['poll_option']) ? count($_POST['poll_option']) : 2);

		$text .= "</td></tr><tr>
		<td style='width:30%;vertical-align:top'>".LAN_OPTIONS." :</td>
		<td style='width:70%'>
		<div id='pollsection'>";

		for($count = 1; $count <= $option_count; $count++)
		{
			$opt = ($count==1) ? "id='pollopt'" : "";
			$text .="<span $opt><input  class='tbox' type='text' name='poll_option[]' size='40' value=\"".$tp -> post_toForm($_POST['poll_option'][($count-1)])."\" maxlength='200' />";
			$text .= "</span><br />";
		}

		$text .="</div><input class='button' type='button' name='addoption' value='".POLLAN_8."' onclick=\"duplicateHTML('pollopt','pollsection')\" /><br />
		</td></tr>

		<tr>
		<td style='width:30%'>".POLLAN_9."</td>
		<td style='width:70%'>
		<input type='radio' name='multipleChoice' value='1'".(varset($_POST['multipleChoice']) ? " checked='checked'" : "")." /> ".POLLAN_10."&nbsp;&nbsp;
		<input type='radio' name='multipleChoice' value='0'".(!varset($_POST['multipleChoice']) ? " checked='checked'" : "")." /> ".POLLAN_11."
		</td>
		</tr>

		<tr>
		<td style='width:30%'>".POLLAN_12."</td>
		<td style='width:70%'>
		<input type='radio' name='showResults' value='0'".(!varset($_POST['showResults']) ? " checked='checked'" : "")." /> ".POLLAN_13."<br />
		<input type='radio' name='showResults' value='1'".(varset($_POST['showResults']) ? " checked='checked'" : "")." /> ".POLLAN_14."
		</td>
		</tr>

		<tr>
		<td style='width:30%'>".POLLAN_15."</td>";
		
		$uclass = (ADMIN) ? "" : "public,member,admin,classes,matchclass";
		
		
		$text .= "
		<td>".r_userclass("pollUserclass", $_POST['pollUserclass'], 'off', $uclass)."</td>
		</tr>

		<tr>
		<td style='width:30%'>".POLLAN_16."</td>
		<td>
		<input type='radio' name='storageMethod' value='0'".(!varset($_POST['storageMethod']) ? " checked='checked'" : "")." /> ".POLLAN_17."<br />
		<input type='radio' name='storageMethod' value='1'".(varset($_POST['storageMethod']) ==1 ? " checked='checked'" : "")." /> ".POLLAN_18."<br />
		<input type='radio' name='storageMethod' value='2'".(varset($_POST['storageMethod']) ==2 ? " checked='checked'" : "")." /> ".POLLAN_19."
		</td></tr>




		<tr>
		<td>".POLLAN_20.": </td><td>
		<input type='radio' name='poll_comment' value='1'".(varset($_POST['poll_comment']) ? " checked='checked'" : "")." /> ".POLLAN_10."
		<input type='radio' name='poll_comment' value='0'".(!varset($_POST['poll_comment']) ? " checked='checked'" : "")." /> ".POLLAN_11."
		</td>
		</tr>
		</table>
		<div class='buttons-bar center'>";

		if (isset($_POST['preview']) || varset($_POST['edit']))
		{
			$text .= "<input class='button' type='submit' name='preview' value='".POLLAN_24."' /> ";
			
			if (POLLACTION == "edit")
			{
				$text .= "<input class='button' type='submit' name='submit' value='".POLLAN_22."' />
				<input type='hidden' name='poll_id' value='".intval($_POST['poll_id'])."' /> ";
			}
			else
			{
				$text .= "<input class='button' type='submit' name='submit' value='".POLLAN_23."' /> ";
			}
		} else {
			$text .= "<input class='button' type='submit' name='preview' value='".POLLAN_24."' /> ";
		}
		
		if (defset('POLLID')) {
			$text .= "<input class='button' type='submit' name='reset' value='".POLLAN_25."' /> ";
		}

		$text .= "</div>
		</form>
		</div>";

		return $text;
	}
}

echo '<script type="text/javascript">
<!--
function setcook(pollid){
	var name = "poll_"+pollid;
	var date = new Date();
	var value = pollid;
	date.setTime(date.getTime()+(365*24*60*60*1000));
	var expires = "; expires="+date.toGMTString();
	document.cookie = name+"="+value+expires+"; path=/";
}
//-->
</script>
';

?>