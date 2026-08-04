<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(!defined('e107_INIT')){ exit; }

if (!getperms("2") && !e107::isCli())
{
	e107::redirect();
	exit;
}


$sql = e107::getDb();
$tp = e107::getParser();
$frm = e107::getForm();

if(isset($_POST['reset']))
{
		for($mc=1;$mc<=5;$mc++)
		{
			$rows = $sql->createQueryBuilder()
				->select('*')->from('menus')
				->where('menu_location', (int) $mc)
				->orderBy('menu_order')
				->fetchAll();
			$count = 1;
			$sql2 = e107::getDb('sql2');
			foreach($rows as $row)
			{
				$sql2->createQueryBuilder()->update('menus')
					->set('menu_order', $count)
					->where('menu_id', (int) $row['menu_id'])->execute();
				$count++;
			}
			$text = "<b>Menus reset in database</b><br /><br />";
		}
}
else
{
	unset($text);
}

$text = "The Menu-Manager allows you to place and arrange your menus within your theme template. 

[u]Hover[/u] over the sub-areas to modify existing menu items. 

If you find the menus are not updating correctly, clicking the refresh button below may help. 

[html]
<form method='post' id='menurefresh' action='".e_SELF."'>
<div>
".$frm->admin_button('reset','Refresh','cancel')."</div>
</form>
[br]
".e107::getParser()->toGlyph('fa-search')." indicates that the menu's visibility has been modified.
[/html]
";

$text = $tp->toHTML($text, true);
e107::getRender()->tablerender("Menu Manager Help", $text);