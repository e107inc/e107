<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/contact_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

e107::coreLan('contact');

class contact_shortcodes extends e_shortcode
{
	
	
	function sc_contact_email_copy($parm='') 
	{
		global $pref;
		if(!isset($pref['contact_emailcopy']) || !$pref['contact_emailcopy'])
		{
			return '';
		}
		return "<input type='checkbox' name='email_copy'  value='1'  />";
	}
	

	function sc_contact_person($parm='') 
	{
		$tp = e107::getParser();
		$sql = e107::getDb();
		$pref = e107::getPref();
		
		if(varset($pref['sitecontacts']) == e_UC_ADMIN)
		{
			$query = "user_admin =1 AND user_ban = 0";
		}
		elseif(varset($pref['sitecontacts']) == e_UC_MAINADMIN)
		{
		    $query = "user_admin = 1 AND (user_perms = '0' OR user_perms = '0.') ";
		}
		else
		{
			$query = "FIND_IN_SET(".$pref['sitecontacts'].",user_class) AND user_ban = 0 ";
		}
		
		$text = "<select name='contact_person' class='tbox contact_person form-control'>\n";
		
		$count = $sql ->select("user", "user_id,user_name", $query . " ORDER BY user_name");
		
		if($count > 1)
		{
		    while($row = $sql->fetch())
			{
		    	$text .= "<option value='".$row['user_id']."'>".$row['user_name']."</option>\n";
		    }
		}
		else
		{
			return '';
		}
		
		$text .= "</select>";
		return $text;
	}
	
	
	function sc_contact_imagecode($parm='') 
	{
		//return e107::getSecureImg()->r_image()."<div>".e107::getSecureImg()->renderInput()."</div>"; 
		//return "<input type='hidden' name='rand_num' value='".e107::getSecureImg()->random_number."' />".e107::getSecureImg()->r_image(); // See #3980
		return e107::getSecureImg()->renderImage();
	}
	
	function sc_contact_imagecode_label($parm='')
	{
		return e107::getSecureImg()->renderLabel();
	}
	
	function sc_contact_imagecode_input($parm='') 
	{
		return e107::getSecureImg()->renderInput();
	}
	
	
	/* example {CONTACT_NAME} */
	/* example {CONTACT_NAME: class=form-control} */
	/* example {CONTACT_NAME: class=col-md-12&placeholder=".LAN_CONTACT_03." *} */
		
	function sc_contact_name($parm=null)
	{
		$userName = deftrue('USERNAME');
		$class = (!empty($parm['class'])) ? $parm['class'] : 'tbox form-control';
		$placeholder = (!empty($parm['placeholder'])) ? " placeholder= '".$parm['placeholder']."'" : '';
		$value      = 	!empty($_POST['author_name']) ?  e107::getParser()->filter( $_POST['author_name']) : $userName;
		return "<input type='text'   id='contactName' title='".LAN_CONTACT_17."' aria-label='".LAN_CONTACT_17."' aria-labelledby='contactName' name='author_name' required='required' size='30' ".$placeholder."  class='".$class."' value=\"".$value."\" />";
	}

	function sc_contact_map($parm=null)
	{
		$pref = e107::getPref('contact_info');

		if(empty($pref['address']) && empty($pref['coordinates']))
		{
			return null;
		}

		$address = !empty($pref['coordinates']) ? $pref['coordinates'] : $pref['address'];
		$address = trim($address);
		$address = str_replace("\n", " ", $address);

		$zoom = varset($parm['zoom'], 'street');

		$zoomOpts = [
			'street' => 17,
			'district'  => 14,
			'city'  => 12,

		];

		$zoom = (int) varset($zoomOpts[$zoom],$zoom);

// &z='.$zoom.'

		return '<iframe class="sc-contact-map" src="https://www.google.com/maps?q='.$address.'&output=embed&z='.$zoom.'"></iframe>';

	}

	function sc_contact_info($parm=null)
	{
		$ipref = e107::getPref('contact_info');
		$type = varset($parm['type']);

		if(empty($type) || empty($ipref[$type]))
		{
			return null;
		}

		$tp = e107::getParser();
		$ret = '';

		switch($type)
		{
			case "organization":
				$ret = $tp->toHTML($ipref[$type], true, 'TITLE');
				break;

			case 'email1':
			case 'email2':
			case 'phone1':
			case 'phone2':
			case 'phone3':
			case 'fax':
				$ret = $tp->obfuscate($ipref[$type]);
				break;

			default:
				$ret = $tp->toHTML($ipref[$type], true, 'BODY');
				// code to be executed if n is different from all labels;
		}

		return $ret;
	}




	/* example {CONTACT_EMAIL} */
	/* example {CONTACT_EMAIL: class=form-control} */
	/* example {CONTACT_EMAIL: class=col-md-12&placeholder=".LAN_CONTACT_04." *} */

	function sc_contact_email($parm=null)
	{
		$userEmail = deftrue('USEREMAIL');
		$disabled = (!empty($userEmail)) ? 'readonly' : ''; // don't allow change from a verified email address.

		$class = (!empty($parm['class'])) ? $parm['class'] : 'tbox form-control';
		$placeholder = (!empty($parm['placeholder'])) ? " placeholder= '".$parm['placeholder']."'" : '';
		$value = !empty($_POST['email_send'] ) ? e107::getParser()->filter($_POST['email_send'],'email') : USEREMAIL;
		return "<input type='email'   ".$disabled." id='contactEmail' title='".LAN_CONTACT_18."' aria-label='".LAN_CONTACT_18."'  aria-labelledby='contactEmail' name='email_send' required='required' size='30' ".$placeholder." class='".$class."' value='".$value."' />";
	}
	
	
	
	/* example {CONTACT_SUBJECT} */
	/* example {CONTACT_SUBJECT: class=form-control} */
	/* example {CONTACT_SUBJECT: class=col-md-12&placeholder=".LAN_CONTACT_05." *} */
	
	function sc_contact_subject($parm=null)
	{
		$class = (!empty($parm['class'])) ? $parm['class'] : 'tbox form-control';
		$placeholder = (!empty($parm['placeholder'])) ? " placeholder= '".$parm['placeholder']."'" : '';
		$value = !empty($_POST['subject']) ? e107::getParser()->filter($_POST['subject']) : '';
		return "<input type='text' id='contactSubject' title='".LAN_CONTACT_19."' aria-label='".LAN_CONTACT_19."' aria-labelledby='contactSubject' name='subject' required='required' size='30' ".$placeholder." class='".$class."' value=\"".$value."\" />";
	}
	
	
	function sc_contact_body($parm=null)
	{
		if(is_string($parm))
		{
			parse_str($parm, $parm);
		}

		$rows = vartrue($parm['rows'],10);
		$cols = vartrue($parm['cols'],70);
		$placeholder = !empty($parm['placeholder']) ? "placeholder=\"".$parm['placeholder']."\"" : "";
		
		$size = ($cols > 60) ? 'input-xxlarge' : '';

		$class = (!empty($parm['class'])) ? $parm['class'] : 'tbox '.$size.' form-control';


		$value = !empty($_POST['body']) ? stripslashes($_POST['body']) : '';
		
		return "<textarea cols='{$cols}'  id='contactBody' rows='{$rows}' title='".LAN_CONTACT_20."' aria-label='".LAN_CONTACT_20."' aria-labelledby='contactBody' name='body' ".$placeholder." required='required' class='".$class."'>".$value."</textarea>";
	}
	
	
	/* example {CONTACT_SUBMIT_BUTTON} */
	/* example {CONTACT_SUBMIT_BUTTON: class=contact submit btn btn-minimal} */
	function sc_contact_submit_button($parm=null)
	{
		$class = (!empty($parm['class'])) ? $parm['class'] : 'btn btn-primary button';
		
		return "<input type='submit' name='send-contactus' value=\"".LAN_CONTACT_08."\" class='".$class."' />";	
	}

	function sc_contact_gdpr_check($parm=null)
	{
		$parm['class'] = (!empty($parm['class'])) ? $parm['class'] : '';
		$parm = array_merge(array('required'=>1), $parm);
		return e107::getForm()->checkbox('gdpr', 1,false, $parm);
	}
     
	/* {CONTACT_GDPR_LINK} */
	function sc_contact_gdpr_link($parm=null)
	{
		$pp = e107::getPref('gdpr_privacypolicy', '');
		if (!$pp)
		{
			return '';
		}
		$pp = e107::getParser()->replaceConstants($pp, 'full'); 
		$class = (!empty($parm['class'])) ? $parm['class'] : '';
		$link = sprintf('<span class="%s"><a href="%s" target="_blank">%s</a></span>', $class, $pp, LAN_CONTACT_22);

		return e107::getParser()->lanVars(LAN_CONTACT_23, $link);

	}


}


