<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/db.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");

if(IsSet($_POST['dump_sql'])){
	getsql($mySQLdefaultdb);
	exit;
}

if(IsSet($_POST['verify_sql'])){
	header("location: db_verify.php");
	exit;
}

require_once("auth.php");
if(IsSet($_POST['optimize_sql'])){
	optimizesql($mySQLdefaultdb);
	require_once("footer.php");
	exit;
}

if(IsSet($_POST['backup_core'])){
	backup_core();
	message_handler("MESSAGE", "Core settings backed up in database.");
}



$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>
<tr>
<td style='width:70%' class='forumheader3'>Click button to save a backup of your e107 database</td>
<td style='width:30%' class='forumheader3' style='text-align:center'><input class='button' type='submit' name='dump_sql' value='Backup SQL database' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>Click button to check validity of e107 database</td>
<td style='width:30%' class='forumheader3' style='text-align:center'><input class='button' type='submit' name='verify_sql' value='Check database validity' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>Click button to optimize your e107 database</td>
<td style='width:30%' class='forumheader3' style='text-align:center'><input class='button' type='submit' name='optimize_sql' value='Optimize SQL database' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>Click button to backup your core settings</td>
<td style='width:30%' class='forumheader3' style='text-align:center'><input class='button' type='submit' name='backup_core' value='Backup core' /></td>
</tr>

</table>
<input type='hidden' name=\"sqltext\" value=`$sqltext`>
</form>
</div>";

$ns -> tablerender("SQL Utilities", $text);

function backup_core(){
	global $pref, $sql;
	if($sql -> db_Select("core", "*", "e107_name='pref_backup' ")){
		save_prefs();
	}else{
		$tmp = serialize($pref);
		$sql -> db_Insert("core", "'pref_backup', '$tmp' ");
	}
}

function optimizesql($mySQLdefaultdb){

	$result = mysql_list_tables($mySQLdefaultdb); 
	while ($row = mysql_fetch_row($result)){ 
		mysql_query("OPTIMIZE TABLE ".$row[0]);
	}

	$str = "
	<div style='text-align:center'>
	<b>mySQL database $mySQLdefaultdb optimized.</b>
	
	<br /><br />
	
	<form method='POST' action='".e_SELF."'>
	<input class='button' type='submit' name='back' value='Back' />
	</form>
	</div>
	<br />";
	$ns = new table;
	$ns -> tablerender("Done", $str);

}


function getsql($mySQLdefaultdb){
	$filename = "e107_backup";
	$ext = "sql";
	$mime_type = "'application/octet-stream";
	$now = gmdate('D, d M Y H:i:s') . ' GMT';

    header('Content-Type: ' . $mime_type);
    header('Expires: ' . $now);
	header('Content-Disposition: inline; filename="' . $filename . '.' . $ext . '"');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');

	$sql = new db;
	$result = mysql_list_tables($mySQLdefaultdb); 
	$es = " \r\n";   
	$sqltext = "#".$es."# e107 sql-dump".$es."# Database: $mySQLdefaultdb".$es."#".$es."# Date: " .  gmdate("d-m-Y H:i:s", time()) . " GMT".$es."#".$es;
	while ($row = mysql_fetch_row($result)){ 
		$sqltext .= $es.$es."## Create data for ".$row[0]." ##".$es;
		$sqltext .= "CREATE TABLE ".$row[0]."(".$es;
		$sql -> db_Select_gen("SHOW FIELDS FROM $row[0]");
		while ($var = $sql -> db_Fetch()){
			$sqltext .= '	' . $var['Field'] . ' ' . $var['Type'];
			if(!empty($var['Default'])){
				$sqltext .= ' DEFAULT \'' . $var['Default'] . '\'';
			}
			if($var['Null'] != "YES"){
				$sqltext .= ' NOT NULL';
			}
			if($var['Extra'] != ""){
				$sqltext .= ' ' . $row['Extra'];
			}
			$sqltext .= ",".$es;
		}
		$sqltext = ereg_replace(','.$es.'$', "", $sqltext);
		$sql -> db_Select_gen("SHOW KEYS FROM $row[0]");
		while ($var = $sql -> db_Fetch()){
			$kname = $var['Key_name'];
			if(($kname != 'PRIMARY') && ($var['Non_unique'] == 0)){
				$kname = "UNIQUE|$kname";
			}
			if(!is_array($index[$kname])){
				$index[$kname] = array();
			}
			$index[$kname][] = $var['Column_name'];
		}
		while(list($x, $columns) = @each($index)){
			$sqltext .= ", $es";
			if($x == 'PRIMARY'){
				$sqltext .= '	PRIMARY KEY (' . implode($columns, ', ') . ')';
			}
			elseif (substr($x,0,6) == 'UNIQUE'){
				$sqltext .= '	UNIQUE ' . substr($x,7) . ' (' . implode($columns, ', ') . ')';
			}else{
				$sqltext .= "	KEY $x (" . implode($columns, ', ') . ')';
			}
		}
		$sqltext .= "$es);";
		$maintable = ereg_replace(MPREFIX, "", $row[0]);
		$sql -> db_Select($maintable);
		while ($var = $sql -> db_Fetch()){
			$sqltext .= $es.$es."## Table Data for ".$row[0]." ##";
			$field_names = array();
			$num_fields = $sql -> db_Num_fields();
			$table_list = '(';
			for ($j = 0; $j < $num_fields; $j++){
				$field_names[$j] = $sql -> db_Fieldname($j);
				$table_list .= (($j > 0) ? ', ' : '') . $field_names[$j];
				
			}
			$table_list .= ')';
			do{
				$sqltext .= $es."INSERT INTO ".$row[0]." $table_list VALUES(";
				for ($j = 0; $j < $num_fields; $j++){
					$sqltext .= ($j > 0) ? ', ' : '';
					if(!isset($var[$field_names[$j]])){
						$sqltext .= 'NULL';
					}elseif ($var[$field_names[$j]] != ''){
						$sqltext .= "'".addslashes($var[$field_names[$j]])."'";
					}else{
						$sqltext .= "''";
					}
				}
				$sqltext .= ');';
				$sqltext = trim($sqltext);
			}
			while ($var = $sql -> db_Fetch());
		}
	}
	echo $sqltext;
}




























require_once("footer.php");
?>