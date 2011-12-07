<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id$
*
* Siteinfo shortcode batch
*/
if (!defined('e107_INIT')) { exit; }

class siteinfo_shortcodes // must match the folder name of the plugin. 
{
	function sc_sitebutton()
	{
		$e107 = e107::getInstance();
		$path = ($_POST['sitebutton'] && $_POST['ajax_used']) ? $e107->tp->replaceConstants($_POST['sitebutton']) : (strstr(SITEBUTTON, 'http:') ? SITEBUTTON : e_IMAGE.SITEBUTTON);
		//TODO use CSS class?
		return '<a href="'.SITEURL.'"><img src="'.$path.'" alt="'.SITENAME.'" /></a>';
	}

	function sc_sitedisclaimer()
	{
		$e107 = e107::getInstance();
		return $e107->tp->toHtml(SITEDISCLAIMER, true, 'constants defs');
	}

	function sc_sitename($parm)
	{
		return ($parm == 'link') ? "<a href='".SITEURL."' title=\"".SITENAME."\">".SITENAME."</a>" : SITENAME;
	}

	function sc_sitedescription()
	{
		global $pref;
		return SITEDESCRIPTION.(defined('THEME_DESCRIPTION') && $pref['displaythemeinfo'] ? THEME_DESCRIPTION : '');
	}

	function sc_sitetag()
	{
		return SITETAG;
	}

	function sc_logo($parm)
	{
		parse_str($parm);		// Optional {LOGO=file=file_name} or {LOGO=link=url} or {LOGO=file=file_name&link=url}
		// Paths to image file, link are relative to site base
		$tp = e107::getParser();
				
		$logopref = e107::getConfig('core')->get('sitelogo');
		$logo = $tp->replaceConstants($logopref);
	
		if(vartrue($logopref) && is_readable($logo))
		{
			$logo = $tp->replaceConstants($logopref,'abs');
			$path = $tp->replaceConstants($logopref);
		}
		elseif (isset($file) && $file && is_readable($file))
		{
			$logo = e_HTTP.$file;						// HTML path
			$path = e_BASE.$file;						// PHP path
		}
		else if (is_readable(THEME.'images/e_logo.png'))
		{
			$logo = THEME_ABS.'images/e_logo.png';		// HTML path
			$path = THEME.'images/e_logo.png';			// PHP path
		}
		else
		{
			$logo = e_IMAGE_ABS.'logo.png';				// HTML path
			$path = e_IMAGE.'logo.png';					// PHP path
		}

		$dimensions = getimagesize($path);

		$image = "<img class='logo' src='".$logo."' style='width: ".$dimensions[0]."px; height: ".$dimensions[1]."px' alt='".SITENAME."' />\n";

		if (isset($link) && $link)
		{
			if ($link == 'index')
			{
				$image = "<a href='".e_HTTP."index.php'>".$image."</a>";
			}
			else
			{
				$image = "<a href='".e_HTTP.$link."'>".$image."</a>";
			}
		}

		return $image;
	}

	function sc_theme_disclaimer($parm)
	{
		$pref = e107::getPref();
		return (defined('THEME_DISCLAIMER') && $pref['displaythemeinfo'] ? THEME_DISCLAIMER : '');
	}

}
?>