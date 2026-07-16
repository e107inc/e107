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
	if($sql->createQueryBuilder()->select('link_id')->from('links')->where('link_url', $page)->where('link_category', (int) $cat)->limit(1)->execute())
	{
		$row = $sql->fetch();
		$parent = (int) $row['link_id'];

		$linkRows = $sql->createQueryBuilder()->select('*')->from('links')
			->whereIn('link_class', explode(',', USERCLASS_LIST))
			->where('link_parent', $parent)
			->orderBy('link_order', 'ASC')
			->fetchAll();
		foreach($linkRows as $linkInfo)
		{
			$text .= $sublinks->makeLink($linkInfo, true, $style);
		}

		$text .= varset($style['postlink']);
	}
	$text .= "\n\n<!-- Sublinks End -->\n\n";

	return $text;
}

