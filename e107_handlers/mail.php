<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 * 
 * Mail handler
 */

/**
 * 
 * @package     e107
 * @subpackage	e107_handlers
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

//require_once(e_HANDLER.'phpmailer/class.phpmailer.php');
//require_once(e_HANDLER.'phpmailer/class.smtp.php');
require_once(e_HANDLER.'phpmailer/PHPMailerAutoload.php');

// Directory for log (if enabled)
define('MAIL_LOG_PATH',e_LOG);

class e107Email extends PHPMailer
{
	private $general_opts 		= array();
	private $logEnable 			= 2;		// 0 = log disabled, 1 = 'dry run' (debug and log, no send). 2 = 'log all' (send, and log result)
	private $logHandle 			= FALSE;	// Save handle of log file if opened

	private $localUseVerp 		= FALSE;	// Use our own variable - PHPMailer one doesn't work with all mailers
	private $save_bouncepath 	= '';		// Used with VERP

	private $add_email 			= 0;		// 1 includes email detail in log (if logging enabled, of course)
	private $allow_html 		= 1;		// Flag for HTML conversion - '1' = default, FALSE = disable, TRUE = force.
	private $add_HTML_header 	= FALSE;	// If TRUE, inserts a standard HTML header at the front of the HTML part of the email (set FALSE for BC)
	private $SendCount 			= 0;		// Keep track of how many emails sent since last SMTP open/connect (used for SMTP KeepAlive)
	private $TotalSent 			= 0;		// Info might be of interest
	private $TotalErrors 		= 0;		// Count errors in sending emails
	private $pause_amount 		= 10;		// Number of emails to send before pausing/resetting (or closing if SMTPkeepAlive set)
	private $pause_time 		= 1;		// Time to pause after sending a block of emails

	public	$legacyBody 		= false;	// TRUE enables legacy conversion of plain text body to HTML in HTML emails
	private $debug 				= false;	// echos various debug info when set to true. 
	private $pref 				= array();	// Store code prefs. 
	private $previewMode		= false;
	private $previewAttachments = array();
	private $overrides			 = array(
									// Legacy					// New 
									'SMTPDebug' 			=> 'SMTPDebug', 
									'subject' 				=> 'subject',
									'email_sender_email' 	=> 'sender_email',
									'email_sender_name' 	=> 'sender_name',
									'email_replyto'			=> 'replyto',
									'send_html'				=> 'html',
									'email_attach'			=> 'attachment',
									'email_copy_to'			=> 'cc',
									'email_bcopy_to'		=> 'bcc',
									'bouncepath'			=> 'bouncepath',
									'returnreceipt'			=> 'returnreceipt',
									'email_priority'		=> 'priority',
									'extra_header'			=> 'extra_header',
									'wordwrap'				=> 'wordwrap',
									'split'					=> 'split',
									'smtp_server'			=> 'smtp_server',
									'smtp_username'			=> 'smtp_username',
									'smtp_password'			=> 'smtp_password',
									'smtp_port'			    => 'smtp_port',
								);
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


		$pref = e107::pref('core');
		$tp = e107::getParser();

		
		if(defined('MAIL_DEBUG'))
		{
			$this->debug = true;
		}
		else 
		{
			$this->Debugoutput = 'handlePHPMailerDebug';	
		}
		
		$this->pref = $pref;

		$this->CharSet = 'utf-8';
		$this->setLanguage(CORE_LC);


		if (($overrides === FALSE) || !is_array($overrides))
		{
			$overrides = array();
		}
		
		foreach (array('mailer', 'smtp_server', 'smtp_username', 'smtp_password', 'smtp_port', 'sendmail', 'siteadminemail', 'siteadmin') as $k)
		{
			if (!isset($overrides[$k])) $overrides[$k] = $pref[$k];
		}

		if(strpos($overrides['smtp_server'],':')!== false)
		{
			list($smtpServer,$smtpPort) = explode(":", $overrides['smtp_server']);
			$overrides['smtp_server'] = $smtpServer;
		}
		else
		{
			$smtpPort = varset($overrides['smtp_port'], 25);
		}


		$this->pause_amount = varset($pref['mail_pause'], 10);
		$this->pause_time =  varset($pref['mail_pausetime'], 1);
		$this->allow_html = varset($pref['mail_sendstyle'],'textonly') == 'texthtml' ? true : 1;

		if (vartrue($pref['mail_options'])) $this->general_opts = explode(',',$pref['mail_options'],'');
		
		if ($this->debug)
		{
			echo 'Mail_options: '.$pref['mail_options'].' Count: '.count($this->general_opts).'<br />';
		}
		
		foreach ($this->general_opts as $k => $v) 
		{
			$v = trim($v);
			$this->general_opts[$k] = $v;
			if (strpos($v,'hostname') === 0)
			{
				list(,$this->HostName) = explode('=',$v);
				if ($this->debug) echo "Host name set to: {$this->HostName}<br />";
			}
		}

		list($this->logEnable,$this->add_email) = explode(',',varset($pref['mail_log_options'],'0,0'));

		switch ($overrides['mailer'])
		{
			case 'smtp' :
				$smtp_options = array();
				$temp_opts = explode(',',varset($pref['smtp_options'],''));
				if (vartrue($overrides ['smtp_pop3auth'])) $temp_opts[] = 'pop3auth';		// Legacy option - remove later
				if (vartrue($pref['smtp_keepalive'])) $temp_opts[] = 'keepalive';	// Legacy option - remove later
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

				$this->isSMTP();			// Enable SMTP functions
				if (vartrue($smtp_options['helo'])) $this->Helo = $smtp_options['helo'];

				if (isset($smtp_options['pop3auth']))			// We've made sure this is set
				{	// Need POP-before-SMTP authorisation
					require_once(e_HANDLER.'phpmailer/class.pop3.php');
					$pop = new POP3();
					$pop->authorise($overrides['smtp_server'], 110, 30, $overrides['smtp_username'], $overrides['smtp_password'], 1);
				}

				$this->Mailer = 'smtp';
				$this->localUseVerp = isset($smtp_options['useVERP']);
				if (isset($smtp_options['secure']))
				{
					switch ($smtp_options['secure'])
					{
						case 'TLS' :
							$this->SMTPSecure = 'tls';
							$this->Port = ($smtpPort != 465) ? $smtpPort : 25;		// Can also use port 587, and maybe even 25
							break;
						case 'SSL' :
							$this->SMTPSecure = 'ssl';
							$this->Port = ($smtpPort != 587) ? $smtpPort : 465;
							break;
						default :
							if ($this->debug) echo "Invalid option: {$smtp_options['secure']}<br />";
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
				$this->Sendmail = ($overrides['sendmail']) ? $overrides['sendmail'] : '/usr/sbin/sendmail -t -i -r '.vartrue($pref['replyto_email'],$overrides['siteadminemail']);
				break;
			case 'php' :
				$this->Mailer = 'mail';
				break;
		}


		$this->FromName 	= $tp->toHTML(vartrue($pref['replyto_name'],$overrides['siteadmin']),'','RAWTEXT');
		$this->From 		= $tp->toHTML(vartrue($pref['replyto_email'],$overrides['siteadminemail']),'','RAWTEXT');
		$this->WordWrap 	= 76;			// Set a sensible default
		$this->Sender       = (!empty($pref['mail_bounce_email'])) ? $pref['mail_bounce_email'] : $this->From;

		$pref['mail_dkim'] = 1;

		$privatekeyfile = e_SYSTEM.'dkim_private.key';

		if($pref['mail_dkim'] && is_readable($privatekeyfile))
		{
			$this->DKIM_domain      = e_DOMAIN; // 'example.com';
			$this->DKIM_private     = $privatekeyfile;
			$this->DKIM_selector    = 'phpmailer';
			$this->DKIM_passphrase  = ''; //key is not encrypted
			$this->DKIM_identifier  = $this->From;
		}



		// Now look for any overrides - slightly cumbersome way of doing it, but does give control over what can be set from here
		// Options are those accepted by the arraySet() method.
		if(!empty($overrides))
		{
			foreach ($this->overrides as $key =>$opt)
			{
				if (isset($overrides[$key]))
				{
					$this->arraySet(array($opt => $overrides[$key]));
				}
				elseif(!empty($overrides[$opt]))
				{
					$this->arraySet(array($opt => $overrides[$opt]));		
				}
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
					$this->addAddress($adr, $to_name);
					break;
				case 'replyto' :
					$this->addReplyTo($adr, $to_name);
					break;
				case 'cc' :
					if($this->Mailer == 'mail')
					{
						$this->addCustomHeader('Cc: '.$adr);
					}
					else
					{
						$this->addCC($adr, $to_name);
					}
					break;
				case 'bcc' :
					if($this->Mailer == 'mail')
					{
						$this->addCustomHeader('Bcc: '.$adr);
					}
					else
					{
						$this->addBCC($adr, $to_name);
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
		
		$message = str_replace("\t", "", $message); // filter out tabs from templates; 

		if ($want_HTML !== FALSE)
		{
			// $message = e107::getParser()->toHtml("[html]".$message."[/html]",true); // using toHtml will break media attachment links. (need to retain {e_XXXX )

			if ($this->debug) echo "Generating multipart email<br />";
			if ($add_HTML_header)
			{
				$message = 	"<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n
				<html xmlns='http://www.w3.org/1999/xhtml' >\n".$message;
			}

			;
			//  !preg_match('/<(table|div|font|br|a|img|b)/i', $message)
			if ($this->legacyBody && e107::getParser()->isHtml($message) != true) // Assume html if it includes one of these tags
			{	// Otherwise assume its a plain text message which needs some conversion to render in HTML
			
				if($this->debug == true)
				{
					echo 'Running legacyBody mode<br />';	
				}
			
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
			if ($this->debug) echo "Generating plain text email<br />";
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
		$mes = e107::getMessage();

		foreach($attachments as $attach)
		{
			$tempName = basename($attach);
			if(is_readable($attach) && $tempName) // First parameter is complete path + filename; second parameter is 'name' of file to send
			{	
				if($this->previewMode === true)
				{
					$this->previewAttachments[] = array('file'=>$attach, 'status'=>true); 			
				}
				else 
				{
					$ext = pathinfo($attach, PATHINFO_EXTENSION);
					$this->addAttachment($attach, $tempName,'base64',$this->_mime_types($ext));	
				}
			
			}
			elseif($this->previewMode === true)
			{
				$this->previewAttachments[] = array('file'=>$attach, 'status'=>false); 			
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
				$this->addEmbeddedImage($inline_img, md5($inline_img), basename($inline_img),'base64',$this->_mime_types($ext));
			}
		}
	}

	/**
	 * Preview the BODY of an email
	 * @param $eml - array.
	 * @return string
	 */
	public function preview($eml)
	{
		$this->previewMode = true; 
		$mes = e107::getMessage();
		
		if (count($eml))
		{	
			if($error = $this->arraySet($eml))  // Set parameters from list
			{
				return $error;
			} 
			
		}	



		$text = $this->Body;

		if($eml['template'] == 'textonly')
		{
			$text = strip_tags($text);
		}

		if(!empty($this->previewAttachments))
		{
			$text .= "<hr />Attachments:";
			foreach($this->previewAttachments as $val)
			{
				$text .= "<div>".$val['file']." - ";
				$text .= ($val['status'] !== true) ? "Not Found" : "OK";
				$text .= "</div>";	
			}	
		}

		if($eml['template'] == 'texthtml' || $eml['template'] == 'textonly' )
		{
			$text = "<body style='background-color:#FFFFFF'>".$text."</body>";
		}

		return $text;
		
	}


	function processShortcodes($eml)
	{
		$tp = e107::getParser();

		$mediaParms = array();



		if(strpos($eml['templateHTML']['body'], '{MEDIA') !==false )
		{
			// check for media sizing.

			if(preg_match_all('/\{MEDIA([\d]): w=([\d]*)\}/', $eml['templateHTML']['body'], $match))
			{

				foreach($match[1] as $k=>$num)
				{
					//$key = $match[1][$k];
					$mediaParms[$num]['w'] = $match[2][$k];

				}
			}
		}

		if(!empty($eml['html']) || strip_tags($eml['template']) != $eml['template']) // HTML Email. 
		{
			$eml['shortcodes']['BODY'] 	= !empty($eml['body']) ? $eml['body'] : ''; // using toEmail() on html templates adds unnecessary <br /> to code. 
		}
		else // Plain Text Email. 
		{
			$eml['shortcodes']['BODY'] 	= !empty($eml['body']) ? $tp->toEmail($eml['body']) : '';	
		}
		
		$eml['shortcodes']['BODY'] 		= !empty($eml['body']) ? $eml['body'] : ''; // $tp->toEmail($eml['body']) : '';
		$eml['shortcodes']['SUBJECT'] 	= !empty($eml['subject']) ? $eml['subject'] : '';
		$eml['shortcodes']['THEME'] 	= ($this->previewMode == true) ? e_THEME_ABS.$this->pref['sitetheme'].'/' :  e_THEME.$this->pref['sitetheme'].'/'; // Always use front-end theme path. 



		if(!empty($eml['media']) && is_array($eml['media']))
		{
			foreach($eml['media'] as $k=>$val)
			{
				if(vartrue($val['path']))
				{
					$nk = ($k+1);
					$id = 'MEDIA'.$nk;
					
					if($tp->isVideo($val['path']))
					{
						$eml['shortcodes'][$id] = "<div class='media media-video'>".$tp->toVideo($val['path'],array('thumb'=>'email'))."</div>";
					}
					else
					{
						$size = isset($mediaParms[$nk]) ? "?w=".$mediaParms[$nk]['w'] : '';
						//echo $nk.": ".$val['path'].$size."<br />";
						$eml['shortcodes'][$id] = "<div class='media media-image'><img class='img-responsive img-fluid ".strtolower($id)."' src='".$val['path'].$size."' alt='' /></div>";
					}
					
				}	
			}
			
					
		}
		
		return $eml['shortcodes'];	
		
	}


	/**
	 *	Sets one or more parameters from an array. See @see{sendEmail()} for list of parameters
	 *	Where parameter not present, doesn't change it - so can repeatedly call this function for bulk mailing, or to build up the list
	 *	(Note that there is no requirement to use this method for everything; parameters can be set by mixing this method with individual setting)
	 *
	 *	@param array $eml - list of parameters to set/change. Key is parameter name. @see{sendEmail()} for list of parameters
	 *
	 *	@return int zero if no errors detected
	 */
	public function arraySet($eml)
	{
		$tp = e107::getParser();
		$tmpl = null;
		
		// Cleanup legacy key names. ie. remove 'email_' prefix. 		
		foreach($eml as $k=>$v)
		{
			if(substr($k,0,6) == 'email_')
			{
				$nkey = substr($k,6);
				$eml[$nkey] = $v;	
				unset($eml[$k]);
			}		
		}
		


		if(vartrue($eml['template'])) // @see e107_core/templates/email_template.php
		{		
					
			if($tmpl = e107::getCoreTemplate('email', $eml['template'], 'front', true))  //FIXME - Core template is failing with template 'notify'. Works with theme template. Issue with core template registry?
			{				
				$eml['templateHTML'] = $tmpl;
				$eml['shortcodes'] = $this->processShortcodes($eml);

				$emailBody = $tmpl['header']. str_replace('{BODY}', $eml['body'], $tmpl['body']) . $tmpl['footer'];
				
				$eml['body'] = $tp->parseTemplate($emailBody, true, $eml['shortcodes']);
				
			//	$eml['body'] = ($tp->toEmail($tmpl['header']). str_replace('{BODY}', $eml['body'], $tmpl['body']). $tp->toEmail($tmpl['footer']));
				
				if($this->debug)
				{
				//	echo "<h4>e107Email::arraySet() - line ".__LINE__."</h4>";

					var_dump($eml['shortcodes']);
					var_dump($this->Subject);
				//	print_a($tmpl);
				}
				
				unset($eml['add_html_header']); // disable other headers when template is used. 
				
				$this->Subject = $tp->parseTemplate($tmpl['subject'], true, varset($eml['shortcodes'],null));

				if($this->debug)
				{
					var_dump($this->Subject);
				}
			}
			else
			{
				if($this->debug)
				{
					echo "<h4>Couldn't find email template: ".$eml['template']."</h4>";	
				}
			//	$emailBody = $eml['body'];
				
				if (vartrue($eml['subject'])) $this->Subject = $tp->parseTemplate($eml['subject'], true, varset($eml['shortcodes'],null)); 	
				e107::getMessage()->addDebug("Couldn't find email template: ".$eml['template']);	
			}
			
		}
		else
		{
			if (vartrue($eml['subject'])) $this->Subject = $tp->parseTemplate($eml['subject'], true, varset($eml['shortcodes'],null)); 	
				//	$eml['body'] = ($tp->toEmail($tmpl['header']). str_replace('{BODY}', $eml['body'], $tmpl['body']). $tp->toEmail($tmpl['footer']));
		}

		$this->Subject = str_replace("&#039;", "'", $this->Subject);

		// Perform Override from template. 
		foreach($this->overrides as $k=>$v)
		{
			if(!empty($tmpl[$v]))
			{
				$eml[$v] = $tmpl[$v];	
			}	
		}


		$identifier = deftrue('MAIL_IDENTIFIER', 'X-e107-id');	

		if (isset($eml['SMTPDebug']))		{ $this->SMTPDebug = $eml['SMTPDebug'];	}		// 'FALSE' is a valid value!
		if (!empty($eml['sender_email']))	{ $this->From = $eml['sender_email']; }
		if (!empty($eml['sender_name']))	{ $this->FromName = $eml['sender_name']; }
		if (!empty($eml['replyto']))		{ $this->AddAddressList('replyto',$eml['replyto'],vartrue($eml['replytonames'],'')); }
		if (isset($eml['html']))			{ $this->allow_html = $eml['html'];	}				// 'FALSE' is a valid value!
		if (isset($eml['html_header']))		{ $this->add_HTML_header = $eml['html_header'];	}	// 'FALSE' is a valid value!
		if (!empty($eml['body']))			{ $this->makeBody($eml['body'], $this->allow_html, $this->add_HTML_header); }
		if (!empty($eml['attachment']))		{ $this->attach($eml['attachment']); }
		if (!empty($eml['cc'])) 			{ $this->AddAddressList('cc',$eml['cc'],vartrue($eml['cc_names'],'')); }
		if (!empty($eml['bcc'])) 			{ $this->AddAddressList('bcc',$eml['bcc'],vartrue($eml['bcc_names'],'')); }
		if (!empty($eml['returnreceipt']))	{ $this->ConfirmReadingTo = $eml['returnreceipt']; }
		if (!empty($eml['inline_images']))	{ $this->addInlineImages($eml['inline_images']); }
		if (!empty($eml['priority']))		{ $this->Priority = $eml['priority']; }
		if (!empty($eml['e107_header']))	{ $this->addCustomHeader($identifier.": {$eml['e107_header']}"); }
		if (!empty($eml['wordwrap']))		{ $this->WordWrap = $eml['wordwrap']; }
		if (!empty($eml['split'])) 			{ $this->SingleTo = ($eml['split'] != FALSE); }
		if (!empty($eml['smtp_username'])) 	{ $this->Username = $eml['smtp_username']; }
		if (!empty($eml['smtp_password'])) 	{ $this->Password = $eml['smtp_password']; }

		if (!empty($eml['bouncepath'])) 
		{
			$this->Sender = $eml['bouncepath'];				// Bounce path
			$this->save_bouncepath = $eml['bouncepath'];		// Bounce path
		}
			
		if (!empty($eml['extra_header'])) 
		{
			if (is_array($eml['extra_header']))
			{
				foreach($eml['extra_header'] as $eh)
				{
					$this->addCustomHeader($eh);
				}
			}
			else
			{
				$this->addCustomHeader($eml['extra_header']);
			}
		}

	//	print_a($eml);

		if($this->debug)
		{
		//	echo "<h4>e107Email::arraySet() - line ".__LINE__."</h4>";
			return 0;
		//	print_a($eml);
			//$this->PreSend(); 
			//$debugEml = $this->GetSentMIMEMessage().
			//print_a($debugEml);
		}	


		$this->logLine("ArraySet Data:".print_r($eml,true));

		return 0;				// No error
	}



	/**
		Send an email where the bulk of the data is passed in an array. Returns 0 on success.
		(Even if the array is null, because everything previously set up, this is the preferred entry point)
		Where parameter not present in the array, doesn't get changed - useful for bulk mailing
		If doing bulk mailing with repetitive calls, set $bulkmail parameter true, and must call allSent() when completed
		Some of these parameters have been made compatible with the array calculated by render_email() in signup.php
	 *
		Possible array parameters:


	 *	@param string $send_to - recipient email address
	 *	@param string $to_name - recipient name
	 *	@param array $eml - optional array of additional parameters (see BELOW)
	 *
	 *  @param string $eml['subject']           - Email Subject
	 * 	@param string $eml['sender_email']	    - 'From' email address
	 * 	@param string $eml['sender_name']		- 'From' name
	 * 	@param string $eml['replyto']			- Optional 'reply to' field
	 * 	@param string $eml['replytonames']	    - Name(s) corresponding to 'reply to' field  - only used if 'replyto' used
	 * 	@param bool $eml['send_html']		    - if TRUE, includes HTML part in messages (only those added after this flag)
	 * 	@param bool $eml['add_html_header']     - if TRUE, adds the 2-line DOCTYPE declaration to the front of the HTML part (but doesn't add <head>...</head>)
	 * 	@param string $eml['body']			    - message body. May be HTML or text. Added according to the current state of the HTML enable flag
	 * 	@param string|array $eml['attach']		- string if one file, array of filenames if one or more.
	 * 	@param string $eml['cc']			- comma-separated list of cc addresses.
	 * 	@param string $eml['cc_names']  		- comma-separated list of cc names. Optional, used only if $eml['cc'] specified
	 * 	@param string $eml['bcc']		    - comma-separated list
	 * 	@param string $eml['bcc_names'] 		- comma-separated list of bcc names. Optional, used only if $eml['bcc'] specified
	 * 	@param string $eml['bouncepath']		- Sender field (used for bounces)
	 * 	@param string $eml['returnreceipt']	    - email address for notification of receipt (reading)
	 * 	@param array $eml['inline_images']	    - array of files for inline images
	 * 	@param int $eml['priority']		        - Email priority (1 = High, 3 = Normal, 5 = low)
	 * 	@param string $eml['e107_header']		- Adds specific 'X-e107-id:' header
	 * 	@param string $eml['extra_header']	    - additional headers (format is name: value
	 * 	@param string $eml['wordwrap']		    - Set wordwrap value
	 * 	@param bool $eml['split']			    - If true, sends an individual email to each recipient
	 *  @param string $eml['template']		    - template to use. 'default'
	 *  @param array $eml['shortcodes']		    - array of shortcode values. eg. array('MY_SHORTCODE'=>'12345');
	 *
	 *  @param boolean $bulkmail - set TRUE if this email is one of a bulk send; FALSE if an isolated email
	 *	@return boolean|string - TRUE if success, error message if failure
	 */
	public function sendEmail($send_to, $to_name, $eml = array(), $bulkmail = false)
	{
		if (count($eml))
		{
			if($error = $this->arraySet($eml))  // Set parameters from list
			{
				return $error;
			} 

		}

		if (($bulkmail == true) && $this->localUseVerp && $this->save_bouncepath && (strpos($this->save_bouncepath,'@') !== false))
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

		if ($this->debug == false && (($this->logEnable == 0) || ($this->logEnable == 2)) )
		{
			// prevent user/script details being exposed in X-PHP-Script header 
			$oldphpself 					= $_SERVER['PHP_SELF']; 
			$oldremoteaddr					= $_SERVER['REMOTE_ADDR']; 
			$_SERVER['PHP_SELF'] 			= "/"; 
			$_SERVER['REMOTE_ADDR'] 		= $_SERVER['SERVER_ADDR']; 
			$_SERVER["HTTP_X_FORWARDED_FOR"] = $_SERVER['SERVER_ADDR'];
			$_SERVER["HTTP_CF_CONNECTING_IP"] = $_SERVER['SERVER_ADDR'];

			$result = $this->send();		// Actually send email

			
			$_SERVER['PHP_SELF'] = $oldphpself; 
			$_SERVER['REMOTE_ADDR'] = $oldremoteaddr;
			$_SERVER["HTTP_X_FORWARDED_FOR"] = $oldremoteaddr;
			$_SERVER["HTTP_CF_CONNECTING_IP"] = $oldremoteaddr;
			
			if (!$bulkmail && !$this->SMTPKeepAlive && ($this->Mailer == 'smtp')) $this->smtpClose();
		}
		else
		{	// Debug
			$result = true;
			echo "<h2>Subject: ".$this->Subject."</h2>";
		//	echo "<h2>SendEmail()->Body</h2>";
		//	print_a($this->Body);
		//	echo "<h2>SendEmail()->AltBody</h2>";
		//	print_a($this->AltBody);
			if (($this->logEnable == 3) && (($this->SendCount % 7) == 4)) $result = FALSE;			// Fail one email in 7 for testing
		}

		$this->TotalSent++;
		
		if (($bulkmail == true) && ($this->pause_amount > 0) && ($this->SendCount >= $this->pause_amount))
		{
			if ($this->SMTPKeepAlive && ($this->Mailer == 'smtp')) $this->smtpClose();
			sleep($this->pause_time);
			$this->SendCount = 0;
		}

		$this->logLine("Send to {$to_name} at {$send_to} Mail-ID={$this->MessageID} - ".($result ? 'Success' : 'Fail'));
		
		if(!$result)
		{
			$this->logLine(print_r($eml,true));	
			
			if(!empty($eml['SMTPDebug']))
			{
				e107::getMessage()->addError($this->ErrorInfo);
				$tmp = $this;
				$tmp->pref = array();
				e107::getMessage()->addDebug(print_a($tmp,true));
			}
		}


		$this->clearAddresses();			// In case we send another email
		$this->clearCustomHeaders();

		if ($result)
		{
			$this->closeLog();
			return TRUE;
		}

		$this->logLine('Error info: '.$this->ErrorInfo);
		// Error sending email


		e107::getLog()->e_log_event(3,debug_backtrace(),"MAIL","Send Failed",$this->ErrorInfo,FALSE,LOG_TO_ROLLING);
		$this->TotalErrors++;
		$this->closeLog();
		return $this->ErrorInfo;
	}


	function setDebug($val)
	{
		$this->debug = $val;
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
			$this->smtpClose();
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
	public function MsgHTML($message, $basedir = '', $advanced=false)
	{
		$tp = e107::getParser();

		$message = $tp->toEmail($message, false, 'rawtext');
				
		preg_match_all("/(src|background)=([\"\'])(.*)\\2/Ui", $message, $images);			// Modified to accept single quotes as well
		if(isset($images[3]) && ($this->previewMode === false)) 
		{
			
			if($this->debug)
			{
				echo "<h4>Detected Image Paths</h4>";
				print_a($images[3]);	
			}
			
			foreach($images[3] as $i => $url) 
			{
				
				// do not change urls for absolute images (thanks to corvuscorax)
				if (!preg_match('#^[A-z]+://#',$url)) 
				{
					$url = $tp->replaceConstants($url);

					$size = 'w=800';

					if(strpos($url, '?w=')!==false)
					{
						list($url,$size) = explode('?', $url);
					}

					// resize on the fly.
					if($this->debug)
					{
						echo "<br />Attempting Resize...".$url;

					}
					// e107::getMessage()->addInfo("Resizing: ".$url." to ".$size);
					if($resized = e107::getMedia()->resizeImage($url, e_TEMP.basename($url), $size))
					{
						$url = $resized;
					}
					elseif($this->debug)
					{
						echo "<br />Couldn't resize ".$url;
					}

					$delim = $images[2][$i];			// Will be single or double quote
					$filename = basename($url);
					$directory = dirname($url);
					if ($directory == '.') $directory='';
					if (strpos($directory, e_HTTP) === 0)
					{
						$directory = substr(SERVERBASE, 0, -1).$directory;		// Convert to absolute server reference
						$basedir = '';
					}
					
					if ($this->debug)
					{ 
						echo "<br />CID file {$filename} in {$directory}. Base = ".SERVERBASE."<br />BaseDir = {$basedir}<br />";
					}
					
					$cid = 'cid:' . md5($filename);
					$ext = pathinfo($filename, PATHINFO_EXTENSION);
					$mimeType  = self::_mime_types($ext);
					if ( (strlen($basedir) > 1) && (substr($basedir,-1) != '/') && (substr($basedir,-1) != '\\')) { $basedir .= '/'; }
					if ( strlen($directory) > 1 && substr($directory,-1) != '/' && substr($directory,-1) != '\\') { $directory .= '/'; }
					//echo "Add image: {$basedir}|{$directory}|{$filename}<br />";
					if ( $this->addEmbeddedImage($basedir.$directory.$filename, md5($filename), $filename, 'base64',$mimeType) ) 
					{
						// $images[1][$i] contains 'src' or 'background'
						$message = preg_replace("/".$images[1][$i]."=".$delim.preg_quote($images[3][$i], '/').$delim."/Ui", $images[1][$i]."=".$delim.$cid.$delim, $message);
					}
					else
					{
						if ($this->debug)
						{
							 echo "Add embedded image {$url} failed<br />";
							 echo "<br />basedir=".$basedir;
							 echo "<br />dir=".$directory;
							 echo "<br />file=".$filename;
							 echo "<br />";
						}
					}
				}
				elseif($this->debug)
				{
					echo "<br />Absolute Image: ".$url;	
					
				}
			}
		}

		if($this->previewMode === true)
		{
			$message = $tp->replaceConstants($message, 'abs');
		}


		$this->isHTML(true);
		$this->Body = $message;
		//print_a($message);
		$textMsg = str_replace("\n", "", $message);
		$textMsg = str_replace(array('<br />', '<br>'), "\n", $textMsg);		// Modified to make sure newlines carried through
		$textMsg = preg_replace('#^.*?<body.*?>#', '', $textMsg);		// Knock off everything up to and including the body statement (if present)
		$textMsg = preg_replace('#</body.*?>.*$#', '', $textMsg);		// Knock off everything after and including the </body> (if present)
		$textMsg = trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/s','',$textMsg)));

		if($this->debug)
		{
			echo "<h2>".__METHOD__.'  $textMsg<small> Line: '.__LINE__.'</small></h2>';
			print_a($textMsg);
		}

		if(!empty($textMsg)) // Always set it, even if AltBody is empty.
		{
			$this->AltBody = html_entity_decode($textMsg);

		}

		if(empty($this->AltBody))
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


// Called by PHPMailer when SMTP debug is active. 
function handlePHPMailerDebug($str,$other)
{
	$text = print_a($str,true);
	e107::getMessage()->addInfo($text);
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

		e107::getLog()->e_log_event(10,
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
	
	$identifier = deftrue('MAIL_IDENTIFIER', 'X-e107-id');

	if (vartrue($mailheader_e107id)) $mail->addCustomHeader($identifier.": {$mailheader_e107id}");

	$mail->legacyBody = TRUE;				// Need to handle plain text email conversion to HTML
	$mail->makeBody($message);				// Add body, with conversion if required

	if($Cc) $mail->AddAddressList('cc', $Cc);

	if ($Bcc) $mail->AddAddressList('bcc', $Bcc);

	if (trim($send_from))
	{
		$mail->setFrom($send_from, $from_name);				// These have already been defaulted to sitewide options, so no need to set again if blank
	}

	$mail->Subject = $subject;

	$mail->attach($attachments);

	// Add embedded images (should be auto-handled now)
	if ($inline) $mail->addInlineImages($inline);

	// Passed parameter overrides any system default for bounce - but should this be 'ReplyTo' address instead?
	//  if (vartrue($returnpath)) $mail->Sender = $AddReplyToAddresses($returnpath,'');
	if (vartrue($returnpath)) $mail->Sender = $returnpath;

	if (vartrue($returnreceipt)) $mail->ConfirmReadingTo($returnreceipt);

	if ($mail->sendEmail($send_to,$to_name) === TRUE) 
	{	// Success
		return TRUE;
	}

	// Error info already logged
	return FALSE;
}



?>
