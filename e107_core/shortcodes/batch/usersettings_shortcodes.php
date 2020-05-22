<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $URL$
 * $Id$
 */

if (!defined('e107_INIT')) { exit; }


class usersettings_shortcodes extends e_shortcode
{
	private $extendedTabs = false;
	public $legacyTemplate = array();

	function sc_username($parm) // This is the 'display name'
	{
		$pref = e107::getPref();
		$dis_name_len = varset($pref['displayname_maxlength'], 15);

		if(check_class($pref['displayname_class']) || $pref['allowEmailLogin'] == 1) // display if email is used for login.
		{
			$options = array(
				'title' => LAN_USER_80,
				'size'  => 40,
			);

			return e107::getForm()->text('username', $this->var['user_name'], $dis_name_len, $options);
		}

		if($parm == 'show') // Show it, but as a readonly field.
		{
			$options = array(
				'title'    => LAN_USER_80,
				'size'     => 40,
				'readonly' => true,
			);

			return e107::getForm()->text('username', $this->var['user_name'], $dis_name_len, $options);
		}

		// Hide it!
		return '';
	}


	function sc_loginname($parm)
	{
		$pref = e107::getPref();

		if($pref['allowEmailLogin'] == 1) // email/password login only. 
		{
			return; // hide login name when email-login is being used. (may contain social login info)	
		}

		$log_name_length = varset($pref['loginname_maxlength'], 30);

		$options = array(
			'title' => ($pref['allowEmailLogin'] == 1) ? LAN_USER_82 : LAN_USER_80,
			'size'  => 40,
		);

		if(ADMIN && getperms("4")) // Has write permission.
		{
			return e107::getForm()->text('loginname', $this->var['user_loginname'], $log_name_length, $options);
		}

		// No write permission.
		$options['readonly'] = true;
		return e107::getForm()->text('loginname', $this->var['user_loginname'], $log_name_length, $options);
	}
	
	
	
	function sc_customtitle($parm)
	{ 	
		$pref = e107::getPref();
		if ($pref['signup_option_customtitle'])
		{		
			$options = array(
				'title'=> '', 
				'size' => 40,
				'required' => ($pref['signup_option_customtitle'] == 2));	
			return e107::getForm()->text('customtitle', $this->var['user_customtitle'], 100, $options);
		}
	}

	
	function sc_realname($parm)
	{ 	
		$pref = e107::getPref();
		if ($pref['signup_option_realname'])
		{		
			$sc = e107::getScBatch('usersettings');
			$options = array(
				'title'    => '',
				'size'     => 40,
				'required' => ($pref['signup_option_realname'] == 2),
			);
			if(!empty($sc->var['user_login']) && !empty($sc->var['user_xup'])) // social login active.
			{
				$options['readonly'] = true;
			}

			return e107::getForm()->text('realname', $sc->var['user_login'], 100, $options);
		}
	}

/*
	function sc_realname2($parm)
	{
		$pref = e107::getPref();
		$sc = e107::getScBatch('usersettings');

		$options = array(
			'title'    => '',
			'size'     => 40,
			'required' => $pref['signup_option_realname'],
		);

		if(!empty($sc->var['user_login']) && !empty($sc->var['user_xup'])) // social login active.
		{
			$options['readonly'] = true;
		}

		return e107::getForm()->text('realname', $sc->var['user_login'], 100, $options);
	}
*/	
	
	
	function sc_password1($parm)
	{ 
		$pref = e107::getPref();

		if(!empty($this->var['user_xup'])) // social login active.
		{
			return null;
		}
		
		if(!isset($pref['auth_method']) || $pref['auth_method'] == '' || $pref['auth_method'] == 'e107' || $pref['auth_method'] == '>e107')
		{
			$options = array('size' => 40,'title'=>LAN_USET_23, 'required'=>0,'autocomplete'=>'off'); 
			return e107::getForm()->password('password1', '', 20, $options);		
		}
		
		return "";
	}
	
	
	
	function sc_password2($parm)
	{ 
		$pref = e107::getPref();

		if(!empty($this->var['user_xup'])) // social login active.
		{
			return null;
		}
		
		if(!isset($pref['auth_method']) || $pref['auth_method'] == '' || $pref['auth_method'] == 'e107' || $pref['auth_method'] == '>e107')
		{
			$options = array('size' => 40,'title'=>LAN_USET_23, 'required'=>0); 
			return e107::getForm()->password('password2', '', 20, $options);	
		}
		
		return "";
	}
	
	
	
	function sc_password_len($parm)
	{ 
		$pref = e107::getPref();
		if(!isset($pref['auth_method']) || ($pref['auth_method'] != 'e107' && $pref['auth_method'] != '>e107'))
		{
			return "";
		}
		return $pref['signup_pass_len'];
	}



	function sc_email($parm)
	{
		$sc = e107::getScBatch('usersettings');

		$options = array(
			'size'     => 40,
			'title'    => '',
		);

		if (e107::getPref('disable_emailcheck') == 0) $options['required'] = true;

		if(!empty($sc->var['user_email']) && !empty($sc->var['user_xup'])) // social login active.
		{
			$options['readonly'] = true;
		}

		return e107::getForm()->email('email', $sc->var['user_email'], 100, $options);
	}
	
	
	
	function sc_hideemail($parm)
	{ 
		if($parm == 'radio')
		{
			$options['enabled'] = array('title' => LAN_USER_84);
			return "<div class='radio'>".e107::getForm()->radio_switch("hideemail", $this->var['user_hideemail'],LAN_YES,LAN_NO,$options)."</div>";
		}
	}
	
	
	
	function sc_userclasses($parm)
	{ 
		global $e_userclass;
		$tp 		= e107::getParser();
		$pref 		= e107::getPref();
		
		$ret = "";
		if(ADMIN && $this->var['user_id'] != USERID)
		{
			return "";
		}
		if (!is_object($e_userclass)) $e_userclass = new user_class;
		$ucList = $e_userclass->get_editable_classes(USERCLASS_LIST, TRUE);			// List of classes which this user can edit (as array)
		$ret = '';
		if(!count($ucList)) return;
		
		  $is_checked = array();
		  foreach ($ucList as $cid)
		  {
		    if (check_class($cid, $this->var['user_class'])) $is_checked[$cid] = $cid;
			if(isset($_POST['class']))
			{
		//	  $is_checked[$cid] = in_array($cid, $_POST['class']);
			}
		
		  }
		  $inclass = implode(',',$is_checked);
		
	//	  $ret = "<table style='width:95%;margin-left:0px'><tr><td class='defaulttext'>";
		  $ret .= $e_userclass->vetted_tree('class',array($e_userclass,'checkbox_desc'),$inclass,'editable, no-excludes');
	//	  $ret .= "</td></tr></table>\n";
		
		return $ret;
	}
	
	
	
	function sc_signature($parm)
	{
		$pref = e107::getPref();
		if(!check_class(varset($pref['signature_access'],0)))
		{
			return; 		
		} 
		//parse_str($parm);
		//$cols = (isset($cols) ? $cols : 58);
		//$rows = (isset($rows) ? $rows : 4);
		//return "<textarea class='tbox signature' name='signature' cols='{$cols}' rows='{$rows}' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$this->var['user_signature']."</textarea>"; 
		return e107::getForm()->bbarea('signature', $this->var['user_signature'], '', '', 'small');
	}
	
	
	/**
	 * @DEPRECATED - it is integreated with sc_signature now. 
	 */
	function sc_signature_help($parm)
	{
		return;
		/*
		$pref = e107::getPref();
		if(!check_class(varset($pref['signature_access'],0)))
		{
			return; 		
		}  
		return display_help("", 2);
		*/
	}
	
	
	
	function sc_avatar_upload($parm) // deprecated and combined into avatarpicker() (see sc_avatar_remote)
	{
		return; 
	}
	
	
	
	function sc_avatar_remote($parm)
	{
		if(!empty($this->var['user_xup'])) // social login active.
		{
		//	return $this->var['user_image'];
			return e107::getParser()->toAvatar($this->var);
		}

		return e107::getForm()->avatarpicker('image',$this->var['user_image'],array('upload'=>1)); 
	}
	
	
	
	function sc_avatar_choose($parm) // deprecated
	{
		return false;
	}
	
	
	
	function sc_photo_upload($parm)
	{ 
		$diz = LAN_USET_27.". ".LAN_USET_28.".";
		$text = '';

		if(USERPHOTO)
		{

			$text .= e107::getParser()->parseTemplate("{PICTURE}",true);
		}
		
		if (e107::getPref('photo_upload') && FILE_UPLOADS)
		{
			$text .= "<div class='checkbox form-check'>";
			$text .= e107::getForm()->checkbox('user_delete_photo', 1, false, LAN_USET_16);
			$text .= "</div>";	
		
			
		//	$text .=  "<input type='checkbox' name='user_delete_photo' value='1' />".LAN_USET_16."<br />\n";
			$text .= "<p><input class='tbox' name='file_userfile[photo]' type='file' size='47' title=\"".$diz."\" /></p>\n";

		}
		
		return $text;
	}
	
	
	
	function sc_userextended_all($parm='')
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		$frm = e107::getForm();
		
		
		$qry = "
		SELECT * FROM #user_extended_struct
		WHERE user_extended_struct_applicable IN (".$tp -> toDB($this->var['userclass_list'], true).")
		AND user_extended_struct_write IN (".USERCLASS_LIST.")
		AND user_extended_struct_type = 0
		ORDER BY user_extended_struct_order ASC";
		
		$ret="";
		
		if($sql->gen($qry))
		{
			$catList = $sql->db_getList();
		}
		else 
		{
			e107::getMessage()->addDebug("No extended fields found");
		}
		
		$catList[] = array("user_extended_struct_id" => 0, "user_extended_struct_name" => LAN_USET_7);
		
		$tabs = array();
		
		if($parm == 'tabs' && deftrue('BOOTSTRAP'))
		{
			$this->extendedTabs = true;	
		}
		
		
		foreach($catList as $cat)
		{
			cachevars("extendedcat_{$cat['user_extended_struct_id']}", $cat);
			$text = $this->sc_userextended_cat($cat['user_extended_struct_id']);
			$ret .= $text;
			$catName = vartrue($cat['user_extended_struct_text'], $cat['user_extended_struct_name']);
			if(!empty($text))
			{
				$tabs[] = array('caption'=>$catName, 'text'=>$text);
			}
		}
		
		
		
		if(($parm == 'tabs') && !empty($tabs) && deftrue('BOOTSTRAP'))
		{
			return e107::getForm()->tabs($tabs);		
		}
		
		
		return $ret;
	}


	function sc_userextended_cat($parm = '')
	{
		global $extended_showed;

		if(THEME_LEGACY === true)
		{
			$USER_EXTENDED_CAT = $this->legacyTemplate['USER_EXTENDED_CAT'];
		}
		else
		{
			$USER_EXTENDED_CAT = e107::getCoreTemplate('usersettings', 'extended-category');
		}


		$sql = e107::getDb();
		$tp = e107::getParser();

		if(isset($extended_showed['cat'][$parm]))
		{
			return "";
		}
		$ret = "";
		$catInfo = getcachedvars("extendedcat_{$parm}");
		if(!$catInfo)
		{
			$qry = "
			SELECT * FROM #user_extended_struct
			WHERE user_extended_struct_applicable IN (" . $tp->toDB($this->var['userclass_list'], true) . ")
			AND user_extended_struct_write IN (" . USERCLASS_LIST . ")
			AND user_extended_struct_id = " . intval($parm) . "
			";
			if($sql->gen($qry))
			{
				$catInfo = $sql->fetch();
			}
		}

		if($catInfo)
		{
			$qry = "
			SELECT * FROM #user_extended_struct
			WHERE user_extended_struct_applicable IN (" . $tp->toDB($this->var['userclass_list'], true) . ")
			AND user_extended_struct_write IN (" . USERCLASS_LIST . ")
			AND user_extended_struct_parent = " . intval($parm) . "
			AND user_extended_struct_type != 0
			ORDER BY user_extended_struct_order ASC
			";

			if($sql->gen($qry))
			{
				$fieldList = $sql->db_getList();
				foreach($fieldList as $field)
				{
					cachevars("extendedfield_{$field['user_extended_struct_name']}", $field);
					//TODO use $this instead of parseTemplate(); 
					$ret .= $this->sc_userextended_field($field['user_extended_struct_name']);
					//		$ret .= $tp->parseTemplate("{USEREXTENDED_FIELD={$field['user_extended_struct_name']}}", TRUE, $usersettings_shortcodes);
				}
			}
		}

		if($ret && $this->extendedTabs == false)
		{
			$catName = $catInfo['user_extended_struct_text'] ? $catInfo['user_extended_struct_text'] : $catInfo['user_extended_struct_name'];
			if(defined($catName))
			{
				$catName = constant($catName);
			}
			$ret = str_replace("{CATNAME}", $tp->toHTML($catName, false, 'emotes_off,defs'), $USER_EXTENDED_CAT) . $ret;
		}

		$extended_showed['cat'][$parm] = 1;

		return $ret;
	}


	function sc_userextended_field($parm = '')
	{
		global $extended_showed;

		$ue = e107::getUserExt();


		if(THEME_LEGACY === true || !deftrue('BOOTSTRAP'))
		{
			$USEREXTENDED_FIELD = $this->legacyTemplate['USEREXTENDED_FIELD'];
			$REQUIRED_FIELD = $this->legacyTemplate['REQUIRED_FIELD'];
		}
		else
		{
			$USEREXTENDED_FIELD = e107::getCoreTemplate('usersettings', 'extended-field');
			$REQUIRED_FIELD = '';
		}


		if(isset($extended_showed['field'][$parm]))
		{
			return "";
		}

		$sql = e107::getDb();
		$tp = e107::getParser();

		$ret = "";

		$fInfo = getcachedvars("extendeddata_{$parm}");

		if(!$fInfo)
		{
			$qry = "
			SELECT * FROM #user_extended_struct
			WHERE user_extended_struct_applicable IN (" . $tp->toDB($this->var['userclass_list'], true) . ")
			AND user_extended_struct_write IN (" . USERCLASS_LIST . ")
			AND user_extended_struct_name = '" . $tp->toDB($parm, true) . "'
			";
			if($sql->gen($qry))
			{
				$fInfo = $sql->fetch();
			}
		}

		if($fInfo)
		{
			$fname = $fInfo['user_extended_struct_text'];

			if(defined($fname))
			{
				$fname = constant($fname);
			}

			$fname = $tp->toHTML($fname, "", "emotes_off, defs");

			if($fInfo['user_extended_struct_required'] == 1 && !deftrue('BOOTSTRAP'))
			{
				$fname = str_replace("{FIELDNAME}", $fname, $REQUIRED_FIELD);
			}

			$parms = explode("^,^", $fInfo['user_extended_struct_parms']);

			$fhide = "";

			if(varset($parms[3]))
			{
				$chk = (strpos($this->var['user_hidden_fields'], "^user_" . $parm . "^") === false) ? false : true;

				if(isset($_POST['updatesettings']))
				{
					$chk = isset($_POST['hide']['user_' . $parm]);
				}

				$fhide = $ue->user_extended_hide($fInfo, $chk);
			}

			$uVal = str_replace(chr(1), "", $this->var['user_' . $parm]);
			$fval = $ue->user_extended_edit($fInfo, $uVal);


			$rVal = !empty($fInfo['user_extended_struct_required']) ;

			$ret = $USEREXTENDED_FIELD;
			$ret = str_replace("{FIELDNAME}", $fname, $ret);
			$ret = str_replace("{FIELDVAL}", $fval, $ret);
			$ret = str_replace("{HIDEFIELD}", $fhide, $ret);
			$ret = str_replace("{REQUIRED}", $this->required($rVal), $ret);
		}

		$extended_showed['field'][$parm] = 1;

		return $ret;
	}


	function sc_updatesettingsbutton($parm='')
	{
		
		return "<input class='button btn btn-primary' type='submit' name='updatesettings' value='".LAN_USET_37."' />";	
		
	}

	private function required($val=null)
	{
		if(empty($val))
		{
			return '';
		}

		return "<span class='required'><!-- empty --></span>";

	}

	function sc_deleteaccountbutton($parm=array())
	{

		if((int) $_GET['id'] !== USERID)
		{
			return null;
		}
		
		$pref = e107::getPref();
		if($pref['del_accu'] == 1)
		{
			$confirm    = defset("LAN_USET_51", "Are you sure? This procedure cannot be reversed! Once completed all personal data that you have entered on this site will be permanently lost and you will no longer be able to login.");
			$label      = defset('LAN_USET_50', "Delete All Account Information");

			$parm['confirm'] = $confirm;

			return e107::getForm()->button('delete_account',1, 'delete', $label, $parm);
		}
		else
		{
			return null;
		}

	}

}
?>