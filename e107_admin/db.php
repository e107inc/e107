<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/db.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");

if(IsSet($_POST['dump_sql'])){
	if(!getperms("0")){
		header("location: ".e_ADMIN."admin.php");
		exit;
	}
	getsql($mySQLdefaultdb);
	exit;
}

if(IsSet($_POST['db_update'])){
	header("location: ".e_ADMIN."e107_update.php");
	exit;
}

if(IsSet($_POST['verify_sql'])){
	header("location: ".e_ADMIN."db_verify.php");
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
	message_handler("MESSAGE", DBLAN_1);
}



$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>\n
<table style='width:85%' class='fborder'>

<tr>
<td style='width:70%' class='forumheader3'>".DBLAN_15."</td>
<td class='forumheader3' style='width:30%;text-align:center'><input class='button' type='submit' name='db_update' value='".DBLAN_16."' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".DBLAN_2."</td>
<td class='forumheader3' style='width:30%;text-align:center'><input class='button' type='submit' name='dump_sql' value='".DBLAN_3."' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".DBLAN_4."</td>
<td class='forumheader3' style='width:30%;text-align:center'><input class='button' type='submit' name='verify_sql' value='".DBLAN_5."' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".DBLAN_6."</td>
<td class='forumheader3' style='width:30%;text-align:center'><input class='button' type='submit' name='optimize_sql' value='".DBLAN_7."' /></td>
</tr>

<tr>
<td style='width:70%' class='forumheader3'>".DBLAN_8."</td>
<td class='forumheader3' style='width:30%;text-align:center'><input class='button' type='submit' name='backup_core' value='".DBLAN_9."' />
<input type='hidden' name='sqltext' value='$sqltext' />
</td></tr>
</table>
</form>
</div>";

$ns -> tablerender(DBLAN_10, $text);

function backup_core(){
        global $pref, $sql;
	$tmp = addslashes(serialize($pref));
	if(!$sql -> db_Insert("core", "'pref_backup', '$tmp' "))
	{ 
		$sql -> db_Update("core", "e107_value='$tmp' WHERE e107_name='pref_backup'"); 
	}
}

function optimizesql($mySQLdefaultdb){

	$result = mysql_list_tables($mySQLdefaultdb);
	while ($row = mysql_fetch_row($result)){
		mysql_query("OPTIMIZE TABLE ".$row[0]);
	}

	$str = "
	<div style='text-align:center'>
	<b>".DBLAN_11." $mySQLdefaultdb ".DBLAN_12.".</b>

	<br /><br />

	<form method='POST' action='".e_SELF."'>
	<input class='button' type='submit' name='back' value='".DBLAN_13."' />
	</form>
	</div>
	<br />";
	$ns = new e107table;
	$ns -> tablerender(DBLAN_14, $str);

}

function sqladdslashes($str)
{						// SQL text data fixup
	$str = str_replace('\\', '\\\\', $str);	// replace \ with \\
	$str = str_replace('\'', '\'\'', $str);	// replace ' with ''
	return $str;
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
	$sTblWild = str_replace('_','\_',MPREFIX).'%'; /* add slash to underscores, end with SQL wildcard */
	$result = mysql_query("SHOW TABLES LIKE '{$sTblWild}'"); // avoid normal sql class (instead of $sql2 which would also work)
	//       $result = mysql_list_tables($mySQLdefaultdb);
	$es = " \r\n";
	$sqltext = "#".$es."# e107 sql-dump".$es."# Database: $mySQLdefaultdb".$es."#".$es."# Date: " .  gmdate("d-m-Y H:i:s", time()) . " GMT".$es."#".$es;
	while ($row = mysql_fetch_row($result)){
		$sqltext .= $es.$es."## (re)create table structure for ".$row[0]." ##".$es;
		$sqltext .= $es.'DROP TABLE IF EXISTS `'.$row[0]."`;".$es;
		//
		// SHOW CREATE TABLE -- requires MySQL 3.23.20
		//
		$sQ="SHOW CREATE TABLE `".$row[0]."`;";
		$qryRes = $sql -> db_Query($sQ);
		$var = $sql->db_Fetch();
		$sCreate = $var['Create Table'];

		$sQ = "SHOW TABLE STATUS LIKE '".str_replace('_','\_',$row[0])."'";
		$sql -> db_Query($sQ);
		if ($sql->db_Rows())
		{
			$var = $sql->db_Fetch();
			if (!empty($var['Auto_increment'])) {
				$sCreate .= ' AUTO_INCREMENT='.$var['Auto_increment'].' ';
			}
		}
		unset($var);
		$sqltext .= $sCreate.';'.$es;

		// String fixups
		$search = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
		$replace= array('\0', '\n', '\r', '\Z');

		$maintable = ereg_replace(MPREFIX, "", $row[0]);
		$sql -> db_Select($maintable);

		$metainfo = array();
		$iFields = $sql->db_Num_fields();

		for ($i = 0; $i < $iFields; $i++) {
			$metainfo[] = $sql->db_Field_info();
		}

		while ($var = $sql -> db_Fetch()){
			$sqltext .= $es.$es."## Table Data for ".$row[0]." ##".$es;
			$field_names = array();
			$num_fields = $sql -> db_Num_fields();
			$table_list = '(';
			for ($j = 0; $j < $num_fields; $j++){
				$field_names[$j] = $sql -> db_Fieldname($j);
				$table_list .= (($j > 0) ? ', ' : '') . $field_names[$j];
			}
			$table_list .= ')';
			$sqltext .="INSERT INTO ".$row[0]." $table_list VALUES ";
			$rowcount=0;
			do{
			$sqltext .= ($rowcount++ ? ',':'').$es."(";
			for ($j = 0; $j < $num_fields; $j++){
				$sqltext .= ($j > 0) ? ', ' : '';
				if(!isset($var[$field_names[$j]]) || is_null($var[$field_names[$j]])){
					$sqltext .= 'NULL';
					// a number;  timestamp is numeric on some MySQL 4.1
				}elseif ($metainfo[$j]->numeric &&
				$metainfo[$j]->type != 'timestamp') {
					$sqltext .= $var[$field_names[$j]];
				}elseif (empty($var[$field_names[$j]])) {
					$sqltext .="''";
				}else{
				$sqltext .= "'".str_replace($search,$replace,sqladdslashes($var[$field_names[$j]]))."'";
			}
		}
		$sqltext .= ')';
	}
	while ($var = $sql -> db_Fetch());
	$sqltext .= ';'.$es;
}
}
echo $sqltext;
}

require_once("footer.php");
?>