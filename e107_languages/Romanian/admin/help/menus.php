<?php
if(!defined('e_HTTP')){ die("Acces neautorizat");}
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
			$text = "<b>Meniuri resetate în baza de date</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
Puteţi aranja de aici unde şi în ce ordine sunt aşezate meniurile.
Folosiţi meniul dropdown pentru a muta meniurile în sus sau în jos, pâna când sunteţi mulţumit de poziţionarea lor.
<br />
<br />
Dacă vedeţi că meniurile nu se actualizează corespunzător, daţi click pe butonul de refresh.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Refresh' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> Indică faptul că vizibilitatea meniului s-a modificat</div>
";

$ns -> tablerender("Asistenţă meniuri", $text);
?>