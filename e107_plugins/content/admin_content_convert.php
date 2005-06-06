<?php
/*
+---------------------------------------------------------------+
|        e107 website system
|        /admin/review.php
|
|        ©Steve Dunstan 2001-2002
|        http://e107.org
|        jalist@e107.org
|
|        Released under the terms and conditions of the
|        GNU General Public License (http://gnu.org).
|
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/admin_content_convert.php,v $
|		$Revision: 1.10 $
|		$Date: 2005-06-06 13:28:13 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

require_once("../../class2.php");

if(!getperms("P")){header("location:".e_BASE."index.php"); exit; }
$e_sub_cat = 'content';
$plugindir = e_PLUGIN."content/";

$lan_file = $plugindir.'languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : $plugindir.'languages/English/lan_content.php');
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once($plugindir."handlers/content_class.php");
$aa = new content;
require_once($plugindir."handlers/content_convert_class.php");
$ac = new content_convert;
e107_require_once(e_HANDLER.'arraystorage_class.php');
$eArrayStorage = new ArrayData();
global $tp;

if (e_QUERY){
	$qs = explode(".", e_QUERY);
}

$stylespacer = "style='height:20px; border:0;'";

//create default mainparent category for content, review and article
if(isset($_POST['create_default'])){
		$content_mainarray			= $ac -> create_mainparent("content", "1", "1");
		$content_main_checkpresent	= $content_mainarray[0];
		$content_main_msg			= $content_mainarray[1];

		$article_mainarray			= $ac -> create_mainparent("article", "1", "2");
		$article_main_checkpresent	= $article_mainarray[0];
		$article_main_msg			= $article_mainarray[1];

		$review_mainarray			= $ac -> create_mainparent("review", "1", "3");
		$review_main_checkpresent	= $review_mainarray[0];
		$review_main_msg			= $review_mainarray[1];

		$text = "<table class='fborder' style='width:95%; padding:0px;'>";
		$text .= $ac -> results_conversion_mainparent($content_mainarray, $review_mainarray, $article_mainarray);
		$text .= "</table>";

		$caption = CONTENT_ADMIN_CONVERSION_LAN_52;
		$ns -> tablerender($caption, $text);
}


//convert old content table
if(isset($_POST['convert_table'])){
		unset($text);

		// ##### STAGE 1 : ANALYSE OLD CONTENT --------------------------------------------------------
		//analyse old content table
		if(!is_object($sql)){ $sql = new db; }
		$totaloldcontentrows		= $sql -> db_Count("content");
		$totaloldrowscat_article	= $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '6'");
		$totaloldrowscat_review		= $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '10'");
		$totaloldrowsitem_content	= $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '1'");
		$totaloldrowsitem_review	= $sql -> db_Count("content", "(*)", "WHERE content_type = '3' || content_type = '16'");
		$totaloldrowsitem_article	= $sql -> db_Count("content", "(*)", "WHERE content_type = '0' || content_type = '15'");

		//content page:		$content_parent == "0" && $content_type == "1"
		//review category:	$content_parent == "0" && $content_type == "10"
		//article category:	$content_parent == "0" && $content_type == "6"
		//review:	$content_type == "3" || $content_type == "16"
		//article:	$content_type == "0" || $content_type == "15"

		//analyse unknown rows
		$unknown_array				= $ac -> analyse_unknown();
		$unknown_bug				= $unknown_array[0];
		$unknown_bug_id				= $unknown_array[1];

		//create mainparent
		$content_mainarray			= $ac -> create_mainparent("content", $totaloldrowsitem_content, "1");
		$content_main_checkpresent	= $content_mainarray[0];
		$content_main_msg			= $content_mainarray[1];

		$article_mainarray			= $ac -> create_mainparent("article", $totaloldrowscat_article, "2");
		$article_main_checkpresent	= $article_mainarray[0];
		$article_main_msg			= $article_mainarray[1];

		$review_mainarray			= $ac -> create_mainparent("review", $totaloldrowscat_review, "3");
		$review_main_checkpresent	= $review_mainarray[0];
		$review_main_msg			= $review_mainarray[1];

		//convert categories
		$article_cat_array			= $ac -> convert_category("article", "content_parent = '0' AND content_type = '6'", "2");
		$article_cat_checkpresent	= $article_cat_array[0];
		$article_cat_valid			= $article_cat_array[1];
		$article_cat_bug			= $article_cat_array[2];
		$article_cat_count			= $article_cat_array[3];

		$review_cat_array			= $ac -> convert_category("review", "content_parent = '0' AND content_type = '10'", "3");
		$review_cat_checkpresent	= $review_cat_array[0];
		$review_cat_valid			= $review_cat_array[1];
		$review_cat_bug				= $review_cat_array[2];
		$review_cat_count			= $review_cat_array[3];

		//convert rows
		$content_array				= $ac -> convert_row("content", "content_parent = '0' AND content_type = '1'", "1");
		$content_present			= $content_array[0];
		$content_count				= $content_array[1];
		$content_valid				= $content_array[2];
		$content_bug_insert			= $content_array[3];	
		$content_bug_oldcat			= $content_array[4];
		$content_bug_newcat			= $content_array[5];

		$article_array				= $ac -> convert_row("article", "content_type = '0' || content_type = '15'", "2");
		$article_present			= $article_array[0];
		$article_count				= $article_array[1];
		$article_valid				= $article_array[2];
		$article_bug_insert			= $article_array[3];	
		$article_bug_oldcat			= $article_array[4];
		$article_bug_newcat			= $article_array[5];

		$review_array				= $ac -> convert_row("review", "content_type = '3' || content_type = '16'", "3");
		$review_present				= $review_array[0];
		$review_count				= $review_array[1];
		$review_valid				= $review_array[2];
		$review_bug_insert			= $review_array[3];	
		$review_bug_oldcat			= $review_array[4];
		$review_bug_newcat			= $review_array[5];

		$ac -> convert_comments();
		$ac -> convert_rating();

		$conversion_analyses_rows_total = $totaloldcontentrows;
		$conversion_analyses_rows_converted = (count($article_cat_valid) + count($review_cat_valid) + count($content_valid) + count($article_valid) + count($review_valid));
		$conversion_analyses_rows_warning = (count($content_bug_oldcat) + count($content_bug_newcat) + count($article_bug_oldcat) + count($article_bug_newcat) + count($review_bug_oldcat) + count($review_bug_newcat));
		$conversion_analyses_rows_failed = (count($article_cat_bug) + count($review_cat_bug) + count($content_bug_insert) + count($article_bug_insert) + count($review_bug_insert) + count($unknown_bug_id));

		$text = "
		<table class='fborder' style='width:95%; padding:0px;'>";

		//conversion analysis
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_11."</td></tr>
		<tr>
			<td class='forumheader3' colspan='2'>
				".CONTENT_ADMIN_CONVERSION_LAN_12.": ".$conversion_analyses_rows_total."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_13.": ".$conversion_analyses_rows_converted."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_14.": ".$conversion_analyses_rows_warning."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_15.": ".$conversion_analyses_rows_failed."<br />
			</td>
		</tr>";

		$text .= "<tr><td $stylespacer colspan='2'></td></tr>";

		//old content table : analysis
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_16."</td></tr>
		<tr>
			<td class='forumheader3' colspan='2'>
				".CONTENT_ADMIN_CONVERSION_LAN_17.": ".$totaloldcontentrows."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_0." ".CONTENT_ADMIN_CONVERSION_LAN_6.": ".$totaloldrowsitem_content."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_4.": ".$totaloldrowscat_review."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_6.": ".$totaloldrowsitem_review."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_4.": ".$totaloldrowscat_article."<br />
				".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_6.": ".$totaloldrowsitem_article."<br />";

				$knownrows = $totaloldrowscat_article + $totaloldrowscat_review + $totaloldrowsitem_content + $totaloldrowsitem_review + $totaloldrowsitem_article;
				if($totaloldcontentrows > $knownrows ){
					$text .= CONTENT_ADMIN_CONVERSION_LAN_18.": ".($totaloldcontentrows - $knownrows)."<br />";
				}else{
					$text .= CONTENT_ADMIN_CONVERSION_LAN_19."<br />";
				}

			$text .= "
			</td>
		</tr>";

		$text .= "<tr><td $stylespacer colspan='2'></td></tr>";

		//unknown rows
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_51."</td></tr>";
		if(count($unknown_bug) > 0 ){
			$text .= "<tr><td class='forumheader3' colspan='2'>";
			$text .= $rs -> form_open("post", e_SELF."?unknown", "dataform", "", "enctype='multipart/form-data'");
			for($i=0;$i<count($unknown_bug);$i++){
				$text .= CONTENT_ICON_ERROR." ".$unknown_bug[$i]." ".$rs -> form_hidden("unknownrows[]", $unknown_bug_id[$i])."<br />";
			}
			$text .= $rs -> form_button("submit", "convert_unknown", "manually convert unknown rows")."<br />";
			$text .= $rs -> form_close()."</td></tr>";
		}

		$text .= "
		<tr><td $stylespacer colspan='2'></td></tr>
		
		".$ac -> results_conversion_mainparent($content_mainarray, $review_mainarray, $article_mainarray)."
		<tr><td $stylespacer colspan='2'></td></tr>
		
		<tr><td class='fcaption' colspan='2'>".strtoupper("content : ".CONTENT_ADMIN_CONVERSION_LAN_27)."</td></tr>
		".$ac -> results_conversion_row("content", $content_array, $totaloldrowsitem_content)."
		<tr><td $stylespacer colspan='2'></td></tr>
		
		<tr><td class='fcaption' colspan='2'>".strtoupper("review : ".CONTENT_ADMIN_CONVERSION_LAN_27)."</td></tr>
		".$ac -> results_conversion_category("review", $review_cat_array, $totaloldrowscat_review)."
		".$ac -> results_conversion_row("review", $review_array, $totaloldrowsitem_review)."
		<tr><td $stylespacer colspan='2'></td></tr>
		
		<tr><td class='fcaption' colspan='2'>".strtoupper("article : ".CONTENT_ADMIN_CONVERSION_LAN_27)."</td></tr>
		".$ac -> results_conversion_category("article", $article_cat_array, $totaloldrowscat_article)."
		".$ac -> results_conversion_row("article", $article_array, $totaloldrowsitem_article)."
		<tr><td $stylespacer colspan='2'></td></tr>
		
		</table>";

		$caption = CONTENT_ADMIN_CONVERSION_LAN_42;
		$ns -> tablerender($caption, $text);

}




//show start page for manual conversion of unknown rows
if($qs[0] == "unknown" && !isset($qs[1])){
		unset($text);

		if(!is_object($sql)){ $sql = new db; }
		if(!is_object($sql2)){ $sql2 = new db; }

		//get all parents into a form option array
		$catarray	= $aa -> getCategoryTree("", "", FALSE);
		$array		= array_keys($catarray);

		$options = "";
		foreach($array as $catid){
			$category_total = $sql -> db_Select($plugintable, "content_id, content_heading, content_parent", "content_id='".$catid."' ");
			$row = $sql -> db_Fetch();

			$pre = "";
			$js = "";
			if($row['content_parent'] == "0"){		//main parent level
				$js = "style='font-weight:bold;'";
			}else{									//sub level
				for($b=0;$b<(count($catarray[$catid])/2)-1;$b++){
					$pre .= "_";
				}
			}
			$options .= $rs -> form_option($row['content_heading'], "0", $row['content_id'], $js );
		}

		$text .= $rs -> form_open("post", e_SELF."?unknown.conversion", "dataform", "", "enctype='multipart/form-data'");
		$text .= "<table class='fborder' style='width:95%;'>";

		for($i=0;$i<count($_POST['unknownrows']);$i++){
			$item = $sql -> db_Select("content", "*", " content_id = '".$_POST['unknownrows'][$i]."' ");
			$row = $sql -> db_Fetch();

			$text .= "
			<tr>
			<td class='forumheader3'>".CONTENT_ICON_ERROR." ".$row['content_id']." ".$row['content_heading']." (parent=".$row['content_parent']." - type=".$row['content_type'].")</td>
			<td class='forumheader3'>";

			if(!$sql2 -> db_Select($plugintable, "content_id as newcontentid, content_heading as newcontentheading", "content_parent = '0' ")){
				//$text .= "no main parents present, manually conversion of old rows is not possible";
			}else{
				$cid = $row['content_id'];
				$text .= "
				".$rs -> form_select_open("newcontent_parent[{$cid}]")."
				".$rs -> form_option(CONTENT_ADMIN_CONVERSION_LAN_57, "0", "")."
				".$options."
				";
			}
			$text .= $rs -> form_select_close()."
			</td>
			</tr>";
		}
		$text .= "<tr><td class='forumheader' colspan='2' style='text-align:center;'>".$rs -> form_button("submit", "convert_unknownrows", "convert unknown rows")."</td></tr>";
		$text .= "</table>".$rs -> form_close();

		$caption = "manually convert unknown rows";
		$ns -> tablerender($caption, $text);
}

//convert unknown rows and show results
if($qs[0] == "unknown" && $qs[1] == "conversion"){
		unset($text);
		if(isset($_POST['convert_unknownrows'])){
			foreach($_POST['newcontent_parent'] as $key => $value){
				if($value == CONTENT_ADMIN_CONVERSION_LAN_57){
					break;
				}
				$oldrow = $sql -> db_Select("content", "*", "content_id = '".$key."' ");
				if(!$oldrow){
					return FALSE;
				}
				$row = $sql -> db_Fetch();

				//summary can contain link to image in e107_images/link_icons/".$summary." THIS STILL NEEDS TO BE CHECKED
				$newcontent_heading		= $tp -> toDB($row['content_heading']);
				$newcontent_subheading	= ($row['content_subheading'] ? $tp -> toDB($row['content_subheading']) : "");
				$newcontent_summary		= ($row['content_summary'] ? $tp -> toDB($row['content_summary']) : "");
				$newcontent_text		= $tp -> toDB($row['content_content']);
				$newcontent_author		= (is_numeric($row['content_author']) ? $row['content_author'] : "0^".$row['content_author']);
				$newcontent_icon		= "";
				$newcontent_attach		= "";
				$newcontent_images		= "";
				$newcontent_parent		= $value;
				$newcontent_comment		= $row['content_comment'];
				$newcontent_rate		= "0";
				$newcontent_pe			= $row['content_pe_icon'];
				$newcontent_refer		= "0";
				$newcontent_starttime	= $row['content_datestamp'];
				$newcontent_endtime		= "0";
				$newcontent_class		= $row['content_class'];
				$newcontent_pref		= "";

				if(!is_object($sql2)){ $sql2 = new db; }
				$sql2 -> db_Insert($plugintable, "'".$row['content_id']."', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '0.0' ");

				if(!is_object($sql3)){ $sql3 = new db; }
				if(!$sql3 -> db_Select($plugintable, "content_id", "content_heading = '".$newcontent_heading."' ")){
					$text .= CONTENT_ICON_ERROR." conversion not succesfull : ".$row['content_id']." ".$row['content_heading']."<br />";
				}else{
					$text .= CONTENT_ICON_OK." conversion succesfull : ".$row['content_id']." ".$row['content_heading']."<br />";
				}
			}
		}
		$caption = "conversion results for unknown rows";
		$ns -> tablerender($caption, $text);
}


//show link to start managing the content management plugin
if(isset($_POST['convert_table']) || isset($_POST['create_default']) || isset($qs[0])){
		$text = "<div style='text-align:center'>".CONTENT_ADMIN_CONVERSION_LAN_46."</div>";
		$caption = CONTENT_ADMIN_CONVERSION_LAN_47;
		$ns -> tablerender($caption, $text);
}


require_once(e_ADMIN."footer.php");

?>