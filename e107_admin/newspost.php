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
if(!getperms("H")){ header("location:".e_BASE."index.php"); exit;}
require_once("auth.php");
require_once(e_HANDLER."userclass_class.php");
require_once(e_HANDLER."news_class.php");
require_once(e_HANDLER."ren_help.php");
$aj = new textparse;


/*
//	added for possible future extention to news sectioning ...
$filename = MAINTHEME."theme.php";
$fd = fopen ($filename, "r");
$themefile = fread ($fd, filesize ($filename));
fclose ($fd);
define("SECTIONING", (strstr($themefile, "\$NEWSHEADER") ? TRUE : FALSE));
$categories = substr_count($themefile, "NEWS_CATEGORY");
if(SECTIONING){
	echo "Sectioning enabled, categories: ".$categories;
}else{
	echo "Cant find \$NEWSHEADER";
}
*/

if($_POST['titleonly']){
	$_POST['data'] = "&nbsp;".$_POST['data'];
}


if(e_QUERY){
	$qs = explode(".", e_QUERY);
	$action = $qs[0];
	$id = $qs[1];
	if($action == "ne"){
		$_POST['edit'] = TRUE;
		$_POST['existing'] = $id;
	}else if($action == "nd"){
		$_POST['delete'] = TRUE;
		$_POST['confirm'] = TRUE;
		$_POST['existing'] = $id;


	}else if($action == "news"){
		$sql -> db_Select("upload", "*", "upload_id=$id");
		$row = $sql -> db_Fetch(); extract($row);

		$post_author_id = substr($upload_poster, 0, strpos($upload_poster, "."));
		$post_author_name = substr($upload_poster, (strpos($upload_poster, ".")+1));
		$poster = (!$post_author_id ? "<b>".$post_author_name."</b>" : "<a href='".e_BASE."user.php?id.".$post_author_id."'><b>".$post_author_name."</b></a>");

		$_POST['news_title'] = $upload_name;
		$_POST['data'] = $upload_type."\n".$upload_filename." by ".$poster." ...\n[blockquote]".$upload_description."[/blockquote]";

	}else if($action == "sn"){
		$sql -> db_Select("submitnews", "*", "submitnews_id ='$id' ");
		$row = $sql -> db_Fetch(); extract($row);
		$_POST['news_title'] = $submitnews_title;
		$_POST['data'] = $submitnews_item."\n\nSubmitted by ".$submitnews_name." ( ".str_replace("@", ".at.", $submitnews_email)." )";
	}
}

$ix = new news;
$news_id = $_POST['news_id'];

if(IsSet($_POST['reset'])){
	$news_id = "";
}

If(IsSet($_POST['delete'])){
	if($_POST['confirm']){
		$sql -> db_Delete("news",  "news_id='".$_POST['existing']."' ");
		$sql -> db_Delete("comments", "comment_item_id='".$_POST['existing']."' ");
		$message = NWSLAN_1;
	}else{
		$message = NWSLAN_2;
	}
}


if(IsSet($_POST['edit'])){
	$sql -> db_Select("news", "*", "news_id='".$_POST['existing']."' ");
	$row = $sql-> db_Fetch();
	extract($row);
	$_POST['news_title'] = $news_title;
	$_POST['data'] = $aj -> formtparev($news_body);
	$_POST['news_extended'] = $aj -> formtparev($news_extended);
	$_POST['news_allow_comments'] = $news_allow_comments;
	$_POST['news_class'] = $news_class;
	$_POST['cat_id'] = $news_category;
	if($news_start){$tmp = getdate($news_start);$_POST['startmonth'] = $tmp['mon'];$_POST['startday'] = $tmp['mday'];$_POST['startyear'] = $tmp['year'];}
	if($news_end){$tmp = getdate($news_end);$_POST['endmonth'] = $tmp['mon'];$_POST['endday'] = $tmp['mday'];$_POST['endyear'] = $tmp['year'];}
	$comment_total = $sql -> db_Count("comments", "(*)", " WHERE comment_item_id='$news_id' AND comment_type='0' ");
}



// submit -------------------------------------------------------------------------------------------------------------------------------------------------------------------
if(IsSet($_POST['submit'])){
	$_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	$_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
	$_POST['admin_id'] = USERID;
	$_POST['admin_name'] = USERNAME;
	$_POST['comment_total'] = $comment_total;
	$_POST['news_datestamp'] = time();
	$message = $ix -> submit_item($_POST);
	unset($_POST['news_title'], $_POST['cat_id'], $_POST['data'], $_POST['news_extended'], $_POST['news_allow_comments'], $_POST['startday'], $_POST['startmonth'], $_POST['startyear'], $_POST['endday'], $_POST['endmonth'], $_POST['endyear'], $_POST['news_id'], $_POST['news_class']);
	$rsd = new create_rss();
}

// preview -------------------------------------------------------------------------------------------------------------------------------------------------------------------
if(IsSet($_POST['preview'])){
	$_POST['active_start'] = (!$_POST['startmonth'] || !$_POST['startday'] || !$_POST['startyear'] ? 0 : mktime (0, 0, 0, $_POST['startmonth'], $_POST['startday'], $_POST['startyear']));
	$_POST['active_end'] = (!$_POST['endmonth'] || !$_POST['endday'] || !$_POST['endyear'] ? 0 : mktime (0, 0, 0, $_POST['endmonth'], $_POST['endday'], $_POST['endyear']));
	$sql -> db_Select("news_category", "*",  "category_id='".$_POST['cat_id']."' ");
	list($_POST['category_id'], $_POST['category_name'], $_POST['category_icon']) = $sql-> db_Fetch();
	$_POST['admin_id'] = USERID;
	$_POST['admin_name'] = USERNAME;
	$_POST['comment_total'] = $comment_total;
	$_POST['news_datestamp'] = time();

	$_POST['news_title'] = $aj -> formtpa($_POST['news_title']);
	$_POST['data'] = $aj -> formtpa($_POST['data']);
	$_POST['news_extended'] = $aj -> formtpa($_POST['news_extended']);

	$ix -> render_newsitem($_POST);
	$_POST['news_title'] = $aj -> formtpa($_POST['news_title']);
	$_POST['data'] = $aj -> formtparev($_POST['data']);
	$_POST['news_extended'] = $aj -> formtparev($_POST['news_extended']);
	
}
// -------------------------------------------------------------------------------------------------------------------------------------------------------------------

if(IsSet($message)){
	$ns -> tablerender("", "<div style='text-align:center'><b>".$message."</b></div>");
}

$text = "<div style='text-align:center'>
<form method='post' action='".e_SELF."' name='dataform'>
<table style='width:95%' class='fborder'>
<tr>
<td colspan='2' style='text-align:center' class='forumheader'>";

if(!$sql -> db_Select("news", "*", "ORDER BY news_datestamp DESC LIMIT 0,20", $mode="no_where")){
	$text .= NWSLAN_3;
}else{
	$text .= "<span class='defaulttext'>".NWSLAN_4.":</span> 
	<select name='existing' class='tbox'>";
	
	while(list($news_id_, $news_title_) = $sql-> db_Fetch()){
		$text .= "<option value='$news_id_'>".$news_title_."</option>";
	}
	$text .= "</select>
<input class='button' type='submit' name='edit' value='".NWSLAN_7."' /> 
<input class='button' type='submit' name='delete' value='".NWSLAN_8."' />
<input type='checkbox' name='confirm' value='1'><span class='smalltext'> ".NWSLAN_9."</span>
";
}

$text .= "
</td></tr>
<tr>
<td colspan='2' style='text-align:center' class='forumheader2'>
<input class='button' type='button' onClick='openwindow()'  value='".NWSLAN_5."' />
</td>
</tr>

<tr>
<td style='width:20%' class='forumheader3'>".NWSLAN_6.": </td>
<td style='width:80%' class='forumheader3'>";

if(!$sql -> db_Select("news_category")){
	$text .= NWSLAN_10;
}else{

	$text .= "
	<select name='cat_id' class='tbox'>";
	
	while(list($cat_id, $cat_name, $cat_icon) = $sql-> db_Fetch()){
		$text .= ($_POST['cat_id'] == $cat_id ? "<option value='$cat_id' selected>".$cat_name."</option>" : "<option value='$cat_id'>".$cat_name."</option>");
	}
	$text .= "</select>";
}
$text .= " [ <a href='news_category.php'>".NWSLAN_11."</a> ]
</td>
</tr>
<tr> 
<td style='width:20%' class='forumheader3'>".NWSLAN_12.":</td>
<td style='width:80%' class='forumheader3'>
<input class='tbox' type='text' name='news_title' size='80' value='".$_POST['news_title']."' maxlength='200' />
</td>
</tr>
<tr> 
<td style='width:20%' class='forumheader3'>".NWSLAN_13.":<br /></td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='data' cols='80' rows='10'>".$_POST['data']."</textarea>
<br />
<input class='helpbox' type='text' name='helpb' size='100' />
<br />
";
$text .= ren_help("addtext", TRUE);
$text .= "</td>
</tr>
<tr> 
<td style='width:20%' class='forumheader3'>".NWSLAN_14.":<br /></td>
<td style='width:80%' class='forumheader3'>
<textarea class='tbox' name='news_extended' cols='80' rows='10'>".$_POST['news_extended']."</textarea>
<br />
";
$text .= ren_help("addtext2");
$text .= "

<tr> 
<td style='width:20%' class='forumheader3'>".NWSLAN_15.":</td>
<td style='width:80%' class='forumheader3'>".
($_POST['news_allow_comments'] ? "<input name='news_allow_comments' type='radio' value='0'>".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1' checked>".NWSLAN_17 : "<input name='news_allow_comments' type='radio' value='0' checked>".NWSLAN_16."&nbsp;&nbsp;<input name='news_allow_comments' type='radio' value='1'>".NWSLAN_17)."
 <span class='smalltext'>( ".NWSLAN_18." )
 </td>
</tr>

<tr> 
<td style='width:20%' class='forumheader3'>".NWSLAN_30.":</td>
<td style='width:80%' class='forumheader3'>
".(substr($_POST['data'], 0, 6) == "&nbsp;" ? "<input name='titleonly' type='radio' value='1' checked>".NWSLAN_16."&nbsp;&nbsp;<input name='titleonly' type='radio' value='0'>".NWSLAN_17 : "<input name='titleonly' type='radio' value='1'>".NWSLAN_16."&nbsp;&nbsp;<input name='titleonly' type='radio' value='0' checked>".NWSLAN_17)."
</td>
</tr>


<tr> 
<td style='width:20%' class='forumheader3'>".NWSLAN_19.":<br /><span class='smalltext'>(".NWSLAN_20.")</span></td>
<td style='width:80%' class='forumheader3'>";

$text .= "<br />".NWSLAN_21.": <select name='startday' class='tbox'><option selected> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['startday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name='startmonth' class='tbox'><option selected> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['startmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name='startyear' class='tbox'><option selected> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['startyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> and <select name='endday' class='tbox'><option selected> </option>";
for($a=1; $a<=31; $a++){
	$text .= ($a == $_POST['endday'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name='endmonth' class='tbox'><option selected> </option>";
for($a=1; $a<=12; $a++){
	$text .= ($a == $_POST['endmonth'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}
$text .= "</select> <select name='endyear' class='tbox'><option selected> </option>";
for($a=2003; $a<=2010; $a++){
	$text .= ($a == $_POST['endyear'] ? "<option selected>".$a."</option>" : "<option>".$a."</option>");
}

$text .= "</select>

<tr>
<td class='forumheader3'>
".NWSLAN_22.":<br /><span class='smalltext'>(".NWSLAN_23.")</span>
</td>
<td class='forumheader3'>".r_userclass("news_class",$_POST['news_class'])."

</td></tr>

<tr style='vertical-align: top;'>
<td colspan='2'  style='text-align=center' class='forumheader'>";
	
if(IsSet($_POST['preview'])){
	$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_24."' /> ";
	if($news_id != ""){
		$text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_25."' /> ";
	}else{
		$text .= "<input class='button' type='submit' name='submit' value='".NWSLAN_26."' /> ";
	}
}else{
	$text .= "<input class='button' type='submit' name='preview' value='".NWSLAN_27."' /> ";
}
if(IsSet($id)){
	$text .= "<input class='button' type='submit' name='reset' value='".NWSLAN_28."' /> ";
}
$text .= "<input type='hidden' name='news_id' value='$news_id'>
</td>
</tr>
</table>
<input type='hidden' name='news_id' value='$news_id'>
</form>
</div>";
$ns -> tablerender("<div style='text-align:center'>".NWSLAN_29."</div>", $text);
?>
<script type="text/javascript">


function addtext(str){
	document.dataform.data.value += str;
}

function addtext2(str){
	document.dataform.news_extended.value += str;
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

/*function storeCaret (textEl) {
	if (textEl.createTextRange) 
	textEl.caretPos = document.selection.createRange().duplicate();
}
*/

require_once("footer.php");

class create_rss{
	function create_rss(){
		/*
		# rss create
		# - parameters		none
		# - return				null
		# - scope					public
		*/
		global $sql;
		$pubdate = strftime("%a, %d %b %Y %I:%M:00 GMT", time());

		$sitebutton = (strstr(SITEBUTTON, "http:") ? SITEBUTTON : SITEURL.str_replace("../", "", e_IMAGE).SITEBUTTON);
		$sitedisclaimer = ereg_replace("<br />|\n", "", SITEDISCLAIMER);

	$rss = "<?xml version=\"1.0\"?>
<rss version=\"2.0\">
<channel>
  <title>".SITENAME."</title> 
  <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."index.php</link> 
  <description>".SITEDESCRIPTION."</description> 
  <language>en-gb</language> 
  <copyright>".$sitedisclaimer."</copyright> 
  <managingEditor>".SITEADMIN."</managingEditor> 
  <webMaster>".SITEADMINEMAIL."</webMaster> 
  <pubDate>$pubdate</pubDate>
  <lastBuildDate>$pubdate</lastBuildDate>
  <docs>http://backend.userland.com/rss</docs>
  <generator>e107 website system (http://e107.org)</generator>
  <ttl>60</ttl>

  <image>
    <title>".SITENAME."</title> 
    <url>".$sitebutton."</url> 
    <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."index.php</link> 
    <width>88</width> 
    <height>31</height> 
    <description>".SITETAG."</description>
  </image>
   
  <textInput>
    <title>Search</title>
    <description>Search ".SITENAME."</description>
    <name>query</name>
    <link>".SITEURL.(substr(SITEURL, -1) == "/" ? "" : "/")."search.php</link>
  </textInput>
  ";

	$sql2 = new db;

	$sql -> db_Select("news", "*", "news_class=0 AND (news_start=0 || news_start < ".time().") AND (news_end=0 || news_end>".time().") ORDER BY news_datestamp DESC LIMIT 0, 10");
	while($row = $sql -> db_Fetch()){
		extract($row);

		$sql2 -> db_Select("news_category", "*",  "category_id='$news_category' ");
		$row = $sql2 -> db_Fetch(); extract($row);
		
		$sql2 -> db_Select("user", "user_name", "user_id=$news_author");
		$row = $sql2 -> db_Fetch(); extract($row);

		$tmp = explode(" ", $news_body);
		unset($nb);
		for($a=0; $a<=100; $a++){
			$nb .= $tmp[$a]." ";
		}
		if($tmp[($a-2)]){ $nb .= " [more ...]"; }
  		$nb = htmlentities($nb);
		$wlog .= $news_title."\n".SITEURL."comment.php?".$news_id."\n\n";

		$itemdate = strftime("%a, %d %b %Y %I:%M:00 GMT", $news_datestamp);



   
  $rss .= "<item>
    <title>$news_title</title> 
    <link>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id."</link> 
    <description>$nb</description>
    <category domain=\"".SITEURL."\">$category_name</category>
    <comments>http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id."</comments>
    <author>$user_name</author>
    <pubDate>$itemdate</pubDate>
    <guid isPermaLink=\"true\">http://".$_SERVER['HTTP_HOST'].e_HTTP."comment.php?".$news_id."</guid>
  </item>
  ";
   
	}

   
	$rss .= "</channel>
</rss>";

	$rss = str_replace("&nbsp;", " ", $rss);


	$fp = fopen(e_FILE."backend/news.xml","w");
	@fwrite($fp, $rss);
	fclose($fp);

	$fp = fopen(e_FILE."backend/news.txt","w");
	@fwrite($fp, $wlog);
	fclose($fp);

	if(!fwrite){
		$text = "<div style='text-align:center'>".LAN_19."</div>";
		$ns -> tablerender("<div style='text-align:center'>".LAN_20."</div>", $text);
	}
}
}
?>

