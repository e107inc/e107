<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_plugins/featurebox/templates/centered.php,v $
|     $Revision: 1.1.1.1 $
|     $Date: 2006-12-02 04:35:10 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$FB_TEMPLATE = "
<div class='defaulttext' style='text-align: center;'><b>$fb_title</b>
<hr />
$fb_text
</div>
<br /><br />
";

?>