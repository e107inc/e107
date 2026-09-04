<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * /contact.php
 *
*/

require_once(__DIR__."/class2.php");


class contact_front
{
	function __construct()
	{
		$range = range(00,24);
		$tp = e107::getParser();
		$defs = array();

		foreach($range as $val)
		{
			$inc = $tp->leadingZeros($val,2);
			$legacy = 'LAN_CONTACT_'.$inc;
		//	$defs[$legacy] = 'LANCONTACT_'.$inc;
			$defs['LANCONTACT_'.$inc] = 'LAN_CONTACT_'.$inc;
		}

		e107::getLanguage()->bcDefs($defs);

		$this->init();

	}

	function init()
	{
		$pref = e107::pref();

		$active = varset($pref['contact_visibility'], e_UC_PUBLIC);
		$contactInfo = trim(SITECONTACTINFO);
		$pref = e107::getPref();

		if(!check_class($active) && empty($contactInfo) && empty($pref['contact_info']))
		{
			e107::redirect();
		}

		$offered = check_class($active) && isset($pref['sitecontacts']) && $pref['sitecontacts'] != e_UC_NOBODY;

		if($offered && isset($_POST['send-contactus']))
		{
			$this->processFormSubmit();
		}

		$form = '';
		$info = '';

		if(deftrue('SITECONTACTINFO') || !empty($pref['contact_info']))
		{
			$info = $this->renderContactInfo();
		}
		if($offered)
		{
			$form = $this->renderContactForm();
		}
		elseif($active == e_UC_MEMBER && ($pref['sitecontacts'] != e_UC_NOBODY))
		{
			$this->renderSignupRequired();
		}

		if(!$LAYOUT = e107::getCoreTemplate('contact', 'layout'))
		{
			$LAYOUT = '{---CONTACT-INFO---} {---CONTACT-FORM---}  ';
		}


		$LAYOUT = e107::getParser()->parseTemplate($LAYOUT, true, e107::getScBatch('contact'));

		echo str_replace(
			['{---CONTACT-FORM---}', '{---CONTACT-INFO---}'],
			[$form, $info],
			$LAYOUT
		);
	}

	/**
	 * Did this submission come from a document this site rendered?
	 *
	 * A request that carries no cookie is exempt from the core check in
	 * class2.php, by design: it has no ambient authority to borrow, so refusing
	 * it protects nobody and breaks machine-to-machine callers. This form is the
	 * exception, because what a caller borrows here is not the visitor's
	 * authority but the site's own mail server, addressed to a recipient the site
	 * chooses. So the request has to present a cookie in every mode, and a
	 * browser that rendered the form always does. The header is read rather than
	 * $_COOKIE, which handlers add to as the request is served.
	 *
	 * Above that, the proof the site's CSRF mode asks for is required too: a
	 * form token where tokens are read, and Sec-Fetch-Site where the browser is
	 * asked instead, both of which {@see e_session::check()} settles.
	 *
	 * @return bool
	 */
	private function submissionIsAuthentic()
	{
		$session = e107::getSession();

		if(empty($_SERVER['HTTP_COOKIE']) || !$session->check(false))
		{
			return false;
		}

		if(!e_session::modeUsesToken() || !deftrue('e_TOKEN'))
		{
			return true;
		}

		$token = isset($_POST['e-token']) ? $_POST['e-token'] : varset($_POST['e_token'], '');

		return $session->checkFormToken($token);
	}

	/**
	 * Resolve the person a submission is addressed to.
	 *
	 * The selector that offered the visitor a choice applied the sitecontacts
	 * predicates, so the same predicates decide who may be named. A posted
	 * contact_person narrows that set; it does not replace it.
	 *
	 * @return array|false user_name and user_email, or false when nobody matches
	 */
	private function findRecipient()
	{
		/** @var contact_shortcodes $sc */
		$sc = e107::getScBatch('contact');

		$qb = $sc->recipientQuery();
		$qb->select('user_name', 'user_email')->setMaxResults(1);

		if(!empty($_POST['contact_person']))
		{
			$qb->where('user_id', (int) $_POST['contact_person']);
		}

		return $qb->fetchRow();
	}

	/**
	 * @return void
	 */
	private function processFormSubmit()
	{
		$sec_img = e107::getSecureImg();
		$tp = e107::getParser();
		$ns = e107::getRender();
		$pref = e107::getPref();

		$error = "";
		$ignore = false;

		if(!$this->submissionIsAuthentic())
		{
			message_handler("P_ALERT", LAN_CONTACT_10);
			return;
		}


		// Contact Form Filter -----

		$contact_filter = e107::pref('core', 'contact_filter', '');

		if(!empty($contact_filter))
		{
			$tmp = explode("\n", $contact_filter);

			if(!empty($tmp))
			{
				foreach($tmp as $filterItem)
				{
					if(strpos($_POST['body'], $filterItem) !== false)
					{
						$ignore = true;
						break;
					}

				}
			}
		}

		// ---------

		$sender_name = $tp->toEmail($_POST['author_name'], true, 'RAWTEXT');
		$sender = check_email($_POST['email_send']);
		$subject = $tp->toEmail($_POST['subject'], true, 'RAWTEXT');
		$body = nl2br($tp->toEmail(strip_tags($_POST['body']), true, 'RAWTEXT'));

		$email_copy = !empty($_POST['email_copy']) ? 1 : 0;

		// Check Image-Code
		if($sec_img->invalidCode(varset($_POST['rand_num'], ''), varset($_POST['code_verify'], ''), secure_image::FORM_CONTACT))
		{
			$error .= LAN_CONTACT_15 . "\n";
		}

		// Check message body.
		if(strlen(trim($body)) < 15)
		{
			$error .= LAN_CONTACT_12 . "\n";
		}

		// Check subject line.
		if(isset($_POST['subject']) && strlen(trim($subject)) < 2)
		{
			$error .= LAN_CONTACT_13 . "\n";
		}

		if(!strpos(trim($sender), "@"))
		{
			$error .= LAN_CONTACT_11 . "\n";
		}

		// No errors - so proceed to email the admin and the user (if selected).
		if($ignore === true)
		{
			$ns->tablerender('', "<div class='alert alert-success'>" . LAN_CONTACT_09 . "</div>"); // ignore and leave them none the wiser.
			e107::getDebug()->log("Contact form post ignored");
			require_once(FOOTERF);
			exit;
		}
		elseif(empty($error))
		{
			$body .= "<br /><br />
				<table class='table'>
				<tr>
				<td>IP:</td><td>" . e107::getIPHandler()->getIP(true) . "</td></tr>";

			$body .= "<tr><td>" . LAN_EMAIL . ":</td><td>" . $tp->toEmail($sender, true, 'RAWTEXT') . "</td></tr>";

			if(USER)
			{
				$body .= "<tr><td>User:</td><td>#" . USERID . " " . USERNAME . "</td></tr>";
			}

			$row = $this->findRecipient();
			if($row)
			{
				$send_to = $row['user_email'];
				$send_to_name = $row['user_name'];
			}
			else
			{
				$send_to = SITEADMINEMAIL;
				$send_to_name = ADMIN;
			}


			// ----------------------

			$CONTACT_EMAIL = e107::getCoreTemplate('contact', 'email');

			unset($_POST['contact_person'], $_POST['author_name'], $_POST['email_send'], $_POST['subject'], $_POST['body'], $_POST['rand_num'], $_POST['code_verify'], $_POST['send-contactus'], $_POST['e-token'], $_POST['e_token']);

			if(!empty($_POST)) // support for custom fields in contact template.
			{
				foreach($_POST as $k => $v)
				{
					$body .= "<tr><td>" . $k . ":</td><td>" . $tp->toEmail($v, true, 'RAWTEXT') . "</td></tr>";
				}
			}

			$body .= "</table>";

			if(!empty($CONTACT_EMAIL['subject']))
			{
				$vars = array('CONTACT_SUBJECT' => $subject, 'CONTACT_PERSON' => $send_to_name);

				if(!empty($_POST)) // support for custom fields in contact template.
				{
					foreach($_POST as $k => $v)
					{
						$scKey = strtoupper($k);
						$vars[$scKey] = $tp->toEmail($v, true, 'RAWTEXT');
					}
				}

				$subject = $tp->simpleParse($CONTACT_EMAIL['subject'], $vars);
			}

			// -----------------------

			// Send as default sender to avoid spam issues. Use 'replyto' instead.
			$eml = array(
				'subject'      => $subject,
				'sender_name'  => $sender_name,
				'body'         => $body,
				'replyto'      => $sender,
				'replytonames' => $sender_name,
				'template'     => 'default'
			);


			$message = e107::getEmail()->sendEmail($send_to, $send_to_name, $eml) ? LAN_CONTACT_09 : LAN_CONTACT_10;

			//	$message =  (sendemail($send_to,"[".SITENAME."] ".$subject, $body,$send_to_name,$sender,$sender_name)) ? LANCONTACT_09 : LANCONTACT_10;

			if(isset($pref['contact_emailcopy']) && $pref['contact_emailcopy'] && $email_copy == 1)
			{
				require_once(e_HANDLER . "mail.php");
				sendemail($sender, "[" . SITENAME . "] " . $subject, $body, ADMIN, $sender, $sender_name);
			}


			$ns->tablerender('', "<div class='alert alert-success'>" . $message . "</div>");
		}
		else
		{
			message_handler("P_ALERT", $error);
		}


	}

	/**
	 * @return string html
	 */
	private function renderContactInfo()
	{

		$contact_shortcodes = e107::getScBatch('contact');

		$CONTACT_INFO = varset($GLOBALS['CONTACT_INFO']);

		if(empty($CONTACT_INFO))
		{
			$CONTACT_INFO = e107::getCoreTemplate('contact', 'info');
		}

		$contact_shortcodes->wrapper('contact/info');
		$text = e107::getParser()->parseTemplate($CONTACT_INFO, true, $contact_shortcodes);
		return e107::getRender()->tablerender(LAN_CONTACT_01, $text, "contact-info", true);

	}


	private function renderContactForm()
	{

		$CONTACT_FORM = varset($GLOBALS['CONTACT_FORM']);

		if(empty($CONTACT_FORM))
		{
			$CONTACT_FORM = e107::getCoreTemplate('contact', 'form'); // require_once(e_THEME."templates/contact_template.php");
		}

		/** @var contact_shortcodes $contact_shortcodes */
		$contact_shortcodes = e107::getScBatch('contact');
		$contact_shortcodes->wrapper('contact/form');

		$text = $contact_shortcodes->withImagecode(
			e107::getParser()->parseTemplate($CONTACT_FORM, true, $contact_shortcodes));

		if(trim($text) !== '')
		{
			return e107::getRender()->tablerender(LAN_CONTACT_02, $text, "contact-form", true);
		}
	}


	private function renderSignupRequired()
	{

		$srch = array("[", "]");
		$repl = array("<a class='alert-link' href='" . e_SIGNUP . "'>", "</a>");
		$message = LAN_CONTACT_16; // "You must be [registered] and signed-in to use this form.";

		e107::getRender()->tablerender(LAN_CONTACT_02, "<div class='alert alert-info'>" . str_replace($srch, $repl, $message) . "</div>", "contact");
	}

}


e107::lan('core','contact');
e107::title(LAN_CONTACT_00);
e107::canonical('contact');
e107::route('contact/index');  

require_once(HEADERF);

new contact_front;

require_once(FOOTERF);

