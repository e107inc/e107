<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_news
{	
	function e_tagwords_news()
	{
		$this->settings = array();

		$this->settings['plugin'] = "";
		$this->settings['table'] = "news";
		$this->settings['db_id'] = "news_id";
		$this->settings['caption'] = "news";
	}

	function getLink($id)
	{
		global $tp;
		if(empty($this->row))
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_BASE."news.php?item.".$this->row['news_id'];
		return "<a href='".$url."'>".$tp->toHTML($this->row['news_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		global $sql;

		$this->row = '';
		$qry = "SELECT n.* 
		FROM #news as n
		WHERE n.news_id='{$id}' 
		AND n.news_start < ".time()." 
		AND (n.news_end=0 || n.news_end>".time().") 
		AND n.news_class REGEXP '".e_CLASS_REGEXP."' ";

		if($sql->db_Select_gen($qry))
		{
			$this->row=$sql->db_Fetch();
		}
		return $this->row;
	}
}

?>