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

if(file_exists(e_ADMIN."ver.php")){ require_once(e_ADMIN."ver.php"); }

$obj = new convert;
$install_date = $obj->convert_date($e107info['e107_datestamp'], "long");

$plugpath = e_PLUGIN.substr(strrchr(substr(e_SELF, 0, strrpos(e_SELF, "/")), "/"), 1)."/admin_menu.php"; 
if(file_exists($plugpath)){
	require_once($plugpath);
}

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
version ".$e107info['e107_version']. ($e107info['e107_build'] ? " build ".$e107info['e107_build'] : "")."
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
$ns -> tablerender("Site Info", $text);

$c=1;
$handle=opendir(e_DOCS);
while ($file = readdir($handle)){
	if($file != "." && $file != ".."){
		$helplist[$c] = $file;
		$c++;
	}
}
closedir($handle);

if($pref['cachestatus']){
	if(!$sql -> db_Select("tmp", "*", " tmp_ip='var_store' && tmp_time='1' ")){		// var_store 1 == cache empty time
		$sql -> db_Insert("tmp", "'var_store', 1, '".$e107info['e107_datestamp']."' ");
	}else{
		$row = $sql -> db_Fetch(); extract($row);
		if(($tmp_info+604800) < time()){
			$sql -> db_Delete("cache");
			$sql -> db_Update("tmp", "tmp_info='".time()."' WHERE tmp_ip='var_store' AND tmp_time=1 ");
		}
	}
}


$text = "º <a style='cursor: pointer; cursor: hand' onclick=\"expandit(this)\">Show Docs</a>
<div style='display: none;'>
<br />";
while(list($key, $value) = each($helplist)){ 
	$text .= "<img src='".THEME."images/bullet2.gif' alt='' /> <a href='".e_ADMIN."docs.php?$key'>$value</a><br />"; 
}
$text .= "</div>";
$ns -> tablerender("Documentation", $text);


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