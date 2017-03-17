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

if (!e107::isInstalled('tagcloud')) 
{
	return '';
}

require_once('tagcloud_class.php');


// http://lotsofcode.github.io/tag-cloud/

class tagcloud_menu
{
	
	function __construct()
	{
		
	}	
	
	function render($parm=null)
	{

		$cloud = new TagCloud();
		$sql = e107::getDb();
		
		e107::getCache()->setMD5(e_LANGUAGE);
		
		if($text = e107::getCache()->retrieve('tagcloud',5,false))
		{
			return $text;	
		}
		
		
		if($result = $sql->retrieve('news','news_id,news_meta_keywords', "news_meta_keywords !='' ", true))
		{
			foreach($result as $row)
			{
				
				$tmp = explode(",", $row['news_meta_keywords']);
				foreach($tmp as $word)
				{
					//$newsUrlparms = array('id'=> $row['news_id'], 'name'=>'a name');
					$url = e107::getUrl()->create('news/list/tag',array('tag'=>$word)); // SITEURL."news.php?tag=".$word;	
					$cloud->addTag(array('tag' => $word, 'url' => $url));
					
				}
			}
			
		}
		else
		{
			$text = "No tags Found";
		}
		
		$cloud->setHtmlizeTagFunction( function($tag, $size) 
		{
			return "<a class='tag' href='".$tag['url']."'><span class='size".$size."'>".$tag['tag']."</span></a> ";
		});
		
		$cloud->setOrder('size','DESC');

		$limit = !empty($parm['tagcloud_limit']) ? intval($parm['tagcloud_limit']) : 50;

		$cloud->setLimit($limit);
		
		$text = $cloud->render();
		
		e107::getCache()->set('tagcloud', $text, true);

		$text .= "<div style='clear:both'></div>";
		
		return $text;	
		
	}
	
	
	
}


$tag = new tagcloud_menu;
$text = $tag->render($parm);


if(!empty($parm))
{

	if(isset($parm['tagcloud_caption'][e_LANGUAGE]))
	{
		$caption = $parm['tagcloud_caption'][e_LANGUAGE];
	}
}
else
{
	$caption = LAN_PLUGIN_TAGCLOUD_NAME;
}



e107::getRender()->tablerender($caption, "<div class='tagcloud-menu'>".$text."</div>", 'tagcloud_menu');




?>