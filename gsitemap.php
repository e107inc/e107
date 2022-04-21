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



class gsitemap_xml
{
	function __construct()
	{

		$items = [];

		// Gsitemap Addon.
		if(!empty($_GET['plug']) && !empty($_GET['func']))
		{
			if(!e107::isInstalled($_GET['plug']))
			{
				exit;
			}

			$obj = e107::getAddon($_GET['plug'], 'e_gsitemap');
			if($items = e107::callMethod($obj, $_GET['func']))
			{
				$this->renderXML($items);
			}

		}
		else // From Gsitemap Database Table.
		{
			$this->renderXML();
		}


	}

	/**
	 * @param $items
	 * @return void
	 */
	function renderXML($items=array())
	{
		header('Content-type: application/xml', TRUE);
		$xml = "<?xml version='1.0' encoding='UTF-8'?>
		<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9' xmlns:image='https://www.google.com/schemas/sitemap-image/1.1'>";

		if(empty($items))
		{
			$smArray = e107::getDb()->retrieve("gsitemap", "*", "gsitemap_active IN (".USERCLASS_LIST.") ORDER BY gsitemap_order ",true);
			$xml .= $this->renderXMLItems($smArray,  'gsitemap_');
		}
		else
		{
			$xml .= $this->renderXMLItems($items);
		}


		$xml .= "
		</urlset>";

		echo $xml;



	}



	function renderXMLItems($data, $prefix = '')
	{
		$tp = e107::getParser();

		$xml = '';

		foreach($data as $sm)
		{
			$url = $sm[$prefix.'url'];

			if($url[0] === '/')
			{
				 $url = ltrim($url, '/');
			}

			$loc = (strpos($url, 'http') === 0) ? $url : SITEURL.$tp->replaceConstants($url,true);
			$xml .= "
			<url>
				<loc>".$loc."</loc>";

			if(!empty($sm[$prefix.'image']))
			{
				$imgUrl = $sm[$prefix.'image'];

				if($imgUrl[0] === '/')
				{
					 $imgUrl = ltrim($imgUrl, '/');
				}

				$imgUrl = (strpos($imgUrl, 'http') === 0) ? $imgUrl : SITEURL.$tp->replaceConstants($imgUrl,true);

				$xml .= "
				<image:image>
                    <image:loc>".$imgUrl."</image:loc>
                </image:image>";
			}

			$xml .= "	
				<lastmod>".date('c', (int) $sm[$prefix.'lastmod'])."</lastmod>
		      	<changefreq>".$sm[$prefix.'freq']."</changefreq>
		      	<priority>".$sm[$prefix.'priority']."</priority>
			</url>";
		}

		return $xml;

	}

}



// HTML below.

if(e_QUERY == "show" || !empty($_GET['show']))
{
	e107::canonical('gsitemap');
	e107::route('gsitemap/index');

	require_once(HEADERF);

	$nfArray = e107::getDb()->retrieve("gsitemap", "*", "gsitemap_active IN (".USERCLASS_LIST.") ORDER BY gsitemap_order ",true);

	$tp = e107::getParser();

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

	$text .= "<div style='text-align:left' class='gsitemap'><ul>";

	foreach($nfArray as $nfa)
	{
		$url = (substr($nfa['gsitemap_url'],0,4)== "http")? $nfa['gsitemap_url'] : SITEURL.$tp->replaceConstants($nfa['gsitemap_url'],TRUE);
		$text .= "<li>".$tp->toHTML($nfa['gsitemap_cat'],"","defs").": <a href='".$url."'>".$tp->toHTML($nfa['gsitemap_name'],"","defs")."</a></li>\n";
	}
	$text .= "</ul></div>";

	e107::getRender() -> tablerender(GSLAN_Name, $text);

	require_once(FOOTERF);
	exit;
}

new gsitemap_xml;



