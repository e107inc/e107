<?php
// $Id: article2.php,v 1.1 2004-10-10 21:20:37 loloirie Exp $ 

switch($idpanel){
  case 1:
    $but_typ = array("",""); // empty = submit
		$but_nam = array("preview","update_article"); // empty = autobutX with X autoincrement
		$but_val = array(ARLAN_28,ADLAN_81." ".ARLAN_20); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;
  
  case 2:
    $but_typ = array("",""); // empty = submit
		$but_nam = array("preview","sa_article"); // empty = autobutX with X autoincrement
		$but_val = array(ARLAN_28,ARLAN_98); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;
    
  case 3:
    $but_typ = array("",""); // empty = submit
		$but_nam = array("preview","create_article"); // empty = autobutX with X autoincrement
		$but_val = array(ARLAN_28,ADLAN_85." ".ARLAN_20); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;
         
  case 4:
    $but_typ = array("",""); // empty = submit
		$but_nam = array("preview","update_article"); // empty = autobutX with X autoincrement
		$but_val = array(ARLAN_27,ADLAN_81." ".ARLAN_20); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;
    
  case 5:
    $but_typ = array("",""); // empty = submit
		$but_nam = array("preview","sa_article"); // empty = autobutX with X autoincrement
		$but_val = array(ARLAN_27,ARLAN_98); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;
    
  case 6:
    $but_typ = array("",""); // empty = submit
		$but_nam = array("preview","create_article"); // empty = autobutX with X autoincrement
		$but_val = array(ARLAN_27,ADLAN_85." ".ARLAN_20); // empty = Submit
		$but_class = array("",""); // empty = button
		$butjs = array("",""); // empty = ""
		$buttitle = array("",""); // empty = ""
		$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle);
		//$text .= "<br /><span class='smalltext'><input type='checkbox' class='tbox' name='update_datestamp' /> ".NWSLAN_105."</span>";
    break;      
  default:
    $text .= "";
    break;
    
  
}

?>
