<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_admin/db_verify.php,v $
|     $Revision: 1.4 $
|     $Date: 2005-01-10 09:49:02 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'database';
require_once("auth.php");

$filename = "sql/core_sql.php";
@$fd = fopen ($filename, "r");
$sql_data = @fread($fd, filesize($filename));
@fclose ($fd);

if(!$sql_data){
        echo DBLAN_1."<br /><br />";
        exit;
}

$tables["core"]=$sql_data;

// require_once(HEADERF);

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

        $table_list="";
        read_tables($what);

        $text="<div style='text-align:center'>
        <table style='".ADMIN_WIDTH."' class='fborder'>
        <tr>
        <td class='fcaption' style='text-align:center'>".DBLAN_4."</td>
        <td class='fcaption' style='text-align:center'>".DBLAN_5."</td>
        <td class='fcaption' style='text-align:center'>".DBLAN_6."</td>
        <td class='fcaption' style='text-align:center'>".DBLAN_7."</td>
        </tr>";
        foreach(array_keys($table_list) as $k){
                $text.="<tr>";
                $prefix=MPREFIX;
                $current_tab=get_current($k,$prefix);
                unset($fields);
                unset($xfields);
                if($current_tab){
                        $lines=split("\n",$current_tab);
                        $fieldnum=0;
                        foreach($tablines[$k] as $x){
                                $fieldnum++;
                                $ffound=0;
                                list($fname,$fparams)=split(" ",$x,2);
                                if($fname == "KEY"){
                                        list($key,$keyname,$keyparms)=split(" ",$x,3);
                                        $fname=$key." ".$keyname;
                                        $fparams = $keyparms;
                                }
                                $fields[$fname]=1;
                                $fparams=ltrim(rtrim($fparams));
                                $fparams=preg_replace("/\r?\n$|\r[^\n]$|,$/", "", $fparams);
                                $text.="<tr><td class='forumheader3'>$k</td><td class='forumheader3'>$fname";
                                if(preg_match("#KEY#",$fparams)){$text .= " $fparams";}
                                $text .= "</td>";
                                $s=0;
                                $xfieldnum=-1;
                                foreach($lines as $l){
                                        $xfieldnum++;
                                        list($xl,$tmp)=split("\n",$l,2);
                                        $xl=ltrim(rtrim(stripslashes($xl)));
                                        $xl=preg_replace("/\r?\n$|\r[^\n]$/", "", $xl);
                                        list($xfname,$xfparams)=split(" ",$xl,2);

                                        if($xfname == "KEY"){
                                                list($key,$keyname,$keyparms)=split(" ",$xl,3);
                                                $xfname=$key." ".$keyname;
                                                $xfparams = $keyparms;
                                        }

                                        if($xfname != "CREATE" && $xfname !=")"){
                                                $xfields[$xfname]=1;
                                        }
                                        $xfparams=preg_replace("/,$/", "", $xfparams);
                                        $fparams=preg_replace("/,$/", "", $fparams);
                                        if($xfname==$fname){
                                                $ffound=1;
                                                if(strcasecmp($fparams,$xfparams) != 0){
                                                        $text.="<td class='forumheader' style='text-align:center'>".DBLAN_8."</td>";
                                                        $text.="<td class='forumheader3' style='text-align:center'>".DBLAN_9."<div class='indent'>".$xfparams."</div><b>".DBLAN_10."</b><div class='indent'>".$fparams."</div></td>";
                                                } elseif($fieldnum != $xfieldnum) {
                                                        $text.="<td class='fcaption' style='text-align:center'>".DBLAN_5." ".DBLAN_8."</td>
                                                        <td class='forumheader3' style='text-align:center'>".DBLAN_9." #{$xfieldnum}<br />".DBLAN_10." #{$fieldnum}</td>";
                                                } else {
                                                        $text.="<td class='forumheader3' style='text-align:center;'>OK</td>
                                                        <td class='forumheader3' style='text-align:center'>&nbsp;</td>";
                                                }
                                        }
                                }
                                if($ffound==0){
                                        $text.="<td class='forumheader' style='text-align:center'><strong><em>".DBLAN_11."</em></strong></td>
                                        <td class='forumheader3' style='text-align:center'><b>".DBLAN_10." [$fparams]</b></td>";
                                }
                                $text.="</tr>\n";
                        }
                        foreach(array_keys($xfields) as $tf){
                                if(!$fields[$tf]){
                                        $text.="<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>$tf</td><td class='forumheader3' style='text-align:center'><strong><em>".DBLAN_12."</em></strong></td><td class='forumheader3' style='text-align:center'>&nbsp;</td></tr>";
                                }
                        }
                } else {
                        $text.="<tr><td class='forumheader3' style='text-align:center'>$k</td><td class='forumheader3' style='text-align:center'>&nbsp;</td><td class='forumheader' style='text-align:center'>".DBLAN_13."<br /><td class='forumheader3' style='text-align:center'>&nbsp;</td></tr>";
                }
        }
        $text.="</table></div>";
        return $text;
}

global $table_list;
if(!$_POST){
        $text="
        <form method='POST' action='".e_SELF."'>
        <table border=0 align='center'>
        <tr><td>".DBLAN_14."<br /><br />";
        foreach(array_keys($tables) as $x){
                $text.="<input type='checkbox' name='table_".$x."'>".$x."<br />";
        }
        $text.="
        <br /><input class='button' type='submit' value='".DBLAN_15."'>
        </td></tr></table></form>";
        $ns->tablerender(DBLAN_16,$text);
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
                        $ns->tablerender(DBLAN_16." - $xx ".DBLAN_18,check_tables($xx).$str);
                }
        }
}
require_once(e_ADMIN."footer.php");
?>