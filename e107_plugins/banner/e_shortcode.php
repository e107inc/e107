<?php
/*
* Copyright (c) e107 Inc 2009 - e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Banner shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

/**
 *	e107 Banner management plugin
 *
 *	Handles the display and sequencing of banners on web pages, including counting impressions
 *
 *	@package	e107_plugins
 *	@subpackage	banner
 *	@version 	$Id$;
 */

class banner_shortcodes // must match the plugin's folder name. ie. [PLUGIN_FOLDER]_shortcodes
{	
	function sc_banner($parm)
	{
		$e107 = e107::getInstance();
			
		$ret = '';
	
		$text = '';
		mt_srand ((double) microtime() * 1000000);
		$seed = mt_rand(1,2000000000);
		$time = time();
	
		$query = " (banner_startdate=0 OR banner_startdate <= {$time}) AND (banner_enddate=0 OR banner_enddate > {$time}) AND (banner_impurchased=0 OR banner_impressions<=banner_impurchased)".($parm ? " AND banner_campaign='".$e107->tp->toDB($parm)."'" : '')."
		AND banner_active IN (".USERCLASS_LIST.")
		ORDER BY RAND($seed) LIMIT 1";
	
		if($e107->sql->db_Select('banner', 'banner_id, banner_image, banner_clickurl', $query))
		{
			$row = $e107->sql->db_Fetch();
	
			if(!$row['banner_image'])
			{
				return "<a href='".e_HTTP.'banner.php?'.$row['banner_id']."' rel='external'>no image assigned to this banner</a>";
			}
	
			$fileext1 = substr(strrchr($row['banner_image'], '.'), 1);
			$e107->sql->db_Update('banner', 'banner_impressions=banner_impressions+1 WHERE banner_id='.(int)$row['banner_id']);
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
					$ban_ret = "<img src='".e_IMAGE_ABS.'banners/'.$row['banner_image']."' alt='".$row['banner_clickurl']."' style='border:0' />";
					break;
			}
			return "<a href='".e_HTTP.'banner.php?'.$row['banner_id']."' rel='external'>".$ban_ret.'</a>';
		}
		else
		{
			return '&nbsp;';
		}
	}
}
?>