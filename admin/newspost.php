<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/admin_newspost.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("../class2.php");
if(!getperms("H")){ header("location:../index.php"); }

require_once("auth.php");

if($_SERVER['QUERY_STRING'] != ""){
	$qs = explode(".", $_SERVER['QUERY_STRING']);
	$action = $qs[0];
	$id = $qs[1];
	$sql -> db_Select("submitnews", "*", "submitnews_id ='$id' ");
	list($submitnews_id, $submitnews_name, $submitnews_email, $submitnews_title, $submitnews_item, $submitnews_datestamp, $submitnews_ip, $submitnews_auth) = $sql-> db_Fetch();
	$news_title = $submitnews_title;
	$news_body = $submitnews_item;
	$news_source = "Submitted by ".$submitnews_name." ( ".$submitnews_email." )";
}

if(IsSet($_POST['news_allow_comments'])){
	$news_allow_comments = $_POST['news_allow_comments'];
}else{
	$news_allow_comments = 1;
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
	$news_body = stripslashes($news_body);
	$news_extended = stripslashes($news_extended);
	$news_source = stripslashes($news_source);
	$news_url = stripslashes($news_url);
	if($news_allow_comments == 0){
		$news_allow_comments = 1;
	}else{
		$news_allow_comments = 0;
	}
}

if(IsSet($_POST['submit'])){
//	if($_POST['news_allow_comments'] == 0){
//		$_POST['news_allow_comments'] = 1;
//	}else{
//		$_POST['news_allow_comments'] = 0;
//	}
	$message = $ix -> submit_item($_POST['news_id'], $_POST['news_title'], $_POST['news_body'], $_POST['news_extended'], $_POST['news_source'], $_POST['news_url'], $_POST['cat_id'], $_POST['news_allow_comments']);
	unset($news_id, $news_title, $news_body, $news_extended, $news_source, $news_url, $news_allow_comments);
	$rsd = new create_rss();
}

if(IsSet($_POST['delete'])){
	$sql -> db_Select("news", "*", "news_id='".$_POST['existing']."' ");
	list($news_id, $news_title, $news_body, $news_extended, $news_datestamp, $news_author, $news_source, $news_url, $news_category) = $sql-> db_Fetch();

	$text = "<div style=\"text-align:center\">
<b>'".$news_title."'</b><br /><br />
Are you absolutely certain you want to delete this news story? Once deleted it <b><u>cannot</u></b> be retreived.
<br /><br />
<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">
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
	$ix -> preview($news_id, $_POST['news_title'], $_POST['news_body'],  $_POST['news_extended'], $_POST['news_source'], $_POST['news_url'], $_POST['cat_id'], $_POST['news_allow_comments']);

	$news_category = $_POST['cat_id'];
	$news_title = stripslashes($_POST['news_title']);
	$news_body = stripslashes($_POST['news_body']);
	$news_extended = stripslashes($_POST['news_extended']);
	$news_source = stripslashes($_POST['news_source']);
	$news_url = stripslashes($_POST['news_url']);

}

if(IsSet($message)){
	$ns -> tablerender("", "<div style=\"text-align:center\"><b>".$message."</b></div>");
}


if(!$sql -> db_Select("news", "*", "ORDER BY news_datestamp DESC LIMIT 0,20", $mode="no_where")){
	$text = "No news items yet.<br />
	<form method=\"post\" action=\"$PHP_SELF\">";
}else{
	$text = "<div style=\"text-align:center\">
	<form method=\"post\" action=\"$PHP_SELF\" name=\"newspostform\">
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

$text .= "<table style=\"width:95%\">
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
$text .= "<span class=\"twelvept\"> [ <a href=\"news_category.php\">Add/Edit Categories</a> ]</span>
</td>
</tr>
<tr> 
<td style=\"width:20%\">Title:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"news_title\" size=\"60\" value=\"$news_title\" maxlength=\"200\" />
</td>
</tr>
<tr> 
<td style=\"width:20%\"><u>Body</u>:</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"news_body\" cols=\"80\" rows=\"10\">$news_body</textarea>
<br />
<input class=\"helpbox\" type=\"text\" name=\"helpb\" size=\"100\" />
<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\" onMouseOver=\"help('Insert link: [link]http://mysite.com[/link] or  [link=http://yoursite.com]Visit My Site[/link]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"addtext('[b][/b]')\" onMouseOver=\"help('Bold text: [b]This text will be bold[/b]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"addtext('[i][/i]')\" onMouseOver=\"help('Italic text: [i]This text will be italicised[/i]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"addtext('[u][/u]')\" onMouseOver=\"help('Underline text: [u]This text will be underlined[/u]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext('[img][/img]')\" onMouseOver=\"help('Insert image: [img]mypicture.jpg[/img]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext('[center][/center]')\" onMouseOver=\"help('Center align: [center]This text will be centered[/center]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext('[left][/left]')\" onMouseOver=\"help('Left align: [left]This text will be left aligned[/left]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext('[right][/right]')\" onMouseOver=\"help('Right align: [right]This text will be right aligned[/right]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext('[blockquote][/blockquote]')\" onMouseOver=\"help('Blockquote text: [blockquote]This text will be indented[/blockquote]')\" onMouseOut=\"help('')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"addtext('[code][/code]')\" onMouseOver=\"help('Code - preformatted text: [code]\$var = foobah;[/code]')\" onMouseOut=\"help('')\">
</td>
</tr>
<tr> 
<td style=\"width:20%\">Extended:</td>
<td style=\"width:80%\">
<textarea class=\"tbox\" name=\"news_extended\" cols=\"80\" rows=\"10\">$news_extended</textarea>
<br />
<input class=\"button\" type=\"button\" value=\"link\" onclick=\"addtext('[link=hyperlink url]hyperlink text[/link]')\">
<input class=\"button\" type=\"button\" value=\"b\" onclick=\"addtext2('[b][/b]')\">
<input class=\"button\" type=\"button\" value=\"i\" onclick=\"addtext2('[i][/i]')\">
<input class=\"button\" type=\"button\" value=\"u\" onclick=\"addtext2('[u][/u]')\">
<input class=\"button\" type=\"button\" value=\"img\" onclick=\"addtext2('[img][/img]')\">
<input class=\"button\" type=\"button\" value=\"center\" onclick=\"addtext2('[center][/center]')\">
<input class=\"button\" type=\"button\" value=\"left\" onclick=\"addtext2('[left][/left]')\">
<input class=\"button\" type=\"button\" value=\"right\" onclick=\"addtext2('[right][/right]')\">
<input class=\"button\" type=\"button\" value=\"blockquote\" onclick=\"addtext2('[blockquote][/blockquote]')\">
<input class=\"button\" type=\"button\" value=\"code\" onclick=\"addtext2('[code][/code]')\">
</tr>
<tr> 
<td style=\"width:20%\">Source:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"news_source\" size=\"60\" value=\"$news_source\" maxlength=\"100\" />
</td>
</tr>
<tr> 
<td style=\"width:20%\">URL:</td>
<td style=\"width:80%\">
<input class=\"tbox\" type=\"text\" name=\"news_url\" size=\"60\" value=\"$news_url\" maxlength=\"100\" />
</td>
</tr>

<tr> 
<td style=\"width:20%\">Allow comments?:</td>
<td style=\"width:80%\">";

if($news_allow_comments == 1){
	$text .= "<input type=\"checkbox\" name=\"news_allow_comments\" value=\"1\"  checked>";
}else{
	$text .= "<input type=\"checkbox\" name=\"news_allow_comments\" value=\"1\">";
}

$text .= "</td>
</tr>

<tr style=\"vertical-align: top;\">
<td colspan=\"2\"  style=\"text-align=center\">";
	
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
<input type=\"hidden\" name=\"news_id\" value=\"$news_id\">
</form>";
$ns -> tablerender("<div style=\"text-align:center\">News Post</div>", $text);
?>
<script type="text/javascript">
function addtext(sc){
	document.newspostform.news_body.value += sc;
}
function addtext2(sc){
	document.newspostform.news_extended.value += sc;
}
function fclear(){
	document.newspostform.news_body.value = "";
	document.newspostform.news_extended.value = "";
}
function help(help){
	document.newspostform.helpb.value = help;
}
</script>
<?php
require_once("footer.php");
?>

