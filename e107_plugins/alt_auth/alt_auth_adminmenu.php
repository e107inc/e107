<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Common admin/configuration functions for alt_auth plugin
 *
 * $URL$
 * $Id$
 */

/**
 *	e107 Alternate authorisation plugin
 *
 *	@package	e107_plugins
 *	@subpackage	alt_auth
 *	@version 	$Id$;
 */



/*
TODO:
	1. Header
	2. Support array of defaults for table
	3. Get rid of all the globals (put into a class?)
*/

if (!defined('e107_INIT')) { exit; }



  define('AUTH_SUCCESS', -1);
  define('AUTH_NOUSER', 1);
  define('AUTH_BADPASSWORD', 2);
  define('AUTH_NOCONNECT', 3);
  define('AUTH_UNKNOWN', 4);
  define('AUTH_NOT_AVAILABLE', 5);


require_once(e_HANDLER.'user_extended_class.php');
require_once(e_PLUGIN.'alt_auth/alt_auth_login_class.php');		// Has base methods class



class alt_auth_admin extends alt_auth_base
{
	private $euf = FALSE;

	public function __construct()
	{
		$this->euf = new e107_user_extended;
	}



	/**
	 *	Get list of supported authentication methods
	 *	Searches for files *_auth.php in the plugin directory
	 *
	 *	@param boolean $incE107 - if TRUE, 'e107' is included as an authentication method.
	 *
	 *	@return array of authentication methods in value fields
	 */
	public function alt_auth_get_authlist($incE107 = TRUE)
	{
		$authlist = $incE107 ? array('e107') : array();
		$handle = opendir(e_PLUGIN.'alt_auth');
		while ($file = readdir($handle))
		{
			if(preg_match("/^(.+)_auth\.php/", $file, $match))
			{
				$authlist[] = $match[1];
			}
		}
		closedir($handle);
		return $authlist;
	}



	/**
	 *	Return HTML for selector for authentication method
	 *
	 *	@param string $name - the name of the selector
	 *	@param string $curval - current value (if any)
	 *	@param string $optlist - comma-separated list of options to be included as choices
	 */
	public function alt_auth_get_dropdown($name, $curval = '', $options = '')
	{
		$optList = explode(',', $options);
		$authList = array_merge($optList, $this->alt_auth_get_authlist(FALSE));
		$ret = "<select class='tbox' name='{$name}'>\n";
		foreach ($authList as $v)
		{
			$sel = ($curval == $v ? " selected = 'selected' " : '');
			$ret .= "<option value='{$v}'{$sel} >{$v}</option>\n";
		}
		$ret .= "</select>\n";
		return $ret;
	}



	/**
	 *	All user fields which might, just possibly, be transferred. The array key is the corresponding field in the E107 user database; code prefixes it
	 *	with 'xf_' to get the parameter
	 *	'default' may be a single value to set the same for all connect methods, or an array to set different defaults.
	 */
	private $alt_auth_user_fields = array(
	  'user_id' 		=> array('prompt' => "User Id", 'help'=>'Use with caution', 'default' => false, 'optional' =>  TRUE, 'otherdb' =>  FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
	  'user_email' 		=> array('prompt' => LAN_ALT_12, 'default' => 'user_email', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => 'mail'),
	  'user_hideemail' 	=> array('prompt' => LAN_ALT_13, 'default' => 'user_hideemail', 'optional' =>  TRUE, 'otherdb' => TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => '', method => 'bool1'),
	  'user_name' 		=> array('prompt' => LAN_ALT_14, 'default' => 'user_name', 'optional' => TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => ''),
	  'user_login'		=> array('prompt' => LAN_ALT_15, 'default' => 'user_login', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => 'sn'),
	  'user_customtitle'=> array('prompt' => LAN_ALT_16, 'default' => 'user_customtitle', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
	  'user_signature' 	=> array('prompt' => LAN_ALT_17, 'default' => 'user_signature', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
	  'user_image' 		=> array('prompt' => LAN_ALT_18, 'default' => 'user_image', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
	  'user_sess' 		=> array('prompt' => LAN_ALT_19, 'default' => 'user_sess', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
	  'user_join' 		=> array('prompt' => LAN_ALT_20, 'default' => 'user_join', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => ''),
	  'user_ban'		=> array('prompt' => LAN_ALT_21, 'default' => 'user_ban', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
	  'user_class'		=> array('prompt' => LAN_ALT_22, 'default' => 'user_class', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE)
	);



	/**
	 *	Returns a block of table rows with user DB fields and either checkboxes or entry boxes
	 *
	 *	@param string $tableType is the prefix used, without the following underscore
	 *	@param $frm is the form object to use to create the text
	 *	@param array $parm is the array of options for the current auth type as read from the DB
	 */
	public function alt_auth_get_field_list($tableType, $frm, $parm, $asCheckboxes = FALSE)
	{
		$ret = '';
		foreach ($this->alt_auth_user_fields as $f => $v)
		{
			if (vartrue($v['showAll']) || vartrue($v[$tableType]))
			{
				$ret .= "<tr><td$log>";
				if ($v['optional'] == FALSE) $ret .= '*&nbsp;';
				$ret .= $v['prompt'].':';

				$ret .= "</td><td class='form-inline' $log>";
	//			$fieldname = $tableType.'_'.$v['optname'];
				$fieldname = $tableType.'_xf_'.$f;			// Name of the input box
				$value = varset($v['default'],'');
				if (is_array($value))
				{
					$value = varset($value[$tableType],'');
				}
				if (isset($v[$tableType.'_field'])) $value = $v[$tableType.'_field'];
				if (isset($parm[$fieldname])) $value = $parm[$fieldname];
	//	  		echo "Field: {$fieldname} => {$value}<br />";
				if ($asCheckboxes)
				{
					$ret .= $frm -> form_checkbox($fieldname, 1, $value);
				}
				else
				{
					$ret .= $frm -> form_text($fieldname, 35, $value, 120);
					if (isset($v['method']) && $v['method'])
					{
						$fieldMethod = $tableType.'_pm_'.$f;			// Processing method ID code
						$method = varset($parm[$fieldMethod],'');
						$ret .= '&nbsp;&nbsp;'.$this->alt_auth_processing($fieldMethod,$v['method'], $method);
					}
				}
				if (isset($v['help']))
				{
					$ret .= "<span class='field-help smalltext'>".$v['help']."</span>";
				}


				$ret .= "</td></tr>\n";
			}
		}
		return $ret;
	}



	/**
	 *	Returns a list of all the user-related fields allowed as an array, whhere the key is the field name
	 *
	 *	@param string $tableType is the prefix used, without the following underscore
	 *
	 *	@return array
	 */
	public function alt_auth_get_allowed_fields($tableType)
	{
		$ret = array();
		foreach ($this->alt_auth_user_fields as $f => $v)
		{
			if (vartrue($v['showAll']) || vartrue($v[$tableType]))
			{
	//	  $fieldname = $tableType.'_'.$v['optname'];
				$fieldname = $tableType.'_xf_'.$f;			// Name of the input box
				$ret[$fieldname] = '1';
			}
		}
		return $ret;
	}



	/**
	 *	Routine adds the extended user fields which may be involved into the table of field definitions, so that they're displayed
	 */
	public function add_extended_fields()
	{
		global $pref;

		if (!isset($pref['auth_extended'])) return;
		if (!$pref['auth_extended']) return;

		static $fieldsAdded = FALSE;

		if ($fieldsAdded) return;
		$xFields = $this->euf->user_extended_get_fieldList('','user_extended_struct_name');
	//	print_a($xFields);
		$fields = explode(',',$pref['auth_extended']);
		foreach ($fields as $f)
		{
			if (isset($xFields[$f]))
			{
				$this->alt_auth_user_fields['x_'.$f] = array('prompt' => varset($xFields[$f]['user_extended_struct_text'],'').' ('.$f.')',
														'default' => varset($xFields[$f]['default'],''),
														'optional' => TRUE,
														'showAll' => TRUE,			// Show for all methods - in principle, its likely to be wanted for all
														'method'  => '*' 			// Specify all convert methods - have little idea what may be around
														);
			}
		}
		$fieldsAdded = TRUE;
	}



	/**
	 *	List of the standard fields which may be displayed for any method.
	 */
	private $common_fields = array(
	  'server' => array('fieldname' => 'server',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_32, 'help' => ''),
	   'port' => array('fieldname' => 'port',	'size' => 4, 'max_size' => 7, 'prompt' => LAN_ALT_80, 'help' => 'eg. 3306'),

	  'uname'  => array('fieldname' => 'username',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_33, 'help' => ''),
	  'pwd'    => array('fieldname' => 'password',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_34, 'help' => ''),
	  'db'     => array('fieldname' => 'database',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_35, 'help' => ''),
	  'table'  => array('fieldname' => 'table',		'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_36, 'help' => ''),
	  'prefix' => array('fieldname' => 'prefix',	'size' => 35, 'max_size' =>  35, 'prompt' => LAN_ALT_39, 'help' => ''),
	  'ufield' => array('fieldname' => 'user_field','size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_37, 'help' => ''),
	  'pwfield'=> array('fieldname' => 'password_field','size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_38, 'help' => ''),
	  'salt'   => array('fieldname' => 'password_salt','size' => 35, 'max_size' => 120,  'prompt' => LAN_ALT_24, 'help' => LAN_ALT_25),
	  'classfilt' => array('fieldname' => 'filter_class', 'size' => 10, 'max_size' =>  8, 'prompt' => LAN_ALT_76, 'help' => LAN_ALT_77)
	);



	/**
	 *	Return the HTML for all server-related fields required for configuration of a particular method.
	 *	Each is a row of a table having two columns (no <table>...</table> etc added, so can be embedded in a larger table
	 *
	 *	@param string $prefix is the prefix used, without the following underscore
	 *	@param $frm is the form object to use
	 *	@param array $parm is an array of the current values of each item
	 *	@param string $fields is a list of the fields to display, separated by '|'. The names are the key values from $common_fields table
	 *
	 */
	public function alt_auth_get_db_fields($prefix, $frm, $parm, $fields = 'server|uname|pwd|db|table|ufield|pwfield')
	{
		$opts = explode('|',$fields);
		$ret = '';
		foreach ($this->common_fields as $fn => $cf)
		{
			if (in_array($fn,$opts))
			{
				$ret .= "<tr><td$log>".$cf['prompt'];

				$ret .= "</td><td$log>";

				if ($cf['fieldname'] == 'password')
				{
					$ret .= $frm->form_password($prefix.'_'.$cf['fieldname'], $cf['size'], $parm[$prefix.'_'.$cf['fieldname']], $cf['max_size']);
				}
				else
				{
					$ret .= $frm->form_text($prefix.'_'.$cf['fieldname'], $cf['size'], $parm[$prefix.'_'.$cf['fieldname']], $cf['max_size']);
				}
				if ($cf['help']) $ret .= "<br /><span class='field-help'>".$cf['help']."</span>";
				$ret .= "</td></tr>\n";
			}
		}
		return $ret;
	}



	/**
	 *	Write all the options for a particular authentication type to the DB
	 *
	 *	@var string $prefix - the prefix string representing the authentication type (currently importdb|e107db|otherdb|ldap|radius). Must NOT have a trailing underscore
	 */
	public function alt_auth_post_options($prefix)
	{
		$sql = e107::getDb();
		$lprefix = $prefix.'_';

		$user_fields = $this->alt_auth_get_allowed_fields($prefix);		// Need this list in case checkboxes for parameters
		foreach ($user_fields as $k => $v)
		{
			if (!isset($_POST[$k]))
			{
				$_POST[$k] = '0';
			}
		}


		// Now we can post everything
		foreach($_POST as $k => $v)
		{
			if (strpos($k,$lprefix) === 0)
			{
				$v = base64_encode(base64_encode($v));
				if($sql -> db_Select('alt_auth', '*', "auth_type='{$prefix}' AND auth_parmname='{$k}' "))
				{
					$sql -> db_Update('alt_auth', "auth_parmval='{$v}' WHERE  auth_type='{$prefix}' AND auth_parmname='{$k}' ");
				}
				else
				{
					$sql -> db_Insert('alt_auth', "'{$prefix}','{$k}','{$v}' ");
				}
			}
		}
		e107::getLog()->add('AUTH_03',$prefix,E_LOG_INFORMATIVE,'');
		return LAN_ALT_UPDATED;
	}



	/**
	 * Get the HTML for a password type selector.
	 *
	 *	@param string $name - name to be used for selector
	 *	@param $frm - form object to use
	 *	@param string $currentSelection - current value (if any)
	 *	@param boolean $getExtended - return all supported password types if TRUE, 'core' password types if FALSE
	 */
	public function altAuthGetPasswordSelector($name, $frm, $currentSelection = '', $getExtended = FALSE)
	{
		$password_methods = ExtendedPasswordHandler::GetPasswordTypes($getExtended);
		$text = "";
		$text .= $frm->form_select_open($name);
		foreach($password_methods as $k => $v)
		{
			$sel = ($currentSelection == $k) ? " Selected='selected'" : '';
			$text .= $frm -> form_option($v, $sel, $k);
		}
		$text .= $frm->form_select_close();
		return $text;
	}




	/**
	 *	Return the HTML needed to display the test form.
	 *
	 *	@param string $prefix - the type of connection being tested
	 *	@param $frm - the form object to use
	 *
	 *	if $_POST['testauth'] is set, attempts to validate the connection, and displays any returned values
	 */
	public function alt_auth_test_form($prefix, $frm)
	{
		$text = '';

		if(!empty($_POST['testauth']))
		{
			// Try and connect to DB/server, and maybe validate user name
			require_once(e_PLUGIN.'alt_auth/'.$prefix.'_auth.php');
			e107::getDebug()->log('Loading: alt_auth/'.$prefix.'_auth.php');

			$_login = new auth_login;
			$log_result = AUTH_UNKNOWN;
			$pass_vars = array();
			$val_name = trim(varset($_POST['nametovalidate'],''));

			if(isset($_login->Available) && ($_login->Available === FALSE))
			{	// Relevant auth method not available (e.g. PHP extension not loaded)
				$log_result = AUTH_NOT_AVAILABLE;
			}
			else
			{
				$log_result = $_login->login($val_name, $_POST['passtovalidate'], $pass_vars, ($val_name == ''));
			}

			$text = "<table class='table'>
	<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
			<tr><th colspan='2'>".LAN_ALT_48."</th></tr>";
			$text .= "<tr><td>";

			if ($val_name)
			{
				$text .= LAN_ALT_49.": ".$val_name.'<br />'.LAN_ALT_50.": ";
				if (varset($_POST['passtovalidate'],'')) $text .= str_repeat('*',strlen($_POST['passtovalidate'])); else $text .= LAN_ALT_51;
			}
			$text .= "</td><td>";

			$err = '';

			switch ($log_result)
			{
				case AUTH_SUCCESS :
					$text .= "<div class='alert alert-success' style='margin:0'>";
					$text .= LAN_ALT_58;
					if (count($pass_vars))
					{
					  $text .= '<br />'.LAN_ALT_59;
					  foreach ($pass_vars as $k => $v)
					  {
						$text .= '<br />&nbsp;&nbsp;'.$k.'=>'.$v;
					  }
					}
					$text .= "</div>";
					break;
				case AUTH_NOUSER :
					$err = LAN_ALT_52.LAN_ALT_55;
					break;
				case AUTH_BADPASSWORD :
					$err = LAN_ALT_52.LAN_ALT_56;
					break;
				case AUTH_NOCONNECT :
					$err = LAN_ALT_52.LAN_ALT_54;
					break;
				case AUTH_UNKNOWN :
					$err = LAN_ALT_52.LAN_ALT_53;
					break;
				case AUTH_NOT_AVAILABLE :
					$err = LAN_ALT_52.LAN_ALT_57;
					break;
				case LOGIN_CONTINUE:
					$err = "wrong encoding?";
				break;
				default :
					$err = "Coding error";
					var_dump($log_result);
			}

			if(!empty($err))
			{
				$text .= "<div class='alert alert-danger' style='margin:0'>".$err."</div>";
			}

			if(!empty($_login ->ErrorText))
			{
				$text .= "<div class='alert alert-danger' style='margin:0'>".$_login ->ErrorText."</div>";
			}

			$text .= "</td></tr></table>";

		//	$text = "<div class='alert'>".$text."</div>";
		}

		$text .= $frm -> form_open('post', e_SELF, 'testform');
		$text .= "<table class='table adminlist'>
		<colgroup>
		<col class='col-label' />
		<col class='col-control' />
		</colgroup>
		<tr><th colspan='2'>".LAN_ALT_42."</th></tr>";

		$text .= "<tr><td $log>".LAN_ALT_33."</td><td $log>";
	//	$text .= $frm->form_text('nametovalidate', 35, '', 120);
		$text .= e107::getForm()->text('nametovalidate','',35);
		$text .= "</td></tr>";

		$text .= "<tr><td $log>".LAN_ALT_34."</td><td $log>";
		$text .= $frm->form_password('passtovalidate', 35, '', 120);
		$text .= "</td></tr>";



		$text .= "</table>";

			$text .= "<div class='buttons-bar center'>";
	//	$text .= $frm->form_button("submit", 'testauth', LAN_ALT_47);
		$text .= e107::getForm()->admin_button('testauth', LAN_ALT_47,'other');
		$text .= "</div>";

		$text .= $frm->form_close();

		return e107::getMessage()->render().$text;
	}



	//-----------------------------------------------
	//			VALUE COPY METHOD SELECTION
	//-----------------------------------------------

	private $procListOpts = array(
					'none' => LAN_ALT_70,
					'bool1' => LAN_ALT_71,
					'ucase' => LAN_ALT_72,
					'lcase' => LAN_ALT_73,
					'ucfirst' => LAN_ALT_74,
					'ucwords' => LAN_ALT_75
					);

	/**
	 *	Return a 'select' box for available processing methods
	 */
	public function alt_auth_processing($selName, $allowed='*', $curVal='')
	{
		if (($allowed == 'none') || ($allowed == '')) return '';
		if ($allowed == '*')
		{
			$valid = $this->procListOpts;		// We just want all the array keys to exist!
		}
		else
		{
			$valid = array_flip(explode(',', $allowed));
			$valid['none'] = '1';		// Make sure this key exists - value doesn't matter
		}
		$ret = "<select class='tbox' name='{$selName}' id='{$selName}'>\n";
		foreach ($this->procListOpts as $k => $v)
		{
			if (isset($valid[$k]))
			{
				$s = ($curVal == $k) ? " selected='selected'" : '';
				$ret .= "<option value='{$k}'{$s}>{$v}</option>\n";
			}
		}
		$ret .= "</select>\n";
	//	$ret .= $selName.':'.$curVal;
		return $ret;
	}

}


function alt_auth_adminmenu()
{
	echo ' ';
	if(!is_array($authlist))
	{
		$authlist = alt_auth_admin::alt_auth_get_authlist();
	}
	define('ALT_AUTH_ACTION', 'main');

	$var['main']['text'] = LAN_ALT_31;
	$var['main']['link'] = e_PLUGIN.'alt_auth/alt_auth_conf.php';


	$icon  = e107::getParser()->toIcon(e_PLUGIN.'alt_auth/images/alt_auth_32.png');
	$caption = $icon."<span>alt auth</span>";

	show_admin_menu($caption, ALT_AUTH_ACTION, $var);


	$var = array();
	foreach($authlist as $a)
	{
	  if($a != 'e107')
	  {
		$var[$a]['text'] = LAN_ALT_30.$a;
		$var[$a]['link'] = e_PLUGIN."alt_auth/{$a}_conf.php";
	  }
	}



	show_admin_menu(LAN_ALT_29, ALT_AUTH_ACTION, $var);
}

