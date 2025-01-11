<?php
/*
* e107 website system
*
* Copyright (C) 2008-2018 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
*	gSitemap addon
*/

if (!defined('e107_INIT'))
{
	exit;
}

e107::coreLan('news');

// v2.x Standard

class news_gsitemap
{

	// Dynamically Generated Sitemap;
	function config()
	{
		$config = array();

		// Viewable from  my-website.com/news-latest-sitemap.xml  ie. plugin-folder + function + 'sitemap.xml'
		$config[] = array(
			'name'			=> "All News Posts",
			'function'		=> "allPosts",
			'sef'           => 'posts', // used in URL. eg. news-posts-sitemap.xml @see e107_plugins/gsitemap/e_url.php L45
		);

		return $config;

	}

	private function getNewsPosts()
	{
		/* public, guests */
		$userclass_list = "0,252";
		$_t = time();		/* public, quests */

        $query = "SELECT n.*, nc.category_name, nc.category_sef FROM #news AS n 
                LEFT JOIN #news_category AS nc ON n.news_category = nc.category_id 
				WHERE n.news_class IN (". $userclass_list.") AND n.news_start < ".$_t." AND (n.news_end=0 || n.news_end>".time().") ORDER BY n.news_datestamp ASC ";

	//	$data = $sql->retrieve("news", "*", "news_class IN (" . $userclass_list . ") AND news_start < " . $_t . "   ORDER BY news_datestamp ASC", true);

		return e107::getDb()->retrieve($query,true);

	}



	function import()
	{
		$import = array();
		$sql = e107::getDb();
		/* public, quests */
		$userclass_list = "0,252";
		$_t = time();
		$data = $sql->retrieve("news_category", "*", " ORDER BY category_order ASC", true);

		foreach($data as $row)
		{
			$import[] = array(
				'id'    => $row['category_id'],
				'table' => 'news_category',
				'name' => $row['category_name'],
				'url' => $this->url('news_category', $row), //  e107::getUrl()->create('news/list/category', $row, array('full' => 1)) ,
				'type' => LAN_NEWS_23,
				'class'=> 0
			);
		}

		$data = $this->getNewsPosts();


		foreach($data as $row)
		{
			$import[] = array(
				'id'    => $row['news_id'],
				'table' => 'news',
				'name' => $row['news_title'],
				'url' => $this->url('news', $row),
				'type' => ADLAN_0,
				'class' => (int) $row['news_class'],
			);
		}

		return $import;
	}



	/* Custom Function for dynamic sitemap of news posts */
	public function allPosts()
	{
		$data = $this->getNewsPosts();

		/** @var news_shortcodes $sc */
		$sc = e107::getScBatch('news');

		e107::getParser()->thumbWidth(1000);

		$ret = [];

		foreach($data as $row)
		{
			$sc->setScVar('news_item', $row);

			$imgUrl = $sc->sc_news_image(['item'=>1, 'type'=>'src']);

			$ret[] = [
				'url'       => $this->url('news', $row),
				'lastmod'   => !empty($row['news_modified']) ? $row['news_modified'] : (int) $row['news_datestamp'],
				'freq'      => 'hourly',
				'priority'  => 0.5,
				'image'     => (strpos($imgUrl, 'http') === 0) ? $imgUrl : SITEURLBASE.$sc->sc_news_image(['item'=>1, 'type'=>'src']),
			];
		}

		return $ret;

	}



	/**
	 * Used above and by gsitemap/e_event.php to update the URL when changed in news, pages etc.
	 *
	 * @param $table
	 * @param $row
	 * @return string
	 */
	function url($table, $row)
	{
		if($table === 'news_category')
		{
			 return e107::getUrl()->create('news/list/category', $row, array('full' => 1));
		}

		return e107::getUrl()->create('news/view/item', $row, array('full' => 1));
	}



}