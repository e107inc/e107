<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * User lookup handler
 *
 * $URL$
 * $Id$
 */

/* @DEPRECATED - SUBJECT TO REMOVAL */
// Possible replacements: $frm->userpicker();

e107::includeLan(e_LANGUAGEDIR.e_LANGUAGE."/lan_user_select.php");

class user_select 
{

	/**
	 * @deprecated use e107::getForm()->userlist() instead.
	 * @param $class
	 * @param $form_name
	 * @return string
	 */
	function user_list($class, $form_name)
	{

	//	e107::getMessage()->addDebug("Deprecated user_list Method used ".debug_backtrace());

		global $sql, $tp;
		if($class === FALSE) { $class = e_UC_MEMBER;}
		switch ($class)
		{
			case e_UC_ADMIN:
				$where = "user_admin = 1";
				break;

			case e_UC_MEMBER:
				$where = "user_ban != 1";
				break;
				
			case e_UC_NOBODY:
				return "";
				break;
				
			default:
				$where = "user_class REGEXP '(^|,)(".$tp -> toDB($class, true).")(,|$)'";
				break;
		}




		$text = "<select class='tbox form-control' id='user' name='user' onchange=\"uc_switch('class')\">";
		$text .= "<option value=''>".US_LAN_1."</option>";
		$sql ->select("user", "user_name", $where." ORDER BY user_name");

		while ($row = $sql ->fetch())
		{
			$text .= "<option value='".$row['user_name']."'>".$row['user_name']."</option>";
		}

		$text .= "</select>";

		if(ADMIN)
		{
			$text .= "user_list method is deprecated. ".print_a(debug_backtrace(),true);
		}

		return $text;
	}


	/**
	 *    Display selection dropdown of all user classes
	 *
	 * @deprecated
	 * @param int $class - if its e_UC_MEMBER, all classes are shown. Otherwise only the class matching the value is shown.
	 * @return string
	 */
	function class_list($class, $form_name)  //TODO Find all instances of use and replace.
	{
		global $sql;
		$text = "<select class='tbox' id='class' name='class' onchange=\"uc_switch('user')\">";
		$text .= "<option value=''>".US_LAN_2."</option>";
		if (ADMINPERMS == '0' && $class == e_UC_MEMBER) 
		{
			$text .= "<option value='all'>".US_LAN_3."</option>";
		}
		if ($class == e_UC_MEMBER) 
		{
			$sql -> db_Select("userclass_classes", "userclass_id, userclass_name", "ORDER BY userclass_name", "nowhere");
			while ($row = $sql -> db_Fetch()) 
			{
				if (check_class($row['userclass_id']) || ADMINPERMS == '0') 
				{
					$text .= "<option value='".$row['userclass_id'].":".$row['userclass_name']."'>".$row['userclass_name']."</option>";
				}
			}
		} 
		else 
		{
			$sql -> db_Select("userclass_classes", "userclass_id, userclass_name", "userclass_id='".intval($class)."' ORDER BY userclass_name");
			while ($row = $sql -> db_Fetch()) 
			{
				$text .= "<option value='".$row['userclass_id'].":".$row['userclass_name']."'>".$row['userclass_name']."</option>";
			}
		}
		return $text;
	}
	

	/**
	 *	Put up user selection form
	 *
	 *	@param string $type  (list|popup) - determines type of display
	 *	@param string $user_form - type.name (textarea|input).name of text box or text area to accept user name (popups only)
	 *	@param string $user_value - initial value of user input box
	 *	@param int|boolean $class - if non-false, userclass ID to filter list (was unused parameter called $class_form)
	 *	@param string $dummy - unused parameter (was called $class_value)
	 *	@param int|boolean $oldClass - unused parameter; for legacy purposes, if non-false, overrides $class
	 *
	 *	@return string html for display
	 *
	 *	@todo remove unused parameters when possible
	 *	N.B. Only used by pm plugin in 0.7 core distribution
	 */
//	function select_form($type, $user_form, $user_value = '', $class_form = false, $class_value = '', $class = false) 
	function select_form($type, $user_form, $user_value = '', $class = false, $dummy = '', $oldClass = FALSE) 
	{
		global $tp;
		if ($oldClass !== FALSE) $class = $oldClass;		// Handle legacy position of $class
		$text = "<script type='text/javascript'>
		<!--
		function uc_switch(uctype) {
			document.getElementById(uctype).value = '';
		}
		//-->
		</script>";

		list($form_type, $form_id) = explode(".", $user_form);
		if($form_id == "") { $form_id = $form_type; }

		if ($type == 'list') 
		{
			$text .= $this -> user_list($class, 'user');
		} 
		else if ($type == 'popup')
		{
			if($form_type == 'textarea')
			{
				$text .= "<textarea class='tbox' name='".$form_id."' id='".$form_id."' cols='50' rows='4'>{$user_value}</textarea>&nbsp;";
			}
			else
			{
				$text .= "<input class='form-control tbox' type='text' name='".$form_id."' id='".$form_id."' size='25' maxlength='30' value='".$tp -> post_toForm($user_value)."'>&nbsp;";
			}
			$text .= "<img src='".e_IMAGE_ABS."generic/user_select.png'
			style='width: 16px; height: 16px; vertical-align: top' alt='".US_LAN_4."...' 
			title='".US_LAN_4."...' onclick=\"window.open('".e_PLUGIN_ABS."pm/pm.php?".$user_form."','user_search', 'toolbar=no,location=no,status=yes,scrollbars=yes,resizable=yes,width=300,height=200,left=100,top=100'); return false;\" />";
		}
		
		/*
		This appears to duplicate other functionality, in an unhelpful way!
		if ($class !== false) 
		{
			if (($class < e_UC_NOBODY && USERCLASS) || ADMINPERMS == '0') 
			{
				$text .= ' '.$this -> class_list($class, 'class');
			}
		}
		*/
		
		return $text;
	}
	


	function real_name($_id) 
	{
		global $sql;
		$sql -> db_Select("user", "user_name", "user_id='".intval($_id)."' ");
		if ($row = $sql -> db_Fetch()) 
		{
			return $row['user_name'];
		}
	}

	/**
	 * @deprecated
	 */
	function popup()
	{
		global $ns, $tp;
		list($elementType, $elementID) = explode(".", e_QUERY);
		if($elementType == 'textarea')
		{
			$job = "
			curval = parent.opener.document.getElementById('{$elementID}').value;
			lastchr = curval.substring(curval.length-1, curval.length);
			if(lastchr != '\\n' && curval.length > 0)
			{
				curval = curval+'\\n';
			}
			parent.opener.document.getElementById('{$elementID}').value = curval+d+'\\n';";
		}
		else
		{
			if($elementID == "")
			{
				$elementID = $elementType;
			}
			$job = "parent.opener.document.getElementById('{$elementID}').value = d;";
		}
		
		// send the charset to the browser - overrides spurious server settings with the lan pack settings.
		header("Content-type: text/html; charset=utf-8", TRUE);
		//echo (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='utf-8' "."?".">\n")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">\n";
		//echo "<html xmlns='http://www.w3.org/1999/xhtml'".(defined("TEXTDIRECTION") ? " dir='".TEXTDIRECTION."'" : "").(defined("CORE_LC") ? " xml:lang=\"".CORE_LC."\"" : "").">
		echo "<!doctype html>
		<html lang='".CORE_LC."'>
		<head>
		<title>".SITENAME."</title>\n";

		echo "<link rel=stylesheet href='".e_WEB_ABS."js/bootstrap/css/bootstrap.min.css'>
		<link rel=stylesheet href='".THEME_ABS."style.css'>
		<script language='JavaScript' type='text/javascript'>
		<!--
		function SelectUser() {
		var d = window.document.results.usersel.value;
		{$job}
		this.close();
		}
		//-->
		</script>
		</head>
		<body>
		";

		$text = "
					<div class='col-sm-12'>
						<h1 class='text-center'>".US_LAN_4."</h1>
						<form action='".e_SELF."?".e_QUERY."' method='POST' role='form'>
							<div class='form-group text-center'>
								<label for='srch'>Search</label>
								<input type='text' name='srch' id='srch' class='tbox form-control' value='".$tp -> post_toForm(varset($_POST['srch'],''))."' size='40'>
							</div>
							<div class='form-group text-center'>
								<button class='btn btn-default btn-secondary button' type='submit' name='dosrch' class='tbox' value='".US_LAN_6."'>".US_LAN_6."</button>
							</div>
						</form>
					</div>
			";

		if (isset($_POST['dosrch'])) 
		{
			$userlist = $this -> findusers($_POST['srch']);
			if($userlist == FALSE)
			{
				$fcount= 0;
			}
			else
			{
				$fcount = count($userlist);
			}
			$text .= "<br />
			<div class='col-sm-12'>
				<form name='results' action='".e_SELF."?".e_QUERY."' method='POST'>
					<div class='form-group text-center'>
						<label for='usersel'>{$fcount} ".US_LAN_5."</label>
						<select class='tbox' name='usersel' id='usersel' width='60' ondblclick='SelectUser()'>


			";
			foreach($userlist as $u) {
				$text .= "<option value='{$u}'>{$u}";
			}
			$text .= "
						</select>
					</div>
					<div class='form-group text-center'>
						<input type='button' class='btn btn-default btn-secondary button' value='".US_LAN_1."' onClick='SelectUser()' />
					</div>
				</form>
			</div>
			";
		}
		//$ns -> tablerender(US_LAN_4, $text);
		echo $text;
		echo "\n</body>\n</html>";
	}

	function findusers($s,$banned=FALSE) {
		global $sql, $tp;
		$inc = ($banned == FALSE) ? " AND user_ban != 1" : "";
		if ($sql->db_Select("user", "*", "user_name LIKE '%".$tp -> toDB($s)."%'".$inc)) 
		{
			while ($row = $sql -> db_Fetch()) {
				$ret[strtolower($row['user_name'])] = $row['user_name'];
			}
			ksort($ret);
		} else {
			$ret = FALSE;
		}
		return $ret;
	}

}
