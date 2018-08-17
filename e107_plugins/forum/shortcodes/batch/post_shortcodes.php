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

	function sc_latestposts($parm) //TODO  move elsewhere?
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

	function sc_forum_post_form_start()
	{
		return "<form class='form-horizontal' enctype='multipart/form-data' method='post' action='".e_REQUEST_URL."' id='dataform'>";
	}

	function sc_forum_post_form_end()
	{
		$frm = e107::getForm();
		return $frm->hidden('action',$this->var['action']).$frm->close();
	}

	function sc_forumjump()
	{
		$jumpList = $this->forum->forumGetAllowed('view');
		$text = "<form class='form-inline' method='post' action='".e_REQUEST_URI."'><div class='btn-group'><p>".LAN_FORUM_1017.": <select name='forumjump' class='tbox form-control'>";
		foreach($jumpList as $key => $val)
		{
			$text .= "\n<option value='".e107::url('forum','forum', $val)."'>".$val['forum_name']."</option>";
		}
		$text .= "</select><input class='btn btn-default btn-secondary button' type='submit' name='fjsubmit' value='".LAN_GO."' /></p></div></form>";

		return $text;


		// return forumjump(); // FIXME - broken in v1 themes
	}

	function sc_userbox()
	{
		global $userbox;
		return (USER == false ? $userbox : '');
	}

	function sc_forum_post_author()
	{
		$opts = array('size' => 'xlarge');
		$tp = e107::getParser();

		if(USER == false)
		{
			$val = $tp->post_toForm($_POST['anonname']);
		}
		else
		{
			$val = USERNAME;
			$opts['disabled'] = true;
		}

		return e107::getForm()->text('anonname',$val, 20, $opts);


		// <input class='tbox form-control' type='text' name='anonname' size='71' value='".vartrue($anonname)."' maxlength='20' style='width:95%' />


	}

	function sc_subjectbox()
	{
		global $subjectbox;
		return ($this->var['action'] == 'nt' ? $subjectbox : '');
	}

	function sc_forum_post_subject()
	{
		$opts = array('size' => 'xlarge');

		if($this->var['action'] =='rp' || $this->var['action'] =='quote')
		{
			$_POST['subject'] = "Re: ". $this->var['thread_name'];
			$opts['disabled'] = 1;
		}
		elseif($this->var['action'] == 'edit')
		{
			$_POST['subject'] = $this->var['thread_name'];
			if($this->var['thread_user'] != USERID && !deftrue('MODERATOR') || !$this->var['initial_post'])
			{
				$opts['disabled'] = 1;
			}
		}
		else
		{
			$opts['required'] = 1;
		}
	//	elseif($this->var['action'] == 'edit')
	//	{
	//		$_POST['subject'] = $this->varp;
	//	}

		$tp = e107::getParser();
		return e107::getForm()->text('subject',$tp->post_toForm($_POST['subject']), 100, $opts);


	//	<input class='tbox form-control' type='text' name='subject' size='71' value='".vartrue($subject)."' maxlength='100' style='width:95%' />

	}

	function sc_forum_post_textarea_label()
	{
		return ($this->var['action'] == 'nt' ? LAN_FORUM_2015 : LAN_FORUM_2006);
	}

	function sc_forum_post_textarea()
	{
		$tp = e107::getParser();


		if(!empty($_POST['post']))
		{
			$text = $tp->post_toForm($_POST['post']);
		}
		elseif($this->var['action'] == 'quote')
		{
			$post = preg_replace('#\[hide].*?\[/hide]#s', '', trim($this->var['post_entry']));
			$quoteName = ($this->var['user_name'] ? $this->var['user_name'] : $this->var['post_user_anon']);
			$text = $tp->toText("[quote={$quoteName}]\n".$post."\n[/quote]\n",true);
			$text .= "\n\n";

		//	$text = $tp->toForm($text);
			$this->var['action'] = 'rp';
		}
		elseif($this->var['action'] == 'edit')
		{
			$text = $tp->toForm($this->var['post_entry']);
		}
		else
		{
			$text = '';
		}

		$editor = $this->forum->prefs->get('editor');

		//$wysiwyg = ($editor === 'bbcode') ? false : null;
		$wysiwyg = is_null($editor) ? 'default' : $editor;

		return e107::getForm()->bbarea('post',$text,'forum','_common','large', array('wysiwyg' => $wysiwyg));

	}

	function sc_forum_post_buttons()
	{

		$ret = "<input class='btn btn-default btn-secondary button' type='submit' name='fpreview' value='".LAN_FORUM_3005."' /> ";

		if($this->var['action'] == 'edit')
		{
			// This user created the thread and is editing the original post.
			if($this->var['thread_datestamp'] == $this->var['post_datestamp'] && $this->var['thread_user'] == $this->var['post_user'])
			{
				return $ret . "<input class='btn btn-primary button' type='submit' name='update_thread' value='".LAN_FORUM_3023."' />";
			}
			else // editing a reply.
			{
				return $ret . "<input class='btn btn-primary button' type='submit' name='update_reply' value='".LAN_FORUM_3024."' />";
			}
		}

		if ($this->var['action'] == 'nt') // new thread.
		{
			$ret .= "<input class='btn btn-primary button' type='submit' name='newthread' value='".LAN_FORUM_2005."' />";
		}
		else // new reply or quoted reply.
		{
			$ret .= "<input class='btn btn-primary button' type='submit' name='reply' value='".LAN_FORUM_2006."' />";
		}

		return $ret;
	}


	function sc_fileattach()
	{
		global $forum, $fileattach, $fileattach_alert;

		$uploadClass = e107::pref('core','upload_class');

		if ($this->forum->prefs->get('attach') && (check_class($uploadClass) || getperms('0')))
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
		$tp = e107::getParser();

		global $forum;
		
		//. <div>".($pref['image_post'] ? "Attach file / image" : "Attach file")."</div>
		
		//$tooltip = "Allowed file types | ".vartrue($allowed_filetypes).". Any other file type will be deleted instantly. Maximum file size: ".(vartrue($max_upload_size) ? $max_upload_size."bytes" : ini_get('upload_max_filesize'));
		$tooltip = LAN_FORUM_3016.": ".vartrue($allowed_filetypes)." <br />".LAN_FORUM_3017."<br />".LAN_FORUM_3018.": ".(vartrue($max_upload_size) ? $max_upload_size." ".LAN_FORUM_3019 : ini_get('upload_max_filesize')); // FIXME <br /> in tooltip, no value $allowed_filetypes on v2/bootstrap

		$fileattach = "
			<div>	
				<div id='fiupsection'>
				<span id='fiupopt'>
					<input class='tbox e-tip' title=\"".$tp->toAttribute($tooltip)."\" name='file_userfile[]' type='file' size='47'  multiple='multiple' />
				</span>
				</div>

			</div>
		
		";	
		//<input class='btn btn-default button' type='button' name='addoption' value=".LAN_FORUM_3020."  />
		if( $this->forum->prefs->get('attach') && (check_class($pref['upload_class']) || getperms('0')))
		{
			return $fileattach;
		}
		
	}


	function sc_forum_post_options_label()
	{
		$type = $this->sc_postthreadas();
		$poll 	= $this->sc_forum_post_poll('front');
		$attach = $this->sc_forumattachment();

		if(empty($type) && empty($poll) && empty($attach))
		{
			return '';
		}

		return LAN_FORUM_8013; 
	}



	function sc_forum_post_options($parm='')
	{
		$type = $this->sc_postthreadas();
		$poll 	= $this->sc_forum_post_poll('front');
		$attach = $this->sc_forumattachment();

		$tabs = array();

		if(!empty($type))
		{
			$tabs['type'] = array('caption'=>LAN_FORUM_3025, 'text'=>$type);
		}

		if(!empty($poll))
		{
			$tabs['poll'] = array('caption'=>LAN_FORUM_1016, 'text'=>$poll);
		}

		if(!empty($attach))
		{
			$tabs['attach'] = array('caption'=>LAN_FORUM_3012, 'text'=>$attach);
		}

		if(!empty($tabs))
		{

			return e107::getForm()->tabs($tabs);
		}
		else
		{
			return false;
		}

/*
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
		
			
		return $text;*/
		
		
		
	}
	

	function sc_forum_post_poll($parm=null)
	{

		if(!e107::isInstalled('poll'))
		{
			return null;
		}

		require_once(e_PLUGIN."poll/poll_class.php");
		$pollo = new poll;
		$type = ($parm == 'front') ? 'front' : 'forum';
			
		$poll_form = $pollo -> renderPollForm($type);

		if ($this->var['action'] == 'nt' && check_class($this->forum->prefs->get('poll')) && strpos(e_QUERY, 'edit') === false)
		{
			if($parm == 'front')
			{
				return $poll_form;	
			}
			
			//BC Code below.
			return "<tr><td class='forumheader3' style='vertical-align:top'><a href='#pollform' class='e-expandit' >".LAN_FORUM_3028."</a></td>
			<td class='forumheader3'>
			<div id='pollform' style='display:none'>
			<table class='table table-striped' style='margin-left:0'>".$poll_form."</table></div></td></tr>";
		}

		return '';
	}


	function sc_postthreadas()
	{
		// Show when creating new topic or when editing the original starting post (make sure post is not a reply)
		if (MODERATOR && $this->var['action'] == "nt" || $this->var['thread_datestamp'] == $this->var['post_datestamp'])
		{
			$thread_sticky = (isset($_POST['threadtype']) ? $_POST['threadtype'] : vartrue($this->var['thread_sticky'], 0)); // no reference of 'head' $threadInfo['head']['thread_sticky']

			$opts = array(0 => LAN_FORUM_3038, 1 => LAN_FORUM_1011, 2 => LAN_FORUM_1013); 

			return "<div class='checkbox'>".e107::getForm()->radio('threadtype',$opts, $thread_sticky)."</div>";

		//	return "<br /><span class='defaulttext'>post thread as 
		//	<input name='threadtype' type='radio' value='0' ".(!$thread_sticky ? "checked='checked' " : "")." />".LAN_1."&nbsp;<input name='threadtype' type='radio' value='1' ".($thread_sticky == 1 ? "checked='checked' " : "")." />".LAN_2."&nbsp;<input name='threadtype' type='radio' value='2' ".($thread_sticky == 2 ? "checked='checked' " : "")." />".LAN_3."</span>";
		}
		return '';
	}

	function sc_forum_post_breadcrumb()
	{
		global $forum, $threadInfo, $eaction, $action,$forumInfo;

		$forumInfo = $this->var;

	//	return print_a($forumInfo,true);
//----		$_tmp = new e_vars();
      $_tmp = array();
		// no reference of 'head' $threadInfo['head']['thread_name']
		$eaction = ($this->var['action'] == 'edit');
		$this->forum->set_crumb(true, ($this->var['action'] == 'nt' ? ($eaction ? LAN_FORUM_3023 : LAN_FORUM_1018) : ($eaction ? LAN_FORUM_3024 : $this->var['thread_name'])), $_tmp);
//----		return $_tmp->BREADCRUMB;
		return $_tmp['breadcrumb'];
	}

	function sc_forum_post_caption()
	{
//		global $forumInfo;
			$tp = e107::getParser();
//    var_dump ($this);
//$this->forumObj->threadGet($this->id, false)		
    if ($this->var['action'] == "rp")
    {
      	$pre = LAN_FORUM_1003;
	$name = $tp->toHTML($this->var['thread_name'], false, 'no_hook, emotes_off');
	$url = e107::url('forum', 'topic', $this->var);
      	$post = LAN_FORUM_2006;
    }
    if ($this->var['action'] == "nt")
    {
      	$pre = LAN_FORUM_1001;
	$name = $tp->toHTML($this->var['forum_name'], false, 'no_hook, emotes_off');
	$url = e107::url('forum', 'forum', $this->var);
      	$post = LAN_FORUM_2005;
    }
    return $pre.($url?": <a {$title} href='".$url."'>{$name}</a> - ":$name).$post;
	}
	
	function sc_noemotes()
	{
		if(vartrue($eaction) == true) { return null; }
		return "<input type='checkbox' name='no_emote' value='1' />&nbsp;<span class='defaulttext'>".LAN_FORUM_3039.'</span>';
	}

	function sc_forum_post_email_notify()
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
