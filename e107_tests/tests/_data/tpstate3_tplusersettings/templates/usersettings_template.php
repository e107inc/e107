<?php

if (!defined("USER_WIDTH"))
{
	define("USER_WIDTH", "width:97%");
}


// global $usersettings_shortcodes, $pref;


$sc_style['CUSTOMTITLE']['pre'] 		= "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USER_04.":</td>
											<td style='width:60%' class='forumheader2'>\n";
$sc_style['CUSTOMTITLE']['post'] 		= "</td></tr>\n";

$sc_style['PASSWORD1']['pre'] 			= "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USET_24."</td>
											<td style='width:60%' class='forumheader2'>\n";
$sc_style['PASSWORD1']['post'] 			= "</td></tr>\n";


$sc_style['PASSWORD2']['pre'] 			= "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USET_25."</td>
											<td style='width:60%' class='forumheader2'>\n";
$sc_style['PASSWORD2']['post'] 			= "</td></tr>\n";


$sc_style['PASSWORD_LEN']['pre'] 		= "<br /><span class='smalltext'>  (".LAN_USER_78." ";
$sc_style['PASSWORD_LEN']['post'] 		= " ".LAN_USER_79.")</span>";

$sc_style['USERCLASSES']['pre'] 		= "<tr>
											<td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USER_76.":".req(e107::getPref('signup_option_class'))."
											<br /><span class='smalltext'>".LAN_USER_73."</span>
											</td>
											<td style='width:60%' class='forumheader2'>";
$sc_style['USERCLASSES']['post'] 		= "</td></tr>\n";

$sc_style['AVATAR_UPLOAD']['pre'] 		= "<tr>
											<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USET_26."<br /></td>
											<td style='width:60%' class='forumheader2'>\n";
$sc_style['AVATAR_UPLOAD']['post'] 		= "</td></tr>\n";


$sc_style['PHOTO_UPLOAD']['pre'] 		= "<tr>
											<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USER_06."</td>
											<td style='width:60%' class='forumheader2'><span class='smalltext'>\n";
$sc_style['PHOTO_UPLOAD']['post'] 		= "</span></td></tr>";


$sc_style['USERNAME']['pre'] 			= "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USER_01."</td>
											<td style='width:60%' class='forumheader2'>\n";

$sc_style['USERNAME']['post'] 			= "</td</tr>";


$sc_style['LOGINNAME']['pre'] 			= "<tr>
											<td style='width:40%' class='forumheader3'>".LAN_USER_81."</td>
											<td style='width:60%' class='forumheader2'>\n";
$sc_style['LOGINNAME']['post'] 			= "</td></tr>\n";


$sc_style['SIGNATURE']['pre']			= "<tr><td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USER_71.req(e107::getPref('signup_option_signature'))."</td>
											<td style='width:60%' class='forumheader2'>";

//$sc_style['SIGNATURE']['pre']			= "<tr><td style='width:40%;vertical-align:top' class='forumheader3'></td>
			//								<td style='width:60%' class='forumheader2'>";

// $sc_style['SIGNATURE_HELP']['pre']		= "</td></tr>";											\
$sc_style['SIGNATURE_HELP']['post']		= "</td></tr>";


$USER_EXTENDED_CAT 						= "<tr><td colspan='2' class='forumheader'>{CATNAME}</td></tr>";
$USEREXTENDED_FIELD 					= "<tr>
											<td style='width:40%' class='forumheader3'>
											{FIELDNAME}
											</td>
											<td style='width:60%' class='forumheader3'>
											{FIELDVAL} {HIDEFIELD}
											</td>
											</tr>
											";




$REQUIRED_FIELD 						= "{FIELDNAME} <span class='required'><!-- emtpy --></span>";

// After Saving has occurred.
$USERSETTINGS_MESSAGE 					= "{MESSAGE}";
$USERSETTINGS_MESSAGE_CAPTION 			= LAN_OK;
$USERSETTINGS_EDIT_CAPTION 				= LAN_USET_39; 	// 'Update User Settings'




$USERSETTINGS_EDIT = "
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
	<td style='width:40%' class='forumheader3'>".LAN_USER_60.req(!e107::getPref('disable_emailcheck'))."</td>
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
	<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USER_07.req(e107::getPref('signup_option_image'))."</td>
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

