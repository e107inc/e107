<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/newspost.php
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
if(!getperms("H")){ header("location:".e_HTTP."index.php"); }

require_once("auth.php");
require_once(e_BASE."classes/news_class.php");
$aj = new textparse;

if(e_QUERY != ""){
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$id = $qs[1];
	if($action == "ne"){
		$_POST['edit'] = TRUE;
		$_POST['existing'] = $id;
	}else if($action == "nd"){
		$_POST['delete'] = TRUE;
		$_POST['existing'] = $id;
	}else{
		$sql -> db_Select("submitnews", "*", "submitnews_id ='$id' ");
		list($submitnews_id, $submitnews_name, $submitnews_email, $submitnews_title, $submitnews_item, $submitnews_datestamp, $submitnews_ip, $submitnews_auth) = $sql-> db_Fetch();
		$news_title = $submitnews_title;
		$data = $submitnews_item;
		$news_source = "Submitted by ".$submitnews_name." ( ".$submitnews_email." )";
	}
}

$ix = new news;
$news_id = $_POST['news_id'];

if(IsSet($_POST['reset'])){
	$news_id = "";
}

if(IsSet($_POST['deletecancel'])){
	$message = "Delete cancelled.<br />";
}

if(IsSet($_POST['deleteconfirm'])){
	$message = $ix -> delete_item($_POST['news_id']);
	$news_id = "";
}

if(IsSet($_POST['edit'])){
	$row = $ix -> edit_item($_POST['existing']);
	extract($row);
	$news_title = stripslashes($news_title);
	$data = stripslashes($news_body);
	$news_extended = stripslashes($news_extended);
	$news_source = stripslashes($news_source);
	$news_url = stripslashes($news_url);
	$_POST['news_allow_comments'] = $news_allow_comments;
	$_POST['news_active'] = $news_active;
	if($news_start){$tmp = getdate($news_start);$_POST['startmonth'] = $tmp['mon'];$_POST['startday'] = $tmp['mday'];$_POST['startyear'] = $tmp['year'];}
	if($news_end){$tmp = getdate($news_end);$_POST['endmonth'] = $tmp['mon'];$_POST['endday'] = $tmp['mday'];$_POST['endyear'] = $tmp['year'];}
}

if(IsSet($_POST['submit'])){
	if(!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? $active_start = 0 : $active_start = mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	if(!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? $active_end = 0 : $active_end = mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));

	$message = $ix -> submit_item($_POST['news_id'], $_POST['news_title'], $_POST['data'], $_POST['news_extended'], $_POST['news_source'], $_POST['news_url'], $_POST['cat_id'], $_POST['news_allow_comments'], $active_start, $active_end, $always_active);
	unset($news_id, $news_title, $data, $news_extended, $news_source, $news_url, $_POST['news_allow_comments'], $active_start, $active_end, $_POST['news_active']);
	$rsd = new create_rss();
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("news", "*", "news_id='".$_POST['existing']."' ");
	$row = $sql -> db_Fetch(); extract($row);

	$text = "<div style=\"text-align:center\">
<b>'".$news_title."'</b><br /><br />
Are you absolutely certain you want to delete this news story? Once deleted it <b><u>cannot</u></b> be retreived.
<br /><br />
<form method=\"post\" action=\"".e_SELF."\">
<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"Cancel\" /> 
<input class=\"button\" type=\"submit\" name=\"deleteconfirm\" value=\"Confirm Delete\" /> 
<input type=\"hidden\" name=\"news_id\" value=\"$news_id\">
</form>
</div>";
$ns -> tablerender("Confirm Delete Category", $text);
	
	require_once("footer.php");
	exit;
}

if(IsSet($_POST['preview'])){
	if(!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? $active_start = 0 : $active_start = mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	if(!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? $active_end = 0 : $active_end = mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
	$temp = $ix -> preview($news_id, $_POST['news_title'], $_POST['data'],  $_POST['news_extended'], $_POST['news_source'], $_POST['news_url'], $_POST['cat_id'], $_POST['news_allow_comments'], $active_start, $active_end, $_POST['news_active']);
	$news_category = $temp[0];
	$news_title = $temp[1];
	$data = $temp[2];
	$news_extended = $temp[3];
	$news_source = $temp[4];
	$news_url = $temp[5];
	$news_title = $aj -> editparse($news_title);
	$data = $aj -> editparse($data);
	$news_extended = $aj -> editparse($news_extended);
	$news_source = $aj -> editparse($news_source);
	$news_url = $aj -> editparse($news_url);
}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}

if(!$sql -> db_Select("news", "*", "ORDER BY news_datestamp DESC LIMIT 0,20", $mode="no_where")){
	$text = "No news items yet.<br />
	<form method=\"post\" action=\"".e_SELF."\">";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"".e_SELF."\" name=\"dataform\">
	Existing News: 
	<select name=\"existing\" class=\"tbox\">";
	
	while(list($news_id_, $news_title_) = $sql-> db_Fetch()){
		$text .= "<option value=\"$news_id_\">".$news_title_."</option>";
	}
	$text .= "</select>
<input class=\"button\" type=\"submit\" name=\"edit\" value=\"Edit\" /> 
<input class=\"button\" type=\"submit\" name=\"delete\" value=\"Delete\" />
</div>
<br />
";
}

$text .= "
<div style=\"text-align:center\">
<table style=\"width:80%\">

<tr>
<td colspan=\"2\" style=\"text-align:center\">
<input class=\"button\" type=\"button\" onClick=\"openwindow()\"  value=\"Open HTML Editor\" />
<br /><br />
</td>
</tr>

<tr>
<td style=\"width:20%\">Category: </td>
<td style=\"width:80%\">";

if(!$sql -> db_Select("news_category")){
	$text .= "No categories set yet.";
}else{

	$text .= "
	<select name=\"cat_id\" class=\"tbox\">";
	
	while(list($cat_id, $cat_name, $cat_icon) = $sql-> db_Fetch()){
		if($news_category == $cat_id){
			$text .= "<option value=\"$cat_id\" selected>".$cat_name."</option>";
		}else{
			$text .= "<option value=\"$cat_id\">".$cat_name."</option>";
		}
	}
	$text .= "</select>";
}
$text .= " [ <a href=\"news_category.php\">Add/Edit Categories</a> ]
</td>
</tr>
<tr> 
<td style=\"width:20%\">Title:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"news_title\" size=\"80\" value=\"$news_title\" maxlength=\"200\" />
</td>
</tr>
<tr> 
<td style=\"width:20%\">Body:<br /></td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"data\" cols=\"80\" rows=\"10\" onselect=\"storeCaret(this);\" onclick=\"storeCaret(this);\" onkeyup=\"storeCaret(this);\">$data</textarea>
<br />
<input class=\"helpbox\" type=\"text\" name=\"helpb\" size=\"100\" />";
$text .= ren_help("addtext");
$text .= "</td>
</tr>
<tr> 
<td style=\"width:20%\">Extended:<br /></td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"news_extended\" cols=\"80\" rows=\"10\" onselect=\"storeCaret2(this);\" onclick=\"storeCaret2(this);\" onkeyup=\"storeCaret2(this);\">$news_extended</textarea>";
$text .= ren_help("addtext2");
$text .= "</tr>
<tr> 
<td style=\"width:20%\">Source:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"news_source\" size=\"80\" value=\"$news_source\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td style=\"width:20%\">URL:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"news_url\" size=\"80\" value=\"$news_url\" maxlength=\"100\" />
</td>
</tr>

<tr> 
<td style=\"width:20%\">Comments:</td>
<td style=\"width:80%\">";

if(!$_POST['news_allow_comments']){
	$text .= "<input name=\"news_allow_comments\" type=\"radio\" value=\"0\" checked>Enabled&nbsp;&nbsp;<input name=\"news_allow_comments\" type=\"radio\" value=\"1\">Disabled";
}else{
	$text .= "<input name=\"news_allow_comments\" type=\"radio\" value=\"0\">Enabled&nbsp;&nbsp;<input name=\"news_allow_comments\" type=\"radio\" value=\"1\" checked>Disabled";
}

$text .= " <span class=\"smalltext\">( Allow comments to be posted to this news item )";

$text .= "</td>
</tr>

<tr> 
<td style=\"width:20%\">Status:</td>
<td style=\"width:80%\">";


$text .= (!$_POST['news_active'] ? "<input name=\"news_active\" type=\"radio\" value=\"0\" checked>Enabled&nbsp;&nbsp;<input name=\"news_active\" type=\"radio\" value=\"1\">Disabled" : "<input name=\"news_active\" type=\"radio\" value=\"0\">Enabled&nbsp;&nbsp;<input name=\"news_active\" type=\"radio\" value=\"1\" checked>Disabled");

$text .= " <span class=\"smalltext\">( Enabled = visible on front page, disabled - not visible )
</td>
</tr>

<tr> 
<td style=\"width:20%\">Activation:<br /><span class=\"smalltext\">(Leave blank to disable auto-activation)</span></td>
<td style=\"width:80%\">";

$text .= "<br />Activate between: <select name=\"startday\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['startday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"startmonth\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['startmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"startyear\" class=\"tbox\"><option selected> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['startyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> and <select name=\"endday\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['endday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"endmonth\" class=\"tbox\"><option selected> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['endmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name=\"endyear\" class=\"tbox\"><option selected> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['endyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}

$text .= "</select>
<tr style=\"vertical-align: top;\">
<td colspan=\"2\"  style=\"text-align=center\"><br />";
	
if(IsSet($_POST['preview'])){
	$text .= "<input class=\"button\" type=\"submit\" name=\"preview\" value=\"Preview again\" /> ";
	if($news_id != ""){
		$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Update news in database\" /> ";
	}else{
		$text .= "<input class=\"button\" type=\"submit\" name=\"submit\" value=\"Post news to database\" /> ";
	}
}else{
	$text .= "<input class=\"button\" type=\"submit\" name=\"preview\" value=\"Preview\" /> ";
}
if(IsSet($id)){
	$text .= "<input class=\"button\" type=\"submit\" name=\"reset\" value=\"New story\" /> ";
}
$text .= "<input type=\"hidden\" name=\"news_id\" value=\"$news_id\">
</td>
</tr>
<tr>
<td colspan=\"2\"  class=\"smalltext\">

<br />
<br />
<span class=\"smalltext\">
Line breaks (&lt;br /&gt;) are auto added. <u>Underlined fields are required.</u>
</span>
</td>
</tr>
</table>
</div>
<input type=\"hidden\" name=\"news_id\" value=\"$news_id\">
</form>";
//<a href=\"#\" onclick=\"window.open('../htmlarea/index.php?Sent to textarea','Editor', 'top=100,left=100,resizable=no,width=670,height=600,scrollbars=no,menubar=no'); return false\">Open editor</a>";
$ns -> tablerender("<div style=\"text-align:center\">News Post</div>", $text);
?>
<script type="text/javascript">

function addtext(text) {
	text = ' ' + text + ' ';
	if (document.dataform.data.createTextRange && document.dataform.data.caretPos) {
		var caretPos = document.dataform.data.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.dataform.data.focus();
	} else {
	document.dataform.data.value  += text;
	document.dataform.data.focus();
	}
}

function addtext2(text) {
	text = ' ' + text + ' ';
	if (document.dataform.news_extended.createTextRange && document.dataform.news_extended.caretPos) {
		var caretPos = document.dataform.news_extended.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.dataform.news_extended.focus();
	} else {
	document.dataform.news_extended  += text;
	document.dataform.news_extended.focus();
	}
}

function storeCaret (textEl) {
	if (textEl.createTextRange) 
	textEl.caretPos = document.selection.createRange().duplicate();
}
function storeCaret2 (textEl) {
	if (textEl.createTextRange) 
	textEl.caretPos = document.selection.createRange().duplicate();
}

function fclear(){
	document.dataform.data.value = "";
	document.dataform.news_extended.value = "";
}
function help(help){
	document.dataform.helpb.value = help;
}
</script>
<?php
require_once("footer.php");

class create_rss{
	function create_rss(){
		/*
		# rss create
		# - parameters		none
		# - return				null
		# - scope					public
		*/
		$rsd = new db;
		$rsd -> db_Select("e107");
		list($e107_author, $e107_url, $e107_version, $e107_build, $e107_datestamp) = $rsd-> db_Fetch();
		$rsd -> db_Select("prefs");

		list($sitename, $siteurl, $sitebutton, $sitetag, $sitedescription, $siteadmin, $siteadminemail, $sitetheme, $posts, $chatbox_d, $chat_posts, $poll_d, $disclaimer, $headline_d, $headline_update, $article_d, $counter_d) = $rsd-> db_Fetch();
		$rsd -> db_Select("news", "*", "ORDER BY news_datestamp DESC LIMIT 0,10", $mode="no_where");
		$host = "http://".getenv("HTTP_HOST");

$rss = "<?xml version=\"1.0\"?>
<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\" \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">
<rss version=\"0.91\">
<channel>
<title>".SITENAME."</title>
<link>".SITEURL."</link>
<description>".SITEDESCRIPTION."</description>
<language>en-us</language>
<copyright>".SITEDISCLAIMER."</copyright>
<managingEditor>".SITEADMIN."</managingEditor>
<webMaster>".SITEADMINEMAIL."</webMaster>
<image>
<title>".SITENAME."</title> 
<url>".SITEBUTTON."</url> 
<link>".SITEURL."</link> 
<width>90</width> 
<height>30</height> 
<description>".SITETAG."</description> 
</image>
";

  while(list($news_id, $news_title, $data, $news_datestamp, $news_author, $news_source, $news_url, $news_catagory) = $rsd-> db_Fetch()){
  		$tmp = explode(" ", $data);
		unset($nb);
		for($a=0; $a<=100; $a++){
			$nb .= $tmp[$a]." ";
		}
  		$nb = htmlentities($nb); 
		$text .= $news_title."\n".SITEURL."/comment.php?".$news_id."\n\n";
		$rss .= "<item>
<title>".$news_title."</title>
<description>".$nb."</description>
<link>".SITEURL."/comment.php?".$news_id."</link> 
</item>
";
	}
	$rss .= "</channel>
</rss>";
	$fp = fopen("../backend/news.xml","w");
	@fwrite($fp, $rss);
	fclose($fp);

	$fp = fopen("../backend/news.txt","w");
	@fwrite($fp, $text);
	fclose($fp);

	if(!fwrite){
		$text = "<div style=\"text-align:center\">".LAN_19."</div>";
		$ns -> tablerender("<div style=\"text-align:center\">".LAN_20."</div>", $text);
	}
}
}

function ren_help($func){
	$str ="<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"$func('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"font-weight:bold; width: 20px\" value=\"b\" onclick=\"$func('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"font-style:italic; width: 20px\" value=\"i\" onclick=\"$func('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" style=\"text-decoration: underline; width: 20px\" value=\"u\" onclick=\"$func('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"$func('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"$func('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"$func('[left][/left]')\" onMouseOver=\"help('Left align: [left]This text will be left aligned[/left]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"$func('[right][/right]')\" onMouseOver=\"help('Right align: [right]This text will be right aligned[/right]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"$func('[blockquote][/blockquote]')\" onMouseOver=\"help('Blockquote text: [blockquote]This text will be indented[/blockquote]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"$func('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">";	
	return $str;
}

?>

