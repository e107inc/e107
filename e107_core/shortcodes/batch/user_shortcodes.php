<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User information - shortcodes
 *
 * $URL$
 * $Id$
 *
 */
if (!defined('e107_INIT')) { exit; }


class user_shortcodes extends e_shortcode
{
	
	function sc_total_chatposts($parm) {
		global $sql;
		if(!$chatposts = getcachedvars('total_chatposts'))
		{
		  $chatposts = 0;				// In case plugin not installed
		  if (isset($pref['plug_installed']['chatbox_menu']))
		  {
			$chatposts = $sql->db_Count("chatbox");
		  }
		  cachevars('total_chatposts', $chatposts);
		}
		return $chatposts;
	}
	
	
	
	function sc_total_commentposts($parm) 
	{
		global $sql;
		if(!$commentposts = getcachedvars('total_commentposts'))
		{
			$commentposts = $sql->db_Count("comments");
			cachevars('total_commentposts', $commentposts);
		}
		return $commentposts;
	}
	
	
	
	function sc_total_forumposts($parm) 
	{
		global $sql;
		if(!$forumposts = getcachedvars('total_forumposts'))
		{
			$forumposts = $sql->db_Count("forum_t");
			cachevars('total_forumposts', $forumposts);
		}
		return $forumposts;
	}
	
	
	
	function sc_user_commentposts($parm) 
	{
		return $this->var['user_comments'];
	}
	
	
	
	function sc_user_forumposts($parm) 
	{
		return $this->var['user_forums'];
	}
	
	
	
	function sc_user_chatposts($parm) 
	{
		return $this->var['user_chats'];
	}
	
	function sc_user_downloads($parm) 
	{
		return e107::getDb()->db_Count("download_requests","(*)","where download_request_userid=".$this->var['user_id']);
	}
	
	function sc_user_chatper($parm) 
	{
		$sql = e107::getDb();
		if(!$chatposts = getcachedvars('total_chatposts'))
		{
		  $chatposts = 0;			// In case plugin not installed
		  if (isset($pref['plug_installed']['chatbox_menu']))
		  {
			$chatposts = $sql->db_Count("chatbox");
		  }
		  cachevars('total_chatposts', $chatposts);
		}
		return ($chatposts!=0) ? round(($this->var['user_chats']/$chatposts) * 100, 2): 0;
	}
	
	
	
	function sc_user_commentper($parm) 
	{
		$sql = e107::getDb();
		if(!$commentposts = getcachedvars('total_commentposts'))
		{
			$commentposts = $sql->db_Count("comments");
			cachevars('total_commentposts', $commentposts);
		}
		return ($commentposts!=0) ? round(($this->var['user_comments']/$commentposts) * 100, 2): 0;
	}
	
	
	
	function sc_user_forumper($parm) 
	{
		$sql = e107::getDb();
		if(!$forumposts = getcachedvars('total_forumposts'))
		{
		  $forumposts = (isset($pref['plug_installed']['forum'])) ? $sql->db_Count("forum_t"): 0;
		  cachevars('total_forumposts', $forumposts);
		}
		return ($forumposts!==0) ? round(($this->var['user_forums']/$forumposts) * 100, 2): 0;
	}
	
	
	
	function sc_user_level($parm) 
	{
		$pref = e107::getPref();
		//FIXME - new level handler, currently commented to avoid parse errors
		//require_once(e_HANDLER."level_handler.php");
		//$ldata = get_level($this->var['user_id'], $this->var['user_forums'], $this->var['user_comments'], $this->var['user_chats'], $this->var['user_visits'], $this->var['user_join'], $this->var['user_admin'], $this->var['user_perms'], $pref);
		$ldata = array();
		if (strstr($ldata[0], "IMAGE_rank_main_admin_image")) 
		{
			return LAN_USER_31;
		}
		elseif(strstr($ldata[0], "IMAGE")) 
		{
			return LAN_USER_32;
		}
		else
		{
			return $USER_LEVEL = $ldata[1];
		}
	}
	
	
	
	function sc_user_lastvisit($parm)
	{
		return $this->var['user_currentvisit'] ? e107::getDate()->convert_date($this->var['user_currentvisit'], "long") : "<i>".LAN_USER_33."</i>";
	}
	
	
	
	function sc_user_lastvisit_lapse($parm) 
	{	
		return $this->var['user_currentvisit'] ? "( ".e107::getDate()->computeLapse($this->var['user_currentvisit'])." ".LAN_USER_34." )" : '';
	}


	
	function sc_user_visits($parm) 
	{
		return $this->var['user_visits'];
	}


	
	function sc_user_join($parm) 
	{
		return e107::getDate()->convert_date($this->var['user_join'], "forum");
	}
	
	
	
	function sc_user_daysregged($parm) 
	{
		return e107::getDate()->computeLapse($this->var['user_join'])." ".LAN_USER_34;
	}

	
		
	function sc_user_realname_icon($parm) 
	{
		if(defined("USER_REALNAME_ICON"))
		{
			return USER_REALNAME_ICON;
		}
		if(file_exists(THEME."images/user_realname.png"))
		{
			return "<img src='".THEME_ABS."images/user_realname.png' alt='' style='vertical-align:middle;' /> ";
		}
		return "<img src='".e_IMAGE_ABS."user_icons/user_realname.png' alt='' style='vertical-align:middle;' /> ";
	}

	
	
	function sc_user_realname($parm) 
	{
		return $this->var['user_login'] ? $this->var['user_login'] : "<i>".LAN_USER_33."</i>";
	}


	
	function sc_user_email_icon($parm) 
	{
		if(defined("USER_EMAIL_ICON"))
		{
			return USER_EMAIL_ICON;
		}
		if(file_exists(THEME."images/email.png"))
		{
			return "<img src='".THEME_ABS."images/email.png' alt='' style='vertical-align:middle;' /> ";
		}
		return "<img src='".e_IMAGE_ABS."generic/email.png' alt='' style='vertical-align:middle;' /> ";
	}


	
	function sc_user_email_link($parm) 
	{
		$tp = e107::getParser();
		return ($this->var['user_hideemail'] && !ADMIN) ? "<i>".LAN_USER_35."</i>" : $tp->parseTemplate("{email={$this->var['user_email']}-link}");
	}


	
	function sc_user_email($parm) 
	{
		$tp = e107::getParser();
		return ($this->var['user_hideemail'] && !ADMIN) ? "<i>".LAN_USER_35."</i>" : $tp->toHTML($this->var['user_email'],"no_replace");
	}


	
	function sc_user_icon($parm) 
	{
		if(defined("USER_ICON"))
		{
			return USER_ICON;
		}
		if(file_exists(THEME."images/user.png"))
		{
			return "<img src='".THEME_ABS."images/user.png' alt='' style='vertical-align:middle;' /> ";
		}
		return "<img src='".e_IMAGE_ABS."user_icons/user.png' alt='' style='vertical-align:middle;' /> ";
	}


	
	function sc_user_icon_link($parm) 
	{

		$uparams = array('id' => $this->var['user_id'], 'name' => $this->var['user_name']);
		$url = e107::getUrl();
		if(defined("USER_ICON"))
		{
			$icon = USER_ICON;
		}
		else if(file_exists(THEME."images/user.png"))
		{
			$icon = "<img src='".THEME_ABS."images/user.png' alt='' style='vertical-align:middle;' /> ";
		}
		else
		{
			$icon = "<img src='".e_IMAGE_ABS."user_icons/user.png' alt='' style='vertical-align:middle;' /> ";
		}
		return "<a href='".$url->create('user/profile/view', $uparams)."'>{$icon}</a>";
	}


	
	function sc_user_id($parm) 
	{
		return $this->var['user_id'];
	}
	
	
	
	function sc_user_name($parm) 
	{
		return $this->var['user_name'];
	}
	
	
	
	function sc_user_name_link($parm) 
	{
		$uparams = array('id' => $this->var['user_id'], 'name' => $this->var['user_name']);
		return "<a href='".e107::getUrl()->create('user/profile/view', $uparams)."'>".$this->var['user_name']."</a>";
	}
	
	
	
	function sc_user_loginname($parm) 
	{
		if(ADMIN && getperms("4")) {
			return $this->var['user_loginname'];
		}
	}


	
	function sc_user_birthday_icon($parm) 
	{
		if(defined("USER_BIRTHDAY_ICON"))
		{
			return USER_BIRTHDAY_ICON;
		}
		if(file_exists(THEME."images/user_birthday.png"))
		{
			return "<img src='".THEME_ABS."images/user_birthday.png' alt='' style='vertical-align:middle;' /> ";
		}
		return "<img src='".e_IMAGE_ABS."user_icons/user_birthday.png' alt='' style='vertical-align:middle;' /> ";
	}

	
		
	function sc_user_birthday($parm) 
	{
		if ($this->var['user_birthday'] != "" && $this->var['user_birthday'] != "0000-00-00" && preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $this->var['user_birthday'], $regs))
		{
			return "$regs[3].$regs[2].$regs[1]";
		}
		else
		{
			return "<i>".LAN_USER_33."</i>";
		}
	}
	
	
	
	function sc_user_signature($parm) 
	{
		$tp = e107::getParser();
		return $this->var['user_signature'] ? $tp->toHTML($this->var['user_signature'], TRUE) : "";
	}


	
	function sc_user_comments_link($parm) 
	{
		return $this->var['user_comments'] ? "<a href='".e_HTTP."userposts.php?0.comments.".$this->var['user_id']."'>".LAN_USER_36."</a>" : "";
	}


	
	function sc_user_forum_link($parm) 
	{
		return $this->var['user_forums'] ? "<a href='".e_HTTP."userposts.php?0.forums.".$this->var['user_id']."'>".LAN_USER_37."</a>" : "";
	}

	
		
	function sc_user_sendpm($parm) 
	{
		$pref = e107::getPref();
		$tp = e107::getParser();
		if(isset($pref['plug_installed']['pm']) && ($this->var['user_id'] > 0))
		{
		  return $tp->parseTemplate("{SENDPM={$this->var['user_id']}}");
		}
	}


	
	function sc_user_rating($parm='') 
	{
		$pref = e107::getPref();
		$frm = e107::getForm();
		
		if(!vartrue($pref['profile_rate'])){ return; }
		if(!USER){ return "Login to rate this user"; } // TODO LAN
		else{
		
		switch ($parm) 
		{
			case 'like':
				return $frm->like('user',$this->var['user_id']);	
			break;
			
			case 'legacy':
				$rater = e107::getRate();
				$ret = "<span>";
				if($rating = $rater->getrating('user', $this->var['user_id']))
				{
					$num = $rating[1];
					for($i=1; $i<= $num; $i++)
					{
						$ret .= "<img src='".e_IMAGE_ABS."rate/star.png' alt='' />";
					}
				}
				if(!$rater->checkrated('user', $this->var['user_id']))
				{
					$ret .= " &nbsp; &nbsp;".$rater->rateselect('', 'user', $this->var['user_id']);
				}
				$ret .= "</span>";
				return $ret;	
			break;
			
			default:
				return $frm->rate('user',$this->var['user_id']);	
			break;
		}		

		return "";
	}}


	
	function sc_user_update_link($parm) 
	{
		$url = e107::getUrl();
		if (USERID == $this->var['user_id']) 
		{
			//return "<a href='".$url->create('user/myprofile/edit')."'>".LAN_USER_38."</a>";
			return "<a href='usersettings.php' alt=''>".LAN_USER_38."</a>"; // TODO: repair dirty fix for usersettings
		}
		else if(ADMIN && getperms("4") && !$this->var['user_admin']) 
		{
			return "<a href='".$url->create('user/profile/edit', array('id' => $this->var['user_id'], 'name' => $this->var['user_name']))."'>".LAN_USER_39."</a>";
		}
	}
	
	
	
	function sc_user_jump_link($parm) 
	{
		global $full_perms;
		$sql = e107::getDb();
		if (!$full_perms) return;
		$url = e107::getUrl();
		if(!$userjump = getcachedvars('userjump'))
		{
		//  $sql->db_Select("user", "user_id, user_name", "`user_id` > ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id ASC LIMIT 1 ");
		  $sql->db_Select_gen("SELECT user_id, user_name FROM `#user` FORCE INDEX (PRIMARY) WHERE `user_id` > ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id ASC LIMIT 1 ");
		  if ($row = $sql->db_Fetch())
		  {
			$userjump['next']['id'] = $row['user_id'];
			$userjump['next']['name'] = $row['user_name'];
		  }
		//  $sql->db_Select("user", "user_id, user_name", "`user_id` < ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id DESC LIMIT 1 ");
		  $sql->db_Select_gen("SELECT user_id, user_name FROM `#user` FORCE INDEX (PRIMARY) WHERE `user_id` < ".intval($this->var['user_id'])." AND `user_ban`=0 ORDER BY user_id DESC LIMIT 1 ");
		  if ($row = $sql->db_Fetch())
		  {
			$userjump['prev']['id'] = $row['user_id'];
			$userjump['prev']['name'] = $row['user_name'];
		  }
		  cachevars('userjump', $userjump);
		}
		if($parm == 'prev')
		{
			return isset($userjump['prev']['id']) ? "&lt;&lt; ".LAN_USER_40." [ <a href='".$url->create('user/profile/view', $userjump['prev'])."'>".$userjump['prev']['name']."</a> ]" : "&nbsp;";
		}
		else
		{
			return isset($userjump['next']['id']) ? "[ <a href='".$url->create('user/profile/view', $userjump['next'])."'>".$userjump['next']['name']."</a> ] ".LAN_USER_41." &gt;&gt;" : "&nbsp;";
		}
	}
	
	
	
	function sc_user_picture($parm) 
	{
		if ($this->var['user_sess'] && file_exists(e_MEDIA."avatars/".$this->var['user_sess']))
		{
			//return $tp->parseTemplate("{USER_AVATAR=".$this->var['user_image']."}", true); // this one will resize. 
			 return "<img src='".e_UPLOAD_ABS."public/avatars/".$this->var['user_sess']."' alt='' />";
		}
		else
		{
			return LAN_USER_42;
		}
	}
	
	/*  sc_USER_AVATAR - see single/user_avatar.php */ 
		
		
	function sc_user_picture_name($parm) 
	{
		if (ADMIN && getperms("4"))
		{
			return $this->var['user_sess'];
		}
	}
	
	
	
	function sc_user_picture_delete($parm) 
	{
		if (USERID == $this->var['user_id'] || (ADMIN && getperms("4")))
		{
			return "
			<form method='post' action='".e_SELF."?".e_QUERY."'>
			<input class='button' type='submit' name='delp' value='".LAN_USER_43."' />
			</form>
			";
		}
	}
	
	

	function sc_user_extended_all($parm) 
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		
		global $EXTENDED_CATEGORY_START, $EXTENDED_CATEGORY_END, $EXTENDED_CATEGORY_TABLE;
		$qry = "SELECT f.*, c.user_extended_struct_name AS category_name, c.user_extended_struct_id AS category_id FROM #user_extended_struct as f
			LEFT JOIN #user_extended_struct as c ON f.user_extended_struct_parent = c.user_extended_struct_id
			ORDER BY c.user_extended_struct_order ASC, f.user_extended_struct_order ASC
		";
		
		
		
		require_once(e_HANDLER."user_extended_class.php");
		
		$ue = new e107_user_extended;
		$ueCatList = $ue->user_extended_get_categories();
		$ueFieldList = $ue->user_extended_get_fields();
		$ueCatList[0][0] = array('user_extended_struct_name' => LAN_USER_44, 'user_extended_struct_text' => '');
		$ret = "";
		foreach($ueCatList as $catnum => $cat)
		{
			$key = $cat[0]['user_extended_struct_text'] ? $cat[0]['user_extended_struct_text'] : $cat[0]['user_extended_struct_name'];
			$cat_name = $tp->parseTemplate("{USER_EXTENDED={$key}.text.{$this->var['user_id']}}", TRUE);
			if($cat_name != FALSE && count($ueFieldList[$catnum]))
			{
		
				$ret .= str_replace("{EXTENDED_NAME}", $key, $EXTENDED_CATEGORY_START);
				foreach($ueFieldList[$catnum] as $f)
				{
					$key = $f['user_extended_struct_name'];
					if($ue_name = $tp->parseTemplate("{USER_EXTENDED={$key}.text.{$this->var['user_id']}}", TRUE))
					{
						$extended_record = str_replace("EXTENDED_ICON","USER_EXTENDED={$key}.icon", $EXTENDED_CATEGORY_TABLE);
					 	$extended_record = str_replace("{EXTENDED_NAME}", $tp->toHTML($ue_name,"","defs"), $extended_record);
						$extended_record = str_replace("EXTENDED_VALUE","USER_EXTENDED={$key}.value.{$this->var['user_id']}", $extended_record);
						if(HIDE_EMPTY_FIELDS === TRUE)
						{
							$this_value = $tp->parseTemplate("{USER_EXTENDED={$key}.value.{$this->var['user_id']}}", TRUE);
							if($this_value != "")
							{
								$ret .= $tp->parseTemplate($extended_record, TRUE);
							}
						}
						else
						{
							$ret .= $tp->parseTemplate($extended_record, TRUE);
						}
					}
				}
			}
			$ret .= $EXTENDED_CATEGORY_END;
		}
		return $ret;
	}


	
	function sc_profile_comments($parm) 
	{
		if(e107::getPref('profile_comments'))
		{
			$ret = e107::getComment()->compose_comment('profile', 'comment', $this->var['user_id'], null, $this->var['user_name'], FALSE,true);
		
		 	return e107::getRender()->tablerender($ret['caption'],$ret['comment_form']. $ret['comment'], 'profile_comments', TRUE);
		}
		return "";
	}
	
	
	
	function sc_profile_comment_form($parm='') // deprecated. 
	{
		return ;
	}
	
	
	
	function sc_total_users($parm='') 
	{
		global $users_total;
		return $users_total;
	}
	
	
	
	function sc_user_form_records($parm='') 
	{
		global $records, $user_frm;
		$ret = $user_frm->form_select_open("records");
		for($i=10; $i<=30; $i+=10)
		{
			$sel = ($i == $records ? true: false);
			$ret .= $user_frm->form_option($i, $sel, $i);
		}
		$ret .= $user_frm->form_select_close();
		return $ret;
	}
	
	
	function sc_user_form_order($parm) 
	{
		global $order;
		if ($order == "ASC")
		{
			$ret = "<select name='order' class='tbox'>
			<option value='DESC'>".LAN_USER_45."</option>
			<option value='ASC' selected='selected'>".LAN_USER_46."</option>
			</select>";
		}
		else
		{
			$ret = "<select name='order' class='tbox'>
			<option value='DESC' selected='selected'>".LAN_USER_45."</option>
			<option value='ASC'>".LAN_USER_46."</option>
			</select>";
		}
		return $ret;
	}
	
	
	function sc_user_form_start($parm) 
	{
		global $from;
		return "
		<form method='post' action='".e_SELF."'>
		<p><input type='hidden' name='from' value='$from' /></p>
		";
	}
	
	
	
	function sc_user_form_end($parm) 
	{
		return "</form>";
	}


	
	function sc_user_form_submit($parm) 
	{
		return "<input class='button' type='submit' name='submit' value='".LAN_USER_47."' />";
	}


	
	function sc_user_embed_userprofile($parm) 
	{
		global $pref, $USER_EMBED_USERPROFILE_TEMPLATE, $embed_already_rendered;
		
		//if no parm, it means we render ALL embedded contents
		//so we're preloading all registerd e_userprofile files
		$key = varset($pref['e_userprofile_list']); 
		
		//if we don't have any embedded contents, return
		if(!is_array($key) || empty($key)){ return; }
		
		//array holding specific hooks to render
		$render=array();
		
		if($parm){
			
			//if the first char of parm is an ! mark, it means it should not render the following parms
			if(strpos($parm,'!')===0){
				$tmp = explode(",", substr($parm,1) );
				foreach($tmp as $not){
					$not=trim($not);
					if(isset($key[$not])){
						//so we're unsetting them from the $key array
						unset($key[$not]);
					}
				}
			
			//else it means we render only the following parms
			}else{
				$tmp = explode(",", $parm );
				foreach($tmp as $yes){
					$yes=trim($yes);
					if(isset($key[$yes])){
						//so add the ones we need to render to the $render array
						$render[$yes] = $key[$yes];
					}
				}
				//finally assign the render array as the key array, overwriting it
				$key = $render;
			}
		}
		
		foreach($key as $hook){
			//include the e_user file and initiate the class
			if(is_readable(e_PLUGIN.$hook."/e_userprofile.php")){
				//if the current hook is not yet rendered
				if(!in_array($hook, $embed_already_rendered)){
					require_once(e_PLUGIN.$hook."/e_userprofile.php");
					$name = "e_userprofile_{$hook}";
					if(function_exists($name)){
						$arr[] = $name();
						//we need to store which hooks are already rendered
						$embed_already_rendered[] = $hook;
					}
				}
			}
		}
		
		$ret = '';
		foreach($arr as $data){
			if(is_array($data['caption'])){
				foreach($data['caption'] as $k=>$v){
					if(isset($data['caption'][$k]) && isset($data['text'][$k])){
						$search = array('{USER_EMBED_USERPROFILE_CAPTION}', '{USER_EMBED_USERPROFILE_TEXT}');
						$replace = array($data['caption'][$k], $data['text'][$k]);
						$ret .= str_replace($search, $replace, $USER_EMBED_USERPROFILE_TEMPLATE);
					}
				}
			}else{
				if(isset($data['caption']) && isset($data['text'])){
					$search = array('{USER_EMBED_USERPROFILE_CAPTION}', '{USER_EMBED_USERPROFILE_TEXT}');
					$replace = array($data['caption'], $data['text']);
					$ret .= str_replace($search, $replace, $USER_EMBED_USERPROFILE_TEMPLATE);
				}
			}
		}
		return $ret;
	}
	
	
	
	function sc_user_customtitle($parm) 
	{
		return $this->var['user_customtitle'];
	}
	 
	

}
?>