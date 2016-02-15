<?php

// $Id$

function sublinks_shortcode($parm)
{
	global $sql,$linkstyle;;

	if($parm){
   		list($page,$cat) = explode(":",$parm);
	}

    $page = ($page) ? $page : e_PAGE;
	$cat = ($cat) ? $cat : 1;

    require_once(e_HANDLER."sitelinks_class.php");
	$sublinks = new sitelinks;

	if(function_exists("linkstyle")){
    	$style = linkstyle($linkstyle);
	}else{
		$style="";
	}

	$text = "\n\n<!-- Sublinks Start -->\n\n";
    $text .= $style['prelink'];
    $sql->select("links", "link_id","link_url= '{$page}' AND link_category = {$cat} LIMIT 1");
    $row = $sql->fetch();
    $parent = $row['link_id'];

 	$link_total = $sql->select("links", "*", "link_class IN (".USERCLASS_LIST.") AND link_parent={$parent} ORDER BY link_order ASC");
	while($linkInfo = $sql->fetch())
	{
 		$text .= $sublinks->makeLink($linkInfo,TRUE, $style, false);
	}

    $text .= $style['postlink'];
	$text .= "\n\n<!-- Sublinks End -->\n\n";

    return $text;
}

?>