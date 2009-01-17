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
		$this->settings['caption'] = "news";
	}

	function getLink($id)
	{
		if(empty($this->row))
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_BASE."news.php?item.".$this->row['news_id'];
		return "<a href='".$url."'>".$this->e107->tp->toHTML($this->row['news_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		$this->row = '';

		$qry = "SELECT n.* 
		FROM #news as n
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