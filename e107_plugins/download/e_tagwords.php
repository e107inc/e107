<?php

if (!defined('e107_INIT')) { exit; }

class e_tagwords_download
{
	function __construct()
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

		$qb = $sql->createQueryBuilder();
		$row = $qb
			->select('d.*')->from('download', 'd')
			->where('d.download_id', (int) $id)
			->andWhere($qb->expr()->regexp('d.download_class', e_CLASS_REGEXP))
			->fetchRow();

		if($row)
		{
			$this->row = $row;
		}
		return $this->row;
	}
}

