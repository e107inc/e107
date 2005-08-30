<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/user_select_class.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-08-30 18:08:59 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined("e_THEME")) {
	require_once('../class2.php');
	$us = new user_select;
	$us -> popup();
}

class user_select {

	function user_list($class, $form_name) {
		global $pref, $sql;
		if ($class == e_UC_ADMIN) {
			$where = "user_admin = 1";
		} else if ($class == e_UC_MEMBER) {
			$where = "1";
		} else if ($class == e_UC_NOBODY) {
			return "";
		} else {
			$where = "user_class REGEXP '(^|,)(".$class.")(,|$)'";
		}

		$text = "<select class='tbox' id='user' name='user' onchange=\"uc_switch('class')\">";
		$text .= "<option value=''>Select user</option>";
		$sql -> db_Select("user", "user_name", $where." ORDER BY user_name");
		while ($row = $sql -> db_Fetch()) {
			$text .= "<option value='".$row['user_name']."'>".$row['user_name']."</option>";
		}
		$text .= "</select>";
		return $text;
	}
	
	function class_list($class, $form_name) {
		global $pref, $sql;
		$text = "<select class='tbox' id='class' name='class' onchange=\"uc_switch('user')\">";
		$text .= "<option value=''>Select user class</option>";
		if (ADMINPERMS == '0' && $class == e_UC_MEMBER) {
			$text .= "<option value='all'>All users</option>";
		}
		if ($class == e_UC_MEMBER) {
			$sql -> db_Select("userclass_classes", "userclass_id, userclass_name", "ORDER BY userclass_name", "nowhere");
			while ($row = $sql -> db_Fetch()) {
				if (check_class($row['userclass_id']) || ADMINPERMS == '0') {
					$text .= "<option value='".$row['userclass_id'].":".$row['userclass_name']."'>".$row['userclass_name']."</option>";
				}
			}
		} else {
			$sql -> db_Select("userclass_classes", "userclass_id, userclass_name", "userclass_id='".$class."' ORDER BY userclass_name");
			while ($row = $sql -> db_Fetch()) {
				$text .= "<option value='".$row['userclass_id'].":".$row['userclass_name']."'>".$row['userclass_name']."</option>";
			}
		}
		return $text;
	}
	
	function select_form($type, $user_form, $user_value = '', $class_form = false, $class_value = '', $class = false) {
		$text .= "<script type='text/javascript'>
		<!--
		function uc_switch(uctype) {
			document.getElementById(uctype).value = '';
		}
		//-->
		</script>";

		list($form_name, $form_id) = explode(",", $user_form);
		if(!$form_id){ $form_id = $form_name; }

		if ($type == 'list') {
			$text .= $this -> user_list($class, 'user');
		} else if ($type == 'popup') {
			$text .= "<input class='tbox' type='text' name='".$form_name."' id='".$form_id."' size='25' maxlength='30' value='".$user_value."'>&nbsp;";
			$text .= "<img src='".e_IMAGE_ABS."generic/".IMODE."/user_select.png' 
			style='width: 16px; height: 16px; vertical-align: top' alt='Find username...' 
			title='Find username...' onclick=\"window.open('".e_HANDLER_ABS."user_select_class.php?".$form_id."','user_search', 'toolbar=no,location=no,status=yes,scrollbars=yes,resizable=yes,width=300,height=200,left=100,top=100'); return false;\" />";
		}
		
		if ($class !== false) {
			if (($class < e_UC_NOBODY && USERCLASS) || ADMINPERMS == '0') {
				$text .= ' '.$this -> class_list($class, 'class');
			}
		}
		
		return $text;
	}
	
	function real_name($_id) {
		global $sql;
		$sql -> db_Select("user", "user_name", "user_id='".$_id."' ");
		if ($row = $sql -> db_Fetch()) {
			return $row['user_name'];
		}
	}
	
	function popup() {
		global $ns;
		echo "<link rel=stylesheet href='".THEME_ABS."style.css'>
		<script language='JavaScript' type='text/javascript'>
		<!--
		function SelectUser() {
		var d = window.document.results.usersel.value;
		parent.opener.document.getElementById('".e_QUERY."').value = d;
		this.close();
		}
		//-->
		</script>
		";

		$text = "<form action='".e_SELF."?".e_QUERY."' method='POST'>
			<table style='width:100%' class='fborder'>
			<tr>
			<td class='forumheader3' style='text-align: center'><input type='text' name='srch' class='tbox' value='".$_POST['srch']."' size='40'>
			<input class='button' type='submit' name='dosrch' class='tbox' value='Search'></td>
			</tr>
			</table>
			</form>
			";
	
		if ($_POST['dosrch']) {
			$userlist = $this -> findusers($_POST['srch']);
			if($userlist == FALSE)
			{
				$fcount= 0;
			}
			else
			{
				$fcount = count($userlist);
			}
			$text .= "<br /><form name='results' action='".e_SELF."?".e_QUERY."' method='POST'>
			<table style='width:100%' class='fborder'>
			<tr><td class='fcaption'>{$fcount} User(s) found</td></tr>
			<tr>
			<td class='forumheader3'>
			<select class='tbox' name='usersel' width='60' ondblclick='SelectUser()'>
			";
			foreach($userlist as $u) {
				$text .= "<option value='{$u}'>{$u}";
			}
			$text .= "
			</select>
			<input type='button' class='button' value='Select User' onClick='SelectUser()'>
			</td>
		 
			</tr>
			</table>
			</form>
			";
		}
	
		$ns -> tablerender('Find username', $text);
	}
	
	function findusers($s) {
		global $sql;
		if ($sql->db_Select("user", "*", "user_name LIKE '%{$s}%' ")) {
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

?>