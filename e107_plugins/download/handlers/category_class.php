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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/download/handlers/category_class.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!e107::isInstalled('download')) { exit(); }

class downloadCategory
{
	var $cat_tree;		// Initialised with all categories in a tree structure
	var $cat_count;		// Count visible subcats and subsubcats
	var $down_count;	// Counts total downloads


	/**
	 * downloadCategory constructor.
	 *
	 * @param int    $nest_level If 0, merges subsubcats with subcats. >0 creates full tree.
	 * @param string $load_class If non-null, assumed to be a 'class set' such as USERCLASS_LIST
	 * @param string $main_cat_load
	 * @param bool   $accum If TRUE, include file counts and sizes in superior categories
	 */
	function __construct($nest_level = 1, $load_class = USERCLASS_LIST, $main_cat_load = '', $accum = FALSE)
	{
		define("SUB_PREFIX","-->");				// Added in front of sub categories
		define("SUBSUB_PREFIX","---->");		// Added in front of sub-sub categories
		$this->cat_tree = $this->down_cat_tree($nest_level, $load_class, $main_cat_load, $accum);
	}


	/**
	 * Function returns a 'tree' of download categories, subcategories, and sub-sub-categories.
	 *
	 * @param int    $nest_level If 0, merges subsubcats with subcats. >0 creates full tree.
	 * @param string $load_cat_class If non-null, assumed to be a 'class set' such as USERCLASS_LIST
	 * @param string $main_cat_load If $main_cat_load is numeric, and the value of a 'main' category, only that main category is displayed. (Unpredictable if $main_cat_load is some other category)
	 * @param bool   $accum If TRUE, include file counts and sizes in superior categories
	 * @return array Returns empty array if nothing defined
	 */
	function down_cat_tree($nest_level = 1, $load_cat_class = USERCLASS_LIST, $main_cat_load = '', $accum = FALSE)
	{
		global $sql2;

		$catlist = array();
		$this->cat_count = 0;
		$this->down_count = 0;
		$temp2 = "";
		$temp1 = "";
		if ($load_cat_class != "")
		{
			$temp1 = " WHERE dc.download_category_class IN ({$load_cat_class}) ";
			$temp2 = "AND d.download_visible IN ({$load_cat_class}) ";
		}

		$qry = "
			SELECT dc.*,
			dc1.download_category_parent AS d_parent1, dc1.download_category_order,
			SUM(d.download_filesize) AS d_size,
			COUNT(d.download_id) AS d_count,
			MAX(d.download_datestamp) as d_last,
			SUM(d.download_requested) as d_requests
			FROM #download_category as dc
			LEFT JOIN #download_category as dc1 ON dc1.download_category_id=dc.download_category_parent
			LEFT JOIN #download_category as dc2 ON dc2.download_category_id=dc1.download_category_parent
			LEFT JOIN #download AS d on d.download_category = dc.download_category_id AND d.download_active > 0 {$temp2}
			{$temp1}
			GROUP by dc.download_category_id
			ORDER by dc2.download_category_order, dc1.download_category_order, dc.download_category_order";   // This puts main categories first, then sub-cats, then sub-sub cats

		if (!$sql2->gen($qry)) return $catlist;

		while ($row = $sql2->fetch())
		{
			$tmp = $row['download_category_parent'];
			if ($tmp == '0')
			{  // Its a main category
				if (!is_numeric($main_cat_load) || ($main_cat_load == $row['download_category_id']))
				{
					$row['subcats'] = array();
					$catlist[$row['download_category_id']] = $row;
				}
			}
			else
			{
				if (isset($catlist[$tmp]))
				{  // Sub-Category
					$this->cat_count++;
					$this->down_count += $row['d_count'];
					$catlist[$tmp]['subcats'][$row['download_category_id']] = $row;
					$catlist[$tmp]['subcats'][$row['download_category_id']]['subsubcats'] = array();
					$catlist[$tmp]['subcats'][$row['download_category_id']]['d_last_subs'] =
						$catlist[$tmp]['subcats'][$row['download_category_id']]['d_last'];
				}
				else
				{  // Its a sub-sub category
					if (isset($catlist[$row['d_parent1']]['subcats'][$tmp]))
					{
						$this->cat_count++;
						$this->down_count += $row['d_count'];
						if ($accum || ($nest_level == 0))
						{  // Add the counts into the subcategory values
							$catlist[$row['d_parent1']]['subcats'][$tmp]['d_size'] += $row['d_size'];
							$catlist[$row['d_parent1']]['subcats'][$tmp]['d_count'] += $row['d_count'];
							$catlist[$row['d_parent1']]['subcats'][$tmp]['d_requests'] += $row['d_requests'];
						}
						if ($nest_level == 0)
						{  // Reflect subcat dates in category
							if ($catlist[$row['d_parent1']]['subcats'][$tmp]['d_last'] < $row['d_last'])
								$catlist[$row['d_parent1']]['subcats'][$tmp]['d_last'] = $row['d_last'];
						}
						else
						{
							$catlist[$row['d_parent1']]['subcats'][$tmp]['subsubcats'][$row['download_category_id']] = $row;
						}
						// Separately accumulate 'last update' for subcat plus associated subsubcats
						if ($catlist[$row['d_parent1']]['subcats'][$tmp]['d_last_subs'] < $row['d_last'])
							$catlist[$row['d_parent1']]['subcats'][$tmp]['d_last_subs'] = $row['d_last'];
					}
				}
			}
		}
		return $catlist;
	}


// Rest of the class isn't actually used normally, but print_tree() might help with debug

	function print_cat($cat, $prefix,$postfix)
	{
		$text = "<tr><td>".$cat['download_category_id']."</td><td>".$cat['download_category_parent']."</td><td>";
		$text .= $prefix.htmlspecialchars($cat['download_category_name']).$postfix."</td><td>".$cat['d_size']."</td>";
		$text .= "<td>".$cat['d_count']."</td><td>".$cat['d_requests']."</td><td>".strftime('%H:%M %d-%m-%Y',$cat['d_last'])."</td>";
		$text .= "</tr>";
		return $text;
	}

	function print_tree()
	{
		echo "<table><tr><th>ID</th><th>Parent</th><th>Name</th><th>Bytes</th><th>Files</th><th>Requests</th><th>Last Download</th><tr>";
		foreach ($this->cat_tree as $thiscat)
		{  // Main categories
			$scprefix = SUB_PREFIX;
			echo $this->print_cat($thiscat,'<strong>','</strong>');
			foreach ($thiscat['subcats'] as $sc)
			{  // Sub-categories
				$sscprefix = SUBSUB_PREFIX;
				echo $this->print_cat($sc,$scprefix,'');
				foreach ($sc['subsubcats'] as $ssc)
				{  // Sub-sub categories
					echo $this->print_cat($ssc,$sscprefix,'');
				}
			}
		}
		echo "</table>";
		return;
	}

}