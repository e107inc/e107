<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *	Event calendar - template file for list generator
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/calendar_menu/ec_pf_template.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-12-20 22:47:32 $
 * $Author: e107steved $
 */

/**
 *	e107 Event calendar plugin
 *
 *	@package	e107_plugins
 *	@subpackage	event_calendar
 *	@version 	$Id: ec_pf_template.php,v 1.3 2009-12-20 22:47:32 e107steved Exp $;
 */

/*
  Templates file for the event calendar listings (display/print/pdf).
  There can be more than one template defined, in which case they are selectable.
  There are four strings to define:
$EVENT_CAL_PDF_NAMES[] - a 'user-friendly' name/description (shown in selection box)
$EVENT_CAL_PDF_HEADER[] - the template for the header - displayed once at the top pf the list
$EVENT_CAL_PDF_BODY[]	- template for each individual entry
$EVENT_CAL_PDF_FOOTER[]	- template for a footer (to close off the list)

The array index defines the name of the template - if there is an entry in the $EVENT_CAL_PDF_NAMES[]
array, there must be a corresponding entry in each of the other three arrays.

There are two ways of managing the styling of the various shortcodes:
	a) The $sc_style array works in the usual way, and should be used where the styling is the same 
	for all templates, or where you can set a 'default' styling which applies to most uses of the shortcode
	b) An $ec_template_styles array sets styles for an individual template. This need only contain the
	styles which override a default $sc_style entry.
*/

if (!defined('e107_INIT')) { exit; }
if (!defined('USER_WIDTH')){ define('USER_WIDTH','width:auto'); }

$sc_style['EC_PR_CHANGE_YEAR']['pre'] = '<br /><em><strong>';
$sc_style['EC_PR_CHANGE_YEAR']['post'] = '</strong></em>';
$sc_style['EC_PR_CHANGE_MONTH']['pre'] = '<br /><strong>';
$sc_style['EC_PR_CHANGE_MONTH']['post'] = '</strong><br />';
$sc_style['EC_PRINT_BUTTON']['pre'] = "<br /><div style='text-align:center'>";
$sc_style['EC_PRINT_BUTTON']['post'] = "</div>";
$sc_style['EC_NOW_DATE']['pre'] = EC_LAN_170;
$sc_style['EC_NOW_DATE']['post'] = "";
$sc_style['EC_NOW_TIME']['pre'] = EC_LAN_144;
$sc_style['EC_NOW_TIME']['post'] = "";
$sc_style['EC_PR_CAT_LIST']['pre'] = EC_LAN_172;
$sc_style['EC_PR_CAT_LIST']['post'] = "";
$sc_style['EC_PR_LIST_TITLE']['pre'] = "<h3>";
$sc_style['EC_PR_LIST_TITLE']['post'] = "</h3>";

// - Default style - very basic
$EVENT_CAL_PDF_NAMES['default'] = EC_LAN_165;
$EVENT_CAL_PDF_HEADER['default'] = "{EC_PR_LIST_TITLE}<br />{EC_PR_CAT_LIST}<br />".EC_LAN_168."{EC_PR_LIST_START=%d-%m-%Y}<br />".EC_LAN_169."{EC_PR_LIST_END=%d-%m-%Y}<br />";
$EVENT_CAL_PDF_BODY['default'] = "{EC_PR_CHANGE_YEAR}{EC_PR_CHANGE_MONTH}{EC_MAIL_SHORT_DATE} {EC_MAIL_TIME_START}  {EC_MAIL_TITLE}<br />\n";
$EVENT_CAL_PDF_FOOTER['default'] = "---End of List---<br /><br />{EC_IFNOT_DISPLAY=EC_NOW_DATE}{EC_IFNOT_DISPLAY=EC_NOW_TIME}<br />{EC_PRINT_BUTTON}";


// - A simple tabular style
$ec_template_styles['simple']['EC_PR_CHANGE_YEAR']['pre'] = "<tr><td colspan='4'><em><strong><br />";
$ec_template_styles['simple']['EC_PR_CHANGE_YEAR']['post'] = '</strong></em></td></tr>';
$ec_template_styles['simple']['EC_PR_CHANGE_MONTH']['pre'] = '<strong>';
$ec_template_styles['simple']['EC_PR_CHANGE_MONTH']['post'] = '</strong>';

$EVENT_CAL_PDF_NAMES['simple'] = EC_LAN_166;
$EVENT_CAL_PDF_HEADER['simple'] = "{EC_IF_PRINT=LOGO}<table border='0px' cellspacing='10px' cellpadding='5px'>
     <colgroup> <col width='15%'><col width='10%'><col width='10%'><col width='65%'></colgroup>
	 <tr ><td colspan='4' style='text-align:center'>".EC_LAN_163."<br />".EC_LAN_168."{EC_PR_LIST_START=%d-%m-%Y}<br />".EC_LAN_169."{EC_PR_LIST_END=%d-%m-%Y}</td></tr>";
$EVENT_CAL_PDF_BODY['simple'] = "{EC_PR_CHANGE_YEAR}<tr><td>{EC_PR_CHANGE_MONTH}&nbsp;</td>
     <td>{EC_MAIL_DATE_START=%a %d}</td><td>{EC_MAIL_TIME_START}</td><td>{EC_MAIL_TITLE}</td></tr>\n";
$EVENT_CAL_PDF_FOOTER['simple'] = "</table><br /><br />{EC_IFNOT_DISPLAY=EC_NOW_DATE}{EC_IFNOT_DISPLAY=EC_NOW_TIME} <br />{EC_PRINT_BUTTON}";


// - A tabular style with lines round the cells
$ec_template_styles['tlinclines']['EC_PR_CHANGE_YEAR']['pre'] = "<tr><td colspan='3'><em><strong><br />";
$ec_template_styles['tlinclines']['EC_PR_CHANGE_YEAR']['post'] = '</strong></em></td></tr>';

$EVENT_CAL_PDF_NAMES['tlinclines'] = EC_LAN_167;
$EVENT_CAL_PDF_HEADER['tlinclines'] = "<table border='1px' cellspacing='0px' cellpadding='5px'>
     <colgroup> <col width='22%'><col width='8%'><col width='70%'></colgroup>
	 <tr ><td colspan='4' style='text-align:center'>".EC_LAN_163."<br />".EC_LAN_168."{EC_PR_LIST_START=%d-%m-%Y}<br />".EC_LAN_169."{EC_PR_LIST_END=%d-%m-%Y}<br /></td></tr>";
$EVENT_CAL_PDF_BODY['tlinclines'] = "{EC_PR_CHANGE_YEAR}<tr>
     <td>{EC_MAIL_DATE_START}</td><td>{EC_MAIL_TIME_START}</td><td>{EC_MAIL_TITLE}</td></tr>\n";
$EVENT_CAL_PDF_FOOTER['tlinclines'] = "</table><br /><br />{EC_IFNOT_DISPLAY=EC_NOW_DATE=%d-%m-%y}{EC_IFNOT_DISPLAY=EC_NOW_TIME}{EC_PRINT_BUTTON}";

// - A tabular style with lines round the cells and categories
$ec_template_styles['tlinccatlines']['EC_PR_CHANGE_YEAR']['pre'] = "<tr><td colspan='4'><em><strong><br />";
$ec_template_styles['tlinccatlines']['EC_PR_CHANGE_YEAR']['post'] = '</strong></em></td></tr>';

$EVENT_CAL_PDF_NAMES['tlinccatlines'] = EC_LAN_171;
$EVENT_CAL_PDF_HEADER['tlinccatlines'] = "<table border='1px' cellspacing='0px' cellpadding='5px'>
     <colgroup> <col width='12%'><col width='8%'><col width='18%'><col width='62%'></colgroup>
	 <tr ><td colspan='4' style='text-align:center'>".EC_LAN_163."<br />".EC_LAN_168."{EC_PR_LIST_START=%d-%m-%Y}<br />".EC_LAN_169."{EC_PR_LIST_END=%d-%m-%Y}<br /></td></tr>";
$EVENT_CAL_PDF_BODY['tlinccatlines'] = "{EC_PR_CHANGE_YEAR}<tr>
     <td>{EC_MAIL_DATE_START=%D %d %b}</td><td>{EC_MAIL_TIME_START}</td><td>{EC_MAIL_CATEGORY}</td><td>{EC_MAIL_TITLE}</td></tr>\n";
$EVENT_CAL_PDF_FOOTER['tlinccatlines'] = "</table><br /><br />{EC_IFNOT_DISPLAY=EC_NOW_DATE=%d-%m-%y}{EC_IFNOT_DISPLAY=EC_NOW_TIME}{EC_PRINT_BUTTON}";

?>