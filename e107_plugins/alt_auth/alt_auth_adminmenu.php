<?php

if (!is_object($euf))
{
	require_once(e_HANDLER.'user_extended_class.php');
	$euf = new e107_user_extended;
}


  define('AUTH_SUCCESS', -1);
  define('AUTH_NOUSER', 1);
  define('AUTH_BADPASSWORD', 2);
  define('AUTH_NOCONNECT', 3);
  define('AUTH_UNKNOWN', 4);
  define('AUTH_NOT_AVAILABLE', 5);

function alt_auth_get_authlist()
{
	$authlist = array("e107");
	$handle=opendir(e_PLUGIN."alt_auth");
	while ($file = readdir($handle))
	{
		if(preg_match("/^(.*)_auth\.php/",$file,$match))
		{
			$authlist[] = $match[1];
		}
	}
	closedir($handle);
	return $authlist;
}



// All user fields which might, just possibly, be transferred. The option name must be the corresponding field in the E107 user database, prefixed with 'xf_'
$alt_auth_user_fields = array(
  'user_email' 		=> array('prompt' => LAN_ALT_12, 'optname' => 'xf_user_email', 'default' => 'user_email', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => 'mail'),
  'user_hideemail' 	=> array('prompt' => LAN_ALT_13, 'optname' => 'xf_user_hideemail', 'default' => 'user_hideemail', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => ''),
  'user_name' 		=> array('prompt' => LAN_ALT_14, 'optname' => 'xf_user_name', 'default' => 'user_name', 'optional' => TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => ''),
  'user_login'		=> array('prompt' => LAN_ALT_15, 'optname' => 'xf_user_login', 'default' => 'user_login', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => 'sn'),
  'user_customtitle'=> array('prompt' => LAN_ALT_16, 'optname' => 'xf_user_customtitle', 'default' => 'user_customtitle', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
  'user_signature' 	=> array('prompt' => LAN_ALT_17, 'optname' => 'xf_user_signature', 'default' => 'user_signature', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
  'user_image' 		=> array('prompt' => LAN_ALT_18, 'optname' => 'xf_user_image', 'default' => 'user_image', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
  'user_sess' 		=> array('prompt' => LAN_ALT_19, 'optname' => 'xf_user_sess', 'default' => 'user_sess', 'optional' =>  TRUE, 'otherdb' =>  TRUE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
  'user_join' 		=> array('prompt' => LAN_ALT_20, 'optname' => 'xf_user_join', 'default' => 'user_join', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => TRUE, 'ldap_field' => ''),
  'user_ban'		=> array('prompt' => LAN_ALT_21, 'optname' => 'xf_user_ban', 'default' => 'user_ban', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
  'user_class'		=> array('prompt' => LAN_ALT_22, 'optname' => 'xf_user_class', 'default' => 'user_class', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE),
  'user_xup' 		=> array('prompt' => LAN_ALT_23, 'optname' => 'xf_user_xup', 'default' => 'user_xup', 'optional' =>  TRUE, 'otherdb' => FALSE, 'e107db' => TRUE, 'importdb' => FALSE, 'ldap' => FALSE)
);


// Returns a block of table rows with user DB fields and either checkboxes or entry boxes
// $tableType is the prefix used, without the following underscore
function alt_auth_get_field_list($tableType, $frm, $parm, $asCheckboxes = FALSE)
{
  global $alt_auth_user_fields;
  $ret = '';
  foreach ($alt_auth_user_fields as $f => $v)
  {
    if (varsettrue($v['showAll']) || varsettrue($v[$tableType]))
	{
	  $ret .= "<tr><td class='forumheader3'>";
	  if ($v['optional'] == FALSE) $ret .= '*&nbsp;';
	  $ret .= $v['prompt'].':';
	  if (isset($v['help']))
	  {
		$ret .= "<br /><span class='smalltext'>".$v['help']."</span>";
	  }
	  $ret .= "</td><td class='forumheader3'>";
	  $fieldname = $tableType.'_'.$v['optname'];
	  $value = varset($v['default'],'');
	  if (isset($v[$tableType.'_field'])) $value = $v[$tableType.'_field'];
	  if (isset($parm[$fieldname])) $value = $parm[$fieldname];
//	  echo "Field: {$fieldname} => {$value}<br />";
	  if ($asCheckboxes)
	  {
		$ret .= $frm -> form_checkbox($fieldname, 1, $value);
	  }
	  else
	  {
		$ret .= $frm -> form_text($fieldname, 35, $value, 120);
	  }
	  $ret .= "</td></tr>\n";
	}
  }
  return $ret;
}


// Returns a list of all the user-related fields allowed as an array, whhere the key is the field name
function alt_auth_get_allowed_fields($tableType)
{
  global $alt_auth_user_fields;
  $ret = array();
  foreach ($alt_auth_user_fields as $f => $v)
  {
    if (varsettrue($v['showAll']) || varsettrue($v[$tableType]))
	{
	  $fieldname = $tableType.'_'.$v['optname'];
	  $ret[$fieldname] = '1';
	}
  }
  return $ret;
}


function add_extended_fields()
{
	global $alt_auth_user_fields, $euf, $pref;
	if (!isset($pref['auth_extended'])) return;
	if (!$pref['auth_extended']) return;
	static $fieldsAdded = FALSE;
	if ($fieldsAdded) return;
	$xFields = $euf->user_extended_get_fieldList('','user_extended_struct_name');
//	print_a($xFields);
	$fields = explode(',',$pref['auth_extended']);
	foreach ($fields as $f)
	{
		if (isset($xFields[$f]))
		{
			$alt_auth_user_fields['x_'.$f] = array('prompt' => varset($xFields[$f]['user_extended_struct_text'],'').' ('.$f.')', 
													'optname' => 'xf_x_'.$f, 
													'default' => varset($xFields[$f]['default'],''),
													'optional' => TRUE,
													'showAll' => TRUE );
		}
	}
	$fieldsAdded = TRUE;
}


$common_fields = array(
  'server' => array('fieldname' => 'server',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_32, 'help' => ''),
  'uname'  => array('fieldname' => 'username',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_33, 'help' => ''),
  'pwd'    => array('fieldname' => 'password',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_34, 'help' => ''),
  'db'     => array('fieldname' => 'database',	'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_35, 'help' => ''),
  'table'  => array('fieldname' => 'table',		'size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_36, 'help' => ''),
  'prefix' => array('fieldname' => 'prefix',	'size' => 35, 'max_size' =>  35, 'prompt' => LAN_ALT_39, 'help' => ''),
  'ufield' => array('fieldname' => 'user_field','size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_37, 'help' => ''),
  'pwfield'=> array('fieldname' => 'password_field','size' => 35, 'max_size' => 120, 'prompt' => LAN_ALT_38, 'help' => ''),
  'salt'   => array('fieldname' => 'password_salt','size' => 35, 'max_size' => 120,  'prompt' => LAN_ALT_24, 'help' => LAN_ALT_25)
);

function alt_auth_get_db_fields($prefix, $frm, $parm, $fields = 'server|uname|pwd|db|table|ufield|pwfield')
{
  global $common_fields;
  $opts = explode('|',$fields);
  $ret = '';
  foreach ($common_fields as $fn => $cf)
  {
    if (in_array($fn,$opts))
	{
	  $ret .= "<tr><td class='forumheader3'>".$cf['prompt'];
	  if ($cf['help']) $ret .= "<br /><span class='smalltext'>".$cf['help']."</span>";
	  $ret .= "</td><td class='forumheader3'>";
	  $ret .= $frm -> form_text($prefix.'_'.$cf['fieldname'], $cf['size'], $parm[$prefix.'_'.$cf['fieldname']], $cf['max_size']);
	  $ret .= "</td></tr>\n";
	}
  }
  return $ret;
}



// Write all the options to the DB. $prefix must NOT have trailing underscore
function alt_auth_post_options($prefix)
{
  global $common_fields, $sql, $admin_log;
  $lprefix = $prefix.'_';

  $user_fields = alt_auth_get_allowed_fields($prefix);		// Need this list in case checkboxes for parameters
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
	  if($sql -> db_Select("alt_auth", "*", "auth_type='{$prefix}' AND auth_parmname='{$k}' "))
	  {
		$sql -> db_Update("alt_auth", "auth_parmval='{$v}' WHERE  auth_type='{$prefix}' AND auth_parmname='{$k}' ");
	  }
	  else
	  {
		$sql -> db_Insert("alt_auth", "'{$prefix}','{$k}','{$v}' ");
	  }
    }
  }
  $admin_log->log_event('AUTH_03',$prefix,E_LOG_INFORMATIVE,'');
  return LAN_ALT_UPDATED;
}




// Return test form
function alt_auth_test_form($prefix,$frm)
{
  $text = $frm -> form_open("post", e_SELF, 'testform');
  $text .= "<table style='width:96%' class='fborder'>
  <tr><td colspan='2' class='forumheader2' style='text-align:center;'>".LAN_ALT_42."</td></tr>";

  if (isset($_POST['testauth']))
  {
    // Try and connect to DB/server, and maybe validate user name
	require_once(e_PLUGIN.'alt_auth/'.$prefix.'_auth.php');
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
	  $log_result = $_login -> login($val_name, $_POST['passtovalidate'], $pass_vars, ($val_name == ''));
	}

	$text .= "<tr><td class='forumheader3'>".LAN_ALT_48;
	if ($val_name)
	{
	  $text .= "<br />".LAN_ALT_49.$val_name.'<br />'.LAN_ALT_50;
	  if (varset($_POST['passtovalidate'],'')) $text .= str_repeat('*',strlen($_POST['passtovalidate'])); else $text .= LAN_ALT_51;
	}
	$text .= "</td><td class='forumheader3'>";
	switch ($log_result)
	{
	  case AUTH_SUCCESS :
	    $text .= LAN_ALT_58;
		if (count($pass_vars))
		{
		  $text .= '<br />'.LAN_ALT_59;
		  foreach ($pass_vars as $k => $v)
		  {
			$text .= '<br />&nbsp;&nbsp;'.$k.'=>'.$v;
		  }
		}
		break;
	  case AUTH_NOUSER :
	    $text .= LAN_ALT_52.LAN_ALT_55;
		break;
	  case AUTH_BADPASSWORD :
	    $text .= LAN_ALT_52.LAN_ALT_56;
	    break;
	  case AUTH_NOCONNECT :
	    $text .= LAN_ALT_52.LAN_ALT_54;
	    break;
	  case AUTH_UNKNOWN :
	    $text .= LAN_ALT_52.LAN_ALT_53;
		break;
	  case AUTH_NOT_AVAILABLE :
	    $text .= LAN_ALT_52.LAN_ALT_57;
		break;
	  default :
	    $text .= "Coding error";
	}
	if (isset($_login ->ErrorText)) $text .= '<br />'.$_login ->ErrorText;
	$text .= "</td></tr>";
  }

  $text .= "<tr><td class='forumheader3'>".LAN_ALT_33."</td><td class='forumheader3'>";
  $text .= $frm -> form_text('nametovalidate', 35, '', 120);
  $text .= "</td></tr>";

  $text .= "<tr><td class='forumheader3'>".LAN_ALT_34."</td><td class='forumheader3'>";
  $text .= $frm -> form_password('passtovalidate', 35, '', 120);
  $text .= "</td></tr>";

  $text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>";
  $text .= $frm -> form_button("submit", 'testauth', LAN_ALT_47);
  $text .= "</td></tr>";

  $text .= "</table>";
  $text .= $frm -> form_close();
  return $text;
}



function alt_auth_adminmenu()
{
	global $authlist;
	echo " ";
	if(!is_array($authlist))
	{
		$authlist = alt_auth_get_authlist();
	}
	define("ALT_AUTH_ACTION", "main");

	$var['main']['text'] = LAN_ALT_31;
	$var['main']['link'] = e_PLUGIN."alt_auth/alt_auth_conf.php";
	show_admin_menu("alt auth", ALT_AUTH_ACTION, $var);
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
?>
