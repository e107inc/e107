<?php
if (!defined('e107_INIT')) { exit; }


class links_page_latest
{
	function config()
	{
		$sql = e107::getDb();
		$submitted_links = $sql->db_Select("tmp", "*", "tmp_ip='submitted_link'");
		
		$var[0]['icon'] 	= E_16_LINKSPAGE;
		$var[0]['title'] 	= ADLAN_LAT_5;
		$var[0]['url']		= e_PLUGIN."links_page/admin_linkspage_config.php?sn";
		$var[0]['total'] 	= $submitted_links;

		return $var;
	}
}


?>