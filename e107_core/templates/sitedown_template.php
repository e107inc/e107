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
 * $Source: /cvs_backup/e107_0.8/e107_themes/templates/sitedown_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

// ##### SITEDOWN TABLE -----------------------------------------------------------------
if(!isset($SITEDOWN_TABLE))
{
	$SITEDOWN_TABLE = (defined("STANDARDS_MODE") ? "" : "<?xml version='1.0' encoding='utf-8' "."?".">")."<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
	";
	$SITEDOWN_TABLE .= "
    <html xmlns='http://www.w3.org/1999/xhtml'".(defined("TEXTDIRECTION") ? " dir='".TEXTDIRECTION."'" : "").(defined("CORE_LC") ? " xml:lang=\"".CORE_LC."\"" : "").">
	<head>
		<meta http-equiv='content-type' content='text/html; charset=utf-8' />
		<meta http-equiv='content-style-type' content='text/css' />
		<link rel='icon' href='{SITEDOWN_FAVICON}' type='image/x-icon' />
		<link rel='shortcut icon' href='{SITEDOWN_FAVICON}' type='image/xicon' />
		<link rel='stylesheet' media='all' property='stylesheet' type='text/css' href='https://netdna.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css' />
		<link rel='stylesheet' media='all' property='stylesheet' type='text/css' href='https://netdna.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' />
		<link rel='stylesheet' href='{SITEDOWN_E107_CSS}' type='text/css' media='all' />
		<link rel='stylesheet' href='{SITEDOWN_THEME_CSS}' type='text/css' media='all' />
		<title>{SITEDOWN_TABLE_PAGENAME}</title>
		<style type='text/css'>
			.img-responsive { display: inline }
		</style>
	</head>
	<body class='sitedown'>
		<div class='container'>
			<div style='text-align:center '>
			<div style='text-align:center'>{LOGO: h=300}</div>
			<hr />
			<br />
			{SITEDOWN_TABLE_MAINTAINANCETEXT}
			</div>
			<div style='margin-top:100px; text-align:center'>
			{XURL_ICONS: type=facebook,twitter,youtube,flickr,vimeo,google-plus,github,instagram,linkedin&size=3x&tip-pos=bottom}
			</div>
		</div>
	</body>
	</html>";
}
// ##### ------------------------------------------------------------------------------------------

