<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 */

if (!defined('e107_INIT')) { exit; }


class comment_shortcodes extends e_shortcode
{
	var $var;

	function sc_subject_input($parm = null)
	{
		$tp = e107::getParser();
		$pref = e107::getPref();
		$form = e107::getForm();

		if(vartrue($pref['nested_comments']))
		{
			$options = array(
				'class'       => 'comment subject-input form-control',
				'placeholder' => COMLAN_324,
				'size'        => 61,
			);

			$text = '<div class="form-group">';
			$text .= $form->text('subject', $tp->toForm($this->var['subject']), 100, $options);
			$text .= '</div>';

			return $text;
		}
	}


	function sc_subject($parm='')
	{
		$tp = e107::getParser();
		$pref = e107::getPref();

		global $SUBJECT, $NEWIMAGE;

		if (vartrue($pref['nested_comments']))
		{
			$SUBJECT = $NEWIMAGE." ".(empty($this->var['comment_subject']) ? $SUBJECT : $tp->toHTML($this->var['comment_subject'], TRUE));
		}
		else
		{
			$SUBJECT = '';
		}

		return $SUBJECT;

	}


	function sc_username($parm = null)
	{
		global $USERNAME;
		if (isset($this->var['comment_author_id']) && $this->var['comment_author_id'])
		{
			$USERNAME = $parm == 'raw' ? $this->var['comment_author_name'] : "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $this->var['comment_author_id'], 'name' => $this->var['comment_author_name']))."'>".$this->var['comment_author_name']."</a>\n";
		}
		else
		{
			$this->var['user_id'] = 0;
			$USERNAME = preg_replace("/[0-9]+\./", '', vartrue($this->var['comment_author_name']));
			$USERNAME = str_replace("Anonymous", LAN_ANONYMOUS, $USERNAME);
		}
		return $USERNAME;
	}


	function sc_timedate($parm = null)
	{
		if($parm == 'relative')
		{
			return e107::getDate()->computeLapse($this->var['comment_datestamp'],time(),false, false, 'short');
		}

		return e107::getDate()->convert_date($this->var['comment_datestamp'], "short");
	}


	function sc_reply($parm = null)
	{
		global $REPLY, $action, $table, $id, $thisaction, $thistable, $thisid;

		$pref = e107::getPref();
		$REPLY = '';
		if(USERID || $pref['anon_post'] == 1)
		{
			if($this->var['comment_lock'] != "1" && $this->var['comment_blocked'] < 1)
			{
				if ($thisaction == "comment" && $pref['nested_comments'])
				{
					$REPLY = "<a id='e-comment-reply-".$this->var['comment_id']."' class='e-comment-reply btn btn-default btn-secondary btn-mini btn-xs' data-type='".$this->var['comment_type']."' data-target='".e_HTTP."comment.php' href='".e_HTTP."comment.php?reply.".$thistable.".".$this->var['comment_id'].".".$thisid."'>".COMLAN_326."</a>";
				}
			}
		}
		return $REPLY;
	}


	function sc_comment_avatar($parm = '')
	{
		$tp = e107::getParser();

		//	return $this->var['user_image'];
		//	$url = $tp->thumbUrl($this->var['user_image']);
		//	$text = $tp->parseTemplate("{USER_AVATAR=".vartrue($this->var['user_image'],USERIMAGE)."}");
		//	$text = $tp->parseTemplate("{USER_AVATAR=".$this->var['user_id']."}");

		// Posting a new comment (check that it is not an existing comment by anonymous user) - #3813 & 3829
		if($this->var['comment_author_id'] != '0' && USERID)
		{
			$userdata = e107::user(USERID); 
			$this->var = array_merge($this->var, $userdata); 
		}

		$text = $tp->toAvatar($this->var);

		$text .= '<div class="field-help" style="display:none;">';
		$text .= '<div class="left">';
		$text .= '<h2>' . $this->sc_username() . '</h2>';
		//	$text .= e107::getDate()-> //    convert($this->var['user_lastvisit'],'short');
		$text .= $this->sc_joined() . '<br />' . $this->sc_comments() . '<br />' . $this->sc_rating() . $this->sc_location;
		$text .= '</div>';
		$text .= '</div>';

		return $text;
	}


	function sc_avatar($parm = null)
	{
		return $this->sc_comment_avatar($parm);

		//  ---------  Legacy Code for reference

		/*
		global $AVATAR;
		if (isset($this->var['user_id']) && $this->var['user_id']) {
			if (isset($this->var['user_image']) && $this->var['user_image']) {
			//	require_once(e_HANDLER."avatar_handler.php");
			//	$this->var['user_image'] = avatar($this->var['user_image']);
				$this->var['user_image'] = "<div class='spacer'><img src='".$this->var['user_image']."' alt='' /></div>";
			}else{
				$this->var['user_image'] = '';
			}
		}else{
			$this->var['user_image'] = '';
		}
		return $this->var['user_image'];
		
		 */

	}


	function sc_comments($parm = null)
	{
		global $COMMENTS;
		return (isset($this->var['user_id']) && $this->var['user_id'] ? LAN_COMMENTS.": ".$this->var['user_comments'] : COMLAN_194)."<br />";
	}


	function sc_joined($parm = null)
	{
		global $JOINED, $gen;
		$JOINED = '';
		if ($this->var['user_id'] && !$this->var['user_admin']) {
			$this->var['user_join'] = $gen->convert_date($this->var['user_join'], "short");
			$JOINED = ($this->var['user_join'] ? COMLAN_145." ".$this->var['user_join'] : '');
		}
		return $JOINED;
	}


	function sc_comment_itemid($parm = null) // for ajax item id.
	{
		return 'comment-'.intval($this->var['comment_id']);
	}


	function sc_comment_moderate($parm = null)
	{
		if(!getperms('0') && !getperms("B"))
		{
			return null;
		}

		// TODO put into a <ul> drop-down format.

	//	e107::getDebug()->log($this->var);

		$text = "<a href='#' data-target='".e_HTTP."comment.php' id='e-comment-delete-".$this->var['comment_id']."'  data-type='".$this->var['comment_type']."' data-itemid='".$this->var['comment_item_id']."' class='e-comment-delete btn btn-default btn-secondary btn-mini btn-xs'>".LAN_DELETE."</a> ";

		if($this->var['comment_blocked'] == 2) // pending approval. 
		{
			$text .= "<a href='#' data-target='" . e_HTTP . "comment.php' id='e-comment-approve-" . $this->var['comment_id'] . "' class='e-comment-approve btn btn-default btn-secondary btn-mini btn-xs'>" . COMLAN_404 . "</a> ";
		}
		return $text;
		/*
		$url 		= e_PAGE."?".e_QUERY;
		
		$unblock 	= "[<a href='".e_ADMIN_ABS."comment.php?unblock-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".COMLAN_1."</a>] ";
		$block 		= "[<a href='".e_ADMIN_ABS."comment.php?block-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".COMLAN_2."</a>] ";
		$delete 	= "[<a href='".e_ADMIN_ABS."comment.php?delete-".$comrow['comment_id']."-$url-".$comrow['comment_item_id']."'>".LAN_DELETE."</a>] ";
		$userinfo 	= "[<a href='".e_ADMIN_ABS."userinfo.php?".e107::getIPHandler()->ipDecode($comrow['comment_ip'])."'>".COMLAN_4."</a>]";
			
		return $unblock.$block.$delete.$userinfo;
		 * */
	}

  	/* example {COMMENT_BUTTON} */
  	/* example {COMMENT_BUTTON: class=btn btn-default pull-right} */
	function sc_comment_button($parm = null)
	{
		$pref = e107::getPref('comments_sort');
		$form = e107::getForm();

		if($this->mode == 'edit')
		{
			$value = (varset($this->var['eaction']) == "edit" ? COMLAN_320 : COMLAN_9);
			$pid = ($this->var['action'] == 'reply') ? $this->var['pid'] : 0;

			$class = "e-comment-submit ";
			$class .= (!empty($parm['class'])) ? $parm['class'] : 'button btn btn-primary e-comment-submit pull-right float-right';
			$options = array(
				'class'         => $class,
				'data-pid'      => $pid,
				'data-sort'     => $pref,
				'data-target'   => e_HTTP . 'comment.php',
				'data-container' => 'comments-container-'.$form->name2id($this->var['table']),
				'data-input'    => 'comment-input-'.$form->name2id($this->var['table'])
			);

			return $form->submit($this->var['action'] . 'submit', $value, $options);
		}
	}

  /* example {AUTHOR_INPUT} */
  /* example {AUTHOR_INPUT: inputclass=form-control&class=form-group} */
	function sc_author_input($parm = null)
	{
		if($this->mode == 'edit')
		{
			if(ANON == true && USER == false) // (anonymous comments - if allowed)
			{
				$form = e107::getForm();

				$inputclass = (!empty($parm['inputclass'])) ? $parm['inputclass'] : 'comment author form-control';
        		$class = (!empty($parm['class'])) ? $parm['class'] : 'form-group';

				$options = array(
					'class'       => $inputclass,
					'placeholder' => COMLAN_16,
					'size'        => 61,
				);

				// Prevent anon users changing names on the same session.
				if(vartrue($_SESSION['comment_author_name']))
				{
					$options['disabled'] = 'disabled';
				}

				$text = '<div class="'.$class.'">';
				$text .= $form->text('author_name', $_SESSION['comment_author_name'], 100, $options);
				$text .= '</div>';

				return $text;
			}
		}
	}


	function sc_comment_rate($parm = null)
	{

		if($this->var['comment_blocked'] > 0 || $this->var['rating_enabled'] == false)
		{
			return null;
		}

		$curVal = array(
			'up'	=> $this->var['rate_up'],
			'down'	=> $this->var['rate_down'],
			'total'	=> $this->var['rate_votes']
		);

		return e107::getRate()->renderLike("comments",$this->var['comment_id'],$curVal);
	}

  /* example {COMMENT_INPUT} */
  /* example {COMMENT_INPUT: inputclass=form-control&class=form-group} */
	function sc_comment_input($parm = null)
	{
		
		$inputclass = (!empty($parm['inputclass'])) ? $parm['inputclass'] : 'comment-input form-control';
    	$class = (!empty($parm['class'])) ? $parm['class'] : 'form-group';
		$options = array(
			'class'       => $inputclass,
			'placeholder' => COMLAN_403,
			'id'          => 'comment-input-'.e107::getForm()->name2id($this->var['table'])
		);

		$text = '<div class="'.$class.'">';

		if($parm == 'bbcode')
		{
			$text .= e107::getForm()->bbarea('comment', $this->var['comval'], 'comment', 'comment-' . $this->var['itemid'], 'large', $options);
		}
		else
		{
			$text .= e107::getForm()->textarea('comment', $this->var['comval'], 3, 80, $options);
		}

		$text .= '</div>';

		return $text;
	}


	/*
	function sc_user_avatar($parm = null)
	{
		$this->var['user_id'] = USERID;
		$this->var['user_image'] = USERIMAGE;
		return $this->sc_comment_avatar($parm);			
	}
	*/


	function sc_comment($parm=null)
	{
		// global $COMMENT, $pref;	
		$tp = e107::getParser();
		if($this->var['comment_blocked'] == 1)
		{
			return COMLAN_0;
		}
		
		return $tp->toHTML($this->var['comment_comment'], TRUE, FALSE, $this->var['user_id']);
	}


	function sc_comment_status($parm = null)
	{
		switch ($this->var['comment_blocked'])
		{
			case 2:
				$text = COMLAN_331;
				break;

			case 1:
				$text = COMLAN_0;
				break;

			default:
				return null;
				break;
		}

		return "<span id='comment-status-".$this->var['comment_id']."'>".$text."</span>";
	}



	function sc_commentedit($parm = null)
	{
		global $COMMENTEDIT, $comment_edit_query;
		$pref = e107::getPref();

		if ($pref['allowCommentEdit'] && USER && $this->var['user_id'] == USERID && ($this->var['comment_lock'] < 1))
		{
			$adop_icon = (file_exists(THEME."images/commentedit.png") ? "<img src='".THEME_ABS."images/commentedit.png' alt='".COMLAN_318."' title='".COMLAN_318."' class='icon' />" : LAN_EDIT);
			//Searching for '.' is BAD!!! It breaks mod rewritten requests. Why is this needed at all?
			if (strstr(e_QUERY, "&"))
			{
				return "<a data-target='".e_HTTP."comment.php' id='e-comment-edit-".$this->var['comment_id']."' class='btn btn-default btn-secondary btn-mini btn-xs e-comment-edit' href='".e_SELF."?".e_QUERY."&amp;comment=edit&amp;comment_id=".$this->var['comment_id']."'>{$adop_icon}</a>";
			}
			else
			{
				//		return "<a href='".e_SELF."?".$comment_edit_query.".edit.".$this->var['comment_id']."'><img src='".e_IMAGE."generic/newsedit.png' alt='".COMLAN_318."' title='".COMLAN_318."' style='border: 0;' /></a>";
				return "<a data-target='".e_HTTP."comment.php' id='e-comment-edit-".$this->var['comment_id']."' class='btn btn-default btn-secondary btn-mini btn-xs e-comment-edit' href='".SITEURL."comment.php?".$comment_edit_query.".edit.".$this->var['comment_id']."#e-comment-form'>".$adop_icon."</a>";
			}
		}
		else
		{
			return "";
		}
	}


	function sc_rating($parm = null)
	{
		global $RATING;
		return $RATING;
	}


	function sc_ipaddress($parm = null)
	{
		global $IPADDRESS, $e107;
		//require_once(e_HANDLER."encrypt_handler.php");
		return (ADMIN ? "<a href='".SITEURL."userposts.php?0.comments.".$this->var['user_id']."'>".COMLAN_330." ".e107::getIPHandler()->ipDecode($this->var['comment_ip'])."</a>" : "");
	}


	function sc_level($parm = null)
	{
		global $LEVEL, $pref;
		//FIXME - new level handler, currently commented to avoid parse errors
		//$ldata = get_level($this->var['user_id'], $this->var['user_forums'], $this->var['user_comments'], $this->var['user_chats'], $this->var['user_visits'], $this->var['user_join'], $this->var['user_admin'], $this->var['user_perms'], $pref);
		//return ($this->var['user_admin'] ? vartrue($ldata[0]) : vartrue($ldata[1]));
	}


	function sc_location($parm = null)
	{
		global $LOCATION;
		$tp = e107::getParser();
		return (isset($this->var['user_location']) && $this->var['user_location'] ? COMLAN_313.": ".$tp->toHTML($this->var['user_location'], TRUE) : '');
	}


	function sc_signature($parm = null)
	{
		global $SIGNATURE;
		$tp = e107::getParser();
		$SIGNATURE = (isset($this->var['user_signature']) && $this->var['user_signature'] ? $tp->toHTML($this->var['user_signature'], true) : '');
		return $SIGNATURE;
	}


	function sc_comment_share($parm = null)
	{
		if(!$xup = e107::getUser()->getProviderName())
		{
			return null;
		}

		list($prov,$id) = explode("_",$xup);
		$prov = strtolower($prov);

		if($prov == 'facebook' || $prov == 'twitter') //TODO Get this working!
		{
			//TODO Move styling to e107.css 
		//	$text = "<img src='".e_IMAGE_ABS."xup/".$prov.".png' style='display:inline-block;width:26px;height:26px;vertical-align:middle' alt='Share' />";
		//	$text .= e107::getForm()->checkbox('comment_share',$prov,true);
			$text = e107::getForm()->hidden('comment_share','');
		//	$text .= LAN_SHARE;
			return $text;
		}
	}

}
