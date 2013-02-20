<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Templates for all emails
 *
 * $URL: $
 * $Revision: 11315 $
 * $Id: $
 */

/**
 * 
 *	@package     e107
 *	@subpackage	e107_templates
 *	@version 	$Id: mail_manager_class.php 11315 2010-02-10 18:18:01Z secretr $;
 *
*/


/**
 *	This file defines the default templates for each type of email which may be sent.
 *	In general it is assumed that HTML emails are being sent (with a plain text alternate part), although simple plain text emails are also possible.
 *
 *	Default values are defined for the key elements of an email:
 *
 *	$EMAIL_HEADER - the first part of the email, usually defining the headers, and everything up to and including <body>
 *	$EMAIL_FOOTER - the last part of the email - it may include a displayed footer, as well as </body> and other 'closing' tags
 *
 *		Taken as a pair, $EMAIL_HEADER.$EMAIL_FOOTER must generate standards-compliant XHTML
 *
 *	$EMAIL_BODY - the body text of the email - essentially, the message. It gets sandwiched between $EMAIL_HEADER and $EMAIL_FOOTER
 *		This must generate standards-compliant XHTML in its own right, when taken with an appropriate header and footer section.
 *		Within the template definition, insert the shortcode '{BODY}' to indicate where the passed text of the email is to be stored.
 *
 *	$EMAIL_OVERRIDES may optionally be defined, in which case it can override default mailout settings (see later). Only define this variable
 *		if you explicitly want overrides - a defined, but empty, variable may have unexpected consequences!
 *
 *	$EMAIL_PLAINTEXT - an alternative template for the alternative text part of HTML emails. Set to empty string if hard-coded default to be used
 *
 *
 *	Templates may be defined for specific purposes
 *	Each template is given a name, which is the name of the variable.
 *	This variable may be a simple string, in which case it defines the email body, and is only available via code.
 *	Alternatively the variable may be an array, in which case each element of the array defines a different aspect of the email:
 *
 *		$NAME['template_name'] is a user-friendly name shown in the mass mailer
 *		$NAME['template_type'] takes values (user|system|all) to define its purpose - only 'user' and 'all' templates are shown in the mass mailer
 *		$NAME['email_header'] defines the header - optional
 *		$NAME['email_footer'] defines the footer - optional
 *		$NAME['email_body'] defines the body text
 *		$NAME['email_overrides'] defines any mailout settings which are to be overridden (see later) - optional
 *
 *		The format and functionality of these four main array elements correspond exactly to those of the defaults already described.
 *
 *		The template need only define those variables which are to be overridden, in which case the default definitions will be used for the others.
 *
 *
 *	For templated HTML emails, a style sheet MUST be specified in the header field (if its required), in one of the following forms:
 *
 *		{STYLESHEET}				- embeds the stylesheet for the current site theme
 *		{STYLESHEET=filename,link}	- embeds a link to the referenced stylesheet file
 *		{STYLESHEET=filename}		- embeds the contents of the specified file
 *		{STYLESHEET=filename,embed} - embeds the contents of the specified file
 *
 *
 *	Where no style sheet is specified for an HTML-format email, the following applies:
 *		If 'emailstyle.css' exists in the current theme directory, it is used
 *		otherwise, the theme's 'style.css' is used
 *
 * The override variable is an array, which can override any of the following mailer parameters:
'mailer', 'smtp_server', 'smtp_username', 'smtp_password', 'sendmail', 'siteadminemail', 'siteadmin', 'smtp_pop3auth',
'SMTPDebug', 'subject', 'from', 'fromname', 'replyto', 'send_html', 'add_html_header', 'attachments', 'cc', 'bcc', 
'bouncepath', 'returnreceipt', 'priority', 'extra_header', 'wordwrap', 'split'

See e_HANDLER.mail.php for more information
 */

if (!defined('e107_INIT')) { exit; }


// @TODO: Move signup email into templated form
$includeSiteButton = e107::getPref('sitebutton');


$SIGNUPEMAIL_SUBJECT = LAN_SIGNUP_96.' {SITENAME}';
$SIGNUPEMAIL_USETHEME = 1; 			// Use CSS STYLE from THEME: 0 = Off, 1 = external, 2 = embedded
$SIGNUPEMAIL_LINKSTYLE = ''; 		// css to use on links eg. color:red;
//$SIGNUPEMAIL_IMAGES =  e_IMAGE.$includeSiteButton; // comma separated paths to image to embed. referenced below with {IMAGE1} (IMAGE2} etc.  Not required
$SIGNUPEMAIL_CC = "";  				// comma separated email addresses to put in CC of the signup email.
$SIGNUPEMAIL_BCC = "";   			// comma separated email addresses to put in BCC of the signup email.
$SIGNUPEMAIL_ATTACHMENTS = ""; 		// files-path array of attachments. eg. array(e_FILE."myfile.zip",e_FILE."myotherfile.zip");
$SIGNUPEMAIL_BACKGROUNDIMAGE = "";	// absolute path to a background image eg. e_IMAGE."mybackground.jpg";



/*===========================================================================
				DEFAULT EMAIL TEMPLATE VALUES
=============================================================================*/
/**
These defaults are used if not overridden by the requirements for a specific template.

There are five defaults, which must exist, and must be named as follows:
	$EMAIL_OVERRIDES - array of override settings; e.g. for mail server to use
	$EMAIL_HEADER - string for the first part of an HTML email
	$EMAIL_BODY - the 'body' text (usually a default here is meaningless!)
	$EMAIL_FOOTER - a standard footer - could include a disclaimer, a link to the site
	$EMAIL_PLAINTEXT - an alternative template for the alternative text part of HTML emails (if empty, alternate text is
							derived from the HTLM body.

In most cases only the body will be overridden; in this case it can be overridden using a variable rather than an array.
*/
/*
Optional mailer admin preferences Override. The following mailer parameters can be overridden:
'mailer', 'smtp_server', 'smtp_username', 'smtp_password', 'sendmail', 'siteadminemail', 'siteadmin', 'smtp_pop3auth',
'SMTPDebug', 'subject', 'from', 'fromname', 'replyto', 'send_html', 'add_html_header', 'attachments', 'cc', 'bcc', 
'bouncepath', 'returnreceipt', 'priority', 'extra_header', 'wordwrap', 'split'

See e_HANDLER.mail.php for more information

If required, uncomment the following block and add array elements for options to be overridden - array key is the option name
DON'T put in empty fields unless you wish to set the value to an empty value! 	*/
/*
$EMAIL_OVERRIDES = array(
	'bouncepath' => 'some email address',
	'returnreceipt' => 1
);
*/

// Not used in signup email
$EMAIL_HEADER = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' >
<head>
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
{STYLESHEET}
</head>
<body>
<div style='padding:10px'>
";


$EMAIL_BODY = 'Software malfunction - no email body text specified for template';		// Help debug

// Not used in signup email
$EMAIL_FOOTER = "
<br /><br />
{SITENAME=link}
</div>
</body>
</html>";


$EMAIL_PLAINTEXT = '';

/*===========================================================================
				TEMPLATES FOR SPECIFIC EMAIL TYPES
=============================================================================*/

/**
Each template is an array whose name must match that used in the code.
The array has two mandatory elements (name and type).
The array may have up to five optional elements, each of which overrides the corresponding default value if present
An empty element sets the field to empty.
An element that is not present results in the default being used.

Elements are as follows:
	'template_name'		- string - mandatory - a 'user-friendly' name for display
	'template_type'		- string(user|system|all) - mandatory - 'all' and 'user' templates are available for selection in the bulk mailer
	'email_overrides'	- an array
	'email_header'		- string
	'email_body'		- string
	'email_footer'		- string
	'email_plainText'	- string

// If everything is standard apart from the body, the body can be defined as a simple variable

*/
//TODO - integrate into mailout routine
/*
$MAILOUT_HEADER = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' >
<head>
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
{STYLESHEET}
</head>
<body>
<div style='padding:10px'>
";

$MAILOUT_FOOTER = "
<br /><br />
{SITENAME=link}
</div>
</body>
</html>";



*/


//-------------------------------------------------------------
//		'SIGNUP' TEMPLATE
//-------------------------------------------------------------

$SIGNUPEMAIL_TEMPLATE = "
<div style='padding:10px'>
<div style='text-align:left; width:90%'>
".LAN_EMAIL_01." {USERNAME},<br />
<br />".
LAN_SIGNUP_97." {SITENAME}<br />
".LAN_SIGNUP_21."<br />
<br />
{ACTIVATION_LINK}<br />
<br />
".LAN_SIGNUP_59."<br />
<br />
".LAN_SIGNUP_18."<br />
<br />
".LAN_LOGINNAME.": <b> {LOGINNAME} </b><br />
".LAN_PASSWORD.": <b> {PASSWORD} </b><br />
<br />
".LAN_EMAIL_04."<br />
".LAN_EMAIL_05."<br />
<br />
".LAN_EMAIL_06."<br />
<br />
{SITENAME}<br />
{SITEURL}
<br /><br />".($includeSiteButton ? "<a href='".SITEURL."' title=''><img src='".e_IMAGE_ABS.str_replace('{e_IMAGE}', '', $includeSiteButton)."' alt='' /></a>" : '')."
</div>
</div>
";

//-------------------------------------------------------------
//		'NOTIFY' TEMPLATE
//-------------------------------------------------------------
$NOTIFY_TEMPLATE = array(
	'template_name' => 'Notify',
	'template_type' => 'system',
	'email_overrides' => '',
	'email_header' => "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml' >
		<head>
		<meta http-equiv='content-type' content='text/html; charset=utf-8' />
		</head>
		<body>
		<div style='padding:0px 10px'>
		",
	'email_body' => '{BODY}',
	'email_footer' => "<br /><br />
		{SITENAME=link}
		</div>
		</body>
		</html>",
	'email_plainText' => ''
	);


//-------------------------------------------------------------
//		USER-DEFINED TEMPLATES (for mass mailouts)
//-------------------------------------------------------------
/*
$TEST_TEMPLATE = array(
	'template_name' => 'TEst1',
	'template_type' => 'system',
	'email_overrides' => '',
//	'email_header' - any header information (usually loaded from the default)
	'email_body' => '{BODY}',
	'email_footer' => 'footer',
	'email_plainText' => ''
	);
$TEST2_TEMPLATE = array(
	'template_name' => 'TEst2',
	'template_type' => 'all',
	'email_overrides' => '',
//	'email_header' - any header information (usually loaded from the default)
	'email_body' => '{BODY}',
	'email_footer' => 'footer'
	);
$TEST3_TEMPLATE = array(
	'template_name' => 'TEst4',
	'template_type' => 'user',
	'email_overrides' => '',
//	'email_header' - any header information (usually loaded from the default)
	'email_body' => '{BODY}',
	'email_footer' => 'footer'
	);
$TEST4_TEMPLATE = array(
	'template_name' => 'TEst5',
	'email_overrides' => '',
//	'email_header' - any header information (usually loaded from the default)
	'email_body' => '{BODY}',
	'email_footer' => 'footer'
	);
	*/
$WHATSNEW_TEMPLATE = array(
	'template_name' => 'WhatsNew',
	'template_type' => 'user',
	'email_overrides' => '',
//	'email_header' - any header information (usually loaded from the default)
	'email_body' => 'All the latest news and updates.<br />{BODY}<br />To find out more, simply click on the links!',
//	'email_footer' => 'footer'
	);
$MONTHLYUPDATE_TEMPLATE = array(
	'template_name' => 'MonthlyUpdate',
	'template_type' => 'user',
	'email_overrides' => '',
//	'email_header' - any header information (usually loaded from the default)
	'email_body' => 'Just to keep you up to date, here\'s a reminder of what\'s changed in the past month.<br />
	{BODY}<br />To find out more, simply click on the links!',
//	'email_footer' => 'footer'
	);

?>