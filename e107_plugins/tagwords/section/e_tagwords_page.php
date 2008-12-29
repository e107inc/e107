<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_page
{
	function e_tagwords_page()
	{
		$this->settings = array();

		$this->settings['plugin'] = "";
		$this->settings['table'] = "page";
		$this->settings['db_id'] = "page_id";
		$this->settings['caption'] = "page";
	}

	function getLink($id)
	{
		global $tp;
		if($this->row=='')
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_BASE."page.php?".$this->row['page_id'];
		return "<a href='".$url."'>".$tp->toHTML($this->row['page_title'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		global $sql;

		$this->row = '';
		
		$qry = "SELECT p.*, u.user_id, u.user_name FROM #page AS p
		LEFT JOIN #user AS u ON p.page_author = u.user_id
		WHERE p.page_id='{$id}' AND p.page_class IN (".USERCLASS_LIST.") ";
		
		if($sql->db_Select_gen($qry))
		{
			$this->row=$sql->db_Fetch();
		}
		return $this->row;
	}
}

?>