<?php
/*
+---------------------------------------------------------------+
|        e107 website system  Language File
|
|        $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Danish/admin/help/menus.php,v $
|        $Revision: 1.3 $
|        $Date: 2006-01-10 16:31:21 $
|        $Author: e107dk $
+---------------------------------------------------------------+
*/








if(!defined('e107_INIT')){ die("Unauthorised Access");}
if (!getperms("2")) {
	header("location:".e_BASE."index.php");
	 exit;
}
global $sql;
if(isset($_POST['reset'])){
		for($mc=1;$mc<=5;$mc++){
			$sql -> db_Select("menus","*", "menu_location='".$mc."' ORDER BY menu_order");
			$count = 1;
			$sql2 = new db;
			while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch()){
				$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
				$count++;
			}
			$text = "<b>Menuer nulstillet i databasen</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
Du kan arrangere hvor og i hvilken orden dine menuer er herfra. 
Brug pilene til at flytte menuerne op og ned indtil du er tilfreds med deres position.
<br />
<br />
Hvis menuerne ikke opdaterer korrekt klik da p&aring; Opdater knappen.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Opdater' /></div>
</form><br />
<div class='indent'><span style='color:red'>*</span> indikerer at menu synlighed er blevet &aelig;ndret</div>
";

$ns -> tablerender("Menuer Hj&aelig;lp", $text);
?>