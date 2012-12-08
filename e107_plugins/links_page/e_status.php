<?php
if (!defined('e107_INIT')) { exit; }


class links_page_status // include plugin-folder in the name.
{
	function config()
	{
		$sql = e107::getDb();
		$links_page_links = $sql->db_Count("links_page", "(*)");
		
		$var[0]['icon'] 	= E_16_LINKSPAGE;
		$var[0]['title'] 	= LCLAN_ADMIN_14;
		$var[0]['url']		= e_PLUGIN."links_page/admin_linkspage_config.php";
		$var[0]['total'] 	= $links_page_links;

		return $var;
	}	
}
?>