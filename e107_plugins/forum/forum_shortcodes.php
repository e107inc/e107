<?php
if (!defined('e107_INIT')) { exit; }

/*
$codes = array(
'top','joined','threaddatestamp','post','postdeleted','attachments','privmessage',
'avatar','anon_ip','ip','poster','emailimg','emailitem','signature','profileimg',
'posts','visits','customtitle','website','websiteimg','editimg','quoteimg','reportimg',
'rpg','memberid','level','modoptions','lastedit','lasteditby','poll','newflag'
);
*/

$codes = array();
$tmp = get_class_methods('forum_shortcodes');
foreach($tmp as $c)
{
	if(strpos($c, 'get_') === 0) 
	{
		$codes[] = substr($c, 4);
	}
}
register_shortcode('forum_shortcodes', $codes);

class forum_shortcodes
{

	var $e107;
	
	function forum_shortcodes()
	{
		$this->e107 = e107::getInstance();
	}

	function get_top()
	{
		return "<a href='".e_SELF.'?'.e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".LAN_10.'</a>';
	}

	function get_joined()
	{
		global $postInfo, $gen;
		if ($postInfo['post_user'])
		{
			return LAN_06.': '.$gen->convert_date($postInfo['user_join'], 'forum').'<br />';
		}
	}

	function get_threaddatestamp()
	{
		global $postInfo, $gen;
		return "<a id='post_{$post_info['post_id']}' href='".$this->e107->url->getUrl('forum', 'thread', array('func' => 'post', 'id' => $postInfo['post_id']))."'>".IMAGE_post."</a> ".$gen->convert_date($postInfo['post_datestamp'], 'forum');
	}

	function get_post()
	{
		global $postInfo;
		$emote = (isset($postInfo['post_options']['no_emote']) ? ',emotes_off' : '');
		return $this->e107->tp->toHTML($postInfo['post_entry'], true, 'USER_BODY'.$emote, 'class:'.$post_info['user_class']);
	}

	function get_postdeleted()
	{
		global $postInfo;
		if($postInfo['post_status'])
		{
			$info = unserialize($postInfo['post_options']);
			return  "
			Post delete on: {$info['deldate']}<br />
			reason: {$info['delreason']}
			";
			$ret = '<pre>'.print_r($info, true).'</pre>';
		}
	}

	function get_attachments()
	{
		global $postInfo;
		if($postInfo['post_attachments'])
		{
			$attachments = explode(',', $postInfo['post_attachments']);
			$txt = '';
			foreach($attachments as $a)
			{
				$info = explode('*', $a);
				switch($info[0])
				{
					case 'file':
					$txt .= IMAGE_attachment." <a href='".e_PLUGIN_ABS."forum/attachments/{$info[1]}'>{$info[2]}</a><br />";
					break;

					case 'img':
					//if image has a thumb, show it and link to main
					if(isset($info[2]))
					{
						$txt .= "<a href='".e_PLUGIN_ABS."forum/attachments/{$info[1]}'><img src='".e_PLUGIN_ABS."forum/attachments/thumb/{$info[2]}' alt='' /></a>";
					}
					else
					{
						$txt .= "<img src='".e_PLUGIN_ABS."forum/attachments/{$info[1]}' alt='' />";
					}
				}
			}
			return $txt;
		}

	}

	function get_privmessage()
	{
		global $postInfo;
		if(plugInstalled('pm') && ($postInfo['post_user'] > 0))
		{
			return $this->e107->tp->parseTemplate("{SENDPM={$postInfo['post_user']}}");
		}
	}

	function get_avatar()
	{
		global $postInfo;
		if ($postInfo['post_user'])
		{
			if(!$avatar = getcachedvars('forum_avatar_'.$postInfo['post_user']))
			{
				if ($postInfo['user_image'])
				{
					require_once(e_HANDLER.'avatar_handler.php');
					$avatar = "<div class='spacer'><img src='".avatar($postInfo['user_image'])."' alt='' /></div><br />";
				}
				else
				{
					$avatar = '';
				}
				cachevars('forum_avatar_'.$postInfo['post_user'], $avatar);
			}
			return $avatar;
		}
		return '';

	}

	function get_anon_ip()
	{
		global $postInfo;
		if($postInfo['post_user_anon'] && (ADMIN || MODERATOR))
		{
			return $this->e107->ipDecode($postInfo['post_ip']);
		}
	}

	function get_ip()
	{
		global $postInfo;
		if((ADMIN || MODERATOR) && !$postInfo['user_admin'])
		{
			return $this->e107->ipDecode($postInfo['post_ip']);
		}

	}

	function get_poster()
	{
		global $postInfo;
		if($postInfo['user_name'])
		{
			return "<a href='".$this->e107->url->getUrl('core:user', 'main', array('func' => 'profile', 'id' => $postInfo['post_user']))."'>{$postInfo['user_name']}</a>";
		}
		else
		{
			return '<b>'.$this->e107->tp->toHTML($postInfo['post_user_anon']).'</b>';
		}

	}

	function get_emailimg()
	{
		global $postInfo;
		if($postInfo['user_name'])
		{
			return (!$post_info['user_hideemail'] ? $this->e107->tp->parseTemplate("{EMAILTO={$postInfo['user_email']}}") : '');
		}
		return '';

	}

	function get_emailitem()
	{
		if($postInfo['thread_start'])
		{
			return $this->e107->tp->parseTemplate("{EMAIL_ITEM=".FORLAN_101."^plugin:forum.{$postInfo['post_thread']}}");
		}
	}

	function get_printitem()
	{
		global $postInfo;
		if($postInfo['thread_start'])
		{
			return $this->e107->tp->parseTemplate("{PRINT_ITEM=".FORLAN_102."^plugin:forum.{$postInfo['post_thread']}}");
		}
	}

	function get_signature()
	{
		if(!USER) { return ''; }
		global $postInfo, $pref;
		static $forum_sig_shown;
		if(varsettrue($pref['forum_sig_once']))
		{
			$_tmp = 'forum_sig_shown_'.$postInfo['post_user'];
			if(getcachedvars($_tmp)) { return ''; }
			cachevars($_tmp, 1);
		}
		return ($postInfo['user_signature'] ? "<br /><hr style='width:15%; text-align:left' /><span class='smalltext'>".$this->e107->tp->toHTML($postInfo['user_signature'], true).'</span>' : '');

	}

	function get_profileimg()
	{
		global $postInfo;
		if (USER && $postInfo['user_name'])
		{
			return $this->e107->tp->parseTemplate("{PROFILE={$postInfo['post_user']}}");
		}
	}

	function get_posts()
	{
		global $postInfo;
		if ($postInfo['post_user'])
		{
			return LAN_67.': '.(int)$postInfo['user_plugin_forum_posts'].'<br />';
		}
	}

	function get_visits()
	{
		global $postInfo;
		if ($postInfo['user_name'])
		{
			return LAN_09.': '.$postInfo['user_visits'].'<br />';
		}
	}

	function get_customtitle()
	{
		global $postInfo;
		if ($postInfo['user_customtitle'])
		{
			return $this->e107->tp->toHTML($postInfo['user_customtitle']).'<br />';
		}
	}

	function get_website()
	{
		global $postInfo;
		if ($postInfo['user_homepage']) {
			return LAN_08.': '.$postInfo['user_homepage'].'<br />';
		}
	}

	function get_websiteimg()
	{
		global $postInfo;
		if ($postInfo['user_homepage'] && $postInfo['user_homepage'] != 'http://')
		{
			return "<a href='{$postInfo['user_homepage']}'>".IMAGE_website.'</a>';
		}
	}

	function get_editimg()
	{
		global $postInfo, $threadInfo;
		if (USER && $postInfo['post_user'] == USERID && $threadInfo['thread_active'])
		{
			return "<a href='".$this->e107->url->getUrl('forum', 'thread', array('func' => 'edit', 'id' => $postInfo['post_id']))."'>".IMAGE_edit.'</a> ';
		}
	}

	function get_quoteimg()
	{
		global $postInfo, $forum;
		if($forum->checkperm($postInfo['post_forum'], 'post'))
		{
			return "<a href='".$this->e107->url->getUrl('forum', 'thread', array('func' => 'quote', 'id' => $postInfo['post_id']))."'>".IMAGE_quote.'</a> ';
		}
	}

	function get_reportimg()
	{
		global $postInfo, $page;
		if (USER) {
			return "<a href='".$this->e107->url->getUrl('forum', 'thread', 'func=report&id='.$postInfo['post_thread'])."'>".IMAGE_report.'</a> ';
		}
	}

	function get_rpg()
	{
		global $postInfo;
		return rpg($postInfo['user_join'],$postInfo['user_plugin_forum_posts']);
	}

	function get_memberid()
	{
		global $post_info, $ldata, $pref, $forum_info;
		if ($post_info['anon']) { return ''; }

		$fmod = ($post_info['user_class'] != '' && check_class($forum_info['forum_moderators'], $post_info['user_class'], TRUE));
		if(!$fmod && $forum_info['forum_moderators'] == e_UC_ADMIN)
		{
			$fmod = $post_info['user_admin'];
		}
		if (!array_key_exists($post_info['user_id'],$ldata)) {
			$ldata[$post_info['user_id']] = get_level($post_info['user_id'], $post_info['user_forums'], $post_info['user_comments'], $post_info['user_chats'], $post_info['user_visits'], $post_info['user_join'], $post_info['user_admin'], $post_info['user_perms'], $pref, $fmod);
		}
		return $ldata[$post_info['user_id']][0];

	}

	function get_level()
	{
		global $post_info, $ldata, $pref, $forum_info;
		if ($post_info['anon']) { return ''; }
		$fmod = ($post_info['user_class'] != "" && check_class($forum_info['forum_moderators'], $post_info['user_class'], TRUE));
		if(!$fmod && $forum_info['forum_moderators'] == e_UC_ADMIN)
		{
			$fmod = $post_info['user_admin'];
		}
		if (!array_key_exists($post_info['user_id'],$ldata)) {
			$ldata[$post_info['user_id']] = get_level($post_info['user_id'], $post_info['user_forums'], $post_info['user_comments'], $post_info['user_chats'], $post_info['user_visits'], $post_info['user_join'], $post_info['user_admin'], $post_info['user_perms'], $pref, $fmod);
		}
		if($parm == 'pic')
		{
			return $ldata[$post_info['user_id']]['pic'];
		}
		if($parm == 'name')
		{
			return $ldata[$post_info['user_id']]['name'];
		}
		if($parm == 'special')
		{
			return $ldata[$post_info['user_id']]['special'];
		}
		if($parm == 'userid')
		{
			return $ldata[$post_info['user_id']]['userid'];
		}
		return $ldata[$post_info['user_id']][1];

	}

	function get_modoptions()
	{
		if (MODERATOR)
		{
			return showmodoptions();
		}
	}

	function get_lastedit()
	{
		global $postInfo, $gen;
		if ($postInfo['post_edit_datestamp'])
		{
			return $gen->convert_date($postInfo['post_edit_datestamp'],'forum');
		}
	}

	function get_lasteditby()
	{
		global $postInfo;
		if(isset($postInfo['edit_name']))
		{
			if($parm == 'link')
			{
				$e107 = e107::getInstance();
				$url = $e107->url->getUrl('core:user', 'main', 'func=profile&id='.$postInfo['post_edit_user']);
				return "<a href='{$url}'>{$postInfo['edit_name']}</a>";
			}
			return $postInfo['edit_name'];
		}
	}

	function get_poll()
	{
		global $pollstr;
		return $pollstr;
	}

	function get_newflag()
	{
		// Defined in case an indicator is required
		return '';
	}
}
?>