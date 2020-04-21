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
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/usersettings_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH"))
{
	$uw = (deftrue('BOOTSTRAP')) ? "" : "width:97%";
	define("USER_WIDTH", $uw); 	
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

	
	
	
	
// e107 v2. bootstrap3 compatible template. 

$USERSETTINGS_WRAPPER['edit']['USERNAME'] =				"<div class='form-group'>
															<label for='username' class='col-sm-3 control-label'>".LAN_USER_01."</label>
														    <div class='col-sm-9'>{---}</div>
														   </div>
														";


$USERSETTINGS_WRAPPER['edit']['LOGINNAME'] = 			"<div class='form-group'>
															<label for='loginname' class='col-sm-3 control-label'>".LAN_USER_81."</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['PASSWORD1'] = 			"<div class='form-group'>
															<label for='password1' class='col-sm-3 control-label'>".LAN_USET_24."</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";
$USERSETTINGS_WRAPPER['edit']['PASSWORD2'] =			"<div class='form-group'>
															<label for='password2' class='col-sm-3 control-label'>".LAN_USET_25."</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['REALNAME'] =			"<div class='form-group'>
															<label for='realname' class='col-sm-3 control-label'>".LAN_USER_63.req(e107::getPref('signup_option_realname'))."</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['CUSTOMTITLE'] =			"<div class='form-group'>
															<label for='customtitle' class='col-sm-3 control-label'>".LAN_USER_04.':'.req(e107::getPref('signup_option_customtitle'))."</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['USERCLASSES'] = 			"<div class='form-group'>
															<label  class='col-sm-3 control-label'>".LAN_USER_76.":".req(e107::getPref('signup_option_class'))."</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['AVATAR_UPLOAD'] = 		"<div class='form-group'>
														<label for='avatar' class='col-sm-3 control-label'>".LAN_USET_26."</label>
												       	<div class='col-sm-9'>{---}</div>
													   	</div>
														";
$USERSETTINGS_WRAPPER['edit']['PHOTO_UPLOAD'] = 		"<div class='form-group'>
														<label for='photo' class='col-sm-3 control-label'>".LAN_USER_06."</label>
												       	<div class='col-sm-9'>{---}</div>
													   	</div>
														";
														
														

$USERSETTINGS_WRAPPER['edit']['SIGNATURE']			= "<div class='form-group'>
														<label for='signature' class='col-sm-3 control-label'>".LAN_USER_71.req(e107::getPref('signup_option_signature'))."</label>
												       	<div class='col-sm-9'>{---}</div>
													   	</div>
													 ";

	// $USERSETTINGS_WRAPPER['edit']['USEREXTENDED_ALL']	= "<div class='form-group'>{---}</div>";





// Bootstrap 3 only.

$USERSETTINGS_TEMPLATE = array();

$USERSETTINGS_TEMPLATE['edit'] = "

<div>

	{USERNAME}

	{LOGINNAME}


	<div class='form-group'>
	<label for='email' class='col-sm-3 control-label'>".LAN_USER_60.req(!e107::getPref('disable_emailcheck'))."</label>
	<div class='col-sm-9'>
		{EMAIL}
	</div>
	</div>

	{REALNAME}

	{CUSTOMTITLE}

	{PASSWORD1}

	{PASSWORD2}


	<div class='form-group'>
	<label for='hideemail' class='col-sm-3 control-label'>".LAN_USER_83."</label>
	<div class='col-sm-9'>
	{HIDEEMAIL=radio}
	</div>
	</div>

	<div class='form-group'>
	<label class='col-sm-3 control-label'>".LAN_USER_07.req(e107::getPref('signup_option_image'))."</label>
	<div class='col-sm-9'>
	{AVATAR_REMOTE}
	</div>
	</div>

	{AVATAR_UPLOAD}
	{PHOTO_UPLOAD}


	{USERCLASSES}
	{USEREXTENDED_ALL=tabs}

	{SIGNATURE}
	{SIGNATURE_HELP}

	 <div class='form-group'>
      <div class='col-sm-offset-3 col-sm-9'>
		{UPDATESETTINGSBUTTON}
		{DELETEACCOUNTBUTTON}
	</div>
	</div>

</div>
";

$USERSETTINGS_TEMPLATE['extended-category'] = "<h3>{CATNAME}</h3>";
$USERSETTINGS_TEMPLATE['extended-field'] = "<div class='form-group'>
	<label class='col-sm-3 control-label'>{FIELDNAME} {REQUIRED}</label>
	<div class='col-sm-9'>
	{FIELDVAL} {HIDEFIELD}
	</div>
	</div>
											";


