<?php
// $Id: newspost.php,v 1.1 2004-10-10 21:20:37 loloirie Exp $ 
if(IsSet($_POST['preview'])){
	if($id && $sub_action != "sn" && $sub_action != "upload"){
		$but_typ = array("",""); // empty = submit
		$but_nam = array("preview","submit"); // empty = autobutX with X autoincrement
		$but_val = array(NWSLAN_24,NWSLAN_25); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
	}else{
		$but_typ = array("",""); // empty = submit
		$but_nam = array("preview","submit"); // empty = autobutX with X autoincrement
		$but_val = array(NWSLAN_24,NWSLAN_26); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
	}
}else{
	$but_typ = array("submit"); // empty = submit
	$but_nam = array("preview"); // empty = autobutX with X autoincrement
	$but_val = array(NWSLAN_27); // empty = Submit
	$but_class = array(""); // empty = button
	$butjs = array(""); // empty = ""
	$buttitle = array(""); // empty = ""
	$text .= e107ml_adpanel(0,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
}

?>
