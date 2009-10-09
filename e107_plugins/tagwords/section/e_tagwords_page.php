<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_page
{
	function e_tagwords_page()
	{
		$this->e107 = e107::getInstance();

		$this->settings = array();

		$this->settings['plugin'] = "";
		$this->settings['table'] = "page";
		$this->settings['db_id'] = "page_id";
		$this->settings['caption'] = "LAN_TAG_CORE_CPAGES_1";
	}

	function getLink($id)
	{
		if($this->row=='')
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_BASE."page.php?".$this->row['page_id'];
		return "<a href='".$url."'>".$this->e107->tp->toHTML($this->row['page_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		$this->row = '';

		$qry = "SELECT p.*, u.user_id, u.user_name FROM #page AS p
		LEFT JOIN #user AS u ON p.page_author = u.user_id
		WHERE p.page_id='{$id}' AND p.page_class IN (".USERCLASS_LIST.") ";

		if($this->e107->sql->db_Select_gen($qry))
		{
			$this->row=$this->e107->sql->db_Fetch();
		}
		return $this->row;
	}
}

?>