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
 * $Source: /cvs_backup/e107_0.8/e107_languages/English/admin/help/ugflag.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

$text = "If you are upgrading e107 or just need your site to be offline for a while just tick the maintenance box and your visitors will be redirected to a page explaining the site is down for repair. After you've finished un-tick the box to return site to normal.";

$ns -> tablerender("Maintenance", $text);
