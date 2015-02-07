<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - view shortcodess
 *
*/

if (!defined('e107_INIT')) { exit; }

class plugin_forum_view_shortcodes extends e_shortcode
{
	protected $e107;

	function __construct()
	{
		parent::__construct();
		$this->e107 = e107::getInstance();
		$this->forum = 	new e107forum();
	}

	function sc_top($parm='')
	{
		$text = ($parm == 'caret') ?  "<span class='caret'></span>" : LAN_FORUM_2030;
		
		return "<a href='".e_SELF.'?'.e_QUERY."#top' onclick=\"window.scrollTo(0,0);\">".$text.'</a>';
	}

	function sc_joined()
	{

		$gen = e107::getDate();
		if ($this->postInfo['post_user'])
		{
			return LAN_FORUM_2031.': '.$gen->convert_date($this->postInfo['user_join'], 'forum').'<br />';
		}
	}

	/**
	 * What does this do?
	 */
	function sc_threaddatestamp($parm='')
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
	function sc_thread_text()
	{
		return $this->sc_post();	
	}

	/**
	 * @DEPRECATED - use {THREAD_TEXT}
	 */
	function sc_post()
	{
	//	return print_a($this->postInfo['post_entry'],true);
		$emote = (isset($this->postInfo['post_options']['no_emote']) ? ',emotes_off' : '');
		return e107::getParser()->toHTML($this->postInfo['post_entry'], true, 'USER_BODY'.$emote, 'class:'.$this->postInfo['user_class']);
	}

	function sc_postdeleted()
	{
		if($this->postInfo['post_status'])
		{
			$info = unserialize($this->postInfo['post_options']);
			return  "
			".LAN_FORUM_2037.": {$info['deldate']}<br />
			".LAN_FORUM_2038.": {$info['delreason']}
			";
			$ret = '<pre>'.print_r($info, true).'</pre>';
		}
	}
	

	

	function sc_attachments()
	{
		$tp = e107::getParser();
		
		if($this->postInfo['post_attachments'])
		{
			$baseDir = $this->forum->getAttachmentPath($this->postInfo['post_user']);

			$images = array();
			$txt = '';
		
			$attachArray = e107::unserialize($this->postInfo['post_attachments']);
			//print_a($attachArray); 
			foreach($attachArray as $type=>$vals)
			{
				foreach($vals as $key=>$file)
				{
					list($date,$user, $name) = explode("_", $file, 3); 

					switch($type)
					{
						case 'file':
					
							$url = e_SELF."?id=".$this->postInfo['post_id']."&amp;dl=".$key;
							$txt .= IMAGE_attachment." <a href='".$url."'>{$name}</a><br />";

						break;

						case 'img': //Always use thumb to hide the hash. 
						
						//	return $baseDir.$file; 
							if(file_exists($baseDir.$file))
							{
								$thumb = $tp->thumbUrl($baseDir.$file,'x=1',true);
								$full = $tp->thumbUrl($baseDir.$file,'w=1000&x=1', true);
							
								$inc = (vartrue($parm['modal'])) ? "data-toggle='modal' data-target='#".$parm['modal']."' " : "";
								$images[] = "<a  {$inc} rel='external' href='{$full}'><img class='thumbnail' src='{$thumb}' alt='' /></a>";	
							}
							elseif(ADMIN)
							{
								$images[] = "Missing File: ".$baseDir.$file;
							}
						
							
							
						break;
					}	
					
				}	
				
			}
			
			if(count($images))
			{
				if(deftrue('BOOTSTRAP')) 
				{

					return "<ul class='thumbnails list-unstyled list-inline'><li>".implode("</li><li>",$images)."</li></ul>".vartrue($txt); 
				}
				else
				{
					return implode("<br />",$images)."<br />".vartrue($txt);	
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

	function sc_avatar()
	{
		return e107::getParser()->toAvatar($this->postInfo);
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
			return "<a href='".$this->e107->url->create('user/profile/view', array('name' => $this->postInfo['user_name'], 'id' => $this->postInfo['post_user']))."'>{$this->postInfo['user_name']}</a>";
		}
		else
		{
			return '<b>'.e107::getParser()->toHTML($this->postInfo['post_user_anon']).'</b>';
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
			return e107::getParser()->parseTemplate("{EMAIL_ITEM=".LAN_FORUM_2044."^plugin:forum.{$this->postInfo['post_thread']}}");
		}
	}

	function sc_printitem()
	{
		if($this->postInfo['thread_start'])
		{
			return e107::getParser()->parseTemplate("{PRINT_ITEM=".LAN_FORUM_2045."^plugin:forum.{$this->postInfo['post_thread']}}");
		}
	}

	function sc_signature($parm='')
	{
		if(!USER) { return ''; }
		global $forum;
		$tp = e107::getParser();
		static $forum_sig_shown;
		if($forum->prefs->get('sig_once'))
		{
			$_tmp = 'forum_sig_shown_'.$this->postInfo['post_user'];
			if(getcachedvars($_tmp)) { return ''; }
			cachevars($_tmp, 1);
		}
		
		if($parm == 'clean')
		{
			return ($this->postInfo['user_signature'] ? trim($tp->toHTML($this->postInfo['user_signature'], true)) : "");
		}
		
		
		return ($this->postInfo['user_signature'] ? "<br /><hr style='width:15%; text-align:left' /><span class='smalltext'>".trim($tp->toHTML($this->postInfo['user_signature'], true)).'</span>' : '');
	}

	function sc_profileimg()
	{
		if (USER && $this->postInfo['user_name'])
		{
			return e107::getParser()->parseTemplate("{PROFILE={$this->postInfo['post_user']}}");
		}
	}

	function sc_posts()
	{
		if ($this->postInfo['post_user'])
		{
			return LAN_FORUM_2032.': '.(int)$this->postInfo['user_plugin_forum_posts'].'<br />';
		}
	}

	function sc_visits()
	{
		if ($this->postInfo['user_name'])
		{
			return LAN_FORUM_2033.': '.$this->postInfo['user_visits'].'<br />';
		}
	}

	function sc_customtitle()
	{
		if ($this->postInfo['user_customtitle'])
		{
			return e107::getParser()->toHTML($this->postInfo['user_customtitle']).'<br />';
		}
	}

	function sc_website()
	{
		if ($this->postInfo['user_homepage']) {
			return LAN_FORUM_2034.': '.$this->postInfo['user_homepage'].'<br />';
		}
	}

	function sc_websiteimg()
	{
		if ($this->postInfo['user_homepage'] && $this->postInfo['user_homepage'] != 'http://')
		{
			return "<a href='{$this->postInfo['user_homepage']}'>".IMAGE_website.'</a>';
		}
	}

	function sc_editimg()
	{
		if (USER && $this->postInfo['post_user'] == USERID && $this->thread->threadInfo['thread_active'])
		{
			return "<a href='".$this->e107->url->create('forum/thread/edit', array('id' => $this->postInfo['post_id']))."'>".IMAGE_edit.'</a> ';
		}
	}

	function sc_quoteimg()
	{
		if($this->forum->checkperm($this->postInfo['post_forum'], 'post'))
		{
			return "<a href='".$this->e107->url->create('forum/thread/quote', array('id' => $this->postInfo['post_id']))."'>".IMAGE_quote.'</a> ';
		}
	}

	function sc_reportimg()
	{
		global $page;
		if (USER) {
			return "<a href='".$this->e107->url->create('forum/thread/report', "id={$this->postInfo['post_thread']}&post={$this->postInfo['post_id']}")."'>".IMAGE_report.'</a> ';
		}
	}

	function sc_rpg()
	{
		return rpg($this->postInfo['user_join'], $this->postInfo['user_plugin_forum_posts']);
	}

	function sc_memberid()
	{
		if (!$this->postInfo['post_user']) { return FALSE; }
		return "<span class='smalltext'>".LAN_FORUM_2035.' #'.$this->postInfo['post_user'].'</span>';
	}

	function sc_level($parm)
	{
		if (!$this->postInfo['post_user']) { return ''; }

		$rankInfo = e107::getRank()->getRanks($this->postInfo['post_user']);
		// FIXME - level handler!!!
		
		if($parm == 'badge')
		{
			return "<span class='label label-info'>".$rankInfo['name']."</span>";	
		}
		
		if(!$parm) { $parm = 'name'; }


		switch($parm)
		{

			case 'userid' :
				return $this->sc_memberid();
				break;

			case 'special':
				if(isset($rankInfo['special'])) { return $rankInfo['special']; }
				if($this->forum->isModerator($this->postInfo['post_user']))
				{
					return "<div class='spacer'>".IMAGE_rank_moderator_image.'</div>';
				}
				return '';
				break;

			default:
				return varset($rankInfo[$parm], '');
				break;
		}
	}

	function sc_modoptions()
	{
		if (MODERATOR)
		{
			return showmodoptions();
		}
	}

	function sc_lastedit()
	{
		$gen = e107::getDate();
		if ($this->postInfo['post_edit_datestamp'])
		{
			return $gen->convert_date($this->postInfo['post_edit_datestamp'],'forum');
		}
	}

	function sc_lasteditby()
	{		if(isset($this->postInfo['edit_name']))
		{
			if($parm == 'link')
			{
				$e107 = e107::getInstance();
				$url = $e107->url->create('user/profile/view', array('name' => $this->postInfo['edit_name'], 'id' => $this->postInfo['post_edit_user']));
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
		
		$ue = $tp->parseTemplate("{USER_EXTENDED=location.text_value}",true);	
		$username = $this->postInfo['user_name'];
								
		$text = '<div class="btn-group ">

    <a class="btn btn-default btn-small" href="'.e_BASE.'user.php?id.'.$this->postInfo['post_user'].'">'.$username.'</a>
    <button class="btn btn-default btn-small dropdown-toggle" data-toggle="dropdown">
    <span class="caret"></span>
    </button>
    <ul class="dropdown-menu left">
    ';		
	
	$text .= "<li><a href='#'>".$this->sc_level('userid')."</a></li>";
	$text .=  "<li><a href='#'>".$this->sc_joined()."</a></li>";
	if($ue)
	{
		$text .= "<li><a hre='#'>".$ue."</a></li>";
	}
	$text .= "<li><a href='#'>".$this->sc_posts()."</a></li>";
	
	$text .= "<li class='divider'></li>";
	
	if(e107::isInstalled('pm') && ($this->postInfo['post_user'] > 0))
	{
		$text .= "<li><a href='".e_PLUGIN_ABS."pm/pm.php?send.{$this->postInfo['post_user']}'>".$tp->toGlyph('envelope')." ".LAN_FORUM_2036." </a></li>";
	}
	
	if($website = $this->sc_website())
	{
		$text .= "<li>".$website."</li>";	
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
    	<button class="btn btn-default btn-small dropdown-toggle" data-toggle="dropdown">
    	Options
    	<span class="caret"></span>
    	</button>
    	<ul class="dropdown-menu pull-right text-right">';
			
    	
		$text .= "<li class='text-right'><a href='".e_HTTP."email.php?plugin:forum.".$this->postInfo['post_thread']."'>".LAN_FORUM_2044." ".$tp->toGlyph('envelope')."</a></li>"; 
		$text .= "<li class='text-right'><a href='".e_HTTP."print.php?plugin:forum.".$this->postInfo['post_thread']."'>".LAN_FORUM_2045." ".$tp->toGlyph('print')."</a></li>"; // FIXME
	
		if (USER) // Report
		{
			$text .= "<li class='text-right'><a href='".$this->e107->url->create('forum/thread/report', "id={$this->postInfo['post_thread']}&post={$this->postInfo['post_id']}")."'>".LAN_FORUM_2046." ".$tp->toGlyph('flag')."</a></li>";
		}
	
		// Edit
		if ( (USER && $this->postInfo['post_user'] == USERID && $this->thread->threadInfo['thread_active']))
		{
			$text .= "<li class='text-right'><a href='".e107::getUrl()->create('forum/thread/edit', array('id' => $this->postInfo['post_id']))."'>".LAN_EDIT." ".$tp->toGlyph('edit')."</a></li>";
			
		}
	
		if($this->forum->checkperm($this->postInfo['post_forum'], 'post'))
		{
			$text .= "<li class='text-right'><a href='".e107::getUrl()->create('forum/thread/quote', array('id' => $this->postInfo['post_id']))."'>".LAN_FORUM_2041." ".$tp->toGlyph('share-alt')."</a></li>";
		}
	
	
		if (MODERATOR)
		{
			$text .= "<li role='presentation' class='divider'> </li>";
			$type = ($this->postInfo['thread_start']) ? 'thread' : 'Post';

			if ((USER && $this->postInfo['post_user'] != USERID && $this->thread->threadInfo['thread_active']))
			{
				$text .= "<li class='text-right'><a href='".e107::getUrl()->create('forum/thread/edit', array('id' => $this->postInfo['post_id']))."'>".LAN_EDIT." ".$tp->toGlyph('edit')."</a></li>";
			}
			
			// only show delete button when post is not the initial post of the topic
			if(!$this->forum->threadDetermineInitialPost($this->postInfo['post_id'])) 
			{
				$text .= "<li class='text-right'><a href='".e_REQUEST_URI."' data-forum-action='deletepost' data-forum-post='".$this->postInfo['post_id']."'>".LAN_DELETE." ".$tp->toGlyph('trash')."</a></li>"; 
			}
		
			if ($type == 'thread')
			{
				$text .= "<li class='text-right'><a href='" . e107::getUrl()->create('forum/thread/move', array('id' => $this->postInfo['post_id']))."'>".LAN_FORUM_2042." ".$tp->toGlyph('move')."</a></a></li>"; 
			}
			else
			{
				$text .= "<li class='text-right'><a href='" . e107::getUrl()->create('forum/thread/split', array('id' => $this->postInfo['post_id']))."'>".LAN_FORUM_2043." ".$tp->toGlyph('cut')."</a></li>";
		
			}
		}


	
		$text .= '
		</ul>
		</div>';
		
		return $text;
		
		
	}
	
	
	
}
?>