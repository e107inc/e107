<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

include_lan(e_PLUGIN.'poll/languages/'.e_LANGUAGE.'.php');
define('POLLCLASS', TRUE);
define('POLL_MODE_COOKIE', 0);
define('POLL_MODE_IP', 1);
define('POLL_MODE_USERID', 2);

class poll
{
	var $pollRow;
	var $pollmode;
	var $barl = null;
	var $barr = null;
	var $bar = null;


	function __construct()
	{
		$this->barl = (file_exists(THEME.'images/barl.png') ? THEME_ABS.'images/barl.png' : e_PLUGIN_ABS.'poll/images/barl.png');
		$this->barr = (file_exists(THEME.'images/barr.png') ? THEME_ABS.'images/barr.png' : e_PLUGIN_ABS.'poll/images/barr.png');
		$this->bar = (file_exists(THEME.'images/bar.png') ? THEME_ABS.'images/bar.png' : e_PLUGIN_ABS.'poll/images/bar.png');	
	}


	/*
	function remove_poll_cookies
	Remove unused poll cookies. See: http://krijnhoetmer.nl/stuff/javascript/maximum-cookies/ Thanks Fanat1k - bugtracker #4983
	no parameters
	*/	
	function remove_poll_cookies()
	{ 
		$arr_polls_cookies = array();
		foreach($_COOKIE as $cookie_name => $cookie_val)
		{	// Collect poll cookies
			list($str, $int) = explode('_', $cookie_name);
			if (($str == 'poll') && is_numeric($int)) 
			{	// Yes, its poll's cookie
				$arr_polls_cookies[] = $int;
			}
		}
		if (count($arr_polls_cookies) > 1) 
		{	// Remove all except first (assumption: there is always only one active poll)
			rsort($arr_polls_cookies);
			for($i = 1; $i < count($arr_polls_cookies); $i++)
			{
				cookie("poll_{$arr_polls_cookies[$i]}", "", (time() - 2592000));
			}
		}
	}	
	
	/*
	function delete_poll
	parameter in: $existing - existing poll id to be deleted
	parameter out: language text string on succesful delete, nothing on failed deletion
	*/
	function delete_poll($existing)
	{
		global $admin_log;
		$sql = e107::getDb();
		
		if ($sql->delete("polls", " poll_id='".intval($existing)."' "))
		{
			if (function_exists("admin_purge_related"))
			{
				admin_purge_related("poll", $existing);
			}
			e107::getLog()->add('POLL_01',LAN_AL_POLL_01.': '.$existing,'');
			//return POLL_ADLAN08;
		}
	}



	/* 
	function clean_poll_array
	parameter in: original array with poll answers as entered in the forums
	parameter out: cleaned array which trims the poll answers (to avoid 'falsely' empty answers) but allows to have '0' as an option

	Note: Used instead of array_filter because array_filter($array, 'trim') would also ignore the value '0' (as that returns FALSE)
	http://www.bubjavier.com/common-problems-php-arrayfilter-no-callback
	*/

	function clean_poll_array($val) 
	{
 		$val = trim($val); // trims the array to remove poll answers which are (seemingly) empty but which may contain spaces
  		$allowed_vals = array("0"); // Allows for '0' to be a poll answer option. Possible to add more allowed values. 
 		
 		return in_array($val, $allowed_vals, true) ? true : ( $val ? true : false );
	}

	/*
	function submit_poll
	$mode = 1 :: poll is main poll
	$mode = 2 :: poll is forum poll
	returns message
	*/
	function submit_poll($mode=1)
	{
		global $admin_log;
				
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		$poll_title		= $tp->toDB($_POST['poll_title']);
		$poll_comment	= $tp->toDB($_POST['poll_comment']);
		$multipleChoice	= intval($_POST['multipleChoice']);
		$showResults	= intval($_POST['showResults']);
		$pollUserclass	= intval($_POST['pollUserclass']);
		$storageMethod	= intval($_POST['storageMethod']);
		$active_start	= (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
		$active_end		= (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
		$poll_options	= '';

		$_POST['poll_option'] = array_filter($_POST['poll_option'], 'poll::clean_poll_array');
 
		foreach ($_POST['poll_option'] as $key => $value)
		{

			$poll_options .= $tp->toDB($value).chr(1);
		}

		if (POLLACTION == 'edit' || vartrue($_POST['poll_id']))
		{
			$sql->update("polls", "poll_title='{$poll_title}', 
			  				   poll_options='{$poll_options}', 
							   poll_comment='{$poll_comment}', 
							   poll_type={$mode},
							   poll_allow_multiple={$multipleChoice}, 
							   poll_result_type={$showResults}, 
							   poll_vote_userclass={$pollUserclass}, 
							   poll_storage_method={$storageMethod}
							   WHERE poll_id=".intval(POLLID));

			/* update poll results - bugtracker #1124 .... */
			$sql->select("polls", "poll_votes", "poll_id='".intval(POLLID)."' ");
			$foo = $sql->fetch();
			$voteA = explode(chr(1), $foo['poll_votes']);

			$opt = count($poll_option) - count($voteA);

			if ($opt)
			{
				for($a=0; $a<=$opt; $a++)
				{
					$foo['poll_votes'] .= '0'.chr(1);
				}
				$sql->update("polls", "poll_votes='".$foo['poll_votes']."' WHERE poll_id='".intval(POLLID)."' ");
			}

			e107::getLog()->add('POLL_02','ID: '.POLLID.' - '.$poll_title,'');
			//$message = POLLAN_45;
		} 
		else 
		{
			$votes = '';
			for($a=1; $a<=count($_POST['poll_option']); $a++)
			{
				$votes .= '0'.chr(1);
			}

			if ($mode == 1)
			{
				/* deactivate other polls */
				if ($sql->select("polls", "*", "poll_type=1 AND poll_vote_userclass!=255"))
				{
					$deacArray = $sql->db_getList();
					foreach ($deacArray as $deacpoll)
					{
						$sql->update("polls", "poll_end_datestamp='".time()."', poll_vote_userclass='255' WHERE poll_id=".$deacpoll['poll_id']);
					}
				}
				$ret = $sql->insert("polls", "'0', ".time().", ".intval($active_start).", ".intval($active_end).", ".ADMINID.", '{$poll_title}', '{$poll_options}', '{$votes}', '', '1', '".$tp->toDB($poll_comment)."', '".intval($multipleChoice)."', '".intval($showResults)."', '".intval($pollUserclass)."', '".intval($storageMethod)."'");
				e107::getLog()->add('POLL_03','ID: '.$ret.' - '.$poll_title,'');		// Intentionally only log admin-entered polls
			}
			else
			{
				$sql->insert("polls", "'0', ".intval($_POST['iid']).", '0', '0', ".USERID.", '$poll_title', '$poll_options', '$votes', '', '2', '0', '".intval($multipleChoice)."', '0', '0', '".intval($storageMethod)."'");
			}
		}
		return $message;
	}

	function get_poll($query)
	{
		global $e107;		
		$sql = e107::getDb();
		
		if ($sql->gen($query))
		{
			$pollArray = $sql->fetch();
			if (!check_class($pollArray['poll_vote_userclass']))
			{
				$POLLMODE = 'disallowed';
			}
			else
			{
				switch($pollArray['poll_storage_method'])
				{
					case POLL_MODE_COOKIE:
						$userid = '';
						$cookiename = 'poll_'.$pollArray['poll_id'];
						if (isset($_COOKIE[$cookiename]))
						{
							$POLLMODE = 'voted';
						}
						else
						{
							$POLLMODE = 'notvoted';
						}
					break;

					case POLL_MODE_IP:
						$userid = e107::getIPHandler()->getIP(FALSE);
						$voted_ids = explode('^', substr($pollArray['poll_ip'], 0, -1));
						if (in_array($userid, $voted_ids))
						{
							$POLLMODE = 'voted';
						}
						else
						{
							$POLLMODE = 'notvoted';
						}
					break;

					case POLL_MODE_USERID:
						if (!USER)
						{
							$POLLMODE = 'disallowed';
						}
						else
						{
							$userid = USERID;
							$voted_ids = explode('^', substr($pollArray['poll_ip'], 0, -1));
							if (in_array($userid, $voted_ids))
							{
								$POLLMODE = 'voted';
							}
							else
							{
								$POLLMODE = 'notvoted';
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
		if (isset($_POST['pollvote']) && $POLLMODE == 'notvoted' && ($POLLMODE != 'disallowed'))
		{
			if ($_POST['votea'])
			{
//					$sql -> db_Select("polls", "*", "poll_vote_userclass!=255 AND poll_type=1 ORDER BY poll_datestamp DESC LIMIT 0,1");
				$row = $pollArray;
				extract($row);
				$votes = explode(chr(1), $poll_votes);
				if (is_array($_POST['votea']))
				{
					/* multiple choice vote */
					foreach ($_POST['votea'] as $vote)
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
				foreach ($optionArray as $k=>$v)
				{
					if (!$votes[$k])
					{
						$votes[$k] = 0;
					}
				}
				$votep = implode(chr(1), $votes);
				$pollArray['poll_votes'] = $votep;
				$sql->update("polls", "poll_votes = '$votep'".($pollArray['poll_storage_method'] != POLL_MODE_COOKIE ? ", poll_ip='".$poll_ip.$userid."^'" : '')." WHERE poll_id=".$poll_id);
				/*echo "
				<script type='text/javascript'>
				<!--
				setcook({$poll_id});
				//-->
				</script>
				";
				*/
				$poll_cookie_expire = time() + (3600 * 24 * 356 * 15); // FIXME cannot be used after 2023 (this year is the maxium unixstamp on 32 bit system)
			 	cookie('poll_'.$poll_id.'', $poll_id, $poll_cookie_expire);
				$POLLMODE = 'voted';
			}
		}
		$this->pollRow = $pollArray;
		$this->pollmode = $POLLMODE;
	}


	function render_poll($pollArray = "", $type = "menu", $POLLMODE = "", $returnMethod=FALSE)
	{
		$ns = e107::getRender();
		$tp = e107::getParser();
		$sql = e107::getDb();
		
		global $POLLSTYLE;
		
		switch ($POLLMODE)
		{
			case 'query' :	// Show poll, register any vote
				if ($this->get_poll($pollArray) === FALSE)
				{
					return '';		// No display if no poll
				}
				$pollArray = $this->pollRow;
				$POLLMODE = $this->pollmode;
				break;
			case 'results' :
				if ($sql->gen($pollArray))
				{
					$pollArray = $sql->fetch();
				}
				break;
		}

		

		if ($type == 'preview')
		{
			$optionArray = array_filter($pollArray['poll_option']);
			$voteArray = array();
			$voteArray = array_pad($voteArray, count($optionArray), 0);
			$pollArray['poll_allow_multiple'] = $pollArray['multipleChoice'];
		}
		else if ($type == 'forum')
		{
			if (isset($_POST['fpreview']))
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

		if (count($voteArray))
		{
			foreach ($voteArray as $votes)
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
		if (file_exists(THEME.'poll_template.php'))
		{
			require(THEME.'poll_template.php');
		}
		else if (!isset($POLL_NOTVOTED_START))
		{
		   	require(e_PLUGIN.'poll/templates/poll_template.php');
		}
		
	
		
		if(deftrue('BOOTSTRAP'))
		{
	
			if($type == 'forum')
			{
				require_once(e_PLUGIN."forum/templates/forum_poll_template.php");
				
				$POLL_FORUM_NOTVOTED_START = $FORUM_POLL_TEMPLATE['form']['start'];
				$POLL_FORUM_NOTVOTED_LOOP = $FORUM_POLL_TEMPLATE['form']['item'];
				$POLL_FORUM_NOTVOTED_END = $FORUM_POLL_TEMPLATE['form']['end'];
				
				$POLL_FORUM_VOTED_START = $FORUM_POLL_TEMPLATE['results']['start'];
				$POLL_FORUM_VOTED_LOOP = $FORUM_POLL_TEMPLATE['results']['item'];
				$POLL_FORUM_VOTED_END = $FORUM_POLL_TEMPLATE['results']['end'];	
			}
			
				$POLL_NOTVOTED_START = $POLL_TEMPLATE['form']['start'];
				$POLL_NOTVOTED_LOOP = $POLL_TEMPLATE['form']['item'];
				$POLL_NOTVOTED_END = $POLL_TEMPLATE['form']['end'];
				
				$POLL_VOTED_START = $POLL_TEMPLATE['results']['start'];
				$POLL_VOTED_LOOP = $POLL_TEMPLATE['results']['item'];
				$POLL_VOTED_END = $POLL_TEMPLATE['results']['end'];			
			
		}
		
		

		$preview = FALSE;
		if ($type == 'preview')
		{
			$POLLMODE = 'notvoted';
		}
		elseif ($type == 'forum') 
		{
			$preview = TRUE;
		}

		$comment_total = 0;
		if ($pollArray['poll_comment'])
		{	// Only get comments if they're allowed on poll. And we only need the count ATM
			$comment_total = $sql->count("comments", "(*)", "WHERE `comment_item_id`='".intval($pollArray['poll_id'])."' AND `comment_type`=4");
		}

		$sc = e107::getScBatch('poll');
		$sc->setVars($pollArray);



		$QUESTION 		= $tp->toHTML($pollArray['poll_title'], TRUE, "emotes_off, defs");
		
		
		$VOTE_TOTAL 	= POLLAN_31.": ".$voteTotal;
		$COMMENTS 		= ($pollArray['poll_comment'] ? " <a href='".e_HTTP."comment.php?comment.poll.".$pollArray['poll_id']."'>".LAN_COMMENTS.": ".$comment_total."</a>" : "");
		
		
		$poll_count 	= $sql->count("polls", "(*)", "WHERE poll_id <= '".$pollArray['poll_id']."'");
		$OLDPOLLS = '';
		
		if ($poll_count > 1)
		{		
			$OLDPOLLS = ($type == 'menu' ? "<a href='".e_PLUGIN_ABS."poll/oldpolls.php'>".POLLAN_28."</a>" : "");
		}
		
		$AUTHOR 		= POLLAN_35." ".($type == 'preview' || $type == 'forum' ? USERNAME : "<a href='".e_HTTP."user.php?id.".$pollArray['poll_admin_id']."'>".$pollArray['user_name']."</a>");

	
		

		switch ($POLLMODE)
		{
			case 'notvoted':
				$text = "<form method='post' action='".e_SELF.(e_QUERY ? "?".e_QUERY : "")."'>\n".preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_START : $POLL_NOTVOTED_START));
				$count = 1;
				$sc->answerCount = 1;
				$alt = 0; // alternate style.
				
				$template = ($type == "forum") ? $POLL_FORUM_NOTVOTED_LOOP : $POLL_NOTVOTED_LOOP;
				
				foreach ($optionArray as $option) 
				{
					$sc->answerOption = $option; 
					//	$MODE = ($mode) ? $mode : "";		/* debug */
				//	$OPTIONBUTTON = ($pollArray['poll_allow_multiple'] ? "<input type='checkbox' name='votea[]' value='$count' />" : "<input type='radio' name='votea' value='$count' />");
				//	$OPTION = $tp->toHTML($option, TRUE);
				
			//		$OPTIONBUTTON = $tp->parseTemplate("{OPTIONBUTTON}",true);
					
				//	$OPTION = $tp->parseTemplate("{OPTION}",true);
					
			//		$OPTION = $tp->parseTemplate("{ANSWER}",true);
						
					$text .= $tp->parseTemplate($template, true, $sc);	
						
					$count ++;
					$sc->answerCount++;
				}
				
				
				$SUBMITBUTTON = "<input class='button btn btn-primary' type='submit' name='pollvote' value='".POLLAN_30."' />";
				// disable submit when previewing the poll or when NOT viewing the poll in the forum
				if (('preview' == $type || $preview == TRUE) && strpos(e_REQUEST_SELF, "forum") === FALSE)
				{
					$SUBMITBUTTON = "<input class='button btn btn-default e-tip' type='button' name='null' title='Disabled' value='".POLLAN_30."' />";
				}

				$text .= "\n".preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_NOTVOTED_END : $POLL_NOTVOTED_END))."\n</form>";
				break;

			case 'voted':
			case 'results' :
				if ($pollArray['poll_result_type'] && !strstr(e_SELF, "comment.php"))
				{
					$text = "<div style='text-align: center;'><br /><br />".POLLAN_39."<br /><br /><a href='".e_HTTP."comment.php?comment.poll.".$pollArray['poll_id']."'>".POLLAN_40."</a></div><br /><br />";
				}
				else
				{
					$text = preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_VOTED_START : $POLL_VOTED_START));
					$count = 0;
					foreach ($optionArray as $option)
					{
						$OPTION = $tp->toHTML($option, TRUE);
						$BAR = $this->generateBar($percentage[$count]);
				//		$BAR = ($percentage[$count] ? "<div style='width: 100%'><div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div><div style='background-image: url($bar); width: ".min(intval($percentage[$count]), 90)."%; height: 14px; float: left;'></div><div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div></div>" : "");
						$PERCENTAGE = $percentage[$count]."%";
						$VOTES = POLLAN_31.": ".$voteArray[$count];
						$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_VOTED_LOOP : $POLL_VOTED_LOOP));
						$count ++;
					}
						
					$text .= preg_replace("/\{(.*?)\}/e", '$\1', ($type == "forum" ? $POLL_FORUM_VOTED_END : $POLL_VOTED_END));
				}
			
				break;

			case 'disallowed':
				$text = preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_START);
				foreach ($optionArray as $option)
				{
					$MODE = $mode;		/* debug */
					$OPTION = $tp->toHTML($option, TRUE);
					$text .= preg_replace("/\{(.*?)\}/e", '$\1', $POLL_DISALLOWED_LOOP);
					$count ++;
				}
				if ($pollArray['poll_vote_userclass'] == 253)
				{
					$DISALLOWMESSAGE = POLLAN_41;
				}
				elseif ($pollArray['poll_vote_userclass'] == 254)
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

		if (!defined("POLLRENDERED")) define("POLLRENDERED", TRUE);
		
		$caption = (file_exists(THEME."images/poll_menu.png") ? "<img src='".THEME_ABS."images/poll_menu.png' alt='' /> ".POLLAN_MENU_CAPTION : POLLAN_MENU_CAPTION);
		
		if ($type == 'preview')
		{
			$caption = POLLAN_23.SEP."Preview"; // TODO LAN
			$text = "<div class='clearfix'>\n<div class='well span3'>".$text."</div></div>";
		}
		elseif ($type == 'forum')
		{
			$caption = LAN_4;
		}

		if ($returnMethod)
		{
			return $text;
		}
		else
		{
			$ns->tablerender($caption, $text, 'poll');
		}
	}


	
	function generateBar($perc)
	{
		if(deftrue('BOOTSTRAP',false))
		{
			$val = intval($perc);
			 return '
			 <div class="progress">
			 <div class="bar progress-bar" role="progressbar" aria-valuenow="'.$val.'" aria-valuemin="0" aria-valuemax="100" style="width: '.$val.'%;">
			   <span class="sr-only">'.$val.'%</span>
			 </div>
			 </div>';	
			
		}
		else
		{
			$barl = $this->barl;
			$barr = $this->barr;
			$bar = $this->bar;
			return ($perc ? "<div style='width: 100%'><div style='background-image: url($barl); width: 5px; height: 14px; float: left;'></div><div style='background-image: url($bar); width: ".min(intval($perc), 90)."%; height: 14px; float: left;'></div><div style='background-image: url($barr); width: 5px; height: 14px; float: left;'></div></div>" : "");	
		}	
	}





	/*
	function renderPollForm
	$mode = "admin" :: called from admin_config.php
	$mode = "forum" :: called from forum_post.php
	*/	
	/**
	 * Render a Poll creation Form
	 * @param $mode string - admin | forum | front 
	 */
	function renderPollForm($mode='admin')
	{
		$tp = e107::getParser();
		$frm = e107::getForm();
	//	echo "MODE=".$mode;
		
		//XXX New v2.x default for front-end. Currently used by forum-post in bootstrap mode. 
		// TODO LAN - Needs a more generic LAN rewrite when used on another area than forum
		if ($mode == 'front')
		{				
			
			$text = "
			
			<div class='alert alert-info'>
				<small >".LAN_FORUM_3029."</small>
			</div>";


		//		$text .= "<form>";


			$text .= "

				<div class='form-group'>
					<label for='poll_title'>Poll question</label>
					".$frm->text('poll_title', $tp->post_toForm(vartrue($_POST['poll_title'])), '200', array('placeholder' => LAN_FORUM_3030, 'id' => 'poll_title'))." 
				</div>";

			$option_count = vartrue($_POST['poll_option']) ? count($_POST['poll_option']) : 2;
			$text .= "		
				<div id='pollsection'>
					<label for='pollopt'>Poll answers</label>";
				
				for($count = 1; $count <= $option_count; $count++)
				{
					// if ($count != 1 && $_POST['poll_option'][($count-1)] =="")
					// {
					// //	break;
					// }
					
					$opt = ($count==1) ? "id='poll_answer'" : "";

					$text .= "<div class='form-group' ".$opt.">
								".$frm->text('poll_option[]', $_POST['poll_option'][($count-1)], '200', array('placeholder' => LAN_FORUM_3031, 'id' => $opt))."
							  </div>";
				}

				$text .= "</div>"; // end pollsection div

				$text .= "<div  class='form-group control-group'>
							<input class='btn btn-default' type='button' id='addoption' name='addoption' value='".LAN_FORUM_3032."' />
						</div>

				";
			
			//FIXME - get this looking good with Bootstrap CSS only. 
			
			$opts = array(1 => LAN_YES, 0=> LAN_NO);
				
			// Set to IP address.. Can add a pref to Poll admin for 'default front-end storage method' if demand is there for it. 
		
		$text .= "<br />
			 <div class='form-horizontal control-group'>
				<label class='control-label'>".LAN_FORUM_3033."</label>
				<div class='radio controls'>
					". $frm->radio('multipleChoice',$opts, vartrue($_POST['multipleChoice'], 0) ).$frm->hidden('storageMethod', 1)."
				</div>
			</div>			
		";

	//	$text .= "</form>";
		
		return $text;
		
			
	/*
			$text .= "
				<div class='controls controls-row'>".POLL_506."
				
				<input type='radio' name='multi/pleChoice' value='1'".(vartrue($_POST['multipleChoice']) ? " checked='checked'" : "")." /> ".POLL_507."&nbsp;&nbsp;
				<input type='radio' name='multi/pleChoice' value='0'".(!$_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".POLL_508."
				
				</div>";
			*/
		
			//XXX Should NOT be decided by USER 
			/*
			$text .= "

			<div>
			".POLLAN_16."
			
			<input type='radio' name='storageMethod' value='0'".(!vartrue($_POST['storageMethod']) ? " checked='checked'" : "")." /> ".POLLAN_17."<br />
			<input type='radio' name='storageMethod' value='1'".($_POST['storageMethod'] == 1 ? " checked='checked'" : "")." /> ".POLLAN_18."<br />
			<input type='radio' name='storageMethod' value='2'".($_POST['storageMethod'] ==2 ? " checked='checked'" : "")." /> ".POLLAN_19."
			</div>
			";
			*/
		
			
		}
		
		
		//TODO Hardcoded FORUM code needs to be moved somewhere. 
		if ($mode == 'forum')
		{
			$text = "
			<tr>
				<td colspan='2'><span class='smalltext'>".LAN_FORUM_3029."</span></td>
			</tr>
			<tr>
				<td style='width:20%'><div class='normaltext'>".LAN_FORUM_3030.": </div></td>
				<td style='width:80%'class='forumheader3'><input class='tbox' type='text' name='poll_title' size='70' value='".$tp->post_toForm(vartrue($_POST['poll_title']))."' maxlength='200' /></td>
			</tr>";

			$option_count = (count(vartrue($_POST['poll_option'])) ? count($_POST['poll_option']) : 1);
			$text .= "
			<tr>
				<td style='width:20%'>".LAN_FORUM_3031."</td>
				<td style='width:80%'>
				<div id='pollsection'>";

				for($count = 1; $count <= $option_count; $count++)
				{
					if ($count != 1 && $_POST['poll_option'][($count-1)] =="")
					{
						break;
					}

					$opt = ($count==1) ? "id='pollopt'" : "";
					$text .="<span {$opt}><input  class='tbox' type='text' name='poll_option[]' size='40' value=\"".$_POST['poll_option'][($count-1)]."\" maxlength='200' />";
					$text .= "</span><br />";
				}

				$text .="
				</div>
				<input class='btn btn-default button' type='button' name='addoption' value='".LAN_FORUM_3032."' onclick=\"duplicateHTML('pollopt','pollsection')\" /><br />
				</td>
			</tr>
			<tr>
				<td style='width:20%'>".LAN_FORUM_3033."</td>
				<td style='width:80%'>
				<input type='radio' name='multipleChoice' value='1'".(vartrue($_POST['multipleChoice']) ? " checked='checked'" : "")." /> ".LAN_YES."&nbsp;&nbsp;
				<input type='radio' name='multipleChoice' value='0'".(!$_POST['multipleChoice'] ? " checked='checked'" : "")." /> ".LAN_NO."
			</td>
			</tr>
			<tr>
				<td style='width:30%'>".LAN_FORUM_3034."</td>
				<td>
					<input type='radio' name='storageMethod' value='0'".(!vartrue($_POST['storageMethod']) ? " checked='checked'" : "")." /> ".LAN_FORUM_3035."<br />
					<input type='radio' name='storageMethod' value='1'".($_POST['storageMethod'] == 1 ? " checked='checked'" : "")." /> ".LAN_FORUM_3036."<br />
					<input type='radio' name='storageMethod' value='2'".($_POST['storageMethod'] ==2 ? " checked='checked'" : "")." /> ".LAN_FORUM_3037."
				</td>
			</tr>
			";


			return $text;
		}

		$formgo = e_SELF.(e_QUERY && !defined("RESET") && strpos(e_QUERY, 'delete') === FALSE ? "?".e_QUERY : "");

		$text = "<div>
		<form method='post' action='{$formgo}'>
		<table class='table adminform'>
        <colgroup>
			<col class='col-label' />
			<col class='col-control' />
		</colgroup>
		<tr>
		<td style='width:30%'><div class='normaltext'>".POLLAN_3.":</div></td>
		<td style='width:70%'>
		<input class='tbox input-xxlarge' type='text' name='poll_title' size='70' value='".$tp->post_toForm(varset($_POST['poll_title']))."' maxlength='200' />";

		$option_count = (varset($_POST['poll_option']) && count($_POST['poll_option']) ? count($_POST['poll_option']) : 2);

		$text .= "</td></tr><tr>
		<td style='width:30%;vertical-align:top'>".LAN_OPTIONS." :</td>
		<td style='width:70%'>
		<div id='pollsection'>";

		for($count = 1; $count <= $option_count; $count++)
		{
			$opt = ($count==1) ? "id='pollopt'" : "";
			$text .="<span class='form-inline' style='display:inline-block; padding-bottom:5px' {$opt}><input  class='tbox input-large' type='text' name='poll_option[]' size='40' value=\"".$tp->post_toForm($_POST['poll_option'][($count-1)])."\" maxlength='200' />";
			$text .= "</span><br />";
		}

		$text .="</div><input class='btn btn-default' type='button' name='addoption' value='".POLLAN_8."' onclick=\"duplicateHTML('pollopt','pollsection')\" /><br />
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
		<td>".r_userclass("pollUserclass", vartrue($_POST['pollUserclass']), 'off', $uclass)."</td>
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
			// $text .= "<input  type='submit' name='preview' value='".POLLAN_24."' /> ";
			$text .= $frm->admin_button('preview',POLLAN_24,'other');
			
			if (POLLACTION == 'edit')
			{
				$text .= $frm->admin_button('submit', LAN_UPDATE, 'update')."
				
				<input type='hidden' name='poll_id' value='".intval($_POST['poll_id'])."' /> ";
			}
			else
			{
				$text .= $frm->admin_button('submit','no-value','submit', LAN_CREATE);
			//	$text .= "<input type='submit' name='submit' value='".POLLAN_23."' /> ";
			}
		} 
		else 
		{
			$text .= $frm->admin_button('preview','no-value','other',POLLAN_24);
		//	$text .= "<input  type='submit' name='preview' value='".POLLAN_24."' /> ";
		}
		
		if (defset('POLLID')) 
		{
			$text .= $frm->admin_button('reset','no-value','reset',POLLAN_25);
		//	$text .= "<input  type='submit' name='reset' value='".POLLAN_25."' /> ";
		}

		$text .= "</div>
		</form>
		</div>";

		return $text;
	}
}




class poll_shortcodes extends e_shortcode
{
	var $answerOption = array();
	var $answerCount;

	function sc_option($parm='')
	{
	 	return $this->answerOption;
	}


	function sc_optionbutton($parm='')
	{
		return ($this->var['poll_allow_multiple'] ? "<input type='checkbox' name='votea[]' value='$count' />" : "<input type='radio' name='votea' value='".$this->answerCount."' />");		
	}

	function sc_question($parm = "")
	{
		$tp = e107::getParser();
		return $tp->toHTML($this->var['poll_title'], TRUE, "emotes_off, defs");		
	}
	
	function sc_answer($parm='')
	{
		$frm = e107::getForm();
		$opt = array('label'=> $this->answerOption);
		return $frm->radio('votea', $this->answerCount,false, $opt);	
	//	$this->answerOption
	}
		
}



/*
e107::js('inline', '

	function setcook(pollid){
		var name = "poll_"+pollid;
		var date = new Date();
		var value = pollid;
		date.setTime(date.getTime()+(365*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
		document.cookie = name+"="+value+expires+"; path=/";
	}
		');*/


?>