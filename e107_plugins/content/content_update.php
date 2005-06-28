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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/content/content_update.php,v $
|     $Revision: 1.3 $
|     $Date: 2005-06-28 11:32:06 $
|     $Author: lisa_ $
+----------------------------------------------------------------------------+
*/
if(!defined('e_HTTP'))
{
	exit;
}

require_once(e_HANDLER."form_handler.php");
$rs = new form;

global $ns, $sql;

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)

require_once($plugindir."handlers/content_defines.php");
require_once($plugindir."handlers/content_class.php");
$aa = new content;
require_once($plugindir."handlers/content_convert_class.php");
$ac = new content_convert;

$lan_file = $plugindir.'languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : $plugindir.'languages/English/lan_content.php');

if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'content'"))
{
	$row = $sql->db_Fetch();
	$content_version = $row['plugin_version'];
}

if($content_version < 1.21){

	$text = "";
	//upgrade to 1.1 (update content_parent string)
	$upgrade_1_1 = FALSE;
	$newcontent = $sql -> db_Count($plugintable, "(*)", "");
	if($newcontent > 0){
		
		if($thiscount = $sql -> db_Select("pcontent", "*", "ORDER BY content_id ", "mode=no_where" )){
			while($row = $sql -> db_Fetch()){
				if( strpos($row['content_parent'], ".") && substr($row['content_parent'],0,1) != "0"){
					//if item with old parent value exists, you need to upgrade to 1.1
					$upgrade_1_1 = TRUE;
				}
			}
		}
		if($upgrade_1_1 === TRUE){
			$text .= $ac -> upgrade_1_1();
		}
	}

	//upgrade to 1.2 (add score, meta, layout fields)
	$upgrade_1_2 = FALSE;
	$fields = mysql_list_fields($mySQLdefaultdb, MPREFIX."pcontent");
	$columns = mysql_num_fields($fields);
	for ($i = 0; $i < $columns; $i++)
	{
		if("content_score" == mysql_field_name($fields, $i))
		{
			$upgrade_1_2 = TRUE;
		}
	}
	if(!$upgrade_1_2){
		$text .= $ac -> upgrade_1_2();
	}


	//upgrade to 1.21 (update content_author fields)
	$upgrade_1_21 = $ac -> upgrade_1_21();
	if($upgrade_1_21){
		$text .= $upgrade_1_21;
	}

	if($upgrade_1_1 || $upgrade_1_2 || $upgrade_1_21){
		$caption = CONTENT_ADMIN_CONVERSION_LAN_63;
		$ns -> tablerender($caption, $text);
		set_content_version();
	}
}



//convert old content table
if($newcontent == 0){
		unset($text);

		//possible database values
		//content page:		$content_parent == "0" && $content_type == "1"
		//review category:	$content_parent == "0" && $content_type == "10"
		//article category:	$content_parent == "0" && $content_type == "6"
		//review:			$content_type == "3" || $content_type == "16"
		//article:			$content_type == "0" || $content_type == "15"

		// ##### STAGE 1 : ANALYSE OLD CONTENT --------------------------------------------------------
		$sql = new db;
		$totaloldcontentrows		= $sql -> db_Count("content");
		$totaloldrowscat_article	= $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '6'");
		$totaloldrowscat_review		= $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '10'");
		$totaloldrowsitem_content	= $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '1'");
		$totaloldrowsitem_review	= $sql -> db_Count("content", "(*)", "WHERE content_type = '3' || content_type = '16'");
		$totaloldrowsitem_article	= $sql -> db_Count("content", "(*)", "WHERE content_type = '0' || content_type = '15'");

		if($totaloldrowsitem_content == 0 && $totaloldrowsitem_article == 0 && $totaloldrowsitem_review == 0){
			$totaloldrowsitem_content	= "1";
			$totaloldrowsitem_article	= "1";
			$totaloldrowsitem_review	= "1";
			create_defaults();
		}else{

			//analyse unknown rows
			$unknown_array				= $ac -> analyse_unknown();

			//create mainparent
			$content_mainarray			= $ac -> create_mainparent("content", $totaloldrowsitem_content, "1");
			$article_mainarray			= $ac -> create_mainparent("article", $totaloldrowsitem_article, "2");
			$review_mainarray			= $ac -> create_mainparent("review", $totaloldrowsitem_review, "3");

			//convert categories
			$article_cat_array			= $ac -> convert_category("article", "content_parent = '0' AND content_type = '6'", "2");
			$review_cat_array			= $ac -> convert_category("review", "content_parent = '0' AND content_type = '10'", "3");

			//convert rows
			$content_array				= $ac -> convert_row("content", "content_parent = '0' AND content_type = '1'", "1");
			$article_array				= $ac -> convert_row("article", "content_type = '0' || content_type = '15'", "2");
			$review_array				= $ac -> convert_row("review", "content_type = '3' || content_type = '16'", "3");

			//convert comments
			$ac -> convert_comments();
			
			//convert rating
			$ac -> convert_rating();

			$conversion_analyses_rows_total		= $totaloldcontentrows;
			
			$conversion_analyses_rows_converted	= (count($article_cat_array[1]) + count($review_cat_array[1]) + count($content_array[2]) + count($article_array[2]) + count($review_array[2]));
			
			$conversion_analyses_rows_warning	= (count($content_array[4]) + count($content_array[5]) + count($article_array[4]) + count($article_array[5]) + count($review_array[4]) + count($review_array[5]));
			
			$conversion_analyses_rows_failed	= (count($article_cat_array[2]) + count($review_cat_array[2]) + count($content_array[3]) + count($article_array[3]) + count($review_array[3]) + count($unknown_array[1]));

			$SPACER = "<tr><td $stylespacer colspan='2'>&nbsp;</td></tr>";
			
			showlink();
			
			$text = "
			<table class='fborder' style='width:95%; padding:0px;'>";

			//conversion analysis
			$text .= "
			<tr>
				<td class='forumheader' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_11."</td>
				<td class='forumheader3'>
					<a style='cursor: pointer; cursor: hand' onclick=\"expandit('analysisconvert');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
					<div id='analysisconvert' style='display: none;'>
						".CONTENT_ADMIN_CONVERSION_LAN_12.": ".$conversion_analyses_rows_total."<br />
						".CONTENT_ADMIN_CONVERSION_LAN_13.": ".$conversion_analyses_rows_converted."<br />
						".CONTENT_ADMIN_CONVERSION_LAN_14.": ".$conversion_analyses_rows_warning."<br />
						".CONTENT_ADMIN_CONVERSION_LAN_15.": ".$conversion_analyses_rows_failed."<br />
					</div>
				</td>
			</tr>";

			$text .= $SPACER;

			//old content table : analysis
			$text .= "
			<tr>
				<td class='forumheader' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ADMIN_CONVERSION_LAN_16."</td>
				<td class='forumheader3'>
					<a style='cursor: pointer; cursor: hand' onclick=\"expandit('analysisold');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
					<div id='analysisold' style='display: none;'>
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
					</div>
				</td>
			</tr>";		

			$text .= $SPACER;

			//unknown rows
			$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_51."</td></tr>";
			$text .= "
			<tr>
				<td class='forumheader3' style='width:5%; white-space:nowrap; vertical-align:top;'>".CONTENT_ICON_ERROR." ".count($unknown_array[0])." ".CONTENT_ADMIN_CONVERSION_LAN_51."</td>
				<td class='forumheader3'>
					<a style='cursor: pointer; cursor: hand' onclick=\"expandit('unknownrows');\">".CONTENT_ADMIN_CONVERSION_LAN_48."</a>
					<div id='unknownrows' style='display: none;'>
						<table style='width:100%; border:0;'>";
						for($i=0;$i<count($unknown_array[0]);$i++){
							$text .= "<tr><td style='width:25%; white-space:nowrap;'>".CONTENT_ICON_ERROR." ".$unknown_array[0][$i]."</td><td>".$unknown_array[2][$i]." ".$rs -> form_hidden("unknownrows[]", $unknown_array[1][$i])."</td></tr>";
						}
						$text .= "
						</table>
					</div>
				</td>
			</tr>";	

			$text .= "

			".$SPACER."
			
			".$ac -> results_conversion_mainparent($content_mainarray, $review_mainarray, $article_mainarray)."
			
			".$SPACER."			
			
			<tr><td class='fcaption' colspan='2'>content : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
			".$ac -> results_conversion_row("content", $content_array, $totaloldrowsitem_content)."
			
			".$SPACER."			
			
			<tr><td class='fcaption' colspan='2'>review : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
			".$ac -> results_conversion_category("review", $review_cat_array, $totaloldrowscat_review)."
			".$ac -> results_conversion_row("review", $review_array, $totaloldrowsitem_review)."
			
			".$SPACER."			
			
			<tr><td class='fcaption' colspan='2'>article : ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
			".$ac -> results_conversion_category("article", $article_cat_array, $totaloldrowscat_article)."
			".$ac -> results_conversion_row("article", $article_array, $totaloldrowsitem_article)."
			
			".$SPACER."
			
			</table>";

			$caption = CONTENT_ADMIN_CONVERSION_LAN_42;
			$ns -> tablerender($caption, $text);
			
			set_content_version();
		}
}



//create default mainparent category for content, review and article
function create_defaults()
{
	global $ns, $ac, $plugindir;

	$plugindir		= e_PLUGIN."content/";
	
	if(!is_object($ac)){
		require_once($plugindir."handlers/content_convert_class.php");
		$ac = new content_convert;
	}

	$content_mainarray			= $ac -> create_mainparent("content", "1", "1");
	$article_mainarray			= $ac -> create_mainparent("article", "1", "2");
	$review_mainarray			= $ac -> create_mainparent("review", "1", "3");
	
	showlink();
	
	$text = "<table class='fborder' style='width:95%; padding:0px;'>";
	$text .= $ac -> results_conversion_mainparent($content_mainarray, $review_mainarray, $article_mainarray);
	$text .= "</table>";

	$caption = CONTENT_ADMIN_CONVERSION_LAN_52;
	$ns -> tablerender($caption, $text);
	
	set_content_version();
}



//show link to start managing the content management plugin
function showlink()
{
	global $ns;
	$text = "<div style='text-align:center'>".CONTENT_ADMIN_CONVERSION_LAN_46."</div>";
	$caption = CONTENT_ADMIN_CONVERSION_LAN_47;
	$ns -> tablerender($caption, $text);
}



//update content plugin version number
function set_content_version()
{
	global $sql;
	$new_version = "1.21";
	$sql->db_Update('plugin',"plugin_version = '{$new_version}' WHERE plugin_path='content'");
	return CONTENT_ADMIN_CONVERSION_LAN_62." $new_version <br />";
}	

?>