<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/themes/templates/header_wanker.php
|
|	©Ricky Rivera 2002
|	http://lsof.host.sk
|	Ricky12369@host.sk
|
|	Based off "wanker" from Open Source Web Design
|	http://www.oswd.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).		|
+---------------------------------------------------------------+
*/
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title><?php echo SITENAME; ?></title>
    <link rel="stylesheet" href="<?php echo THEME; ?>style.css" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta http-equiv="content-style-type" content="text/css" />
  </head>
<body>
<?php

$ns = new table;
echo "<br />
<center>
<table cellspacing=\"0\" cellpadding=\"2\" border=\"0\" bgcolor=\"#ffffff\" width=\"98%\"><tr><td>
<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" bgcolor=\"#5A6F5A\" width=\"100%\"><tr><td>
<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" bgcolor=\"#7F907F\" width=\"100%\"><tr><td>
<table cellspacing=\"1\" cellpadding=\"0\" border=\"0\" width=\"100%\">
<tr>
		<td align=\"center\">
		<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" bgcolor=\"#7C8AA4\" width=\"100%\">
		
		<tr><td bgcolor=\"#5A6F5A\" align=\"left\"><h3>".SITENAME." // ".SITETAG."</h3></td></tr>
		<tr><td bgcolor=\"#5A6F5A\" align=\"right\" class=\"small\"><b>:.&#183;.: :'': :&#183;.: :&#183;: :::. ::''&nbsp;&nbsp;</b></td></tr>
		</table>
	</td>
</tr>
<tr>
		<td align=\"right\">
		<table cellspacing=\"1\" cellpadding=\"4\" border=\"0\" bgcolor=\"#5A6F5A\">
		<tr>";
sitelinks();
echo "		</tr>
		</table>

	
	</td>
</tr>
<tr>
		<td>
		<table cellspacing=\"1\" cellpadding=\"2\" border=\"0\" bgcolor=\"#5A6F5A\" WIDTH=\"100%\">
		<tr>
			<table bgcolor=\"#CCCCCC\" cellspacing=\"0\" cellpadding=\"0\" width=\"100%\" border=\"0\">
			<td style=\"width:20%; vertical-align: top;\">";

$sql5 = new dbFunc;
$sql5 -> dbQuery("SELECT * FROM ".MUSER."menus WHERE menu_location='1' ORDER BY menu_order");
while(list($menu_id, $menu_name, $menu_location, $menu_order) = $sql5-> dbFetch()){
	if(!eregi("menu", $menu_name)){
		if($links_display != 1){
			$menu_name();
		}
	}else{
		require_once("menus/".$menu_name.".php");
	}
}
?>
</td>
<td style="width:60%; vertical-align: top;">