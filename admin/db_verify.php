<?php

$db_verify_version="1.0";

$tables_version["core"]="0.549beta";
$filename = "sql/core_sql.php";
@$fd = fopen ($filename, "r");
$sql_data = @fread($fd, filesize($filename));
@fclose ($fd);

if(!$sql_data){
	echo "Unable to read the sql datafile<br /><br />Please ensure the file <b>core_sql.php</b> exists in the <b>/admin/sql</b> directory.<br /><br />";
	exit;
}

$tables["core"]=$sql_data;

require_once("../class2.php");
require_once(HEADERF);



if(ADMIN==FALSE){
	$ns->tablerender("Error","You must be logged in at an admin to access this page.");
	exit;
}


function read_tables($tab){

	global $tablines;
	global $table_list;
	global $tables;
	$file=split("\n",$tables[$tab]);
	foreach($file as $line){
		$line=ltrim(stripslashes($line));
		if(preg_match("/CREATE TABLE (.*) /",$line,$match)){
			$table_list[$match[1]]=1;
			$current_table=$match[1];
			$x=0;
			$cnt=0;
		}
		if(preg_match("/TYPE=/",$line,$match)){
			$current_table="";
		}
		if($current_table && $x){
			$tablines[$current_table][$cnt++]=$line;
		}
		$x=1;
	}
}

function get_current($tab,$prefix=""){
	if(!$prefix){$prefix=MPREFIX;}
	$result= mysql_query('SET SQL_QUOTE_SHOW_CREATE = 1');
	$qry='SHOW CREATE TABLE `'.$prefix.$tab."`";
   $z=mysql_query($qry);
	if($z){
	   $row=mysql_fetch_row($z);
   	return str_replace("`","",stripslashes($row[1]));
   } else {
   	return FALSE;
   }
}

function check_tables($what){
	global $tablines;
	global $table_list;
	global $ns;
	global $tables_version;
	global $coppermine_prefix;
	
	$table_list="";
	read_tables($what);
	
	$text="
	<table style='width:95%' class='fborder'>
	<tr><td class='forumheader3' colspan='4' style='text-align:center'>
	<b>Verifying all ".$what." tables for version: ".$tables_version[$what]."</b></td><tr>
	<tr>
	<td class='forumheader' style='text-align:center'>Table</td>
	<td class='forumheader' style='text-align:center'>Field</td>
	<td class='forumheader' style='text-align:center'>Status</td>
	<td class='forumheader' style='text-align:center'>Notes</td>
	</tr>";
	foreach(array_keys($table_list) as $k){
		$text.="<tr>";
		if($what=="Coppermine"){
			$prefix=$coppermine_prefix;
		} else {
			$prefix=MPREFIX;
		}
		$current_tab=get_current($k,$prefix);
		unset($fields);
		unset($xfields);
		if($current_tab){
			$lines=split("\n",$current_tab);
			foreach($tablines[$k] as $x){
				$ffound=0;
				list($fname,$fparams)=split(" ",$x,2);
				$fields[$fname]=1;
				$fparams=ltrim(rtrim($fparams));
				$fparams=preg_replace("/\r?\n$|\r[^\n]$|,$/", "", $fparams);
				$text.="<tr><td class='forumheader3'>$k</td><td class='forumheader3'>$fname</td>";
				foreach($lines as $l){
					list($xl,$tmp)=split("\n",$l,2);
					$xl=ltrim(rtrim(stripslashes($xl)));
					$xl=preg_replace("/\r?\n$|\r[^\n]$/", "", $xl);
					list($xfname,$xfparams)=split(" ",$xl,2);
					if($xfname != "CREATE" && $xfname !=")"){
						$xfields[$xfname]=1;
					}
					$xfparams=preg_replace("/,$/", "", $xfparams);
					$fparams=preg_replace("/,$/", "", $fparams);
					if($xfname == $fname){
						$ffound=1;
						if($fparams != $xfparams){
							$text.="<td class='forumheader' style='text-align:center'>Mismatch</td>";
							$text.="<td class='forumheader3' style='text-align:center'><b>Currently [".$xfparams."] <br />should be [".$fparams."]</b></td>";
						} else {
							$text.="<td class='forumheader3' style='text-align:center'>OK</td>
							<td class='forumheader3' style='text-align:center'>&nbsp;</td>";
						}
					}
				}
				if($ffound==0){
					$text.="<td class='forumheader' style='text-align:center'>Field missing</td>
					<td class='forumheader3' style='text-align:center'><b>should be [$fparams]</b></td>";
				}
				$text.="</tr>\n";
			}
			foreach(array_keys($xfields) as $tf){
				if(!$fields[$tf]){
					$text.="<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>$tf</td><td class='forumheader3' style='text-align:center'>Extra Field!</td><td class='forumheader3' style='text-align:center'>&nbsp;</td></tr>";
				}
			}
		} else {
			$text.="<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>&nbsp;</td><td class='forumheader' style='text-align:center'>Table missing !!<br /><td class='forumheader3' style='text-align:center'>&nbsp;</td></tr>";
		}
	}
	$text.="</table>";
	return $text;
}

global $table_list;
if(!$_POST){
	$text="
	<form method=\"POST\" action=\"".e_SELF."\">
	<table border=0 align=\"center\">
	<tr><td>Choose table(s) to validate<br /><br />";
	foreach(array_keys($tables) as $x){
		$text.="<input type=\"checkbox\" name=\"table_".$x."\">".$x."<br />";
	}
	$text.="
	<br /><input class=\"button\" type=\"submit\" value=\"Start Verify\">
	</td></tr></table></form>";
	$ns->tablerender("SQL Verification - Version: ".$db_verify_version,$text);
} else {
	foreach(array_keys($_POST) as $k){
		if(preg_match("/table_(.*)/",$k,$match)){
			$xx=$match[1];


			$str = "<br />
			<div style='text-align:center'>
			<form method='POST' action='db.php'>
			<input class='button' type='submit' name='back' value='Back' />
			</form>
			</div>";



			$ns->tablerender("SQL Verification - $xx tables",check_tables($xx). $str);
		}
	}
}
require_once("footer.php");
?>    