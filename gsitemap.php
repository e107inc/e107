<?php
require_once("class2.php");

if(e_QUERY == "show")
{
	require_once(HEADERF);

	$sql -> db_Select("gsitemap", "*", "gsitemap_active='0'");
	$nfArray = $sql -> db_getList();
	$text = "<ul>";

	foreach($nfArray as $nfa)
	{
		$text .= "<li><a href='".$nfa['gsitemap_url']."'>".$nfa['gsitemap_name']."</a><br />\n";
	}
	$text .= "</ul>";

	$ns -> tablerender(SITENAME." : Sitemap", $text);

	require_once(FOOTERF);
	exit;
}


$xml = "<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns='http://www.google.com/schemas/sitemap/0.84'
xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'	xsi:schemaLocation='http://www.google.com/schemas/sitemap/0.84
http://www.google.com/schemas/sitemap/0.84/sitemap.xsd'>";

$sql -> db_Select("gsitemap", "*", "gsitemap_active='0'");
$smArray = $sql -> db_getList();
foreach($smArray as $sm)
{
	$xml .= "
	<url>
		<loc>".$tp->toRSS($sm['gsitemap_url'])."</loc>
		<lastmod>".$sm['gsitemap_lastmod']."</lastmod>
    		<changefreq>".$sm['gsitemap_freq']."</changefreq>
    		<priority>".$sm['gsitemap_priority']."</priority>
	</url>";
}

$xml .= "
</urlset>";

echo $xml;


?>