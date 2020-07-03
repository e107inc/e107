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

require_once('tagcloud_class.php');


// http://lotsofcode.github.io/tag-cloud/
if (!class_exists('tagcloud_menu'))
{
	class tagcloud_menu
	{

		public $template = array();

		function __construct()
		{
			$this->template = e107::getTemplate('tagcloud', 'tagcloud_menu', 'default');

		}

		function render($parm = null)
		{

			$cloud = new TagCloud();
			$sql = e107::getDb();

			e107::getCache()->setMD5(e_LANGUAGE);

			if ($text = e107::getCache()->retrieve('tagcloud', 5, false))
			{
				return $text;
			}

			$nobody_regexp = "'(^|,)(" . str_replace(",", "|", e_UC_NOBODY) . ")(,|$)'";

			if ($result = $sql->retrieve('news', 'news_id,news_meta_keywords', "news_meta_keywords !='' AND news_class REGEXP '" . e_CLASS_REGEXP . "' AND NOT (news_class REGEXP " . $nobody_regexp . ")
					AND news_start < " . time() . " AND (news_end=0 || news_end>" . time() . ")", true))
			{
				foreach ($result as $row)
				{

					$tmp = explode(",", $row['news_meta_keywords']);
					foreach ($tmp as $word)
					{
						//$newsUrlparms = array('id'=> $row['news_id'], 'name'=>'a name');
						$url = e107::getUrl()->create('news/list/tag', array('tag' => $word)); // SITEURL."news.php?tag=".$word;
						$cloud->addTag(array('tag' => $word, 'url' => $url));

					}
				}

			}
			else
			{
				$text = "No tags Found";
			}

			$cloud->setHtmlizeTagFunction(function ($tag, $size)
			{
				$tp = e107::getParser();
				$var = array('TAG_URL' => $tag['url'],
					'TAG_SIZE' => $size,
					'TAG_NAME' => $tag['tag'],
					'TAG_COUNT' => $tag['size'],
				);

				$text = $tp->simpleParse($this->template['item'], $var);
				//$text = "<a class='tag' href='".$tag['url']."'><span class='size".$size."'>".$tag['tag']."</span></a> ";

				return $text;
			});

			$cloud->setOrder('size', 'DESC');

			$limit = !empty($parm['tagcloud_limit']) ? intval($parm['tagcloud_limit']) : 50;

			$cloud->setLimit($limit);

			$text = $cloud->render();

			e107::getCache()->set('tagcloud', $text, true);

			//$text .= "<div style='clear:both'></div>";   moved to $template['default']['end']

			return $text;

		}


	}
}
/* TODO: add template type as parm, now always default */
$tag = new tagcloud_menu;
$text = $tag->render($parm);


if (!empty($parm))
{

	if (isset($parm['tagcloud_caption'][e_LANGUAGE]))
	{
		$caption = $parm['tagcloud_caption'][e_LANGUAGE];
	}
}
else
{
	$caption = LAN_PLUGIN_TAGCLOUD_NAME;
}

$var = array('TAGCLOUD_MENU_CAPTION' => $caption);

$caption = e107::getParser()->simpleParse($tag->template['caption'], $var);

$start = $tag->template['start'];
$end = $tag->template['end'];

e107::getRender()->tablerender($caption, $start . $text . $end, 'tagcloud_menu');




