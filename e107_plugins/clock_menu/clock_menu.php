<?php
/*
+---------------------------------------------------------------+
|	e107 Clock Menu
|	/clock_menu.php
|
|	Compatible with the e107 content management system
|		http://e107.org
|	
|	Originally written by jalist, modified for greater 
|	detail and cross browser compatiblity by Caveman
|	Last modified 19:11 08/04/2003
|	
|	Works with Mozilla 1.x, NS6, NS7, IE5, IE5.5, Opera 7
|	
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
$text = "\n\n<!-- ### clock ### //-->\n<div id='Clock'>&nbsp;</div>\n";
if(!$clock_flat){
	$ns -> tablerender($menu_pref['clock_caption'], "<div style='text-align:center'>".$text."</div>");
}else{
	echo $text;
}

?>
<script type="text/javascript">
<!--
var DayNam = new Array(
"<?php echo isset($LAN_407)?$LAN_407:"Sunday"; ?>","<?php echo isset($LAN_401)?$LAN_401:"Monday"; ?>","<?php echo isset($LAN_402)?$LAN_402:"Tuesday"; ?>","<?php echo isset($LAN_403)?$LAN_403:"Wednesday"; ?>","<?php echo isset($LAN_404)?$LAN_404:"Thursday"; ?>","<?php echo isset($LAN_405)?$LAN_405:"Friday"; ?>","<?php echo isset($LAN_406)?$LAN_406:"Saturday"; ?>");
var MnthNam = new Array(
"<?php echo isset($LAN_411)?$LAN_411:"January"; ?>","<?php echo isset($LAN_412)?$LAN_412:"February"; ?>","<?php echo isset($LAN_413)?$LAN_413:"March"; ?>","<?php echo isset($LAN_414)?$LAN_414:"April"; ?>","<?php echo isset($LAN_415)?$LAN_415:"May"; ?>","<?php echo isset($LAN_416)?$LAN_416:"June"; ?>","<?php echo isset($LAN_417)?$LAN_417:"July"; ?>","<?php echo isset($LAN_418)?$LAN_418:"August"; ?>","<?php echo isset($LAN_419)?$LAN_419:"September"; ?>","<?php echo isset($LAN_420)?$LAN_420:"October"; ?>","<?php echo isset($LAN_421)?$LAN_421:"November"; ?>","<?php echo isset($LAN_422)?$LAN_422:"December"; ?>");
//-->
</script>
<?php
echo "<script type='text/javascript' src='".e_PLUGIN."clock_menu/clock.js'></script>\n<!-- ### end clock ### //-->\n\n";

?>