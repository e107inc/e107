<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2015 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if (!defined('e107_INIT'))
{
	exit;
}

if (!e107::isInstalled('tagcloud'))
{
	return '';
}

/**
 * @example {MENU: path=tagcloud&order=size,desc}
 * @example {MENU: path=tagcloud&order=tag,asc&limit=20}
 * @example {MENU: path=tagcloud&order=tag,asc&words=2}
 */

require_once('tagcloud_class.php');


// http://lotsofcode.github.io/tag-cloud/
if(!class_exists('tagcloud_menu'))
{
	class tagcloud_menu
	{

		public $template = array();

		function __construct()
		{
			$this->template = e107::getTemplate('tagcloud', 'tagcloud_menu', 'default');
		}

		public function render($parm = null)
		{

			$cloud = new TagCloud();
			$sql = e107::getDb();
			$words = 25; // Number of words to read from each record.

			if(is_string($parm))
			{
				parse_str($parm,$parm);
			}

			if(!empty($parm['words']))
			{
				$words = (int) $parm['words'];
			}

			e107::getCache()->setMD5(e_LANGUAGE);

			if ($text = e107::getCache()->retrieve('tagcloud', 5))
			{
				return $text;
			}

			$nobody_regexp = "'(^|,)(" . str_replace(",", "|", e_UC_NOBODY) . ")(,|$)'";
			$wordCount = 0;

			if ($result = $sql->retrieve('news', 'news_id,news_meta_keywords', "news_meta_keywords !='' AND news_class REGEXP '" . e_CLASS_REGEXP . "' AND NOT (news_class REGEXP " . $nobody_regexp . ")
					AND news_start < " . time() . " AND (news_end=0 || news_end>" . time() . ")", true))
			{
				foreach ($result as $row)
				{

					$tmp = explode(",", $row['news_meta_keywords']);

					$c = 0;
					foreach ($tmp as $word)
					{

						if($c >= $words)
						{
							continue;
						}



						//$newsUrlparms = array('id'=> $row['news_id'], 'name'=>'a name');
						$url = e107::getUrl()->create('news/list/tag', array('tag' => $word)); // SITEURL."news.php?tag=".$word;
						$cloud->addTag(array('tag' => $word, 'url' => $url));
						$c++;
						$wordCount++;

					}
				}

			}
			else
			{
				$text = "No tags Found";
			}

			if(empty($wordCount))
			{
				e107::getCache()->clear('tagcloud');
				return "No Tags Found";
			}

			$cloud->setHtmlizeTagFunction(function ($tag, $size)
			{
				$tp = e107::getParser();
				$var = array('TAG_URL' => $tag['url'],
					'TAG_SIZE' => $size,
					'TAG_NAME' => $tag['tag'],
					'TAG_COUNT' => $tag['size'],
				);

				return $tp->simpleParse($this->template['item'], $var);
				//$text = "<a class='tag' href='".$tag['url']."'><span class='size".$size."'>".$tag['tag']."</span></a> ";
				
			});



			if(!empty($parm['order']))
			{
				list($o1,$o2) = explode(',', $parm['order']);
				$cloud->setOrder($o1, strtoupper($o2));
			}
			else
			{
				$cloud->setOrder('size', 'DESC');
			}

			$limit = !empty($parm['tagcloud_limit']) ? intval($parm['tagcloud_limit']) : 50;

			if(!empty($parm['limit']))
			{
				$limit = (int) $parm['limit'];
			}

			$cloud->setLimit($limit);

			$text = $cloud->render();

			e107::getCache()->set('tagcloud', $text, true);

			//$text .= "<div style='clear:both'></div>";   moved to $template['default']['end']

			return $text;

		}


	}
}

/* TODO: add template type as parm, now always default */
if(class_exists('tagcloud_menu'))
{
	$tag = new tagcloud_menu;
	if(!isset($parm))
	{
		$parm = null;
	}
	$text = $tag->render($parm);
}
else
{
	$text = '';
}

$caption = LAN_PLUGIN_TAGCLOUD_NAME;

if (!empty($parm))
{

	if (isset($parm['tagcloud_caption'][e_LANGUAGE]))
	{
		$caption = $parm['tagcloud_caption'][e_LANGUAGE];
	}
}

$var = array('TAGCLOUD_MENU_CAPTION' => $caption);

$caption = e107::getParser()->simpleParse($tag->template['caption'], $var);

$start = $tag->template['start'];
$end = $tag->template['end'];

e107::getRender()->tablerender($caption, $start . $text . $end, 'tagcloud_menu');




