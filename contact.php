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

require_once("class2.php");
//define('e_HANDLER', "e107_handlers/");
// security image may be disabled by removing the appropriate shortcodes from the template.
$active = varset($pref['contact_visibility'], e_UC_PUBLIC);
$contactInfo = trim(SITECONTACTINFO);

if(!check_class($active) && empty($contactInfo))
{
	e107::getRedirect()->go(e_HTTP."index.php");
}

require_once(e_HANDLER."secure_img_handler.php");
$sec_img = new secure_image;

e107::lan('core','contact');

define('PAGE_NAME', LANCONTACT_00);

require_once(HEADERF);

$tp = e107::getParser();
$ns = e107::getRender();

if (!vartrue($CONTACT_FORM))
 {
	if (file_exists(THEME."contact_template.php")) 
	{
		require_once(THEME."contact_template.php");
	}
	else
	{		
		// Redirect Page if no contact-form or contact-info is available. 
		if(($pref['sitecontacts']== e_UC_NOBODY) && trim(SITECONTACTINFO) == "")
		{
			e107::getRedirect()->redirect(e_BASE."index.php");
			exit;
		}
		
		$CONTACT_FORM = e107::getCoreTemplate('contact','form'); // require_once(e_THEME."templates/contact_template.php");
	}
}

if(isset($_POST['send-contactus']))
{
	$error          = "";
	$ignore         = false;


	// Contact Form Filter -----

	$contact_filter = e107::pref('core','contact_filter','');

	if(!empty($contact_filter))
	{
		$tmp = explode("\n", $contact_filter);

		if(!empty($tmp))
		{
			foreach($tmp as $filterItem)
			{
				if(strpos($_POST['body'], $filterItem)!==false)
				{
					$ignore = true;
					break;
				}

			}
		}
	}

	// ---------

	$sender_name    = $tp->toEmail($_POST['author_name'], true,'RAWTEXT');
	$sender         = check_email($_POST['email_send']);
	$subject        = $tp->toEmail($_POST['subject'], true,'RAWTEXT');
	$body           = nl2br($tp->toEmail($_POST['body'], true,'RAWTEXT'));

	$email_copy     = !empty($_POST['email_copy']) ? 1 : 0;
	
// Check Image-Code
    if (isset($_POST['rand_num']) && !$sec_img->verify_code($_POST['rand_num'], $_POST['code_verify']))
	{
		$error .= LANCONTACT_15."\\n";
	}

// Check message body.
	if(strlen(trim($body)) < 15)
	{
		$error .= LANCONTACT_12."\\n";
    }

// Check subject line.
	if(isset($_POST['subject']) && strlen(trim($subject)) < 2)
	{
		$error .= LANCONTACT_13."\\n";
    }

	if(!strpos(trim($sender),"@"))
	{
		$error .= LANCONTACT_11."\\n";
    }



// Check email address on remote server (if enabled). XXX Problematic!
	/*
	if ($pref['signup_remote_emailcheck'] && $error == '')
	{
		require_once(e_HANDLER."mail_validation_class.php");
		list($adminuser,$adminhost) = explode('@', SITEADMINEMAIL, 2);
		$validator = new email_validation_class;
		$validator->localuser= $adminuser;
		$validator->localhost= $adminhost;
		$validator->timeout=3;
		//	$validator->debug=1;
		//	$validator->html_debug=1;
		if($validator->ValidateEmailBox($sender) != 1)
		{
			$error .= LANCONTACT_11."\\n";
		}

	}
	*/

	// No errors - so proceed to email the admin and the user (if selected).
	if($ignore === true)
    {
        $ns->tablerender('', "<div class='alert alert-success'>".LANCONTACT_09."</div>"); // ignore and leave them none the wiser.
        e107::getDebug()->log("Contact form post ignored");
        require_once(FOOTERF);
		exit;
    }
    elseif(empty($error))
	{
		$body .= "<br /><br />
		<table class='table'>
		<tr>
		<td>IP:</td><td>".e107::getIPHandler()->getIP(TRUE)."</td></tr>";

		if (USER)
		{
			$body .= "<tr><td>User:</td><td>#".USERID." ".USERNAME."</td></tr>";
		}

		if(empty($_POST['contact_person']) && !empty($pref['sitecontacts'])) // only 1 person, so contact_person not posted.
		{
    		if($pref['sitecontacts'] == e_UC_MAINADMIN)
			{
        		$query = "user_perms = '0' OR user_perms = '0.' ";
			}
			elseif($pref['sitecontacts'] == e_UC_ADMIN)
			{
				$query = "user_admin = 1 ";
			}
			else
			{
				$query = "FIND_IN_SET(".$pref['sitecontacts'].",user_class) ";
			}
		}
		else
		{
      		$query = "user_id = ".intval($_POST['contact_person']);
		}

    	if($sql->gen("SELECT user_name,user_email FROM `#user` WHERE ".$query." LIMIT 1"))
		{
    		$row = $sql->fetch();
    		$send_to = $row['user_email'];
			$send_to_name = $row['user_name'];
		}
    	else
		{
		    $send_to = SITEADMINEMAIL;
			$send_to_name = ADMIN;
		}


		// ----------------------

		$CONTACT_EMAIL = e107::getCoreTemplate('contact','email');

		unset($_POST['contact_person'], $_POST['author_name'], $_POST['email_send'] , $_POST['subject'], $_POST['body'], $_POST['rand_num'], $_POST['code_verify'], $_POST['send-contactus']);

		if(!empty($_POST)) // support for custom fields in contact template.
		{
			foreach($_POST as $k=>$v)
			{
				$body .=  "<tr><td>".$k.":</td><td>".$tp->toEmail($v, true,'RAWTEXT')."</td></tr>";
			}
		}

		$body .= "</table>";

		if(!empty($CONTACT_EMAIL['subject']))
		{
			$vars = array('CONTACT_SUBJECT'=>$subject,'CONTACT_PERSON'=>$send_to_name);

			if(!empty($_POST)) // support for custom fields in contact template.
			{
				foreach($_POST as $k=>$v)
				{
					$scKey = strtoupper($k);
					$vars[$scKey] =$tp->toEmail($v, true,'RAWTEXT');
				}
			}

			$subject = $tp->simpleParse($CONTACT_EMAIL['subject'],$vars);
		}

		// -----------------------

		// Send as default sender to avoid spam issues. Use 'replyto' instead. 
    	$eml = array(
    	    'subject'       => $subject,
    	    'sender_name'   => $sender_name,
    	    'body'          => $body,
		    'replyto'       => $sender,
		    'replytonames'  => $sender_name,
		    'template'      => 'default'
	    );



	    $message = e107::getEmail()->sendEmail($send_to, $send_to_name, $eml, false)  ? LANCONTACT_09 : LANCONTACT_10;

	    //	$message =  (sendemail($send_to,"[".SITENAME."] ".$subject, $body,$send_to_name,$sender,$sender_name)) ? LANCONTACT_09 : LANCONTACT_10;

	    if(isset($pref['contact_emailcopy']) && $pref['contact_emailcopy'] && $email_copy == 1)
	    {
		    require_once(e_HANDLER."mail.php");
			sendemail($sender,"[".SITENAME."] ".$subject, $body,ADMIN,$sender,$sender_name);
	    }


    	$ns->tablerender('', "<div class='alert alert-success'>".$message."</div>");
		require_once(FOOTERF);
		exit;
    }
    else
	{
		message_handler("P_ALERT", $error);
	}

}

if(SITECONTACTINFO)
{
	if(!isset($CONTACT_INFO))
	{
		$CONTACT_INFO = e107::getCoreTemplate('contact','info'); 
	}
	
	$text = $tp->parseTemplate($CONTACT_INFO, true, vartrue($contact_shortcodes));
	$ns -> tablerender(LANCONTACT_01, $text,"contact");
}


if(check_class($active) && isset($pref['sitecontacts']) && $pref['sitecontacts'] != e_UC_NOBODY)
{
	$contact_shortcodes = e107::getScBatch('contact');
	// Wrapper support
	$contact_shortcodes->wrapper('contact/form');
	
	$text = $tp->parseTemplate($CONTACT_FORM, true, $contact_shortcodes);

	if(trim($text) != "")
	{
		$ns -> tablerender(LANCONTACT_02, $text, "contact");
	}
}
elseif($active == e_UC_MEMBER && ($pref['sitecontacts'] != e_UC_NOBODY))
{
	$srch = array("[","]");
	$repl = array("<a class='alert-link' href='".e_SIGNUP."'>","</a>");
	$message = LANCONTACT_16; // "You must be [registered] and signed-in to use this form.";

	$ns -> tablerender(LANCONTACT_02, "<div class='alert alert-info'>".str_replace($srch, $repl, $message)."</div>", "contact");
}



require_once(FOOTERF);
exit;
?>