<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2012 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Cron Administration
 *
 * $URL: https://e107.svn.sourceforge.net/svnroot/e107/trunk/e107_0.8/e107_admin/cron.php $
 * $Id: cron.php 12492 2011-12-30 16:09:10Z e107steved $
 *
 */

/**
 *
 * @package     e107
 * @subpackage	frontend
 * @version     $Id: cron.php 12492 2011-12-30 16:09:10Z e107steved $
 *	Ultra-simple Image-Gallery
 */
 
 /*
  * THIS SCRIPT IS HIGHLY EXPERIMENTAL. USE AT OWN RISK. 
  * 
  */
  
  
require_once("class2.php");
if (!getperms('0'))
{
	header('location:'.e_BASE.'index.php');
	exit;
}
require_once(HEADERF);


class gallery
{
	private $catList = array();
	
	function __construct()
	{
		$this->catList = e107::getMedia()->getCategories('gallery');
		
		if(($_GET['cat']) && isset($this->catList[$_GET['cat']]))
		{
			$this->showImages($_GET['cat']);	
		}
		else
		{
			$this->listCategories();		
		}
	}
	
	function listCategories()
	{
			
		foreach($this->catList as $val)
		{
			$thumb = "<img src='".e107::getParser()->thumbUrl($val['media_cat_image'],'aw=190&ah=150')."' alt='' />";	
			$text .= "<div style='width:190px;height:180px;float:left;margin:3px;border:1px solid black;background-color:black'>
			<a href='".e_SELF."?cat=".$val['media_cat_category']."'>
			".$thumb ."
			<div style='text-align:center'><h3>".$val['media_cat_title']."</h2></a></div>
			</div>";	
		}	
		
		e107::getRender()->tablerender("Gallery",$text);
	}
	
	
	function showImages($cat)
	{
	
		$GALLERY_LIST_START = "<div class='gallery-list-start' style='clear:both'>";
		$GALLERY_LIST_ITEM = "<div class='gallery-list-item'>
		{MEDIA_THUMB}
			{MEDIA_CAPTION}
		</div>";
		
		$list = e107::getMedia()->getImages($cat);
	
		foreach($list as $row)
		{
			$thumb = "<img src='".e107::getParser()->thumbUrl($row['media_url'],'aw=190&ah=150')."' alt='' />";	
			
			$text .= "<div style='width:190px;height:180px;float:left;margin:3px;border:1px solid black;background-color:black'>
			<div>".$thumb."</div>
			<div style='display:block;text-align:center;color:white;'>".$row['media_caption']."</div>
			</div>";
			
			//TODO GET TEMPLATING TO WORK WITHOUT THE USE OF A SHORTCODE FILE. 
			//TODO Shadowbox/Popup support. 
			
			//	$sc = array("MEDIA_CAPTION" => $row['media_caption'],"MEDIA_THUMB"=> $thumb);
			//	$text .= e107::getParser()->simpleParse($GALLERY_LIST_ITEM, $sc); // NOT WORKING?
			
		}
		
		$GALLERY_LIST_END = "</div>
		<div class='gallery-list-end' style='text-align:center;clear:both'><a href='".e_SELF."'>Back to Categories</a></div>";
		
		
		$text = $GALLERY_LIST_START.$text.$GALLERY_LIST_END;
		
		e107::getRender()->tablerender("Gallery :: ".str_replace("_"," ",$cat),$text);
		
	}
	
}


new gallery;

require_once(FOOTERF);
exit;


?>