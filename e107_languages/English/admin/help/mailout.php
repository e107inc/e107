<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT')) { exit; }

$e107 = e107::getInstance();

$action = e107::getParser()->toDB(varset($_GET['mode'],'makemail'));

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
		$text = 'List of mailshots released for sending, together with current status. The mail scheduler task will process these emails as it is able, taking account of
		the earliest and latest sending dates you set';
		break;
	case 'held' :
		$text = 'List of emails which have been prepared for sending, but not yet released';
		break;
	case 'sent' :
	  $text = 'List of completed mailshots. Allows you to see the sending results.<br />';
	  break;
	case 'savedmail' :
	case 'makemail' :
	case 'main' :
	  $text = 'Create an email, give it a meaningful title, and select the list of recipients. You can save everything as a template for later, or send immediately.<br />';
	  $text .= 'Email addresses may be contributed by plugins (such as newsletter), and duplicates are removed when the mail is sent<br />';
	  $text .= 'Any attachment is selected from the list of valid downloads.<br />';
	  $text .= 'Mail may be sent as plain text (most universal, and least at risk of being classed as spam), or as HTML (in which case a plain text alternative is automatically generated). The theme style
				may optionally be added to the email. Alternatively a predefined template can be selected.';
	  break;
	case 'recipients' :
		$text = 'Shows all recipients or potential recipients of an email, together with current status';
		break;
	case 'prefs' :
	  $text = '<b>Configure mailshot options.</b><br />
	  A test email is sent using the current method and settings. If you are having problems with emails bouncing, try sending a test email to: <i>check-auth@verifier.port25.com</i> to ensure your server MX records are correct. Of course, be sure your site email address is correct before doing so.<br /><br />';
	  $text .= '<b>Emailing Method</b><br />
	  Use SMTP to send mail if possible. The settings will depend on your host\'s mail server.<br /><br />';
	  $text .= '<b>Default email format</b><br />
	  Emails may be sent either in plain text only, or in HTML format. The latter generally gives a better appearance, but is more prone to being filtered by various
	  security measures. If you select HTML, a separate plain text part is added.<br /><br />';
	  $text .= '<b>Bulk mail controls</b><br />
	  The values you set here will depend on your host, and on the number of emails you send; it may be possible to set all values to zero so that the
	  mail queue is emptied virtually instantly. Typically it is best to send less than 500 emails per hour.<br /><br />';
	  $text .= '<b>Bounced Emails</b><br />
	  You can specify an email address to receive the return response when an email is undeliverable. If you have control over your server, you can specify the
	  separate scheduler-driven auto-processing script; this receives bounce messages as they arrive, and updates status instantly. Otherwise you can specify a separate email account,
	  which can be checked either periodically (using the scheduler), or manually via the user options menu. Normally this will be a standard
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

$ns->tablerender('Mail Help', $text);
