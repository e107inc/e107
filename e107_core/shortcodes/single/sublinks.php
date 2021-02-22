<?php

// $Id$

function sublinks_shortcode($parm)
{

	$sql = e107::getDb();
	$linkstyle = varset($GLOBALS['linkstyle'], array());

	if($parm)
	{
		list($page, $cat) = explode(":", $parm);
	}

	$page = isset($page) ? $page : defset('e_PAGE');
	$cat = isset($cat) ? $cat : 1;

	require_once(e_HANDLER . "sitelinks_class.php");
	$sublinks = new sitelinks;

	if(function_exists("linkstyle"))
	{
		$style = linkstyle($linkstyle);
	}
	else
	{
		$style = array('prelink' => '', 'postlink'=>'');
	}

	$text = "\n\n<!-- Sublinks Start -->\n\n";
	$text .= varset($style['prelink']);
	if($sql->select("links", "link_id", "link_url= '{$page}' AND link_category = {$cat} LIMIT 1"))
	{
		$row = $sql->fetch();
		$parent = (int) $row['link_id'];

		$link_total = $sql->select("links", "*", "link_class IN (" . USERCLASS_LIST . ") AND link_parent={$parent} ORDER BY link_order ASC");
		while($linkInfo = $sql->fetch())
		{
			$text .= $sublinks->makeLink($linkInfo, true, $style);
		}

		$text .= varset($style['postlink']);
	}
	$text .= "\n\n<!-- Sublinks End -->\n\n";

	return $text;
}

