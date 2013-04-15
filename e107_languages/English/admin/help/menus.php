<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/menus.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if(!defined('e107_INIT')){ die("Unauthorised Access");}
if (!getperms("2")) {
	header("location:".e_BASE."index.php");
	 exit;
}

$sql = e107::getDb();
$tp = e107::getParser();

if(isset($_POST['reset']))
{
		for($mc=1;$mc<=5;$mc++)
		{
			$sql -> db_Select("menus","*", "menu_location='".$mc."' ORDER BY menu_order");
			$count = 1;
			$sql2 = new db;
			while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql-> db_Fetch())
			{
				$sql2 -> db_Update("menus", "menu_order='$count' WHERE menu_id='$menu_id' ");
				$count++;
			}
			$text = "<b>Menus reset in database</b><br /><br />";
		}
}
else
{
	unset($text);
}

$frm = e107::getForm();

$text = "The Menu-Manager allows you to place and arrange your menus within your theme template. 
[u]Hover[/u] over the sub-areas to modify existing menu items. 

If you find the menus are not updating correctly, clicking the refresh button below may help. 
[html]
<form method='post' id='menurefresh' action='".$_SERVER['PHP_SELF']."'>
<div>
".$frm->admin_button('reset','Refresh','cancel')."</div>
</form>
<div class='indent'><span class='required'><i class='icon-search icon-white'></i></span> indicates that the menu's visibility has been modified.</div>
[/html]
";

$text = $tp->toHtml($text,true);
$ns -> tablerender("Menus Help", $text);
