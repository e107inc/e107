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
		$url = e107::getUrl()->createCoreNews('action=extend&id='.$this->row['news_id'].'&sef='.$this->row['news_rewrite_string']);
		return "<a href='".$url."'>".$this->e107->tp->toHTML($this->row['news_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		$this->row = '';
		//FIXME - only if news mod rewrite is on
		$qry = "SELECT n.*, nr.* 
		FROM #news as n
		LEFT JOIN #news_rewrite AS nr ON n.news_id=nr.news_rewrite_source AND nr.news_rewrite_type=1
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