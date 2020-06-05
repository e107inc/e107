<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Bounce handler for cron or manual triggering
 *
 * $Source: /cvs_backup/e107_0.8/e107_handlers/pop_bounce_handler.php,v $
 * $Revision$
 * $Date$
 * $Author$
*/

/*
This handler is intended for use with a cron job, or with manual triggering, to get bounce notifications from a POP3 or IMAP account.
It extends the receiveMail class (pop3_class.php) to provide some higher level methods.
Can be used for getting mail from any POP3 mail account.

Some parts used from/based on the receiveMail class, by Mitul Koradia (Email: mitulkoradia@gmail.com, Cell : +91 9879697592)

Notes:
1. Requires that the IMAP extension is installed
2. Mailbox names that contain international characters besides those in the printable ASCII space have to be encoded width imap_utf7_encode(). 
*/



class pop3BounceHandler
{
	protected	$e107;

	protected $server='';				// String to connect to server
	protected $username='';
	protected $password='';

	protected $email='';				// Email address receiving bounces
	protected $delBounce = FALSE;		// TRUE to delete emails after reading

	protected $mailResource = '';		// Resource identifier for IMAP
	
	protected $mailManager = FALSE;



	public function __construct($override = FALSE)
	{
		global $pref;
		$this->e107 = e107::getInstance();
		if (($override === FALSE) || !is_array($override))
		{	// Set up from prefs
			$override['mail_bounce_user'] = $pref['mail_bounce_user'];
			$override['mail_bounce_pass'] = $pref['mail_bounce_pass'];
			$override['mail_bounce_email'] = $pref['mail_bounce_email'];
			$override['mail_bounce_pop3'] = $pref['mail_bounce_pop3'];
			$override['mail_bounce_type'] = varset($pref['mail_bounce_type'],'pop3');
		}

		if($override['mail_bounce_type']=='imap')
		{
			$port = varset($override['mail_bounce_port'], '143');
			$strConnect='{'.$override['mail_bounce_pop3'].':'.$port. '}INBOX';
		}
		else
		{
			$port = varset($override['mail_bounce_port'], '110');		// POP3 port
			$servertype = '/'.varset($override['mail_bounce_type'], 'pop3');
			$strConnect='{'.$override['mail_bounce_pop3'].':'.$port. $servertype.'}INBOX';
		}
		$this->server			=	$strConnect;
		$this->username			=	$override['mail_bounce_user'];
		$this->password			=	$override['mail_bounce_pass'];
		$this->email			=	$override['mail_bounce_email'];

		$this->delBounce = ($pref['mail_bounce_delete']) ? true : false;
	}


	function connect() //Connect To the Mail Box
	{
		$this->mailResource=imap_open($this->server,$this->username,$this->password);
	}



	function getTotalMails() //Get Total Number off Unread Email In Mailbox
	{
		$headers=imap_headers($this->mailResource);
		return count($headers);
	}



	function getHeaders($mid) // Get Header info
	{
		$mail_header=imap_header($this->mailResource,$mid);
		$sender=$mail_header->from[0];
		$sender_replyto=$mail_header->reply_to[0];
		$stat = (strtolower($sender->mailbox)!='mailer-daemon' && strtolower($sender->mailbox)!='postmaster') ? FALSE : TRUE;
        if(strpos($mail_header->subject,"delayed"))
		{
			$stat = FALSE;
		}
			$mail_details=array(
					'from'=>strtolower($sender->mailbox).'@'.$sender->host,
					'fromName'=>$sender->personal,
					'toOth'=>strtolower($sender_replyto->mailbox).'@'.$sender_replyto->host,
					'toNameOth'=>$sender_replyto->personal,
					'subject'=>$mail_header->subject,
					'to'=>strtolower($mail_header->toaddress),
					'bounce'=>$stat,
					'date'=>$mail_header->date
				);
		return $mail_details;
	}


	protected function get_mime_type(&$structure) //Get Mime type Internal Private Use
	{
		$primary_mime_type = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

		if($structure->subtype) 
		{
			return $primary_mime_type[(int) $structure->type] . '/' . $structure->subtype;
		}
		return "TEXT/PLAIN";
	}


	protected function get_part($stream, $msg_number, $mime_type, $structure = false, $part_number = false) //Get Part Of Message Internal Private Use
	{
		if(!$structure) 
		{
			$structure = imap_fetchstructure($stream, $msg_number);
		}
		if($structure) 
		{
			if($mime_type == $this->get_mime_type($structure))
			{
				if(!$part_number)
				{
					$part_number = '1';
				}
				$text = imap_fetchbody($stream, $msg_number, $part_number);
				if($structure->encoding == 3)
				{
					return imap_base64($text);
				}
				else if($structure->encoding == 4)
				{
					return imap_qprint($text);
				}
				else
				{
					return $text;
				}
			}
			if($structure->type == 1) /* multipart */
			{
				while(list($index, $sub_structure) = each($structure->parts))
				{
					if($part_number)
					{
						$prefix = $part_number . '.';
					}
					$data = $this->get_part($stream, $msg_number, $mime_type, $sub_structure, $prefix . ($index + 1));
					if($data)
					{
						return $data;
					}
				}
			}
		}
		return false;
	}


	function getBody($mid,$mode='') // Get Message Body
	{
		if($mode != 'plain')
		{
			$body = $this->get_part($this->mailResource, $mid, 'TEXT/HTML');
		}
		if (($body == '') || $mode == 'plain')
		{
			$body = $this->get_part($this->mailResource, $mid, 'TEXT/PLAIN');
		}
		if ($body == '') 
		{
			return '';
		}
		return $body;
	}


	function deleteMails($mid) // Delete That Mail
	{
		imap_delete($this->mailResource,$mid);
	}


	function close_mailbox() //Close Mail Box
	{
		imap_close($this->mailResource,CL_EXPUNGE);
	}



	/**
	 * Loop reading all emails from the bounces mailbox. If an email address and/or e107 header are
	 * identified, process the bounce.
	 * Delete all emails after processing, if $pref flag is set.
	 *
	 * @return void
	 */
	public function processBounces()
	{
		$identifier = deftrue('MAIL_IDENTIFIER', 'X-e107-id');
		
		$this->connect();
		$tot = $this->getTotalMails();		// Get all the emails
		for ($i = 1; $i <= $tot; $i++)
		{
			$head = $this->getHeaders($i);	// Get the headers
			if ($head['bounce'])
			{
				//print_a($head);
				$body = $this->getBody($i);
				$e107Header = '';
				if (preg_match('#.*'.$identifier.':(.*?)[\n\r]#',$body, $result))
				{
					$e107Header = varset($result[1],'');
				}
				$emailAddress = '';
				if (preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i",$body,$result))
				{
					$emailAddress = varset($result[0],'');
					if ($emailAddress == $this->email)
					{
						$emailAddress = '';			// Email address found is that of the bounce email account
					}
				}
				// Call the bounce handler here
				if ($this->mailManager === FALSE)
				{
					require_once(e_HANDLER.'mail_manager_class.php');
					$this->mailManager = new e107MailManager();
				}
				echo "Email: {$emailAddress}<br />Header: {$e107Header}<br />";
				$this->mailManager->markBounce($e107Header, $emailAddress);
			}
			// Now delete the email, if option set (do it regardless of whether a bounce email)
			if ($this->delBounce)
			{
				$this->deleteMails($i);		// Not actually deleted until we close the mailbox
			}
		}
		$this->close_mailbox();
	}
}



