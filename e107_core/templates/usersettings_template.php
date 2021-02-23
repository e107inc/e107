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

	
	
	
// e107 v2. bootstrap3 compatible template. 

$USERSETTINGS_WRAPPER['edit']['USERNAME'] =				"<div class='form-group'>
															<label for='username' class='col-sm-3 control-label'>{LAN=USER_01}</label>
														    <div class='col-sm-9'>{---}</div>
														   </div>
														";


$USERSETTINGS_WRAPPER['edit']['LOGINNAME'] = 			"<div class='form-group'>
															<label for='loginname' class='col-sm-3 control-label'>{LAN=USER_81}</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['PASSWORD1'] = 			"<div class='form-group'>
															<label for='password1' class='col-sm-3 control-label'>{LAN=USET_24}</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";
$USERSETTINGS_WRAPPER['edit']['PASSWORD2'] =			"<div class='form-group'>
															<label for='password2' class='col-sm-3 control-label'>{LAN=USET_25}</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['REALNAME'] =			"<div class='form-group'>
															<label for='realname' class='col-sm-3 control-label'>{LAN=USER_63}{REQUIRED=realname}</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['CUSTOMTITLE'] =			"<div class='form-group'>
															<label for='customtitle' class='col-sm-3 control-label'>{LAN=USER_04}:{REQUIRED=customtitle}</label>
														       	<div class='col-sm-9'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['USERCLASSES'] = 			"<div class='form-group'>
															<label  class='col-sm-3 control-label'>{LAN=USER_76}:{REQUIRED=class}</label>
														       	<div class='col-sm-9 checkbox'>{---}</div>
														   </div>
														";

$USERSETTINGS_WRAPPER['edit']['AVATAR_UPLOAD'] = 		"<div class='form-group'>
														<label for='avatar' class='col-sm-3 control-label'>{LAN=USET_26}</label>
												       	<div class='col-sm-9'>{---}</div>
													   	</div>
														";
$USERSETTINGS_WRAPPER['edit']['PHOTO_UPLOAD'] = 		"<div class='form-group'>
														<label for='photo' class='col-sm-3 control-label'>{LAN=USER_06}</label>
												       	<div class='col-sm-9'>{---}</div>
													   	</div>
														";
														
														

$USERSETTINGS_WRAPPER['edit']['SIGNATURE']			= "<div class='form-group'>
														<label for='signature' class='col-sm-3 control-label'>{LAN=USER_71}{REQUIRED=signature}</label>
												       	<div class='col-sm-9'>{---}</div>
													   	</div>
													 ";

	// $USERSETTINGS_WRAPPER['edit']['USEREXTENDED_ALL']	= "<div class='form-group'>{---}</div>";



// Bootstrap only.

$USERSETTINGS_TEMPLATE = array();

$USERSETTINGS_TEMPLATE['edit'] = "

<div>
	{USERNAME}
	{LOGINNAME}
	<div class='form-group'>
	<label for='email' class='col-sm-3 control-label'>{LAN=USER_60}{REQUIRED=email}</label>
	<div class='col-sm-9'>
		{EMAIL}
	</div>
	</div>

	{REALNAME}

	{CUSTOMTITLE}

	{PASSWORD1}

	{PASSWORD2}

	<div class='form-group'>
	<label for='hideemail' class='col-sm-3 control-label'>{LAN=USER_83}</label>
	<div class='col-sm-9'>
	{HIDEEMAIL=radio}
	</div>
	</div>

	<div class='form-group'>
	<label class='col-sm-3 control-label'>{LAN=USER_07}{REQUIRED=image}</label>
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


