// $Id$
//<? 
if (ADMIN && $pref['multilanguage'])
{
	$language = ($pref['sitelanguage'] == e_LANGUAGE) ? ADLAN_133 : e_LANGUAGE;
	return ' <strong>'.ADLAN_132.'</strong> '.$language;
}
