<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     Copyright (C) 2001-2002 Steve Dunstan (jalist@e107.org)
|     Copyright (C) 2008-2010 e107 Inc (e107.org)
|
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $URL$
|     $Revision$
|     $Id$
|     $Author$
+----------------------------------------------------------------------------+
*/
if (!defined('e107_INIT')) { exit; }

require_once(e_HANDLER."form_handler.php");
$rs = new form;

global $ns, $sql, $pref;

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)

require_once($plugindir."handlers/content_defines.php");
require_once($plugindir."handlers/content_class.php");
$aa = new content;
require_once($plugindir."handlers/content_convert_class.php");
$ac = new content_convert;

include_lan($plugindir.'languages/'.e_LANGUAGE.'/lan_content_admin.php');

if($sql->db_Select("plugin", "plugin_version", "plugin_path = 'content'"))
{
	$row = $sql->db_Fetch();
	$content_version = $row['plugin_version'];
}

//create table if it doesn't exist
if(!$sql->db_Query("SHOW COLUMNS FROM ".MPREFIX."pcontent")) {
	$query = "CREATE TABLE ".MPREFIX."pcontent (
	content_id int(10) unsigned NOT NULL auto_increment,
	content_heading varchar(255) NOT NULL default '',
	content_subheading varchar(255) NOT NULL default '',
	content_summary text NOT NULL,
	content_text longtext NOT NULL,
	content_author varchar(255) NOT NULL default '',
	content_icon varchar(255) NOT NULL default '',
	content_file text NOT NULL,
	content_image text NOT NULL,
	content_parent varchar(50) NOT NULL default '',
	content_comment tinyint(1) unsigned NOT NULL default '0',
	content_rate tinyint(1) unsigned NOT NULL default '0',
	content_pe tinyint(1) unsigned NOT NULL default '0',
	content_refer text NOT NULL,
	content_datestamp int(10) unsigned NOT NULL default '0',
	content_enddate int(10) unsigned NOT NULL default '0',
	content_class varchar(255) NOT NULL default '',
	content_pref text NOT NULL, 
	content_order varchar(10) NOT NULL default '0',
	content_score tinyint(3) unsigned NOT NULL default '0',
	content_meta text NOT NULL,
	content_layout varchar(255) NOT NULL default '',
	PRIMARY KEY  (content_id)
	) ENGINE=MyISAM;";
	
	$sql->db_Select_gen($query);
}

$text = "";
$main_convert = "";
//main convert

$newcontent = $sql -> db_Count($plugintable, "(*)", "");
if($newcontent == 0){

	unset($text);

	//possible database values
	//content page:		$content_parent == "1" && $content_type == "1"	//added at 20051031
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
	$totaloldrowsitem_content	= $sql -> db_Count("content", "(*)", "WHERE (content_parent = '0' || content_parent = '1') AND content_type = '1'");
	$totaloldrowsitem_review	= $sql -> db_Count("content", "(*)", "WHERE content_type = '3' || content_type = '16'");
	$totaloldrowsitem_article	= $sql -> db_Count("content", "(*)", "WHERE content_type = '0' || content_type = '15'");

	if($totaloldrowsitem_content == 0 && $totaloldrowsitem_article == 0 && $totaloldrowsitem_review == 0){
		$totaloldrowsitem_content	= "1";
		$totaloldrowsitem_article	= "1";
		$totaloldrowsitem_review	= "1";
		
		//if no old records exist, create a few default categories
		$main_convert = create_defaults();
	}else{

		//analyse unknown rows
		$unknown_array				= $ac -> analyse_unknown();

		if($totaloldcontentrows == 0){
			$totaloldrowsitem_content	= "1";
			$totaloldrowsitem_article	= "1";
			$totaloldrowsitem_review	= "1";
		}

		//create mainparent
		$content_mainarray			= $ac -> create_mainparent("content", $totaloldrowsitem_content, "1");
		$article_mainarray			= $ac -> create_mainparent("article", $totaloldrowsitem_article, "2");
		$review_mainarray			= $ac -> create_mainparent("review", $totaloldrowsitem_review, "3");

		//convert categories
		$article_cat_array			= $ac -> convert_category("article", "content_parent = '0' AND content_type = '6'", "2");
		$review_cat_array			= $ac -> convert_category("review", "content_parent = '0' AND content_type = '10'", "3");

		//convert rows
		$content_array				= $ac -> convert_row("content", "(content_parent = '0' || content_parent = '1') AND content_type = '1'", "1");
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
		
		
		//only output detailed information if developer mode is set
		if ($pref['developer']) {
			showlink();
			$SPACER = "<tr><td $stylespacer colspan='2'>&nbsp;</td></tr>";
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
			if(count($unknown_array[0]) > 0){
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
			}

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

			$main_convert = array($caption, $text);
		}

	}
}

$text = "";
//update to 1.1 parent values to new style
$upgrade_1_1 = FALSE;
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

//upgrade to 1.2 table structure (add score, meta, layout fields)
$text .= $ac -> upgrade_1_2();

//upgrade to 1.21 (update content_author fields)
$text .= $ac -> upgrade_1_21();

//upgrade to 1.22 (update preference storage method)
$text .= $ac -> upgrade_1_22();

//upgrade to 1.23 (update preference storage method)
$text .= $ac -> upgrade_1_23();

//upgrade to 1.24 (update custom theme)
$text .= $ac -> upgrade_1_24();

//render message
if(isset($text)){
	//only output detailed information if developer mode is set
	if ($pref['developer']) {
		$caption = CONTENT_ADMIN_CONVERSION_LAN_63;
		$ns -> tablerender($caption, $text);
	}
}

//render primary conversion results
if(is_array($main_convert)){
	if(isset($main_convert[1])){
		//only output detailed information if developer mode is set
		if ($pref['developer']) {
			$ns -> tablerender($main_convert[0], $main_convert[1]);
		}
	}
}

//finally set the new content plugin version number
set_content_version();




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
	
	$main_convert = '';
	//only output detailed information if developer mode is set
	if ($pref['developer']) {
		showlink();
	
		$text = "<table class='fborder' style='width:95%; padding:0px;'>";
		$text .= $ac -> results_conversion_mainparent($content_mainarray, $review_mainarray, $article_mainarray);
		$text .= "</table>";

		$main_convert = array(CONTENT_ADMIN_CONVERSION_LAN_52, $text);
	}
	return $main_convert;
}



//show link to start managing the content management plugin
function showlink()
{
	global $ns, $pref;
	//only output detailed information if developer mode is set
	if ($pref['developer']) {
		$text = "<div style='text-align:center'>".CONTENT_ADMIN_CONVERSION_LAN_46."</div>";
		$caption = CONTENT_ADMIN_CONVERSION_LAN_47;
		$ns -> tablerender($caption, $text);
	}
}



//update content plugin version number
function set_content_version()
{
	global $sql, $pref;
	$new_version = "1.24";
	$sql->db_Update('plugin',"plugin_version = '{$new_version}' WHERE plugin_path='content'");
	$text = '';
	//only output detailed information if developer mode is set
	if ($pref['developer']) {
		$text = CONTENT_ADMIN_CONVERSION_LAN_62." $new_version <br />";
	}
	return $text;
}	

?>