<?php
/*
+---------------------------------------------------------------+
|        e107 website system                                    |
|        /pm.php                                                |
|                                                               |
|                                                               |
|        Released under the terms and conditions of the         |
|        GNU General Public License (http://gnu.org).           |
+---------------------------------------------------------------+
*/
require_once("../../class2.php");
require_once(e_PLUGIN."pm_menu/pm_inc.php");
require_once(e_PM."pm_class.php");

$PMREAD="
{PMREADSUMMARY}
{PMSENDFORM}
";

$PMSENT="
{PMSENTSUMMARY}
{PMSENDFORM}
";

$PMSEND="
{PMSENDFORM}
{PMREADSUMMARY}
";

$PMREPLY="
{PMREPLY}
{PMREADSUMMARY}
";


$user_dropdown=$pref['pm_user_dropdown'];
global $pref;

if($pref['pm_userclass'] && !check_class($pref['pm_userclass'])){$notallowed=1;}
if(ADMINPERMS==0){$notallowed=0;}
if(USER == FALSE){$notallowed=1;}

if($notallowed){
	require_once(HEADERF);
	$ns->tablerender(PMLAN_7,PMLAN_8);
	require_once(FOOTERF);
	exit;
}

////////////////  BEGIN MAIN CODE /////////////////////////
$pm=new pm;
$pm->Delete_Expired();
$text="";

list($qstr,$msg)=explode(",",e_QUERY);
define("PM_QUERY",$qstr);


if($_POST['delsel']){
	foreach(array_keys($_POST['delid']) as $i){
		$pm -> delete_pm($i);
	}
}

if(preg_match("#read#",PM_QUERY) || PM_QUERY=="" && !$_POST['reply'] && !$_POST['postpm']){
	$parms=explode(".",PM_QUERY);
	$readstart = (is_numeric($parms[0])) ? $parms[0] : 0;
	$np_query="read";
	require_once(HEADERF);
	if($msg){
		pm_show_message($msg);
	}
	pm_parseformat($PMREAD);
}

if(eregi(PMLAN_9,$_POST['postpm'])){
	list($success,$failed,$notfound)=explode(",",$pm->post_pm());
	if($_POST['sendtype'] == "userclass"){
		list($cnum,$cname)=explode(":",$_POST['class']);
		if($_POST['class'] == "ALL"){$cname=PMLAN_48;}
		header("location:pm.php?,ms.".preg_replace("# #","_",$cname)); exit;
	} else {
		if($notfound){
			require_once(HEADERF);
			$ns->tablerender(PMLAN_10,PMLAN_12.": ".$_POST['to']." ".PMLAN_13." .");
			$ns->tablerender(PMLAN_11,$pm->send_message("","",$_POST['subject'],$_POST['pm_text']));
		} elseif($failed){
			header("location:pm.php?send,mb.".$_POST['to']); exit;
		} else {
			header("location:pm.php?read,ms.".$_POST['to']); exit;
		}
	}
}

if(preg_match("/^del\./",PM_QUERY)){
	$tmp=explode(".",PM_QUERY);
	if($pm->delete_pm($tmp[1])){
		header("location:pm.php?read,md"); exit;
	} else {
		header("location:pm.php?read"); exit;
	}
}

if(preg_match("/view\./",PM_QUERY)){
	$tmp=explode(".",PM_QUERY);
	$msg_id=$tmp[1];
	$readstart=0;
	$np_query=PM_QUERY;
	if($tmp[1] == "view"){
		$msg_id=$tmp[2];
		$np_query=$tmp[1].".".$tmp[2];
		$readstart=$tmp[0];
	}

   $msgtext=$pm->view_pm($msg_id);
   if($msgtext){
   	switch($msgtext){
   		case("MARK"):
				header("location:pm.php?".e_QUERY); exit;
				break;
   		case("FAIL"):
				require_once(HEADERF);
   			break;
   		default:
				require_once(HEADERF);
				$ns->tablerender(PMLAN_27,$msgtext);
				$ns->tablerender(PMLAN_1,$pm->read_summary($readstart,$np_query));
				break;
		}
   } else {
		header("location:pm.php"); exit;
   }
}

if($msg){
	require_once(HEADERF);
	pm_show_message($msg);
}

require_once(HEADERF);

if($_POST['reply'] || preg_match("/reply\./",PM_QUERY)){
	$tmp=explode(".",PM_QUERY);
	$readstart=0;
	$np_query="read";
	if($tmp[1] == "reply"){
		$readstart=$tmp[0];
	}
	pm_parseformat($PMREPLY);
}

if(PM_QUERY == "sent"){
	pm_parseformat($PMSENT);
}

if(preg_match("/send/",PM_QUERY)){
	pm_parseformat($PMSEND);
}


if(PM_QUERY == "vb"){
	$text.=$pm->view_block();
	$ns->tablerender(PMLAN_25,$text);
}


if(preg_match("/^block\./",PM_QUERY)){
	$tmp=explode(".",PM_QUERY);
	if($pm->block_user($tmp[1],USERNAME)){
		header("location:pm.php?read,b.".$tmp[1]); exit;
	} else {
		header("location:pm.php?read,bp.".$tmp[1]); exit;
	}
}

if(preg_match("/^unblock\./",PM_QUERY)){
	$tmp=explode(".",PM_QUERY);
	if($pm->is_blocked($tmp[1],USERNAME)){
		if($pm->unblock_user($tmp[1],USERNAME)){
			header("location:pm.php?vb,ub.".$tmp[1]); exit;
		} else {
			header("location:pm.php?vb".$tmp[1]); exit;
		}
	} else {
			header("location:pm.php?vb,ubnf.".$tmp[1]); exit;
	}
}

require_once(FOOTERF);

function pm_show_send(){
	global $ns,$pm,$pref;
	if(check_class($pref['pm_userclass'])){
		$temp=explode(".",PM_QUERY);
		$ns->tablerender(PMLAN_PM,$pm->send_message($temp[1]));
	} else {
		$ns->tablerender(PMLAN_PM,PMLAN_16);
	}
}	

function pm_show_message($message){

/*
# Show a mesage
# Paremeter #1	- string $message, Message to display
# Return -	None
*/
	global $ns;
	$obj=new convert;
	$m=explode(".",$message);
	switch ($m[0]) {
		case "md":
			$mtext.=PMLAN_14.".";
			break;
		case "b":
			$mtext=PMLAN_18.$m[1];
			break;
		case "bp":
			$row=$this->is_blocked($m[1],USERNAME);
			if(is_array($row)){
				extract($row);
				$mtext=PMLAN_21.$m[1];
				$mtext.="<br />".PMLAN_22.$obj->convert_date($block_datestamp,"long");
				$mtext.="<br /><br />".$block_count.PMLAN_23;
			}
			break;
		case "ub":
			$mtext=$m[1].PMLAN_19;
			break;
		case "ubnf":
			$mtext=PMLAN_24;
			break;
		case "ms":
			$mtext.=PMLAN_15.": ";
			$mtext.=" ".preg_replace("#_#"," ",$m[1]);
			break;
		case "mb":
			$mtext.=PMLAN_16.": ";
			$mtext.=" ".$m[1];
			$mtext.="<br />".PMLAN_17;
			break;
		default:
			$mtext=$message;
	}
	$ns->tablerender("","<div style='text-align:center'><b>".$mtext."</b></div>");
}

function pm_parseformat($format){
	$lines=explode("\n",$format);
	foreach($lines as $line){
		$line=ltrim(rtrim($line));
		if($line=="{PMREADSUMMARY}"){
			pm_readsummary();
		}
		if($line=="{PMSENTSUMMARY}"){
			pm_sentsummary();
		}
		if($line=="{PMSENDFORM}"){
			pm_show_send();
		}
		if($line=="{PMREPLY}"){
			pm_reply();
		}
	}
}

function pm_readsummary(){
	global $ns,$pm, $readstart,$np_query;
	$text="";
	$text.=$pm->read_summary($readstart,$np_query);
	$ns->tablerender(PMLAN_1,$text);
}

function pm_reply(){
	global $ns,$pm;
	$text=$pm->send_message("",$_POST['msgid']);
	$ns->tablerender(PMLAN_26,$text);
}

function pm_sentsummary(){
	global $ns,$pm;
	$ns->tablerender(PMLAN_2,$pm->sent_summary());
}

?>