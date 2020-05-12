<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Plugin supporting file - gsitemap
 *
 * $Source: /cvs_backup/e107_0.8/gsitemap.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/
require_once("class2.php");
if(!e107::isInstalled('gsitemap'))
{ 
	e107::redirect();
	exit();
}

e107::lan('gsitemap'); 

if(e_QUERY == "show" || !empty($_GET['show']))
{
	require_once(HEADERF);

	$nfArray = $sql ->retrieve("gsitemap", "*", "gsitemap_active IN (".USERCLASS_LIST.") ORDER BY gsitemap_order ",true);

	if(deftrue('BOOTSTRAP'))
	{
		$bread = array(
			0 => array('text' => $tp->toHTML(GSLAN_Name), 'url'=> null ) // e107::url('gsitemap','index')
		);
		$text = e107::getForm()->breadcrumb($bread);
		e107::breadcrumb($bread);
	}
	else
	{
		$text = '';
	}

	$text .= "<div style='text-align:left'><ul>";

	foreach($nfArray as $nfa)
	{
		$url = (substr($nfa['gsitemap_url'],0,4)== "http")? $nfa['gsitemap_url'] : SITEURL.$tp->replaceConstants($nfa['gsitemap_url'],TRUE);
		$text .= "<li>".$tp->toHTML($nfa['gsitemap_cat'],"","defs").": <a href='".$url."'>".$tp->toHTML($nfa['gsitemap_name'],"","defs")."</a></li>\n";
	}
	$text .= "</ul></div>";

	$ns -> tablerender(GSLAN_Name."", $text);

	require_once(FOOTERF);
	exit;
}

header('Content-type: application/xml', TRUE);
$xml = "<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";

$smArray = $sql ->retrieve("gsitemap", "*", "gsitemap_active IN (".USERCLASS_LIST.") ORDER BY gsitemap_order ",true);

foreach($smArray as $sm)
{
	if($sm['gsitemap_url'][0] == '/') $sm['gsitemap_url'] = ltrim($sm['gsitemap_url'], '/');
	$loc = (substr($sm['gsitemap_url'],0,4)== "http")? $sm['gsitemap_url'] : SITEURL.$tp->replaceConstants($sm['gsitemap_url'],TRUE);
	$xml .= "
	<url>
		<loc>".$loc."</loc>
		<lastmod>".get_iso_8601_date($sm['gsitemap_lastmod'])."</lastmod>
    		<changefreq>".$sm['gsitemap_freq']."</changefreq>
    		<priority>".$sm['gsitemap_priority']."</priority>
	</url>";
}

$xml .= "
</urlset>";

echo $xml;

/* ungu at terong dot com */
function get_iso_8601_date($int_date)
{
   $date_mod = date('Y-m-d\TH:i:s', $int_date);
   $pre_timezone = date('O', $int_date);
   $time_zone = substr($pre_timezone, 0, 3).":".substr($pre_timezone, 3, 2);
   $date_mod .= $time_zone;
   return $date_mod;
}



