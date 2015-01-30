<?php
/*
 * Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 *
 * Forum plugin - post shortcodess
 *
*/

if (!defined('e107_INIT')) { exit; }

class plugin_forum_post_shortcodes extends e_shortcode
{
	protected $e107;

	function __construct()
	{
		parent::__construct();
		$this->e107 = e107::getInstance();
	}

	function sc_latestposts($parm)
	{
		$parm = ($parm ? $parm : 10);
		global $LATESTPOSTS_START, $LATESTPOSTS_END, $LATESTPOSTS_POST;
		$tp = e107::getParser();

		$txt = $tp->parseTemplate($LATESTPOSTS_START, true);
		$start = max($this->threadInfo['thread_total_replies'] - $parm, 0);
		$num = min($this->threadInfo['thread_total_replies'], $parm);

		$tmp = $this->forum->postGet($this->threadInfo['thread_id'], $start, $num);

		$bach = e107::getScBatch('view', 'forum');
		for($i = count($tmp); $i > 0; $i--)
		{
			$bach->setScVar('postInfo', $tmp[$i-1]);
			$txt .= $tp->parseTemplate($LATESTPOSTS_POST, true);
		}
		$txt .= $tp->parseTemplate($LATESTPOSTS_END, true);
		return $txt;
	}

	function sc_threadtopic()
	{
		global $THREADTOPIC_REPLY;
		$tmp = $this->forum->postGet($this->threadInfo['thread_id'], 0, 1);
		e107::getScBatch('view', 'forum')->setScVar('postInfo', $tmp[0]);
		return e107::getParser()->parseTemplate($THREADTOPIC_REPLY, true);
	}

	function sc_formstart()
	{
		return "<form enctype='multipart/form-data' method='post' action='".e_REQUEST_URL."' id='dataform'>";
	}

	function sc_formend()
	{
	return '</form>';
	}

	function sc_forumjump()
	{
		return forumjump();
	}

	function sc_userbox()
	{
		global $userbox;
		return (USER == false ? $userbox : '');
	}

	function sc_subjectbox()
	{
		global $subjectbox, $action;
		return ($action == 'nt' ? $subjectbox : '');
	}

	function sc_posttype()
	{
		global $action;
		return ($action == 'nt' ? LAN_FORUM_2015 : LAN_FORUM_2006);
	}

	function sc_postbox()
	{
		global $post;
		return e107::getForm()->bbarea('post',$post,'forum');
		
		
		$rows = (e107::wysiwyg()==true) ? 15 : 10;
		$ret = "<textarea class='e-wysiwyg tbox form-control' id='post' name='post' cols='70' rows='{$rows}' style='width:95%' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>$post</textarea>\n<br />\n";
	//	if(!e_WYSIWYG)
		{
	//		$ret .= display_help('helpb', 'forum');
		}
		return $ret;
	}

	function sc_buttons()
	{
		global $action, $eaction;
		$ret = "<input class='btn btn-default button' type='submit' name='fpreview' value='".LAN_FORUM_3005."' /> ";
		if ($action != 'nt')
		{
			$ret .= ($eaction ? "<input class='btn btn-primary button' type='submit' name='update_reply' value='".LAN_FORUM_3024."' />" : "<input class='btn btn-primary button' type='submit' name='reply' value='".LAN_FORUM_2006."' />");
		}
		else
		{
			$ret .= ($eaction ? "<input class='btn btn-primary button' type='submit' name='update_thread' value='".LAN_FORUM_3023."' />" : "<input class='btn btn-primary button' type='submit' name='newthread' value='".LAN_FORUM_2005."' />");
		}
		return $ret;
	}

	function sc_fileattach()
	{
		global $forum, $fileattach, $fileattach_alert;

		if ($forum->prefs->get('attach') && (check_class($pref['upload_class']) || getperms('0')))
		{
			if (is_writable(e_PLUGIN.'forum/attachments'))
			{
				return $fileattach;
			}
			else
			{
				$FILEATTACH = '';
				if(ADMIN)
				{
					if(!$fileattach_alert)
					{
						$fileattach_alert = "<tr><td colspan='2' class='nforumcaption2'>".(e107::getPref('image_post') ? LAN_FORUM_3012 : LAN_FORUM_3013)."</td></tr><tr><td colspan='2' class='forumheader3'>".str_replace('[x]', e_FILE."public", LAN_FORUM_3021)."</td></tr>\n";
					}
					return $fileattach_alert;
				}
			}
		}
	}
	
	
	function sc_forumattachment()
	{
		$pref = e107::getPref();
		global $forum;
		
		//. <div>".($pref['image_post'] ? "Attach file / image" : "Attach file")."</div>
		
		//$tooltip = "Allowed file types | ".vartrue($allowed_filetypes).". Any other file type will be deleted instantly. Maximum file size: ".(vartrue($max_upload_size) ? $max_upload_size."bytes" : ini_get('upload_max_filesize'));
		$tooltip = LAN_FORUM_3016.": ".vartrue($allowed_filetypes)." <br />".LAN_FORUM_3017."<br />".LAN_FORUM_3018.": ".(vartrue($max_upload_size) ? $max_upload_size." ".LAN_FORUM_3019 : ini_get('upload_max_filesize')); // FIXME <br /> in tooltip, no value $allowed_filetypes on v2/bootstrap

		$fileattach = "
			<div>	
				<div id='fiupsection'>
				<span id='fiupopt'>
					<input class='tbox e-tip' title=\"".$tooltip."\" name='file_userfile[]' type='file' size='47' />
				</span>
				</div>
				<input class='btn btn-default button' type='button' name='addoption' value=".LAN_FORUM_3020." onclick=\"duplicateHTML('fiupopt','fiupsection')\" />
			</div>
		
		";	
		
		if ($forum->prefs->get('attach') && (check_class($pref['upload_class']) || getperms('0')))
		{
			return $fileattach;
		}
		
	}
	
	function sc_postoptions($parm='')
	{
		$type = $this->sc_postthreadas();
		$poll 	= $this->sc_poll('front');
		$attach = $this->sc_forumattachment();
		
		$text = "
		<ul class='nav nav-tabs'>
		<li class='active'><a href='#type' data-toggle='tab'>".LAN_FORUM_3025."</a></li>";
		
		$text .= ($poll) ? "<li><a href='#poll' data-toggle='tab'>".LAN_FORUM_1016."</a></li>\n" : "";
		$text .= ($attach) ? "<li><a href='#attach' data-toggle='tab'>".LAN_FORUM_3012."</a></li>\n" : "";
		
		$text .= "
		</ul>
				<div class='tab-content text-left'>
					<div class='tab-pane active' id='type'>
						<div class='control-group'>
							<label class='control-label'>".LAN_FORUM_3026."</label>
							<div class='controls'>
								".$type."
							</div>
						</div>
					</div>
				";
				
		if($poll)
		{
			$text .= "<div class='tab-pane' id='poll'>
						".$poll."
					</div>";	
			
		}
					
		if($attach)
		{
			$text .= "
			<div class='tab-pane' id='attach'>
						".$attach."
					</div>";	
		}			
										
		$text .= "			
		</div>";
		
			
		return $text;
		
		
		
	}
	

	function sc_poll($parm='')
	{		
		global $forum,  $action;

		if(is_readable(e_PLUGIN."poll/poll_class.php")) 
		{
			require_once(e_PLUGIN."poll/poll_class.php");
			$pollo = new poll;
			$type = ($parm == 'front') ? 'front' : 'forum';
			
			$poll_form = $pollo -> renderPollForm($type);
		}


		if ($action == 'nt' && check_class($forum->prefs->get('poll')) && strpos(e_QUERY, 'edit') === false)
		{
			if($parm == 'front')
			{
				return $poll_form;	
			}
			
			
			return "<tr><td><a href='#pollform' class='e-expandit'>".LAN_FORUM_3028."</a></td><td>
			<div id='pollform' style='display:none'>
			<table class='table table-striped'>".$poll_form."</table></div></td></tr>";
		}
		return '';
	}


	function sc_postthreadas()
	{
		global $action, $threadInfo;
		
		if (MODERATOR && $action == "nt")
		{
			$thread_sticky = (isset($_POST['threadtype']) ? $_POST['threadtype'] : vartrue($threadInfo['thread_sticky'],0)); // no reference of 'head' $threadInfo['head']['thread_sticky']
				
			$opts = array(0 => LAN_FORUM_3038, 1 => LAN_FORUM_1011, 2 => LAN_FORUM_1013); 
				
			return e107::getForm()->radio('threadtype',$opts, $thread_sticky);
			
		//	return "<br /><span class='defaulttext'>post thread as 
		//	<input name='threadtype' type='radio' value='0' ".(!$thread_sticky ? "checked='checked' " : "")." />".LAN_1."&nbsp;<input name='threadtype' type='radio' value='1' ".($thread_sticky == 1 ? "checked='checked' " : "")." />".LAN_2."&nbsp;<input name='threadtype' type='radio' value='2' ".($thread_sticky == 2 ? "checked='checked' " : "")." />".LAN_3."</span>";
		}
		return '';
	}

	function sc_backlink()
	{
		global $forum, $threadInfo, $eaction, $action;
		$_tmp = new e_vars();
		// no reference of 'head' $threadInfo['head']['thread_name']
		$forum->set_crumb(true, ($action == 'nt' ? ($eaction ? LAN_FORUM_3023 : LAN_FORUM_1018) : ($eaction ? LAN_FORUM_3024 : $threadInfo['thread_name'])), $_tmp);
		return $_tmp->BREADCRUMB;
	}

	function sc_noemotes()
	{
		if(vartrue($eaction) == true) { return ; }
		return "<input type='checkbox' name='no_emote' value='1' />&nbsp;<span class='defaulttext'>".LAN_FORUM_3039.'</span>';
	}

	function sc_emailnotify()
	{

		
		global $threadInfo, $action, $eaction;

		$pref = e107::getPlugPref('forum');

		if($eaction == true) { return ; }
		if (vartrue($pref['notify']) && $action == 'nt' && USER)
		{
			if(isset($_POST['fpreview']))
			{
				$chk = ($_POST['notify'] ? "checked = 'checked'" : '');
			}
			else
			{
				if(isset($threadInfo))
				{
					// no reference of 'head' $threadInfo['head']['thread_active']
					$chk = ($threadInfo['thread_active'] == 99 ? "checked='checked'" : '');
				}
				else
				{
					$chk = ($pref['notify_on'] ? "checked='checked'" : '');
				}
			}
			return "<br /><input type='checkbox' name='notify' value='1' {$chk} />&nbsp;<span class='defaulttext'>".LAN_FORUM_3040."</span>";
		}
		return '';
	}



}
?>