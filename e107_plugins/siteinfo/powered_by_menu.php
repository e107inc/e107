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
 * $Source: /cvs_backup/e107_0.8/e107_plugins/siteinfo/powered_by_menu.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:06:01 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

$text = "
<div style='text-align: center'>
<div class='spacer'>
<a href='http://e107.org' rel='external'><img src='".e_IMAGE_ABS."button.png' alt='e107' style='border: 0px; width: 88px; height: 31px' /></a>
</div>
<div class='spacer'>
<a href='http://php.net' rel='external'><img src='".e_IMAGE_ABS."generic/php-small-trans-light.gif' alt='PHP' style='border: 0px; width: 88px; height: 31px' /></a>
</div>
<div class='spacer'>
<a href='http://mysql.com' rel='external'><img src='".e_IMAGE_ABS."generic/poweredbymysql-88.png' alt='MySQL' style='border: 0px; width: 88px; height: 31px' /></a>
</div>
</div>";
$ns -> tablerender(POWEREDBY_L1,  $text, 'powered_by');
?>