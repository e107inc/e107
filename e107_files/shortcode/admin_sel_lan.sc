//<? $Id: admin_sel_lan.sc,v 1.3 2009-08-03 18:46:05 marj_nl_fr Exp $
if (ADMIN && $pref['multilanguage'])
{
	$language = ($pref['sitelanguage'] == e_LANGUAGE) ? ADLAN_133 : e_LANGUAGE;
	return ' <strong>'.ADLAN_132.'</strong> '.$language;
}
