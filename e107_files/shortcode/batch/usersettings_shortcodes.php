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
|     $Source: /cvs_backup/e107_0.7/e107_files/shortcode/batch/usersettings_shortcodes.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$usersettings_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*
SC_BEGIN USERNAME
global $rs, $curVal, $pref;
// This is the 'display name'
if (check_class($pref['displayname_class']))
{
  $dis_name_len = varset($pref['displayname_maxlength'],15);
  return $rs->form_text("username", $dis_name_len, $curVal['user_name'], $dis_name_len, "tbox");
}
else
{
	return $curVal['user_name'];
}
SC_END

SC_BEGIN LOGINNAME
global $rs, $curVal;
if (ADMIN && getperms("4"))
{
  $log_name_length = varset($pref['loginname_maxlength'],30);
  return $rs->form_text("loginname", $log_name_length, $curVal['user_loginname'], $log_name_length, "tbox");
}
else
{
	return $curVal['user_loginname'];
}
SC_END

SC_BEGIN CUSTOMTITLE
global $rs, $curVal, $pref;
if ($pref['forum_user_customtitle'] || ADMIN)
{
	return $rs->form_text("customtitle", 40, $curVal['user_customtitle'], 100, "tbox");
}
SC_END

SC_BEGIN REALNAME
global $rs, $curVal;
return $rs->form_text("realname", 20, $curVal['user_login'], 100, "tbox");
SC_END

SC_BEGIN PASSWORD1
global $rs, $curVal, $pref;
if(!isset($pref['auth_method']) || $pref['auth_method'] == '' || $pref['auth_method'] == 'e107' || $pref['auth_method'] == '>e107')
{
	return $rs->form_password("password1", 40, "", 20);
}

return "";
SC_END

SC_BEGIN PASSWORD2
global $rs, $curVal, $pref;
if(!isset($pref['auth_method']) || $pref['auth_method'] == '' || $pref['auth_method'] == 'e107' || $pref['auth_method'] == '>e107')
{
	return $rs->form_password("password2", 40, "", 20);
}

return "";
SC_END

SC_BEGIN PASSWORD_LEN
global $pref;
if(!isset($pref['auth_method']) || ($pref['auth_method'] != 'e107' && $pref['auth_method'] != '>e107'))
{
	return "";
}
return $pref['signup_pass_len'];
SC_END

SC_BEGIN EMAIL
global $rs, $curVal;
return $rs->form_text("email", 40, $curVal['user_email'], 100);
SC_END

SC_BEGIN HIDEEMAIL
global $rs, $curVal;
if($parm == 'radio')
{
	return ($curVal['user_hideemail'] ? $rs->form_radio("hideemail", 1, 1)." ".LAN_416."&nbsp;&nbsp;".$rs->form_radio("hideemail", 0)." ".LAN_417 : $rs->form_radio("hideemail", 1)." ".LAN_416."&nbsp;&nbsp;".$rs->form_radio("hideemail", 0, 1)." ".LAN_417);
}
SC_END

SC_BEGIN USERCLASSES
global $sql, $pref, $tp, $curVal;
$ret = "";
if(ADMIN && $curVal['user_id'] != USERID)
{
	return "";
}
if($sql->db_Select("userclass_classes", "*", "userclass_editclass IN(".$tp -> toDB($curVal['userclass_list'], true).") ORDER BY userclass_name"))
{
	$ucList = $sql->db_getList();
	$ret = "<table style='width:95%'>";
	foreach($ucList as $row)
	{
		$inclass = check_class($row['userclass_id'], $curVal['user_class']) ? TRUE : FALSE;
		if(isset($_POST['class']))
		{
			$inclass = in_array($row['userclass_id'], $_POST['class']);
		}
		$frm_checked = $inclass ? "checked='checked'" : "";
		$ret .= "<tr><td class='defaulttext'>";
		$ret .= "<input type='checkbox' name='class[]' value='{$row['userclass_id']}' $frm_checked />\n";
		$ret .= $tp->toHTML($row['userclass_name'], "", "defs")."</td>";
		$ret .= "<td class='smalltext'>".$tp->toHTML($row['userclass_description'], "", "defs")."</td>";
		$ret .= "</tr>\n";
	}
	$ret .= "</table>\n";
}
return $ret;
SC_END

SC_BEGIN SIGNATURE
global $curVal;
parse_str($parm);
$cols = (isset($cols) ? $cols : 58);
$rows = (isset($rows) ? $rows : 4);
return "<textarea class='tbox signature' name='signature' cols='{$cols}' rows='{$rows}' onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'>".$curVal['user_signature']."</textarea>";
SC_END

SC_BEGIN SIGNATURE_HELP
return display_help("", 2);
SC_END


SC_BEGIN TIMEZONE
global $curVal;
$ret = "<select name='timezone' class='tbox'>\n";
$timezone = array("-12", "-11", "-10", "-9", "-8", "-7", "-6", "-5", "-4", "-3", "-2", "-1", "GMT", "+1", "+2", "+3", "+4", "+5", "+6", "+7", "+8", "+9", "+10", "+11", "+12", "+13");
$timearea = array("International DateLine West", "Samoa", "Hawaii", "Alaska", "Pacific Time (US and Canada)", "Mountain Time (US and Canada)", "Central Time (US and Canada), Central America", "Eastern Time (US and Canada)", "Atlantic Time (Canada)", "Greenland, Brasilia, Buenos Aires, Georgetown", "Mid-Atlantic", "Azores", "GMT - UK, Ireland, Lisbon", "West Central Africa, Western Europe", "Greece, Egypt, parts of Africa", "Russia, Baghdad, Kuwait, Nairobi", "Abu Dhabi, Kabul", "Islamabad, Karachi", "Astana, Dhaka", "Bangkok, Rangoon", "Hong Kong, Singapore, Perth, Beijing", "Tokyo, Seoul", "Brisbane, Canberra, Sydney, Melbourne", "Soloman Islands", "New Zealand", "Nuku'alofa");
$count = 0;
while ($timezone[$count])
{
	if ($timezone[$count] == $curVal['user_timezone'])
	{
		$ret .= "<option value='".$timezone[$count]."' selected='selected'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
	}
	else
	{
		$ret .= "<option value='".$timezone[$count]."'>(GMT".$timezone[$count].") ".$timearea[$count]."</option>\n";
	}
	$count++;
}

$ret .= "</select>";
return $ret;
SC_END

SC_BEGIN AVATAR_UPLOAD
global $pref;
if ($pref['avatar_upload'] && FILE_UPLOADS)
{
		return "<input class='tbox' name='file_userfile[avatar]' type='file' size='47' />";
}
SC_END

SC_BEGIN AVATAR_REMOTE
global $curVal;
return "<input class='tbox' type='text' name='image' size='60' value='".$curVal['user_image']."' maxlength='100' />";
SC_END

SC_BEGIN AVATAR_CHOOSE
$ret = "
<input class='button' type ='button' style=' cursor:pointer' size='30' value='".LAN_403."' onclick='expandit(this)' />
<div style='display:none' >";
$avatarlist[0] = "";
$handle = opendir(e_IMAGE."avatars/");
while ($file = readdir($handle))
{
	if ($file != "." && $file != ".." && $file != "index.html" && $file != "CVS")
	{
		$avatarlist[] = $file;
	}
}
closedir($handle);

for($c = 1; $c <= (count($avatarlist)-1); $c++)
{
	$ret .= "<a href='javascript:addtext_us(\"$avatarlist[$c]\")'><img src='".e_IMAGE."avatars/".$avatarlist[$c]."' style='border:0' alt='' /></a> ";
}

$ret .= "
<br />
</div>
";
return $ret;
SC_END

SC_BEGIN PHOTO_UPLOAD
global $pref;
if ($pref['photo_upload'] && FILE_UPLOADS)
{
	return "<input type='checkbox' name='user_delete_photo' value='1' />".LAN_USET_16."<br />\n
	        <input class='tbox' name='file_userfile[photo]' type='file' size='47' />";
}
SC_END

SC_BEGIN XUP
global $pref,$curVal;
if(isset($pref['xup_enabled']) && $pref['xup_enabled'] == 1)
{
	return 	"<input class='tbox' type='text' name='user_xup' size='50' value='{$curVal['user_xup']}' maxlength='100' />";
}
SC_END


SC_BEGIN USEREXTENDED_ALL
global $sql, $tp, $curVal, $usersettings_shortcodes;
$qry = "
SELECT * FROM #user_extended_struct
WHERE user_extended_struct_applicable IN (".$tp -> toDB($curVal['userclass_list'], true).")
AND user_extended_struct_write IN (".USERCLASS_LIST.")
AND user_extended_struct_type = 0
ORDER BY user_extended_struct_order ASC
";
$ret="";
if($sql->db_Select_gen($qry))
{
	$catList = $sql->db_getList();
}
$catList[] = array("user_extended_struct_id" => 0, "user_extended_struct_name" => LAN_USET_7);
foreach($catList as $cat)
{
	cachevars("extendedcat_{$cat['user_extended_struct_id']}", $cat);
  	$ret .= $tp->parseTemplate("{USEREXTENDED_CAT={$cat['user_extended_struct_id']}}", TRUE, $usersettings_shortcodes);
}
 return $ret;
SC_END

SC_BEGIN USEREXTENDED_CAT
global $sql, $tp, $curVal, $usersettings_shortcodes, $USER_EXTENDED_CAT, $extended_showed;
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
	WHERE user_extended_struct_applicable IN (".$tp -> toDB($curVal['userclass_list'], true).")
	AND user_extended_struct_write IN (".USERCLASS_LIST.")
	AND user_extended_struct_id = ".intval($parm)."
	";
	if($sql->db_Select_gen($qry))
	{
		$catInfo = $sql->db_Fetch();
	}
}

if($catInfo)
{
	$qry = "
	SELECT * FROM #user_extended_struct
	WHERE user_extended_struct_applicable IN (".$tp -> toDB($curVal['userclass_list'], true).")
	AND user_extended_struct_write IN (".USERCLASS_LIST.")
	AND user_extended_struct_parent = ".intval($parm)."
	AND user_extended_struct_type != 0
	ORDER BY user_extended_struct_order ASC
	";
	if($sql->db_Select_gen($qry))
	{
		$fieldList = $sql->db_getList();
		foreach($fieldList as $field)
		{
			cachevars("extendedfield_{$cat['user_extended_struct_name']}", $field);
			$ret .= $tp->parseTemplate("{USEREXTENDED_FIELD={$field['user_extended_struct_name']}}", TRUE, $usersettings_shortcodes);
		}
	}
}

if($ret)
{
	$ret = str_replace("{CATNAME}", $catInfo['user_extended_struct_name'], $USER_EXTENDED_CAT).$ret;
}

$extended_showed['cat'][$parm] = 1;
return $ret;
SC_END

SC_BEGIN USEREXTENDED_FIELD
global $sql, $tp, $curVal, $usersettings_shortcodes, $extended_showed, $ue, $USEREXTENDED_FIELD, $REQUIRED_FIELD;
if(isset($extended_showed['field'][$parm]))
{
	return "";
}
$ret = "";

$fInfo = getcachedvars("extendeddata_{$parm}");
if(!$fInfo)
{
	$qry = "
	SELECT * FROM #user_extended_struct
	WHERE user_extended_struct_applicable IN (".$tp -> toDB($curVal['userclass_list'], true).")
	AND user_extended_struct_write IN (".USERCLASS_LIST.")
	AND user_extended_struct_name = '".$tp -> toDB($parm, true)."'
	";
	if($sql->db_Select_gen($qry))
	{
		$fInfo = $sql->db_Fetch();
	}
}

if($fInfo)
{
	$fname = $tp->toHTML($fInfo['user_extended_struct_text'], "", "emotes_off, defs");
	if($fInfo['user_extended_struct_required'] == 1)
	{
		$fname = str_replace("{FIELDNAME}", $fname, $REQUIRED_FIELD);
	}

	$parms = explode("^,^",$fInfo['user_extended_struct_parms']);

	$fhide="";
	if($parms[3])
	{
		$chk = (strpos($curVal['user_hidden_fields'], "^user_".$parm."^") === FALSE) ? FALSE : TRUE;
		if(isset($_POST['updatesettings']))
		{
			$chk = isset($_POST['hide']['user_'.$parm]);
		}
		$fhide = $ue->user_extended_hide($fInfo, $chk);
	}

	$uVal = str_replace(chr(1), "", $curVal['user_'.$parm]);
	$fval = $ue->user_extended_edit($fInfo, $uVal);

	$ret = $USEREXTENDED_FIELD;
	$ret = str_replace("{FIELDNAME}", $fname, $ret);
	$ret = str_replace("{FIELDVAL}", $fval, $ret);
	$ret = str_replace("{HIDEFIELD}", $fhide, $ret);
}

$extended_showed['field'][$parm] = 1;
return $ret;
SC_END

*/
?>
