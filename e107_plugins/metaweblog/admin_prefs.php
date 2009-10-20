<?php
require_once ("../../class2.php");
if (!getperms("P"))
{
	header("location:".e_BASE."index.php");
}
require_once (e_ADMIN."auth.php");
include_lan(e_PLUGIN."metaweblog/languages/".e_LANGUAGE.".php");

 
 
/*
 $preftitle = XMLRPC_PREFS_001;
 $pageid = XMLRPC_ADMIN_002;
 // plugin title page
 $prefcapt[] = XMLRPC_PREFS_002;
 $prefname[] = "eXMLRPC_title";
 $preftype[] = "text";
 $prefvalu[] = "";
 // news render type 0-1-2-3-4
 $prefcapt[] = XMLRPC_PREFS_003;
 $prefname[] = "eXMLRPC_NEWS_RENDER_TYPE";
 $preftype[] = "dropdown";
 $prefvalu[] = "0,1,2,3";
 // use news title as sub-gallery folder names
 $prefcapt[] = XMLRPC_PREFS_004;
 $prefname[] = "eXMLRPC_FILES_PATH";
 $preftype[] = "text";
 $prefvalu[] = "xmlrpc";
 // use custom gallery names
 $prefcapt[] = XMLRPC_PREFS_005;
 $prefname[] = "eXMLRPC_BLOG_ID";
 $preftype[] = "text";
 $prefvalu[] = "Blog ID (no matter what)";
 // thumbinails w
 $prefcapt[] = XMLRPC_PREFS_006;
 $prefname[] = "eXMLRPC_BLOG_NAME";
 $preftype[] = "text";
 $prefvalu[] = "Blog Name (no matter what)";
 //---------------------------------------------------------------
 //              END OF CONFIGURATION AREA
 //---------------------------------------------------------------
 if(IsSet($_POST['updatesettings'])){
 $count = count($prefname);
 for ($i=0; $i<$count; $i++) {
 $namehere = $prefname[$i];
 if($preftype[$i]=="date" || $fieldtype[$i] == "datestamp"){
 $year = $prefname[$i]."_year";
 $month = $prefname[$i]."_month";
 $day = $prefname[$i]."_day";
 if($fieldtype[$i]=="date"){
 $datevalue = $_POST[$year]."-".$_POST[$month]."-".$_POST[$day];
 }else {
 $datevalue = mktime (0,0,0,$_POST[$month],$_POST[$day],$_POST[$year]);
 }
 $pref[$namehere] = $datevalue;
 }else{
 $pref[$namehere] = $_POST[$namehere];
 }
 //    echo $namehere." = ".$_POST[$namehere]."<br>";
 //      echo $namehere." = ".$datevalue;
 };*/

if (isset($_POST['updatesettings']))
{
	save_prefs();
	$message = LAN_SETSAVED;
}

if ($message)
{
	$ns->tablerender("", "<div style='text-align:center'><b>$message</b></div>");
}


$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."'>
<table style='width:94%' class='fborder'>";
for ($i = 0; $i < count($prefcapt); $i++)
{
	$form_send = $prefname[$i]."|".$preftype[$i]."|".$prefvalu[$i];
	$text .= "
<tr>
<td style=\"width:30%; vertical-align:top\" class=\"forumheader3\">".$prefcapt[$i].":</td>
<td style=\"width:70%\" class=\"forumheader3\">";
	$name = $prefname[$i];
//	$text .= $rs->user_extended_element_edit($form_send, $pref[$name], $name);
	$text .= "</td></tr>";
}
;
$text .= "<tr style='vertical-align:top'>
<td colspan='2'  style='text-align:center' class='forumheader'>
<input class='button' type='submit' name='updatesettings' value='Salva impostazioni' />
</td>
</tr>
</table>
</form>
</div>";


$ns->tablerender($preftitle, $text);
require_once (e_ADMIN."footer.php");
?>
