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

if(!defined('e107_INIT'))
{
	exit;
}

e107::coreLan('user');
e107::coreLan('usersettings');


class usersettings_shortcodes extends e_shortcode
{

	private $extendedTabs = false;
	public $legacyTemplate = array();
	private $pref;
	private $extendedShown = array();

	private $catInfo = array(); // user's extended-field category list data;
	private $fieldInfo = array();  // user's extended-field field list data;

	function __construct()
	{
		$this->pref = e107::getPref();
	}

	// Reset so that extended field data is reloaded.
	public function reset()
	{
		$this->extendedShown = array();
		$this->fieldInfo = array();
		$this->catInfo = array();
		$this->extendedTabs = false;
	}


	function sc_username($parm = null) // This is the 'display name'
	{

		$pref = $this->pref;
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


	function sc_loginname($parm = null)
	{

		if($this->pref['allowEmailLogin'] == 1) // email/password login only.
		{
			return; // hide login name when email-login is being used. (may contain social login info)	
		}

		$log_name_length = varset($this->pref['loginname_maxlength'], 30);

		$options = array(
			'title' => ($this->pref['allowEmailLogin'] == 1) ? LAN_USER_82 : LAN_USER_80,
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


	function sc_customtitle($parm = null)
	{

		if($this->pref['signup_option_customtitle'])
		{
			$options = array(
				'title'    => '',
				'size'     => 40,
				'required' => ($this->pref['signup_option_customtitle'] == 2));

			return e107::getForm()->text('customtitle', $this->var['user_customtitle'], 100, $options);
		}
	}


	function sc_realname($parm = null)
	{

		if($this->pref['signup_option_realname'])
		{
			$sc = e107::getScBatch('usersettings');
			$options = array(
				'title'    => '',
				'size'     => 40,
				'required' => ($this->pref['signup_option_realname'] == 2),
			);
			if(!empty($sc->var['user_login']) && !empty($sc->var['user_xup'])) // social login active.
			{
				$options['readonly'] = true;
			}

			return e107::getForm()->text('realname', $sc->var['user_login'], 100, $options);
		}
	}

	/*
		function sc_realname2($parm=null)
		{

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


	function sc_password1($parm = null)
	{

		if(!empty($this->var['user_xup'])) // social login active.
		{
			return null;
		}

		if(!isset($pref['auth_method']) || $pref['auth_method'] == '' || $pref['auth_method'] == 'e107' || $pref['auth_method'] == '>e107')
		{
			$options = array('size' => 40, 'title' => LAN_USET_23, 'required' => 0, 'autocomplete' => 'new-password');

			return e107::getForm()->password('password1', '', 20, $options);
		}

		return "";
	}


	function sc_password2($parm = null)
	{

		if(!empty($this->var['user_xup'])) // social login active.
		{
			return null;
		}

		if(!isset($this->pref['auth_method']) || $this->pref['auth_method'] == '' || $this->pref['auth_method'] == 'e107' || $this->pref['auth_method'] == '>e107')
		{
			$options = array('size' => 40, 'title' => LAN_USET_23, 'required' => 0);

			return e107::getForm()->password('password2', '', 20, $options);
		}

		return "";
	}


	function sc_password_len($parm = null)
	{

		if(!isset($this->pref['auth_method']) || ($this->pref['auth_method'] != 'e107' && $this->pref['auth_method'] != '>e107'))
		{
			return "";
		}

		return $this->pref['signup_pass_len'];
	}


	function sc_email($parm = null)
	{

		$sc = $this;

		$options = array(
			'size'  => 40,
			'title' => '',
		);

		if(e107::getPref('disable_emailcheck') == 0)
		{
			$options['required'] = true;
		}

		if(!empty($sc->var['user_email']) && !empty($sc->var['user_xup'])) // social login active.
		{
			$options['readonly'] = true;
		}

		return e107::getForm()->email('email', $sc->var['user_email'], 100, $options);
	}


	function sc_hideemail($parm = null)
	{

		if($parm == 'radio')
		{
			$options['enabled'] = array('title' => LAN_USER_84);

			return "<div class='radio'>" . e107::getForm()->radio_switch("hideemail", $this->var['user_hideemail'], LAN_YES, LAN_NO, $options) . "</div>";
		}
	}


	function sc_userclasses($parm = null)
	{

		global $e_userclass;
		$tp = e107::getParser();

		$ret = "";
		if(ADMIN && $this->var['user_id'] != USERID)
		{
			return "";
		}
		if(!is_object($e_userclass))
		{
			$e_userclass = new user_class;
		}
		$ucList = $e_userclass->get_editable_classes(USERCLASS_LIST, true);            // List of classes which this user can edit (as array)
		$ret = '';
		if(!count($ucList))
		{
			return;
		}

		$is_checked = array();
		foreach($ucList as $cid)
		{
			if(check_class($cid, $this->var['user_class']))
			{
				$is_checked[$cid] = $cid;
			}
			//	if(isset($_POST['class']))
			//	{
			//	  $is_checked[$cid] = in_array($cid, $_POST['class']);
			//	}

		}
		$inclass = implode(',', $is_checked);

		//	  $ret = "<table style='width:95%;margin-left:0px'><tr><td class='defaulttext'>";
		$ret .= $e_userclass->vetted_tree('class', array($e_userclass, 'checkbox_desc'), $inclass, 'editable, no-excludes');

		//	  $ret .= "</td></tr></table>\n";

		return $ret;
	}


	function sc_signature($parm = null)
	{

		if(!check_class(varset($this->pref['signature_access'], 0)))
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
	function sc_signature_help($parm = null)
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


	function sc_avatar_upload($parm = null) // deprecated and combined into avatarpicker() (see sc_avatar_remote)
	{

		return;
	}


	function sc_avatar_remote($parm = null)
	{

		if(!empty($this->var['user_xup'])) // social login active.
		{
			//	return $this->var['user_image'];
			return e107::getParser()->toAvatar($this->var);
		}

		return e107::getForm()->avatarpicker('image', $this->var['user_image'], array('upload' => 1));
	}


	function sc_avatar_choose($parm = null) // deprecated
	{

		return false;
	}


	function sc_photo_upload($parm = null)
	{

		$diz = LAN_USET_27 . ". " . LAN_USET_28 . ".";
		$text = '';

		if(defset('USERPHOTO'))
		{
			$text .= e107::getParser()->parseTemplate("{PICTURE}", true);
		}

		if(e107::getPref('photo_upload') && FILE_UPLOADS)
		{
			$text .= "<div class='checkbox form-check'>";
			$text .= e107::getForm()->checkbox('user_delete_photo', 1, false, LAN_USET_16);
			$text .= "</div>";


			//	$text .=  "<input type='checkbox' name='user_delete_photo' value='1' />".LAN_USET_16."<br />\n";
			$text .= "<p><input class='tbox' name='file_userfile[photo]' type='file' size='47' title=\"" . $diz . "\" /></p>\n";

		}

		return $text;
	}


	function sc_userextended_all($parm = '')
	{

		$tp = e107::getParser();
		$frm = e107::getForm();

		$this->reset();

		if(empty($this->catInfo))
		{
			$this->loadUECatData();
		}
		if(empty($this->fieldInfo))
		{
			$this->loadUEFieldData();
		}

		$catList = $this->catInfo;

		$tabs = array();

		if($parm === 'tabs' && deftrue('BOOTSTRAP'))
		{
			$this->extendedTabs = true;
		}

		$ret = '';

		foreach($catList as $cat)
		{
			$this->catInfo[$cat['user_extended_struct_id']] = $cat;
			$text = $this->sc_userextended_cat($cat['user_extended_struct_id']);
			$ret .= $text;
			$catName = vartrue($cat['user_extended_struct_text'], $cat['user_extended_struct_name']);
			if(!empty($text))
			{
				$tabs[] = array('caption' => $catName, 'text' => $text);
			}
		}

		if(($parm == 'tabs') && !empty($tabs) && deftrue('BOOTSTRAP'))
		{
			return e107::getForm()->tabs($tabs);
		}

		return $ret;
	}


	public function sc_userextended_cat($parm = 0)
	{

		$parm = (int) $parm;

		if(empty($this->catInfo))
		{
			$this->loadUECatData('write');
		}

		if(THEME_LEGACY === true)
		{
			$USER_EXTENDED_CAT = $this->legacyTemplate['USER_EXTENDED_CAT'];
		}
		else
		{
			$USER_EXTENDED_CAT = e107::getCoreTemplate('usersettings', 'extended-category');
		}

		if(empty($USER_EXTENDED_CAT))
		{
			trigger_error('User settings template key "extended-category" was empty', E_USER_NOTICE);
		}


		$tp = e107::getParser();

		if(!empty($this->extendedShown['cat'][$parm]))
		{
			trigger_error('Category already shown. Use ->reset()', E_USER_NOTICE);

			return "";
		}

		$catInfo = varset($this->catInfo[$parm]);

		if(empty($catInfo))
		{
			return null;
		}

		$ret = '';

		if($fieldList = $this->loadUEFieldData('write', $parm))
		{
			foreach($fieldList as $field => $row)
			{
				$ret .= $this->sc_userextended_field($field);
			}
		}

	//	if(empty($ret))
	//	{
		//	trigger_error(__METHOD__ . ' returned nothing. Line: ' . __LINE__, E_USER_NOTICE);
	//	}


		if(!empty($ret) && ($this->extendedTabs === false))
		{
			$catName = !empty($catInfo['user_extended_struct_text']) ? $catInfo['user_extended_struct_text'] : $catInfo['user_extended_struct_name'];
			$ret = str_replace("{CATNAME}", $tp->toHTML($catName, false, 'TITLE'), $USER_EXTENDED_CAT) . $ret;
		}



		$this->extendedShown['cat'][$parm] = true;

		return $ret;
	}


	/**
	 * Return a list of User-Extended categories based on the logged in user permissions.
	 * For Internal Use Only
	 * @param string $perm read|write|applicable
	 */
	public function loadUECatData($perm = 'read')
	{

		$ue = e107::getUserExt();
		$data = (array) $ue->getCategories();
		$uclass = !empty($this->var['userclass_list']) ? $this->var['userclass_list'] : USERCLASS_LIST;

		$this->catInfo = [];
		foreach($data as $id => $row)
		{
			$userclass = (int) $row['user_extended_struct_' . $perm];
			if(check_class($userclass, $uclass))
			{
				$this->catInfo[$id] = $row;
			}

		}

		$this->catInfo[0] = array("user_extended_struct_id" => 0, "user_extended_struct_name" => LAN_USET_7);

		return $this->catInfo;
	}


	/**
	 * Get Fields by category ID and perms v2.3.1 for the current user. ie. respecting userclass permissionss.
	 * For Internal Use Only
	 * @param string $perm
	 * @return array
	 */
	public function loadUEFieldData($perm = 'read', $cat = null)
	{

		$uclass = !empty($this->var['userclass_list']) ? $this->var['userclass_list'] : USERCLASS_LIST;

		$ue = e107::getUserExt();
		$data = (array) $ue->getFields($cat);

	//	if(empty($data))
	//	{
			// trigger_error('$data was empty', E_USER_NOTICE);
	//	}

		$this->fieldInfo = [];
		foreach($data as $k => $row)
		{
			$fieldname = 'user_' . $row['user_extended_struct_name'];

			if($ue->hasPermission($fieldname, $perm, $uclass) && $ue->hasPermission($fieldname, 'applicable', $uclass))
			{
				$key = $row['user_extended_struct_name'];
				$this->fieldInfo[$key] = $row;
			}

		}

		return $this->fieldInfo;
	}


	/**
	 * @param string $parm extended field name without the 'user_' prefix.
	 * @return string|string[]
	 */
	function sc_userextended_field($parm = null)
	{

		if(empty($parm) || !empty($this->extendedShown['field'][$parm]))
		{
			return '';
		}

		if(empty($this->fieldInfo))
		{
			$this->loadUEFieldData('write');
		}

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


		$tp = e107::getParser();

		$ret = "";

		$fInfo = varset($this->fieldInfo[$parm]);

		if(empty($fInfo))
		{
			trigger_error('$fInfo was empty', E_USER_NOTICE);
			return null;
		}

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

		$uVal = str_replace(chr(1), "", varset($this->var['user_' . $parm]));
		$fval = (string) $ue->user_extended_edit($fInfo, $uVal);


		$rVal = !empty($fInfo['user_extended_struct_required']);

		$ret = $USEREXTENDED_FIELD;
		$ret = str_replace("{FIELDNAME}", $fname, $ret);
		$ret = str_replace("{FIELDVAL}", $fval, $ret);
		$ret = str_replace("{HIDEFIELD}", $fhide, $ret);
		$ret = str_replace("{REQUIRED}", $this->required($rVal), $ret);


		$this->extendedShown['field'][$parm] = true;

		return $ret;
	}


	function sc_updatesettingsbutton($parm = '')
	{

		return "<input class='button btn btn-primary' type='submit' name='updatesettings' value='" . LAN_USET_37 . "' />";

	}

	private function required($val = null)
	{

		if(empty($val))
		{
			return '';
		}

		return "<span class='required'><!-- empty --></span>";

	}

	function sc_required($parm = null)
	{

		if(empty($parm) || !isset($this->pref['signup_option_' . $parm]))
		{
			return null;
		}

		if($parm === 'email' && !e107::getPref('disable_emailcheck'))
		{
			return $this->required(true);
		}

		if((int) $this->pref['signup_option_' . $parm] === 2)
		{
			return $this->required(true);
		}

	}

	function sc_deleteaccountbutton($parm = array())
	{

		if(!empty($_GET['id']) && (int) $_GET['id'] !== USERID)
		{
			return null;
		}


		if($this->pref['del_accu'] == 1)
		{
			$confirm = defset("LAN_USET_51", "Are you sure? This procedure cannot be reversed! Once completed all personal data that you have entered on this site will be permanently lost and you will no longer be able to login.");
			$label = defset('LAN_USET_50', "Delete All Account Information");

			$parm['confirm'] = $confirm;

			return e107::getForm()->button('delete_account', 1, 'delete', $label, $parm);
		}
		else
		{
			return null;
		}

	}

}
