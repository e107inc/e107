<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_content
{
	function e_tagwords_content()
	{
		$this->settings = array();

		$this->settings['plugin'] = "content";
		$this->settings['table'] = "pcontent";
		$this->settings['db_id'] = "content_id";
		$this->settings['caption'] = "content";
	}

	function getLink($id)
	{
		global $tp;
		if($this->row=='')
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_PLUGIN."content/content.php?content.{$this->row['content_id']}";
		return "<a href='".$url."'>".$tp->toHTML($this->row['content_heading'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		global $sql;

		$this->row = '';
		
		$qry = "SELECT c.*
		FROM #pcontent as c
		WHERE c.content_id='{$id}' AND c.content_refer !='sa' 
		AND c.content_datestamp < ".time()." AND (c.content_enddate=0 || c.content_enddate>".time().") 
		AND c.content_class REGEXP '".e_CLASS_REGEXP."' ";
		
		if($sql->db_Select_gen($qry))
		{
			$this->row=$sql->db_Fetch();
		}
		return $this->row;
	}
}

?>