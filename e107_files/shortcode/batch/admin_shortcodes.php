<?php
/*
* Copyright e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: admin_shortcodes.php,v 1.1 2009-01-09 01:12:17 mcfly_e107 Exp $
*
* Admin shortcode batch - registration
*/
if (!defined('e107_INIT')) { exit; }

$codes = array(
'admin_alt_nav',
'admin_credits',
'admin_docs',
'admin_help',
'admin_icon',
'admin_lang',
'admin_latest',
'admin_log',
'admin_logged',
'admin_logo',
'admin_menu',
'admin_msg',
'admin_nav',
'admin_plugins',
'admin_preset',
'admin_pword',
'admin_sel_lan',
'admin_siteinfo',
'admin_status',
'admin_update',
'admin_userlan',
);

register_shortcode('admin_shortcodes', $codes, e_FILE.'shortcode/batch/admin_shortcodes_class.php');
?>