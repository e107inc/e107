<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Banner shortcode
 *
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 */

class banner_shortcodes extends e_shortcode
{

// $parm now can be array, old campaign $parm still allowed....
	function sc_banner($parm='')
	{
		$sql = e107::getDb();
		$tp = e107::getParser();
		mt_srand ((double) microtime() * 1000000);
		$seed = mt_rand(1,2000000000);
		$time = time();
		$campaign = (is_array($parm)?$parm['campaign']:$parm);
		$query = " (banner_startdate=0 OR banner_startdate <= {$time}) AND (banner_enddate=0 OR banner_enddate > {$time}) AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased)".($campaign ? " AND banner_campaign='".$tp->toDB($campaign)."'" : '')."
		AND banner_active IN (".USERCLASS_LIST.") ";
		if($tags = e107::getRegistry('core/form/related'))
		{
			$tags_regexp = "'(^|,)(".str_replace(",", "|", $tags).")(,|$)'";
			$query .= " AND banner_keywords REGEXP ".$tags_regexp;
		}
		$query .= "	ORDER BY RAND($seed) LIMIT 1";
		if($sql->select('banner', 'banner_id, banner_image, banner_clickurl, banner_description', $query))
		{
			$row = $sql->fetch();
			return $this->renderBanner($row, $parm);
		}
		else
		{
			return '&nbsp;';
		}
	}

	// Also used by banner_menu.php 
	public function renderBanner($row, $parm = null)
	{
		$sql = e107::getDb('banner');
		$tp = e107::getParser();
		if(!$row['banner_image'])
		{
			return "<a href='".e_HTTP.'banner.php?'.$row['banner_id']."' rel='external'>".BANNERLAN_39."</a>";
		}
	
		$fileext1 = substr(strrchr($row['banner_image'], '.'), 1);
		
		$sql->update('banner', 'banner_impressions=banner_impressions+1 WHERE banner_id='.(int)$row['banner_id']);
		
		switch ($fileext1)
			{
				case 'swf':
					return  "
					<object classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\" width=\"468\" height=\"60\">\n
					<param name=\"movie\" value=\"".e_IMAGE_ABS."banners/".$row['banner_image']."\">\n
					<param name=\"quality\" value=\"high\">\n
					<param name=\"SCALE\" value=\"noborder\">\n
					<embed src=\"".e_IMAGE_ABS."banners/".$row['banner_image']."\" width=\"468\" height=\"60\" scale=\"noborder\" quality=\"high\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" type=\"application/x-shockwave-flash\"></embed>
					</object>
					";
				break;
					
				case 'html':
				case 'js':
				case 'php':			// Code - may 'echo' text, or may return it as a value
					$file_data = file_get_contents(e_IMAGE.'banners/'.$row['banner_image']);
					return $file_data;
				break;
				
				default:

						$class = empty($parm['class']) ? "e-banner img-responsive img-fluid" : $parm['class'];
						$ban_ret = $tp->toImage($row['banner_image'], array('class'=> $class , 'alt'=>basename($row['banner_image']), 'legacy'=>'{e_IMAGE}banners'));

				break;
			}
			$start = "<a class='e-tip' href='".e_HTTP.'banner.php?'.$row['banner_id']."' rel='external' title=\"".$tp->toAttribute(varset($row['banner_tooltip'],''))."\">";
			$end = '</a>';
			$text = $start.$ban_ret.$end;
	
			if(!empty($row['banner_description']))
			{
				$text .= "<div class='e-banner-description'>".$start.$tp->toHTML($row['banner_description'], true).$end. "</div>";
			}

			return $text;
	}
}

