<?php
/*
 * e107 website system
 *
 * Copyright (C) 2001-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/mailout.php,v $
 * $Revision: 1.5 $
 * $Date: 2009-11-17 11:13:04 $
 * $Author: marj_nl_fr $
 */

if (!defined('e107_INIT')) { exit; }


$e107 = e107::getInstance();
$action = $e107->tp->toDB(varset($_GET['mode'],'makemail'));

  switch ($action)
  {
	case 'justone' :
	  $text = 'Send mail with constraints specified by an optional plugin';
	  break;
	case 'debug' :
	  $text = 'For devs only. Not used at present';
	  break;
	case 'saved' :
	  $text = 'Select and use a saved email template to send a mailshot. Delete any template no longer required';
	  break;
	case 'pending' :
		$text = 'List of mailshots released for sending, together with current status.';
		break;
	case 'held' :
		$text = 'List of emails which have been prepared for sending, but not yet released';
		break;
	case 'sent' :
	  $text = 'List of completed mailshots. Allows you to see the sending results.<br />';
	  break;
	case 'savedmail' :
	case 'makemail' :
	  $text = 'Create an email, give it a meaningful title, and select the list of recipients. You can save everything as a template for later, or send immediately.<br />';
	  $text .= 'Email addresses may be contributed by plugins (such as newsletter), and duplicates are removed when the mail is sent<br />';
	  $text .= 'Any attachment is selected from the list of valid downloads.<br />';
	  $text .= 'Mail may be sent as plain text (most universal, and least at risk of being classed as spam), or as HTML (in which case a plain text alternative is automatically generated). The theme style
				may optionally be added to the email';
	  break;
	case 'recipients' :
		$text = 'Shows all recipients or potential recipients of an email, together with current status';
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
	  The logging option creates a text file in the system log directory. This must be deleted periodically. The \'logging
	  only\' options allow you to see exactly who would receive emails if actually sent. The \'with errors\' option fails every
	  7th email, primarily for testing';
	  break;
	 case 'maint' :
		$text = 'Maintenance functions for the mail database';
		break;
	default :
	  $text = 'Undocumented option';
  }

$ns -> tablerender('Mail Help', $text);
