<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_news
{	
	function e_tagwords_news()
	{
		$this->e107 = e107::getInstance();

		$this->settings = array();

		$this->settings['plugin'] = "";
		$this->settings['table'] = "news";
		$this->settings['db_id'] = "news_id";
		$this->settings['caption'] = "LAN_TAG_CORE_NEWS_1";
	}

	function getLink($id)
	{
		if(empty($this->row))
		{
			$this->row = $this->getRecord($id);
		}
		//$url = e_BASE."news.php?item.".$this->row['news_id'];
		$url = e107::getUrl()->create('news/view/item', $this->row);
		return "<a href='".$url."'>".$this->e107->tp->toHTML($this->row['news_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		$this->row = '';
		//FIXME - only if news mod rewrite is on
		$qry = "SELECT n.*, nc.* 
		FROM #news as n
		LEFT JOIN #news_category AS nc ON n.news_category=nc.category_id
		WHERE n.news_id='{$id}' 
		AND n.news_start < ".time()." 
		AND (n.news_end=0 || n.news_end>".time().") 
		AND n.news_class REGEXP '".e_CLASS_REGEXP."' ";

		if($this->e107->sql->db_Select_gen($qry))
		{
			$this->row=$this->e107->sql->db_Fetch();
		}
		return $this->row;
	}
}

?>