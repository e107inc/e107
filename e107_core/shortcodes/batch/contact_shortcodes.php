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
		return "<input type='hidden' name='rand_num' value='".e107::getSecureImg()->random_number."' />".e107::getSecureImg()->r_image();
	}
	
	function sc_contact_imagecode_label($parm='')
	{
		return e107::getSecureImg()->renderLabel();
	}
	
	function sc_contact_imagecode_input($parm='') 
	{
		return e107::getSecureImg()->renderInput();
	}
	
	
	function sc_contact_name($parm='')
	{
		$userName = deftrue('USERNAME');

		return "<input type='text'   id='contactName' title='".LANCONTACT_17."' name='author_name' required='required' size='30' class='tbox form-control' value=\"".varset($_POST['author_name'],$userName)."\" />";

	}



	function sc_contact_email($parm='')
	{
		$userEmail = deftrue('USEREMAIL');
		$disabled = (!empty($userEmail)) ? 'readonly' : ''; // don't allow change from a verified email address.

		return "<input type='email'   ".$disabled." id='contactEmail' title='".LANCONTACT_18."' name='email_send' required='required' size='30' class='tbox form-control' value='".(vartrue($_POST['email_send']) ? $_POST['email_send'] : USEREMAIL)."' />";
	}
	
	
	
	function sc_contact_subject($parm='')
	{
		return "<input type='text' id='contactSubject' title='".LANCONTACT_19."' name='subject' required='required' size='30' class='tbox form-control' value=\"".varset($_POST['subject'])."\" />";
	}
	
	
	function sc_contact_body($parm='')
	{
		parse_str($parm, $parm);
		$rows = vartrue($parm['rows'],10);
		$cols = vartrue($parm['cols'],70);
		
		if($cols > 60)
		{
			$size = 'input-xxlarge';	
		}
		
		return "<textarea cols='{$cols}'  id='contactBody' rows='{$rows}' title='".LANCONTACT_20."' name='body' required='required' class='tbox {$size} form-control'>".stripslashes(varset($_POST['body']))."</textarea>";
	}
	
	
	function sc_contact_submit_button($parm='')
	{
		return "<input type='submit' name='send-contactus' value=\"".LANCONTACT_08."\" class='btn btn-primary button' />";	
	}

}

?>