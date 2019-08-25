<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 */

if (!defined('e107_INIT')) { exit; }
e107::lan('banner');

if(file_exists(THEME.'templates/banner/banner_template.php')) // v2.x location. 
{
	require(THEME.'templates/banner/banner_template.php'); // don't use require_once as we might use this menu in more than 1 location.
}
elseif(file_exists(THEME.'banner_template.php')) // v1.x location. 
{
	require(THEME.'banner_template.php');
}
else
{
	require(e_PLUGIN.'banner/banner_template.php');
}

$menu_pref = e107::getConfig('menu')->getPref(''); // legacy preference lookup.

if(defset('BOOTSTRAP'))
{
	$BANNER_MENU_START = $BANNER_TEMPLATE['menu']['start'];
	$BANNER_MENU_ITEM = $BANNER_TEMPLATE['menu']['item'];
	$BANNER_MENU_END = $BANNER_TEMPLATE['menu']['end']; 
}
else
{
	$BANNER_MENU_ITEM = $BANNER_MENU; 	
}


	if(!empty($parm))
	{
		if(is_string($parm)) // unserailize the v2.x e_menu.php preferences.
		{
			parse_str($parm, $parms); // if it fails, use legacy method. (query string format)
		}
		elseif(is_array($parm)) // prefs array so overwrite the legacy preference values.
		{
			if(isset($parm['banner_caption'][e_LANGUAGE]))
			{
				$parm['banner_caption'] = $parm['banner_caption'][e_LANGUAGE];
			}

			$menu_pref = $parm;


			$menu_pref['banner_campaign'] = implode("|",$menu_pref['banner_campaign']);
			unset($parm);
		}
	}



//print_a($menu_pref);


if(!empty($menu_pref['banner_campaign']) /*&& !empty($menu_pref['banner_amount'])*/)
{
		$sc = e107::getScBatch('banner');	
		
		$ret = array(); 
		
		$head = e107::getParser()->parseTemplate($BANNER_MENU_START,true);

		if(!empty($menu_pref['banner_width']))
		{
			e107::getParser()->thumbWidth($menu_pref['banner_width']);
		}


		mt_srand ((double) microtime() * 1000000);
		$seed = mt_rand(1,2000000000);
		$time = time();
	
		$tmp = explode("|", $menu_pref['banner_campaign']); 
		foreach($tmp as $v)
		{
			$filter[] = "banner_campaign=\"".$v."\""; 		
		}
	
		$query = " (banner_startdate=0 OR banner_startdate <= {$time}) AND (banner_enddate=0 OR banner_enddate > {$time}) AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased)";
		$query .= (count($filter)) ? " AND (".implode(" OR ",$filter)." ) " : ""; 
	//	$query .= ($parm ? " AND banner_campaign='".$tp->toDB($parm)."'" : '');




		$query .= "	AND banner_active IN (".USERCLASS_LIST.") ";

		$query .= " ORDER BY ";


		$ord = array();

		if($tags =	e107::getRegistry('core/form/related'))
		{
			$tags_regexp = "'(^|,)(".str_replace(",", "|", $tags).")(,|$)'";
			$ord[] = " banner_keywords REGEXP ".$tags_regexp." DESC";
		}

		$ord[] = " 	RAND($seed) ASC";

		$query .= implode(', ',$ord);

		if(!empty($menu_pref['banner_amount'])) // if empty, show unlimited
		{
			$query .= " LIMIT ".intval($menu_pref['banner_amount']);
		}
		
		if($data = $sql->retrieve('banner', 'banner_id, banner_image, banner_clickurl,banner_campaign, banner_description', $query,true))
		{
			foreach($data as $k=>$row)
			{
				$var = array('BANNER' => $sc->renderBanner($row)); 
				$cat = $row['banner_campaign'];
				$ret[$cat][] = $tp->simpleParse($BANNER_MENU_ITEM, $var); 
			}			
		}
		elseif(e_DEBUG == true && getperms('0'))
		{
			echo "no banner data";
			print_a($menu_pref);
			print_a($query);
		}
	
		$foot = e107::getParser()->parseTemplate($BANNER_MENU_END,true);

		switch ($menu_pref['banner_rendertype']) 
		{

			case 0: // All banners - no render or caption. 
				$text = "";
				foreach($ret as $cat)
				{
					foreach($cat as $val)
					{
						$text .= $head.$val.$foot; 	
					}
				}
				echo $text;
			break;

			case 1: // One menu for each campaign. 
				$text = "";
				foreach($ret as $cat)
				{
					$text = "";
					foreach($cat as $val)
					{
						$text .= $head.$val.$foot; 	
					}
					
					$ns->tablerender($menu_pref['banner_caption'], $text, 'banner-menu');
				}
			break; 


			case 3:  // one rendered menu per banner
				foreach($ret as $cat)
				{
					foreach($cat as $val)
					{
						$ns->tablerender($menu_pref['banner_caption'], $head.$val.$foot,  'banner-menu');
					}
				}
			break;


			case 2: // all campaigns/banners single menu. 
			default:
				$text = "";
				foreach($ret as $cat)
				{
					foreach($cat as $val)
					{
						$text .= $head.$val.$foot; 	
					}
				}
				$ns->tablerender($menu_pref['banner_caption'], $text, 'banner-menu');
			break;
		}
	

}