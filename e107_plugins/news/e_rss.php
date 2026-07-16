<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	RSS news feed addon
 */
 
if (!defined('e107_INIT')) { exit; }

// v2.x Standard 
class news_rss // plugin-folder + '_rss'
{

	private $showImages         = false;
	private $summaryDescription = false;

	/**
	 * Admin RSS Configuration
	 *
	 */		
	function config() 
	{
		$config = array();

		$config[] = array(
			'name'			=> ADLAN_0,
			'url'			=> 'news',               // The identifier and plugin-path location for the rss feed url
			'topic_id'		=> '',                  // The topic_id, empty on default (to select a certain category)
			'description'	=> RSS_PLUGIN_LAN_7,     // that's 'description' not 'text'
			'class'			=> '0',
			'limit'			=> '9'
		);

		// News categories
		$rowsi = e107::getDb()->createQueryBuilder()
			->select('*')->from('news_category')
			->where('category_id', '!=', '')
			->orderBy('category_name')
			->fetchAll();
		foreach($rowsi as $rowi)
		{

				$config[] = array(
					'name'			=> ADLAN_0.' > '.$rowi['category_name'],
					'url'			=> 'news',
					'topic_id'		=> $rowi['category_id'],
					'description'	=> RSS_PLUGIN_LAN_10.' '.$rowi['category_name'], // that's 'description' not 'text'
					'class'			=> '0',
					'limit'			=> '9'
				);

			}

		return $config;
	}


	/**
	 * Generate the Feed Data
	 * @param string $parms
	 * @return array
	 */
	function data($parms=null)
	{
		$pref                       = e107::getConfig()->getPref();
		$tp                         = e107::getParser();

		$this->showImages           = vartrue($pref['rss_shownewsimage'],false);
		$this->summaryDescription   =  vartrue($pref['rss_summarydiz'],false);

		$nobody_regexp  = "(^|,)(".str_replace(",", "|", e_UC_NOBODY).")(,|$)";
		$limit          = (int) vartrue($parms['limit'],10);
		$now            = time();

		$qb = e107::getDb()->createQueryBuilder();
		$qb->select('n.*', 'u.user_id', 'u.user_name', 'u.user_email', 'u.user_customtitle', 'nc.category_name', 'nc.category_sef', 'nc.category_icon')
			->from('news', 'n')
			->leftJoin('user', 'u', $qb->expr()->compareColumns('n.news_author', 'u.user_id'))
			->leftJoin('news_category', 'nc', $qb->expr()->compareColumns('n.news_category', 'nc.category_id'))
			->whereIn('n.news_class', array_map('intval', explode(',', USERCLASS_LIST)))
			->where($qb->expr()->not($qb->expr()->regexp('n.news_class', $nobody_regexp)))
			->where('n.news_start', '<', $now)
			->where($qb->expr()->anyOf($qb->expr()->eq('n.news_end', 0), $qb->expr()->gt('n.news_end', $now)));

		if($pref['rss_othernews'] != 1)
		{
			$qb->where($qb->expr()->anyOf(
				$qb->expr()->findInSet('n.news_render_type', '0'),
				$qb->expr()->findInSet('n.news_render_type', 1)
			));
		}

		if(!empty($parms['id']) && is_numeric($parms['id']))
		{
			$qb->where('news_category', (int) $parms['id']);
		}

		$tmp = $qb->orderBy('n.news_datestamp', 'DESC')
			->setFirstResult(0)->setMaxResults($limit)
			->fetchAll();

		$rss = array();
		$i=0;
		
		foreach($tmp as $value)
		{
			$rss[$i]['title']           = $value['news_title'];
			$rss[$i]['link']            = e107::getUrl()->create('news/view/item', $value, 'full=1');
			$rss[$i]['author']          = $value['user_name'];
			$rss[$i]['author_email']    = $value['user_email'];
			$rss[$i]['category_name']   = $tp->toHTML($value['category_name'],TRUE,'defs');
			//category sef
			if (empty($value['category_sef']))
			{
				$url = SITEURL . "news.php?cat." . $value['news_category']; 
			}
			else
			{
				$category = array('id' => $value['news_category'], 'name' => $value['category_sef']);
				$opts = array('full' => 1);
				$url = e107::getUrl()->create('news/list/category', $category, $opts);
			}
			$rss[$i]['datestamp']         = $value['news_datestamp'];
			$rss[$i]['description']     = $this->getDescription($value);

			if($value['news_allow_comments'] && $pref['comments_disabled'] != 1)
			{
				$rss[$i]['comment']     = "http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?comment.news.".$value['news_id'];
			}

			$rss[$i]['media']            = $this->getMedia($value);

			$i++;
		}
	
		return $rss;
	
	}

	function getDescription($row)
	{

		$tp = e107::getParser();

		if($row['news_summary'] && $this->summaryDescription == true)
		{
			$text = $tp->toHTML($row['news_summary'],true);
		}
		else
		{

			$text= $tp->toHTML($row['news_body'],true). "<br />".$tp->toHTML($row['news_extended'], true);
		}

		if($this->showImages == true && !empty($row['news_thumbnail']))
		{
			$tmp = explode(",", $row['news_thumbnail']);

			foreach($tmp as $img)
			{

				$text .= "<br />\n";
				$text .= $tp->toImage($img, array('w'=>800,'h'=>600, 'legacy'=> "{e_IMAGE}newspost_images/"));
			}

		}

		return $text;

	}

	function getMedia($row)
	{
		$tp = e107::getParser();

		if(empty($this->showImages) ||  empty($row['news_thumbnail']))
		{
			return '';
		}

		$tmp = explode(",", $row['news_thumbnail']);

		$ret = array();

		foreach($tmp as $v)
		{

			if($tp->isImage($v))
			{
				$ret[] =  array(
					'media:content'   => array(
						'url'=>$tp->thumbUrl($v,array('w'=>800), true, true),
						'medium'=>'image',
						'value' => array('media:title'=> array('type'=>'html', 'value'=>basename($v)))

					)
				);
			}
			elseif($tp->isVideo($v))
			{
				list($code,$type) = explode(".",$v);

				if($type == 'youtube')
				{

					//TODO Needs to be verified as working.
					$ret[] = array(
						'media:player'  => array('url'=>"http://www.youtube.com/embed/".$code, 'height'=>"560", 'width'=>"315" )
					);

				}
			}
		}

		return $ret;

	}	
}
