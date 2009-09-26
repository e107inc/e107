<?php
/*
+ ----------------------------------------------------------------------------+
|     Russian Language Pack for e107 0.7
|     $Revision: 1.3 $
|     $Date: 2009-09-26 15:53:33 $
|     $Author: yarodin $
+----------------------------------------------------------------------------+
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
			$text = "<b>Меню сброшены в базе данных</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
Здесь вы можете редактировать, где и в каком порядке будут располагаться элементы меню. 
Используйте выпадающее меню, чтобы передвигать меню вверх или вниз, пока вы не будете удовлетворены расположением.
<br />
<br />
Если вы обнаружите, что меню не обновляются надлежащим образом, нажмите кнопку 'Сбросить'.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Сбросить' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> указывает на то, что видимость меню была изменена</div>
";

$ns -> tablerender("Меню: Справка", $text);
?>