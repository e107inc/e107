<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2014 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Templates for all emails
 *
 */

/**
 *	This file defines the default templates for each type of email which may be sent.
 *	In general it is assumed that HTML emails are being sent (with a plain text alternate part), although simple plain text emails are also possible.
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

$includeSiteButton = e107::getPref('sitebutton');
e107::lan('core','signup'); // required for when mailer runs under CLI.

/*
$SIGNUPEMAIL_SUBJECT = LAN_SIGNUP_96.' {SITENAME}';
$SIGNUPEMAIL_USETHEME = 1; 			// Use CSS STYLE from THEME: 0 = Off, 1 = external, 2 = embedded
$SIGNUPEMAIL_LINKSTYLE = ''; 		// css to use on links eg. color:red;
//$SIGNUPEMAIL_IMAGES =  e_IMAGE.$includeSiteButton; // comma separated paths to image to embed. referenced below with {IMAGE1} (IMAGE2} etc.  Not required
$SIGNUPEMAIL_CC = "";  				// comma separated email addresses to put in CC of the signup email.
$SIGNUPEMAIL_BCC = "";   			// comma separated email addresses to put in BCC of the signup email.
$SIGNUPEMAIL_ATTACHMENTS = ""; 		// files-path array of attachments. eg. array(e_FILE."myfile.zip",e_FILE."myotherfile.zip");
$SIGNUPEMAIL_BACKGROUNDIMAGE = "";	// absolute path to a background image eg. e_IMAGE."mybackground.jpg";
*/


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


/** Standardized v2 template rewrite 
 * 
 * Format for individual emails sent by e107 (not bulk emails for now) - a work in progress - bulk could be ported later.
 * @see e107Email::sendEmail(); 
 * Aim: to make email templates follow the same spec. as other templates while remaining as intuitive as other v2 templates in e107. 
 * Note: giving a template a 'name' value will make it available in the admin->mailout area. 
 */


// Default - test email and when no template specified. 
$EMAIL_TEMPLATE = [];
$EMAIL_TEMPLATE['default']['name']	 		= 'Default';
$EMAIL_TEMPLATE['default']['subject']		= '{SITENAME}: {SUBJECT} ';
$EMAIL_TEMPLATE['default']['header']		= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
												<html xmlns='http://www.w3.org/1999/xhtml' >
												<head>
												<meta http-equiv='content-type' content='text/html; charset=utf-8' />
												<style type='text/css'>
													body { padding:10px; background-color: #E1E1E1 } 
													 div#body { padding:10px; width: 93%; max-width:800px; background-color: #FFFFFF; border-radius: 5px; font-family: helvetica,arial }
													.video-thumbnail { max-width: 400px }
													.media img { max-width:600px }
													.unsubscribe { font-size:11px; color:#aaaaaa; margin-top:20px; padding:20px 0; border-top:solid 1px #e5e5e5; }
													.sitebutton img { max-height: 100px; border-radius:4px; margin-right:5px }
													h4.sitename  { font-size: 20px; margin-bottom:5px; margin-top:0; text-decoration:none }
													h4.sitename a { text-decoration:none }
													.text-right { text-align: right }
													.text-center { text-align: center }
													a.siteurl { font-size: 14px }
													a 			{ color: #428BCA }

													/* Bootstrap */
													table.table {
													    border-collapse: collapse;
													    border-spacing: 0;
													    width:100%;
													}
													.table-striped > tbody > tr:nth-child(2n+1) > td, .table-striped > tbody > tr:nth-child(2n+1) > th {
													    background-color: #F9F9F9;
													}
													.table-bordered > thead > tr > th, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > tbody > tr > td, .table-bordered > tfoot > tr > td {
													    border: 1px solid #DDD;
													}
													.table > thead > tr > th, .table > tbody > tr > th, .table > tfoot > tr > th, .table > thead > tr > td, .table > tbody > tr > td, .table > tfoot > tr > td {
													    padding: 8px;
													    line-height: 1.42857;
													    vertical-align: top;
													    border-top: 1px solid #DDD;
													}
													.alert {  padding: 15px;  margin-bottom: 20px; border: 1px solid transparent; border-radius: 4px; 	}
													.alert-info {  color: #31708f; background-color: #d9edf7; border-color: #bce8f1; }
													.alert-warning { color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc; }
													.alert-danger { color: #a94442; background-color: #f2dede; border-color: #ebccd1; }
													.alert-success { color: #3c763d; background-color: #dff0d8; border-color: #d6e9c6; }
													a.btn {  text-decoration: none; }
													.btn {
													    display: inline-block;
													    padding: 6px 12px;
													    margin-bottom: 0;
													    font-size: 14px;
													    font-weight: 400;
													    line-height: 1.42857143;
													    text-align: center;
													    white-space: nowrap;
													    vertical-align: middle;
													    -ms-touch-action: manipulation;
													    touch-action: manipulation;
													    cursor: pointer;
													    -webkit-user-select: none;
													    -moz-user-select: none;
													    -ms-user-select: none;
													    user-select: none;
													    background-image: none;
													    border: 1px solid transparent;
													    border-radius: 4px;
													}
													.btn-primary 	{ color: #fff; background-color: #337ab7; border-color: #2e6da4; }
													.btn-success 	{ color: #fff; background-color: #5cb85c; border-color: #4cae4c; }
													.btn-warning 	{ color: #fff; background-color: #f0ad4e; border-color: #eea236; }
													.btn-danger  	{ color: #fff; background-color: #d9534f; border-color: #d43f3a; }
													.btn-lg 		{ padding: 10px 16px; font-size: 18px; line-height: 1.3333333; border-radius: 6px; }
													.btn-sm 		{ padding: 5px 10px; font-size: 12px; line-height: 1.5; border-radius: 3px; }
												</style>
												</head>
												
												<body>
												<div id='body'>
												";

$EMAIL_TEMPLATE['default']['body']			= "{BODY}<br />{MEDIA1}{MEDIA2}{MEDIA3}{MEDIA4}{MEDIA5}";											

$EMAIL_TEMPLATE['default']['footer']		= "<br /><br /><table cellspacing='4'>
												<tr><td>{SITEBUTTON: type=email&h=60}</td>
												<td><h4 class='sitename'>{SITENAME=link}</h4>
												<small>{SITEURL}</small></td></tr>
												</table>
												</div>
												</body>
												</html>";

// -------------------------------


/**
 *  Signup Template. 
 * @example developer tests
 * signup.php?preview
 * signup.php?test 
 * signup.php?preview.aftersignup
 */
$EMAIL_TEMPLATE['signup']['name']	 		= 'Signup';
$EMAIL_TEMPLATE['signup']['subject']		= '{SITENAME}: '. LAN_SIGNUP_98;
$EMAIL_TEMPLATE['signup']['header']			= $EMAIL_TEMPLATE['default']['header'];
$EMAIL_TEMPLATE['signup']['body'] 			= "											
												<div style='text-align:left'>
												".LAN_EMAIL_01." {USERNAME},<br />
												<br />".
												LAN_SIGNUP_97." {SITENAME}<br />
												".LAN_SIGNUP_21."<br />
												<br />
												{ACTIVATION_LINK}<br />
												<br />
												<small>".LAN_SIGNUP_59."</small><br />
												<br />
												".LAN_SIGNUP_18."<br />
												<br />
												".LAN_LOGIN.": <b> {LOGINNAME} </b><br />
												".LAN_PASSWORD.": <b> {PASSWORD} </b><br />
												<br />
												".LAN_EMAIL_04."<br />
												".LAN_EMAIL_05."<br />
												<br />
												".LAN_EMAIL_06."<br />
												<br />

											
												<br /><table cellspacing='4'>
												<tr><td>{SITEBUTTON: type=email&h=60}</td>
												<td><h4 class='sitename'>{SITENAME=link}</h4>
												{SITEURL}</td></tr>
												</table>
												</div>
												
												";
$EMAIL_TEMPLATE['signup']['footer']			= "</div>
												</body>
												</html>";
$EMAIL_TEMPLATE['signup']['cc']				= "";
$EMAIL_TEMPLATE['signup']['bcc']			= "";
$EMAIL_TEMPLATE['signup']['attachments']	= "";



// -----------------------------

												
/*
 * QUICK ADD USER EMAIL TEMPLATE - BODY. 	
 * This is the email that is sent when an admin creates a user account in admin. "Quick Add User"
 	USRLAN_185 = A user account has been created for you at {SITEURL} with the following login:<br />Login Name: {LOGIN}<br />Password: {PASSWORD}<br/><br />
	USRLAN_186 = Please go to the site as soon as possible and log in, then change your password using the \'Settings\' option.<br /><br />
						You can also change other settings at the same time.<br /><br />Note that your password cannot be recovered if you lose it.
*/
$EMAIL_TEMPLATE['quickadduser']['subject']		= '{SITENAME}: {SUBJECT} ';
$EMAIL_TEMPLATE['quickadduser']['header']		= $EMAIL_TEMPLATE['default']['header']; // will use default header above. 												
$EMAIL_TEMPLATE['quickadduser']['body']			= USRLAN_185.USRLAN_186;											
$EMAIL_TEMPLATE['quickadduser']['footer']		= $EMAIL_TEMPLATE['default']['footer']; // will use default footer above. 		


// ------- Notify (@see admin-> notify)
//$EMAIL_WRAPPER['notify']['SUBJECT'] = "*** {---} ***";

$EMAIL_TEMPLATE['notify']['name']	 		    = 'Notify';
$EMAIL_TEMPLATE['notify']['subject']			= '{SITENAME}: {SUBJECT} ';
$EMAIL_TEMPLATE['notify']['header']		        = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
												<html xmlns='http://www.w3.org/1999/xhtml' >
												<head>
												<meta http-equiv='content-type' content='text/html; charset=utf-8' />
												<style type='text/css'>
													body { padding:10px; background-color: #E1E1E1 }
													 div#body { padding:10px; width: 93%; max-width:800px; background-color: #FFFFFF; border-radius: 5px; font-family: helvetica,arial }
													.video-thumbnail { max-width: 400px }
													.media img { max-width:200px; border-radius:5px  }
													.text-right { text-align: right }
													.text-muted { color: #cccccc;  }
													.pull-left { float:left }
													h1,h2,h3,h4 { margin-top:0; }
													h2 small { font-size: 50%; padding-left:20px }
													h2 { margin-bottom: 5px }
													h2 a { text-decoration: none; margin-bottom:5px }
													h4 { margin-bottom: 3px }
													a 			{ color: #428BCA }
													.datestamp { float: right; padding-top:10px }
													.author { font-style: italic ; color: #cccccc}
													.summary { padding:5px 0;  }
													 .btn {
													    display: inline-block;
													    padding: 6px 12px;
													    margin-bottom: 0px;
														margin-top:10px;
													    font-size: 14px;
													    font-weight: 400;
													    line-height: 1.42857;
													    text-align: center;
													    white-space: nowrap;
													    vertical-align: middle;
													    cursor: pointer;
													    -moz-user-select: none;
													    background-image: none;
													    border: 1px solid transparent;
													    border-radius: 4px;
														text-decoration: none;
													}
													 .btn-primary {
													    color: #FFF;
													    background-color: #428BCA;
													    border-color: #357EBD;
													}

													td { padding:5px; vertical-align: top }
													td.body { width:80% }
													table { width: 100%; margin-top:8px; border-top: 1px solid #cccccc; border-bottom: 1px solid #cccccc;padding:10px 0 }
													.unsubscribe { font-size:11px; color:#aaaaaa; margin-top:20px; padding:20px 0; border-top:solid 1px #e5e5e5; }
													.sitebutton img { padding-right:5px; border-radius:3px }

												</style>
												</head>

												<body>
												<div id='body'>
												";
$EMAIL_TEMPLATE['notify']['body']			    = "<h2><span class='pull-left'>{SITEBUTTON: type=email&h=30}</span> {SITENAME=link} <small class='text-muted datestamp'>{DATE_LONG}</small></h2><table><tr><td class='media'>{MEDIA1}</td><td class='body'>{BODY}</td></tr></table>";
$EMAIL_TEMPLATE['notify']['footer']		        = "<br /><br />


												<div class='unsubscribe'>{UNSUBSCRIBE_MESSAGE}</div>
												</div>
												</body>
												</html>";


// ------ User-Specific Templates 


$EMAIL_TEMPLATE['monthly']['name']				= 'Monthly Update';												
$EMAIL_TEMPLATE['monthly']['subject']			= '{SITENAME}: {SUBJECT} ';
$EMAIL_TEMPLATE['monthly']['header']			= $EMAIL_TEMPLATE['default']['header']; // will use default header above. 	
$EMAIL_TEMPLATE['monthly']['body']				= "Hi {USERNAME},<br /><br />Just to keep you up to date, here's a reminder of what's changed in the past month.<br />{BODY}{MEDIA1}{MEDIA2}{MEDIA3}{MEDIA4}{MEDIA5}To find out more, simply click on the links!";
$EMAIL_TEMPLATE['monthly']['footer']			= $EMAIL_TEMPLATE['default']['footer'];




$EMAIL_TEMPLATE['whatsnew']['name']				= "What's New";												
$EMAIL_TEMPLATE['whatsnew']['subject']			= '{SITENAME}: {SUBJECT} ';
$EMAIL_TEMPLATE['whatsnew']['header']			= $EMAIL_TEMPLATE['default']['header']; // will use default header above. 	
$EMAIL_TEMPLATE['whatsnew']['body']				= "Hi {USERNAME},<br />{BODY}";
$EMAIL_TEMPLATE['whatsnew']['footer']			= $EMAIL_TEMPLATE['default']['footer'];



// ------ A Dummy Example for theme developers. 

$EMAIL_TEMPLATE['example']['subject']			= '{SITENAME}: {SUBJECT} ';
$EMAIL_TEMPLATE['example']['header']			= $EMAIL_TEMPLATE['default']['header']; // will use default header above. 	
$EMAIL_TEMPLATE['example']['body']				= $EMAIL_TEMPLATE['default']['body']; // will use default header above. 	
$EMAIL_TEMPLATE['example']['footer']			= "<br /><br />
												
												<a href='{SITEURL}'><img src='{THEME}images/my-signature.png' alt='{SITENAME}' /></a>
												</div>
												</body>
												</html>";

												// Overrides any data sent from script. 											
$EMAIL_TEMPLATE['example']['cc']				= "example@example.com";
$EMAIL_TEMPLATE['example']['bcc']				= "example@example.com";
$EMAIL_TEMPLATE['example']['attachment']		= "{e_PLUGIN}myplugin/myattachment.zip";
$EMAIL_TEMPLATE['example']['priority']			= 3; // (1 = High, 3 = Normal, 5 = low).



