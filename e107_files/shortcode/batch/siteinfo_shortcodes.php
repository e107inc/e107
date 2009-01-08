<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: siteinfo_shortcodes.php,v 1.1 2009-01-08 21:47:44 mcfly_e107 Exp $
*
* News shortcode batch
*/
if (!defined('e107_INIT')) { exit; }
//include_once(e_HANDLER.'shortcode_handler.php');

$codes = array('sitebutton', 'sitedisclaimer', 'sitename', 'sitedescription', 'sitetag');
register_shortcode('siteinfo_shortcodes', $codes);

class siteinfo_shortcodes
{
	function get_sitebutton()
	{
		$e107 = e107::getInstance();
		$path = ($_POST['sitebutton'] && $_POST['ajax_used']) ? $e107->tp->replaceConstants($_POST['sitebutton']) : (strstr(SITEBUTTON, 'http:') ? SITEBUTTON : e_IMAGE.SITEBUTTON);
		return "<a href='".SITEURL."'><img src='".$path."' alt=\"".SITENAME."\" style='border: 0px; width: 88px; height: 31px' /></a>";
	}

	function get_sitedisclaimer()
	{
		$e107 = e107::getInstance();
		return $e107->tp->toHtml(SITEDISCLAIMER, true, 'constants defs');
	}

	function get_sitename($parm)
	{
		return ($parm == 'link') ? "<a href='".SITEURL."' title=\"".SITENAME."\">".SITENAME."</a>" : SITENAME;
	}

	function get_sitedescription()
	{
		global $pref;
		return SITEDESCRIPTION.(defined('THEME_DESCRIPTION') && $pref['displaythemeinfo'] ? THEME_DESCRIPTION : '');
	}

	function get_sitetag()
	{
		return SITETAG;
	}

}
?>