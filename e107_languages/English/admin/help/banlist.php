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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/banlist.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$caption = "Banning users from your site";
if (e_QUERY) list($action,$junk) = explode('-',e_QUERY.'-'); else $action = 'list';		// Non-standard separator in query

switch ($action)
{
case 'transfer' :
  $text = 'This page allows you to transfer banlist data to and from this site as CSV (Comma Separated Variable) files.<br /><br />';
  $text .= "<b>Data Export</b><br />
  Select the types of ban to export. The fields will be delimited by the chosen separator, and optionally included within the selected quotation marks.<br /><br />";
  $text .= "<b>Data Import</b><br />
  You can choose whether the imported bans replace existing imported bans, or whether they add to the list. If the imported data includes an expiry date/time, you
  can select whether this is used, or whether the value for this site is used.<br /><br />";
  $text .= "<b>CSV Format</b><br />
  The format of each line in the file is: IP/email, date, expiry, type, reason, notes.<br />
  Date and expiry are in the format YYYYMMDD_HHMMDD, except that a zero value indicates 'unknown' or 'indefinite'<br />
  Only the IP or email address is essential; the other fields are imported if present.<br /><br />
  <b>Note:</b> You will need to modify filetypes.xml to allow admins to upload the 'CSV' file type.";
  break;
case 'times' :
  $text = 'This page sets the default behaviour for various types of ban.<br />
  If a message is specified, this will be shown to the user (where appropriate). If the message starts with \'http://\' or \'https://\' control is 
  passed to the specified URL. Otherwise the user will most likely get a blank screen.<br />
  The ban will be in force for the time specified; after which it will be cleared next time they access the site.';
  break;
case 'options' :
  $text = '<b>Reverse DNS</b><br />
    If enabled, the user\'s IP address is looked up to obtain the associated domain name. This accesses an external server, so there may
	be a delay before the information is available - and if the server is off-line, there may be a very long delay.<br /><br />
	You can choose to look up server names on all site accesses, or only when adding a new ban.<br /><br />
	<b>Maximum Access Rate</b><br />
	This sets the maximum number of site accesses permitted from a single user or IP address in any five-minute period, and is intended
	to detect denial of service attacks. At 90% of the selected limit, the user receives a warning; on reaching the limit they are banned.
	Different thresholds may be set for guests and logged-in users.<br /><br />
	<b>Retrigger Ban Period</b><br />
	This option is only relevant if the option to ban users for a specified time, rather than indefinitely, has been used. If enabled, and
	the user attempts to access the site, the ban period is extended (as if the ban had just started).
	';
  break;
case 'edit' :
case 'add' :
$text = "You can ban users from your site at this screen.<br />
Either enter their full IP address or use a wildcard to ban a range of IP addresses. You can also enter an email address to stop a user registering as a member on your site.<br /><br />
<b>Banning by IP address:</b><br />
Entering the IP address 123.123.123.123 will stop the user with that address visiting your site.<br />
Entering an IP address with one or more wildcards in the end blocks, such as 123.123.123.* or 214.098.*.*, will stop anyone in that IP range from visiting your 
site. (Note that there must be exactly four groups of digits or asterisks)<br /><br />
IPV6 format addresses are also supported, including '::' to represent a block of zero values. Each pair of digits in the end fields may be a separate wildcard<br /><br />
<b>Banning by email address</b><br />
Entering the email address foo@bar.com will stop anyone using that email address from registering as a member on your site.<br />
Entering the email address *@bar.com will stop anyone using that email domain from registering as a member on your site.<br /><br />
<b>Banning by user name</b><br />
This is done from the user administration page.<br /><br />";
  break;
case 'whadd' :
case 'whedit' :
  $text = "You can specify IP addresses which you know to be 'friendly' here - generally those for the main site admins, to guarantee that they can
  always gain access to the site.<br />
  You are advised to keep the number of addresses in this list to an absolute minimum; both for security, and to minimise the impact on site performance.";
  break;
case 'banlog' :
  $text = 'This shows a list of all site accesses involving an address which is in the ban list or the white list. The \'reason\' column shows the outcome.';
  break;
case 'white' :
  $text = "This page shows a list of all IP addresses and email addresses which are explicitly permitted.<br />
    This list takes priority over the ban list - it should not be possible for an address from this list to be banned.<br />
	All addresses must be manually entered.";
  break;
case 'list' :
default :
$text = 'This page shows a list of all IP addresses, hostnames and email addresses which are banned. 
(Banned users are shown on the user administration page)<br /><br />
<b>Automatic Bans</b><br />
e107 automatically bans individual IP addresses if they attempt to flood the site, as well as addresses with failed logins.<br />
These bans also appear in this list. You can select (on the options page) what to do for each type of ban.<br /><br />
<b>Removing a ban</b><br />
You can set an expiry period for each type of ban, in which case the entry is removed once the ban period expires. Otherwise the
 ban remains until you remove it.<br />
You can modify the ban period from this page - times are calculated from now.';
}
$ns -> tablerender($caption, $text);
