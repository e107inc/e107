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


	class plugin_forum_view_shortcodes extends e_shortcode
	{

		protected $e107;
		protected $defaultImgAttachSize = false;
		protected $pref;
		// $param is sent from nfp menu.

		function __construct()
		{
			parent::__construct();
			$this->e107 = e107::getInstance();
			$this->forum = new e107forum();
			$this->pref = e107::pref('forum');

			$this->defaultImgAttachSize = e107::pref('forum', 'maxwidth', false); // don't resize here if set to 0.
		}


		/**
		 * v2.1.5 - Start of Shortcode rewrite rewrite for use throughout all of the forum plugin..
		 * return 1 piece of data. (ie. no combining of titles and urls unless absolutely required)
		 * @param $this->var - table data.
		 * @param $this->param - dynamic control of shortcode via menu configuration.
		 * Only by nfp menu at this time.
		*/

	// @todo new post shortcodes

		function sc_post_url($parm=null)
		{
				$url = e107::url('forum', 'topic', $this->var, array(
					'query'    => array(
						'f' => 'post',
						'id'    => intval($this->var['post_id']) // proper page number
					),
				));

				return $url;
		}

		function sc_post_datestamp($parm=null)
		{
			return $this->sc_threaddatestamp('relative');
		}

		function sc_post_topic($parm=null)
		{
			return $this->sc_threadname($parm);
		}

		function sc_post_content($parm=null)
		{
			$tp = e107::getParser();
			$pref = e107::getPref();
			$post = strip_tags($tp->toHTML($this->var['post_entry'], true, 'emotes_off, no_make_clickable', '', $pref['menu_wordwrap']));
			$post = $tp->text_truncate($post, varset($this->param['nfp_characters'],120), varset($this->param['nfp_postfix'],'...'));

			return $post;
		}

		function sc_post_author_name($parm=null)
		{
			return $this->sc_poster();
		}

		function sc_post_author_avatar($parm=null)
		{
			return e107::getParser()->toAvatar($this->postInfo, $parm);
		//	return $this->sc_avatar($parm);
		}

	// @todo new thread/topic shortcodes

		function sc_topic_name($parm=null)
		{
			return $this->sc_threadname($parm);
		}


		function sc_topic_author_name($parm=null)
		{
			if($this->var['thread_user_username'])
			{
				return "<a href='" . e107::getUrl()->create('user/profile/view', array('name' => $this->postInfo['thread_user_username'], 'id' => $this->postInfo['thread_user'])) . "'>{$this->postInfo['thread_user_username']}</a>";
			}
			else
			{
				return '<b>' . e107::getParser()->toHTML($this->postInfo['thread_user_anon']) . '</b>';
			}
		}


		function sc_topic_author_url($parm=null)
		{
			if(empty($this->var['thread_user_username']) || empty($this->var['thread_user']))
			{
				return '';
			}

			return e107::getUrl()->create('user/profile/view', array('name' => $this->var['thread_user_username'], 'id' => $this->var['thread_user']));
		}


		function sc_topic_author_avatar($parm=null)
		{
			$arr = array(
				'user_id'           => $this->var['thread_user'],           // standardized field names.
				'user_name'         => $this->var['thread_user_username'],
				'user_image'        => $this->var['thread_user_userimage'],
				'user_currentvisit' => $this->var['thread_user_usercurrentvisit']
			);

			return e107::getParser()->toAvatar($arr, $parm);
		//	return $this->sc_avatar($parm);
		}




		function sc_topic_url($parm=null)
		{
			return e107::url('forum', 'topic', $this->var);
		}

		function sc_topic_views($parm=null)
		{
			$val = ($this->var['thread_views']) ? $this->var['thread_views'] : '0' ;
			return e107::getParser()->toBadge($val);
		}


		function sc_topic_replies($parm=null)
		{
			$val = ($this->var['thread_total_replies']) ? $this->var['thread_total_replies'] : '0';
			return e107::getParser()->toBadge($val);
		}

		/**
		 * @example {TOPIC_DATESTAMP: format=relative}
		 * @param string $parm['format'] short|long|forum|relative
		 * @return HTML
		 */
		function sc_topic_datestamp($parm=null)
		{
			$mode = empty($parm['format']) ? 'forum' : $parm['format'];
			return e107::getParser()->toDate($this->var['thread_datestamp'], $mode);
		}


		/**
		 * @example {TOPIC_LASTPOST_DATE: format=relative}
		 * @param string $parm['format'] short|long|forum|relative
		 * @return string
		 */
		function sc_topic_lastpost_date($parm=null)
		{
			if(empty($this->var['thread_total_replies']))
			{
				return '';
			}
			
		
			$mode = empty($parm['format']) ? 'forum' : $parm['format'];
			return e107::getParser()->toDate($this->var['thread_lastpost'], $mode);
		}


		function sc_topic_lastpost_author($parm=null)
		{

			if($this->var['thread_views'] && !empty($this->var['thread_total_replies']))
			{

				if($this->var['thread_lastuser_username'])
				{
					$url = e107::getUrl()->create('user/profile/view', "name={$this->var['thread_lastuser_username']}&id={$this->var['thread_lastuser']}");
					return "<a href='{$url}'>" . $this->var['thread_lastuser_username'] . "</a>";
				}
				elseif($this->var['thread_lastuser_anon'])
				{
					return e107::getParser()->toHTML($this->var['thread_lastuser_anon']);
				}
				else
				{
					return LAN_FORUM_1015;

				}
			}

			return ' - ';

		}


		function sc_topic_icon($parm=null)
		{

			$newflag = (USER && $this->var['thread_lastpost'] > USERLV && !in_array($this->var['thread_id'], $this->forum->threadGetUserViewed()));

			$ICON = ($newflag ? IMAGE_new : IMAGE_nonew);

			if($this->var['thread_total_replies'] >= vartrue($this->pref['popular'], 10))
			{
				$ICON = ($newflag ? IMAGE_new_popular : IMAGE_nonew_popular);
			}
			elseif(empty($this->var['thread_total_replies']) && defined('IMAGE_noreplies'))
			{
				$ICON = IMAGE_noreplies;
			}

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


	// @todo new forum shortcodes

		function sc_forum_name($parm=null)
		{
			if(substr($this->var['forum_name'], 0, 1) === '*')
			{
				$this->var['forum_name'] = substr($this->var['forum_name'], 1);
			}

			$this->var['forum_name'] = e107::getParser()->toHTML($this->var['forum_name'], true, 'no_hook');

			return $this->var['forum_name'];
		}

		function sc_forum_url($parm=null)
		{
			return e107::url('forum', 'forum', $this->var);
		}


		// More sc_topic_xxxxx and sc_forum_xxxx in the same format.

	// ---------------------------------------












		function sc_breadcrumb()
		{
			return $this->var['breadcrumb'];
		}

		function sc_backlink()
		{
			return $this->var['breadcrumb'];
		}

		function sc_top($parm = '')
		{
			$text = ($parm == 'caret') ? "<span class='caret'></span>" : LAN_FORUM_2030;

			return "<a href='" . e_SELF . '?' . e_QUERY . "#top' onclick=\"window.scrollTo(0,0);\">" . $text . '</a>';
		}

		function sc_joined()
		{

			$gen = e107::getDate();
			if($this->postInfo['post_user'])
			{
				return LAN_FORUM_2031 . ': ' . $gen->convert_date($this->postInfo['user_join'], 'forum') . '<br />';
			}
		}


		function sc_threaddatestamp($parm = '')
		{
			$gen = e107::getDateConvert(); // XXX _URL_ check if all required info is there

			if($parm == 'relative')
			{
				return $gen->computeLapse($this->postInfo['post_datestamp'], time(), false, false, 'short');
			}

			// XXX what is this line meant to do?
			// $text = "<a id='post_{$this->postInfo['post_id']}' href='".$this->e107->url->create('forum/thread/post', array('name' => $this->postInfo['thread_name'], 'thread' => $this->postInfo['post_thread'], 'id' => $this->postInfo['post_id']))."'>".IMAGE_post."</a> ";
			return $gen->convert_date($this->postInfo['post_datestamp'], 'forum');
		}

		function sc_postid()
		{
			return $this->postInfo['post_id'];
		}

		/* Preferred - as {POST} may conflict with other shortcodes */
		function sc_thread_text($parm=null)
		{
			return $this->sc_post($parm=null);
		}

		/**
		 * @DEPRECATED - use {THREAD_TEXT}
		 */
		function sc_post($parm)
		{
			//	return print_a($this->postInfo['post_entry'],true);
			$emote = (isset($this->postInfo['post_options']['no_emote']) ? ',emotes_off' : '');

			$uclass = (!empty($this->postInfo['user_class'])) ? $this->postInfo['user_class'] : 0;

			return e107::getParser()->toHTML($this->postInfo['post_entry'], true, 'USER_BODY' . $emote, 'class:' . $uclass);
		}

		function sc_postdeleted()
		{
			if($this->postInfo['post_status'])
			{
				$info = unserialize($this->postInfo['post_options']);

				return "
			" . LAN_FORUM_2037 . ": {$info['deldate']}<br />
			" . LAN_FORUM_2038 . ": {$info['delreason']}
			";
				//	$ret = '<pre>'.print_r($info, true).'</pre>';
			}
		}


		function sc_attachments($parm = array())
		{
			$tp = e107::getParser();

			if($this->postInfo['post_attachments'])
			{
				$baseDir = $this->forum->getAttachmentPath($this->postInfo['post_user']);

				$images = array();
				$txt = '';

				$attachArray = e107::unserialize($this->postInfo['post_attachments']);

				$thumbAtt = (!empty($this->defaultImgAttachSize)) ? array('w' => $this->defaultImgAttachSize, 'x' => 1) : null;

				//print_a($attachArray);

				foreach($attachArray as $type => $vals)
				{
					foreach($vals as $key => $file)
					{
						if(is_array($file))
						{

							$name = !empty($file['name']) ? $file['name'] : $file['file'];

							$file = $file['file'];
						}
						else
						{
							list($date, $user, $name) = explode("_", $file, 3);
						}

						switch($type)
						{
							case "file":

								$url = e_REQUEST_SELF . "?id=" . $this->postInfo['post_id'] . "&amp;dl=" . $key;

								$saveicon = (deftrue('BOOTSTRAP') === 4) ? 'fa-save' : 'icon-save.glyph'; 
                				$saveicon = e107::getParser()->toGlyph($saveicon,false);
								
								if(defset("BOOTSTRAP"))
								{
									$txt .= "<a class='forum-attachment-file btn btn-sm btn-default' href='" . $url . "'>" . $saveicon . " {$name}</a><br />";
								}
								else
								{
									$txt .= IMAGE_attachment . " <a href='" . $url . "'>{$name}</a><br />";
								}

								break;

							case 'img': //Always use thumb to hide the hash.


								//	return $baseDir.$file;
								if(file_exists($baseDir . $file))
								{
									$thumb = $tp->thumbUrl($baseDir . $file, $thumbAtt, true);
									$full = $tp->thumbUrl($baseDir . $file, 'w=1000&x=1', true);

									//TODO Use jQuery zoom instead.

									$caption = $name;

									$inc = (vartrue($parm['modal'])) ? "data-modal-caption=\"" . $caption . "\" data-target='#uiModal' " : "";
									$images[] = "<a  {$inc} rel='external' href='{$full}' class='forum-attachment-image e-modal' ><img class='thumbnail' src='{$thumb}' alt='' /></a>";
								}
								elseif(ADMIN)
								{
									$images[] = "Missing File: " . $baseDir . $file;
								}


								break;
						}

					}

				}

				if(count($images))
				{
					if(deftrue('BOOTSTRAP'))
					{

						return "<ul class='thumbnails list-unstyled list-inline'><li>" . implode("</li><li>", $images) . "</li></ul>" . vartrue($txt);
					}
					else
					{
						return implode("<br />", $images) . "<br />" . vartrue($txt);
					}
				}

				return $txt;
			}

		}

		function sc_privmessage()
		{
			if(e107::isInstalled('pm') && ($this->postInfo['post_user'] > 0))
			{
				return e107::getParser()->parseTemplate("{SENDPM={$this->postInfo['post_user']}}");
			}
		}

		function sc_avatar($opts)
		{
			return e107::getParser()->toAvatar($this->postInfo, $opts);
			// return $tp->parseTemplate("{USER_AVATAR=".$this->postInfo['user_image']."}", true);
		}

		function sc_anon_ip()
		{
			if($this->postInfo['post_user_anon'] && (ADMIN || MODERATOR))
			{
				return e107::getIPHandler()->ipDecode($this->postInfo['post_ip']);
			}
		}

		function sc_ip()
		{
			if((ADMIN || MODERATOR) && !$this->postInfo['user_admin'])
			{
				return e107::getIPHandler()->ipDecode($this->postInfo['post_ip']);
			}

		}

		function sc_poster()
		{
			if($this->postInfo['user_name'])
			{
				return "<a href='" . e107::getUrl()->create('user/profile/view', array('name' => $this->postInfo['user_name'], 'id' => $this->postInfo['post_user'])) . "'>{$this->postInfo['user_name']}</a>";
			}
			else
			{
				return '<b>' . e107::getParser()->toHTML($this->postInfo['post_user_anon']) . '</b>';
			}

		}

		function sc_emailimg()
		{
			if($this->postInfo['user_name'])
			{
				return (!$this->postInfo['user_hideemail'] ? e107::getParser()->parseTemplate("{EMAILTO={$this->postInfo['user_email']}}") : '');
			}

			return '';

		}

		function sc_emailitem()
		{
			if($this->postInfo['thread_start'])
			{
				return e107::getParser()->parseTemplate("{EMAIL_ITEM=" . LAN_FORUM_2044 . "^plugin:forum.{$this->postInfo['post_thread']}}");
			}
		}

		function sc_printitem()
		{
			if($this->postInfo['thread_start'])
			{
				return e107::getParser()->parseTemplate("{PRINT_ITEM=" . LAN_FORUM_2045 . "^plugin:forum.{$this->postInfo['post_thread']}}");
			}
		}

		function sc_signature($parm = '')
		{
			if(!USER)
			{
				return '';
			}
			global $forum;
			$tp = e107::getParser();
			static $forum_sig_shown;
			if($forum->prefs->get('sig_once'))
			{
				$_tmp = 'forum_sig_shown_' . $this->postInfo['post_user'];
				if(getcachedvars($_tmp))
				{
					return '';
				}
				cachevars($_tmp, 1);
			}

			if($parm == 'clean')
			{
				return ($this->postInfo['user_signature'] ? trim($tp->toHTML($this->postInfo['user_signature'], true)) : "");
			}


			return ($this->postInfo['user_signature'] ? "<br /><hr style='width:15%; text-align:left' /><span class='smalltext'>" . trim($tp->toHTML($this->postInfo['user_signature'], true)) . '</span>' : '');
		}

		function sc_profileimg()
		{
			if(USER && $this->postInfo['user_name'])
			{
				return e107::getParser()->parseTemplate("{PROFILE={$this->postInfo['post_user']}}");
			}
		}

		function sc_posts()
		{
			if($this->postInfo['post_user'])
			{
				return LAN_FORUM_2032 . ': ' . (int) $this->postInfo['user_plugin_forum_posts'] . '<br />';
			}
		}

		function sc_visits()
		{
			if($this->postInfo['user_name'])
			{
				return LAN_FORUM_2033 . ': ' . $this->postInfo['user_visits'] . '<br />';
			}
		}

		function sc_customtitle()
		{
			if($this->postInfo['user_customtitle'])
			{
				return e107::getParser()->toHTML($this->postInfo['user_customtitle']) . '<br />';
			}
		}

		function sc_website()
		{
			if(!empty($this->postInfo['user_homepage']))
			{
				return LAN_FORUM_2034 . ': ' . $this->postInfo['user_homepage'] . '<br />';
			}
		}

		function sc_websiteimg()
		{
			if($this->postInfo['user_homepage'] && $this->postInfo['user_homepage'] != 'http://')
			{
				return "<a href='{$this->postInfo['user_homepage']}'>" . IMAGE_website . '</a>';
			}
		}

		function sc_editimg()
		{
			if(USER && $this->postInfo['post_user'] == USERID && $this->thread->threadInfo['thread_active'])
			{
				$qry = array('f' => 'edit', 'id' => $this->postInfo['post_thread'], 'post' => $this->postInfo['post_id']);
				$editURL = e107::url('forum', 'post', null, array('query' => $qry));

				return "<a class='e-tip' href='" . $editURL . "' title=\"" . LAN_EDIT . "\">" . IMAGE_edit . '</a> ';
			}
		}

		function sc_quoteimg()
		{
			if($this->forum->checkperm($this->postInfo['post_forum'], 'post'))
			{
				$qry = array('f' => 'quote', 'id' => $this->postInfo['post_thread'], 'post' => $this->postInfo['post_id']);
				$quoteURL = e107::url('forum', 'post', null, array('query' => $qry));

				return "<a class='e-tip' href='" . $quoteURL . "' title=\"" . LAN_FORUM_2041 . "\">" . IMAGE_quote . '</a> ';
			}
		}

		function sc_reportimg()
		{
			if(USER)
			{
				$qry = array('f' => 'report', 'id' => $this->postInfo['post_thread'], 'post' => $this->postInfo['post_id']);
				$reportURL = e107::url('forum', 'post', null, array('query' => $qry));

				return "<a class='e-tip' href='" . $reportURL . "' title=\"" . LAN_FORUM_2046 . "\">" . IMAGE_report . '</a> ';
			}
		}

		function sc_rpg()
		{
			return rpg($this->postInfo['user_join'], $this->postInfo['user_plugin_forum_posts']);
		}

		function sc_memberid()
		{
			if(!$this->postInfo['post_user'])
			{
				return false;
			}

			return "<span class='smalltext'>" . LAN_FORUM_2035 . ' #' . $this->postInfo['post_user'] . '</span>';
		}

		function sc_level($parm)
		{

			if(isset($this->pref['ranks']) && empty($this->pref['ranks']))
			{
				return false;
			}


			if(!$this->postInfo['post_user'])
			{
				return '';
			}

			$rankInfo = e107::getRank()->getRanks($this->postInfo['post_user']);
			// FIXME - level handler!!!


			//	print_a($rankInfo);

			if($parm == 'badge')
			{
				return "<span class='label label-info'>" . $rankInfo['name'] . "</span>";
			}

			if(!$parm)
			{
				$parm = 'name';
			}


			switch($parm)
			{

				case 'userid' :
					return $this->sc_memberid();
					break;

				case 'special':
					if(isset($rankInfo['special']))
					{
						return $rankInfo['special'];
					}
					if($this->forum->isModerator($this->postInfo['post_user']))
					{
						return "<div class='spacer'>" . IMAGE_rank_moderator_image . '</div>';
					}

					return '';
					break;

				case 'glyph':
					$text = "";
					$tp = e107::getParser();
					for($i = 0; $i < $rankInfo['value']; $i++)
					{
						$text .= $tp->toGlyph('fa-star');
					}

					return $text;
					break;

				default:
					return varset($rankInfo[$parm], '');
					break;
			}
		}

		function sc_modoptions()
		{
			if(MODERATOR)
			{
				return showmodoptions();
			}
		}

		function sc_lastedit()
		{
			$gen = e107::getDate();
			if($this->postInfo['post_edit_datestamp'])
			{
				return $gen->convert_date($this->postInfo['post_edit_datestamp'], 'forum');
			}
		}

		function sc_lasteditby($parm = '')
		{
			if(isset($this->postInfo['edit_name']))
			{
				if($parm == 'link')
				{
					$url = e107::getUrl()->create('user/profile/view', array('name' => $this->postInfo['edit_name'], 'id' => $this->postInfo['post_edit_user']));

					return "<a href='{$url}'>{$this->postInfo['edit_name']}</a>";
				}

				return $this->postInfo['edit_name'];
			}
		}

		function sc_poll()
		{
			if($this->postInfo['thread_start'] == 1)
			{
				global $pollstr;

				return $pollstr;
			}
		}

		function sc_newflag()
		{
			// Defined in case an indicator is required
			return '';
		}

		function sc_usercombo()
		{

			$tp = e107::getParser();

			//	$text2 = $this->sc_level('special');
			//	$text .= $this->sc_level('pic');

			$ue = $tp->parseTemplate("{USER_EXTENDED=location.text_value}", true);
			$username = (empty($this->postInfo['user_name'])) ? LAN_ANONYMOUS : $this->postInfo['user_name'];

			$userUrl = empty($this->postInfo['post_user']) ? '#' : e107::getUrl()->create('user/profile/view', array('user_id' => $this->postInfo['post_user'], 'user_name' => $username));
			// e_HTTP.'user.php?id.'.$this->postInfo['post_user']
			$text = '<div class="btn-group ">

    <a class="btn btn-default btn-secondary btn-sm btn-small" href="' . $userUrl . '">' . $username . '</a>
    <button class="btn btn-default btn-secondary btn-sm btn-small dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu left">
    ';

			$text .= "<li><a href='#'>" . $this->sc_level('userid') . "</a></li>";
			$text .= "<li><a href='#'>" . $this->sc_joined() . "</a></li>";
			if($ue)
			{
				$text .= "<li><a hre='#'>" . $ue . "</a></li>";
			}
			$text .= "<li><a href='#'>" . $this->sc_posts() . "</a></li>";


			if(e107::isInstalled('pm') && ($this->postInfo['post_user'] > 0))
			{
				if($pmButton = $tp->parseTemplate("{SENDPM: user=" . $this->postInfo['post_user'] . "&glyph=envelope&class=pm-send}", true))
				{
					$text .= "<li class='divider'></li>";
					$text .= "<li>" . $pmButton . "</li>";
				}

				// $text .= "<li><a href='".e_PLUGIN_ABS."pm/pm.php?send.{$this->postInfo['post_user']}'>".$tp->toGlyph('envelope')." ".LAN_FORUM_2036." </a></li>";
			}

			if($website = $this->sc_website())
			{
				$text .= "<li>" . $website . "</li>";
			}

//	{EMAILIMG}
//	{WEBSITEIMG}

			$text .= "</ul>
	</div>";


			return $text;
		}


		function sc_postoptions()
		{
			$tp = e107::getParser();
			// {EMAILITEM} {PRINTITEM} {REPORTIMG}{EDITIMG}{QUOTEIMG}

			$text = '<div class="btn-group pull-right">
    	<button class="btn btn-default btn-secondary btn-sm btn-small dropdown-toggle" data-toggle="dropdown">
    	' . LAN_FORUM_8013 . '
    	<span class="caret"></span>
    	</button>
    	<ul class="dropdown-menu pull-right text-right">';


			$text .= "<li class='text-right'><a href='" . e_HTTP . "email.php?plugin:forum." . $this->postInfo['post_thread'] . "'>" . LAN_FORUM_2044 . " " . $tp->toGlyph('envelope') . "</a></li>";
			$text .= "<li class='text-right'><a href='" . e_HTTP . "print.php?plugin:forum." . $this->postInfo['post_thread'] . "'>" . LAN_FORUM_2045 . " " . $tp->toGlyph('print') . "</a></li>"; // FIXME

			if(USER) // Report
			{
				$urlReport = e107::url('forum', 'post') . "?f=report&amp;id=" . $this->postInfo['post_thread'] . "&amp;post=" . $this->postInfo['post_id'];
				//	$urlReport = $this->e107->url->create('forum/thread/report', "id={$this->postInfo['post_thread']}&post={$this->postInfo['post_id']}");
				$text .= "<li class='text-right'><a href='" . $urlReport . "'>" . LAN_FORUM_2046 . " " . $tp->toGlyph('flag') . "</a></li>";
			}

			// Edit
			if((USER && $this->postInfo['post_user'] == USERID && $this->thread->threadInfo['thread_active']))
			{


				$url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $this->postInfo['post_thread'] . "&amp;post=" . $this->postInfo['post_id'];
				//$url = e107::getUrl()->create('forum/thread/edit', array('id' => $this->postInfo['post_thread'], 'post'=>$this->postInfo['post_id']));
				$text .= "<li class='text-right'><a href='" . $url . "'>" . LAN_EDIT . " " . $tp->toGlyph('edit') . "</a></li>";

			}

			if($this->forum->checkperm($this->postInfo['post_forum'], 'post'))
			{
				$url = e107::url('forum', 'post') . "?f=quote&amp;id=" . $this->postInfo['post_thread'] . "&amp;post=" . $this->postInfo['post_id'];
				//$url = e107::getUrl()->create('forum/thread/quote', array('id' => $this->postInfo['post_thread'], 'post'=>$this->postInfo['post_id']));
				$text .= "<li class='text-right'><a href='" . $url . "'>" . LAN_FORUM_2041 . " " . $tp->toGlyph('share-alt') . "</a></li>";

				//	$text .= "<li class='text-right'><a href='".e107::getUrl()->create('forum/thread/quote', array('id' => $this->postInfo['post_id']))."'>".LAN_FORUM_2041." ".$tp->toGlyph('share-alt')."</a></li>";
			}


			if(MODERATOR)
			{
				$text .= "<li role='presentation' class='divider'> </li>";
				$type = ($this->postInfo['thread_start']) ? 'thread' : 'Post';

				//	print_a($this->postInfo);

				if((USER && $this->postInfo['post_user'] != USERID && $this->thread->threadInfo['thread_active']))
				{

					$url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $this->postInfo['post_thread'] . "&amp;post=" . $this->postInfo['post_id'];
					// $url = e107::getUrl()->create('forum/thread/edit', array('id' => $this->postInfo['post_thread'], 'post'=>$this->postInfo['post_id']));

					$text .= "<li class='text-right'><a href='" . $url . "'>" . LAN_EDIT . " " . $tp->toGlyph('edit') . "</a></li>";
				}

				// only show delete button when post is not the initial post of the topic
				//	if(!$this->forum->threadDetermineInitialPost($this->postInfo['post_id']))
				if(empty($this->postInfo['thread_start']))
				{
					$text .= "<li class='text-right'><a href='" . e_REQUEST_URI . "' data-forum-action='deletepost' data-forum-thread='" . $this->postInfo['post_thread'] . "' data-forum-post='" . $this->postInfo['post_id'] . "'>" . LAN_DELETE . " " . $tp->toGlyph('trash') . "</a></li>";
				}

				if($type == 'thread')
				{
					$url = e107::url('forum', 'move', array('thread_id' => $this->postInfo['post_thread']));
					$text .= "<li class='text-right'><a href='" . $url . "'>" . LAN_FORUM_2042 . " " . $tp->toGlyph('move') . "</a></li>";
				}
				elseif(e_DEVELOPER === true) //TODO
				{
					$text .= "<li class='text-right'><a href='" . e107::url('forum', 'split', array('thread_id' => $this->postInfo['post_thread'], 'post_id' => $this->postInfo['post_id'])) . "'>" . LAN_FORUM_2043 . " " . $tp->toGlyph('cut') . "</a></li>";

				}
			}


			$text .= '
		</ul>
		</div>';

			return $text;


		}

//---- SHORTCODES CONVERTED FROM $tVars....
		function sc_threadname($parm=null)
		{
			return e107::getParser()->toHTML($this->var['thread_name'], true, 'no_hook, emotes_off');
		}

		function sc_nextprev()
		{
			global $forum, $thread;
			$prev = $forum->threadGetNextPrev('prev', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
			$next = $forum->threadGetNextPrev('next', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);

			$options = array();

			if($prev !== false)
			{
				$options[] = "<a class='btn btn-default btn-secondary btn-sm btn-small' href='" . e107::url('forum', 'topic', $prev) . "'>&laquo; " . LAN_FORUM_2001 . "</a>";
			}
			if($next !== false)
			{
				$options[] = "<a class='btn btn-default btn-secondary btn-sm btn-small' href='" . e107::url('forum', 'topic', $next) . "'>" . LAN_FORUM_2002 . " &raquo;</a>";
			}

//----	$tVars->NEXTPREV = implode(" | ", $options);
			return implode(" | ", $options);
		}


		function sc_track()
		{
			global $forum;
			if($forum->prefs->get('track') && USER)
			{
				// BC Fix for old template.
				if(!defined('IMAGE_track'))
				{
					define('IMAGE_track', '<img src="' . img_path('track.png') . '" alt="' . LAN_FORUM_4009 . '" title="' . LAN_FORUM_4009 . '" class="icon S16 action" />');
				}

				if(!defined('IMAGE_untrack'))
				{
					define('IMAGE_untrack', '<img src="' . img_path('untrack.png') . '" alt="' . LAN_FORUM_4010 . '" title="' . LAN_FORUM_4010 . '" class="icon S16 action" />');
				}


				$img = ($this->var['track_userid'] ? IMAGE_track : IMAGE_untrack);


				/*
					$url = $e107->url->create('forum/thread/view', array('id' => $thread->threadId), 'encode=0'); // encoding could break AJAX call

					$url = e107::url('forum','index');

					$tVars->TRACK .= "
							<span id='forum-track-trigger-container'>
							<a class='btn btn-default btn-sm btn-small e-ajax' data-target='forum-track-trigger' href='{$url}' id='forum-track-trigger'>{$img}</a>
							</span>
							<script type='text/javascript'>
							e107.runOnLoad(function(){
								$('forum-track-trigger').observe('click', function(e) {
									e.stop();
									new e107Ajax.Updater('forum-track-trigger-container', '{$url}', {
										method: 'post',
										parameters: { //send query parameters here
											'track_toggle': 1
										},
										overlayPage: $(document.body)
									});
								});
							}, document, true);
							</script>
					";*/


				$trackDiz = ($forum->prefs->get('trackemail', true)) ? LAN_FORUM_3040 : LAN_FORUM_3041;

//	$tVars->TRACK = "<a id='forum-track-button' href='#' title=\"".$trackDiz."\" data-token='".deftrue('e_TOKEN','')."' data-forum-insert='forum-track-button'  data-forum-post='".$thread->threadInfo['thread_forum_id']."' data-forum-thread='".$thread->threadInfo['thread_id']."' data-forum-action='track' name='track' class='e-tip btn btn-default' >".$img."</a>";
				return "<a id='forum-track-button' href='#' title=\"" . $trackDiz . "\" data-token='" . deftrue('e_TOKEN', '') . "' data-forum-insert='forum-track-button'  data-forum-post='" . $this->var['thread_forum_id'] . "' data-forum-thread='" . $this->var['thread_id'] . "' data-forum-action='track' name='track' class='e-tip btn btn-default' >" . $img . "</a>";

			}

			return '';
		}

		function sc_moderators()
		{
			global $forum;

			$modUser = array();
			foreach($forum->modArray as $user)
			{
				$modUser[] = "<a href='" . e107::getUrl()->create('user/profile/view', $user) . "'>" . $user['user_name'] . "</a>";
			}

//$tVars->MODERATORS = LAN_FORUM_2003.": ". implode(', ', $modUser);
			return LAN_FORUM_2003 . ": " . implode(', ', $modUser);
//unset($modUser);
		}

		function sc_threadstatus()
		{
//$tVars->THREADSTATUS = (!$thread->threadInfo['thread_active'] ? LAN_FORUM_2004 : '');
			return (!$this->var['thread_active'] ? LAN_FORUM_2004 : '');
		}


		function sc_gotopages()
		{
			global $thread;
			if($thread->pages > 1)
			{
				if(!$thread->page)
				{
					$thread->page = 1;
				}

				// issue #3171 old method produced an invalid url: /forum/subforum/35/forum-topic/&p=2
				// moved additional parameter p= to the options/query array
				$url = e107::url('forum', 'topic', $this->var, array('query' => array('p' => '--FROM--'))); // . "&amp;p=[FROM]";

				$parms = "total={$thread->pages}&type=page&current={$thread->page}&url=" . urlencode($url) . "&caption=off&tmpl=default&navcount=4&glyphs=1";

				return e107::getParser()->parseTemplate("{NEXTPREV={$parms}}");
			}
		}


		function sc_buttons()
		{
			global $forum, $thread;
//----$tVars->BUTTONS = '';
			if($forum->checkPerm($this->var['thread_forum_id'], 'post') && $this->var['thread_active'])
			{
				// print_a($thread->threadInfo);
				$url = e107::url('forum', 'post') . "?f=rp&amp;id=" . $this->var['thread_id'] . "&amp;post=" . $thread->threadId;

//	$url = $e107->url->create('forum/thread/reply', array('id' => $thread->threadId));
				return "<a href='" . $url . "'>" . IMAGE_reply . "</a>";
			}
			if($forum->checkPerm($this->var['thread_forum_id'], 'thread'))
			{
				$ntUrl = e107::url('forum', 'post') . "?f=nt&amp;id=" . $this->var['thread_forum_id'];

//	$ntUrl = $e107->url->create('forum/thread/new', array('id' => $thread->threadInfo['thread_forum_id']));
				return "<a href='" . $ntUrl . "'>" . IMAGE_newthread . "</a>";
			}

			return '';
		}

//$tVars->BUTTONSX = forumbuttons($thread);

		function sc_buttonsx()
		{
			global $forum, $thread;

			if($forum->checkPerm($this->var['thread_forum_id'], 'post') && $this->var['thread_active'])
			{
				$url = e107::url('forum', 'post') . "?f=rp&amp;id=" . $this->var['thread_id'] . "&amp;post=" . $thread->threadId;
				//	$url = e107::getUrl()->create('forum/thread/reply', array('id' => $thread->threadId));
			}
			$replyUrl = "<a class='btn btn-primary" . ($url ? "" : " disabled") . "' "
				. ($url ? "" : " data-toggle='tooltip' title='" . LAN_FORUM_0046 . "'
	style='cursor: not-allowed; pointer-events: all !important;'") . " href='" . ($url ?: "#") . "'>" . LAN_FORUM_2006 . "</a>" . ($url ? "" : "<span>&nbsp;</span>");

			if($forum->checkPerm($this->var['thread_forum_id'], 'post'))
			{
				$ntUrl = e107::url('forum', 'post') . "?f=nt&amp;id=" . $this->var['thread_forum_id'];
				//	$ntUrl = e107::getUrl()->create('forum/thread/new', array('id' => $thread->threadInfo['thread_forum_id']));
				$options[] = " <a  href='" . $ntUrl . "'>" . LAN_FORUM_2005 . "</a>";
			}

//	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2001."</a>";
//	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2002."</a>";

//---- SIMILAR CODE AS SC_NEXTPREV!!!!!!!
			$prev = $forum->threadGetNextPrev('prev', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
			$next = $forum->threadGetNextPrev('next', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);

			if($prev !== false)
			{
				$options[] = "<a href='" . e107::url('forum', 'topic', $prev) . "'>" . LAN_FORUM_1017 . " " . LAN_FORUM_2001 . "</a>";
			}
			if($next !== false)
			{
				$options[] = "<a href='" . e107::url('forum', 'topic', $next) . "'>" . LAN_FORUM_1017 . " " . LAN_FORUM_2002 . "</a>";
			}


			/*
			$text = '<div class="btn-group">
			   '.($replyUrl?:"").'
				<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
				'.($replyUrl?"":LAN_FORUM_1003." ".LAN_FORUM_8013).'<span class="caret"></span>
				<span class="sr-only">Toggle Dropdown</span>
				</button>
				<ul class="dropdown-menu pull-right">
				';
			*/
			$text = '<div class="btn-group">
   ' . $replyUrl . '
    <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
    <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu pull-right">
    ';

			foreach($options as $key => $val)
			{
				$text .= '<li>' . $val . '</li>';
			}

			$jumpList = $forum->forumGetAllowed();

			$text .= "<li class='divider'></li>";

			foreach($jumpList as $key => $val)
			{
				$text .= '<li><a href ="' . e107::url('forum', 'forum', $val) . '">' . LAN_FORUM_1017 . " " . $val['forum_name'] . '</a></li>';
			}

			$text .= '
    </ul>
    </div>';


			return $text;
		}

		/*---- Function redeclared, this one came directly from forum_viewtopic.php.....
		function sc_poll()
		{
			global $pollstr;
		return vartrue($pollstr);
		}
		----*/
		function sc_forumjump()
		{
			global $forum;
			$jumpList = $forum->forumGetAllowed();
			$text = "<form method='post' action='" . e_SELF . "'><p>" . LAN_FORUM_1017 . ": <select name='forumjump' class='tbox'>";
//--	foreach ($jumpList as $key => $val)
			foreach($jumpList as $val)
			{
				$text .= "\n<option value='" . e107::url('forum', 'forum', $val) . "'>" . $val['forum_name'] . "</option>";
			}
			$text .= "</select> <input class='btn btn-default btn-secondary button' type='submit' name='fjsubmit' value='" . LAN_GO . "' /></p></form>";

			return $text;
		}

		function sc_message()
		{
			global $thread;

			return $thread->message;
		}

		function sc_quickreply()
		{
			global $forum, $forum_quickreply;

			if($forum->checkPerm($this->var['thread_forum_id'], 'post') && $this->var['thread_active'])
			{
				//XXX Show only on the last page??
				if(!vartrue($forum_quickreply))
				{
					$ajaxInsert = ($thread->pages == $thread->page || $thread->pages == 0) ? 1 : 0;
					//	$ajaxInsert = 1;
					//	echo "AJAX-INSERT=".$ajaxInsert ."(".$thread->pages." vs ".$thread->page.")";
//Orphan $frm variable????		$frm = e107::getForm();

					$urlParms = array('f' => 'rp', 'id' => $this->var['thread_id'], 'post' => $this->var['thread_id']);
					$url = e107::url('forum', 'post', null, array('query' => $urlParms));; // ."?f=rp&amp;id=".$thread->threadInfo['thread_id']."&amp;post=".$thread->threadInfo['thread_id'];

					$qr = e107::getPlugPref('forum', 'quickreply', 'default');
					if ($qr == 'default')
					{

						return "
						<form action='" . $url . "' method='post'>
						<div class='form-group'>
							<textarea cols='80' placeholder='" . LAN_FORUM_2007 . "' rows='4' id='forum-quickreply-text' class='tbox input-xxlarge form-control' name='post' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'></textarea>
						</div>
						<div class='center text-center form-group'>
							<input type='submit' data-token='" . e_TOKEN . "' data-forum-insert='" . $ajaxInsert . "' data-forum-post='" . $this->var['thread_forum_id'] . "' data-forum-thread='" . $this->var['thread_id'] . "' data-forum-action='quickreply' name='reply' value='" . LAN_FORUM_2006 . "' class='btn btn-success button' />
							<input type='hidden' name='thread_id' value='" . $this->var['thread_id'] . "' />
						</div>
	
						</form>";
					}
					else
					{
						$editor = $this->forum->prefs->get('editor');
						$editor = is_null($editor) ? 'default' : $editor;
						$text = "
						<form action='" . $url . "' method='post'>
						<div class='form-group'>" .
						e107::getForm()->bbarea('post','','forum', '_common', 'small', array('id' => 'forum-quickreply-text', 'wysiwyg' => $editor)) .
						"</div>
						<div class='center text-center form-group'>
							<input type='submit' data-token='" . e_TOKEN . "' data-forum-insert='" . $ajaxInsert . "' data-forum-post='" . $this->var['thread_forum_id'] . "' data-forum-thread='" . $this->var['thread_id'] . "' data-forum-action='quickreply' name='reply' value='" . LAN_FORUM_2006 . "' class='btn btn-success button' />
							<input type='hidden' name='thread_id' value='" . $this->var['thread_id'] . "' />
						</div>
	
						</form>";

						return $text;
					}

					if(E107_DEBUG_LEVEL > 0)
					{
						//	echo "<div class='alert alert-info'>Thread id: ".$threadId."</div>";
						//	print_a($this);
					}


					// Preview should be reserved for the full 'Post reply' page. <input type='submit' name='fpreview' value='" . Preview . "' /> &nbsp;
				}
//----	else
//----	{
				return $forum_quickreply;
//----	}
			}
		}

	}


?>
