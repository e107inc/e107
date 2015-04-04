<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 */

if (!defined('e107_INIT')) { exit; }


if(file_exists(THEME.'templates/banner/banner_template.php')) // v2.x location. 
{
	require_once (THEME.'templates/banner/banner_template.php');
}
elseif(file_exists(THEME.'banner_template.php')) // v1.x location. 
{
	require_once (THEME.'banner_template.php');
}
else
{
	require_once (e_PLUGIN.'banner/banner_template.php');
}

$menu_pref = e107::getConfig('menu')->getPref('');

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
		parse_str($parm, $parms);
	}

	if(isset($parms['w']) && isset($parms['h']))
	{
		e107::getParser()->setThumbSize(intval($parms['w']), intval($parms['h']));


	}


/*


	if(isset($menu_pref['banner_campaign']) && $menu_pref['banner_campaign'])
	{
		$parms = array();
		if(strstr($menu_pref['banner_campaign'], "|"))
		{
			$campaignlist = explode('|', $menu_pref['banner_campaign']);
			$amount = ($menu_pref['banner_amount'] < 1 ? '1' : $menu_pref['banner_amount']);
			$amount = ($amount > count($campaignlist) ? count($campaignlist) : $amount);
			$keys = array_rand($campaignlist, $amount);		// If one entry, returns a single value
			if (!is_array($keys))
			{
				$keys = array($keys);
			}
			foreach ($keys as $k=>$v)
			{
				$parms[] = $campaignlist[$v];
			}
		}
		else
		{
			$parms[] = $menu_pref['banner_campaign'];
		}
		
		$txt = e107::getParser()->parseTemplate($BANNER_MENU_START,true);

		$sc = e107::getScBatch('banner');
		
		
		
		
		
		
		
		
		
		

		foreach ($parms as $parm)
		{
			$p = array('banner_campaign'=>$parm); 
			$sc->setVars($p); 
			
			$txt .= e107::getParser()->parseTemplate($BANNER_MENU_ITEM, true, $sc); 
		//	$txt .= e107::getParser()->parseTemplate("{BANNER=".$parm."}",true); 
		}
		
		$txt .= e107::getParser()->parseTemplate($BANNER_MENU_END,true);
	}

*/


if(!empty($menu_pref['banner_campaign']) && !empty($menu_pref['banner_amount']))
{
		$sc = e107::getScBatch('banner');	
		
		$ret = array(); 
		
		$head = e107::getParser()->parseTemplate($BANNER_MENU_START,true);

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
		$query .= ($parm ? " AND banner_campaign='".$tp->toDB($parm)."'" : '');
		
		$query .= "	AND banner_active IN (".USERCLASS_LIST.") ORDER BY RAND($seed) LIMIT ".intval($menu_pref['banner_amount']);
		
		if($data = $sql->retrieve('banner', 'banner_id, banner_image, banner_clickurl,banner_campaign', $query,true))
		{
			foreach($data as $k=>$row)
			{
				$var = array('BANNER' => $sc->renderBanner($row)); 
				$cat = $row['banner_campaign'];
				$ret[$cat][] = $tp->simpleParse($BANNER_MENU_ITEM, $var); 
			}			
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
?>