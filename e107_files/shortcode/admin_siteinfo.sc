if(ADMIN){
	global $ns, $themename, $themeversion, $themeauthor, $themedate, $themeinfo, $mySQLdefaultdb;
	$sql = new db;
	$sql -> db_Select("core", "*", "e107_name='e107' ");
	$row = $sql -> db_Fetch();
	$e107info = unserialize($row['e107_value']);

	if(file_exists(e_ADMIN."ver.php")){ @require_once(e_ADMIN."ver.php"); }

	$obj = new convert;
	$install_date = $obj->convert_date($e107info['e107_datestamp'], "long");

	$text = "<b>".FOOTLAN_1."</b>
	<br />".
	SITENAME."
	<br /><br />
	<b>".FOOTLAN_2."</b>
	<br />
	<a href=\"mailto:".SITEADMINEMAIL."\">".SITEADMIN."</a>
	<br />
	<br />
	<b>e107</b>
	<br />
	".FOOTLAN_3." ".$e107info['e107_version']. ($e107info['e107_build'] ? " ".FOOTLAN_4." ".$e107info['e107_build'] : "")."
	<br /><br />
	<b>".FOOTLAN_5."</b>
	<br />
	".$themename." v".$themeversion." ".FOOTLAN_6." ".$themeauthor." (".$themedate.")
	<br />
	".FOOTLAN_7.": ".$themeinfo."
	<br /><br />
	<b>".FOOTLAN_8."</b>
	<br />
	".$install_date."
	<br /><br />
	<b>".FOOTLAN_9."</b>
	<br />".
	 eregi_replace("PHP.*", "", $_SERVER['SERVER_SOFTWARE'])."<br />(".FOOTLAN_10.": ".$_SERVER['SERVER_NAME'].")
	<br /><br />
	<b>".FOOTLAN_11."</b>
	<br />
	".phpversion()."
	<br /><br />
	<b>".FOOTLAN_12."</b>
	<br />
	".mysql_get_server_info().
	"<br />
	".FOOTLAN_16.": ".$mySQLdefaultdb;
	$ns -> tablerender(FOOTLAN_13, $text);
}
