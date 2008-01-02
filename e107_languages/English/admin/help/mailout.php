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
|     $Revision: 1.3 $
|     $Date: 2008-01-02 20:14:13 $
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
	  $text = '<b>Configure mailshot options.</b><br />
	  A test email is sent using the current method and settings.<br /><br />';
	  $text .= '<b>Emailing Method</b><br />
	  Use SMTP to send mail if possible. The settings will depend on your host\'s mail server.<br /><br />';
	  $text .= '<b>Bounced Emails</b><br />
	  You can specify a POP3 account to receive the return response when an email is undeliverable. Normally this will be a standard
	  POP3 account; use the TLS-related options only if specifically required by your host<br /><br />';
	  $text .= '<b>Email Address Sources</b><br />
	  If you have additional mail-related plugins, you can select which of them may contribute email addresses to the list.<br /><br />';
	  $text .= '<b>Logging</b><br />
	  The logging option creates a text file in the stats plugin\'s log directory. This must be deleted periodically. The \'logging
	  only\' options allow you to see exactly who would receive emails if actually sent. The \'with errors\' option fails every
	  7th email, primarily for testing';
	  break;
	default :
	  $text = 'Undocumented option';
  }

$ns -> tablerender("Mail Help", $text);
?>