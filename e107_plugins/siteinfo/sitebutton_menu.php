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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/siteinfo/sitebutton_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }
echo "parm=".$parm; //FIXME - just for testing only.
$ns->tablerender(SITEBUTTON_MENU_L1, "<div style='text-align:center'>\n<a href='".SITEURL."'><img src='".(strstr(SITEBUTTON, "http:") ? SITEBUTTON : e_IMAGE_ABS.SITEBUTTON)."' alt='".SITEBUTTON_MENU_L1."' style='border: 0px; width: 88px; height: 31px' /></a>\n</div>", 'sitebutton');
