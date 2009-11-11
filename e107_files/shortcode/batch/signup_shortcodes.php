<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_files/shortcode/batch/signup_shortcodes.php,v $
|     $Revision: 1.16 $
|     $Date: 2009-11-11 19:53:44 $
|     $Author: e107steved $
|
| Mods to show extended field categories
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$signup_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);

/*
SC_BEGIN SIGNUP_COPPA_FORM
if (strpos(LAN_109, "stage") !== FALSE)
{
	return "";
}
else
{
	return "
<form method='post' action='".e_SELF."?stage1' >\n
<div><br />
<input type='radio' name='coppa' value='0' checked='checked' /> ".LAN_200."
<input type='radio' name='coppa' value='1' /> ".LAN_201."<br />
<br />
<input class='button' type='submit' name='newver' value=\"".LAN_399."\" />
</div></form>
";
}
SC_END

SC_BEGIN SIGNUP_FORM_OPEN
global $rs;
return $rs->form_open("post", e_SELF, "signupform");
SC_END

SC_BEGIN SIGNUP_SIGNUP_TEXT
global $pref, $tp, $SIGNUP_TEXT;

if($pref['signup_text'])
{
	return $tp->toHTML($pref['signup_text'], TRUE, 'parse_sc,defs');
}
elseif($pref['user_reg_veri'])
{
	return $SIGNUP_TEXT;
}
SC_END


SC_BEGIN SIGNUP_XUP
global $pref, $tp, $SIGNUP_XUP_FORM, $signup_shortcodes;
if(isset($pref['xup_enabled']) && $pref['xup_enabled'])
{
	return $tp->parseTemplate($SIGNUP_XUP_FORM, TRUE, $signup_shortcodes);
}
SC_END

SC_BEGIN SIGNUP_XUP_ACTION
global $pref, $tp, $SIGNUP_XUP_BUTTON, $signup_shortcodes;
if(isset($pref['xup_enabled']) && $pref['xup_enabled'])
{
// Puts the button to allow XUP signup onto the 'normal' signup screen
	return $tp->parseTemplate($SIGNUP_XUP_BUTTON, TRUE, $signup_shortcodes);
}
SC_END


SC_BEGIN SIGNUP_DISPLAYNAME
global $pref, $rs, $signupValue;
if (check_class($pref['displayname_class']))
{
  $dis_name_len = varset($pref['displayname_maxlength'],15);
  $name = ($_POST['name'] ? $_POST['name'] : varset($signupValue['name']));
  return $rs->form_text("name", $dis_name_len+5, $name, $dis_name_len);
}
SC_END


SC_BEGIN SIGNUP_LOGINNAME
global $rs, $signupValue;
$log_name_length = varset($pref['loginname_maxlength'],30);
$loginname = ($_POST['loginname'] ? $_POST['loginname'] : varset($signupValue['loginname']));
return $rs->form_text("loginname", $log_name_length+5, $loginname, $log_name_length);
SC_END

SC_BEGIN SIGNUP_REALNAME
global $rs, $pref, $signupValue;
if ($pref['signup_option_realname'])
{
	$realname = ($_POST['realname'] ? $_POST['realname'] : varset($signupValue['realname']));
	return $rs->form_text("realname", 30,  $realname, 100);
}
SC_END

SC_BEGIN SIGNUP_PASSWORD1
global $rs;
return $rs->form_password("password1", 30, $password1, 20);
SC_END

SC_BEGIN SIGNUP_PASSWORD2
global $rs;
return $rs->form_password("password2", 30, $password2, 20);
SC_END

SC_BEGIN SIGNUP_PASSWORD_LEN
global $pref, $SIGNUP_PASSWORD_LEN;
if($pref['signup_pass_len'])
{
	return $SIGNUP_PASSWORD_LEN;
}
SC_END

SC_BEGIN SIGNUP_EMAIL
global $rs, $signupValue;
$email = ($_POST['email'] ? $_POST['email'] : varset($signupValue['email']));
return $rs->form_text("email", 30, $email, 100);
SC_END

SC_BEGIN SIGNUP_EMAIL_CONFIRM
global $rs, $signupValue;
$email_confirm = ($_POST['email_confirm'] ? $_POST['email_confirm'] : varset($signupValue['email_confirm']));
return $rs->form_text("email_confirm", 30, $email_confirm, 100);
SC_END


SC_BEGIN SIGNUP_HIDE_EMAIL
global $rs;
$default_email_setting = 1;   // Gives option of turning into a pref later if wanted
return $rs->form_radio("hideemail", 1, $default_email_setting==1)." ".LAN_SIGNUP_10."&nbsp;&nbsp;".$rs->form_radio("hideemail",  0,$default_email_setting==0)." ".LAN_200;
SC_END


SC_BEGIN SIGNUP_USERCLASS_SUBSCRIBE
global $tp, $pref, $sql, $USERCLASS_SUBSCRIBE_START, $USERCLASS_SUBSCRIBE_ROW, $USERCLASS_SUBSCRIBE_END;
$ret = "";
$search = array('{USERCLASS_ID}', '{USERCLASS_NAME}', '{USERCLASS_DESCRIPTION}');
if($pref['signup_option_class'] && ($sql->db_Select("userclass_classes", "*", "userclass_editclass = 0 order by userclass_name")))
{
	$ret = $USERCLASS_SUBSCRIBE_START;
	while($row = $sql->db_Fetch())
	{
		$row['userclass_description'] = $tp->toHTML($row['userclass_description'], "", "defs");
		$row['userclass_name'] = $tp->toHTML($row['userclass_name'], "", "defs");
		$replace = array($row['userclass_id'], $row['userclass_name'], $row['userclass_description']);
		$ret .= str_replace($search, $replace, $USERCLASS_SUBSCRIBE_ROW);
	}
	$ret .= $USERCLASS_SUBSCRIBE_END;
	return $ret;
}
SC_END


SC_BEGIN SIGNUP_EXTENDED_USER_FIELDS
global $usere, $tp, $SIGNUP_EXTENDED_USER_FIELDS, $EXTENDED_USER_FIELD_REQUIRED, $SIGNUP_EXTENDED_CAT;
$text = "";

$search = array(
'{EXTENDED_USER_FIELD_TEXT}',
'{EXTENDED_USER_FIELD_REQUIRED}',
'{EXTENDED_USER_FIELD_EDIT}'
);


// What we need is a list of fields, ordered first by parent, and then by display order?
// category entries are `user_extended_struct_type` = 0
// 'unallocated' entries are `user_extended_struct_parent` = 0

// Get a list of defined categories
$catList = $usere->user_extended_get_categories(FALSE);
// Add in category zero - the 'no category' category
array_unshift($catList,array('user_extended_struct_parent' => 0, 'user_extended_struct_id' => '0'));



foreach($catList as $cat)
{
  $extList = $usere->user_extended_get_fieldList($cat['user_extended_struct_id']);

  $done_heading = FALSE;
  
  foreach($extList as $ext)
  {
  	if($ext['user_extended_struct_required'] == 1 || $ext['user_extended_struct_required'] == 2)
   	{
      if(!$done_heading  && ($cat['user_extended_struct_id'] > 0))
      {	// Add in a heading
		$text .= str_replace('{EXTENDED_CAT_TEXT}', $tp->toHTML($cat['user_extended_struct_name'], '', 'emotes_off defs'), $SIGNUP_EXTENDED_CAT);
		$done_heading = TRUE;
	  }
  	  $replace = array(
    			$tp->toHTML($ext['user_extended_struct_text'], '', 'emotes_off defs'),
    			($ext['user_extended_struct_required'] == 1 ? $EXTENDED_USER_FIELD_REQUIRED : ''),
    			$usere->user_extended_edit($ext, $_POST['ue']['user_'.$ext['user_extended_struct_name']])
        );
      $text .= str_replace($search, $replace, $SIGNUP_EXTENDED_USER_FIELDS);
    }
  }
}
return $text;
SC_END

SC_BEGIN SIGNUP_SIGNATURE
global $pref, $SIGNUP_SIGNATURE_START, $SIGNUP_SIGNATURE_END, $signupValue;
if($pref['signup_option_signature'])
{
	require_once(e_HANDLER."ren_help.php");
	$SIGNUP_SIGNATURE_START = str_replace("{REN_HELP}", display_help('helpb', 2), $SIGNUP_SIGNATURE_START);
	$SIGNUP_SIGNATURE_END = str_replace("{REN_HELP}", display_help('helpb', 2), $SIGNUP_SIGNATURE_END);
	$sig = ($_POST['signature'] ? $_POST['signature'] : varset($signupValue['signature']));
	return $SIGNUP_SIGNATURE_START.$sig.$SIGNUP_SIGNATURE_END;
}
SC_END

SC_BEGIN SIGNUP_IMAGES
global $pref;
if($pref['signup_option_image'])
{

	$text = "
	<input class='tbox' style='width:80%' id='avatar' type='text' name='image' size='40' value='$image' maxlength='100' />

	<input class='button' type ='button' style='cursor:pointer' size='30' value='".LAN_SIGNUP_27."' onclick='expandit(this)' />
	<div style='display:none' >";
	$avatarlist[0] = "";
	$handle = opendir(e_IMAGE."avatars/");
	while ($file = readdir($handle))
	{
		if ($file != "." && $file != ".." && $file != "CVS" && $file != "index.html")
		{
			$avatarlist[] = $file;
		}
	}
	closedir($handle);

	for($c = 1; $c <= (count($avatarlist)-1); $c++)
	{
		$text .= "<a href='javascript:insertext(\"$avatarlist[$c]\", \"avatar\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
	}

	$text .= "<br />
	</div><br />";

    // Intentionally disable uploadable avatar and photos at this stage
	if (false && $pref['avatar_upload'] && FILE_UPLOADS)
	{
		$text .= "<br /><span class='smalltext'>".LAN_SIGNUP_25."</span> <input class='tbox' name='file_userfile[]' type='file' size='40' />
		<br /><div class='smalltext'>".LAN_SIGNUP_34."</div>";
	}

	if (false && $pref['photo_upload'] && FILE_UPLOADS)
	{
		$text .= "<br /><span class='smalltext'>".LAN_SIGNUP_26."</span> <input class='tbox' name='file_userfile[]' type='file' size='40' />
		<br /><div class='smalltext'>".LAN_SIGNUP_34."</div>";
	}  
	return $text;
}
SC_END

SC_BEGIN SIGNUP_TIMEZONE
global $pref;
if ($pref['signup_option_timezone'])
{
	$timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+5.30", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
	$timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Mumbai, Delhi, Calcutta", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
	$text .= "
	<select style='width:99%' name='timezone' class='tbox'>\n";

	if (!isset($user_timezone) || (($user_timezone |= 'GMT') && (($user_timezone < -12) || ($user_timezone > 13))))
	{
		$user_timezone = 'GMT';		// Default to something a bit more helpful
	}
	$count = 0;
	while ($timezone[$count])
	{
		if ($timezone[$count] == $user_timezone)
		{
			$text .= "<option value='".$timezone[$count]."' selected>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
		}
		else
		{
			$text .= "<option value='".$timezone[$count]."'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
		}
		$count++;
	}
	$text .= "</select>";
	return $text;
}
return "";
SC_END


SC_BEGIN SIGNUP_IMAGECODE
global $signup_imagecode, $rs, $sec_img;
if($signup_imagecode)
{
	return $rs->form_hidden("rand_num", $sec_img->random_number). $sec_img->r_image()."<br />".$rs->form_text("code_verify", 20, "", 20);
}
SC_END

SC_BEGIN SIGNUP_FORM_CLOSE
return "</form>";
SC_END

SC_BEGIN SIGNUP_XUP_LOGINNAME
global $rs, $loginname;
return $rs->form_text("loginnamexup", 30, $loginname, 30);
SC_END

SC_BEGIN SIGNUP_XUP_PASSWORD1
global $rs, $password1;
return $rs->form_password("password1xup", 30, $password1, 20);
SC_END

SC_BEGIN SIGNUP_XUP_PASSWORD2
global $rs, $password1;
return $rs->form_password("password2xup", 30, $password2, 20);
SC_END

SC_BEGIN SIGNUP_IS_MANDATORY
global $pref;
if (isset($parm))
{
  switch ($parm)
  {
    case 'email' : if (varset($pref['disable_emailcheck'],FALSE)) return '';
  }
}
return " *";
SC_END

*/

?>
