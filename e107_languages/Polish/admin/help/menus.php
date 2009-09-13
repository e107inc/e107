<?php
/*
+-----------------------------------------------------------------------------+
|     e107 website system - Language File.
+-----------------------------------------------------------------------------+
|     Spolszczenie systemu e107 v0.7
|     Polskie wsparcie: http://e107.org.pl - http://e107poland.org
|
|     $Revision: 1.8 $
|     $Date: 2009-09-13 10:26:27 $
|     $Author: marcelis_pl $
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Polish/admin/help/menus.php,v $
+-----------------------------------------------------------------------------+
|     Zgodne z: /e107_languages/English/admin/help/menus.php rev. 1.4
+-----------------------------------------------------------------------------+
*/

if(!defined('e107_INIT')){ die("Nieautoryzowany dostęp");}
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
			$text = "<b>Menu zostały zresetowane w bazie danych</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
Na tej stronie możesz zarządzać gdzie i w jakiej kolejności będą wyświetlane Twoje menu. 
Używaj rozwijalnej listy do przenoszenia menu w górę i w dół do momentu aż będziesz zadowolony z ich rozmieszczenia.
<br />
<br />
Jeśli znalezione menu nie aktualizują się poprawnie, kliknij w przycisk <i>Odśwież</i>.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Odśwież' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> wskazuje menu, którego widoczność została zmodyfikowana</div>
";

$ns -> tablerender("Menu", $text);

?>
