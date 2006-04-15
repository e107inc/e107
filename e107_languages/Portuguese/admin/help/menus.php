<?php
/*
+----------------------------------------------------------------------------+
|     e107 website system - PT Language File.
|
|     $Revision: 1.1 $
|     $Date: 2006-04-15 20:48:49 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
if(!defined('e107_INIT')){ die("Acesso não autorizado");}
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
			$text = "<b>Menus reiniciados na base de dados</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
Neste ecrã poderá modificar a localização e a ordem pela qual os seus menus são visualizados. Utilize o caixa drop-down para mover os menus para cima/baixo até ficar satisfeito com o seu posicionamento.
<br />
<br />
Se notar que os menus não estão a ser devidamente actualizados clique no botão 'Refrescar'.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Refrescar' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> indica que a visibilidade do menu foi alterarada</div>
";

$ns -> tablerender("Ajuda = Menus", $text);
?>