<?php
// $Id: newspostcat.php,v 1.1 2004-10-10 21:20:37 loloirie Exp $ 

$but_typ = array("",""); // empty = submit
$but_nam = array("update_category","category_clear"); // empty = autobutX with X autoincrement
$but_val = array(NWSLAN_55,NWSLAN_79); // empty = Submit
$but_class = array("",""); // empty = button
$butjs = array("",""); // empty = ""
$buttitle = array("",""); // empty = ""
$text .= e107ml_adpanel(1,$but_typ,$but_nam,$but_val,$but_class,$butjs,$buttitle).$rs -> form_hidden("category_id", $id);

?>
