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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/userclass_class.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-01-27 19:52:29 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/
@include_once(e_LANGUAGEDIR.e_LANGUAGE."/lan_userclass.php");
@include_once(e_LANGUAGEDIR."English/lan_userclass.php");
	
/*
With $optlist you can now specify which classes are shown in the dropdown.
All or none can be included, separated by comma (or whatever).
Valid options are:
public
guest
nobody
member
readonly
admin
classes - shows all classes
matchclass - if 'classes' is set, this option will only show the classes that the user is a member of
*/
	
function r_userclass($fieldname, $curval = 0, $mode = "off", $optlist = "") {
	$sql = new db;
	$text = "<select class='tbox' name='{$fieldname}'>\n";
	if (!$optlist || preg_match("#public#", $optlist)) {
		($curval == e_UC_PUBLIC) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option  value='".e_UC_PUBLIC."' ".$s.">".UC_LAN_0."</option>\n";
	}
	 
	if (!$optlist || preg_match("#guest#", $optlist)) {
		($curval == e_UC_GUEST) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option  value='".e_UC_GUEST."' ".$s.">".UC_LAN_1."</option>\n";
	}
	if (!$optlist || preg_match("#nobody#", $optlist)) {
		($curval == e_UC_NOBODY) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option value='".e_UC_NOBODY."' ".$s.">".UC_LAN_2."</option>\n";
	}
	if (!$optlist || preg_match("#member#", $optlist)) {
		($curval == e_UC_MEMBER) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option value='".e_UC_MEMBER."' ".$s.">".UC_LAN_3."</option>\n";
	}
	if ($mode != "off" || preg_match("#admin#", $optlist)) {
		($curval == e_UC_ADMIN) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option  value='".e_UC_ADMIN."' ".$s.">".UC_LAN_5."</option>\n";
	}
	if (!$optlist || preg_match("#classes#", $optlist)) {
		if ($sql->db_Select("userclass_classes")) {
			while ($row = $sql->db_Fetch()) {
				extract($row);
				if (!preg_match("#matchclass#", $optlist) || getperms("0") || check_class($userclass_id)) {
					($userclass_id == $curval) ? $s = " selected='selected'" :
					 $s = "";
					$text .= "<option value='$userclass_id' ".$s.">".$userclass_name ."</option>\n";
				}
			}
		}
	}
	if ($mode != "off" || preg_match("#readonly#", $optlist)) {
		($curval == e_UC_READONLY) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option  value='".e_UC_READONLY."' ".$s.">".UC_LAN_4."</option>\n";
	}
	 
	$text .= "</select>\n";
	return $text;
}
	
function r_userclass_radio($fieldname, $curval = 0) {
	$sql = new db;
	($curval == e_UC_PUBLIC) ? $c = " checked" :
	 $c = "";
	$text = "<input type='radio' name='{$fieldname}' value='".e_UC_PUBLIC."' ".$c." />".UC_LAN_0."<br />";
	($curval == e_UC_NOBODY) ? $c = " checked" :
	 $c = "";
	$text .= "<input type='radio' name='{$fieldname}' value='".e_UC_NOBODY."' ".$c." />".UC_LAN_2."<br />";
	($curval == e_UC_GUEST) ? $c = " checked" :
	 $c = "";
	$text .= "<input type='radio' name='{$fieldname}' value='".e_UC_GUEST."' ".$c." />".UC_LAN_1."<br />";
	($curval == e_UC_MEMBER) ? $c = " checked" :
	 $c = "";
	$text .= "<input type='radio' name='{$fieldname}' value='".e_UC_MEMBER."' ".$c." />".UC_LAN_3."<br />";
	if ($sql->db_Select("userclass_classes")) {
		while ($row = $sql->db_Fetch()) {
			extract($row);
			($userclass_id == $curval) ? $c = " checked" :
			 $c = "";
			$text .= "<input type='radio' name='{$fieldname}' value='$userclass_id' ".$c." />".$userclass_name ."<br />";
		}
	}
	return $text;
}
	
function r_userclass_name($id) {
	$sql = new db;
	if ($sql->db_Select("userclass_classes", "userclass_name", "userclass_id=$id")) {
		extract($row = $sql->db_Fetch());
		return $userclass_name;
	} else {
		switch ($id) {
			case e_UC_PUBLIC:
			return UC_LAN_0;
			case e_UC_GUEST:
			return UC_LAN_1;
			case e_UC_NOBODY:
			return UC_LAN_2;
			case e_UC_MEMBER:
			return UC_LAN_3;
			case e_UC_READONLY:
			return UC_LAN_4;
			case e_UC_ADMIN:
			return UC_LAN_5;
		}
	}
}
	
class e_userclass {
	function class_add($cid, $uinfoArray) {
		$sql2 = new db;
		foreach($uinfoArray as $uid => $curclass) {
			if ($curclass) {
				$newarray = array_unique(array_merge(explode(',', $curclass), array($cid)));
				$new_userclass = implode(',', $newarray);
			} else {
				$new_userclass = $cid;
			}
			$sql2->db_Update('user', "user_class='{$new_userclass}' WHERE user_id={$uid}");
		}
	}
	 
	function class_remove($cid, $uinfoArray) {
		$sql2 = new db;
		foreach($uinfoArray as $uid => $curclass) {
			$newarray = array_diff(explode(',', $curclass), array('', $cid));
			if (count($newarray) > 1) {
				$new_userclass = implode(',', $newarray);
			} else {
				$new_userclass = $newarray[0];
			}
			$sql2->db_Update('user', "user_class='{$new_userclass}' WHERE user_id={$uid}");
		}
	}
}
	
?>