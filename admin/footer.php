<?php
echo "<br />
<div style=\"text-align:center\">".
SITEDISCLAIMER.
"</div>";
?>
</td>
<td style="width:20%; vertical-align:top">
<?php

if(ADMIN == TRUE){

$sql -> db_Select("core", "*", "e107_name='e107' ");
$row = $sql -> db_Fetch();
$e107info = unserialize($row['e107_value']);

$obj = new convert;
$install_date = $obj->convert_date($e107info['e107_datestamp'], "long");

$text = "<b>Site</b>
<br />".
SITENAME." 
<br /><br />
<b>Head Admin</b>
<br />
<a href=\"mailto:".SITEADMINEMAIL."\">".SITEADMIN."</a>
<br />
<br />
<b>e107</b>
<br />
version ".$e107info['e107_version']. " build ".$e107info['e107_build']."
<br /><br />
<b>Theme</b>
<br />
".$themename." v".$themeversion." by ".$themeauthor." (".$themedate.")
<br />
Info: ".$themeinfo."
<br /><br />
<b>Install date</b>
<br />
".$install_date."
<br /><br />
<b>Server</b>
<br />".
 eregi_replace("PHP.*", "", $_SERVER['SERVER_SOFTWARE'])."<br />(host: ".$_SERVER['SERVER_NAME'].")
<br /><br />
<b>PHP Version</b>
<br />
".phpversion()."
<br /><br />
<b>mySQL Version</b>
<br />
".mysql_get_server_info();
//"<br /><br />Unique admin pgen: $cookiepgen";
$ns -> tablerender("Site Info", $text);

/*-----------------------------------*/


}
?>
</td>
</tr>
</table>
</div>
</div>
<br />
<br />
</body>
</html>

<?php
$sql -> db_Close();
?>