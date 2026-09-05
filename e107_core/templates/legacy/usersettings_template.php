<?php

if (!defined('e107_INIT')) { exit; }

if (!defined("USER_WIDTH"))
{
	define("USER_WIDTH", "width:97%");
}

$required = "<span class='required'><!-- empty --></span>";

$USERSETTINGS_WRAPPER['edit']['CUSTOMTITLE'] = "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USER_04.":</td>
											<td style='width:60%' class='forumheader2'>\n{---}</td></tr>\n";

$USERSETTINGS_WRAPPER['edit']['PASSWORD1'] = "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USET_24."</td>
											<td style='width:60%' class='forumheader2'>\n{---}</td></tr>\n";

$USERSETTINGS_WRAPPER['edit']['PASSWORD2'] = "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USET_25."</td>
											<td style='width:60%' class='forumheader2'>\n{---}</td></tr>\n";

$USERSETTINGS_WRAPPER['edit']['PASSWORD_LEN'] = "<br /><span class='smalltext'>  (".LAN_USER_78." {---} ".LAN_USER_79.")</span>";

$USERSETTINGS_WRAPPER['edit']['USERCLASSES'] = "<tr>
											<td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USER_76.":".((int) e107::getPref('signup_option_class') === 2 ? $required : '')."
											<br /><span class='smalltext'>".LAN_USER_73."</span>
											</td>
											<td style='width:60%' class='forumheader2'>{---}</td></tr>\n";

$USERSETTINGS_WRAPPER['edit']['AVATAR_UPLOAD'] = "<tr>
											<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USET_26."<br /></td>
											<td style='width:60%' class='forumheader2'>\n{---}</td></tr>\n";

$USERSETTINGS_WRAPPER['edit']['PHOTO_UPLOAD'] = "<tr>
											<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USER_06."</td>
											<td style='width:60%' class='forumheader2'><span class='smalltext'>\n{---}</span></td></tr>";

$USERSETTINGS_WRAPPER['edit']['USERNAME'] = "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USER_01."</td>
											<td style='width:60%' class='forumheader2'>\n{---}</td</tr>";

$USERSETTINGS_WRAPPER['edit']['LOGINNAME'] = "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USER_81."</td>
											<td style='width:60%' class='forumheader2'>\n{---}</td></tr>\n";

$USERSETTINGS_WRAPPER['edit']['SIGNATURE'] = "<tr><td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USER_71.((int) e107::getPref('signup_option_signature') === 2 ? $required : '')."</td>
											<td style='width:60%' class='forumheader2'>{---}";

$USERSETTINGS_WRAPPER['edit']['SIGNATURE_HELP'] = "{---}</td></tr>";

$USERSETTINGS_TEMPLATE['extended-category'] = "<tr><td colspan='2' class='forumheader'>{CATNAME}</td></tr>";

$USERSETTINGS_TEMPLATE['extended-field'] = "<tr>
											<td style='width:40%' class='forumheader3'>
											{FIELDNAME}
											</td>
											<td style='width:60%' class='forumheader3'>
											{FIELDVAL} {HIDEFIELD}
											</td>
											</tr>
											";

$USERSETTINGS_TEMPLATE['required-field'] = "{FIELDNAME} <span class='required'><!-- emtpy --></span>";

$USERSETTINGS_TEMPLATE['edit'] = "
<div style='text-align:center'>
	<table style='".USER_WIDTH."' class='table fborder adminform'>
    	<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
	<tr>
	<td colspan='2' class='forumheader'>".LAN_USET_31."</td>
	</tr>
	{USERNAME}
	{LOGINNAME}

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_60.(e107::getPref('disable_emailcheck') ? '' : $required)."</td>
	<td style='width:60%' class='forumheader2'>
	{EMAIL}
	</td>
	</tr>

	{REALNAME}

	{CUSTOMTITLE}

	{PASSWORD1}
	{PASSWORD_LEN}
	{PASSWORD2}


	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_83."</td>
	<td style='width:60%' class='forumheader2'><span class='defaulttext'>
	{HIDEEMAIL=radio}
	</span>
	</td>
	</tr>
	
	<tr>
	<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USER_07.((int) e107::getPref('signup_option_image') === 2 ? $required : '')."</td>
	<td style='width:60%' class='forumheader2'>
	{AVATAR_REMOTE}
	</td>
	</tr>

	{AVATAR_UPLOAD}
	{PHOTO_UPLOAD}

	{USERCLASSES}
	{USEREXTENDED_ALL}

	
	{SIGNATURE=cols=58&rows=4}	
	{SIGNATURE_HELP}
	</tr>
	</table>
	<div>
	{UPDATESETTINGSBUTTON}
	{DELETEACCOUNTBUTTON}
	</div>
	</div>
	";
