<?php

$db_verify_version="1.0";

$tables_version["core"]="0.600alpha";
$filename = "sql/core_sql.php";
@$fd = fopen ($filename, "r");
$sql_data = @fread($fd, filesize($filename));
@fclose ($fd);

if(!$sql_data){
	echo DBLAN_1."<br /><br />";
	exit;
}

$tables["core"]=$sql_data;

require_once("../class2.php");
require_once(HEADERF);

if(!getperms("0")){ header("location:".e_BASE."index.php"); exit; }

//Get any plugin _sql.php files
$handle=opendir(e_PLUGIN);
$c=1;
while(false !== ($file = readdir($handle))){
	if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
		$plugin_handle=opendir(e_PLUGIN.$file."/");
		while(false !== ($file2 = readdir($plugin_handle))){
			if(preg_match("/(.*?)_sql.php/",$file2,$matches)){
				$filename = e_PLUGIN.$file."/".$file2;
				@$fd = fopen ($filename, "r");
				$sql_data = @fread($fd, filesize($filename));
				@fclose ($fd);
				$tables[$matches[1]]=$sql_data;
			}
		}
		closedir($plugin_handle);
	}
}
closedir($handle);

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
	<b>".DBLAN_2." ".$what." tables ".DBLAN_3.": ".$tables_version[$what]."</b></td><tr>
	<tr>
	<td class='forumheader' style='text-align:center'>".DBLAN_4."</td>
	<td class='forumheader' style='text-align:center'>".DBLAN_5."</td>
	<td class='forumheader' style='text-align:center'>".DBLAN_6."</td>
	<td class='forumheader' style='text-align:center'>".DBLAN_7."</td>
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
							$text.="<td class='forumheader' style='text-align:center'>".DBLAN_8."</td>";
							$text.="<td class='forumheader3' style='text-align:center'><b>".DBLAN_9." [".$xfparams."] <br />".DBLAN_10." [".$fparams."]</b></td>";
						} else {
							$text.="<td class='forumheader3' style='text-align:center'>OK</td>
							<td class='forumheader3' style='text-align:center'>&nbsp;</td>";
						}
					}
				}
				if($ffound==0){
					$text.="<td class='forumheader' style='text-align:center'>".DBLAN_11."</td>
					<td class='forumheader3' style='text-align:center'><b>".DBLAN_10." [$fparams]</b></td>";
				}
				$text.="</tr>\n";
			}
			foreach(array_keys($xfields) as $tf){
				if(!$fields[$tf]){
					$text.="<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>$tf</td><td class='forumheader3' style='text-align:center'>".DBLAN_12."</td><td class='forumheader3' style='text-align:center'>&nbsp;</td></tr>";
				}
			}
		} else {
			$text.="<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>&nbsp;</td><td class='forumheader' style='text-align:center'>".DBLAN_13."<br /><td class='forumheader3' style='text-align:center'>&nbsp;</td></tr>";
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
	<tr><td>".DBLAN_14."<br /><br />";
	foreach(array_keys($tables) as $x){
		$text.="<input type=\"checkbox\" name=\"table_".$x."\">".$x."<br />";
	}
	$text.="
	<br /><input class=\"button\" type=\"submit\" value=\"".DBLAN_15."\">
	</td></tr></table></form>";
	$ns->tablerender(DBLAN_16.": ".$db_verify_version,$text);
} else {
	foreach(array_keys($_POST) as $k){
		if(preg_match("/table_(.*)/",$k,$match)){
			$xx=$match[1];


			$str = "<br />
			<div style='text-align:center'>
			<form method='POST' action='db.php'>
			<input class='button' type='submit' name='back' value='".DBLAN_17."' />
			</form>
			</div>";



			$ns->tablerender("SQL Verification - $xx tables",check_tables($xx). $str);
		}
	}
}
require_once("footer.php");
?>    