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
|     $Source: /cvs_backup/e107_langpacks/e107_languages/Persian/admin/help/menus.php,v $
|     $Revision: 1.1 $
|     $Date: 2008-06-14 23:59:30 $
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
			$text = "<b>Menus reset in database</b><br /><br />";
		}
}else{
	unset($text);
}

$text .= "
شما می توانید در این قسمت منوهای سایت خود را مدیریت کنید. 
از منوهای پایین افتادنی استفاده کنید و عمل مورد نظر خود را انتخاب کنید.
<br />
<br />
اگر تغییرات انجام شده را مشاهده نمی کنید دکمه  Refresh را بزنید  .
<br />
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div><input type='submit' class='button' name='reset' value='Refresh' /></div>
</form>
<br />
<div class='indent'><span style='color:red'>*</span> شیوه نمایش منو اصلاح شده است</div>
";

$ns -> tablerender("راهنمای منوها", $text);
?>