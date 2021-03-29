<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_files/shortcode/batch/sitedown_shortcodes.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

e107::coreLan('sitedown');

class sitedown_shortcodes extends e_shortcode
{
	function sc_sitedown_table_maintainancetext($parm=null)
	{
		$pref = e107::pref('core');
		$tp = e107::getParser();

		if(!empty($pref['maintainance_text']))
		{
			return $tp->toHTML($pref['maintainance_text'], true, 'BODY', 'admin');
		}
		else
		{
			return "<b>- ".SITENAME." ".LAN_SITEDOWN_00." -</b><br /><br />".LAN_SITEDOWN_01 ;
		}
	}


	function sc_sitedown_table_pagename($parm=null)
	{
		return PAGE_NAME;
	}


	function sc_sitedown_theme_css($parm=null)
	{
		return THEME_ABS."style.css";
	}

	function sc_sitedown_social_css($parm=null)
	{
		return e_PLUGIN_ABS."social/css/fontello.css";
	}

	function sc_sitedown_e107_css($parm=null)
	{
		return e_WEB_ABS."css/e107.css";
	}

	function sc_sitedown_favicon($parm=null)
	{
		if (file_exists(THEME."favicon.ico"))
		{
			return THEME_ABS."favicon.ico";
		}
		elseif (file_exists(e_BASE."favicon.ico"))
		{
			return SITEURL."favicon.ico";
		}
	}

}


?>