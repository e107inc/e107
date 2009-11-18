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
 * $Revision: 1.10 $
 * $Date: 2009-11-18 01:06:08 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }
if (!defined("USER_WIDTH")){ define("USER_WIDTH", "width:auto"); }
global $usersettings_shortcodes, $pref;


$sc_style['CUSTOMTITLE']['pre'] = "
<tr>
<td style='width:40%' class='forumheader3'>".LAN_USER_04.":</td>
<td style='width:60%' class='forumheader2'>
";
$sc_style['CUSTOMTITLE']['post'] = "</td></tr>";

$sc_style['PASSWORD1']['pre'] = "
	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USET_24."<br /><span class='smalltext'>".LAN_USET_23."</span></td>
	<td style='width:60%' class='forumheader2'>
";

$sc_style['PASSWORD2']['pre'] = "
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USET_25."<br /><span class='smalltext'>".LAN_USET_23."</span></td>
	<td style='width:60%' class='forumheader2'>
";
$sc_style['PASSWORD2']['post'] = "
	</td>
	</tr>
";

$sc_style['PASSWORD_LEN']['pre'] = "<br /><span class='smalltext'>  (".LAN_USER_78." ";
$sc_style['PASSWORD_LEN']['post'] = " ".LAN_USER_79.")</span>";

$sc_style['USERCLASSES']['pre'] = "<tr>
<td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USER_76.":".req($pref['signup_option_class'])."
<br /><span class='smalltext'>".LAN_USER_73."</span>
</td>
<td style='width:60%' class='forumheader2'>";
$sc_style['USERCLASSES']['post'] = "</td></tr>";

$sc_style['AVATAR_UPLOAD']['pre'] = "<tr>
<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USET_26."<br /></td>
<td style='width:60%' class='forumheader2'>
";
$sc_style['AVATAR_UPLOAD']['post'] = "</td></tr>";

$sc_style['PHOTO_UPLOAD']['pre'] = "
<tr>
<td colspan='2' class='forumheader'>".LAN_USER_06."</td>
</tr>

<tr>
<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USET_27."<br /><span class='smalltext'>".LAN_USET_28."</span></td>
<td style='width:60%' class='forumheader2'><span class='smalltext'>
";
$sc_style['PHOTO_UPLOAD']['post'] = "</span></td></tr>";


$sc_style['XUP']['pre'] = "
<tr>
<td colspan='2' class='forumheader'>".LAN_USER_11."</td>
</tr>
<tr>
<td style='width:20%; vertical-align:top' class='forumheader3'>".LAN_USET_29."<br /><span class='smalltext'><a href='http://e107.org/generate_xup.php' rel='external'>".LAN_USET_30."</a></span></td>
<td style='width:80%' class='forumheader2'>
";
$sc_style['XUP']['post'] = "</td></tr>";

$USER_EXTENDED_CAT = "<tr><td colspan='2' class='forumheader'>{CATNAME}</td></tr>";
$USEREXTENDED_FIELD = "
<tr>
<td style='width:40%' class='forumheader3'>
{FIELDNAME}
</td>
<td style='width:60%' class='forumheader3'>
{FIELDVAL} {HIDEFIELD}
</td>
</tr>
";
$REQUIRED_FIELD = "{FIELDNAME}<span class='required'> *</span>";

$USERSETTINGS_EDIT = "
<div style='text-align:center'>
	<table style='".USER_WIDTH."' class='fborder adminform'>
    	<colgroup span='2'>
    		<col class='col-label' />
    		<col class='col-control' />
    	</colgroup>
	<tr>
	<td colspan='2' class='forumheader'>".LAN_USET_31."</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_01."<br /><span class='smalltext'>".LAN_USER_80."</span></td>
	<td style='width:60%' class='forumheader2'>
	{USERNAME}
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_81."<br /><span class='smalltext'>".LAN_USER_82."</span></td>
	<td style='width:60%' class='forumheader2'>
	{LOGINNAME}
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_63.req($pref['signup_option_realname'])."</td>
	<td style='width:60%' class='forumheader2'>
	{REALNAME}
	</td>
	</tr>

	{CUSTOMTITLE}

	{PASSWORD1}
	{PASSWORD_LEN}
	{PASSWORD2}

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_60.req(!$pref['disable_emailcheck'])."</td>
	<td style='width:60%' class='forumheader2'>
	{EMAIL}
	</td>
	</tr>

	<tr>
	<td style='width:40%' class='forumheader3'>".LAN_USER_83."<br /><span class='smalltext'>".LAN_USER_84."</span></td>
	<td style='width:60%' class='forumheader2'><span class='defaulttext'>
	{HIDEEMAIL=radio}
	</span>
	</td>
	</tr>

	{USERCLASSES}
	{USEREXTENDED_ALL}

	<tr><td colspan='2' class='forumheader'>".LAN_USET_8."</td></tr>
	<tr>
	<td style='width:40%;vertical-align:top' class='forumheader3'>".LAN_USER_71.req($pref['signup_option_signature'])."</td>
	<td style='width:60%' class='forumheader2'>
	{SIGNATURE=cols=58&rows=4}
	<br />
	{SIGNATURE_HELP}
	</td>
	</tr>

	<tr>
	<td colspan='2' class='forumheader'>".LAN_USER_07."</td>
	</tr>

	<tr>
	<td colspan='2' class='forumheader3' style='text-align:center'>".LAN_USET_32.($pref['im_width'] || $pref['im_height'] ? "<br />".str_replace(array('--WIDTH--','--HEIGHT--'), array($pref['im_width'], $pref['im_height']), LAN_USER_86) : "")."</td>
	</tr>

	<tr>
	<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USET_34.req($pref['signup_option_image'])."<br /><span class='smalltext'>".LAN_USET_35."</span></td>
	<td style='width:60%' class='forumheader2'>
	{AVATAR_REMOTE}
	</td>
	</tr>

	<tr>
	<td style='width:40%; vertical-align:top' class='forumheader3'>".LAN_USET_33."<br /><span class='smalltext'>".LAN_USET_36."</span></td>
	<td style='width:60%' class='forumheader2'>
	{AVATAR_CHOOSE}
	</td>
	</tr>

	{AVATAR_UPLOAD}
	{PHOTO_UPLOAD}
	{XUP}

	<tr style='vertical-align:top'>
	<td colspan='2' style='text-align:center' class='forumheader'><input class='button' type='submit' name='updatesettings' value='".LAN_USET_37."' /></td>
	</tr>
	</table>
	</div>
	";


?>