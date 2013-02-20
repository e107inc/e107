<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * e107 Main
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_handlers/redirection_class.php $
 * $Id: redirection_class.php 11922 2010-10-27 11:31:18Z secretr $
 * $Revision: 11315 $
 */

/**
 * 
 * @package     e107
 * @subpackage	e107_handlers
 * @version     $Revision: 12078 $
 * @author      $Author: e107coders $
 *
 *	Mailout handler - concerned with processing and sending a single email
 *	Extends the PHPMailer class
 */

/*
TODO:
1. Mustn't include header in text section of emails
2. Option to wrap HTML in a standard header (up to <body>) and footer (from </body>)

Maybe each template is an array with several parts - optional header and footer, use defaults if not defined
header looks for the {STYLESHEET} variable
If we do that, can have a single override file, plus a core file

3. mail (PHP method) - note that it has parameters for additional headers and other parameters
4. Check that language support works - PHPMailer defaults to English if other files not available
		- PHPMailer expects a 2-letter code - $this->SetLanguage(CORE_LC)     - e.g. 'en', 'br'
5. Logging:
	- Use rolling log for errors - error string(s) available - sort out entry
	- Look at support of some other logging options
9. Make sure SMTPDebug can be set (TRUE/FALSE)
12. Check support for port number - ATM we just override for SSL. Looks as if phpmailer can take it from end of server link.
18. Note object iteration - may be useful for dump of object state
19. Consider overriding error handler
20. Look at using new prefs structure
21. Should we always send an ID?
22. Force singleton so all mail sending flow controlled a bit (but not where parameters overridden in constructor)


Tested so far (with PHP4 version)
------------
SMTP send (standard)
Return receipts
text or mixed
replyto
priority field
TLS to googlemail (use TLS)


Notes if problems
-----------------
1. Attachment adding call had dirname() added round path.
2. There are legacy and new methods for generating a multi-part body (HTML + plain text). Only the new method handles inline images.
		- Currently uses the new method (which is part of phpmailer)


General notes
-------------
1. Can specify a comma-separated list of smtp servers - presumably all require the same login credentials
2. qmail can be used (if available) by selecting sendmail, and setting the sendmail path to that for qmail instead
3. phpmailer does trim() on passed parameters where needed - so we don't need to.
4. phpmailer has its own list of MIME types.
5. Attachments - note method available for passing string attachments
		- AddStringAttachment($string,$filename,$encoding,$type)
6. Several email address-related methods can accept two comma-separated strings, one for addresses and one for related names
7. Its possible to send a text-only email by passing an array of parameters including 'send_html' = FALSE
8. For bulk emailing, must call the 'allSent()' method when complete to ensure SMTP mailer is properly closed.
9. For sending through googlemail (and presumably gmail), use TLS
10. Note that the 'add_html_header' option adds only the DOCTYPE bits - not the <head>....</head> section


Possible Enhancements
---------------------
1. Support other fields:
		ContentType
		Encoding    - ???. Defaults to 8-bit



Preferences used:
	$pref['mailer'] - connection type - SMTP, sendmail etc
	
	$pref['mail_options'] - NEW - general mailing options
		textonly 		- if true, defaults to plain text emails
		hostname=text	- used in Message ID and received headers, and default Helo string. (Otherwise server-related default used)
	
	$pref['mail_log_options'] - NEW. Logging options (also used in mailout_process). Comma-separated list of values
		1 - logenable		- numeric value 0..3 controlling logging to a text file
		2 - add_email		- if '1', the detail of the email is logged as well

	$pref['smtp_server']	|
	$pref['smtp_username']	| Server details. USed for POP3 server if POP before SMTP authorisation
	$pref['smtp_password']	|
	$pref['smtp_keepalive'] - deprecated in favour of option - flag
	$pref['smtp_pop3auth'] - deprecated in favour of option - POP before SMTP authorisation flag
	$pref['smtp_options'] - NEW - comma separated list:
		keepalive			- If active, bulk email send keeps the SMTP connection open, closing it every $pref['mail_pause'] emails
		useVERP				- formats return path to facilitate bounce processing
		secure=[TLS|SSL]	- enable secure authorisation by TLS or SSL
		pop3auth			- enable POP before SMTP authorisation
		helo=text			- Alternative Helo string

	$pref['sendmail'] - path to sendmail

	$pref['mail_pause'] - number of emails to send before pause
	$pref['mail_pausetime'] - time to pause

	$pref['mail_bounce_email'] - 'reply to' address
	$pref['mail_bounce_pop3']
	$pref['mail_bounce_user']
	$pref['mail_bounce_pass']

Usage
=====
1. Create new object of the correct class
2. Set up everything - to/from/email etc
3. Call create_connection()
4. Call send_mail()
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }


//define('MAIL_DEBUG',TRUE);
//define('LOG_CALLER', TRUE);

require_once(e_HANDLER.'phpmailer/class.phpmailer.php');

// Directory for log (if enabled)
define('MAIL_LOG_PATH',e_LOG);

class e107Email extends PHPMailer
{
	private $general_opts = array();
	private $logEnable = 0;				// 0 = log disabled, 1 = 'dry run' (debug and log, no send). 2 = 'log all' (send, and log result)
	private $logHandle = FALSE;			// Save handle of log file if opened

	private $localUseVerp = FALSE;		// Use our own variable - PHPMailer one doesn't work with all mailers
	private $save_bouncepath = '';		// Used with VERP

	private $add_email = 0;				// 1 includes email detail in log (if logging enabled, of course)
	private $allow_html = 1;			// Flag for HTML conversion - '1' = default, FALSE = disable, TRUE = force.
	private $add_HTML_header = FALSE;	// If TRUE, inserts a standard HTML header at the front of the HTML part of the email (set FALSE for BC)
	private $SendCount = 0;				// Keep track of how many emails sent since last SMTP open/connect (used for SMTP KeepAlive)
	private $TotalSent = 0;				// Info might be of interest
	private $TotalErrors = 0;			// Count errors in sending emails
	private $pause_amount = 10;			// Number of emails to send before pausing/resetting (or closing if SMTPkeepAlive set)
	private $pause_time = 1;			// Time to pause after sending a block of emails

	public	$legacyBody = FALSE;		// TRUE enables legacy conversion of plain text body to HTML in HTML emails

	/**
	 * Constructor sets up all the global options, and sensible defaults - it should be the only place the prefs are accessed
	 * 
	 * @var array $overrides - array of values which override mail-related prefs. Key is the same as the corresponding pref.
	 *						- second batch of keys can preset values configurable through the arraySet() method
	 * @return none
	 */
	public function __construct($overrides = FALSE) 
	{
		parent::__construct(FALSE);		// Parent constructor - no exceptions for now

		$e107 = e107::getInstance();
		$pref = e107::pref('core');

		$this->CharSet = 'utf-8';
		$this->SetLanguage(CORE_LC);

		if (($overrides === FALSE) || !is_array($overrides))
		{
			$overrides = array();
		}
		
		foreach (array('mailer', 'smtp_server', 'smtp_username', 'smtp_password', 'sendmail', 'siteadminemail', 'siteadmin') as $k)
		{
			if (!isset($overrides[$k])) $overrides[$k] = $pref[$k];
		}
		$this->pause_amount = varset($pref['mail_pause'], 10);
		$this->pause_time =  varset($pref['mail_pausetime'], 1);

		if (varsettrue($pref['mail_options'])) $this->general_opts = explode(',',$pref['mail_options'],'');
		if (defined('MAIL_DEBUG')) echo 'Mail_options: '.$pref['mail_options'].' Count: '.count($this->general_opts).'<br />';
		foreach ($this->general_opts as $k => $v) 
		{
			$v = trim($v);
			$this->general_opts[$k] = $v;
			if (strpos($v,'hostname') === 0)
			{
				list(,$this->HostName) = explode('=',$v);
				if (defined('MAIL_DEBUG')) echo "Host name set to: {$this->HostName}<br />";
			}
		}

		list($this->logEnable,$this->add_email) = explode(',',varset($pref['mail_log_options'],'0,0'));

		switch ($overrides['mailer'])
		{
			case 'smtp' :
				$smtp_options = array();
				$temp_opts = explode(',',varset($pref['smtp_options'],''));
				if (varsettrue($overrides ['smtp_pop3auth'])) $temp_opts[] = 'pop3auth';		// Legacy option - remove later
				if (varsettrue($pref['smtp_keepalive'])) $temp_opts[] = 'keepalive';	// Legacy option - remove later
				foreach ($temp_opts as $k=>$v) 
				{ 
					if (strpos($v,'=') !== FALSE)
					{
						list($v,$k) = explode('=',$v,2);
						$smtp_options[trim($v)] = trim($k);
					}
					else
					{
						$smtp_options[trim($v)] = TRUE;		// Simple on/off option
					}
				}
				unset($temp_opts);

				$this->IsSMTP();			// Enable SMTP functions
				if (varsettrue($smtp_options['helo'])) $this->Helo = $smtp_options['helo'];

				if (isset($smtp_options['pop3auth']))			// We've made sure this is set
				{	// Need POP-before-SMTP authorisation
					require_once(e_HANDLER.'phpmailer/class.pop3.php');
					$pop = new POP3();
					$pop->Authorise($overrides['smtp_server'], 110, 30, $overrides['smtp_username'], $overrides['smtp_password'], 1);
				}

				$this->Mailer = 'smtp';
				$this->localUseVerp = isset($smtp_options['useVERP']);
				if (isset($smtp_options['secure']))
				{
					switch ($smtp_options['secure'])
					{
						case 'TLS' :
							$this->SMTPSecure = 'tls';
							$this->Port = 465;		// Can also use port 587, and maybe even 25
							break;
						case 'SSL' :
							$this->SMTPSecure = 'ssl';
							$this->Port = 465;
							break;
						default :
							if (defined('MAIL_DEBUG')) echo "Invalid option: {$smtp_options['secure']}<br />";
					}
				}
				$this->SMTPKeepAlive = varset($smtp_options['keepalive'],FALSE);									// ***** Control this
				$this->Host = $overrides['smtp_server'];
				if($overrides['smtp_username'] && $overrides['smtp_password'])
				{
					$this->SMTPAuth = (!isset($smtp_options['pop3auth']));
					$this->Username = $overrides['smtp_username'];
					$this->Password = $overrides['smtp_password'];
				}
				break;
			case 'sendmail' :
				$this->Mailer = 'sendmail';
				$this->Sendmail = ($overrides['sendmail']) ? $overrides['sendmail'] : '/usr/sbin/sendmail -t -i -r '.varsettrue($pref['replyto_email'],$overrides['siteadminemail']);
				break;
			case 'php' :
				$this->Mailer = 'mail';
				break;
		}
		if (varsettrue($pref['mail_bounce_email'])) $this->Sender = $pref['mail_bounce_email'];

		$this->FromName = $e107->tp->toHTML(varsettrue($pref['replyto_name'],$overrides['siteadmin']),'','RAWTEXT');
		$this->From = $e107->tp->toHTML(varsettrue($pref['replyto_email'],$overrides['siteadminemail']),'','RAWTEXT');
		$this->WordWrap = 76;			// Set a sensible default

		// Now look for any overrides - slightly cumbersome way of doing it, but does give control over what can be set from here
		// Options are those accepted by the arraySet() method.
		foreach (array('SMTPDebug', 'email_subject', 'email_sender_email', 'email_sender_name', 'email_replyto', 'send_html', 
						'add_html_header', 'email_attach', 'email_copy_to', 'email_bcopy_to', 
						'bouncepath', 'returnreceipt', 'email_inline_images', 'email_priority', 'extra_header', 'wordwrap', 'split') as $opt)
		{
			if (isset($overrides[$opt]))
			{
				$this->arraySet(array($opt => $overrides[$opt]));
			}
		}
	}

	/**
	 * Set log level
	 * @param int $level 0|1|2
	 * @param int $emailDetails 0|1
	 * @return e107Email
	 */
	public function logEnable($level, $emailDetails = null)
	{
		$this->logEnable = (int) $level;
		if(null !== $this->add_email)
		{
			$this->add_email = (int) $emailDetails;
		}
		return $this;
	}
	
	/**
	 * Disable log completely
	 * @return e107Email
	 */
	public function logDisable()
	{
		$this->logEnable = 0;
		$this->add_email = 0;
		return $this;
	}


	/**
	 * Format 'to' address and name
	 * 
	 * @param string $email - email address of recipient
	 * @param string $to - name of recipient
	 * @return string in form: Fred Bloggs<fred.bloggs@somewhere.com>
	 */
	public function makePrintableAddress($email,$to)
	{
		$to = trim($to);
		$email = trim($email);
		return $to.' <'.$email.'>';
	}


	/**
	 * Log functions - write to a log file
	 * Each entry logged to a separate line
	 * 
	 * @return none
	 */
	protected function openLog($logInfo = TRUE)
	{
		if ($this->logEnable && ($this->logHandle === FALSE))
		{
			$logFileName = MAIL_LOG_PATH.'mailoutlog.txt';
			$this->logHandle = fopen($logFileName, 'a');      // Always append to file
		}
		if ($this->logHandle !== FALSE)
		{
			fwrite($this->logHandle,"\n\n=====".date('H:i:s y.m.d')."----------------------------------------------------------------=====\r\n");
			if ($logInfo)
			{
				fwrite($this->logHandle,'  Mailer opened by '.USERNAME." - ID: {$this->MessageID}. Subject: {$this->Subject}  Log action: {$this->logEnable}\r\n");
				if ($this->add_email)
				{
					fwrite($this->logHandle, 'From: '.$this->From.' ('.$this->FromName.")\r\n");
					fwrite($this->logHandle, 'Sender: '.$this->Sender."\r\n");
					fwrite($this->logHandle, 'Subject: '.$this->Subject."\r\n");
					// Following are private variables ATM
//					fwrite($this->logHandle, 'CC: '.$email_info['copy_to']."\r\n");
//					fwrite($this->logHandle, 'BCC: '.$email_info['bcopy_to']."\r\n");
//					fwrite($this->logHandle, 'Attach: '.$attach."\r\n");
					fwrite($this->logHandle, 'Body: '.$this->Body."\r\n");
					fwrite($this->logHandle,"-----------------------------------------------------------\r\n");
				}
			}
			if (defined('LOG_CALLER'))
			{
				$temp = debug_backtrace();
				foreach ($temp as $t)
				{
					if (!isset($t['class']) || ($t['class'] != 'e107Email'))
					{
						fwrite($this->logHandle, print_a($t,TRUE)."\r\n");		// Found the caller
						break;
					}
				}
			}
		}
	}

	/**
	 *	Add a line to log file - time/date is prepended, and CRLF is appended
	 *
	 *	@param string $text - line to add
	 *	@return none
	 */
	protected function logLine($text)
	{
		if ($this->logEnable && ($this->logHandle > 0))
		{
			fwrite($this->logHandle,date('H:i:s y.m.d').' - '.$text."\r\n");
		}
	}

	/**
	 *	Close log
	 */
	protected function closeLog()
	{
		if ($this->logEnable && ($this->logHandle > 0))
		{
			fclose($this->logHandle);
		}
	}



	/**
	 * Add a list of addresses to one of the address lists.
	 * @param string $list - 'to', 'replyto', 'cc', 'bcc'
	 * @param string $addresses - comma separated
	 * @param string $names - either a single name (used for all addresses) or a comma-separated list corresponding to the address list
	 * If the name field for an entry is blank, or there are not enough entries, the address is substituted
	 * @return TRUE if list accepted, FALSE if invalid list name
	 */
	public function AddAddressList($list = 'to',$addresses,$names = '')
	{
		$list = trim(strtolower($list));
		$tmp = explode(',',$addresses);

		if (strpos($names,',') === FALSE)
		{
			$names = array_fill(0,count($tmp),$names);		// Same value for all addresses
		}
		else
		{
			$names = explode(',',$names);
		}
		foreach($tmp as $k => $adr)
		{
			$to_name = ($names[$k]) ? $names[$k] : $adr;
			switch ($list)
			{
				case 'to' :
					$this->AddAddress($adr, $to_name);
					break;
				case 'replyto' :
					$this->AddReplyTo($adr, $to_name);
					break;
				case 'cc' :
					if($this->Mailer == 'mail')
					{
						$this->AddCustomHeader('Cc: '.$adr);
					}
					else
					{
						$this->AddCC($adr, $to_name);
					}
					break;
				case 'bcc' :
					if($this->Mailer == 'mail')
					{
						$this->AddCustomHeader('Bcc: '.$adr);
					}
					else
					{
						$this->AddBCC($adr, $to_name);
					}
					break;
				default :
					return FALSE;
			}
		}
		return TRUE;
	}



	/**
	 *	Create email body, primarily using the inbuilt functionality of phpmailer
	 *
	 *	@param boolean|int $want_HTML determines whether an HTML part of the email is created. 1 uses default setting for HTML part. Set TRUE to enable, FALSE to disable
	 *	@param boolean $add_HTML_header - if TRUE, a standard HTML header is added to the front of the HTML part
	 *
	 *	@return none
	 */
	public function makeBody($message,$want_HTML = 1, $add_HTML_header = FALSE)
	{
		switch (varset($this->general_opts['textonly'],'off'))
		{
		  case 'pref' :		// Disable HTML as default
			if ($want_HTML == 1) $want_HTML = FALSE;
			break;
		  case 'force' :	// Always disable HTML
			$want_HTML = FALSE;
			break;
		}
	
		if ($want_HTML !== FALSE)
		{
			if (defined('MAIL_DEBUG')) echo "Generating multipart email<br />";
			if ($add_HTML_header)
			{
				$message = 	"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n
				<html xmlns='http://www.w3.org/1999/xhtml' >\n".$message;
			}
			if ($this->legacyBody && !preg_match('/<(font|br|a|img|b)/i', $message)) // Assume html if it includes one of these tags
			{	// Otherwise assume its a plain text message which needs some conversion to render in HTML
				$message = htmlspecialchars($message,ENT_QUOTES,$this->CharSet);
				$message = preg_replace('%(http|ftp|https)(://\S+)%', '<a href="\1\2">\1\2</a>', $message);
				$message = preg_replace('/([[:space:]()[{}])(www.[-a-zA-Z0-9@:%_\+.~#?&\/\/=]+)/i', '\\1<a href="http://\\2">\\2</a>', $message);
				$message = preg_replace('/([_\.0-9a-z-]+@([0-9a-z][0-9a-z-]+\.)+[a-z]{2,3})/i', '<a href="mailto:\\1">\\1</a>', $message);
				$message = str_replace("\r\n","\n",$message);		// Handle alternative newline characters
				$message = str_replace("\n\r","\n",$message);		// Handle alternative newline characters
				$message = str_replace("\r","\n",$message);			// Handle alternative newline characters
				$message = str_replace("\n", "<br />\n", $message);
			}
			$this->MsgHTML($message);		// Theoretically this should do everything, including handling of inline images.
		}
		else
		{	// generate the plain text as the sole part of the email
			if (defined('MAIL_DEBUG')) echo "Generating plain text email<br />";
			if (strpos($message,'</style>') !== FALSE)
			{
				$text = strstr($message,'</style>');
			}
			else
			{
				$text = $message;
			}

			$text = str_replace('<br />', "\n", $text);
			$text = strip_tags(str_replace('<br>', "\n", $text));
			
			// TODO: strip bbcodes here

			$this->Body = $text;
			$this->AltBody = '';		// Single part email
		}
	}

  
	/**
	 *	Add attachments to the current email - either a single one as a string, or an array  
	 *	Always sent in base64 encoding
	 *
	 *	@param string|array $attachments - single attachment name as a string, or any number as an array
	 *
	 *	@return none
	 */
	public function attach($attachments)
	{
		if (!$attachments) return;
		if (!is_array($attachments)) $attachments = array($attachments);

		foreach($attachments as $attach)
		{
			$tempName = basename($attach);
			if(is_readable($attach) && $tempName)
			{	// First parameter is complete path + filename; second parameter is 'name' of file to send
				$ext = pathinfo($attach, PATHINFO_EXTENSION);
				$this->AddAttachment($attach, $tempName,'base64',$this->_mime_types($ext));
			}
		}
	}


	/**
	 *	Add inline images (should usually be handled automatically by PHPMailer)
	 *
	 *	@param string $inline - comma separated list of file names
	 */
	function addInlineImages($inline)
	{
		if(!$inline) return;
		$tmp = explode(',',$inline);
		foreach($tmp as $inline_img)
		{
			if(is_readable($inline_img) && !is_dir($inline_img))
			{
				$ext = pathinfo($inline_img, PATHINFO_EXTENSION);
				$this->AddEmbeddedImage($inline_img, md5($inline_img), basename($inline_img),'base64',$this->_mime_types($ext));
			}
		}
	}


	/**
	 *	Sets one or more parameters from an array. See @see{sendEmail()} for list of parameters
	 *	Where parameter not present, doesn't change it - so can repeatedly call this function for bulk mailing, or to build up the list
	 *	(Note that there is no requirement to use this method for everything; parameters can be set by mixing this method with individual setting)
	 *
	 *	@param array $paramlist - list of parameters to set/change. Key is parameter name. @see{sendEmail()} for list of parameters
	 *
	 *	@return int zero if no errors detected
	 */
	public function arraySet($paramlist)
	{
		if (isset($paramlist['SMTPDebug'])) $this->SMTPDebug = $paramlist['SMTPDebug'];			// 'FALSE' is a valid value!
		if (varsettrue($paramlist['email_subject'])) $this->Subject = $paramlist['email_subject'];
		if (varsettrue($paramlist['email_sender_email'])) $this->From = $paramlist['email_sender_email'];
		if (varsettrue($paramlist['email_sender_name'])) $this->FromName = $paramlist['email_sender_name'];
		if (varsettrue($paramlist['email_replyto'])) $this->AddAddressList('replyto',$paramlist['email_replyto'],varsettrue($paramlist['email_replytonames'],''));	
		if (isset($paramlist['send_html'])) $this->allow_html = $paramlist['send_html'];							// 'FALSE' is a valid value!
		if (isset($paramlist['add_html_header'])) $this->add_HTML_header = $paramlist['add_html_header'];			// 'FALSE' is a valid value!
		if (varsettrue($paramlist['email_body'])) $this->makeBody($paramlist['email_body'], $this->allow_html, $this->add_HTML_header);
		if (varsettrue($paramlist['email_attach'])) $this->attach($paramlist['email_attach']);
		if (varsettrue($paramlist['email_copy_to'])) $this->AddAddressList('cc',$paramlist['email_copy_to'],varsettrue($paramlist['email_cc_names'],''));
		if (varsettrue($paramlist['email_bcopy_to'])) $this->AddAddressList('bcc',$paramlist['email_bcopy_to'],varsettrue($paramlist['email_bcc_names'],''));
		if (varsettrue($paramlist['bouncepath'])) 
		{
			$this->Sender = $paramlist['bouncepath'];				// Bounce path
			$this->save_bouncepath = $paramlist['bouncepath'];		// Bounce path
		}
		if (varsettrue($paramlist['returnreceipt'])) $this->ConfirmReadingTo = $paramlist['returnreceipt'];
		if (varsettrue($paramlist['email_inline_images'])) $this->addInlineImages($paramlist['email_inline_images']);
		if (varsettrue($paramlist['email_priority'])) $this->Priority = $paramlist['email_priority'];
		if (varsettrue($paramlist['e107_header'])) $this->AddCustomHeader("X-e107-id: {$paramlist['e107_header']}");
		if (varsettrue($paramlist['extra_header'])) 
		{
			if (is_array($paramlist['extra_header']))
			{
				foreach($paramlist['extra_header'] as $eh)
				{
					$this->addCustomHeader($eh);
				}
			}
			else
			{
				$this->addCustomHeader($paramlist['extra_header']);
			}
		}

		if (varset($paramlist['wordwrap'])) $this->WordWrap = $paramlist['wordwrap'];
		if (varsettrue($paramlist['split'])) $this->SingleTo = ($paramlist['split'] != FALSE);

		return 0;				// No error
	}



	/**
		Send an email where the bulk of the data is passed in an array. Returns 0 on success.
		(Even if the array is null, because everything previously set up, this is the preferred entry point)
		Where parameter not present in the array, doesn't get changed - useful for bulk mailing
		If doing bulk mailing with repetitive calls, set $bulkmail parameter true, and must call allSent() when completed
		Some of these parameters have been made compatible with the array calculated by render_email() in signup.php
		Possible array parameters:
		$eml['email_subject']
		$eml['email_sender_email']	- 'From' email address
		$eml['email_sender_name']	- 'From' name
		$eml['email_replyto']		- Optional 'reply to' field 
		$eml['email_replytonames']	- Name(s) corresponding to 'reply to' field  - only used if 'replyto' used
		$eml['send_html']		- if TRUE, includes HTML part in messages (only those added after this flag)
		$eml['add_html_header'] - if TRUE, adds the 2-line DOCTYPE declaration to the front of the HTML part (but doesn't add <head>...</head>)
		$eml['email_body']		- message body. May be HTML or text. Added according to the current state of the HTML enable flag
		$eml['email_attach']	- string if one file, array of filenames if one or more.
		$eml['email_copy_to']	- comma-separated list of cc addresses.
		$eml['email_cc_names']  - comma-separated list of cc names. Optional, used only if $eml['email_copy_to'] specified
		$eml['email_bcopy_to']	- comma-separated list
		$eml['email_bcc_names'] - comma-separated list of bcc names. Optional, used only if $eml['email_copy_to'] specified
		$eml['bouncepath']		- Sender field (used for bounces)
		$eml['returnreceipt']	- email address for notification of receipt (reading)
		$eml['email_inline_images']	- array of files for inline images
		$eml['priority']		- Email priority (1 = High, 3 = Normal, 5 = low)
		$eml['e107_header']		- Adds specific 'X-e107-id:' header
		$eml['extra_header']	- additional headers (format is name: value
		$eml['wordwrap']		- Set wordwrap value
		$eml['split']			- If true, sends an individual email to each recipient

	 *	@param string $send_to - recipient email address
	 *	@param string $to_name - recipient name
	 *	@param array $eml - optional array of additional parameters (see above)
	 *	@param boolean $bulkmail - set TRUE if this email is one of a bulk send; FALSE if an isolated email
	 *
	 *	@return boolean|string - TRUE if success, error message if failure
	 */
	public function sendEmail($send_to, $to_name, $eml = '', $bulkmail = FALSE)
	{
		if (count($eml))
		{	// Set parameters from list
			$ret = $this->arraySet($eml);
			if ($ret) return $ret;
		}

		if ($bulkmail && $this->localUseVerp && $this->save_bouncepath && (strpos($this->save_bouncepath,'@') !== FALSE))
		{
			// Format where sender is owner@origin, target is user@domain is: owner+user=domain@origin
			list($our_sender,$our_domain) = explode('@', $this->save_bouncepath,2);
			if ($our_sender && $our_domain)
			{
				$this->Sender = $our_sender.'+'.str_replace($send_to,'@','=').'@'.$our_domain; 
			}
		}

		$this->AddAddressList('to',$send_to,$to_name);

		$this->openLog();		// Delay log open until now, so all parameters set up

		$result = TRUE;			// Temporary 'success' flag
		$this->SendCount++;

		if (($this->logEnable == 0) || ($this->logEnable == 2))
		{
			$result = $this->Send();		// Actually send email

			if (!$bulkmail && !$this->SMTPKeepAlive && ($this->Mailer == 'smtp')) $this->SmtpClose();
		}
		else
		{	// Debug
			$result = TRUE;
			//print_a($this);
			if (($this->logEnable == 3) && (($this->SendCount % 7) == 4)) $result = FALSE;			// Fail one email in 7 for testing
		}

		$this->TotalSent++;
		if (($this->pause_amount > 0) && ($this->SendCount >= $this->pause_amount))
		{
			if ($this->SMTPKeepAlive && ($this->Mailer == 'smtp')) $this->SmtpClose();
			sleep($this->pause_time);
			$this->SendCount = 0;
		}

		$this->logLine("Send to {$to_name} at {$send_to} Mail-ID={$this->MessageID} - ".($result ? 'Success' : 'Fail'));
		
		$this->ClearAddresses();			// In case we send another email
		$this->ClearCustomHeaders();

		if ($result)
		{
			$this->closeLog();
			return TRUE;
		}

		$this->logLine('Error info: '.$this->ErrorInfo);
		// Error sending email
		$e107 = e107::getInstance();
		$e107->admin_log->e_log_event(3,debug_backtrace(),"MAIL","Send Failed",$this->ErrorInfo,FALSE,LOG_TO_ROLLING);
		$this->TotalErrors++;
		$this->closeLog();
		return $this->ErrorInfo;
	}



	/**
	 *	Called after a bulk mailing completed, to tidy up nicely
	 *
	 *	@return none
	 */
	public function allSent()
	{
		if ($this->SMTPKeepAlive && ($this->Mailer == 'smtp') && ($this->SendCount > 0)) 
		{
			$this->SmtpClose();
			$this->SendCount = 0;
		}
	}



	/**
	 *	Evaluates the message and returns modifications for inline images and backgrounds
	 *	Also creates an alternative plain text part (unless $this->AltBody already non-empty)
	 *	Modification of standard PHPMailer function (which it overrides)
	 *	@access public
	 *
	 *	@param string $message - the mail body to send
	 *	@basedir string - optional 'root part' of paths specified in email - prepended as necessary
	 *	
	 *	@return string none (message saved ready to send)
	 */
	public function MsgHTML($message, $basedir = '') 
	{
		preg_match_all("/(src|background)=([\"\'])(.*)\\2/Ui", $message, $images);			// Modified to accept single quotes as well
		if(isset($images[3])) 
		{
			foreach($images[3] as $i => $url) 
			{
				// do not change urls for absolute images (thanks to corvuscorax)
				if (!preg_match('#^[A-z]+://#',$url)) 
				{
					$delim = $images[2][$i];			// Will be single or double quote
					$filename = basename($url);
					$directory = dirname($url);
					if ($directory == '.') $directory='';
					if (strpos($directory, e_HTTP) === 0)
					{
						$directory = substr(SERVERBASE, 0, -1).$directory;		// Convert to absolute server reference
						$basedir = '';
					}
					//echo "CID file {$filename} in {$directory}. Base = ".SERVERBASE."<   BaseDir = {$basedir}<br />";
					$cid = 'cid:' . md5($filename);
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$mimeType  = self::_mime_types($ext);
					if ( (strlen($basedir) > 1) && (substr($basedir,-1) != '/') && (substr($basedir,-1) != '\\')) { $basedir .= '/'; }
					if ( strlen($directory) > 1 && substr($directory,-1) != '/' && substr($directory,-1) != '\\') { $directory .= '/'; }
					//echo "Add image: {$basedir}|{$directory}|{$filename}<br />";
					if ( $this->AddEmbeddedImage($basedir.$directory.$filename, md5($filename), $filename, 'base64',$mimeType) ) 
					{
						// $images[1][$i] contains 'src' or 'background'
						$message = preg_replace("/".$images[1][$i]."=".$delim.preg_quote($url, '/').$delim."/Ui", $images[1][$i]."=".$delim.$cid.$delim, $message);
					}
					else
					{
						if (defined('MAIL_DEBUG')) echo "Add embedded image {$url} failed<br />";
					}
				}
			}
		}
		$this->IsHTML(true);
		$this->Body = $message;
		//print_a($message);
		$textMsg = str_replace(array('<br />', '<br>'), "\n", $message);		// Modified to make sure newlines carried through
		$textMsg = preg_replace('#^.*?<body.*?>#', '', $textMsg);		// Knock off everything up to and including the body statement (if present)
		$textMsg = preg_replace('#</body.*?>.*$#', '', $textMsg);		// Knock off everything after and including the </body> (if present)
		$textMsg = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s','',$textMsg)));
		if (!empty($textMsg) && empty($this->AltBody)) 
		{
			$this->AltBody = html_entity_decode($textMsg);
		}
		if (empty($this->AltBody)) 
		{
			$this->AltBody = 'To view this email message, enable HTML!' . "\n\n";
		}
	}

}		// End of e107Mailer class



//-----------------------------
//		Exception handler
//-----------------------------
// Overrides the phpmailer handler
// For now just work the same as the phpmailer handler - maybe add features to log to rolling log or something later
// Could throw an e107Exception
class e107MailerException extends phpmailerException 
{
	public function errorMessage() 
	{
		return parent::errorMsg();
	}
}


//--------------------------------------
//		Generic e107 Exception handler
//--------------------------------------
// Overrides the default handler - start of a more general handler
class e107Exception extends Exception 
{
    public function __construct($message = '', $code = 0) 
	{
        parent::__construct($message, $code);
		$e107 = e107::getInstance();
		$e107->admin_log->e_log_event(10,
									$this->getFile().'|@'.$this->getLine(),
									'EXCEPT',
									$this->getCode().':'.$this->getMessage(),
									$this->getTraceAsString(),
									FALSE,
									LOG_TO_ROLLING);
    }
}


//-----------------------------------------------------
//		Function call to send an email
//-----------------------------------------------------
/**
 *	Function call to send an email
 *
 *	Deprecated function
 *
 *	Preferred method is to instantiate an e107MailManager object, and use the sendEmails() method, which also allows templates.
 *
 *	see also sendTemplated() where non-default formating is required
 *
 *	Note that plain text emails are converted to HTML, and also sent with a text part
 *
 *	@param string $send_to - email address of recipient
 *	@param string $subject
 *	@param string $message
 *	@param string $to_name
 *	@param string $send_from - sender email address. (Defaults to the sitewide 'replyto' name and email if set, otherwise site admins details)
 *	@param string $from_name - sender name. If $send_from is empty, defaults to the sitewide 'replyto' name and email if set, otherwise site admins details
 *	@param string $attachments - comma-separated list of attachments
 *	@param string $Cc - comma-separated list of 'copy to' email addresses
 *	@param string $Bcc - comma-separated list of 'blind copy to' email addresses
 *	@param string $returnpath - Sets 'reply to' email address
 *	@param boolean $returnreceipt - TRUE to request receipt
 *	@param string $inline - comma separated list of images to send inline
 *
 *	@return boolean TRUE if send successfully (NOT an indication of receipt!), FALSE if error
 */
function sendemail($send_to, $subject, $message, $to_name='', $send_from='', $from_name='', $attachments='', $Cc='', $Bcc='', $returnpath='', $returnreceipt='',$inline ='') 
{
	global $mailheader_e107id;


	$overrides = array();
	// TODO: Find a way of doing this which doesn't use a global (or just ditch sendemail() )
	// Use defaults from email template?
    // ----- Mail pref. template override for parked domains, site mirrors or dynamic values
    global $EMAIL_OVERRIDES;
	if (isset($EMAIL_OVERRIDES) && is_array($EMAIL_OVERRIDES))
	{
		$overrides = &$EMAIL_OVERRIDES;		// These can override many of the email-related prefs
		if (isset($EMAIL_OVERRIDES['bouncepath'])) $returnpath = $EMAIL_OVERRIDES['bouncepath'];
		if (isset($EMAIL_OVERRIDES['returnreceipt'])) $returnreceipt = $EMAIL_OVERRIDES['returnreceipt'];
	}

	// Create a mailer object of the correct type (which auto-fills in sending method, server details)
	$mail = new e107Email($overrides);

	if (varsettrue($mailheader_e107id)) $mail->AddCustomHeader("X-e107-id: {$mailheader_e107id}");

	$mail->legacyBody = TRUE;				// Need to handle plain text email conversion to HTML
	$mail->makeBody($message);				// Add body, with conversion if required

	if($Cc) $mail->AddAddressList('cc', $Cc);

	if ($Bcc) $mail->AddAddressList('bcc', $Bcc);

	if (trim($send_from))
	{
		$mail->SetFrom($send_from, $from_name);				// These have already been defaulted to sitewide options, so no need to set again if blank
	}

	$mail->Subject = $subject;

	$mail->attach($attachments);

	// Add embedded images (should be auto-handled now)
	if ($inline) $mail->addInlineImages($inline);

	// Passed parameter overrides any system default for bounce - but should this be 'ReplyTo' address instead?
	//  if (varsettrue($returnpath)) $mail->Sender = $AddReplyToAddresses($returnpath,'');
	if (varsettrue($returnpath)) $mail->Sender = $returnpath;

	if (varsettrue($returnreceipt)) $mail->ConfirmReadingTo($returnreceipt);

	if ($mail->sendEmail($send_to,$to_name) === TRUE) 
	{	// Success
		return TRUE;
	}

	// Error info already logged
	return FALSE;
}



?>
