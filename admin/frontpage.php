<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/splash.php
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
if(!getperms("P")){ header("location:".e_HTTP."index.php"); }

if(IsSet($_POST['updatesettings'])){
	if($_POST['frontpage'] == "other"){
		$_POST['frontpage'] = ($_POST['frontpage_url'] ? $_POST['frontpage_url'] : "news");
	}
	$pref['frontpage'][1] = $_POST['frontpage'];
	$pref['frontpage_type'][1] = $_POST['frontpage_type'];
	save_prefs();

	if($pref['frontpage'][1] != "news"){
		if(!$sql -> db_Select("links", "*", "link_url='news.php' ")){
			$sql -> db_Insert("links", "0, 'News', 'news.php', '', '', 1, 0, 0, 0");
		}
	}else{
		$sql -> db_Delete("links", "link_url='news.php'");
	}
//	header("location:".e_SELF."?u");
}

require_once("auth.php");

$frontpage_re = ($pref['frontpage'][1] ? $pref['frontpage'][1] : "news");
$frontpage_type = ($pref['frontpage_type'][1] ? $pref['frontpage_type'][1] : "constant");


if(e_QUERY == "u"){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>Front Page settings updated.</b></div>");
}

$text = "<div style=\"text-align:center\">
<form method=\"post\" action=\"".e_SELF."\">
<table style=\"width:95%\" class=\"fborder\">
<tr>

<td style=\"width:30%\" class=\"forumheader3\">Display: </td>
<td style=\"width:70%\" class=\"forumheader3\">


<input name=\"frontpage\" type=\"radio\" value=\"news\"";
if($frontpage_re == "news"){
	$text .= "checked";
	$flag = TRUE;
}
$text .= ">News (default)<br />
<input name=\"frontpage\" type=\"radio\" value=\"forum\"";
if($frontpage_re == "forum"){
	$text .= "checked";
	$flag = TRUE;
}
$text .= ">Forum<br />
<input name=\"frontpage\" type=\"radio\" value=\"download\"";
if($frontpage_re == "download"){
	$text .= "checked";
	$flag = TRUE;
}
$text .= ">Downloads<br />
<input name=\"frontpage\" type=\"radio\" value=\"links\"";
if($frontpage_re == "links"){
	$text .= "checked";
	$flag = TRUE;
}
$text .= ">Links<br />";

if($sql -> db_Select("content", "*", "content_type='1'")){
	while($row = $sql -> db_Fetch()){
		extract($row);
		$text .= "<input name=\"frontpage\" type=\"radio\" value=\"".$content_id."\"";
		if($frontpage_re == $content_id){
			$text .= "checked";
			$flag = TRUE;
		}
		$text .= ">Content Page: ".$content_heading."/".$content_subheading."<br />";
	}
}

$text .= "
<input name=\"frontpage\" type=\"radio\" value=\"other\"";
if($flag != TRUE){
	$text .= "checked";
}

$text .= ">Other  
<input class=\"tbox\" type=\"text\" name=\"frontpage_url\" size=\"50\" value=\"";
if($flag != TRUE){
	$text .= $pref['frontpage'][1];
}

$text .= "\" maxlength=\"100\" /> (type full URL)
</td>
</tr>

<tr>
<td style=\"width:30%\" class=\"forumheader3\">Type: </td>
<td style=\"width:70%\" class=\"forumheader3\">

<input name=\"frontpage_type\" type=\"radio\" value=\"constant\"";
if($frontpage_type == "constant"){
	$text .= "checked";
}
$text .= ">Always front page<br />
<input name=\"frontpage_type\" type=\"radio\" value=\"splash\"";
if($frontpage_type == "splash"){
	$text .= "checked";
}
$text .= ">Splashscreen only<br />


</td>
</tr>

<tr>
<tr style=\"vertical-align:top\"> 
<td colspan=\"2\"  style=\"text-align:center\"  class=\"forumheader\">
<input class=\"button\" type=\"submit\" name=\"updatesettings\" value=\"Update Front Page Settings\" />
</td>
</tr>
</table>
</form>
</div>";

$ns -> tablerender("<div style=\"text-align:center\">Front Page Settings</div>", $text);
require_once("footer.php");

?>