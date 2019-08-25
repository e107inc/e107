<?php
	/*
	 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
	 *
	 * Forum plugin - view shortcodess
	 *
	*/
	if(!defined('e107_INIT'))
	{
		exit;
	}


	class plugin_forum_viewforum_shortcodes extends e_shortcode
	{

		private $gen;

		function __construct()
		{
			$this->gen = new convert; // TODO replace all usage with e107::getParser()->toDate();
//		$this->forum_rules = forum_rules('check');
		}

// LEGACY shortcodes, to be deprecated & directly handled in template file???
		function sc_startertitle()
		{
			return LAN_FORUM_1004;
		}

		function sc_threadtitle()
		{
			return LAN_FORUM_1003;
		}

		function sc_replytitle()
		{
			return LAN_FORUM_0003;
		}

		function sc_lastpostitle()
		{
			return LAN_FORUM_0004;
		}

		function sc_viewtitle()
		{
			return LAN_FORUM_1005;
		}

// End of LEGACY shortcodes...

		function sc_message()
		{
			return $this->var['message'];
		}

		function sc_threadpages()
		{
			if(empty($this->var['parms']))
			{
				return null;
			}
			return e107::getParser()->parseTemplate("{NEXTPREV={$this->var['parms']}}");
		}

		function sc_newthreadbutton()
		{
			return "<a href='" . $this->var['ntUrl'] . "'>" . IMAGE_newthread . '</a>';
		}

		function sc_newthreadbuttonx()
		{

			if(!BOOTSTRAP)
			{
				return $this->sc_newthreadbutton();
			}

			//--function newthreadjump($url)
			//--{


			global $forum;
			$jumpList = $forum->forumGetAllowed('view');

			$text = '<div class="btn-group">';
/*
			$text .=
			($this->var['ntUrl'] ? '<a href="'.$this->var['ntUrl'].'" class="btn btn-primary">'.LAN_FORUM_1018.'</a>' :'').
		    	'<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
		    	'.($this->var['ntUrl'] ? '' : LAN_FORUM_1001." ".LAN_FORUM_8013).'<span class="caret"></span>
		    	<span class="sr-only">Toggle Dropdown</span>
			</button>
		    	<ul class="dropdown-menu pull-right">
		    	';
*/
			$text .=
			'<a href="'.($this->var['ntUrl'] ?:"#").
			'" class="btn btn-primary'.($this->var['ntUrl'] ?"":" disabled").'"'
			.($this->var['ntUrl'] ?"":" data-toggle='tooltip' title='".LAN_FORUM_0006."'
			style='cursor: not-allowed; pointer-events: all !important;'").'>'.LAN_FORUM_1018.'</a>
			'.($this->var['ntUrl'] ?"":"<span>&nbsp;</span>").'
			<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
			';
			if(BOOTSTRAP !== 4)
			{
		    	$text .= '<span class="caret"></span>';
		    }
		    $text .= '
		    	<span class="sr-only">Toggle Dropdown</span>
			</button>
		    	<ul class="dropdown-menu pull-right float-right">
		    	';
			
			//--	foreach($jumpList as $key => $val)
			foreach($jumpList as $val)
			{
				$text .= '<li><a class="dropdown-item" href="' . e107::url('forum', 'forum', $val) . '">' . LAN_FORUM_1017 . ': ' . $val['forum_name'] . '</a></li>';
			}

			$text .= '
		    </ul>
		    </div>';

			return $text;

//}


		}

		function sc_breadcrumb()
		{
			return $this->var['breadcrumb'];
		}

		function sc_backlink()
		{
			return $this->var['breadcrumb'];
		}

		function sc_forum_crumb()
		{
			return $this->var['forum_crumb'];
		}

		function sc_forumimage($parms=null)
		{
			if(empty($this->var['forum_image'])) return '';
			if (!empty($parms) && !is_array($parms)) parse_str($parms, $parms);
			if (empty($parms)) $parms = array();
			$parms = array_merge(array('class'=>'img-fluid', 'h' => 50), $parms);
			$text = e107::getParser()->toImage($this->var['forum_image'], $parms);
			return $text."&nbsp;";
		}

		/**
		* @example: {FORUMICON: size=2x} 
		*/
		function sc_forumicon($parms = null)
		{
			if(empty($this->var['forum_icon'])) return '';

			return e107::getParser()->toIcon($this->var['forum_icon'], $parms);
		}

		function sc_forumtitle()
		{
			return $this->var['forum_name'];
		}

		function sc_forumdescription()
		{
			//    global $f, $restricted_string;
		    global $restricted_string;
			//	$tp = e107::getParser();
			$this->var['forum_description'] = e107::getParser()->toHTML($this->var['forum_description'], true, 'no_hook');
			return $this->var['forum_description'].($restricted_string ? "<br /><span class='smalltext'><i>$restricted_string</i></span>" : "");
	    }

		function sc_moderators()
		{
			return is_array($this->var['modUser']) ? implode(", ",$this->var['modUser']) : $this->var['modUser'];
		}

		function sc_browsers()
		{
			global $member_users, $users, $guest_users;

			if($this->var['track_online'])
			{
				return $users . ' ' . ($users == 1 ? LAN_FORUM_0059 : LAN_FORUM_0060) . ' (' . $member_users . ' ' . ($member_users == 1 ? LAN_FORUM_0061 : LAN_FORUM_0062) . ", " . $guest_users . " " . ($guest_users == 1 ? LAN_FORUM_0063 : LAN_FORUM_0064) . ')';
			}

		}

		function sc_iconkey()
		{
			global $FORUM_VIEWFORUM_TEMPLATE;

			/*--
			if(defset('BOOTSTRAP')==3 && !empty($FORUM_VIEWFORUM_TEMPLATE['iconkey'])) // v2.x
			{
				return e107::getParser()->parseTemplate($FORUM_VIEWFORUM_TEMPLATE['iconkey'],true);
			}
			// v1.x

				return "
				<table class='table table-bordered' style='width:100%'>
				<tr>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_0039."</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_0040."</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_sticky_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1011."</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_announce_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1013."</td>
				</tr>
				<tr>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_new_popular_small."</td>
				<td style='width:2%' class='smallblacktext'>".LAN_FORUM_0039." ".LAN_FORUM_1010."</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_nonew_popular_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_0040." ".LAN_FORUM_1010."</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_stickyclosed_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1012."</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>".IMAGE_closed_small."</td>
				<td style='width:10%' class='smallblacktext'>".LAN_FORUM_1014."</td>
				</tr>
				</table>";
			--*/

			return (defset('BOOTSTRAP') && !empty($FORUM_VIEWFORUM_TEMPLATE['iconkey'])) ? e107::getParser()->parseTemplate($FORUM_VIEWFORUM_TEMPLATE['iconkey'], true) : "
				<table class='table table-bordered' style='width:100%'>
				<tr>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_new_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_0039 . "</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_nonew_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_0040 . "</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_sticky_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_1011 . "</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_announce_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_1013 . "</td>
				</tr>
				<tr>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_new_popular_small . "</td>
				<td style='width:2%' class='smallblacktext'>" . LAN_FORUM_0039 . " " . LAN_FORUM_1010 . "</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_nonew_popular_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_0040 . " " . LAN_FORUM_1010 . "</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_stickyclosed_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_1012 . "</td>
				<td style='vertical-align:middle; text-align:center; width:2%'>" . IMAGE_closed_small . "</td>
				<td style='width:10%' class='smallblacktext'>" . LAN_FORUM_1014 . "</td>
				</tr>
				</table>";

		}

		function sc_viewable_by()
		{
			global $forum, $forumId;

			if($users = $forum->getForumClassMembers($forumId))
			{
				$userList = array();
				$viewable = e107::getUserClass()->getFixedClassDescription($users);
				if(is_array($users))
				{
					foreach($users as $user)
					{
						$userList[] = "<a href='" . e107::getUrl()->create('user/profile/view', $user) . "'>" . $user['user_name'] . "</a>";
					}

					$viewable = implode(', ', $userList);;
				}
				elseif($users == 0)
				{
					$viewable = '';
				}
				/*--
					else
					{
						$viewable =  e107::getUserClass()->getFixedClassDescription($users);
					}
				--*/
			}

			/*--
			if(!empty($viewable))
			{

				return "

									<div class='panel panel-default' style='margin-top:10px'>
										<div class='panel-heading'>Viewable by</div>
											<div class='panel-body'>
												".$viewable."
											</div>
										</div>
									</div>
							";
			}
			//else
			//{
				return '';
			//}
			--*/
			//else
			//{
			//-- Possible candidate for wrapper????
						/*	WORKING WRAPPER
							$SC_WRAPPER['VIEWABLE_BY'] = "<div class='panel panel-default' style='margin-top:10px'><div class='panel-heading'>".LAN_FORUM_8012."</div><div class='panel-body'>{---}</div></div></div>";

						*/
			return empty($viewable) ? '' : $viewable;
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
			global $forum, $forumId;

			// ----- Perm Display ---

			$permDisplay = array();

			$permDisplay['topics'] = ($forum->checkPerm($forumId, 'thread')) ? LAN_FORUM_0043 : LAN_FORUM_0044;
			$permDisplay['post'] = LAN_FORUM_0046;
			$permDisplay['edit'] = LAN_FORUM_0048;

			if($forum->checkPerm($forumId, 'post'))
			{
				$permDisplay['post'] = LAN_FORUM_0045;
				$permDisplay['edit'] = LAN_FORUM_0047;
			}
			/*--
				else
				{
					$permDisplay['post'] =LAN_FORUM_0046;
					$permDisplay['edit'] = LAN_FORUM_0048;
				}
			--*/

			return implode("<span class='forum-perms-separator'><!-- --></span>", $permDisplay);

		}

		function sc_forumjump()
		{
			global $forum;
			$jumpList = $forum->forumGetAllowed('view');
			$text = "<form method='post' action='" . e_SELF . "'><p>" . LAN_FORUM_1017 . ": <select name='forumjump' class='tbox'>";
			//--	foreach($jumpList as $key => $val)
			foreach($jumpList as $val)
			{
				$text .= "\n<option value='" . e107::url('forum', 'forum', $val, 'full') . "'>" . $val['forum_name'] . "</option>";
			}
			$text .= "</select> <input class='btn btn-default btn-secondary button' type='submit' name='fjsubmit' value='" . LAN_GO . "' /></form>";
			return $text;
		}


		// FIXME - TOPLINK not used anymore?
		function sc_toplink()
		{
			return "<a href='" . e_SELF . '?' . e_QUERY . "#top' onclick=\"window.scrollTo(0,0);\">" . LAN_GO . '</a>';
		}

		function sc_subforums()
		{

			//  echo "subforums";

			// Initial ideia, to have a separate shortcode var ($subsc)....
			//global $forum, $forumId, $threadFrom, $view;
						global $forum, $forumId;
			//  	var_dump ($forumId);
			//  	var_dump (vartrue($forumId));
			//var_dump ($forum->forumGetSubs(vartrue($forum_id)));

			//  	var_dump ($FORUM_VIEW_SUB);
			//	$tp = e107::getParser();

			// Initial ideia, to have a separate shortcode var ($subsc)....
			//  $subsc = e107::getScBatch('viewforum', 'forum', 'viewsubforum');
			//var_dump ($subsc);

			//-- $forum_id ??????
			//--$subList = $forum->forumGetSubs(vartrue($forum_id));
			//--$subList = $forum->forumGetSubs(vartrue($forumId));
			$subList = $forum->forumGetSubs(false);

			//  	var_dump ($forum);

			if(is_array($subList) && isset($subList[$this->var['forum_parent']][$forumId]))
			{
			//-- $newflag_list ??????
			//--	$newflag_list = $forum->forumGetUnreadForums();
				$sub_info = '';
				global $FORUM_VIEW_SUB, $FORUM_VIEW_SUB_START, $FORUM_VIEW_SUB_END;
				foreach($subList[$this->var['forum_parent']][$forumId] as $subInfo)
				{

			//----	global $FORUM_VIEW_SUB, $gen, $newflag_list;
			//  	var_dump ($FORUM_VIEW_SUB);

			//--	$tp = e107::getParser();
			//	$tVars = new e_vars;

			//----	$forumName = $tp->toHTML($subInfo['forum_name'], true);
			//----	$tVars['SUB_FORUMTITLE'] = "<a href='".e107::getUrl()->create('forum/forum/view', $subInfo)."'>{$forumName}</a>";
			//----	$tVars['SUB_DESCRIPTION'] = $tp->toHTML($subInfo['forum_description'], false, 'no_hook');
			//----	$tVars['SUB_THREADS'] = $subInfo['forum_threads'];
			//----	$tVars['SUB_REPLIES'] = $subInfo['forum_replies'];

			//----	$badgeReplies = ($subInfo['forum_replies']) ? "badge-info" : "";
			//----	$badgeThreads = ($subInfo['forum_replies']) ? "badge-info" : "";

			//----	$tVars['SUB_THREADSX'] = "<span class='badge {$badgeThreads}'>".$subInfo['forum_threads']."</span>";
			//----	$tVars['SUB_REPLIESX'] = "<span class='badge {$badgeReplies}'>".$subInfo['forum_replies']."</span>";

			//	$tVars['REPLIESX'] = "<span class='badge badge-info'>".$thread_info['thread_total_replies']."</span>";
			//	$tVars['VIEWSX'] = "<span class='badge {$badge}'>".$thread_info['thread_views']."</span>";

					/*----
						if(USER && is_array($newflag_list) && in_array($subInfo['forum_id'], $newflag_list))
						{

							$tVars['NEWFLAG'] = "<a href='".e107::getUrl()->create('forum/forum/mfar', 'id='.$subInfo['forum_id'])."'>".IMAGE_new.'</a>';
						}
						else
						{
							$tVars['NEWFLAG'] = IMAGE_nonew;
						}
					----*/
					/*----
						if($subInfo['forum_lastpost_info'])
						{
							$tmp = explode('.', $subInfo['forum_lastpost_info']);
							$lp_thread = "<a href='".e107::getUrl()->create('forum/thread/last', array('id' => $tmp[1]))."'>".IMAGE_post2.'</a>';
							$lp_date = $gen->convert_date($tmp[0], 'forum');

							if($subInfo['user_name'])
							{
								$lp_name = "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $subInfo['forum_lastpost_user'], 'name' => $subInfo['user_name']))."'>{$subInfo['user_name']}</a>";
							}
							else
							{
								$lp_name = $subInfo['forum_lastpost_user_anon'];
							}
							$tVars['SUB_LASTPOST'] = $lp_date.'<br />'.$lp_name.' '.$lp_thread;

							$tVars['SUB_LASTPOSTDATE'] = $gen->computeLapse($tmp[0], time(), false, false, 'short');
							$tVars['SUB_LASTPOSTUSER'] = $lp_name;
						}
						else
						{
							$tVars['SUB_LASTPOST'] = '-';
							$tVars['SUB_LASTPOSTUSER'] = '';
							$tVars['SUB_LASTPOSTDATE'] = '';
						}
					----*/
				//----	$tVars['_WRAPPER_'] = 'forum_viewforum';
				//var_dump ($subInfo);

				// Initial ideia, to have a separate shortcode var ($subsc)....
				//				$subsc->setVars($subInfo);
				// Use setVars or addVars???
					$this->addVars($subInfo);
			//echo "--------------------------------------";

			// Initial ideia, to have a separate shortcode var ($subsc)....
			//	$sub_info .= e107::getParser()->parseTemplate($FORUM_VIEW_SUB, false,  $subsc);
					$sub_info .= e107::getParser()->parseTemplate($FORUM_VIEW_SUB, false, $this);

			//var_dump ($sc);


				}
			//var_dump ("----------->".$FORUM_VIEW_SUB_START.$sub_info.$FORUM_VIEW_SUB_END."<-----------");

				return $FORUM_VIEW_SUB_START . $sub_info . $FORUM_VIEW_SUB_END;
			}

			return '';

		}


			// Initial ideia, to have a separate shortcode var ($subsc)....
		/*------
		}

		class plugin_forum_viewsubforum_shortcodes extends plugin_forum_viewforum_shortcodes
		//-- or ???
		//--class plugin_forum_viewsubforum_shortcodes extends e_shortcode
		{

			function __construct()
			{
		//		$this->forum_rules = forum_rules('check');
			}
		------*/


		function sc_sub_forumimage($parms=null)
		{
			if(empty($this->var['forum_image'])) return '';
			if (!empty($parms) && !is_array($parms)) parse_str($parms, $parms);
			if (empty($parms)) $parms = array();
			$parms = array_merge(array('class'=>'img-fluid', 'h' => 50), $parms);
			$text = e107::getParser()->toImage($this->var['forum_image'], $parms);
			return "<a href='".e107::url('forum', 'forum', $this->var)."'>{$text}</a>&nbsp;";
		}

		function sc_sub_forumtitle()
		{
			$forumName = e107::getParser()->toHTML($this->var['forum_name'], true);
			return "<a href='" .  e107::url('forum', 'forum', $this->var) . "'>".$forumName."</a>";
		}


		function sc_sub_description()
		{
			return e107::getParser()->toHTML($this->var['forum_description'], false, 'no_hook');
		}


		function sc_sub_threads()
		{
			return $this->var['forum_threads'];
		}


		function sc_sub_replies()
		{
			return $this->var['forum_replies'];
		}


		function sc_sub_threadsx()
		{
			$badgeThreads = ($this->var['forum_replies']) ? "badge-info" : "";
			return "<span class='badge {$badgeThreads}'>" . $this->var['forum_threads'] . "</span>";
		}


		function sc_sub_repliesx()
		{
			$badgeReplies = ($this->var['forum_replies']) ? "badge-info" : "";
			return "<span class='badge {$badgeReplies}'>" . $this->var['forum_replies'] . "</span>";
		}


		function sc_newflag()
		{
//--	global $newflag_list;
			global $forum;
			$newflag_list = $forum->forumGetUnreadForums();
			/*--
					if(USER && is_array($newflag_list) && in_array($this->var['forum_id'], $newflag_list))
				{
					return "<a href='".e107::getUrl()->create('forum/forum/mfar', 'id='.$this->var['forum_id'])."'>".IMAGE_new.'</a>';
				}
					return IMAGE_nonew;
			--*/

		//	$url = e107::getUrl()->create('forum/forum/mfar', 'id=' . $this->var['forum_id']);
			$url = e107::url('forum', 'markread', $this->var);
			return (USER && is_array($newflag_list) && in_array($this->var['forum_id'], $newflag_list)) ? "<a href='" . $url . "'>" . IMAGE_new . '</a>' : IMAGE_nonew;

		}


		function sc__wrapper_() // TODO XXX ??
		{
			return 'forum_viewforum';
		}


		function subinfo()
		{
			$backtrace = debug_backtrace();
			$caller = $backtrace[1]['function'];
			if($this->var['forum_lastpost_info'])
			{
				//	global $gen;
				$tmp = explode('.', $this->var['forum_lastpost_info']);
			//	$lp_url = e107::getUrl()->create('forum/thread/last', array('id' => $tmp[1]));
				$lp_url = $threadUrl = e107::url('forum','topic',$this->var, array('query'=>array('last'=>1)));

				$lp_thread = "<a href='" . $lp_url . "'>" . IMAGE_post2 . '</a>';
				$lp_date = $this->gen->convert_date($tmp[0], 'forum');

				/*--
							$lp_name = $this->var['forum_lastpost_user_anon'];
						if($this->var['user_name'])
						{
							$lp_name = "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $this->var['forum_lastpost_user'], 'name' => $this->var['user_name']))."'>{$this->var['user_name']}</a>";
						}
				--*/
				/*--
						else
						{
							$lp_name = $this->var['forum_lastpost_user_anon'];
						}
				--*/
				$lp_name = ($this->var['user_name']) ? "<a href='" . e107::getUrl()->create('user/profile/view', array('id' => $this->var['forum_lastpost_user'], 'name' => $this->var['user_name'])) . "'>{$this->var['user_name']}</a>" : $this->var['forum_lastpost_user_anon'];
				/*----
						$tVars['SUB_LASTPOST'] = $lp_date.'<br />'.$lp_name.' '.$lp_thread;

						$tVars['SUB_LASTPOSTDATE'] = $gen->computeLapse($tmp[0], time(), false, false, 'short');
						$tVars['SUB_LASTPOSTUSER'] = $lp_name;
				----*/
				return ($caller == 'sc_sub_lastpostuser' ? $lp_name : ($caller == 'sc_sub_lastpostdate' ? $this->gen->computeLapse($tmp[0], time(), false, false, 'short') : ($caller == 'sc_sub_lastpost' ? $lp_date . '<br />' . $lp_name . ' ' . $lp_thread : '')));

			}
			/*----
				else
				{
					$tVars['SUB_LASTPOST'] = '-';
					$tVars['SUB_LASTPOSTUSER'] = '';
					$tVars['SUB_LASTPOSTDATE'] = '';
				}
			----*/
			return ($caller == 'sc_sub_lastpost' ? '-' : '');
		}


		function sc_sub_lastpostuser()
		{
			return $this->subinfo();
		}


		function sc_sub_lastpostdate()
		{
			return $this->subinfo();
		}


		function sc_sub_lastpost()
		{
			return $this->subinfo();
		}

// Initial ideia, to have a separate shortcode var ($threadsc)....
		/*------
		}

		class plugin_forum_viewforumthread_shortcodes extends plugin_forum_viewforum_shortcodes
		//-- or ???
		//--class plugin_forum_viewforumthread_shortcodes extends e_shortcode
		{

			function __construct()
			{
		//		$this->forum_rules = forum_rules('check');
			}
		------*/


		function sc_views($parm=null)
		{
			$val = ($this->var['thread_views']) ? $this->var['thread_views'] : '0' ;

			if(!empty($parm['raw']))
			{
				return $val;
			}

			return e107::getParser()->toBadge($val);
		}


		function sc_replies($parm=null)
		{
			$val = ($this->var['thread_total_replies']) ? $this->var['thread_total_replies'] : '0';

			if(!empty($parm['raw']))
			{
				return $val;
			}

			return e107::getParser()->toBadge($val);
		}


		function sc_viewsx($parm='')
		{
			return $this->sc_views($parm);
		}


		function sc_repliesx($parm='')
		{
			return $this->sc_replies($parm);
		}

//	function sc__wrapper_()	{	return 'forum_viewforum';}

		function threadlastpostdata()
		{
			$backtrace = debug_backtrace();
			$caller = $backtrace[1]['function'];
//	if($this->var['thread_views'])
//($this->var['thread_total_replies']?:"0")
			if($this->var['thread_views'] || $this->var['thread_total_replies'] > 0)
			{
//	global $gen;
//----		$lastpost_datestamp = $gen->convert_date($this->var['thread_lastpost'], 'forum');
				$LASTPOST = $LASTPOSTUSER = LAN_FORUM_1015;
				if($this->var['lastpost_username'])
				{
					// XXX hopefully & is not allowed in user name - it would break parsing of url parameters, change to array if something wrong happens
					$url = e107::getUrl()->create('user/profile/view', "name={$this->var['lastpost_username']}&id={$this->var['thread_lastuser']}");
//----			$tVars['LASTPOST'] = "<a href='{$url}'>".$this->var['lastpost_username']."</a>";
//----			$tVars['LASTPOSTUSER'] = "<a href='{$url}'>".$this->var['lastpost_username']."</a>";
					$LASTPOST = $LASTPOSTUSER = "<a href='{$url}'>" . $this->var['lastpost_username'] . "</a>";
				}
				/*--
						else
						{
							if(!$this->var['thread_lastuser'])
				--*/
				elseif(!$this->var['thread_lastuser'])
				{
//----				$tVars['LASTPOST'] = $tp->toHTML($this->var['thread_lastuser_anon']);
//----				$tVars['LASTPOSTUSER'] = $tp->toHTML($this->var['thread_lastuser_anon']);
					$LASTPOST = $LASTPOSTUSER = e107::getParser()->toHTML($this->var['thread_lastuser_anon']);
					/*--
								}

								else
								{
					//----				$tVars['LASTPOST'] = LAN_FORUM_1015;
					//----				$tVars['LASTPOSTUSER'] = LAN_FORUM_1015;
					//--$LASTPOST = $LASTPOSTUSER = LAN_FORUM_1015;
								}
					--*/
				}
//----		$tVars['LASTPOST'] .= '<br />'.$lastpost_datestamp;
				$LASTPOST .= '<br />' . $this->gen->convert_date($this->var['thread_lastpost'], 'forum');

//----		$tVars['LASTPOSTUSER'] = $this->var['lastpost_username']; // $lastpost_name;
				$LASTPOSTUSER = $this->var['lastpost_username'];

//----- ???????
				$temp['thread_sef'] = eHelper::title2sef($this->var['thread_name'], 'dashl');
				$this->addVars($temp);

				$urlData = array('forum_sef' => $this->var['forum_sef'], 'thread_id' => $this->var['thread_id'], 'thread_sef' => $this->var['thread_sef']);
				$url = e107::url('forum', 'topic', $urlData);
				$url .= (strpos($url, '?') !== false) ? '&' : '?';
				$url .= "last=1#post-" . $this->var['lastpost_id'];

//----		$tVars['LASTPOSTDATE'] .= "<a href='".$url."'>".  $gen->computeLapse($thread_info['thread_lastpost'],time(), false, false, 'short')."</a>";


				return ($caller == 'sc_lastpostuser' ? $LASTPOSTUSER : ($caller == 'sc_lastpostdate' ? "<a href='" . $url . "'>" . $this->gen->computeLapse($this->var['thread_lastpost'], time(), false, true, 'short') . "</a>" : ($caller == 'sc_lastpost' ? $LASTPOST : '')));

			}

			return ($caller == 'sc_lastpostuser' ? '' : '-');
		}


		function sc_lastpostuser()
		{
			return $this->threadlastpostdata();
		}


		function sc_lastpostdate()
		{
			return $this->threadlastpostdata();
		}


		function sc_lastpost()
		{
			return $this->threadlastpostdata();
		}


		function sc_threaddate()
		{
//	global $gen;
			return $this->gen->convert_date($this->var['thread_datestamp'], 'forum');
		}


		function sc_threadtimelapse()
		{
//	global $gen;
			return $this->gen->computeLapse($this->var['thread_datestamp'], time(), false, false, 'short'); //  convert_date($thread_info['thread_datestamp'], 'forum');
		}


		function sc_icon()
		{
			global $forum;
//	global $forum, $FORUM_VIEW_FORUM, $FORUM_VIEW_FORUM_STICKY, $FORUM_VIEW_FORUM_ANNOUNCE, $gen, $menu_pref, 
//$threadsViewed = $forum->threadGetUserViewed();
//	$newflag = (USER && $this->var['thread_lastpost'] > USERLV && !in_array($this->var['thread_id'], $threadsViewed));
			$newflag = (USER && $this->var['thread_lastpost'] > USERLV && !in_array($this->var['thread_id'], $forum->threadGetUserViewed()));
			$ICON = ($newflag ? IMAGE_new : IMAGE_nonew);
//-- CANDIDATE FOR TERNARY IF
			if($this->var['thread_total_replies'] >= $forum->prefs->get('popular', 10))
			{
				$ICON = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
			}
			elseif(empty($this->var['thread_total_replies']) && defined('IMAGE_noreplies'))
			{
				$ICON = IMAGE_noreplies;
			}

//-- CANDIDATE FOR TERNARY IF
			if($this->var['thread_sticky'] == 1)
			{
				$ICON = ($this->var['thread_active'] ? IMAGE_sticky : IMAGE_stickyclosed);
			}
			elseif($this->var['thread_sticky'] == 2)
			{
				$ICON = IMAGE_announce;
			}
			elseif(!$this->var['thread_active'])
			{
				$ICON = IMAGE_closed;
			}

			return $ICON;
		}


		function sc_threadtype()
		{
			//-- CANDIDATE FOR TERNARY IF
			if($this->var['thread_sticky'] == 1)
			{
				return '[' . LAN_FORUM_1011 . ']<br />';
			}
			elseif($this->var['thread_sticky'] == 2)
			{
				return '[' . LAN_FORUM_1013 . ']<br />';
			}

			return '';
		}


		/**
		 *
		 * @outdated use {TOPIC_TITLE} or {TOPIC_URL}
		 * @param null $parm
		 * @return string
		 */
		function sc_threadname($parm=null)
		{
			global $menu_pref, $forum;
			$tp = e107::getParser();

			$thread_name = strip_tags($tp->toHTML($this->var['thread_name'], false, 'no_hook, emotes_off'));
			if(isset($this->var['thread_options']['poll']))
			{
				$thread_name = '[' . LAN_FORUM_1016 . '] ' . $thread_name;
			}

		//	if (strtoupper($THREADTYPE) == strtoupper(substr($thread_name, 0, strlen($THREADTYPE))))
		//	{
		//		$thread_name = substr($thread_name, strlen($THREADTYPE));
		//	}
			$title = '';
			if($forum->prefs->get('tooltip'))
			{
				$thread_thread = strip_tags($tp->toHTML($this->var['thread_thread'], true, 'no_hook'));
				$tip_length = $forum->prefs->get('tiplength', 400);
				if(strlen($thread_thread) > $tip_length)
				{
					//$thread_thread = substr($thread_thread, 0, $tip_length).' '.$menu_pref['newforumposts_postfix'];
					$thread_thread = $tp->text_truncate($thread_thread, $tip_length, $menu_pref['newforumposts_postfix']);    // Doesn't split entities
				}
				$thread_thread = str_replace("'", '&#39;', $thread_thread);
				$title = "title='" . $thread_thread . "'";
			}
			/*--
				else
				{
					$title = '';
				}
			--*/
			// $tVars['THREADNAME'] = "<a {$title} href='".e107::getUrl()->create('forum/thread/view', array('id' => $threadId, 'name' => $thread_name))."'>{$thread_name}</a>";

			//	$url = e107::getUrl()->create('forum/thread/view', array('id' => $threadId, 'name' => $thread_name));

			//	$thread_info['thread_sef'] = eHelper::title2sef($this->var['thread_name'],'dashl');
			$temp['thread_sef'] = eHelper::title2sef($this->var['thread_name'], 'dashl');
			$this->addVars($temp);

			$url = e107::url('forum', 'topic', $this->var);

			if($parm === 'url')
			{
				return $url;
			}

			if($parm === 'title')
			{
				return $thread_name;
			}


			return "<a {$title} href='" . $url . "'>{$thread_name}</a>";
		}

		//v2.1.4
		function sc_topic_title($parm=null)
		{
			return $this->sc_threadname('title');
		}

		//v2.1.4
		function sc_topic_url($parm=null)
		{
			return $this->sc_threadname('url');
		}

		//v2.1.4
		function sc_topic_date($parm=null)
		{
			return $this->sc_threaddate();
		}


		//@todo more topic_xxxx shortcode aliases.



		function sc_pages()
		{
//	$tVars['PAGES'] = fpages($thread_info, $tVars['REPLIES']);
			$ret = fpages($this->var, $this->var['thread_total_replies']);

			if(!empty($ret))
			{
				return LAN_GOPAGE.": ".$ret;
			}

			return null;
		}


		function sc_pagesx()
		{
//	$tVars['PAGESX'] = fpages($thread_info, $tVars['REPLIES']);
			return $this->sc_pages();
		}


		function sc_admin_icons()
		{

			if(MODERATOR)
			{
				$threadId = $this->var['thread_id'];
				$forumId = $this->var['thread_forum_id'];
				// FIXME _URL_ thread name
				// e107::getUrl()->create('forum/forum/view', "id={$thread_info['thread_forum_id']}")
				// USED self instead

//		$moveUrl        = e107::url('forum','move', $this->var);

				return "
				<form method='post' action='" . e_REQUEST_URI . "' id='frmMod_{$forumId}_{$threadId}' style='margin:0;'><div class='forum-viewforum-admin-icons'>
				<input type='image' " . IMAGE_admin_delete . " name='deleteThread_{$threadId}' value='thread_action' onclick=\"return confirm_({$threadId})\" />
				" . ($this->var['thread_sticky'] == 1 ? "<input type='image' " . IMAGE_admin_unstick . " name='unstick_{$threadId}' value='thread_action' /> " : "<input type='image' " . IMAGE_admin_stick . " name='stick_{$threadId}' value='thread_action' /> ") . "
				" . ($this->var['thread_active'] ? "<input type='image' " . IMAGE_admin_lock . " name='lock_{$threadId}' value='thread_action' /> " : "<input type='image' " . IMAGE_admin_unlock . " name='unlock_{$threadId}' value='thread_action' /> ") . "
				<a class='e-tip' title=\"" . LAN_FORUM_5019 . "\" href='" . e107::url('forum', 'move', $this->var) . "'>" . IMAGE_admin_move . '</a>
				</div></form>
				';
			}
			return '';
		}


		function sc_adminoptions()
		{
			if(!deftrue('BOOTSTRAP'))
			{
				return $this->sc_admin_icons();
			}
			else if (MODERATOR)
			{
				return fadminoptions($this->var);
			}
			else
			{
				return '';
			}
		}


		function sc_poster()
		{
			/*--
				if ($this->var['user_name'])
				{
					return "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $this->var['thread_user'], 'name' => $this->var['user_name']))."'>".$this->var['user_name']."</a>";
				}
			//	else
			//	{
					elseif($this->var['thread_user_anon'])
					{
						return e107::getParser()->toHTML($this->var['thread_user_anon']);
					}
			//		else
			//		{
						return LAN_FORUM_1015;
			--*/
//		else
//		{
			return (($this->var['user_name']) ? "<a href='" . e107::getUrl()->create('user/profile/view', array('id' => $this->var['thread_user'], 'name' => $this->var['user_name'])) . "'>" . $this->var['user_name'] . "</a>" : (($this->var['thread_user_anon']) ? e107::getParser()->toHTML($this->var['thread_user_anon']) : LAN_FORUM_1015));
//		}
//	}
		}
////////////////////////////////////////////////
		/*

			function sc_sub_description()
			{
			return e107::getParser()->toHTML($this->var['forum_description'], false, 'no_hook');
		  }


			function sc_sub_threadsx()
			{
			$badgeThreads = ($this->var['forum_replies']) ? "badge-info" : "";
			return "<span class='badge {$badgeThreads}'>".$this->var['forum_threads']."</span>";
		  }

			function sc_sub_repliesx()
			{
			$badgeReplies = ($this->var['forum_replies']) ? "badge-info" : "";
			return "<span class='badge {$badgeReplies}'>".$this->var['forum_replies']."</span>";
		  }

			function sc_newflag()
			{
			global $newflag_list;
				if(USER && is_array($newflag_list) && in_array($this->var['forum_id'], $newflag_list))
			{
				return "<a href='".e107::getUrl()->create('forum/forum/mfar', 'id='.$this->var['forum_id'])."'>".IMAGE_new.'</a>';
			}
				return IMAGE_nonew;

		  }

		*/
		function sc_avatar($opts)
		{
			if(isset($this->var['thread_id']))
			{
				return e107::getParser()->toAvatar(e107::user($this->var['thread_lastuser']), $opts);
			}
			elseif(isset($this->var['forum_id']))
			{
				return e107::getParser()->toAvatar(e107::user($this->var['forum_lastpost_user']), $opts);
			}

			return '';
		}
	}


?>
