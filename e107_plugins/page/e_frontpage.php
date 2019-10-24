<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/

/*
if($sql->db_Select('page', 'page_id, page_title', "menu_name=''")) 
{
	$front_page['custom']['title'] = FRTLAN_30;
	while($row = $sql->db_Fetch())
	{
		$front_page['custom']['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']);
	}
}
*/

if (!defined('e107_INIT')) { exit; }

//v2.x spec.
class page_frontpage // include plugin-folder in the name.
{

	function config()
	{
		$sql 	= e107::getDb();
		$config = array();

		// Retrieve all custom pages 
		if($sql->select('page', 'page_id, page_title, page_sef', "menu_name IS NULL OR menu_name=''"))
		{
			$config['title'] = FRTLAN_30;

			while($row = $sql->fetch())
			{
				$config['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']); // TODO SEF URL
			}
		}

		return $config;
	}

}