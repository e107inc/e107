<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_download
{
	function e_tagwords_download()
	{
		$this->e107 = e107::getInstance();

		$this->settings = array();

		$this->settings['plugin'] = "";
		$this->settings['table'] = "download";
		$this->settings['db_id'] = "download_id";
		$this->settings['caption'] = "download";
	}

	function getLink($id)
	{
		if($this->row=='')
		{
			$this->row = $this->getRecord($id);
		}
		$url = e_BASE."download.php?view.".$this->row['download_id'];
		return "<a href='".$url."'>".e107::getParser()->toHTML($this->row['download_name'], TRUE, '')."</a>";
	}

	function getRecord($id)
	{
		$sql = e107::getDb();
		$this->row = '';

		$qry = "SELECT d.*
		FROM #download as d
		WHERE d.download_id='{$id}' AND d.download_class REGEXP '".e_CLASS_REGEXP."' ";

		if($sql->gen($qry))
		{
			$this->row=$sql->fetch();
		}
		return $this->row;
	}
}

