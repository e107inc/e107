//<?
global $sql,$sysprefs,$SEARCH_SHORTCODE;

e107::includeLan(e_PLUGIN."search_menu/languages/".e_LANGUAGE.".php");
$text = "";
if (!isset($SEARCH_SHORTCODE)) 
{
    if (file_exists(THEME."templates/search_template.php"))
 	{
		include(THEME."templates/search_template.php");
 	}
	elseif (file_exists(THEME."search_template.php"))
	{
			include(THEME."search_template.php");
	} 
	else 
	{
	  		include(e_CORE."templates/search_template.php");
	}
}
	$ref['all'] = 'all';
	$ref['news'] = '0';
	$ref['comments'] = 1;
	$ref['users'] = 2;
	$ref['downloads'] = 3;
	$ref['pages'] = 4;

//	$search_prefs = $sysprefs -> getArray('search_prefs');
	
	$search_prefs = e107::getConfig('search')->getPref();


	if(is_array($search_prefs['plug_handlers']))  
	{
	    foreach ($search_prefs['plug_handlers'] as $plug_dir => $active) 
	    {
			if (is_readable(e_PLUGIN.$plug_dir."/e_search.php")) 
			{
				$ref[$plug_dir] = $plug_dir;
			}
		}
	}
	if($ref[$parm]!= ''){
        $page = $ref[$parm];
    }elseif($parm='all' && $ref[e_PAGE] != ''){
    	$page = $ref[e_PAGE];
	}else{
    	$page = 'all';
	}

	$text .= "<form method='get' action='".e_HTTP."search.php'><div>\n";
	$text .= "<input type='hidden' name='t' value='$page' />\n";
	$text .= "<input type='hidden' name='r' value='0' />\n";

	if (defined('SEARCH_SHORTCODE_REF') && SEARCH_SHORTCODE_REF != '') {
	  	$text .= "<input type='hidden' name='ref' value=\"".SEARCH_SHORTCODE_REF."\" />\n";
	}

	$text .= $SEARCH_SHORTCODE;

	$text .="\n</div></form>";

 return $text;
