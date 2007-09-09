<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     咎teve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_langpacks/e107_languages/ChineseTrad/admin/help/menus.php,v $
|     $Revision: 1.1 $
|     $Date: 2007-09-09 07:18:47 $
|     $Author: e107coders $
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
			$text = "<b>資料庫選單重新設定</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
您可以排列選單順序. 
可以使用下拉選單移動您的選項到您想要的位置.
<br />
<br />
如果您找到選單尚未更新請點選重新整理.
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='重新整理' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> 選單瀏覽權限已變更</div>
";

$ns -> tablerender("選單問題", $text);
?>