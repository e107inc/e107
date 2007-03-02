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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Dutch_formal/admin/help/menus.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-03-02 18:43:37 $
|     $Author: mijnheer $
+----------------------------------------------------------------------------+
*/

if(!defined('e107_INIT')){ die("Verboden toegang");}
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
			$text = "<b>Menu's hersteld in de database</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "U kunt instellen waar en op welke volgorde de menuobjecten op uw site worden getoond. Gebruikt u de pijltjes om de menu's omhoog en omlaag te verplaatsen.
<br />
<br />
Als u niet meteen het resultaat ziet, druk dan op de 'refresh' knop.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Refresh' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> geeft aan dat de zichtbaarheid is aangepast</div>
";

$ns -> tablerender("Menu's Help", $text);
?>
