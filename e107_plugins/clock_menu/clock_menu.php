<?php
/*
+---------------------------------------------------------------+
| e107 Clock Menu
| /clock_menu.php
|
| Compatible with the e107 content management system
|  http://e107.org
|
| Originally written by jalist, modified for greater
| detail and cross browser compatiblity by Caveman
| Last modified 19:11 08/04/2003
|
| Works with Mozilla 1.x, NS6, NS7, IE5, IE5.5, Opera 7
|
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/

//FIXME looks pretty much to be 0.6 compatible
if (!defined('e107_INIT')) { exit(); }

e107::lan('clock_menu',e_LANGUAGE);

$menu_pref = e107::getConfig('menu')->getPref('');

$indexArray = array('clock_dateprefix','clock_format','clock_datesuffix1','clock_datesuffix2','clock_datesuffix3','clock_datesuffix4');
foreach($indexArray as $ind)
{
	if(!isset($menu_pref[$ind]))
	{
		$menu_pref[$ind]='';
	}
}

$ec_dir = e_PLUGIN.'clock_menu/';

$text = "\n\n<!-- ### clock ### //-->\n<div id='Clock'>&nbsp;</div>\n";
if (!isset($clock_flat) || !$clock_flat) {
	$ns->tablerender($menu_pref['clock_caption'], "<div style='text-align:center'>".$text."</div>", 'clock');
} else {
	echo $text;
}
?>
<script type="text/javascript">
<!--
var DayNam = new Array(
"<?php echo isset($LAN_407)?$LAN_407:"".CLOCK_MENU_L11; ?>","<?php echo isset($LAN_401)?$LAN_401:"".CLOCK_MENU_L5; ?>","<?php echo isset($LAN_402)?$LAN_402:"".CLOCK_MENU_L6; ?>","<?php echo isset($LAN_403)?$LAN_403:"".CLOCK_MENU_L7; ?>","<?php echo isset($LAN_404)?$LAN_404:"".CLOCK_MENU_L8; ?>","<?php echo isset($LAN_405)?$LAN_405:"".CLOCK_MENU_L9; ?>","<?php echo isset($LAN_406)?$LAN_406:"".CLOCK_MENU_L10; ?>");
var MnthNam = new Array(
"<?php echo isset($LAN_411)?$LAN_411:"".CLOCK_MENU_L12; ?>","<?php echo isset($LAN_412)?$LAN_412:"".CLOCK_MENU_L13; ?>","<?php echo isset($LAN_413)?$LAN_413:"".CLOCK_MENU_L14; ?>","<?php echo isset($LAN_414)?$LAN_414:"".CLOCK_MENU_L15; ?>","<?php echo isset($LAN_415)?$LAN_415:"".CLOCK_MENU_L16; ?>","<?php echo isset($LAN_416)?$LAN_416:"".CLOCK_MENU_L17; ?>","<?php echo isset($LAN_417)?$LAN_417:"".CLOCK_MENU_L18; ?>","<?php echo isset($LAN_418)?$LAN_418:"".CLOCK_MENU_L19; ?>","<?php echo isset($LAN_419)?$LAN_419:"".CLOCK_MENU_L20; ?>","<?php echo isset($LAN_420)?$LAN_420:"".CLOCK_MENU_L21; ?>","<?php echo isset($LAN_421)?$LAN_421:"".CLOCK_MENU_L22; ?>","<?php echo isset($LAN_422)?$LAN_422:"".CLOCK_MENU_L23; ?>");
//-->
</script>
<?php
echo "
<script type='text/javascript' src='".e_PLUGIN_ABS."clock_menu/clock.js'></script>

<script type=\"text/javascript\">\nwindow.setTimeout(\"tick('".$menu_pref['clock_dateprefix']."', '".$menu_pref['clock_format']."', '".$menu_pref['clock_datesuffix1']."', '".$menu_pref['clock_datesuffix2']."', '".$menu_pref['clock_datesuffix3']."', '".$menu_pref['clock_datesuffix4']."')\",150);\n</script>
<!-- ### end clock ### //-->\n\n";
?>