<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/upgrade.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
@include("config.php");

define("VERSION", "0.600");
define("BUILD", "beta");

define("MSERVER", $mySQLserver);
define("MUSER", $mySQLuser);
define("MPASS", $mySQLpassword);
define("MDB", $mySQLdefaultdb);
define("MPREFIX", $mySQLprefix);

$sql = new db;
$sql -> db_SetErrorReporting(TRUE);
$sql -> db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb);

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><e107 upgrade></title>
<link rel="stylesheet" href="themes/e107/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
</head>
<body>
<div style="text-align:center">
<table style="width:100%" cellspacing="0" cellpadding="0">
<tr>
<td style="width:66%; background-color:#E2E2E2; text-align:left\">
<img src="themes/shared/logo.png" alt="Logo" />
</td>
<td style="background-color:#E2E2E2; text-align:right; vertical-align:bottom" class="smalltext">
©Steve Dunstan 2002. See gpl.txt for licence details.
</td>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<tr> 
<td colspan="2" style="background-color:#ccc; vertical-align: top;">
<div class="mediumtext">&nbsp;Upgrade</div>
</td>
</tr>
<tr> 
<td colspan="2" style="background-color:#000; vertical-align: top;"></td>
</tr>
<tr>
<td colspan="2" style="vertical-align: top; text-align:center"><br />
<table style="width:66%" class="fborder">
<tr>
<td class="installb">
<br /><img src="themes/e107/images/installlogo.png" alt="" /><br /><span class="smalltext">php/mySQL website system</span><br />
<br />
<span class="installe">e107 upgrade</span>
<br /><br />

<?php

if(IsSet($_POST['usubmit'])){
	if($sql -> db_Select("user", "*", "user_name='".$_POST['a_name']."' AND user_password='".md5($_POST['a_password'])."' AND user_perms='0'")){
		$row = $sql -> db_Fetch();
		extract($row);
		if($user_perms != 0){
			$error = "You do not have the correct permissions to execute this script.";
		}
	}else{
		$error = "Administrator name not found in database or incorrect password - script halted.";
	}

	if($error){
		echo "<span class='installh'>".$error."</span><br /><br />";
		echo "</td></tr></table></body></html>";
		exit;
	}

	echo "<span class='installh'>Main site administrator found, click button to begin upgrade process.</span><br /><br />
	<form method='post' action='".$_SERVER['PHP_SELF']."'><br /><br />
	<input class='button' type='submit' name='csubmit' value='Begin Upgrade' />
	</form><br /><br />
	</td></tr></table></body></html>";
	exit;
	
}

if(IsSet($_POST['csubmit'])){
	update_tables();
	echo $str;
	if(eregi("ERROR", $str)){
		echo "<br /><span class='installe'>* Error *</span><br /><br />An error was encountered while running the upgrade process.";
	}else{
		echo "<span class='installe'>* Success! *</span><br /><br />Upgrade completed successfully, you are now running <b>version ".VERSION." ".BUILD."</b></span><br /><br />		
		<a href='index.php'>Click here to go to your front page</a><br />";
	}
	echo "</td></tr></table></body></html>";
	exit;
}


echo "This script will upgrade your e107 core install from v0.555 to v".VERSION.".<br /><br /><br />
<b>PLEASE NOTE</b><br />
While every measure has been taken to ensure none of your site content is altered during this process, it would still be wise to backup your database before continuing.<br />
Also, if you are upgrading from a version older than v5.4, please backup your config.php and set it's permissions to 777 (CHMOD 777) as it will be rewritten to the latest standard. After the upgrade has completed chmod it back to 644 (CHMOD 644).<br /><br /><br />

Please enter your main administrator username/password to continue<br /><br />

<form method='post' action='".$_SERVER['PHP_SELF']."'>
<table style='width:70%'>
<tr>
<td style='width:30%' class='mediumtext'>Main administrator name:</td>
<td style='width:70%'>
<input class='tbox' type='text' name='a_name' size='60' value='' maxlength='100' />
</td>
</tr>
<tr>
<td style='width:30%' class='mediumtext'>Main administrator Password:</td>
<td style='width:70%'>
<input class='tbox' type='password' name='a_password' size='60' value='' maxlength='100' />
</td>
</tr>

<tr>
<td colspan='2' style='text-align:center'>
<br />
<input class='button' type='submit' name='usubmit' value='Submit' />
</td>
</tr>
</table>
</form>
</body>
</html>";


function update_tables(){
	global $pref, $sql;

	
	@mysql_query("ALTER TABLE ".MPREFIX."banlist CHANGE banlist_ip banlist_ip VARCHAR( 100 ) NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."content ADD content_class TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."forum DROP forum_active");
	@mysql_query("ALTER TABLE ".MPREFIX."forum_t CHANGE thread_user thread_user VARCHAR( 250 ) NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."links ADD link_class TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."news DROP news_source , DROP news_url");
	@mysql_query("ALTER TABLE ".MPREFIX."news CHANGE news_active news_class TINYINT( 3 ) UNSIGNED DEFAULT '0'  NOT NULL");
	@mysql_query("ALTER TABLE ".MPREFIX."stat_counter DROP counter_today_total , DROP counter_today_unique");
	@mysql_query("ALTER TABLE ".MPREFIX."userclass_classes CHANGE userclass_id userclass_id TINYINT( 3 ) UNSIGNED DEFAULT '0' NOT NULL");
	@mysql_query("CREATE TABLE ".MPREFIX."plugin ( plugin_id int(10) unsigned NOT NULL auto_increment, plugin_name varchar(100) NOT NULL default '', plugin_version varchar(10) NOT NULL default '', plugin_path varchar(100) NOT NULL default '', plugin_installflag tinyint(1) unsigned NOT NULL default '0', PRIMARY KEY  (plugin_id)) TYPE=MyISAM;");
	@mysql_query("CREATE TABLE ".MPREFIX."rbinary (binary_id int(10) unsigned NOT NULL auto_increment,binary_name varchar(200) NOT NULL default '',binary_filetype varchar(100) NOT NULL default '',binary_data longblob NOT NULL,PRIMARY KEY  (binary_id)) TYPE=MyISAM");

	$sql -> db_Select("binary");
	while($row = $sql -> db_Fetch()){
		extract($row);
		$sql -> db_Insert("rbinary", "'$binary_id', '$binary_name', '$binary_filetype', '$binary_data' ");
	}
	@mysql_query("DROP TABLE ".MPREFIX."binary");
	
	
	$sql -> db_Select("core", "*", "e107_name='pref' ");
	$row = $sql -> db_Fetch();
	$tmp = stripslashes($row['e107_value']);
	$pref=unserialize($tmp);
	if(!is_array($pref)){
		$pref=unserialize($row['e107_value']);
	}

	while(list($key, $user_id) = each($pref)){
		$tmpref[$key] = $pref[$key][1]; 
	}

	$tmpref['admintheme'] = "";
	$tmpref['profanity_words'] = "";
	$tmpref['displaythemeinfo'] = "";
	$tmpref['displayrendertime'] = "";
	$tmpref['displaysql'] = "";
	$tmpref['cachestatus'] = "";
	$tmpref['forum_enclose'] = "1";
	$tmpref['forum_title'] = "Forums";
	$tmpref['forum_postspage'] = "15";
	$tmpref['forum_levels'] = "";
	
	$tmp = addslashes(serialize($tmpref));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref' ");


	$sql -> db_Select("core", "*", "e107_name='e107' ");
	$row = $sql -> db_Fetch();
	$tmp = stripslashes($row['e107_value']);
	$e107=unserialize($tmp);
	if(!is_array($e107)){
		$e107=unserialize($row['e107_value']);
	}

	$e107['e107_version'] = VERSION;
	$e107['e107_build'] = BUILD;
	$e107['e107_datestamp'] = time();

	$tmp = addslashes(serialize($e107));
	$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='e107' ");


}

class db{
	var $mySQLserver;
	var $mySQLuser;
	var $mySQLpassword;
	var $mySQLdefaultdb;
	var $mySQLaccess;
	var $mySQLresult;
	var $mySQLrows;
	var $mySQLerror;
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Connect($mySQLserver, $mySQLuser, $mySQLpassword, $mySQLdefaultdb){
		$this->mySQLserver = $mySQLserver;
		$this->mySQLuser = $mySQLuser;
		$this->mySQLpassword = $mySQLpassword;
		$this->mySQLdefaultdb = $mySQLdefaultdb;
		$temp = $this->mySQLerror;
		$this->mySQLerror = FALSE;
		$this->mySQL_access = @mysql_connect($this->mySQLserver, $this->mySQLuser, $this->mySQLpassword);
		@mysql_select_db($this->mySQLdefaultdb);
		$this->dbError("dbConnect/SelectDB");
		return $this->mySQLerror = $temp;
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Select($table, $fields="*", $arg="", $mode="default"){
//		echo "SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg."<br />";
		if($arg != "" && $mode=="default"){
			if($this->mySQLresult = @mysql_query("SELECT ".$fields." FROM ".MPREFIX.$table." WHERE ".$arg)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("dbQuery ($query)");
				return FALSE;
			}
		}else if($arg != "" && $mode != "default"){
			if($this->mySQLresult = @mysql_query("SELECT ".$fields." FROM ".MPREFIX.$table." ".$arg)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("dbQuery ($query)");
				return FALSE;
			}
		}else{
			if($this->mySQLresult = @mysql_query("SELECT ".$fields." FROM ".MPREFIX.$table)){
				$this->dbError("dbQuery");
				return $this->db_Rows();
			}else{
				$this->dbError("db_Query ($query)");
				return FALSE;
			}		
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Insert($table, $arg){
		if($result = $this->mySQLresult = @mysql_query("INSERT INTO ".MPREFIX.$table." VALUES (".$arg.")" )){
			return $result;
		}else{
			$this->dbError("db_Insert ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Update($table, $arg){
		if($result = $this->mySQLresult = @mysql_query("UPDATE ".MPREFIX.$table." SET ".$arg)){	
			return $result;
		}else{
			$this->dbError("db_Update ($query)");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Fetch(){
		if($row = @mysql_fetch_array($this->mySQLresult)){
			while (list($key,$val) = each($row)) {
				$row[$key] = stripslashes($val);
			}
			$this->dbError("db_Fetch");
			return $row;
		}else{
			$this->dbError("db_Fetch");
			return FALSE;
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Close(){
		mysql_close();
		$this->dbError("dbClose");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Delete($table, $arg){
		if($arg == ""){
			if($result = $this->mySQLresult = @mysql_query("DELETE FROM ".MPREFIX.$table)){
				return $result;
			}else{
				$this->dbError("db_Delete ($query)");
				return FALSE;
			}
		}else{
			if($result = $this->mySQLresult = @mysql_query("DELETE FROM ".MPREFIX.$table." WHERE ".$arg)){
				return $result;
			}else{
				$this->dbError("db_Delete ($query)");
				return FALSE;
			}
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_Rows(){
		$rows = $this->mySQLrows = @mysql_num_rows($this->mySQLresult);
		return $rows;
		$this->dbError("db_Rows");
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function dbError($from){
		if($error_message = @mysql_error()){
			if($this->mySQLerror == TRUE){
				echo "<b>mySQL Error!</b> Function: $from. [".@mysql_errno()." - $error_message]<br />";
				return $error_message;
			}
		}
	}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
	function db_SetErrorReporting($mode){
		$this->mySQLerror = $mode;
	}
}


?>