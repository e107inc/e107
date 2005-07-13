<?php

require_once(e_PLUGIN.'links_page/link_class.php');
$lc = new linkclass();
require_once(e_HANDLER."form_handler.php");
$rs = new form;
global $tp;

$lan_file = e_PLUGIN."links_page/languages/".e_LANGUAGE.".php";
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN."links_page/languages/English.php");

$bullet = "<img src='".THEME."images/bullet2.gif' alt='' style='border:0;' />";

$linkspage_pref = $lc -> getLinksPagePref();

//navigator -------------------------
$mains = "";
$text = "";
$baseurl = e_PLUGIN."links_page/links.php";
if(isset($linkspage_pref['link_menu_navigator_frontpage']) && $linkspage_pref['link_menu_navigator_frontpage']){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LAN_LINKS_14, "0", $baseurl, "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."'>".LAN_LINKS_14."</a><br />";
	}
}
if(isset($linkspage_pref['link_menu_navigator_refer']) && $linkspage_pref['link_menu_navigator_refer']){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LAN_LINKS_12, "0", $baseurl."?top", "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."?top'>".LAN_LINKS_12."</a><br />";
	}
}
if(isset($linkspage_pref['link_menu_navigator_rated']) && $linkspage_pref['link_menu_navigator_rated']){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LAN_LINKS_13, "0", $baseurl."?rated", "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."?rated'>".LAN_LINKS_13."</a><br />";
	}
}
if(isset($linkspage_pref['link_menu_navigator_category']) && $linkspage_pref['link_menu_navigator_category']){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LAN_LINKS_43, "0", $baseurl."?cat", "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."?cat'>".LAN_LINKS_43."</a><br />";
	}
}
if(isset($linkspage_pref['link_menu_navigator_links']) && $linkspage_pref['link_menu_navigator_links']){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LCLAN_OPT_68, "0", $baseurl."?all", "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."?all'>".LCLAN_OPT_68."</a><br />";
	}
}
if(isset($linkspage_pref['link_menu_navigator_submit']) && $linkspage_pref['link_menu_navigator_submit'] && isset($linkspage_pref['link_submit']) && $linkspage_pref['link_submit'] && check_class($linkspage_pref['link_submit_class'])){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LAN_LINKS_27, "0", $baseurl."?submit", "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."?submit'>".LAN_LINKS_27."</a><br />";
	}
}
if(isset($linkspage_pref['link_menu_navigator_manager']) && $linkspage_pref['link_menu_navigator_manager'] && isset($linkspage_pref['link_manager']) && $linkspage_pref['link_manager'] && check_class($linkspage_pref['link_manager_class'])){
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$mains .= $rs -> form_option(LCLAN_ITEM_35, "0", $baseurl."?manage", "");
	}else{
		$mains .= $bullet." <a href='".$baseurl."?manage'>".LCLAN_ITEM_35."</a><br />";
	}
}

if($mains){
	$cap = (isset($linkspage_pref['link_menu_navigator_caption']) && $linkspage_pref['link_menu_navigator_caption'] ? $linkspage_pref['link_menu_navigator_caption'] : LCLAN_OPT_82);
	if(isset($linkspage_pref['link_menu_navigator_rendertype']) && $linkspage_pref['link_menu_navigator_rendertype'] == "1"){
		$selectjs = "style='width:100%;' onchange=\"if(this.options[this.selectedIndex].value != ''){ return document.location=this.options[this.selectedIndex].value; }\" ";
		$text .= $rs -> form_select_open("navigator", $selectjs);
		$text .= $rs -> form_option($cap, "0", "", "");
		$text .= $mains;
		$text .= $rs -> form_select_close();
		$text .= "<br />";
	}else{
		$text .= $cap."<br />";
		$text .= $mains;
	}
	
}

//categories ------------------------
if(isset($linkspage_pref['link_menu_category']) && $linkspage_pref['link_menu_category']){
	$mains = "";
	$cap = (isset($linkspage_pref['link_menu_category_caption']) && $linkspage_pref['link_menu_category_caption'] ? $linkspage_pref['link_menu_category_caption'] : LCLAN_OPT_83);
	$sqlc = new db; $sql2 = new db;
	if ($sqlc->db_Select("links_page_cat", "link_category_id, link_category_name", "link_category_class REGEXP '".e_CLASS_REGEXP."' ORDER BY link_category_name")){
		while ($rowc = $sqlc->db_Fetch()){
			if(isset($linkspage_pref['link_menu_category_amount']) && $linkspage_pref['link_menu_category_amount']){
				$amount = $sql2 -> db_Count("links_page", "(*)", "WHERE link_category = '".$rowc['link_category_id']."' AND link_class REGEXP '".e_CLASS_REGEXP."' ");
				$amount = "(".$amount.")";
			}else{
				$amount = "";
			}
			if(isset($linkspage_pref['link_menu_category_rendertype']) && $linkspage_pref['link_menu_category_rendertype'] == "1"){
				$mains .= $rs -> form_option($rowc['link_category_name']." ".$amount, "0", $baseurl."?cat.".$rowc['link_category_id'], "");
			}else{
				$mains .= $bullet." <a href='".$baseurl."?cat.".$rowc['link_category_id']."'>".$rowc['link_category_name']."</a> ".$amount."<br />";
			}
		}
		if(isset($linkspage_pref['link_menu_category_rendertype']) && $linkspage_pref['link_menu_category_rendertype'] == "1"){
			$selectjs = "style='width:100%;' onchange=\"if(this.options[this.selectedIndex].value != ''){ return document.location=this.options[this.selectedIndex].value; }\" ";
			$text .= "<br />";
			$text .= $rs -> form_select_open("category", $selectjs);
			$text .= $rs -> form_option($cap, "0", "", "");
			$text .= $mains;
			$text .= $rs -> form_select_close();
			$text .= "<br />";
		}else{
			$text .= "<br />".$cap."<br />";
			$text .= $mains;
		}
	}
}


//recent ----------------------------
$num = (isset($linkspage_pref["link_menu_recent_number"]) && $linkspage_pref["link_menu_recent_number"] ? $linkspage_pref["link_menu_recent_number"] : "5");
$qry = "
SELECT l.*, c.link_category_id, c.link_category_name
FROM #links_page AS l
LEFT JOIN #links_page_cat AS c ON c.link_category_id = l.link_category
WHERE l.link_class REGEXP '".e_CLASS_REGEXP."' AND c.link_category_class REGEXP '".e_CLASS_REGEXP."'
ORDER BY l.link_datestamp DESC LIMIT 0,".$num." 
";

$cap = (isset($linkspage_pref['link_menu_recent_caption']) && $linkspage_pref['link_menu_recent_caption'] ? $linkspage_pref['link_menu_recent_caption'] : LCLAN_OPT_84);
if($sql -> db_Select_gen($qry)){
	$text .= "<br />".$cap."<br />";
	while($row = $sql -> db_Fetch()){

		$append = $lc -> parse_link_append($row['link_open'], $row['link_id']);

		$heading = $append.$tp->toHTML($row['link_name'],TRUE,"")."</a>";

		$cat = (isset($linkspage_pref['link_menu_recent_category']) && $linkspage_pref['link_menu_recent_category'] ? "<br /><a href='".e_PLUGIN."links_page/links.php?cat.".$row['link_category_id']."'>".$row['link_category_name']."</a>" : "");

		$desc = (isset($linkspage_pref['link_menu_recent_description']) && $linkspage_pref['link_menu_recent_description'] && $row['link_description'] ? "<br />".$tp->toHTML($row['link_description'],TRUE,"") : "");

		$text .= "
		<table style='width:100%; text-align:left; border:0;' cellpadding='0' cellspacing='0'>
			<tr>
				<td style='width:1%; white-space:nowrap; vertical-align:top; padding-right:5px;'>".$bullet."</td>
				<td>
					".$heading."
					".$cat."
					".$desc."
				</td>
			</tr>
		</table>";
	}
}

$caption = (isset($linkspage_pref['link_menu_caption']) && $linkspage_pref['link_menu_caption'] ? $linkspage_pref['link_menu_caption'] : LCLAN_OPT_86);
$ns -> tablerender($caption, $text);

?>