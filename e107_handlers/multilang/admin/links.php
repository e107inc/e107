<?php
// $Id: links.php,v 1.1 2004-10-10 21:20:37 loloirie Exp $ 

switch($idpanel){
  case 1:
    $but_typ = array(""); // empty = submit
		$but_nam = array("add_link"); // empty = autobutX with X autoincrement
		$but_val = array(LCLAN_27); // empty = Submit
		$but_class = array(""); // empty = button
		$butjs = array(""); // empty = ""
		$buttitle = array(""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;
    
  case 2:
    $but_typ = array(""); // empty = submit
		$but_nam = array("add_link"); // empty = autobutX with X autoincrement
		$but_val = array(LCLAN_28); // empty = Submit
		$but_class = array(""); // empty = button
		$butjs = array(""); // empty = ""
		$buttitle = array(""); // empty = ""
		$text .= e107ml_adpanel(0,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		break;
		
  default:
    $text .= "";
    break;
    
  
}

?>
