<?php

function r_userclass($fieldname, $curval=0, $mode="off"){
	$sql = new db;
	$text="<select class='tbox' name='{$fieldname}'>\n";
	($curval==e_UC_PUBLIC) ? $s=" selected" : $s="";
	$text.="<option  value='".e_UC_PUBLIC."' ".$s.">Everyone (public)\n";
	($curval==e_UC_GUEST) ? $s=" selected" : $s="";
	$text.="<option  value='".e_UC_GUEST."' ".$s.">Guests Only \n";
	($curval==e_UC_NOBODY) ? $s=" selected" : $s="";
	$text.="<option value='".e_UC_NOBODY."' ".$s.">No one (Inactive)\n";
	($curval==e_UC_MEMBER) ? $s=" selected" : $s="";
	$text.="<option value='".e_UC_MEMBER."' ".$s.">Members only\n";
	if($sql -> db_Select("userclass_classes")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			($userclass_id==$curval) ? $s=" selected" : $s="";
			$text .= "<option value='$userclass_id' ".$s.">".$userclass_name ."\n";
		}
	}
	if($mode != "off"){
		($curval==e_UC_READONLY) ? $s=" selected" : $s="";
		$text.="<option  value='".e_UC_READONLY."' ".$s.">Read only\n";
		($curval==e_UC_ADMIN) ? $s=" selected" : $s="";
		$text.="<option  value='".e_UC_ADMIN."' ".$s.">Admin only\n";
	}
	$text.="</select>\n";
	return $text;
}

function r_userclass_radio($fieldname,$curval=0){
	$sql = new db;
	($curval==e_UC_PUBLIC) ? $c=" checked" : $c="";
	$text="<input type='radio' name='{$fieldname}' value='".e_UC_PUBLIC."' ".$c.">Everyone (public)<br />";
	($curval==e_UC_NOBODY) ? $c=" checked" : $c="";
	$text.="<input type='radio' name='{$fieldname}' value='".e_UC_NOBODY."' ".$c.">No one (Inactive)<br />";	
	($curval==e_UC_GUEST) ? $c=" checked" : $c="";
	$text.="<input type='radio' name='{$fieldname}' value='".e_UC_GUEST."' ".$c.">Guests Only<br />";
	($curval==e_UC_MEMBER) ? $c=" checked" : $c="";
	$text.="<input type='radio' name='{$fieldname}' value='".e_UC_MEMBER."' ".$c.">Members only<br />";
	if($sql -> db_Select("userclass_classes")){
		while($row = $sql -> db_Fetch()){
			extract($row);
			($userclass_id==$curval) ? $c=" checked" : $c="";
			$text .= "<input type='radio' name='{$fieldname}' value='$userclass_id' ".$c.">".$userclass_name ."<br />";
		}
	}
	return $text;
}

?>