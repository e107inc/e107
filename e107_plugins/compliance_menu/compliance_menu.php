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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/compliance_menu/compliance_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-13 13:20:43 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
$text = "<div style='text-align:center'>
<a href='http://validator.w3.org/check/referer'><img style='border:0' src='http://www.w3.org/Icons/valid-xhtml11' alt='Valid XHTML 1.1!' height='31' width='88' /></a>
<br />
<a href='http://jigsaw.w3.org/css-validator/'><img style='border:0' src='http://jigsaw.w3.org/css-validator/images/vcss' alt='Valid CSS!' height='31' width='88' /></a>
</div>";
$caption = (file_exists(THEME."images/compliance_menu.png") ? "<img src='".THEME."images/compliance_menu.png' alt='' /> ".COMPLIANCE_L1 : COMPLIANCE_L1);
$ns -> tablerender($caption, $text, 'compliance');
?>
