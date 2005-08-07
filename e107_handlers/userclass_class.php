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
|     $Revision: 1.13 $
|     $Date: 2005-08-07 14:07:30 $
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
language - list of languages.

*/

function r_userclass($fieldname, $curval = 0, $mode = "off", $optlist = "") {
	$sql = new db;
	global $pref;

	$text = "<select class='tbox' name='{$fieldname}'>\n";
	if (!$optlist || preg_match("#public#", $optlist)) {
		($curval == e_UC_PUBLIC) ? $s = " selected='selected'" : $s = "";
		$text .= "<option  value='".e_UC_PUBLIC."' ".$s.">".UC_LAN_0."</option>\n";
	}

	if (!$optlist || preg_match("#guest#", $optlist)) {
		($curval == e_UC_GUEST) ? $s = " selected='selected'" : $s = "";
		$text .= "<option  value='".e_UC_GUEST."' ".$s.">".UC_LAN_1."</option>\n";
	}
	if (!$optlist || preg_match("#nobody#", $optlist)) {
		($curval == e_UC_NOBODY) ? $s = " selected='selected'" : $s = "";
		$text .= "<option value='".e_UC_NOBODY."' ".$s.">".UC_LAN_2."</option>\n";
	}
	if (!$optlist || preg_match("#member#", $optlist)) {
		($curval == e_UC_MEMBER) ? $s = " selected='selected'" : $s = "";
		$text .= "<option value='".e_UC_MEMBER."' ".$s.">".UC_LAN_3."</option>\n";
	}
	if ($mode != "off" || preg_match("#admin#", $optlist))
	{
		($curval == e_UC_ADMIN) ? $s = " selected='selected'" : $s = "";
		$text .= "<option  value='".e_UC_ADMIN."' ".$s.">".UC_LAN_5."</option>\n";
	}
	if (!$optlist || preg_match("#classes#", $optlist))
	{
		$classList = get_userclass_list();
		foreach($classList as $row)
		{
			extract($row);
			if (!preg_match("#matchclass#", $optlist) || getperms("0") || check_class($userclass_id))
			{
				($userclass_id == $curval) ? $s = " selected='selected'" :
				 $s = "";
				$text .= "<option value='$userclass_id' ".$s.">".$userclass_name ."</option>\n";
			}
		}
	}
	if (($mode != "off" && $mode != "admin") || preg_match("#readonly#", $optlist))
	{
		($curval == e_UC_READONLY) ? $s = " selected='selected'" :
		 $s = "";
		$text .= "<option  value='".e_UC_READONLY."' ".$s.">".UC_LAN_4."</option>\n";
	}

	if (preg_match("#language#", $optlist) && $pref['multilanguage']) {
			$text .= "<option value=''> ------ </option>\n";
		$tmpl = explode(",",e_LANLIST);
        foreach($tmpl as $lang){
			$s = ($curval == $lang) ?  " selected='selected'" : "";
        	$text .= "<option  value='$lang' ".$s.">".$lang."</option>\n";
		}
	}


	$text .= "</select>\n";
	return $text;
}

function r_userclass_radio($fieldname, $curval = '')
{
	$sql = new db;
	($curval == e_UC_PUBLIC) ? $c = " checked" : $c = "";
	$text = "<input type='radio' name='{$fieldname}' value='".e_UC_PUBLIC."' ".$c." />".UC_LAN_0."<br />";
	($curval == e_UC_NOBODY) ? $c = " checked" : $c = "";
	$text .= "<input type='radio' name='{$fieldname}' value='".e_UC_NOBODY."' ".$c." />".UC_LAN_2."<br />";
	($curval == e_UC_GUEST) ? $c = " checked" : $c = "";
	$text .= "<input type='radio' name='{$fieldname}' value='".e_UC_GUEST."' ".$c." />".UC_LAN_1."<br />";
	($curval == e_UC_MEMBER) ? $c = " checked" : $c = "";
	$text .= "<input type='radio' name='{$fieldname}' value='".e_UC_MEMBER."' ".$c." />".UC_LAN_3."<br />";
	$classList = get_userclass_list();
	foreach($classList as $row)
	{
		extract($row);
		($row['userclass_id'] == $curval) ? $c = " checked" : $c = "";
		$text .= "<input type='radio' name='{$fieldname}' value='{$row['userclass_id']}' ".$c." />{$row['userclass_name']}<br />";
	}
	return $text;
}

function r_userclass_check($fieldname, $curval = '', $optlist = "")
{
	global $pref;
	$sql = new db;
	$curArray = explode(",", $curval);
	$ret = "";
	$ret .= "<div class='tbox' style='margin-left:0px;margin-right:auto;width:60%;height:58px;overflow:auto'>";
	if (!$optlist || preg_match("#public#", $optlist))
	{
		$c = (in_array(e_UC_PUBLIC, $curArray)) ? " checked='checked' " : "";
		$ret .= "<label><input type='checkbox' name='{$fieldname}[".e_UC_PUBLIC."]' value='1' {$c} /> ".UC_LAN_0."</label><br />";
	}

	if (!$optlist || preg_match("#guest#", $optlist))
	{
		$c = (in_array(e_UC_GUEST, $curArray)) ? " checked='checked' " : "";
		$ret .= "<label><input type='checkbox' name='{$fieldname}[".e_UC_GUEST."]' value='1' {$c} /> ".UC_LAN_1."</label><br />";
	}

	if (!$optlist || preg_match("#nobody#", $optlist))
	{
		$c = (in_array(e_UC_NOBODY, $curArray)) ? " checked='checked' " : "";
		$ret .= "<label><input type='checkbox' name='{$fieldname}[".e_UC_NOBODY."]' value='1' {$c} /> ".UC_LAN_2."</label><br />";
	}

	if (!$optlist || preg_match("#member#", $optlist))
	{
		$c = (in_array(e_UC_MEMBER, $curArray)) ? " checked='checked' " : "";
		$ret .= "<label><input type='checkbox' name='{$fieldname}[".e_UC_MEMBER."]' value='1' {$c} /> ".UC_LAN_3."</label><br />";
	}

	if (!$optlist || preg_match("#admin#", $optlist))
	{
		$c = (in_array(e_UC_ADMIN, $curArray)) ? " checked='checked' " : "";
		$ret .= "<label><input type='checkbox' name='{$fieldname}[".e_UC_ADMIN."]' value='1' {$c} /> ".UC_LAN_5."</label><br />";
	}

	if (!$optlist || preg_match("#readonly#", $optlist))
	{
		$c = (in_array(e_UC_READONLY, $curArray)) ? " checked='checked' " : "";
		$ret .= "<label><input type='checkbox' name='{$fieldname}[".e_UC_READONLY."]' value='1' {$c} /> ".UC_LAN_4."</label><br />";
	}

	if (!$optlist || preg_match("#classes#", $optlist))
	{
		$classList = get_userclass_list();
		foreach($classList as $row)
		{
			if (!preg_match("#matchclass#", $optlist) || getperms("0") || check_class($row['userclass_id'])) {
				$c = (in_array($row['userclass_id'], $curArray)) ? " checked='checked' " : "";
				$ret .= "<label><input type='checkbox' name='{$fieldname}[{$row['userclass_id']}]' value='1' {$c} /> {$row['userclass_name']}</label><br />";
			}
		}
	}

	if (preg_match("#language#", $optlist) && $pref['multilanguage']) {
			$ret .= "<hr />\n";
		$tmpl = explode(",",e_LANLIST);
        foreach($tmpl as $lang){
				$c = (in_array($lang, $curArray)) ? " checked='checked' " : "";
        		$ret .= "<label><input type='checkbox' name='{$fieldname}[{$lang}]'  value='1' {$c} /> {$lang}</label><br />";
		}
	}



	$ret .= "</div>";
	return $ret;
}

function get_userclass_list()
{
	global $sql;
	if($classList = getcachedvars('uclass_list'))
	{
		return $classList;
	}
	else
	{
		$sql->db_Select('userclass_classes');
		$classList = $sql->db_getList();
		cachevars('uclass_list', $classList);
		return $classList;
	}
}

function r_userclass_name($id) {
	$sql = new db;
	$class_names = getcachedvars('userclass_names');
	if(!is_array($class_names))
	{
		$class_names[e_UC_PUBLIC] = UC_LAN_0;
		$class_names[e_UC_GUEST] = UC_LAN_1;
		$class_names[e_UC_NOBODY] = UC_LAN_2;
		$class_names[e_UC_MEMBER] = UC_LAN_3;
		$class_names[e_UC_READONLY] = UC_LAN_4;
		$class_names[e_UC_ADMIN] = UC_LAN_5;
		if ($sql->db_Select("userclass_classes", "userclass_id, userclass_name"))
		{
			while($row = $sql->db_Fetch())
			{
				$class_names[$row['userclass_id']] = $row['userclass_name'];
			}
		}
		cachevars('userclass_names', $class_names);
	}
	return $class_names[$id];
}

class e_userclass {
	function class_add($cid, $uinfoArray)
	{
		$sql2 = new db;
		foreach($uinfoArray as $uid => $curclass)
		{
			if ($curclass)
			{
				$newarray = array_unique(array_merge(explode(',', $curclass), array($cid)));
				$new_userclass = implode(',', $newarray);
			}
			else
			{
				$new_userclass = $cid;
			}
			$sql2->db_Update('user', "user_class='{$new_userclass}' WHERE user_id={$uid}");
		}
	}

	function class_remove($cid, $uinfoArray)
	{
		$sql2 = new db;
		foreach($uinfoArray as $uid => $curclass)
		{
			$newarray = array_diff(explode(',', $curclass), array('', $cid));
			if (count($newarray) > 1)
			{
				$new_userclass = implode(',', $newarray);
			}
			else
			{
				$new_userclass = $newarray[0];
			}
			$sql2->db_Update('user', "user_class='{$new_userclass}' WHERE user_id={$uid}");
		}
	}

	function class_create($ulist, $class_prefix = "NEW_CLASS_", $num = 0)
	{
		global $sql;
		$varname = "uc_".$ulist;
		if($ret = getcachedvars($varname))
		{
			return $ret;
		}
		$ul = explode(",", $ulist);
		array_walk($ul, array($this, 'munge'));
		$qry = "
		SELECT user_id, user_class from #user AS u
		WHERE user_name = ".implode(" OR user_name = ", $ul);
		if($sql->db_Select_gen($qry))
		{
			while($row = $sql->db_Fetch())
			{
				$idList[$row['user_id']] = $row['user_class'];

			}
			while($sql->db_Count("userclass_classes","(*)","WHERE userclass_name = '".strtoupper($class_prefix.$num)."'"))
			{
				$num++;
			}
			$newname = strtoupper($class_prefix.$num);
			$i = 1;
			while ($sql->db_Select('userclass_classes', '*', "userclass_id='".$i."' ") && $i < 255)
			{
				$i++;
			}
			if ($i < 255)
			{
				$sql->db_Insert("userclass_classes", "{$i}, '{$newname}', 'Auto_created_class', 254");
				$this->class_add($i, $idList);
				cachevars($varname, $i);
				return $i;
			}
		}

	}

	function munge(&$value, &$key)
	{
		$value = "'".trim($value)."'";
	}
}

?>