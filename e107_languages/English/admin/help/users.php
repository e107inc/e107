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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/users.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$caption = "User Management";
if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'list';

switch ($action)
{
  case 'create' :
    $text = "This page allows you to create a user, who is immediately a normal member of the site, with whatever class memberships you assign.<br /><br />
	If you check the 'Send confirmation email with password to new user' box, the login name and password are sent <b>in clear</b>, so the user must change
	their password on receipt.
	";
    break;

  case 'prune' :
	$text = "Bulk deletes unwanted users from the database. These may be either those who failed to complete the registration process, or 
	those whose email addresses bounced. Any forum posts, comments etc remain, and are flagged as from a 'Deleted User'.
	";
	break;

  case 'unverified' :
	$text = "Shows those members who have failed to complete their registration. Options generally as for the complete members list.";
    break;

  case 'options' :
	$text = "Sets various options affecting all users.<br /><br />
	<b>Allow Avatar Upload</b><br />
	If enabled, users may upload an avatar of their choice, which is stored on your server. This can have security implications.<br /><br />
	<b>Allow Photo Upload</b><br />
	If enabled, users may upload a photo of their choice, which is stored on your server. This can have security implications.<br /><br />
	<b>Online User Tracking</b><br />
	This must be enabled to keep track of most user activity, including counts of online members. It does increase database activity significantly.<br /><br />
	<b>Member Information</b><br />
	Determines which class of members can view the member list.
	";
    break;

  default :
	$text = "This page shows a list of your registered members. You can update their settings, give them administrator status and set their user class 
	among other things.<br /><br />
	The sort order can be changed by clicking on the column heading.<br />
	The displayed columns can be changed by clicking on 'Edit Display Options', selecting the required columns, then clicking on 'Search/Refresh'<br /><br />
	<b>Info</b><br />
	Show this user's profile (can also click on their display name)<br /><br />
	<b>Edit</b><br />
	Edit the settings for this user. <br /><br />
	<b>Ban/Unban</b><br />
	Determine whether the user can access the site. <br /><br />
	<b>Activate</b><br />
	This activates a user who would normally have replied to the activation email.<br /><br />
	<b>Resend Email</b><br />
	Resends the activation email to the user.<br /><br />
	<b>Test email</b><br />
	Check user's email address for validity (doesn't send an email)<br /><br />
	<b>Set Class</b><br />
	Set class membership for a user<br /><br />
	<b>Delete</b><br />
	Totally removes the user from membership of the site (any forum posts, comments etc remain, and are flagged as from a 'Deleted User')
	";
}


$ns -> tablerender($caption, $text);
unset($text);
