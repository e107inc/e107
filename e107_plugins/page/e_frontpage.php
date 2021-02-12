<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
*/



if (!defined('e107_INIT')) { exit; }

//v2.x spec.
class page_frontpage // include plugin-folder in the name.
{

	function config()
	{
		$sql 	= e107::getDb();
		$config = array();

	//	require_once(e_PLUGIN."page/includes/pageHelper.php");

		// Retrieve all custom pages 
		if($sql->select('page', 'page_id, page_title, page_sef, page_chapter', "menu_name IS NULL OR menu_name=''"))
		{
			$config['title'] = FRTLAN_30;

			while($row = $sql->fetch())
			{
				/*if(!empty($row['page_chapter']))
				{
					pageHelper::addSefFields($row, 'page_chapter');
					$url = e107::url('page/view', $row);
				}
				else
				{
					$url = e107::url('page/view/other', $row);
				}*/

				/**
				 * Do NOT add SEF to the 'page' value below.
				 * XXX legacy URL method uses page.php?x to 'include' the file on the frontpage.
				 * XXX Switching to sef will cause a redirect on SITEURL to the SEF url instead.
				*/
				$config['page'][] = array('page' => 'page.php?'.$row['page_id'], 'title' => $row['page_title']);
			}
		}

		return $config;
	}

}