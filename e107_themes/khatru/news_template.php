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
 * $Source: /cvs_backup/e107_0.8/e107_themes/khatru/news_template.php,v $
 * $Revision: 1.3 $
 * $Date: 2009-11-18 01:06:02 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

$NEWSCOLUMNS = 2;

$NEWSCLAYOUT = "
<table style='width: 100%;'>
<tr>
<td style='width: 48%; vertical-align: top;'>{ITEMS1}</td>
<td style='width: 4%;'></td>
<td style='width: 48%; vertical-align: top;'>{ITEMS2}</td>
</tr>
</table>
";


?>