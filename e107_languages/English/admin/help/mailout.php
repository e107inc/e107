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
|     $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/mailout.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-12-22 14:49:34 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

if (e_QUERY) list($action,$junk) = explode('.',e_QUERY); else $action = 'makemail';

  switch ($action)
  {
	case 'justone' :
	  $text = 'Send mail with constraints specified by an optional plugin';
	  break;
	case 'debug' :
	  $text = 'For devs only. A second query parameter matches the gen_type field in the \'generic\' table. Ignore the column headings';
	  break;
	case 'list' :
	  $text = 'Select and use a saved email template to send a mailshot. Delete any template no longer required';
	  break;
	case 'mailouts' :
	  $text = 'List of stored mailshots. Allows you to see whether they have been sent, and re-send any emails which failed.<br />';
	  $text .= 'You can also view some detail of the email, including the error reason for some of those that failed.<br />';
	  $text .= 'To retry outstanding emails, click on the \'resend\' icon. Then click on \'Proceed\', which will open a progress window.';
	  $text .= ' To abort a mailshot, click on the \'Cancel\' button in the main screen.';
	  break;
	case 'savedmail' :
	case 'makemail' :
	  $text = 'Create an email, and select the list of recipients. You can save the email text as a template for later, or send immediately.<br />';
	  $text .= 'Any attachment is selected from the list of valid downloads.';
	  break;
	case 'prefs' :
	  $text = 'Configure mailshot options.<br />';
	  $text .= 'A test email is sent using the current method and settings.<br />';
	  $text .= 'Use SMTP to send mail if possible. The settings will depend on your host\'s mail server.<br />';
	  $text .= 'You can specifiy a POP3 account to receive the return response when an email is undeliverable.<br />';
	  $text .= 'If you have additional mail-related plugins, you can select which of them may contribute email addresses to the list.<br />';
	  $text .= 'The logging option creates a text file in the stats plugin\'s log directory. This must be deleted periodically.';
	  break;
	default :
	  $text = 'Undocumented option';
  }

$ns -> tablerender("Mail Help", $text);
?>