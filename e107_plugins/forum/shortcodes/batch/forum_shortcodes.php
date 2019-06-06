<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - view shortcodess
 *
*/

if (!defined('e107_INIT')) { exit; }

class forum_shortcodes extends e_shortcode
{
	private $forum_rules, $gen;

	public $newFlagList;

	function __construct()
	{
		$this->forum_rules = forum_rules('check');
    $this->gen = new convert;
	}

	// START OF $FVARS
	function sc_forumtitle()
	{
		return e107::pref('forum','title', LAN_PLUGIN_FORUM_NAME);	
	}

// LEGACY shortcodes, to be deprecated & directly handled in template file???
	function sc_threadtitle()
	{
		return LAN_FORUM_0002;	
	}

	function sc_replytitle()
	{
		return LAN_FORUM_0003;	
	}

	function sc_lastpostitle()
	{
		return LAN_FORUM_0004;	
	}

	function sc_infotitle()
	{
		return LAN_FORUM_0009;	
	}

	function sc_newthreadtitle()
	{
		return LAN_FORUM_0075;	
	}

	function sc_postedtitle()
	{
		return LAN_FORUM_0074;	
	}

	function sc_tracktitle()
	{
		return LAN_FORUM_0073;	
	}

	function sc_statlink()
	{
		return "<a href='".e107::url('forum','stats')."'>".LAN_FORUM_0017."</a>\n";
	}

	function sc_iconkey()
	{
		 return "
		<table class='table table-bordered' style='width:100%'>\n<tr>
		<td style='width:2%'>".IMAGE_new_small."</td>
		<td style='width:10%'><span class='smallblacktext'>".LAN_FORUM_0039."</span></td>
		<td style='width:2%'>".IMAGE_nonew_small."</td>
		<td style='width:10%'><span class='smallblacktext'>".LAN_FORUM_0040."</span></td>
		<td style='width:2%'>".IMAGE_closed_small."</td>
		<td style='width:10%'><span class='smallblacktext'>".LAN_FORUM_0041."</span></td>
		</tr>\n</table>\n";
  }
	// End of LEGACY shortcodes...

	function sc_logo()
	{
		return IMAGE_e;	
	}

	function sc_newimage()
	{
		return IMAGE_new_small;	
	}

	function sc_userinfo()
	{
		//---- Pass globals via $sc?????
		  global $forum, $pref;

		$text = "<a href='".e_BASE."top.php?0.top.forum.10'>".LAN_FORUM_0010."</a> | <a href='".e_BASE."top.php?0.active'>".LAN_FORUM_0011."</a>";
		if(USER)
		{
			$text .= " | <a href='".e_BASE.'userposts.php?0.forums.'.USERID."'>".LAN_FORUM_0012."</a> | <a href='".e_BASE."usersettings.php'>".LAN_FORUM_0013."</a> | <a href='".e_HTTP."user.php?id.".USERID."'>".LAN_FORUM_0014."</a>";
		// To be reworked to get the $forum var
			if($forum->prefs->get('attach') && (check_class($pref['upload_class']) || getperms('0')))
			{
				$text .= " | <a href='".e_PLUGIN."forum/forum_uploads.php'>".LAN_FORUM_0015."</a>";
			}
		}
			if(!empty($this->forum_rules))
			{
				$text .= " | <a href='".e107::url('forum','rules')."'>".LAN_FORUM_0016.'</a>';
			}
				return $text;
	}


	function sc_userinfox()
	{
        global $forum;

         $uInfo = array();
		$uInfo[0] = "<a href='".e107::url('forum','stats')."'>".LAN_FORUM_6013.'</a>';

		if(!empty($this->forum_rules))
		{
			$uInfo[1] = "<a href='".e107::url('forum','rules')."'>".LAN_FORUM_0016.'</a>';
		}

		// To be reworked to get the $forum var
		$trackPref = $forum->prefs->get('track');
//var_dump($forum->checkPerm($this->var['forum_id'], 'post'));
		if(!empty($trackPref) && $forum->checkPerm($this->var['forum_id'], 'post'))
		{
			$uInfo[2] = "<a href='".e107::url('forum','track')."'>".LAN_FORUM_0030."</a>";
		}

		return implode(" | ",$uInfo);
	}

	function sc_userlist()
	{
		if(!defined('e_TRACKING_DISABLED'))
		{
		// String candidate for USERLIST wrapper
			$text = LAN_FORUM_0036.": ";

			global $listuserson;
			$c = 0;
			if(is_array($listuserson))
			{
		//----	foreach($listuserson as $uinfo => $pinfo)
			    foreach(array_keys($listuserson) as $uinfo)
			//	foreach($listuserson as $uinfo => &$pinfo)
				{
					list($oid, $oname) = explode(".", $uinfo, 2);
					$c ++;
					$text .= "<a href='".e_HTTP."user.php?id.$oid'>$oname</a>".($c == MEMBERS_ONLINE ? "." :", ");
				}

			}
		// String candidate for USERLIST wrapper
			$text .= "<br /><a rel='external' href='".e_BASE."online.php'>".LAN_FORUM_0037."</a> ".LAN_FORUM_0038;
		}
		  return $text;
	}

	/**
	 * @example {SEARCH: placeholder=Search forums} - sets placeholder 'Search forums'
	 * @example {SEARCH: buttonclass=btn btn-small} - sets button class 'btn btn-small'
	*/
	function sc_search($parm=null)
	{

		if(!deftrue('FONTAWESOME') || !$srchIcon = e107::getParser()->toGlyph('fa-search'))
		{
			$srchIcon = LAN_SEARCH;
		}

		$buttonclass 	= (!empty($parm['buttonclass'])) ? "class='".$parm['buttonclass']."'" : "class='btn btn-default btn-secondary button'";
		$placeholder    = (!empty($parm['placeholder'])) ? $parm['placeholder'] : LAN_SEARCH;

		// String candidate for USERLIST wrapper
		return "
		<form method='get' class='form-inline input-append' action='".e_HTTP."search.php'>
		<div class='input-group'>
		<input type='hidden' name='r' value='0' />
		<input type='hidden' name='t' value='forum' />
		<input type='hidden' name='forum' value='all' />
		<input class='tbox form-control' type='text' name='q' size='20' value='' placeholder='".$placeholder."' maxlength='50' />
		<span class='input-group-btn'>
		<button ".$buttonclass." type='submit' name='s' value='search' >".$srchIcon."</button>
		</span>
		</div>

		</form>\n";
	}

	function sc_perms()
	{
		return (USER == TRUE || ANON == TRUE ? LAN_FORUM_0043." - ".LAN_FORUM_0045." - ".LAN_FORUM_0047 : LAN_FORUM_0044." - ".LAN_FORUM_0046." - ".LAN_FORUM_0048);
	}

	function sc_info()
	{
		//$fVars->INFO = "";
		//  global $forum;
		//$sql = e107::getDb();
		//$gen = new convert;
		if (ANON == TRUE)
		{
			$text = LAN_FORUM_0049.'<br />'.LAN_FORUM_0050." <a href='".e_SIGNUP."'>".LAN_FORUM_0051."</a> ".LAN_FORUM_0052;
		}
		elseif(USER == FALSE)
		{
			$text = LAN_FORUM_0049.'<br />'.LAN_FORUM_0053." <a href='".e_SIGNUP."'>".LAN_FORUM_0054."</a> ".LAN_FORUM_0055;
		}

		if (USER == TRUE)
		{
			$total_new_threads = e107::getDb()->count('forum_thread', '(*)', "WHERE thread_datestamp>'".USERLV."' ");
				$total_read_threads = 0;
			if (defset('USERVIEWED') != "")
			{
				$tmp = explode(".", USERVIEWED); // List of numbers, separated by single period
				$total_read_threads = count($tmp);
			}
		/*
			else
			{
				$total_read_threads = 0;
			}
		*/

		//		$gen = new convert;
			$text = LAN_FORUM_0018." ".USERNAME."<br />";
			$lastvisit_datestamp = $this->gen->convert_date(USERLV, 'long'); //FIXME Use e107::getParser()->toDate();
			$datestamp = $this->gen->convert_date(time(), "long");

		/*
			if (!$total_new_threads)
			{
				$text .= LAN_FORUM_0019." ";
			}
			elseif($total_new_threads == 1)
			{
				$text .= LAN_FORUM_0020;
			}
			else
			{
				$text .= LAN_FORUM_0021." ".$total_new_threads." ".LAN_FORUM_0022." ";
			}
		*/
				$text .= (!$total_new_threads?LAN_FORUM_0019." ":($total_new_threads == 1?LAN_FORUM_0020:LAN_FORUM_0021." ".$total_new_threads." ".LAN_FORUM_0022." ")).LAN_FORUM_0023;
		//	$text .= LAN_FORUM_0023;
		//	if ($total_new_threads == $total_read_threads && $total_new_threads != 0 && $total_read_threads >= $total_new_threads)
			if ($total_new_threads != 0 && $total_read_threads >= $total_new_threads)
			{
				$text .= LAN_FORUM_0029;
				$allread = TRUE;
			}
			elseif($total_read_threads != 0)
			{
				$text .= " (".LAN_FORUM_0027." ".$total_read_threads." ".LAN_FORUM_0028.")";
			}

			$text .= "<br />
			".LAN_FORUM_0024." ".$lastvisit_datestamp."<br />
			".LAN_FORUM_0025." ".$datestamp;
		}
		/*
		else
		{
			$text .= '';
			if (ANON == TRUE)
			{
				$text .= LAN_FORUM_0049.'<br />'.LAN_FORUM_0050." <a href='".e_SIGNUP."'>".LAN_FORUM_0051."</a> ".LAN_FORUM_0052;
			}
			elseif(USER == FALSE)
			{
				$text .= LAN_FORUM_0049.'<br />'.LAN_FORUM_0053." <a href='".e_SIGNUP."'>".LAN_FORUM_0054."</a> ".LAN_FORUM_0055;
			}
		}
		*/

		//if (USER && vartrue($allread) != TRUE && $total_new_threads && $total_new_threads >= $total_read_threads)
		if (USER && !$allread && $total_new_threads && $total_new_threads >= $total_read_threads)
		{
			$text .= "<br /><a href='".e_SELF."?mark.all.as.read'>".LAN_FORUM_0057.'</a>'.(e_QUERY != 'new' ? ", <a href='".e_SELF."?new'>".LAN_FORUM_0058."</a>" : '');
		}

		$forum = new e107forum;
		//$trackPref = $forum->prefs->get('track');
		//if (USER && vartrue($trackPref) && e_QUERY != 'track')
		if (USER && vartrue($forum->prefs->get('track')) && e_QUERY != 'track')
		{
			$text .= "<br /><a href='".e107::url('forum','track')."'>".LAN_FORUM_0030.'</a>';
		}
		return $text;
	}



	function sc_foruminfo()
	{
		$sql = e107::getDb();

		$total_topics = $sql->count("forum_thread", "(*)");
		$total_replies = $sql->count("forum_post", "(*)");
		$total_members = $sql->count("user");
		//----$newest_member = $sql->select("user", "*", "user_ban='0' ORDER BY user_join DESC LIMIT 0,1");
		list($nuser_id, $nuser_name) = $sql->fetch('num'); // FIXME $nuser_id & $user_name return empty even though print_a($newest_member); returns proper result.

		if(!defined('e_TRACKING_DISABLED'))
		{
			$member_users = $sql->select("online", "*", "online_location REGEXP('forum.php') AND online_user_id!='0' ");
			$guest_users = $sql->select("online", "*", "online_location REGEXP('forum.php') AND online_user_id='0' ");
			$users = $member_users+$guest_users;
		}

		return str_replace("[x]", ($total_topics+$total_replies), LAN_FORUM_0031)." ($total_topics ".($total_topics == 1 ? LAN_FORUM_0032 : LAN_FORUM_0033).", $total_replies ".($total_replies == 1 ? LAN_FORUM_0034 : LAN_FORUM_0035).")
		".(!defined("e_TRACKING_DISABLED") ? "" : "<br />".$users." ".($users == 1 ? LAN_FORUM_0059 : LAN_FORUM_0060)." (".$member_users." ".($member_users == 1 ? LAN_FORUM_0061 : LAN_FORUM_0062).", ".$guest_users." ".($guest_users == 1 ? LAN_FORUM_0063 : LAN_FORUM_0064).")<br />".LAN_FORUM_0066." ".$total_members."<br />".LAN_FORUM_0065." <a href='".e_HTTP."user.php?id.".$nuser_id."'>".$nuser_name."</a>.\n"); // FIXME cannot find other references to e_TRACKING_DISABLED, use pref?
	}



	function sc_parentstatus()
	{
	//----		return $this->parentstatus;
	//  	if(!check_class($this->fparent['forum_postclass']))
	    if(!check_class($this->var['forum_postclass']))
	    {
	        $status = '('.LAN_FORUM_0056.')';
	    }
	    return vartrue($status);
	}


	function sc_parentimage($parms=null)
	{
		if (empty($this->var['forum_image'])) return '';
		if (!empty($parms) && !is_array($parms)) parse_str($parms, $parms);
		if (empty($parms)) $parms = array();
		$parms = array_merge(array('class'=>'img-fluid', 'h' => 50), $parms);
		$text = e107::getParser()->toImage($this->var['forum_image'], $parms);
		return $text.'&nbsp;';
	}

	function sc_parentname()
	{
		return $this->var['forum_name'];
	}


	// Function to show the retrieval of parent ID, not really needed by core template
	function sc_parentid()
	{
		return $this->var['forum_id'];
	}


// START OF parse_forum function $FVARS

	function sc_newflag()
	{

		if(USER && is_array($this->newFlagList) && in_array($this->var['forum_id'], $this->newFlagList))
		{

			$url = $this->sc_lastpost(array('type'=>'url'));
			return "<a href='".$url."'>".IMAGE_new.'</a>';
		}
		elseif(empty($this->var['forum_replies']) && defined('IMAGE_noreplies'))
		{
			return IMAGE_noreplies;
		}

		return IMAGE_nonew;

	}


	function sc_forumimage($parms=null)
	{
		if(empty($this->var['forum_image'])) return '';

		if (!empty($parms) && !is_array($parms)) parse_str($parms, $parms);
		if (empty($parms)) $parms = array();
		$parms = array_merge(array('class'=>'img-fluid', 'h' => 50), $parms);
		$text = e107::getParser()->toImage($this->var['forum_image'], $parms);
		return "<a href='".e107::url('forum', 'forum', $this->var)."'>{$text}</a>&nbsp;";

	}

	/**
	* @example: {FORUMICON: size=2x} 
	*/
	function sc_forumicon($parms = null)
	{
		if(empty($this->var['forum_icon'])) return '';

		return e107::getParser()->toIcon($this->var['forum_icon'], $parms);
	}

	function sc_forumname($parm = null)
	{
		if(substr($this->var['forum_name'], 0, 1) == '*')
		{
			$this->var['forum_name'] = substr($this->var['forum_name'], 1);
		}

		$this->var['forum_name'] = e107::getParser()->toHTML($this->var['forum_name'], true, 'no_hook');

		$class = !empty($parm['class']) ? "class='".$parm['class']."'" : '';

		$url = e107::url('forum', 'forum', $this->var);
		return "<a href='".$url."' ".$class.">{$this->var['forum_name']}</a>";

	}


	function sc_forumdescription()
	{
		//    global $f, $restricted_string;
	    global $restricted_string;
		//	$tp = e107::getParser();
		$this->var['forum_description'] = e107::getParser()->toHTML($this->var['forum_description'], true, 'no_hook');
		return $this->var['forum_description'].($restricted_string ? "<br /><span class='smalltext'><i>$restricted_string</i></span>" : "");
    }


	function sc_threads()
	{
		return $this->sc_threadsx();
	}


	function sc_replies($parm = '')
	{
		return $this->sc_repliesx($parm);
	}


	function sc_threadsx($parm = null) // EQUAL TO SC_THREADS.......................
	{
		$val = ($this->var['forum_threads']) ? $this->var['forum_threads'] : '0';

		if(!empty($parm['raw']))
		{
			return $val;
		}

		return e107::getParser()->toBadge($val);
	}


	function sc_repliesx($parm = null) // EQUAL TO SC_REPLIES.......................
	{
		//print_a($parm);
		$val = ($this->var['forum_replies']) ? $this->var['forum_replies'] : '0';

		if(!empty($parm['raw']))
		{
			return $val;
		}

		return e107::getParser()->toBadge($val);
	}


	function sc_forumsubforums()
	{
  		return (!empty($this->var['text'])) ? "<br /><div class='smalltext'>".LAN_FORUM_0069.": {$this->var['text']}</div>":"";
	}

	function sc_lastpostuser()
	{
        return $this->sc_lastpost(array('type'=>'username'));
	}


	function sc_lastpostdate()
	{
        return $this->sc_lastpost(array('type'=>'datelink'));
	}


	function sc_lastpost($parm = null)
	{
		if (empty($this->var['forum_lastpost_info']))
		{
			return false;
		}

		global $forum;

		list($lastpost_datestamp, $lastpost_thread) = explode('.', $this->var['forum_lastpost_info']);

		// e107::getDebug()->log($this->var);


		$lastpost       = $forum->threadGetLastpost($lastpost_thread); //FIXME TODO inefficient to have SQL query here.
		$urlData        = array('forum_sef'=>$this->var['forum_sef'], 'thread_id'=>$lastpost['post_thread'],'thread_sef'=>$lastpost['thread_sef']);
		$url            = e107::url('forum', 'topic', $urlData)."?last=1#post-".$lastpost['post_id'];
		$lastpost_username = empty($this->var['user_name']) ? e107::getParser()->toHTML($this->var['forum_lastpost_user_anon']) : "<a href='".e107::url('user/profile/view', array('name' => $this->var['user_name'], 'id' => $this->var['forum_lastpost_user']))."'>{$this->var['user_name']}</a>";


		$format = !empty($parm['date-format']) ? $parm['date-format'] : 'relative';

		$relativeDate = e107::getParser()->toDate($lastpost_datestamp, $format);

		if(!empty($parm['type']))
		{
			switch($parm['type'])
//		switch($mode)
			{
				case "username":
					return $lastpost_username;
//				break;

				case "datelink":
					return "<a href='".$url."'>". $relativeDate."</a>";
//				break;
				case "date":
					return $relativeDate;

				case "url":
					return $url;
//					break;
				case "name":
					return $lastpost['thread_name'];
//			default:

//				return $relativeDate.'<br />'.$lastpost_name." <a href='".$url."'>".IMAGE_post2.'</a>';

				// code to be executed if n is different from all labels;
			}
  		}
				return $lastpost_username." <a href='".$url."'>".$relativeDate.'</a>';
	}

	function sc_startertitle()
	{

		$author_name = ($this->var['user_name'] ? $this->var['user_name'] : $this->var['lastuser_anon']);

//--		$datestamp = $gen->convert_date($thread['thread_lastpost'], 'forum');
		$datestamp = $this->gen->convert_date($this->var['thread_lastpost'], 'forum');

  
		if(!$this->var['user_name'])
		{
			return $author_name.'<br />'.$datestamp;
		}

//			return "<a href='".$e107->url->create('user/profile/view', array('id' => $thread['thread_lastuser'], 'name' => $sc->author_name))."'>{$sc->author_name}</a><br />".$sc->datestamp;
			return "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $this->var['thread_lastuser'], 'name' => $author_name))."'>{$author_name}</a><br />".$datestamp;
//----		}
	}


	function sc_newspostname()
	{
		//  global $thread;
		//	$e107 = e107::getInstance();
		//	$tp = e107::getParser();

		//		return empty($thread)?LAN_FORUM_0029:"<a href='".$e107->url->create('forum/thread/last', $thread)."'>".$tp->toHTML($thread['thread_name'], TRUE, 'no_make_clickable, no_hook').'</a>';
		// Only $this->var???'
		return empty($this->var) ? LAN_FORUM_0029:"<a href='".e107::getUrl()->create('forum/thread/last', $this->var)."'>".e107::getParser()->toHTML($this->var['thread_name'], TRUE, 'no_make_clickable, no_hook').'</a>';
	}



	function sc_forum_breadcrumb()
	{
        global $breadarray;
		$frm = e107::getForm();
		return $frm->breadcrumb($breadarray);
	}

	function sc_avatar($opts)
	{
		return e107::getParser()->toAvatar(e107::user($this->var['forum_lastpost_user']),$opts);
	}
}
