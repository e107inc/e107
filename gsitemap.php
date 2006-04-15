<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/gsitemap.php,v $
|     $Revision: 1.6 $
|     $Date: 2006-04-15 17:44:17 $
|     $Author: e107coders $
+----------------------------------------------------------------------------+
*/
require_once("class2.php");

if (file_exists(e_PLUGIN."gsitemap/languages/gsitemap_".e_LANGUAGE.".php"))
{
	include_once(e_PLUGIN."gsitemap/languages/gsitemap_".e_LANGUAGE.".php");
}
else
{
	include_once(e_PLUGIN."gsitemap/languages/gsitemap_English.php");
}


if(e_QUERY == "show")
{
	require_once(HEADERF);

	$sql -> db_Select("gsitemap", "*", "gsitemap_active IN (".USERCLASS_LIST.") ");
	$nfArray = $sql -> db_getList();
	$text = "<ul>";

	foreach($nfArray as $nfa)
	{
		$text .= "<li>".$tp->toHTML($nfa['gsitemap_cat'],"","defs").": <a href='".$nfa['gsitemap_url']."'>".$tp->toHTML($nfa['gsitemap_name'],"","defs")."</a><br />\n";
	}
	$text .= "</ul>";

	$ns -> tablerender(SITENAME." : ".GSLAN_Name."", $text);

	require_once(FOOTERF);
	exit;
}


$xml = "<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns='http://www.google.com/schemas/sitemap/0.84'
xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'	xsi:schemaLocation='http://www.google.com/schemas/sitemap/0.84
http://www.google.com/schemas/sitemap/0.84/sitemap.xsd'>";

$sql -> db_Select("gsitemap", "*", "gsitemap_active IN (".USERCLASS_LIST.") ");
$smArray = $sql -> db_getList();
foreach($smArray as $sm)
{
	$xml .= "
	<url>
		<loc>".$tp->toRSS($sm['gsitemap_url'],TRUE)."</loc>
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


?>