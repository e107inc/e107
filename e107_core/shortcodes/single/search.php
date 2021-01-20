<?php


function search_shortcode($parm=null)
{

	//<?php

	global $sql, $SEARCH_SHORTCODE; // in case it is declared in a v1 theme.php file.

	e107::coreLan('search');
	e107::includeLan(e_PLUGIN . "search_menu/languages/" . e_LANGUAGE . ".php");


	$text = "";

	if(empty($SEARCH_SHORTCODE))
	{
		if(!$SEARCH_SHORTCODE = e107::getCoreTemplate('search','shortcode'))
		{
			$SEARCH_SHORTCODE = include(e107::coreTemplatePath('search'));
		}
	}

	if(empty($SEARCH_SHORTCODE))
	{
		trigger_error('$SEARCH_SHORTCODE template was empty', E_USER_NOTICE);
		return null;
	}

	$ref = array();

	$ref['all'] = 'all';
	$ref['news'] = '0';
	$ref['comments'] = 1;
	$ref['users'] = 2;
	$ref['downloads'] = 3;
	$ref['pages'] = 4;

//	$search_prefs = $sysprefs -> getArray('search_prefs');

	$search_prefs = e107::getConfig('search')->getPref();


	if(!empty($search_prefs['plug_handlers']) && is_array($search_prefs['plug_handlers']))
	{
		foreach($search_prefs['plug_handlers'] as $plug_dir => $active)
		{
			if(is_readable(e_PLUGIN . $plug_dir . "/e_search.php"))
			{
				$ref[$plug_dir] = $plug_dir;
			}
		}
	}
	if(!empty($parm) && !empty($ref[$parm]))
	{
		$page = $ref[$parm];
	}
	elseif($parm == 'all' && defined('e_PAGE') && !empty($ref[e_PAGE]))
	{
		$page = $ref[e_PAGE];
	}
	else
	{
		$page = 'all';
	}



	$sc = e107::getScBatch('search');
	$sc->wrapper('search/shortcode');

	$text .= "<form method='get' action='" . e_HTTP . "search.php'><div>\n";
	$text .= "<input type='hidden' name='t' value='$page' />\n";
	$text .= "<input type='hidden' name='r' value='0' />\n";

	if(defined('SEARCH_SHORTCODE_REF') && SEARCH_SHORTCODE_REF != '')
	{
		$text .= "<input type='hidden' name='ref' value=\"" . SEARCH_SHORTCODE_REF . "\" />\n";
	}

	$text .= e107::getParser()->parseTemplate($SEARCH_SHORTCODE, true, $sc);

	$text .= "\n</div></form>";

	return $text;


}