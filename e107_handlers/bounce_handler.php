#!/usr/bin/php -q
<?php


	// WARNING, any echoed output from this script will be returned to the sender as a bounce message.

	$_E107['debug'] = true;

	if(!defined('e107_INIT'))
	{
		$_E107['cli'] = true;
		$_E107['debug'] = false;
		$_E107['no_online'] = true;
		$_E107['no_forceuserupdate'] = true;
		$_E107['no_menus'] = true;
		$_E107['allow_guest'] = true; // allow crons to run while in members-only mode.
		$_E107['no_maintenance'] = true;

		$class2 = realpath(dirname(__FILE__) . "/../") . "/class2.php";

		@require_once($class2);
	}

	if(empty($_E107['phpunit']))
	{
		$bnc = new e107Bounce;
		$bnc->process();
	}


	class e107Bounce
	{

		private $debug  = false;
		private $source = false;

		function __construct()
		{

/*			if(ADMIN && vartrue($_GET['eml']))
			{
				$this->debug = 2; // mode2  - via browser for admin.
				$this->source = $_GET['eml'] . ".eml";
			}*/
		}

		public function setSource($source)
		{
			$this->source = $source;
		}

		/**
		 * @param bool $sendEmail true | false
		 * @return mixed|null
		 */
		public function process($sendEmail=true)
		{

			$pref = e107::getPref();
			$e107_userid = null;

			e107::getCache()->CachePageMD5 = '_';
			e107::getCache()->set('emailLastBounce', time(), true, false, true);

			$strEmail = ($this->source === false) ? $this->mailRead(-1) : file_get_contents($this->source);

			file_put_contents(e_LOG . "bounce.log", date('r') . "\n\n" . $strEmail . "\n\n", FILE_APPEND);


			if(strpos($strEmail, 'X-Bounce-Test: true') !== false) // Bounce Test from Admin Area.
			{
				$this->debug = true;    // mode 1 - for email test.
			}

			if(empty($strEmail)) // Failed.
			{
				if($this->debug === true && !empty($this->source))
				{
					$message =  "Couldn't get email data";
				}
				else
				{
				//	$message = "Failed: Unable to read email!";
					return false;
				}

			}
			else
			{
			//	$multiArray = BounceHandler::get_the_facts($strEmail);
				$head = BounceHandler::parse_head($strEmail);
				$message = null;
				$identifier = deftrue('MAIL_IDENTIFIER', 'X-e107-id');
				$e107_userid = (isset($head[$identifier])) ? $head[$identifier] : $this->getHeader($strEmail, $identifier);
			}


			if($this->debug === true) // admin is sending a bounce-handler test email.
			{
				require_once(e_HANDLER . "mail.php");
				$message = "Your Bounce Handler is working. The data of the email you sent is displayed below.<br />";

				if(!empty($e107_userid))
				{
					$message .= "A user-id was detected in the email you sent: <b>" . $e107_userid . "</b><br />";
				}

				//	$message .= "<br /><h4>Head</h4>";
				//	$message .= print_a($head,true);
				//	$message .= "<h4>Emails Found</h4><pre>".print_r($multiArray,TRUE). "</pre>";

				$message .= "<pre>" . $strEmail . "</pre>";
				
			}


			if(!empty($e107_userid))
			{
				if($errors = $this->setUser_Bounced($e107_userid))
				{
					if($this->debug === 2)
					{
						echo "<h3>Errors</h3>";
						print_a($errors);
					}

				}

			}

			if(!empty($message) && ($sendEmail === true))
			{

				$eml = array(
					'subject'      => "Bounce-Handler : ",
					'sender_email' => $pref['siteadminemail'],
					'sender_name'  => $pref['siteadmin'],
					//	'replyto'		=> $email,
					'html'         => true,
					'template'     => 'default',
					'body'         => $message
				);


				e107::getEmail()->sendEmail($pref['siteadminemail'], SITENAME . " :: Bounce-Handler.", $eml);
			}


			return $e107_userid;


			/*		echo "<pre>";
					print_r($multiArray);
					echo "</pre>";
			*/


			/*foreach($multiArray as $the)
			{
				$the['user_id'] = $head[$identifier];
				$the['user_email'] = $the['recipient'];
				unset($the['recipient']);

				switch($the['action'])
				{
					case 'failed':
						e107::getEvent()->trigger('email_bounce_failed', $the);
						$this->setUser_Bounced(null, $the['user_email']);
						break;

					case 'transient':

						//    $num_attempts  = delivery_attempts($the['user_email']);
						e107::getEvent()->trigger('email_bounce_transient', $the);
						if($num_attempts > 10)
						{
							$this->setUser_Bounced($the['user_id'], $the['user_email']);
						}
						else
						{
							//       insert_into_queue($the['user_email'], ($num_attempts+1));
						}
						break;

					case 'autoreply':
						e107::getEvent()->trigger('email_bounce_autoreply', $the);
						//  postpone($the['user_email'], '7 days');
						break;

					default:
						//don't do anything
						break;
				}
			}
			*/
		}


		function getHeader($message, $id = 'X-e107-id')
		{

			$tmp = explode("\n", $message);
			foreach($tmp as $val)
			{
				if(strpos($val, $id . ":") !== false)
				{
					return str_replace($id . ":", "", $val);
				}
			}

			return null;
		}


		function setUser_Bounced($bounceString = '', $email = '')
		{

			if(!$email && !$bounceString)
			{
				return false;
			}
			//	echo "Email bounced ID: ".$id_or_email;

			$mailManager = e107::getBulkEmail();

			$debug = ($this->debug === 2) ? true : false;

			$mailManager->controlDebug($debug);

			if($errors = $mailManager->markBounce($bounceString, $email))
			{
				return $errors;  // Failure
			}

			return false;
		}


		/**
		 * Read the Mail.
		 *
		 * @param int $iKlimit [optional]
		 * @return string mail message
		 */
		function mailRead($iKlimit = 4096)
		{

			$fp = fopen("php://stdin", "r");

			if(!$fp)
			{
				$pref = e107::getPref();

				$eml = array(
					'subject'      => "Bounce-Handler-Error :",
					'sender_email' => $pref['siteadminemail'],
					'sender_name'  => $pref['siteadmin'],
					//	'replyto'		=> $email,
					'html'         => true,
					'template'     => 'default',
					'body'         => "Error - failed to read mail from STDIN! : " . __FILE__ . " (" . __LINE__ . ")"
				);

				e107::getEmail()->sendEmail($pref['siteadminemail'], SITENAME . " :: Bounce-Handler.", $eml);
				exit();
			}

			// Create empty string for storing message
			$sEmail = "";
			$i_limit = 0;

			if($iKlimit == -1)
			{
				while(!feof($fp))
				{
					$sEmail .= fread($fp, 1024);
				}
			}
			else
			{
				while(!feof($fp) && $i_limit < $iKlimit)
				{
					$sEmail .= fread($fp, 1024);
					$i_limit++;
				}
			}

			fclose($fp);

			return $sEmail;
		}
	}


	//error_reporting(E_ALL);

	/* BOUNCE HANDLER Class, Version 5.1
	 * Description: "chops up the bounce into associative arrays"
	 *     ~ http://www.phpclasses.org/browse/file/11665.html
	 */

	/* Debugging / Contributers:
		* "Kanon"
		* Jamie McClelland http://mayfirst.org
		* Michael Cooper
		* Thomas Seifert
		* Tim Petrowsky http://neuecouch.de
		* Willy T. Koch http://apeland.no
	*/


	/*
	 The BSD License
	 Copyright (c) 2006, Chris Fortune http://cfortune.kics.bc.ca
	 All rights reserved.

	Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

		* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
		* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
		* Neither the name of the BounceHandler nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

	THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
	*/


	class BounceHandler
	{


		// this is the most commonly used public method
		// quick and dirty
		// useage: $multiArray = BounceHandler::get_the_facts($strEmail);
		static function get_the_facts($eml)
		{

			// fluff up the email
			$bounce = self::init_bouncehandler($eml);
			list($head, $body) = preg_split("/\r\n\r\n/", $bounce, 2);
			$head_hash = self::parse_head($head);

			// initialize output variable
			$output[0]['recipient'] = "";
			$output[0]['status'] = "";
			$output[0]['action'] = "";

			// sanity check.
			if(!self::is_a_bounce($head_hash))
			{
				return $output;
			}

			// parse the email into data structures
			$boundary = $head_hash['Content-type']['boundary'];
			$mime_sections = self::parse_body_into_mime_sections($body, $boundary);
			$arrBody = explode("\r\n", $body);

			// now we try all our weird text parsing methods
			if(preg_match("/auto.{0,20}reply|vacation|(out|away|on holiday).*office/i", $head_hash['Subject']))
			{
				// looks like a vacation autoreply, ignoring

				$output[0]['action'] = 'autoreply';
			}
			elseif(self::is_RFC1892_multipart_report($head_hash) === true)
			{
				$rpt_hash = self::parse_machine_parsable_body_part($mime_sections['machine_parsable_body_part']);
				for($i = 0; $i < count($rpt_hash['per_recipient']); $i++)
				{
					$output[$i]['recipient'] = self::get_recipient($rpt_hash['per_recipient'][$i]);
					$output[$i]['status'] = $rpt_hash['per_recipient'][$i]['Status'];
					$output[$i]['action'] = $rpt_hash['per_recipient'][$i]['Action'];
				}
			}
			elseif(isset($head_hash['X-failed-recipients']))
			{
				//  Busted Exim MTA
				//  Up to 50 email addresses can be listed on each header.
				//  There can be multiple X-Failed-Recipients: headers. - (not supported)
				$arrFailed = explode(',', $head_hash['X-failed-recipients']);
				for($j = 0; $j < count($arrFailed); $j++)
				{
					$output[$j]['recipient'] = trim($arrFailed[$j]);
					$output[$j]['status'] = self::get_status_code_from_text($output[$j]['recipient'], $arrBody, 0);
					$output[$j]['action'] = self::get_action_from_status_code($output[$j]['status']);
				}
			}
			elseif(!empty($boundary) && self::is_a_bounce($head_hash))
			{
				// oh god it could be anything, but at least it has mime parts, so let's try anyway
				$arrFailed = self::find_email_addresses($mime_sections['first_body_part']);
				for($j = 0; $j < count($arrFailed); $j++)
				{
					$output[$j]['recipient'] = trim($arrFailed[$j]);
					$output[$j]['status'] = self::get_status_code_from_text($output[$j]['recipient'], $arrBody, 0);
					$output[$j]['action'] = self::get_action_from_status_code($output[$j]['status']);
				}
			}
			elseif(self::is_a_bounce($head_hash))
			{
				// last ditch attempt
				// could possibly produce erroneous output, or be very resource consuming,
				// so be careful.  You should comment out this section if you are very concerned
				// about 100% accuracy or if you want very fast performance.
				// Leave it turned on if you know that all messages to be analyzed are bounces.
				$arrFailed = self::find_email_addresses($body);
				for($j = 0; $j < count($arrFailed); $j++)
				{
					$output[$j]['recipient'] = trim($arrFailed[$j]);
					$output[$j]['status'] = self::get_status_code_from_text($output[$j]['recipient'], $arrBody, 0);
					$output[$j]['action'] = self::get_action_from_status_code($output[$j]['status']);
				}
			}

			// else if()..... add a parser for your busted-ass MTA here
			return $output;
		}

		// general purpose recursive heuristic function
		// to try to extract useful info from the bounces produced by busted MTAs
		static function get_status_code_from_text($recipient, $arrBody, $index)
		{

			for($i = $index; $i < count($arrBody); $i++)
			{
				$line = trim($arrBody[$i]);

				/******** recurse into the email if you find the recipient ********/
				if(stristr($line, $recipient) !== false)
				{
					// the status code MIGHT be in the next few lines after the recipient line,
					// depending on the message from the foreign host... What a laugh riot!
					$output = self::get_status_code_from_text($recipient, $arrBody, $i + 1);
					if($output)
					{
						return $output;
					}

				}

				/******** exit conditions ********/
				// if it's the end of the human readable part in this stupid bounce
				if(stristr($line, '------ This is a copy of the message') !== false)
				{
					return '';
				}
				//if we see an email address other than our current recipient's,
				if(count(self::find_email_addresses($line)) >= 1
					&& stristr($line, $recipient) === false
					&& strstr($line, 'FROM:<') === false)
				{ // Kanon added this line because Hotmail puts the e-mail address too soon and there actually is error message stuff after it.
					return '';
				}
				/******** pattern matching ********/
				if(stristr($line, 'no such address') !== false
					|| stristr($line, 'Recipient address rejected') !== false
					|| stristr($line, 'User unknown in virtual alias table') !== false)
				{
					return '5.1.1';
				}
				elseif(stristr($line, 'unrouteable mail domain') !== false
					|| stristr($line, 'Esta casilla ha expirado por falta de uso') !== false)
				{
					return '5.1.2';
				}
				elseif(stristr($line, 'mailbox is full') !== false
					|| stristr($line, 'Mailbox quota usage exceeded') !== false
					|| stristr($line, 'User mailbox exceeds allowed size') !== false)
				{
					return '4.2.2';
				}
				elseif(stristr($line, 'not yet been delivered') !== false)
				{
					return '4.2.0';
				}
				elseif(stristr($line, 'mailbox unavailable') !== false)
				{
					return '5.2.0';
				}
				elseif(stristr($line, 'Unrouteable address') !== false)
				{
					return '5.4.4';
				}
				elseif(stristr($line, 'retry timeout exceeded') !== false)
				{
					return '4.4.7';
				}
				elseif(stristr($line, 'The account or domain may not exist, they may be blacklisted, or missing the proper dns entries.') !== false)
				{ // Kanon added
					return '5.2.0'; // I guess.... seems like 5.1.1, 5.1.2, or 5.4.4 would fit too, but 5.2.0 seemed most generic
				}
				elseif(stristr($line, '554 TRANSACTION FAILED') !== false)
				{ // Kanon added
					return '5.5.4'; // I think this should be 5.7.1. "SMTP error from remote mail server after end of data: ... (HVU:B1) http://postmaster.info.aol.com/errors/554hvub1.html" -- AOL rejects messages that have links to certain sites in them.
				}
				elseif(stristr($line, 'Status: 4.4.1') !== false
					|| stristr($line, 'delivery temporarily suspended') !== false)
				{ // Kanon added
					return '4.4.1';
				}
				elseif(stristr($line, '550 OU-002') !== false
					|| stristr($line, 'Mail rejected by Windows Live Hotmail for policy reasons') !== false)
				{ // Kanon added
					return '5.5.0'; // Again, why isn't this 5.7.1 instead?
				}
				elseif(stristr($line, 'PERM_FAILURE: DNS Error: Domain name not found') !== false)
				{ // Kanon added
					return '5.1.2'; // Not sure if this is right code. Just copied from above.
				}
				elseif(stristr($line, 'Delivery attempts will continue to be made for') !== false)
				{ // Kanon added. From Symantec_AntiVirus_for_SMTP_Gateways@uqam.ca
					return '4.2.0'; // I'm not sure why Symantec delayed this message, but x.2.x means something to do with the mailbox, which seemed appropriate. x.5.x (protocol) or x.7.x (security) also seem possibly appropriate. It seems a lot of times it's x.5.x when it seems to me it should be x.7.x, so maybe x.5.x is standard when mail is rejected due to spam-like characteristics instead of x.7.x like I think it should be.
				}
				elseif(stristr($line, '554 delivery error:') !== false)
				{
					return '5.5.4'; // rogers.com
				}
				elseif(strstr($line, '550-5.1.1') !== false
					|| stristr($line, 'This Gmail user does not exist.') !== false)
				{ // Kanon added
					return '5.1.1'; // Or should it be 5.5.0?
				}


				// end strstr tests


				// rfc1893 return code
				if(preg_match('/([245]\.[01234567]\.[012345678])/', $line, $matches))
				{
					$mycode = str_replace('.', '', $matches[1]);
					$mycode = self::format_status_code($mycode);

					return implode('.', $mycode['code']);
				}

				// search for RFC821 return code
				// thanks to mark.tolman@gmail.com
				// Maybe at some point it should have it's own place within the main parsing scheme (at line 88)
				if(preg_match('/\]?: ([45][01257][012345]) /', $line, $matches)
					|| preg_match('/^([45][01257][012345]) (?:.*?)(?:denied|inactive|deactivated|rejected|disabled|unknown|no such|not (?:our|activated|a valid))+/i', $line, $matches))
				{
					$mycode = $matches[1];
					// map common codes to new rfc values
					if($mycode == '450' || $mycode == '550' || $mycode == '551' || $mycode == '554')
					{
						$mycode = '511';
					}
					elseif($mycode == '452' || $mycode == '552')
					{
						$mycode = '422';
					}
					elseif($mycode == '421')
					{
						$mycode = '432';
					}
					$mycode = self::format_status_code($mycode);

					return implode('.', $mycode['code']);
				}

			}

			return '';
		}

		static function init_bouncehandler($blob, $format = 'string')
		{
			$strEmail = "";

			if($format == 'xml_array')
			{

				//$out = "";
				for($i = 0; $i < $blob; $i++)
				{
					$out = preg_replace("/<HEADER>/i", "", $blob[$i]);
					$out = preg_replace("/</HEADER>/i", "", $out);
					$out = preg_replace("/<MESSAGE>/i", "", $out);
					$out = preg_replace("/</MESSAGE>/i", "", $out);
					$out = rtrim($out) . "\r\n";
					$strEmail .= $out;
				}
			}
			elseif($format == 'string')
			{
				$strEmail = str_replace("\r\n", "\n", $blob);
				$strEmail = str_replace("\n", "\r\n", $strEmail);
			}
			elseif($format == 'array')
			{
				$strEmail = "";
				for($i = 0; $i < $blob; $i++)
				{
					$strEmail .= rtrim($blob[$i]) . "\r\n";
				}
			}

			return $strEmail;
		}

		static function is_RFC1892_multipart_report($head_hash)
		{

			return $head_hash['Content-type']['type'] == 'multipart/report'
				&& $head_hash['Content-type']['report-type'] == 'delivery-status'
				&& $head_hash['Content-type']['boundary'] !== '';
		}

		static function parse_head($headers)
		{

			if(!is_array($headers))
			{
				$headers = explode("\r\n", $headers);
			}
			$hash = self::standard_parser($headers);
			// get a little more complex
			$arrRec = explode('|', $hash['Received']);
			$hash['Received'] = $arrRec;
			if($hash['Content-type'])
			{//preg_match('/Multipart\/Report/i', $hash['Content-type'])){
				$multipart_report = explode(';', $hash['Content-type']);
				$hash['Content-type'] = array();
				$hash['Content-type']['type'] = strtolower($multipart_report[0]);
				foreach($multipart_report as $mr)
				{
					if(preg_match('/([^=.]*?)=(.*)/i', $mr, $matches))
					{
						// didn't work when the content-type boundary ID contained an equal sign,
						// that exists in bounces from many Exchange servers
						//if(preg_match('/([a-z]*)=(.*)?/i', $mr, $matches)){
						$hash['Content-type'][strtolower(trim($matches[1]))] = str_replace('"', '', $matches[2]);
					}
				}
			}

			return $hash;
		}

		static function parse_body_into_mime_sections($body, $boundary)
		{

			if(!$boundary)
			{
				return array();
			}
			if(is_array($body))
			{
				$body = implode("\r\n", $body);
			}
			$body = explode($boundary, $body);
			$mime_sections['first_body_part'] = $body[1];
			$mime_sections['machine_parsable_body_part'] = $body[2];
			$mime_sections['returned_message_body_part'] = $body[3];

			return $mime_sections;
		}


		static function standard_parser($content)
		{ // associative array orstr
			// receives email head as array of lines
			// simple parse (Entity: value\n)
			$entity = "";

			$hash = array();

			if(!is_array($content))
			{
				$content = explode("\r\n", $content);
			}
			foreach($content as $line)
			{
				if(preg_match('/([^\s.]*):\s(.*)/', $line, $array))
				{
					$entity = ucfirst(strtolower($array[1]));
					if(empty($hash[$entity]))
					{
						$hash[$entity] = trim($array[2]);
					}
					elseif($hash['Received'])
					{
						// grab extra Received headers :(
						// pile it on with pipe delimiters,
						// oh well, SMTP is broken in this way
						if($entity and $array[2] and $array[2] != $hash[$entity])
						{
							$hash[$entity] .= "|" . trim($array[2]);
						}
					}
				}
				else
				{
					if($entity)
					{
						$hash[$entity] .= " $line";
					}
				}
			}

			return $hash;
		}

		static function parse_machine_parsable_body_part($str)
		{

			//Per-Message DSN fields
			$hash = self::parse_dsn_fields($str);
			$hash['mime_header'] = self::standard_parser($hash['mime_header']);
			$hash['per_message'] = self::standard_parser($hash['per_message']);
			if(isset($hash['per_message']['X-postfix-sender']) && $hash['per_message']['X-postfix-sender'])
			{
				$arr = explode(';', $hash['per_message']['X-postfix-sender']);
				$hash['per_message']['X-postfix-sender'] = '';
				$hash['per_message']['X-postfix-sender']['type'] = trim($arr[0]);
				$hash['per_message']['X-postfix-sender']['addr'] = trim($arr[1]);
			}
			if($hash['per_message']['Reporting-mta'])
			{
				$arr = explode(';', $hash['per_message']['Reporting-mta']);
				$hash['per_message']['Reporting-mta'] = '';
				$hash['per_message']['Reporting-mta']['type'] = trim($arr[0]);
				$hash['per_message']['Reporting-mta']['addr'] = trim($arr[1]);
			}
			//Per-Recipient DSN fields
			for($i = 0; $i < count($hash['per_recipient']); $i++)
			{
				$temp = self::standard_parser(explode("\r\n", $hash['per_recipient'][$i]));
				$arr = explode(';', $temp['Final-recipient']);
				$temp['Final-recipient'] = array();
				$temp['Final-recipient']['type'] = trim($arr[0]);
				$temp['Final-recipient']['addr'] = trim($arr[1]);
				$arr = explode(';', $temp['Original-recipient']);
				$temp['Original-recipient'] = array();
				$temp['Original-recipient']['type'] = trim($arr[0]);
				$temp['Original-recipient']['addr'] = trim($arr[1]);
				$arr = explode(';', $temp['Diagnostic-code']);
				$temp['Diagnostic-code'] = array();
				$temp['Diagnostic-code']['type'] = trim($arr[0]);
				$temp['Diagnostic-code']['text'] = trim($arr[1]);
				// now this is wierd: plenty of times you see the status code is a permanent failure,
				// but the diagnostic code is a temporary failure.  So we will assert the most general
				// temporary failure in this case.
			//	$ddc = '';
			//	$judgement = '';
				$ddc = self::decode_diagnostic_code($temp['Diagnostic-code']['text']);
				$judgement = self::get_action_from_status_code($ddc);

				if($judgement == 'transient')
				{
					if(stristr($temp['Action'], 'failed') !== false)
					{
						$temp['Action'] = 'transient';
						$temp['Status'] = '4.3.0';
					}
				}

				$hash['per_recipient'][$i] = '';
				$hash['per_recipient'][$i] = $temp;
			}

			return $hash;
		}

		static function get_head_from_returned_message_body_part($mime_sections)
		{

			$temp = explode("\r\n\r\n", $mime_sections['returned_message_body_part']);
			$head = self::standard_parser($temp[1]);
			$head['From'] = self::extract_address($head['From']);
			$head['To'] = self::extract_address($head['To']);

			return $head;
		}

		static function extract_address($str)
		{

			$from = '';

			$from_stuff = preg_split('/[ \"\'\<\>:\(\)\[\]]/', $str);
			foreach($from_stuff as $things)
			{
				if(strpos($things, '@') !== false)
				{
					$from = $things;
				}
			}

			return $from;
		}

		static function get_recipient($per_rcpt)
		{

			$recipient = '';

			if($per_rcpt['Original-recipient']['addr'] !== '')
			{
				$recipient = $per_rcpt['Original-recipient']['addr'];
			}
			elseif($per_rcpt['Final-recipient']['addr'] !== '')
			{
				$recipient = $per_rcpt['Final-recipient']['addr'];
			}
			$recipient = str_replace('<', '', $recipient);
			$recipient = str_replace('>', '', $recipient);

			return $recipient;
		}

		static function parse_dsn_fields($dsn_fields)
		{

			if(!is_array($dsn_fields))
			{
				$dsn_fields = explode("\r\n\r\n", $dsn_fields);
			}
			$j = 0;
			reset($dsn_fields);

			$hash = array();

			for($i = 0; $i < count($dsn_fields); $i++)
			{
				if($i == 0)
				{
					$hash['mime_header'] = $dsn_fields[0];
				}
				elseif($i == 1 && !preg_match('/(Final|Original)-Recipient/', $dsn_fields[1]))
				{
					// some mta's don't output the per_message part, which means
					// the second element in the array should really be
					// per_recipient - test with Final-Recipient - which should always
					// indicate that the part is a per_recipient part
					$hash['per_message'] = $dsn_fields[1];
				}
				else
				{
					if($dsn_fields[$i] == '--')
					{
						continue;
					}
					$hash['per_recipient'][$j] = $dsn_fields[$i];
					$j++;
				}
			}

			return $hash;
		}

		static function format_status_code($code)
		{

			$ret = array();

			if(preg_match('/([245]\.[01234567]\.[012345678])(.*)/', $code, $matches))
			{
				$ret['code'] = $matches[1];
				$ret['text'] = $matches[2];
			}
			elseif(preg_match('/([245][01234567][012345678])(.*)/', $code, $matches))
			{
				preg_match_all("/./", $matches[1], $out);
				$ret['code'] = $out[0];
				$ret['text'] = $matches[2];
			}

			return $ret;
		}

		static function fetch_status_messages($code)
		{

			$status_code_classes = array();
			$status_code_subclasses = array();


			$status_code_classes['2']['title'] = "Success";
			$status_code_classes['2']['descr'] = "Success specifies that the DSN is reporting a positive delivery action.  Detail sub-codes may provide notification of transformations required for delivery.";

			$status_code_classes['4']['title'] = "Persistent Transient Failure";
			$status_code_classes['4']['descr'] = "A persistent transient failure is one in which the message as sent is valid, but some temporary event prevents the successful sending of the message.  Sending in the future may be successful.";

			$status_code_classes['5']['title'] = "Permanent Failure";
			$status_code_classes['5']['descr'] = "A permanent failure is one which is not likely to be resolved by resending the message in the current form.  Some change to the message or the destination must be made for successful delivery.";

			$status_code_subclasses['0.0']['title'] = "Other undefined Status";
			$status_code_subclasses['0.0']['descr'] = "Other undefined status is the only undefined error code. It should be used for all errors for which only the class of the error is known.";

			$status_code_subclasses['1.0']['title'] = "Other address status";
			$status_code_subclasses['0.0']['descr'] = "Something about the address specified in the message caused this DSN.";

			$status_code_subclasses['1.1']['title'] = "Bad destination mailbox address";
			$status_code_subclasses['1.1']['descr'] = "The mailbox specified in the address does not exist.  For Internet mail names, this means the address portion to the left of the @ sign is invalid.  This code is only useful for permanent failures.";

			$status_code_subclasses['1.2']['title'] = "Bad destination system address";
			$status_code_subclasses['1.2']['descr'] = "The destination system specified in the address does not exist or is incapable of accepting mail.  For Internet mail names, this means the address portion to the right of the @ is invalid for mail.  This codes is only useful for permanent failures.";

			$status_code_subclasses['1.3']['title'] = "Bad destination mailbox address syntax";
			$status_code_subclasses['1.3']['descr'] = "The destination address was syntactically invalid.  This can apply to any field in the address.  This code is only useful for permanent failures.";

			$status_code_subclasses['1.4']['title'] = "Destination mailbox address ambiguous";
			$status_code_subclasses['1.4']['descr'] = "The mailbox address as specified matches one or more recipients on the destination system.  This may result if a heuristic address mapping algorithm is used to map the specified address to a local mailbox name.";

			$status_code_subclasses['1.5']['title'] = "Destination address valid";
			$status_code_subclasses['1.5']['descr'] = "This mailbox address as specified was valid.  This status code should be used for positive delivery reports.";

			$status_code_subclasses['1.6']['title'] = "Destination mailbox has moved, No forwarding address";
			$status_code_subclasses['1.6']['descr'] = "The mailbox address provided was at one time valid, but mail is no longer being accepted for that address.  This code is only useful for permanent failures.";

			$status_code_subclasses['1.7']['title'] = "Bad sender's mailbox address syntax";
			$status_code_subclasses['1.7']['descr'] = "The sender's address was syntactically invalid.  This can apply to any field in the address.";

			$status_code_subclasses['1.8']['title'] = "Bad sender's system address";
			$status_code_subclasses['1.8']['descr'] = "The sender's system specified in the address does not exist or is incapable of accepting return mail.  For domain names, this means the address portion to the right of the @ is invalid for mail. ";

			$status_code_subclasses['2.0']['title'] = "Other or undefined mailbox status";
			$status_code_subclasses['2.0']['descr'] = "The mailbox exists, but something about the destination mailbox has caused the sending of this DSN.";

			$status_code_subclasses['2.1']['title'] = "Mailbox disabled, not accepting messages";
			$status_code_subclasses['2.1']['descr'] = "The mailbox exists, but is not accepting messages.  This may be a permanent error if the mailbox will never be re-enabled or a transient error if the mailbox is only temporarily disabled.";

			$status_code_subclasses['2.2']['title'] = "Mailbox full";
			$status_code_subclasses['2.2']['descr'] = "The mailbox is full because the user has exceeded a per-mailbox administrative quota or physical capacity.  The general semantics implies that the recipient can delete messages to make more space available.  This code should be used as a persistent transient failure.";

			$status_code_subclasses['2.3']['title'] = "Message length exceeds administrative limit";
			$status_code_subclasses['2.2']['descr'] = "A per-mailbox administrative message length limit has been exceeded.  This status code should be used when the per-mailbox message length limit is less than the general system limit.  This code should be used as a permanent failure.";

			$status_code_subclasses['2.4']['title'] = "Mailing list expansion problem";
			$status_code_subclasses['2.3']['descr'] = "The mailbox is a mailing list address and the mailing list was unable to be expanded.  This code may represent a permanent failure or a persistent transient failure. ";

			$status_code_subclasses['3.0']['title'] = "Other or undefined mail system status";
			$status_code_subclasses['3.0']['descr'] = "The destination system exists and normally accepts mail, but something about the system has caused the generation of this DSN.";

			$status_code_subclasses['3.1']['title'] = "Mail system full";
			$status_code_subclasses['3.1']['descr'] = "Mail system storage has been exceeded.  The general semantics imply that the individual recipient may not be able to delete material to make room for additional messages.  This is useful only as a persistent transient error.";

			$status_code_subclasses['3.2']['title'] = "System not accepting network messages";
			$status_code_subclasses['3.2']['descr'] = "The host on which the mailbox is resident is not accepting messages.  Examples of such conditions include an immanent shutdown, excessive load, or system maintenance.  This is useful for both permanent and permanent transient errors. ";

			$status_code_subclasses['3.3']['title'] = "System not capable of selected features";
			$status_code_subclasses['3.3']['descr'] = "Selected features specified for the message are not supported by the destination system.  This can occur in gateways when features from one domain cannot be mapped onto the supported feature in another.";

			$status_code_subclasses['3.4']['title'] = "Message too big for system";
			$status_code_subclasses['3.4']['descr'] = "The message is larger than per-message size limit.  This limit may either be for physical or administrative reasons. This is useful only as a permanent error.";

			$status_code_subclasses['3.5']['title'] = "System incorrectly configured";
			$status_code_subclasses['3.5']['descr'] = "The system is not configured in a manner which will permit it to accept this message.";

			$status_code_subclasses['4.0']['title'] = "Other or undefined network or routing status";
			$status_code_subclasses['4.0']['descr'] = "Something went wrong with the networking, but it is not clear what the problem is, or the problem cannot be well expressed with any of the other provided detail codes.";

			$status_code_subclasses['4.1']['title'] = "No answer from host";
			$status_code_subclasses['4.1']['descr'] = "The outbound connection attempt was not answered, either because the remote system was busy, or otherwise unable to take a call.  This is useful only as a persistent transient error.";

			$status_code_subclasses['4.2']['title'] = "Bad connection";
			$status_code_subclasses['4.2']['descr'] = "The outbound connection was established, but was otherwise unable to complete the message transaction, either because of time-out, or inadequate connection quality. This is useful only as a persistent transient error.";

			$status_code_subclasses['4.3']['title'] = "Directory server failure";
			$status_code_subclasses['4.3']['descr'] = "The network system was unable to forward the message, because a directory server was unavailable.  This is useful only as a persistent transient error. The inability to connect to an Internet DNS server is one example of the directory server failure error. ";

			$status_code_subclasses['4.4']['title'] = "Unable to route";
			$status_code_subclasses['4.4']['descr'] = "The mail system was unable to determine the next hop for the message because the necessary routing information was unavailable from the directory server. This is useful for both permanent and persistent transient errors.  A DNS lookup returning only an SOA (Start of Administration) record for a domain name is one example of the unable to route error.";

			$status_code_subclasses['4.5']['title'] = "Mail system congestion";
			$status_code_subclasses['4.5']['descr'] = "The mail system was unable to deliver the message because the mail system was congested. This is useful only as a persistent transient error.";

			$status_code_subclasses['4.6']['title'] = "Routing loop detected";
			$status_code_subclasses['4.6']['descr'] = "A routing loop caused the message to be forwarded too many times, either because of incorrect routing tables or a user forwarding loop. This is useful only as a persistent transient error.";

			$status_code_subclasses['4.7']['title'] = "Delivery time expired";
			$status_code_subclasses['4.7']['descr'] = "The message was considered too old by the rejecting system, either because it remained on that host too long or because the time-to-live value specified by the sender of the message was exceeded. If possible, the code for the actual problem found when delivery was attempted should be returned rather than this code.  This is useful only as a persistent transient error.";

			$status_code_subclasses['5.0']['title'] = "Other or undefined protocol status";
			$status_code_subclasses['5.0']['descr'] = "Something was wrong with the protocol necessary to deliver the message to the next hop and the problem cannot be well expressed with any of the other provided detail codes.";

			$status_code_subclasses['5.1']['title'] = "Invalid command";
			$status_code_subclasses['5.1']['descr'] = "A mail transaction protocol command was issued which was either out of sequence or unsupported.  This is useful only as a permanent error.";

			$status_code_subclasses['5.2']['title'] = "Syntax error";
			$status_code_subclasses['5.2']['descr'] = "A mail transaction protocol command was issued which could not be interpreted, either because the syntax was wrong or the command is unrecognized. This is useful only as a permanent error.";

			$status_code_subclasses['5.3']['title'] = "Too many recipients";
			$status_code_subclasses['5.3']['descr'] = "More recipients were specified for the message than could have been delivered by the protocol.  This error should normally result in the segmentation of the message into two, the remainder of the recipients to be delivered on a subsequent delivery attempt.  It is included in this list in the event that such segmentation is not possible.";

			$status_code_subclasses['5.4']['title'] = "Invalid command arguments";
			$status_code_subclasses['5.4']['descr'] = "A valid mail transaction protocol command was issued with invalid arguments, either because the arguments were out of range or represented unrecognized features. This is useful only as a permanent error. ";

			$status_code_subclasses['5.5']['title'] = "Wrong protocol version";
			$status_code_subclasses['5.5']['descr'] = "A protocol version mis-match existed which could not be automatically resolved by the communicating parties.";

			$status_code_subclasses['6.0']['title'] = "Other or undefined media error";
			$status_code_subclasses['6.0']['descr'] = "Something about the content of a message caused it to be considered undeliverable and the problem cannot be well expressed with any of the other provided detail codes. ";

			$status_code_subclasses['6.1']['title'] = "Media not supported";
			$status_code_subclasses['6.1']['descr'] = "The media of the message is not supported by either the delivery protocol or the next system in the forwarding path. This is useful only as a permanent error.";

			$status_code_subclasses['6.2']['title'] = "Conversion required and prohibited";
			$status_code_subclasses['6.2']['descr'] = "The content of the message must be converted before it can be delivered and such conversion is not permitted.  Such prohibitions may be the expression of the sender in the message itself or the policy of the sending host.";

			$status_code_subclasses['6.3']['title'] = "Conversion required but not supported";
			$status_code_subclasses['6.3']['descr'] = "The message content must be converted to be forwarded but such conversion is not possible or is not practical by a host in the forwarding path.  This condition may result when an ESMTP gateway supports 8bit transport but is not able to downgrade the message to 7 bit as required for the next hop.";

			$status_code_subclasses['6.4']['title'] = "Conversion with loss performed";
			$status_code_subclasses['6.4']['descr'] = "This is a warning sent to the sender when message delivery was successfully but when the delivery required a conversion in which some data was lost.  This may also be a permanant error if the sender has indicated that conversion with loss is prohibited for the message.";

			$status_code_subclasses['6.5']['title'] = "Conversion Failed";
			$status_code_subclasses['6.5']['descr'] = "A conversion was required but was unsuccessful.  This may be useful as a permanent or persistent temporary notification.";

			$status_code_subclasses['7.0']['title'] = "Other or undefined security status";
			$status_code_subclasses['7.0']['descr'] = "Something related to security caused the message to be returned, and the problem cannot be well expressed with any of the other provided detail codes.  This status code may also be used when the condition cannot be further described because of security policies in force.";

			$status_code_subclasses['7.1']['title'] = "Delivery not authorized, message refused";
			$status_code_subclasses['7.1']['descr'] = "The sender is not authorized to send to the destination. This can be the result of per-host or per-recipient filtering.  This memo does not discuss the merits of any such filtering, but provides a mechanism to report such. This is useful only as a permanent error.";

			$status_code_subclasses['7.2']['title'] = "Mailing list expansion prohibited";
			$status_code_subclasses['7.2']['descr'] = "The sender is not authorized to send a message to the intended mailing list. This is useful only as a permanent error.";

			$status_code_subclasses['7.3']['title'] = "Security conversion required but not possible";
			$status_code_subclasses['7.3']['descr'] = "A conversion from one secure messaging protocol to another was required for delivery and such conversion was not possible. This is useful only as a permanent error. ";

			$status_code_subclasses['7.4']['title'] = "Security features not supported";
			$status_code_subclasses['7.4']['descr'] = "A message contained security features such as secure authentication which could not be supported on the delivery protocol. This is useful only as a permanent error.";

			$status_code_subclasses['7.5']['title'] = "Cryptographic failure";
			$status_code_subclasses['7.5']['descr'] = "A transport system otherwise authorized to validate or decrypt a message in transport was unable to do so because necessary information such as key was not available or such information was invalid.";

			$status_code_subclasses['7.6']['title'] = "Cryptographic algorithm not supported";
			$status_code_subclasses['7.6']['descr'] = "A transport system otherwise authorized to validate or decrypt a message was unable to do so because the necessary algorithm was not supported. ";

			$status_code_subclasses['7.7']['title'] = "Message integrity failure";
			$status_code_subclasses['7.7']['descr'] = "A transport system otherwise authorized to validate a message was unable to do so because the message was corrupted or altered.  This may be useful as a permanent, transient persistent, or successful delivery code.";


			$ret = self::format_status_code($code);
			$arr = explode('.', $ret['code']);
			$str = "<p><b>" . $status_code_classes[$arr[0]]['title'] . "</b> - " . $status_code_classes[$arr[0]]['descr'] . "  <B>" . $status_code_subclasses[$arr[1] . "." . $arr[2]]['title'] . "</B> - " . $status_code_subclasses[$arr[1] . "." . $arr[2]]['descr'] . "</p>";

			return $str;
		}

		static function get_action_from_status_code($code)
		{

			if($code == '')
			{
				return '';
			}
			$ret = self::format_status_code($code);
			$stat = $ret['code'][0];
			switch($stat)
			{
				case(2):
					return 'success';
					break;
				case(4):
					return 'transient';
					break;
				case(5):
					return 'failed';
					break;
				default:
					return '';
					break;
			}
		}

		static function decode_diagnostic_code($dcode)
		{

			if(preg_match("/(\d\.\d\.\d)\s/", $dcode, $array))
			{
				return $array[1];
			}
			elseif(preg_match("/(\d\d\d)\s/", $dcode, $array))
			{
				return $array[1];
			}

			return null;
		}

		static function is_a_bounce($head_hash)
		{

			if(preg_match("/(mail delivery failed|failure notice|warning: message|delivery status notif|delivery failure|delivery problem|spam eater|returned mail|undeliverable|returned mail|delivery errors|mail status report|mail system error|failure delivery|delivery notification|delivery has failed|undelivered mail|returned email|returning message to sender|returned to sender|message delayed|mdaemon notification|mailserver notification|mail delivery system|nondeliverable mail|mail transaction failed)|auto.{0,20}reply|vacation|(out|away|on holiday).*office/i", $head_hash['Subject']))
			{
				return true;
			}

			if(preg_match('/auto_reply/', $head_hash['Precedence']))
			{
				return true;
			}

			if(preg_match("/^(postmaster|mailer-daemon)\@?/i", $head_hash['From']))
			{
				return true;
			}

			return false;
		}

		static function find_email_addresses($first_body_part)
		{

			// not finished yet.  This finds only one address.
			if(preg_match("/\b([A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/i", $first_body_part, $matches))
			{
				return array($matches[1]);
			}
			else
			{
				return array();
			}
		}

	}


