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
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/comment_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
//include_once(e_HANDLER.'shortcode_handler.php');
//$comment_shortcodes = e107::getScParser()->parse_scbatch(__FILE__);


class comment_shortcodes extends e_shortcode
{
	var $var;
	
	function sc_subject($parm)
	{
		
		global $SUBJECT, $comrow, $pref, $NEWIMAGE, $tp;
		if (isset($pref['nested_comments']) && $pref['nested_comments']) {
			$SUBJECT = $NEWIMAGE." ".(empty($this->var['comment_subject']) ? $subject : $tp->toHTML($this->var['comment_subject'], TRUE));
		} else {
			$SUBJECT = '';
		}
		return $SUBJECT;
	}
	
	function sc_username($parm)
	{
		global $USERNAME;
		if (isset($this->var['user_id']) && $this->var['user_id']) 
		{
		  $USERNAME = $parm == 'raw' ? $this->var['user_name'] : "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $this->var['user_id'], 'name' => $this->var['user_name']))."'>".$this->var['user_name']."</a>\n";
		}
		else
		{
		  $this->var['user_id'] = 0;
		  $USERNAME = preg_replace("/[0-9]+\./", '', $this->var['comment_author_name']);
		  $USERNAME = str_replace("Anonymous", LAN_ANONYMOUS, $USERNAME);
		}
		return $USERNAME;
	}
	
	
	function sc_TIMEDATE($parm) 
	{
		global $TIMEDATE, $datestamp, $gen;
		$datestamp = $gen->convert_date($this->var['comment_datestamp'], "short");
		return $datestamp;
	}
	
	function sc_reply($parm) 
	{
		global $REPLY, $action, $pref, $table, $id, $thisaction, $thistable, $thisid;
		$REPLY = '';
		if($this->var['comment_lock'] != "1"){
			if ($thisaction == "comment" && $pref['nested_comments']) {
				$REPLY = "<a href='".SITEURL."comment.php?reply.".$thistable.".".$this->var['comment_id'].".".$thisid."'>".COMLAN_326."</a>";
			}
		}
		return $REPLY;
	}
	
	
	function sc_comment_avatar($parm)
	{
		$height 	= e107::getPref("im_height");
		$width 		= e107::getPref("im_width");
		$tp 		= e107::getParser();
		
		if (vartrue($this->var['user_id'])) 
		{
			if (vartrue($this->var['user_image'])) 
			{
				$img = $tp->thumbUrl(e_MEDIA."avatars/".$this->var['user_image'],"aw=".$width."&ah=".$height);
				$text = "<div class='comments-avatar'><img class='comment-avatar' src='".$img."' alt='' /></div>";
			}
			else
			{
				$img = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","aw=".$width."&ah=".$height);
				$text = "<div class='comments-avatar'><img class='comment-avatar' src='".$img."' alt='' /></div>";
			}
		}
		else
		{
			$img = $tp->thumbUrl(e_IMAGE."generic/blank_avatar.jpg","aw=".$width."&ah=".$height);
			$text = "<div class='comments-avatar'><img class='comment-avatar' src='".$img."' alt='' /></div>";
		}
			
		return $text;						
	}	
	
	
	
	
	function sc_avatar($parm) 
	{
					

		
		//  ---------  Legacy Code below for those who still want to use it. 
		
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
	}
	
	function sc_comments($parm) 
	{
		global $COMMENTS;
		return (isset($this->var['user_id']) && $this->var['user_id'] ? COMLAN_99.": ".$this->var['user_comments'] : COMLAN_194)."<br />";
	}
	
	function sc_joined($parm) 
	{
		global $JOINED, $gen;
		$JOINED = '';
		if ($this->var['user_id'] && !$this->var['user_admin']) {
			$this->var['user_join'] = $gen->convert_date($this->var['user_join'], "short");
			$JOINED = ($this->var['user_join'] ? COMLAN_145." ".$this->var['user_join'] : '');
		}
		return $JOINED;
	}
	
	function sc_comment($parm) 
	{
		global $COMMENT, $pref;
		$tp = e107::getParser();
		return (isset($this->var['comment_blocked']) && $this->var['comment_blocked'] ? COMLAN_0 : $tp->toHTML($this->var['comment_comment'], TRUE, FALSE, $this->var['user_id']));
	}
	
	function sc_commentedit($parm) 
	{
		global $COMMENTEDIT, $pref, $comment_edit_query;
		if ($pref['allowCommentEdit'] && USER && $this->var['user_id'] == USERID && $this->var['comment_lock'] != "1")
		{
		    $adop_icon = (file_exists(THEME."images/commentedit.png") ? THEME_ABS."images/commentedit.png" : e_IMAGE_ABS."admin_images/edit_16.png");
			//Searching for '.' is BAD!!! It breaks mod rewritten requests. Why is this needed at all?
			if (strstr(e_QUERY, "&"))
			{
				return "<a href='".e_SELF."?".e_QUERY."&amp;comment=edit&amp;comment_id=".$this->var['comment_id']."'><img src='".$adop_icon."' alt='".COMLAN_318."' title='".COMLAN_318."' class='icon' /></a>";
			}
			else
			{
		//		return "<a href='".e_SELF."?".$comment_edit_query.".edit.".$this->var['comment_id']."'><img src='".e_IMAGE."generic/newsedit.png' alt='".COMLAN_318."' title='".COMLAN_318."' style='border: 0;' /></a>";
				return "<a href='".SITEURL."comment.php?".$comment_edit_query.".edit.".$this->var['comment_id']."#e-comment-form'><img src='".$adop_icon."' alt='".COMLAN_318."' title='".COMLAN_318."' class='icon' /></a>";
			}
		}
		else
		{
			return "";
		}
	}
	
	function sc_rating($parm) 	
	{
		global $RATING;
		return $RATING;
	}
	
	function sc_ipaddress($parm) 
	{
		global $IPADDRESS, $e107;
		//require_once(e_HANDLER."encrypt_handler.php");
		return (ADMIN ? "<a href='".SITEURL."userposts.php?0.comments.".$this->var['user_id']."'>".COMLAN_330." ".e107::getIPHandler()->ipDecode($this->var['comment_ip'])."</a>" : "");
	}
	
	function sc_level($parm) 
	{
		global $LEVEL, $pref;
		//FIXME - new level handler, currently commented to avoid parse errors
		//$ldata = get_level($this->var['user_id'], $this->var['user_forums'], $this->var['user_comments'], $this->var['user_chats'], $this->var['user_visits'], $this->var['user_join'], $this->var['user_admin'], $this->var['user_perms'], $pref);
		return ($this->var['user_admin'] ? $ldata[0] : $ldata[1]);
	}
	
	function sc_location($parm) 
	{
		global $LOCATION;
		$tp = e107::getParser();
		return (isset($this->var['user_location']) && $this->var['user_location'] ? COMLAN_313.": ".$tp->toHTML($this->var['user_location'], TRUE) : '');
	}
	
	function sc_signature($parm) 
	{
		global $SIGNATURE;
		$tp = e107::getParser();
		$SIGNATURE = (isset($this->var['user_signature']) && $this->var['user_signature'] ? $tp->toHTML($this->var['user_signature'], true) : '');
		return $SIGNATURE;
	}	
}

/*
SC_BEGIN SUBJECT
global $SUBJECT, $comrow, $pref, $NEWIMAGE, $tp;
if (isset($pref['nested_comments']) && $pref['nested_comments']) {
	$SUBJECT = $NEWIMAGE." ".(empty($comrow['comment_subject']) ? $subject : $tp->toHTML($comrow['comment_subject'], TRUE));
} else {
	$SUBJECT = '';
}
return $SUBJECT;
SC_END

SC_BEGIN USERNAME
global $USERNAME, $comrow;
if (isset($comrow['user_id']) && $comrow['user_id']) 
{
  $USERNAME = $parm == 'raw' ? $comrow['user_name'] : "<a href='".e107::getUrl()->create('user/profile/view', array('id' => $comrow['user_id'], 'name' => $comrow['user_name']))."'>".$comrow['user_name']."</a>\n";
}
else
{
  $comrow['user_id'] = 0;
  $USERNAME = preg_replace("/[0-9]+\./", '', $comrow['comment_author_name']);
  $USERNAME = str_replace("Anonymous", LAN_ANONYMOUS, $USERNAME);
}
return $USERNAME;
SC_END


SC_BEGIN TIMEDATE
global $TIMEDATE, $comrow, $datestamp, $gen;
$datestamp = $gen->convert_date($comrow['comment_datestamp'], "short");
return $datestamp;
SC_END

SC_BEGIN REPLY
global $REPLY, $comrow, $action, $pref, $table, $id, $thisaction, $thistable, $thisid;
$REPLY = '';
if($comrow['comment_lock'] != "1"){
	if ($thisaction == "comment" && $pref['nested_comments']) {
		$REPLY = "<a href='".SITEURL."comment.php?reply.".$thistable.".".$comrow['comment_id'].".".$thisid."'>".COMLAN_326."</a>";
	}
}
return $REPLY;
SC_END

SC_BEGIN AVATAR
global $AVATAR, $comrow;
if (isset($comrow['user_id']) && $comrow['user_id']) {
	if (isset($comrow['user_image']) && $comrow['user_image']) {
		require_once(e_HANDLER."avatar_handler.php");
		$comrow['user_image'] = avatar($comrow['user_image']);
		$comrow['user_image'] = "<div class='spacer'><img src='".$comrow['user_image']."' alt='' /></div>";
	}else{
		$comrow['user_image'] = '';
	}
}else{
	$comrow['user_image'] = '';
}
return $comrow['user_image'];
SC_END

SC_BEGIN COMMENTS
global $COMMENTS, $comrow;
return (isset($comrow['user_id']) && $comrow['user_id'] ? COMLAN_99.": ".$comrow['user_comments'] : COMLAN_194)."<br />";
SC_END

SC_BEGIN JOINED
global $JOINED, $comrow, $gen;
$JOINED = '';
if ($comrow['user_id'] && !$comrow['user_admin']) {
	$comrow['user_join'] = $gen->convert_date($comrow['user_join'], "short");
	$JOINED = ($comrow['user_join'] ? COMLAN_145." ".$comrow['user_join'] : '');
}
return $JOINED;
SC_END

SC_BEGIN COMMENT
global $COMMENT, $comrow, $tp, $pref;
return (isset($comrow['comment_blocked']) && $comrow['comment_blocked'] ? COMLAN_0 : $tp->toHTML($comrow['comment_comment'], TRUE, FALSE, $comrow['user_id']));
SC_END

SC_BEGIN COMMENTEDIT
global $COMMENTEDIT, $pref, $comrow, $comment_edit_query;
if ($pref['allowCommentEdit'] && USER && $comrow['user_id'] == USERID && $comrow['comment_lock'] != "1")
{
    $adop_icon = (file_exists(THEME."images/commentedit.png") ? THEME_ABS."images/commentedit.png" : e_IMAGE_ABS."admin_images/edit_16.png");
	//Searching for '.' is BAD!!! It breaks mod rewritten requests. Why is this needed at all?
	if (strstr(e_QUERY, "&"))
	{
		return "<a href='".e_SELF."?".e_QUERY."&amp;comment=edit&amp;comment_id=".$comrow['comment_id']."'><img src='".$adop_icon."' alt='".COMLAN_318."' title='".COMLAN_318."' class='icon' /></a>";
	}
	else
	{
//		return "<a href='".e_SELF."?".$comment_edit_query.".edit.".$comrow['comment_id']."'><img src='".e_IMAGE."generic/newsedit.png' alt='".COMLAN_318."' title='".COMLAN_318."' style='border: 0;' /></a>";
		return "<a href='".SITEURL."comment.php?".$comment_edit_query.".edit.".$comrow['comment_id']."#e-comment-form'><img src='".$adop_icon."' alt='".COMLAN_318."' title='".COMLAN_318."' class='icon' /></a>";
	}
}
else
{
	return "";
}
SC_END

SC_BEGIN RATING
global $RATING;
return $RATING;
SC_END

SC_BEGIN IPADDRESS
global $IPADDRESS, $comrow, $e107;
//require_once(e_HANDLER."encrypt_handler.php");
return (ADMIN ? "<a href='".SITEURL."userposts.php?0.comments.".$comrow['user_id']."'>".COMLAN_330." ".e107::getIPHandler()->ipDecode($comrow['comment_ip'])."</a>" : "");
SC_END

SC_BEGIN LEVEL
global $LEVEL, $comrow, $pref;
//FIXME - new level handler, currently commented to avoid parse errors
//$ldata = get_level($comrow['user_id'], $comrow['user_forums'], $comrow['user_comments'], $comrow['user_chats'], $comrow['user_visits'], $comrow['user_join'], $comrow['user_admin'], $comrow['user_perms'], $pref);
return ($comrow['user_admin'] ? $ldata[0] : $ldata[1]);
SC_END

SC_BEGIN LOCATION
global $LOCATION, $comrow, $tp;
return (isset($comrow['user_location']) && $comrow['user_location'] ? COMLAN_313.": ".$tp->toHTML($comrow['user_location'], TRUE) : '');
SC_END

SC_BEGIN SIGNATURE
global $SIGNATURE, $comrow, $tp;
$SIGNATURE = (isset($comrow['user_signature']) && $comrow['user_signature'] ? $tp->toHTML($comrow['user_signature'], true) : '');
return $SIGNATURE;
SC_END

*/

?>