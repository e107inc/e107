<?php
if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$content_shortcodes = $tp -> e_sc -> parse_scbatch(__FILE__);
/*

SC_BEGIN CONTENTFORM_HOOK
global $CONTENTFORM_HOOK;
return $CONTENTFORM_HOOK;
SC_END

SC_BEGIN CM_AMOUNT
global $row, $tp, $content_pref;
if($sc_mode){

	if($sc_mode=='author'){
			global $totalcontent;
			if(varsettrue($content_pref["content_author_amount"])){
				return $totalcontent." ".($totalcontent==1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
			}
	}elseif($sc_mode=='cat'){
			global $totalitems;
			if(varsettrue($content_pref["content_catall_amount"])){
				return $totalitems." ".($totalitems == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
			}
	}elseif($sc_mode=='catlist'){
			global $totalparent;
			if(varsettrue($content_pref["content_cat_amount"])){
				return $totalparent." ".($totalparent == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
			}
	}elseif($sc_mode=='catlistsub'){
			global $totalsubcat;
			if(varsettrue($content_pref["content_catsub_amount"])){
				return $totalsubcat." ".($totalsubcat == "1" ? CONTENT_LAN_53 : CONTENT_LAN_54);
			}
	}elseif($sc_mode=='type'){
			global $contenttotal;
			return $contenttotal." ".($contenttotal == 1 ? CONTENT_LAN_53 : CONTENT_LAN_54);
	}
}
SC_END

SC_BEGIN CM_AUTHOR
global $row, $aa, $tp;
if($sc_mode){
	if($sc_mode=='top'){
			global $CM_AUTHOR;
			return $CM_AUTHOR;
	}elseif($sc_mode=='score'){
			global $CM_AUTHOR;
			return $CM_AUTHOR;
	}elseif($sc_mode=='archive'){
			global $CM_AUTHOR;
			return $aa -> prepareAuthor("archive", $row['content_author'], $row['content_id']);
	}elseif($sc_mode=='recent'){
			global $CM_AUTHOR;
			return $CM_AUTHOR;
	}elseif($sc_mode=='author'){
			global $authordetails, $i, $row;
			$name = ($authordetails[$i][1] == "" ? "... ".CONTENT_LAN_29." ..." : $authordetails[$i][1]);
			return "<a href='".e_SELF."?author.".$row['content_id']."'>".$name."</a>";
	}elseif($sc_mode=='content'){
			global $CM_AUTHOR;
			return $CM_AUTHOR;
	}elseif($sc_mode=='cat'){
			global $CM_AUTHOR;
			return $CM_AUTHOR;
	}elseif($sc_mode=='catlist'){
			global $CM_AUTHOR;
			return $CM_AUTHOR;
	}elseif($sc_mode=='searchresult'){
			$authordetails = $aa -> getAuthor($row['content_author']);
			$ret = $authordetails[1];
			if(USER){
				if(is_numeric($authordetails[3])){
					$ret .= " <a href='".e_BASE."user.php?id.".$authordetails[0]."' title='".CONTENT_LAN_40."'>".CONTENT_ICON_USER."</a>";
				}else{
					$ret .= " ".CONTENT_ICON_USER;
				}
			}else{
				$ret .= " ".CONTENT_ICON_USER;
			}
			$ret .= " <a href='".e_SELF."?author.".$row['content_id']."' title='".CONTENT_LAN_39."'>".CONTENT_ICON_AUTHORLIST."</a>";
			return $ret;
	}
}
SC_END

SC_BEGIN CM_COMMENT
global $row, $tp, $content_pref;
if($sc_mode){
	if($sc_mode=='cat'){
			global $comment_total, $plugintable;
			if(varsettrue($row['content_comment']) && varsettrue($content_pref["content_catall_comment"])){
				$sqlc = new db;
				$comment_total = $sqlc -> db_Select("comments", "*",  "comment_item_id='".$row['content_id']."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
				return "<a href='".e_SELF."?cat.".$row['content_id'].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
			}
	}elseif($sc_mode=='catlist'){
			global $qs, $comment_total, $sql, $plugintable;
			if(varsettrue($row['content_comment']) && varsettrue($content_pref["content_cat_comment"])){
				$comment_total = $sql -> db_Count("comments", "(*)",  "WHERE comment_item_id='".intval($qs[1])."' AND comment_type='".$plugintable."' AND comment_pid='0' ");
				return "<a href='".e_SELF."?cat.".$qs[1].".comment'>".CONTENT_LAN_57." ".$comment_total."</a>";
			}
	}elseif($sc_mode=='content'){
			global $cobj, $qs, $plugintable;
			if((varsettrue($content_pref["content_content_comment"]) && varsettrue($row['content_comment'])) || varsettrue($content_pref["content_content_comment_all"]) ){
				return $cobj -> count_comments($plugintable, $qs[1]);
			}
	}
}
SC_END

SC_BEGIN CM_DATE
global $row, $tp, $content_pref, $gen;
if($sc_mode){
	if($sc_mode=='archive'){
			if(varsettrue($content_pref["content_archive_date"])){
				$datestyle = varset($content_pref["content_archive_datestyle"],"%d %b %Y");
				return strftime($datestyle, $row['content_datestamp']);
			}
	}elseif($sc_mode=='recent'){
			if(varsettrue($content_pref["content_list_date"])){
				$datestyle = varset($content_pref["content_list_datestyle"],"%d %b %Y");
				return strftime($datestyle, $row['content_datestamp']);
			}
	}elseif($sc_mode=='content'){
			if(varsettrue($content_pref["content_content_date"])){
				if(!is_object($gen)){ $gen = new convert; }
				$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
				return ($datestamp != "" ? $datestamp : "");
			}
	}elseif($sc_mode=='cat'){
			if(varsettrue($content_pref["content_catall_date"])){
				if(!is_object($gen)){ $gen = new convert; }
				$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
				return ($datestamp != "" ? $datestamp : "");
			}
	}elseif($sc_mode=='catlist'){
			if(varsettrue($content_pref["content_cat_date"])){
				if(!is_object($gen)){ $gen = new convert; }
				$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
				return ($datestamp != "" ? $datestamp : "");
			}
	}elseif($sc_mode=='searchresult'){
			return preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "short"));

	}
}
SC_END

SC_BEGIN CM_EDITICON
global $content_pref, $row, $plugindir, $tp;
if($sc_mode){
	if($sc_mode=='content'){
			if(ADMIN && getperms("P") && varsettrue($content_pref["content_content_editicon"])){
				return "<a href='".$plugindir."admin_content_config.php?content.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
			}
	}elseif($sc_mode=='recent'){
			if(ADMIN && getperms("P") && varsettrue($content_pref["content_list_editicon"])){
				return "<a href='".$plugindir."admin_content_config.php?content.edit.".$row['content_id']."'>".CONTENT_ICON_EDIT."</a>";
			}
	}
}
SC_END

SC_BEGIN CM_EPICONS
global $content_pref, $row, $tp;
if($sc_mode){
	$epicons = "";
	if($sc_mode=='content'){
			if((varsettrue($content_pref["content_list_peicon"]) && varsettrue($row['content_pe'])) || varsettrue($content_pref["content_list_peicon_all"]) ){
				$epicons = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
				$epicons .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
				$epicons .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
				return $epicons;
			}
	}elseif($sc_mode=='recent'){
			if(varsettrue($content_pref["content_list_peicon"])){
				if( varsettrue($row['content_pe']) || varsettrue($content_pref["content_list_peicon_all"]) ){
					$epicons = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
					$epicons .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
					$epicons .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
					return $epicons;
				}
			}
	}elseif($sc_mode=='cat'){
			if( varsettrue($row['content_pe']) && varsettrue($content_pref["content_catall_peicon"])){
				$epicons = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
				$epicons .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.".$row['content_id']."}");
				$epicons .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.".$row['content_id']."}");
				return $epicons;
			}
	}elseif($sc_mode=='catlist'){
			if( (varsettrue($content_pref["content_cat_peicon"]) && varsettrue($row['content_pe'])) || varsettrue($content_pref["content_cat_peicon_all"]) ){
				$epicons = $tp -> parseTemplate("{EMAIL_ITEM=".CONTENT_LAN_69." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
				$epicons .= " ".$tp -> parseTemplate("{PRINT_ITEM=".CONTENT_LAN_70." ".CONTENT_LAN_72."^plugin:content.$qs[1]}");
				$epicons .= " ".$tp -> parseTemplate("{PDF=".CONTENT_LAN_76." ".CONTENT_LAN_71."^plugin:content.$qs[1]}");
				return $epicons;
			}
	}
}
SC_END

SC_BEGIN CM_HEADING
global $row, $tp;
$row['content_heading'] = $tp -> toHTML($row['content_heading'], TRUE, "");
if($sc_mode){
	if($sc_mode=='type'){
		$row['content_heading'] = $tp -> toHTML($row['content_heading'], TRUE, "emotes_off, no_make_clickable");
		return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='top'){
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='score'){
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='archive'){
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='cat'){
		return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='catlist'){
		return "<a href='".e_SELF."?cat.".$row['content_id'].".view'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='catlistsub'){
		return "<a href='".e_SELF."?cat.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='recent'){
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='content'){
		return $row['content_heading'];
	}elseif($sc_mode=='searchresult'){
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='manager'){
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}elseif($sc_mode=='manager_link'){
		return "<a href='".e_PLUGIN."content/content_manager.php'>".CONTENT_LAN_67."</a>";
	}else{
		return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
	}
}else{
	return "<a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
}
SC_END

SC_BEGIN CM_ICON
global $aa, $row, $content_pref;
if($sc_mode){
	if($sc_mode=='top'){
			if(varsettrue($content_pref["content_top_icon"])){
				$width = varsettrue($content_pref["content_upload_icon_size"], '100');
				$width = varsettrue($content_pref["content_top_icon_width"], $width);
				return $aa -> getIcon("item", $row['content_icon'], $content_pref['content_icon_path'], "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
			}
	}elseif($sc_mode=='score'){
			if(varsettrue($content_pref["content_score_icon"])){
				$width = varsettrue($content_pref["content_upload_icon_size"], '100');
				$width = varsettrue($content_pref["content_score_icon_width"], $width);
				return $aa -> getIcon("item", $row['content_icon'], $content_pref['content_icon_path'], "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
			}
	}elseif($sc_mode=='cat'){
			if(varsettrue($content_pref["content_catall_icon"])){
				$qry = "cat.".$row['content_id'];
				return $aa -> getIcon("catlarge", $row['content_icon'], $content_pref['content_cat_icon_path_large'], $qry, "", $content_pref["content_blank_caticon"]);
			}
	}elseif($sc_mode=='catlist'){
			if(varsettrue($content_pref["content_cat_icon"])){
				return $aa -> getIcon("catlarge", $row['content_icon'], $content_pref['content_cat_icon_path_large'], "", "", $content_pref["content_blank_caticon"]);
			}
	}elseif($sc_mode=='catlistsub'){
			if(varsettrue($content_pref["content_catsub_icon"])){
				return $aa -> getIcon("catsmall", $row['content_icon'], $content_pref['content_cat_icon_path_small'], "cat.".$row['content_id'], "", $content_pref["content_blank_caticon"]);
			}
	}elseif($sc_mode=='recent'){
			if(varsettrue($content_pref["content_list_icon"])){
				$width = varsettrue($content_pref["content_upload_icon_size"], '100');
				return $aa -> getIcon("item", $row['content_icon'], $content_pref['content_icon_path'], "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
			}
	}elseif($sc_mode=='author'){
			return "<a href='".e_SELF."?author.".$row['content_id']."'>".CONTENT_ICON_AUTHORLIST."</a>";
	}elseif($sc_mode=='content'){
			if(varsettrue($content_pref["content_content_icon"])){
				$width = varsettrue($content_pref["content_upload_icon_size"], '100');
				return $aa -> getIcon("item", $row['content_icon'], $content_pref['content_icon_path'], "", $width, $content_pref["content_blank_icon"]);
			}
	}elseif($sc_mode=='type'){
			$qry = "cat.".$row['content_id'];
			return $aa -> getIcon("catlarge", $row['content_icon'], $content_pref['content_cat_icon_path_large'], $qry, "", $content_pref["content_blank_caticon"]);
	}elseif($sc_mode=='searchresult'){
			$width = varsettrue($content_pref["content_upload_icon_size"], '100');
			return $aa -> getIcon("item", $row['content_icon'], $content_pref['content_icon_path'], "content.".$row['content_id'], $width, $content_pref["content_blank_icon"]);
	}elseif($sc_mode=='manager_link'){
			return "<a href='".e_PLUGIN."content/content_manager.php'>".CONTENT_ICON_CONTENTMANAGER."</a>";
	}elseif($sc_mode=='manager_new'){
			if( (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) || (isset($content_pref["content_manager_submit"]) && check_class($content_pref["content_manager_submit"])) ){

				if( (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) ){
					return "<a href='".e_SELF."?content.create.".$row['content_id']."'>".CONTENT_MANAGER_LAN_1."</a>";
				}elseif( isset($content_pref["content_manager_submit"]) && check_class($content_pref["content_manager_submit"]) ){
					return "<a href='".e_SELF."?content.submit.".$row['content_id']."'>".CONTENT_MANAGER_LAN_4."</a>";
				}
			}
	}elseif($sc_mode=='manager_edit'){
			if( (isset($content_pref["content_manager_personal"]) && check_class($content_pref["content_manager_personal"])) || (isset($content_pref["content_manager_category"]) && check_class($content_pref["content_manager_category"])) ){
				return "<a href='".e_SELF."?content.".$row['content_id']."'>".CONTENT_MANAGER_LAN_2."</a>";
			}
	}elseif($sc_mode=='manager_submit'){
			global $plugintable;
			if(isset($content_pref["content_manager_approve"]) && check_class($content_pref["content_manager_approve"])){
				if(!is_object($sqls)){ $sqls = new db; }
				$num = $sqls -> db_Count($plugintable, "(*)", "WHERE content_refer = 'sa' AND content_parent='".intval($row['content_id'])."' ");
				return "<a href='".e_SELF."?content.approve.".$row['content_id']."'>".CONTENT_MANAGER_LAN_3." (".$num.")</a>";
			}	
	}
}
SC_END

SC_BEGIN CM_PARENT
global $aa, $array, $row, $content_pref, $tp;
if($sc_mode){
	if($sc_mode=='content'){
			if(varsettrue($content_pref["content_content_parent"])){
				return $aa -> getCrumbItem($row['content_parent'], $array);
			}
	}elseif($sc_mode=='recent'){
			if(varsettrue($content_pref["content_list_parent"])){
				return $aa -> getCrumbItem($row['content_parent'], $array);
			}
	}
}
SC_END

SC_BEGIN CM_RATING
global $row, $tp, $rater, $content_pref, $plugintable;
if($sc_mode){
	if($sc_mode=='content'){
			if(($content_pref["content_content_rating"] && $row['content_rate']) || $content_pref["content_content_rating_all"] ){
				return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
			}
	}elseif($sc_mode=='recent'){
			if($content_pref["content_list_rating"]){
				if($content_pref["content_list_rating_all"] || $row['content_rate']){
					return $rater->composerating($plugintable, $row['content_id'], $enter=FALSE, $userid=FALSE);
				}
			}
	}elseif($sc_mode=='top'){
			$row['rate_avg'] = round($row['rate_avg'], 1);
			$row['rate_avg'] = (strlen($row['rate_avg'])>1 ? $row['rate_avg'] : $row['rate_avg'].".0");
			$tmp = explode(".", $row['rate_avg']);
			$rating = "";
			$rating .= $row['rate_avg']." ";
			for($c=1; $c<=$tmp[0]; $c++){
				$rating .= "<img src='".e_IMAGE."rate/box.png' alt='' style='border:0; height:8px; vertical-align:middle' />";
			}
			if($tmp[0] < 10){
				for($c=9; $c>=$tmp[0]; $c--){
					$rating .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='border:0; height:8px; vertical-align:middle' />";
				}
			}
			$rating .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='border:0; height:8px; vertical-align:middle' />";
			return $rating;

	}elseif($sc_mode=='cat'){
			if($row['content_rate'] && varsettrue($content_pref["content_catall_rating"])){
				return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
			}
	}elseif($sc_mode=='catlist'){
			if( varsettrue($content_pref["content_cat_rating_all"]) || (varsettrue($content_pref["content_cat_rating"]) && $row['content_rate'])){
				return $rater->composerating($plugintable, $row['content_id'], $enter=TRUE, $userid=FALSE);
			}
	}
}
SC_END

SC_BEGIN CM_REFER
global $sql, $row, $tp, $qs, $content_pref, $plugintable;
if($sc_mode){
	if($sc_mode=='content'){
			if(varsettrue($content_pref["content_content_refer"])){
				$sql -> db_Select($plugintable, "content_refer", "content_id='".intval($qs[1])."' ");
				list($content_refer) = $sql -> db_Fetch();
				$refercounttmp = explode("^", $content_refer);
				return ($refercounttmp[0] ? $refercounttmp[0] : "");
			}
	}elseif($sc_mode=='recent'){
			if($content_pref["content_log"] && $content_pref["content_list_refer"]){
				$refercounttmp = explode("^", $row['content_refer']);
				$refer = ($refercounttmp[0] ? $refercounttmp[0] : "0");
				if($refer > 0){
					return $refer;
				}
			}
	}
}
SC_END

SC_BEGIN CM_SCORE
global $row, $tp;
if($sc_mode){
	if($sc_mode=='content'){
			$score = $row['content_score'];
			if($score>0){
				$height = "height:8px;";
				$img = "";
				$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
				$img .= "<img src='".e_PLUGIN."content/images/score.png' alt='' style='$height width:".$score."px; border:0;' />";
				$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
				if($score < 100){
					$empty = 100-$score;
					$img .= "<img src='".e_PLUGIN."content/images/score_empty.png' alt='' style='$height width:".$empty."px; border:0;' />";
				}
				$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
				return $img." ".$score;
			}
	}elseif($sc_mode=='score'){
			$score = $row['content_score'];
			$height = "height:8px;";
			$img = "";
			$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
			$img .= "<img src='".e_PLUGIN."content/images/score.png' alt='' style='$height width:".$score."px; border:0;' />";
			$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
			if($score < 100){
				$empty = 100-$score;
				$img .= "<img src='".e_PLUGIN."content/images/score_empty.png' alt='' style='$height width:".$empty."px; border:0;' />";
			}
			$img .= "<img src='".e_PLUGIN."content/images/score_end.png' alt='' style='$height width:1px; border:0;' />";
			return $score."/100 ".$img;
	}
}
SC_END

SC_BEGIN CM_SUBHEADING
global $tp, $content_pref, $qs, $row;
if($sc_mode){
	if($sc_mode=='content'){
			return ($content_pref["content_content_subheading"] && $row['content_subheading'] ? $tp -> toHTML($row['content_subheading'], TRUE, "") : "");
	}elseif($sc_mode=='recent'){
			if (varsettrue($content_pref["content_list_subheading"]) && $row['content_subheading'] && $content_pref["content_list_subheading_char"] && $content_pref["content_list_subheading_char"] != "" && $content_pref["content_list_subheading_char"] != "0"){
				if(strlen($row['content_subheading']) > $content_pref["content_list_subheading_char"]) {
					$row['content_subheading'] = substr($row['content_subheading'], 0, $content_pref["content_list_subheading_char"]).$content_pref["content_list_subheading_post"];
				}
				$ret = ($row['content_subheading'] != "" && $row['content_subheading'] != " " ? $row['content_subheading'] : "");
			}else{
				$ret = ($row['content_subheading'] ? $row['content_subheading'] : "");
			}
			return $tp->toHTML($ret, TRUE, "");
	}elseif($sc_mode=='type'){
			return $tp -> toHTML($row['content_subheading'], TRUE, "emotes_off, no_make_clickable");
	}elseif($sc_mode=='cat'){
			if(varsettrue($content_pref["content_catall_subheading"])){
				return $tp -> toHTML($row['content_subheading'], TRUE, "");
			}
	}elseif($sc_mode=='catlist'){
			if(varsettrue($content_pref["content_cat_subheading"])){
				return $tp -> toHTML($row['content_subheading'], TRUE, "");
			}
	}elseif($sc_mode=='catlistsub'){
			if(varsettrue($content_pref["content_catsub_subheading"])){
				return $tp -> toHTML($row['content_subheading'], TRUE, "");
			}
	}elseif($sc_mode=='searchresult'){
			return $tp -> toHTML($row['content_subheading'], TRUE, "");
	}elseif($sc_mode=='manager'){
			return $tp->toHTML($row['content_subheading'], TRUE);
	}
}
SC_END

SC_BEGIN CM_SUMMARY
global $content_pref, $tp, $row, $CONTENT_CONTENT_TABLE_SUMMARY;
if($sc_mode){
	if($sc_mode=='content'){
			return $CONTENT_CONTENT_TABLE_SUMMARY;
	}elseif($sc_mode=='recent'){
			if (varsettrue($content_pref["content_list_summary"])){
				if($row['content_summary'] && $content_pref["content_list_summary_char"] && $content_pref["content_list_summary_char"] != "" && $content_pref["content_list_summary_char"] != "0"){
					if(strlen($row['content_summary']) > $content_pref["content_list_summary_char"]) {
						$row['content_summary'] = substr($row['content_summary'], 0, $content_pref["content_list_summary_char"]).$content_pref["content_list_summary_post"];
					}
					$ret = ($row['content_summary'] != "" && $row['content_summary'] != " " ? $row['content_summary'] : "");
				}else{
					$ret = ($row['content_summary'] ? $row['content_summary'] : "");
				}
				return $tp->toHTML($ret, TRUE, "");
			}
	}elseif($sc_mode=='catlist'){
			return ($row['content_summary'] ? $tp -> toHTML($row['content_summary'], TRUE, "") : "");
	}
}
SC_END

SC_BEGIN CM_TEXT
global $content_pref, $row, $tp, $CONTENT_CONTENT_TABLE_TEXT;
if($sc_mode){
	if($sc_mode=='content'){
			return $CONTENT_CONTENT_TABLE_TEXT;
	}elseif($sc_mode=='recent'){
			if(varsettrue($content_pref["content_list_text"]) && $content_pref["content_list_text_char"] > 0){
				$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
				$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
				$rowtext = strip_tags($rowtext);
				$words = explode(" ", $rowtext);
				$ret = implode(" ", array_slice($words, 0, $content_pref["content_list_text_char"]));
				if($ret){
					if($content_pref["content_list_text_link"]){
						$ret .= " <a href='".e_SELF."?content.".$row['content_id']."'>".$content_pref["content_list_text_post"]."</a>";
					}else{
						$ret .= " ".$content_pref["content_list_text_post"];
					}
				}
				return $ret;
			}
	}elseif($sc_mode=='cat'){
			if($row['content_text'] && varsettrue($content_pref["content_catall_text"]) && ($content_pref["content_catall_text_char"] > 0 || $content_pref["content_catall_text_char"] == 'all')){
				if($content_pref["content_catall_text_char"] == 'all'){
					$ret = $row['content_text'];
				}else{
					$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
					$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak");
					$rowtext = strip_tags($rowtext);
					$words = explode(" ", $rowtext);
					$ret = implode(" ", array_slice($words, 0, $content_pref["content_catall_text_char"]));
					if($content_pref["content_catall_text_link"]){
						$ret .= " <a href='".e_SELF."?cat.".$row['content_id']."'>".$content_pref["content_catall_text_post"]."</a>";
					}else{
						$ret .= " ".$content_pref["content_catall_text_post"];
					}
				}
				return $ret;
			}
	}elseif($sc_mode=='catlist'){
			if($row['content_text'] && varsettrue($content_pref["content_cat_text"]) && ($content_pref["content_cat_text_char"] > 0 || $content_pref["content_cat_text_char"] == 'all')){
				if($content_pref["content_cat_text_char"] == 'all'){
					$CONTENT_CAT_LIST_TABLE_TEXT = $tp->toHTML($row['content_text'], TRUE, "constants");
				}else{
					$rowtext = preg_replace("/\[newpage.*?]/si", " ", $row['content_text']);
					$rowtext = $tp->toHTML($rowtext, TRUE, "nobreak, constants");
					$rowtext = strip_tags($rowtext);
					$words = explode(" ", $rowtext);
					$CONTENT_CAT_LIST_TABLE_TEXT = implode(" ", array_slice($words, 0, $content_pref["content_cat_text_char"]));
					if($content_pref["content_cat_text_link"]){
						$CONTENT_CAT_LIST_TABLE_TEXT .= " <a href='".e_SELF."?cat.".$row['content_id'].".view'>".$content_pref["content_cat_text_post"]."</a>";
					}else{
						$CONTENT_CAT_LIST_TABLE_TEXT .= " ".$content_pref["content_cat_text_post"];
					}
				}
				return $CONTENT_CAT_LIST_TABLE_TEXT;
			}
	}elseif($sc_mode=='searchresult'){
			return $tp -> toHTML($row['content_text'], TRUE, "");

	}
}
SC_END

SC_BEGIN CM_FILE
global $row, $tp;
if($sc_mode){
	if($sc_mode=='content'){
			global $row, $content_pref;
			if($content_pref["content_content_attach"]){
				$filestmp = explode("[file]", $row['content_file']);
				foreach($filestmp as $key => $value) { 
					if($value == "") { 
						unset($filestmp[$key]); 
					} 
				} 
				$files = array_values($filestmp);
				$content_files_popup_name = str_replace("'", "", $row['content_heading']);
				$file = "";
				$filesexisting = "0";
				for($i=0;$i<count($files);$i++){
					if(file_exists($content_pref['content_file_path'].$files[$i])){
						$filesexisting = $filesexisting+1;
						$file .= "<a href='".$content_pref['content_file_path'].$files[$i]."' rel='external'>".CONTENT_ICON_FILE."</a> ";						
					}else{
						$file .= "&nbsp;";
					}
				}
				return ($filesexisting == "0" ? "" : CONTENT_LAN_41." ".($filesexisting == 1 ? CONTENT_LAN_42 : CONTENT_LAN_43)." ".$file." ");
			}
	}elseif($sc_mode=='print'){
	}elseif($sc_mode=='pdf'){
	}
}
SC_END

SC_BEGIN CM_IMAGES
global $row, $tp;
if($sc_mode){
	if($sc_mode=='content'){
			global $row, $aa, $tp, $authordetails, $content_pref;
			if($content_pref["content_content_images"]){
				$authordetails = $aa -> getAuthor($row['content_author']);
				$imagestmp = explode("[img]", $row['content_image']);
				foreach($imagestmp as $key => $value) { 
					if($value == "") { 
						unset($imagestmp[$key]); 
					} 
				} 
				$images = array_values($imagestmp);
				$content_image_popup_name = $row['content_heading'];
				$ret = "";
				require_once(e_HANDLER."popup_handler.php");
				$pp = new popup;
				$gen = new convert;
				$datestamp = preg_replace("# -.*#", "", $gen -> convert_date($row['content_datestamp'], "long"));
				for($i=0;$i<count($images);$i++){		
					$oSrc = $content_pref['content_image_path'].$images[$i];
					$oSrcThumb = $content_pref['content_image_path']."thumb_".$images[$i];
					$oIconWidth = varsettrue($content_pref["content_upload_image_size_thumb"], '100');
					$oMaxWidth = varsettrue($content_pref["content_upload_image_size"], '500');
					$subheading	= $tp -> toHTML($row['content_subheading'], TRUE);
					$popupname	= $tp -> toHTML($content_image_popup_name, TRUE);
					$author		= $tp -> toHTML($authordetails[1], TRUE);
					$oTitle		= $popupname." ".($i+1);
					$oText		= $popupname." ".($i+1)."<br />".$subheading."<br />".$author." (".$datestamp.")";
					$ret .= $pp -> popup($oSrc, $oSrcThumb, $oIconWidth, $oMaxWidth, $oTitle, $oText);
				}
				return $ret;
			}

	}elseif($sc_mode=='print'){
			global $row, $tp, $content_pref;
			if($content_pref["content_content_images"]){
				$imagestmp = explode("[img]", $row['content_image']);
				foreach($imagestmp as $key => $value) { 
					if($value == "") { 
						unset($imagestmp[$key]); 
					} 
				} 
				$images = array_values($imagestmp);
				$ret = "";
				for($i=0;$i<count($images);$i++){		
					$oSrc = $content_pref['content_image_path'].$images[$i];
					$oSrcThumb = $content_pref['content_image_path']."thumb_".$images[$i];
					$iconwidth = varsettrue($content_pref["content_upload_image_size_thumb"], '100');
					if($iconwidth){
						$style = "style='width:".$iconwidth."px;'";
					}
					
					//use $image if $thumb doesn't exist
					if(is_readable($oSrc)){
						if(!is_readable($oSrcThumb)){
							$thumb = $oSrc;
						}else{
							$thumb = $oSrcThumb;
						}
						$ret .= "<img src='".$thumb."' ".$style." alt='' /><br /><br />";
					}
				}
				return $ret;
			}

	}elseif($sc_mode=='pdf'){
			global $row, $tp, $content_pref;
			if($content_pref["content_content_images"]){
				$imagestmp = explode("[img]", $row['content_image']);
				foreach($imagestmp as $key => $value) { 
					if($value == "") { 
						unset($imagestmp[$key]); 
					} 
				} 
				$images = array_values($imagestmp);
				$ret = "";
				for($i=0;$i<count($images);$i++){		
					$oSrc = $content_pref['content_image_path'].$images[$i];
					$oSrcThumb = $content_pref['content_image_path']."thumb_".$images[$i];
					$iconwidth = varsettrue($content_pref["content_upload_image_size_thumb"], '100');
					if($iconwidth){
						$style = "style='width:".$iconwidth."px;'";
					}
					
					//use $image if $thumb doesn't exist
					if(is_readable($oSrc)){
						if(!is_readable($oSrcThumb)){
							$thumb = $oSrc;
						}else{
							$thumb = $oSrcThumb;
						}
						$thumb = $oSrc;
						$ret .= "<img src='".$thumb."' ".$style." alt='' />";
					}
				}
				return $ret;
			}
	}
}
SC_END

// ############################################################################
// ##### SHORTCODES THAT STILL NEED TO BE CONVERTED TO THE NEW STANDARD! ------
// ############################################################################

SC_BEGIN CONTENT_NEXTPREV
global $CONTENT_NEXTPREV;
return $CONTENT_NEXTPREV;
SC_END

// CONTENT_TYPE_TABLE ------------------------------------------------

SC_BEGIN CONTENT_TYPE_TABLE_LINK
global $row, $tp;
$text = "
[<a href='".e_PLUGIN."content/content.php?cat.list.".$row['content_id']."'>".CONTENT_TYPE_LAN_0."</a>] 
[<a href='".e_PLUGIN."content/content.php?author.list.".$row['content_id']."'>".CONTENT_TYPE_LAN_1."</a>] 
[<a href='".e_PLUGIN."content/content.php?list.".$row['content_id']."'>".CONTENT_TYPE_LAN_2."</a>] 
[<a href='".e_PLUGIN."content/content.php?top.".$row['content_id']."'>".CONTENT_TYPE_LAN_3."</a>] 
[<a href='".e_PLUGIN."content/content.php?score.".$row['content_id']."'>".CONTENT_TYPE_LAN_4."</a>] 
[<a href='".e_PLUGIN."content/content.php?recent.".$row['content_id']."'>".CONTENT_TYPE_LAN_5."</a>]";
return $text;
SC_END

// CONTENT_AUTHOR_TABLE ------------------------------------------------

SC_BEGIN CONTENT_AUTHOR_TABLE_LASTITEM
global $gen, $row, $content_pref;
if($content_pref["content_author_lastitem"]){
	if(!is_object($gen)){ $gen = new convert; }
	$date = $gen -> convert_date($row['content_datestamp'], "short");
	return $date." : <a href='".e_SELF."?content.".$row['content_id']."'>".$row['content_heading']."</a>";
}
SC_END

// CONTENT_ARCHIVE_TABLE ------------------------------------------------

SC_BEGIN CONTENT_ARCHIVE_TABLE_LETTERS
global $content_pref, $CONTENT_ARCHIVE_TABLE_LETTERS;
if($content_pref["content_archive_letterindex"]){
	return $CONTENT_ARCHIVE_TABLE_LETTERS;
}
SC_END

// CONTENT_RECENT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_RECENT_TABLE_INFOPRE
global $CONTENT_RECENT_TABLE_INFOPRE;
if($CONTENT_RECENT_TABLE_INFOPRE === TRUE){
	$CONTENT_RECENT_TABLE_INFOPRE = " ";
	return $CONTENT_RECENT_TABLE_INFOPRE;
}
SC_END
SC_BEGIN CONTENT_RECENT_TABLE_INFOPOST
global $CONTENT_RECENT_TABLE_INFOPOST;
if($CONTENT_RECENT_TABLE_INFOPOST === TRUE){
	$CONTENT_RECENT_TABLE_INFOPOST = " ";
	return $CONTENT_RECENT_TABLE_INFOPOST;
}
SC_END

// CONTENT_CAT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_TABLE_INFO_PRE
global $CONTENT_CAT_TABLE_INFO_PRE;
if($CONTENT_CAT_TABLE_INFO_PRE === TRUE){
	$CONTENT_CAT_TABLE_INFO_PRE = " ";
	return $CONTENT_CAT_TABLE_INFO_PRE;
}
SC_END
SC_BEGIN CONTENT_CAT_TABLE_INFO_POST
global $CONTENT_CAT_TABLE_INFO_POST;
if($CONTENT_CAT_TABLE_INFO_POST === TRUE){
	$CONTENT_CAT_TABLE_INFO_POST = " ";
	return $CONTENT_CAT_TABLE_INFO_POST;
}
SC_END

// CONTENT_CAT_LIST_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CAT_LIST_TABLE_INFO_PRE
global $CONTENT_CAT_LIST_TABLE_INFO_PRE;
if($CONTENT_CAT_LIST_TABLE_INFO_PRE === TRUE){
	$CONTENT_CAT_LIST_TABLE_INFO_PRE = " ";
	return $CONTENT_CAT_LIST_TABLE_INFO_PRE;
}
SC_END
SC_BEGIN CONTENT_CAT_LIST_TABLE_INFO_POST
global $CONTENT_CAT_LIST_TABLE_INFO_POST;
if($CONTENT_CAT_LIST_TABLE_INFO_POST === TRUE){
	$CONTENT_CAT_LIST_TABLE_INFO_POST = " ";
	return $CONTENT_CAT_LIST_TABLE_INFO_POST;
}
SC_END

// CONTENT_CONTENT_TABLE ------------------------------------------------
SC_BEGIN CONTENT_CONTENT_TABLE_INFO_PRE
global $CONTENT_CONTENT_TABLE_INFO_PRE;
if($CONTENT_CONTENT_TABLE_INFO_PRE === TRUE){
	$CONTENT_CONTENT_TABLE_INFO_PRE = " ";
	return $CONTENT_CONTENT_TABLE_INFO_PRE;
}
SC_END
SC_BEGIN CONTENT_CONTENT_TABLE_INFO_POST
global $CONTENT_CONTENT_TABLE_INFO_POST;
if($CONTENT_CONTENT_TABLE_INFO_POST === TRUE){
	$CONTENT_CONTENT_TABLE_INFO_POST = " ";
	return $CONTENT_CONTENT_TABLE_INFO_POST;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA
global $CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA;
if($CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA === TRUE){
	$CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA = " ";
	return $CONTENT_CONTENT_TABLE_INFO_PRE_HEADDATA;
}
SC_END
SC_BEGIN CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA
global $CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA;
if($CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA === TRUE){
	$CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA = " ";
	return $CONTENT_CONTENT_TABLE_INFO_POST_HEADDATA;
}
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_CUSTOM_TAGS
global $CONTENT_CONTENT_TABLE_CUSTOM_TAGS;
return $CONTENT_CONTENT_TABLE_CUSTOM_TAGS;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_PAGENAMES
global $CONTENT_CONTENT_TABLE_PAGENAMES;
return $CONTENT_CONTENT_TABLE_PAGENAMES;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_NEXT_PAGE
global $CONTENT_CONTENT_TABLE_NEXT_PAGE;
return $CONTENT_CONTENT_TABLE_NEXT_PAGE;
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_PREV_PAGE
global $CONTENT_CONTENT_TABLE_PREV_PAGE;
return $CONTENT_CONTENT_TABLE_PREV_PAGE;
SC_END

// CONTENT_SEARCH_TABLE ------------------------------------------------
SC_BEGIN CONTENT_SEARCH_TABLE_SELECT
global $CONTENT_SEARCH_TABLE_SELECT;
return $CONTENT_SEARCH_TABLE_SELECT;
SC_END

SC_BEGIN CONTENT_SEARCH_TABLE_ORDER
global $CONTENT_SEARCH_TABLE_ORDER;
return $CONTENT_SEARCH_TABLE_ORDER;
SC_END

SC_BEGIN CONTENT_SEARCH_TABLE_KEYWORD
global $CONTENT_SEARCH_TABLE_KEYWORD;
return $CONTENT_SEARCH_TABLE_KEYWORD;
SC_END

// ############################################################################
// ##### SHORTCODES USED IN THE MENU ------------------------------------------
// ############################################################################

//##### SEARCH SELECT ORDER --------------------------------------------------

SC_BEGIN CM_MENU_SEARCH
global $content_pref, $aa, $menutypeid;
if($content_pref["content_menu_search"]){
	return $aa -> showOptionsSearch("menu", $menutypeid);
}
SC_END

SC_BEGIN CM_MENU_SELECT
global $content_pref, $aa, $menutypeid;
if( ($content_pref["content_menu_links"] && $content_pref["content_menu_links_dropdown"]) || ($content_pref["content_menu_cat"] && $content_pref["content_menu_cat_dropdown"]) ){
	return $aa -> showOptionsSelect("menu", $menutypeid);
}
SC_END

SC_BEGIN CM_MENU_ORDER
global $content_pref, $aa, $menutypeid;
if($content_pref["content_menu_sort"]){
	return $aa -> showOptionsOrder("menu", $menutypeid);
}
SC_END

//##### LINKS --------------------------------------------------

SC_BEGIN CM_MENU_LINKCAPTION
global $content_pref;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	return ($content_pref["content_menu_links_caption"] != "" ? $content_pref["content_menu_links_caption"] : CONTENT_MENU_LAN_4)."<br />";
}
SC_END

SC_BEGIN CM_MENU_LINKS_ICON
global $content_pref, $bullet;
//TODO review bullet + use switch
	//define icon
	if($content_pref["content_menu_links_icon"] == "0")
	{
		$ret = "";
	}
	elseif($content_pref["content_menu_links_icon"] == "1")
	{
		$ret = $bullet;
	}
	elseif($content_pref["content_menu_links_icon"] == "2")
	{
		$ret = "&middot";
	}
	elseif($content_pref["content_menu_links_icon"] == "3")
	{
		$ret = "&ordm;";
	}
	elseif($content_pref["content_menu_links_icon"] == "4")
	{
		$ret = "&raquo;";
	}
	return $ret;
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWALLCAT
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if($content_pref["content_menu_viewallcat"]){
		return $icon." <a href='".$plugindir."content.php?cat.list.".$menutypeid."'>".CONTENT_LAN_6."</a>";
	}
}
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWALLAUTHOR
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if($content_pref["content_menu_viewallauthor"]){
		return $icon." <a href='".$plugindir."content.php?author.list.".$menutypeid."'>".CONTENT_LAN_7."</a>";
	}
}
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWALLITEM
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if($content_pref["content_menu_viewallitems"]){
		return $icon." <a href='".$plugindir."content.php?list.".$menutypeid."'>".CONTENT_LAN_83."</a>";
	}
}
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWTOPRATED
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if($content_pref["content_menu_viewtoprated"]){
		return $icon." <a href='".$plugindir."content.php?top.".$menutypeid."'>".CONTENT_LAN_8."</a>";
	}
}
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWTOPSCORE
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if($content_pref["content_menu_viewtopscore"]){
		return $icon." <a href='".$plugindir."content.php?score.".$menutypeid."'>".CONTENT_LAN_12."</a>";
	}
}
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWRECENT
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if($content_pref["content_menu_viewrecent"]){
		return $icon." <a href='".$plugindir."content.php?recent.".$menutypeid."'>".CONTENT_LAN_61."</a>";
	}
}
SC_END

SC_BEGIN CM_MENU_LINKS_VIEWSUBMIT
global $content_pref, $plugindir, $menutypeid, $icon;
if($content_pref["content_menu_links"] && !$content_pref["content_menu_links_dropdown"]){
	if( $content_pref["content_menu_viewsubmit"] && $content_pref["content_submit"] && check_class($content_pref["content_submit_class"]) ){
		return $icon." <a href='".$plugindir."content_submit.php'>".CONTENT_LAN_75."</a>";
	}
}
SC_END

//##### CATEGORY LIST --------------------------------------------------

SC_BEGIN CM_MENU_CATEGORY_CAPTION
global $content_pref;
return ($content_pref["content_menu_cat_caption"] != "" ? $content_pref["content_menu_cat_caption"] : CONTENT_MENU_LAN_3);
SC_END

SC_BEGIN CM_MENU_CATEGORY_ICON
//TODO legacy bullet + use switch
global $content_pref, $row, $bullet;
	$ret = "";
	if($content_pref["content_menu_cat_icon"] == "0"){ $ret = "";
	}elseif($content_pref["content_menu_cat_icon"] == "1"){ $ret = $bullet;
	}elseif($content_pref["content_menu_cat_icon"] == "2"){ $ret = "&middot";
	}elseif($content_pref["content_menu_cat_icon"] == "3"){ $ret = "&ordm;";
	}elseif($content_pref["content_menu_cat_icon"] == "4"){ $ret = "&raquo;";
	}elseif($content_pref["content_menu_cat_icon"] == "5"){
		if($row['content_icon'] != "" && is_readable($content_pref['content_cat_icon_path_small'].$row['content_icon']) ){
			$ret = "<a href='".e_PLUGIN."content/content.php?cat.".$row['content_id']."'><img src='".$content_pref['content_cat_icon_path_small'].$row['content_icon']."' alt='' style='border:0;' /></a>";
		}else{
			//default category icon
			if($content_pref["content_menu_cat_icon_default"] == "0"){ $ret = "";
			}elseif($content_pref["content_menu_cat_icon_default"] == "1"){ $ret = $bullet;
			}elseif($content_pref["content_menu_cat_icon_default"] == "2"){ $ret = "&middot";
			}elseif($content_pref["content_menu_cat_icon_default"] == "3"){ $ret = "&ordm;";
			}elseif($content_pref["content_menu_cat_icon_default"] == "4"){ $ret = "&raquo;";
			}
		}
	}
	return $ret;
SC_END

SC_BEGIN CM_MENU_CATEGORY_HEADING
global $row;
return "<a href='".e_PLUGIN."content/content.php?cat.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CM_MENU_CATEGORY_COUNT
global $row, $aa;
return $aa -> countCatItems($row['content_id']);
SC_END

//##### RECENT --------------------------------------------------

SC_BEGIN CM_MENU_RECENT_CAPTION
global $content_pref;
return ($content_pref["content_menu_recent_caption"] != "" ? $content_pref["content_menu_recent_caption"] : CONTENT_MENU_LAN_2);
SC_END

SC_BEGIN CM_MENU_RECENT_ICON
global $content_pref, $row;
	if($content_pref["content_menu_recent_icon"] == "0"){ $ret = "";
	}elseif($content_pref["content_menu_recent_icon"] == "1"){ $ret = $bullet;
	}elseif($content_pref["content_menu_recent_icon"] == "2"){ $ret = "&middot";
	}elseif($content_pref["content_menu_recent_icon"] == "3"){ $ret = "&ordm;";
	}elseif($content_pref["content_menu_recent_icon"] == "4"){ $ret = "&raquo;";
	}elseif($content_pref["content_menu_recent_icon"] == "5"){

		if($content_pref["content_menu_recent_icon_width"]){
			$recenticonwidth = " width:".$content_pref["content_menu_recent_icon_width"]."px; ";
		}else{
			$recenticonwidth = " width:50px; ";
		}
		if($row['content_icon'] != "" && is_readable($content_pref['content_icon_path'].$row['content_icon'])){
			$ret = "<img src='".$content_pref['content_icon_path'].$row['content_icon']."' alt='' style='".$recenticonwidth." border:0;' />";
		}
	}
	return "<a href='".e_PLUGIN."content/content.php?content.".$row['content_id']."'>".$ret."</a>";
SC_END

SC_BEGIN CM_MENU_RECENT_DATE
global $content_pref, $row;
	if($content_pref["content_menu_recent_date"]){
		$datestyle = ($content_pref["content_archive_datestyle"] ? $content_pref["content_archive_datestyle"] : "%d %b %Y");
		return strftime($datestyle, $row['content_datestamp']);
	}
SC_END

SC_BEGIN CM_MENU_RECENT_AUTHOR
global $content_pref, $row, $aa;
	if($content_pref["content_menu_recent_author"]){
		$authordetails = $aa -> getAuthor($row['content_author']);
		return $authordetails[1];
	}
SC_END

SC_BEGIN CM_MENU_RECENT_SUBHEADING
global $content_pref, $row;
	if($content_pref["content_menu_recent_subheading"] && $row['content_subheading']){
		if($content_pref["content_menu_recent_subheading_char"] && $content_pref["content_menu_recent_subheading_char"] != "" && $content_pref["content_menu_recent_subheading_char"] != "0"){
			if(strlen($row['content_subheading']) > $content_pref["content_menu_recent_subheading_char"]) {
				$row['content_subheading'] = substr($row['content_subheading'], 0, $content_pref["content_menu_recent_subheading_char"]).$content_pref["content_menu_recent_subheading_post"];
			}
		}
		return $row['content_subheading'];
	}
SC_END

SC_BEGIN CM_MENU_RECENT_HEADING
global $row;
return "<a href='".e_PLUGIN."content/content.php?content.".$row['content_id']."'>".$row['content_heading']."</a>";
SC_END

SC_BEGIN CMT_CATEGORY
global $CMT_CATEGORY;
return $CMT_CATEGORY;
SC_END

SC_BEGIN CMT_RECENT
global $CMT_RECENT;
return $CMT_RECENT;
SC_END

// ############################################################################
// ##### SHORTCODES USED IN THE ADMIN PAGES -----------------------------------
// ############################################################################

SC_BEGIN CONTENT_ID
global $row;
return $row['content_id'];
SC_END

SC_BEGIN CONTENT_CAT_ICON
global $row, $content_pref, $tp;
$caticon = $content_pref['content_cat_icon_path_small'].$row['content_icon'];
return ($row['content_icon'] ? "<img src='".$caticon."' alt='' style='vertical-align:middle' />" : "&nbsp;");
SC_END

SC_BEGIN CONTENT_ICON
global $CONTENT_ICON;
return $CONTENT_ICON;
SC_END

SC_BEGIN CONTENT_AUTHOR
global $row, $aa;
$authordetails = $aa -> getAuthor($row['content_author']);
return ($authordetails[0] != "0" ? "<a href='".e_BASE."user.php?id.".$authordetails[0]."'>".CONTENT_ICON_USER."</a>" : "")." ".$authordetails[1];
SC_END

SC_BEGIN CONTENT_HEADING
global $row, $tp;
return $tp->toHTML($row['content_heading'], TRUE, "");
SC_END

SC_BEGIN CONTENT_SUBHEADING
global $row, $tp;
return $tp->toHTML($row['content_subheading'], TRUE, "");
SC_END

SC_BEGIN CONTENT_LINK_ITEM
global $row, $plugindir;
return "<a href='".$plugindir."content.php?content.".$row['content_id']."'>".CONTENT_ICON_LINK."</a> ";
SC_END

SC_BEGIN CONTENT_LINK_CATEGORY
global $row, $plugindir;
return "<a href='".$plugindir."content.php?cat.".$row['content_id']."'>".CONTENT_ICON_LINK."</a>";
SC_END

SC_BEGIN CONTENT_LINK_OPTION
global $row;
return "<a href='".e_SELF."?option.".$row['content_id']."'>".CONTENT_ICON_OPTIONS."</a>";
SC_END

SC_BEGIN CONTENT_INHERIT
global $row, $content_pref;
return "<input type='checkbox' value='1' name='content_inherit[".$row['content_id']."]' ".(isset($content_pref['content_inherit']) && $content_pref['content_inherit']=='1' ? "checked='checked'" : "")." /><input type='hidden' name='id[".$row['content_id']."]' value='1' />";
SC_END

SC_BEGIN CONTENT_LINK_MANAGER
global $row;
return "<a href='".e_SELF."?manager.".intval($row['content_id'])."'>".CONTENT_ICON_CONTENTMANAGER_SMALL."</a>";
SC_END

SC_BEGIN CONTENT_MANAGER_PRE
global $row, $catarray, $catid;
$pre = '';
if($row['content_parent'] != "0"){
	for($b=0;$b<(count($catarray[$catid])/2)-1;$b++){
		$pre .= "_";
	}
}
return $pre;
SC_END

SC_BEGIN CONTENT_ADMIN_HTML_CLASS
global $row;
if($row['content_parent'] == "0"){
	//top level
	$class = "forumheader";
}else{
	//sub level
	$class = "forumheader3";
}
return $class;
SC_END

SC_BEGIN CONTENT_MANAGER_INHERIT
global $row, $content_pref;
return "<input type='checkbox' value='1' name='content_manager_inherit[".$row['content_id']."]' ".(isset($content_pref['content_manager_inherit']) && $content_pref['content_manager_inherit']=='1' ? "checked='checked'" : "")." /><input type='hidden' name='id[".$row['content_id']."]' value='1' />";
SC_END

SC_BEGIN CONTENT_ADMIN_MANAGER_SUBMIT
global $row, $content_pref;
return r_userclass("content_manager_submit", $content_pref["content_manager_submit"], 'off', "public,guest,nobody,member,admin,classes");
SC_END

SC_BEGIN CONTENT_ADMIN_MANAGER_APPROVE
global $row, $content_pref;
return r_userclass("content_manager_approve", $content_pref["content_manager_approve"], 'off', "public,guest,nobody,member,admin,classes");
SC_END

SC_BEGIN CONTENT_ADMIN_MANAGER_PERSONAL
global $row, $content_pref;
return r_userclass("content_manager_personal", $content_pref["content_manager_personal"], 'off', "nobody,member,admin,classes");
SC_END

SC_BEGIN CONTENT_ADMIN_MANAGER_CATEGORY
global $row, $content_pref;
return r_userclass("content_manager_category", $content_pref["content_manager_category"], 'off', "nobody,member,admin,classes");
SC_END

SC_BEGIN CONTENT_ADMIN_MANAGER_OPTIONS
global $CONTENT_ADMIN_MANAGER_OPTIONS;
return $CONTENT_ADMIN_MANAGER_OPTIONS;
SC_END

SC_BEGIN CONTENT_ORDER
global $row;
return $row['content_order'];
SC_END

SC_BEGIN CONTENT_ADMIN_CATEGORY
global $CONTENT_ADMIN_CATEGORY;
return $CONTENT_ADMIN_CATEGORY;
SC_END

SC_BEGIN CONTENT_ADMIN_OPTIONS
global $CONTENT_ADMIN_OPTIONS;
return $CONTENT_ADMIN_OPTIONS;
SC_END

SC_BEGIN CONTENT_ADMIN_BUTTON
global $CONTENT_ADMIN_BUTTON;
return $CONTENT_ADMIN_BUTTON;
SC_END

SC_BEGIN CONTENT_ADMIN_SPACER
global $CONTENT_ADMIN_SPACER;
return ($parm==true || $CONTENT_ADMIN_SPACER ? " " : "");
SC_END

SC_BEGIN CONTENT_ADMIN_FORM_TARGET
global $CONTENT_ADMIN_FORM_TARGET;
return $CONTENT_ADMIN_FORM_TARGET;
SC_END

SC_BEGIN CONTENT_ADMIN_ORDER_SELECT
global $CONTENT_ADMIN_ORDER_SELECT;
return $CONTENT_ADMIN_ORDER_SELECT;
SC_END

SC_BEGIN CONTENT_ADMIN_ORDER_UPDOWN
global $CONTENT_ADMIN_ORDER_UPDOWN;
return $CONTENT_ADMIN_ORDER_UPDOWN;
SC_END

SC_BEGIN CONTENT_ADMIN_ORDER_AMOUNT
global $CONTENT_ADMIN_ORDER_AMOUNT;
return $CONTENT_ADMIN_ORDER_AMOUNT;
SC_END

SC_BEGIN CONTENT_ADMIN_ORDER_CAT
global $CONTENT_ADMIN_ORDER_CAT;
return $CONTENT_ADMIN_ORDER_CAT;
SC_END

SC_BEGIN CONTENT_ADMIN_ORDER_CATALL
global $CONTENT_ADMIN_ORDER_CATALL;
return $CONTENT_ADMIN_ORDER_CATALL;
SC_END

SC_BEGIN CONTENT_ADMIN_LETTERINDEX
global $CONTENT_ADMIN_LETTERINDEX;
return $CONTENT_ADMIN_LETTERINDEX;
SC_END

//##### CONTENT CATEGORY CREATE FORM -------------------------

SC_BEGIN CATFORM_CATEGORY
global $CATFORM_CATEGORY;
return $CATFORM_CATEGORY;
SC_END

SC_BEGIN CATFORM_HEADING
global $row, $rs;
return $rs -> form_text("cat_heading", 90, $row['content_heading'], 250);
SC_END

SC_BEGIN CATFORM_SUBHEADING
global $row, $rs, $show;
if($show['subheading']===true){
	return $rs -> form_text("cat_subheading", 90, $row['content_subheading'], 250);
}
SC_END

SC_BEGIN CATFORM_TEXT
global $row, $rs, $show, $pref;
require_once(e_HANDLER."ren_help.php");
$insertjs = (!$pref['wysiwyg'] ? "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'" : "");
$text = $rs -> form_textarea("cat_text", 80, 20, $row['content_text'], $insertjs)."<br />";
if (!$pref['wysiwyg']) { $text .= $rs -> form_text("helpb", 90, '', '', "helpbox")."<br />". display_help("helpb"); }
return $text;
SC_END

SC_BEGIN CATFORM_DATESTART
global $row, $rs, $show, $months, $ne_day, $ne_month, $ne_year, $current_year;
if($show['startdate']===true){
	$text = "
	".$rs -> form_select_open("ne_day")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
	for($count=1; $count<=31; $count++){
		$text .= $rs -> form_option($count, ($ne_day == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("ne_month")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
	for($count=1; $count<=12; $count++){
		$text .= $rs -> form_option($months[($count-1)], ($ne_month == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("ne_year")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
	for($count=($current_year-5); $count<=$current_year; $count++){
		$text .= $rs -> form_option($count, ($ne_year == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close();
	return $text;
}
SC_END

SC_BEGIN CATFORM_DATEEND
global $row, $rs, $show, $months, $end_day, $end_month, $end_year, $current_year;
if($show['enddate']===true){
	$text = "
	".$rs -> form_select_open("end_day")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 1, "none");
	for($count=1; $count<=31; $count++){
		$text .= $rs -> form_option($count, ($end_day == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("end_month")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 1, "none");
	for($count=1; $count<=12; $count++){
		$text .= $rs -> form_option($months[($count-1)], ($end_month == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("end_year")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 1, "none");
	for($count=($current_year-5); $count<=$current_year; $count++){
		$text .= $rs -> form_option($count, ($end_year == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close();
	return $text;
}
SC_END

SC_BEGIN CATFORM_UPLOAD
global $row, $show, $content_pref;
if($show['uploadicon']===true){
	$text='';
	if(!FILE_UPLOADS){
		$text = "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
	}else{
		if(!is_writable($content_pref['content_cat_icon_path_large'])){
			$text = "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_pref['content_cat_icon_path_large']." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
		}
		$text .= CONTENT_ADMIN_CAT_LAN_62."
		<input class='tbox' type='file' name='file_userfile[]'  size='58' /> 
		<input type='hidden' name='iconpathlarge' value='".$content_pref['content_cat_icon_path_large']."' />
		<input type='hidden' name='iconpathsmall' value='".$content_pref['content_cat_icon_path_small']."' />
		<input class='button' type='submit' name='uploadcaticon' value='".CONTENT_ADMIN_CAT_LAN_63."' />";
	}
	return $text;
}
SC_END

SC_BEGIN CATFORM_ICON
global $row, $rs, $show, $fl, $content_pref;
if($show['selecticon']===true){
	$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*');
	$iconlist = $fl->get_files($content_pref['content_cat_icon_path_large'],"",$rejectlist);
	$text = $rs -> form_text("cat_icon", 60, $row['content_icon'], 100)."
	".$rs -> form_button("button", '', CONTENT_ADMIN_CAT_LAN_8, "onclick=\"expandit('divcaticon')\"")."
	<div id='divcaticon' style='display:none;'>";
	foreach($iconlist as $icon){
		$text .= "<a href=\"javascript:insertext('".$icon['fname']."','cat_icon','divcaticon')\"><img src='".$icon['path'].$icon['fname']."' style='border:0' alt='' /></a> ";
	}
	$text .= "</div>";
	return $text;
}
SC_END

SC_BEGIN CATFORM_COMMENT
global $row, $rs, $show;
if($show['comment']===true){
	return $rs -> form_radio("cat_comment", "1", ($row['content_comment'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85." ".$rs -> form_radio("cat_comment", "0", ($row['content_comment'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86;
}
SC_END

SC_BEGIN CATFORM_RATING
global $row, $rs, $show;
if($show['rating']===true){
	return $rs -> form_radio("cat_rate", "1", ($row['content_rate'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85." ".$rs -> form_radio("cat_rate", "0", ($row['content_rate'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86;
}
SC_END

SC_BEGIN CATFORM_PEICON
global $row, $rs, $show;
if($show['pe']===true){
	return $rs -> form_radio("cat_pe", "1", ($row['content_pe'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85." ".$rs -> form_radio("cat_pe", "0", ($row['content_pe'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86;
}
SC_END

SC_BEGIN CATFORM_VISIBILITY
global $row, $show;
if($show['visibility']===true){
	return r_userclass("cat_class",$row['content_class'], "CLASSES");
}
SC_END

//##### CONTENT CREATE FORM -------------------------

SC_BEGIN CONTENTFORM_CATEGORYSELECT
global $CONTENTFORM_CATEGORYSELECT;
return $CONTENTFORM_CATEGORYSELECT;
SC_END

SC_BEGIN CONTENTFORM_CATEGORY
global $CONTENTFORM_CATEGORY;
return $CONTENTFORM_CATEGORY;
SC_END

SC_BEGIN CONTENTFORM_HEADING
global $row, $rs;
return $rs -> form_text("content_heading", 74, $row['content_heading'], 250);
SC_END

SC_BEGIN CONTENTFORM_SUBHEADING
global $row, $rs, $show;
if($show['subheading']===true){
	return $rs -> form_text("content_subheading", 74, $row['content_subheading'], 250);
}
SC_END

SC_BEGIN CONTENTFORM_SUMMARY
global $row, $rs, $show;
if($show['summary']===true){
	return $rs -> form_textarea("content_summary", 74, 5, $row['content_summary']);
}
SC_END

SC_BEGIN CONTENTFORM_TEXT
global $row, $rs, $tp, $show, $pref;
if(e_WYSIWYG){
	$row['content_text'] = $tp->replaceConstants($row['content_text'], true);
}
require_once(e_HANDLER."ren_help.php");
$insertjs = (!e_WYSIWYG) ? "onselect='storeCaret(this);' onclick='storeCaret(this);' onkeyup='storeCaret(this);'": "";
$text = $rs -> form_textarea("content_text", 74, 20, $row['content_text'], $insertjs)."<br />";
if (!$pref['wysiwyg']) { $text .= $rs -> form_text("helpb", 90, '', '', "helpbox")."<br />".display_help("helpb"); }
return $text;
SC_END

SC_BEGIN CONTENTFORM_AUTHOR
global $row, $rs, $show, $content_author_name_value, $content_author_name_js, $content_author_email_value, $content_author_email_js, $content_author_id;
$text = "
<table style='width:100%; text-align:left;'>
<tr><td>".CONTENT_ADMIN_ITEM_LAN_14."</td><td>".$rs -> form_text("content_author_name", 70, $content_author_name_value, 100, "tbox", "", "", $content_author_name_js )."</td></tr>
<tr><td>".CONTENT_ADMIN_ITEM_LAN_15."</td><td>".$rs -> form_text("content_author_email", 70, $content_author_email_value, 100, "tbox", "", "", $content_author_email_js )."
".$rs -> form_hidden("content_author_id", $content_author_id)."
</td></tr></table>";
return $text;
SC_END

SC_BEGIN CONTENTFORM_DATESTART
global $row, $rs, $show, $months, $ne_day, $ne_month, $ne_year, $current_year;
if($show['startdate']===true){
	$text = "
	".$rs -> form_select_open("ne_day")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
	for($count=1; $count<=31; $count++){
		$text .= $rs -> form_option($count, (isset($ne_day) && $ne_day == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("ne_month")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
	for($count=1; $count<=12; $count++){
		$text .= $rs -> form_option($months[($count-1)], (isset($ne_month) && $ne_month == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("ne_year")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
	for($count=($current_year-5); $count<=($current_year+1); $count++){
		$text .= $rs -> form_option($count, (isset($ne_year) && $ne_year == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close();
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_DATEEND
global $row, $rs, $show, $months, $end_day, $end_month, $end_year, $current_year;
if($show['enddate']===true){
	$text = "
	".$rs -> form_select_open("end_day")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_12, 0, "none");
	for($count=1; $count<=31; $count++){
		$text .= $rs -> form_option($count, (isset($end_day) && $end_day == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("end_month")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_13, 0, "none");
	for($count=1; $count<=12; $count++){
		$text .= $rs -> form_option($months[($count-1)], (isset($end_month) && $end_month == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close()."
	".$rs -> form_select_open("end_year")."
	".$rs -> form_option(CONTENT_ADMIN_DATE_LAN_14, 0, "none");
	for($count=($current_year-5); $count<=($current_year+1); $count++){
		$text .= $rs -> form_option($count, (isset($end_year) && $end_year == $count ? "1" : "0"), $count);
	}
	$text .= $rs -> form_select_close();
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_UPLOAD
global $row, $rs, $show, $content_pref;
if($show['upload']===true){
	$text = "";
	if(!FILE_UPLOADS){
		$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_21."</b>";
	}else{
		if($show['icon']===true){
			if(!is_writable($content_pref['content_icon_path_tmp'])){
				$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_pref['content_icon_path_tmp']." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
			}
		}
		if($show['attach']===true){
			if(!is_writable($content_pref['content_file_path_tmp'])){
				$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_pref['content_file_path_tmp']." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
			}
		}
		if($show['images']===true){
			if(!is_writable($content_pref['content_image_path_tmp'])){
				$text .= "<b>".CONTENT_ADMIN_ITEM_LAN_22." ".$content_pref['content_image_path_tmp']." ".CONTENT_ADMIN_ITEM_LAN_23."</b><br />";
			}
		}
		$text .= "<br />
		<input class='tbox' type='file' name='file_userfile[]'  size='36' /> 
			".$rs -> form_select_open("uploadtype")."
			".($show['icon'] ? $rs -> form_option(CONTENT_ADMIN_ITEM_LAN_114, "0", "1") : '')."
			".($show['attach'] ? $rs -> form_option(CONTENT_ADMIN_ITEM_LAN_115, "0", "2") : '')."
			".($show['images'] ? $rs -> form_option(CONTENT_ADMIN_ITEM_LAN_116, "0", "3") : '')."
			".$rs -> form_select_close()."
		".($show['icon'] ? "<input type='hidden' name='tmppathicon' value='".$content_pref['content_icon_path_tmp']."' />" : '')."
		".($show['attach'] ? "<input type='hidden' name='tmppathfile' value='".$content_pref['content_file_path_tmp']."' />" : '')."
		".($show['images'] ? "<input type='hidden' name='tmppathimage' value='".$content_pref['content_image_path_tmp']."' />" : '')."
		<input class='button' type='submit' name='uploadfile' value='".CONTENT_ADMIN_ITEM_LAN_104."' />";
	}
	$text .= "<br />";
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_ICON
global $row, $rs, $show, $iconlist;
if($show['icon']===true){
	$text = $rs -> form_text("content_icon", 60, $row['content_icon'], 100)."
	".$rs -> form_button("button", '', CONTENT_ADMIN_ITEM_LAN_105, "onclick=\"expandit('divicon')\"")."
	<div id='divicon' style='display:none;'>";
	if(empty($iconlist)){
		$text .= CONTENT_ADMIN_ITEM_LAN_121;
	}else{
		foreach($iconlist as $icon){
			if(file_exists($icon['path']."thumb_".$icon['fname'])){
				$img = "<img src='".$icon['path']."thumb_".$icon['fname']."' style='width:50px; border:0' alt='' />";
			}else{
				$img = "<img src='".$icon['path'].$icon['fname']."' style='width:50px; border:0' alt='' />";
			}
			$text .= "<a href=\"javascript:insertext('".$icon['fname']."','content_icon','divicon')\">".$img."</a> ";
		}
	}
	$text .= "</div>";
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_ATTACH
global $row, $rs, $show, $checkattachnumber, $filelist;
if($show['attach']===true){

	$filetmp = explode("[file]", $row['content_file']);
	foreach($filetmp as $key => $value) { 
		if($value == "") { 
			unset($filetmp[$key]); 
		} 
	} 
	$attachments = array_values($filetmp);
	for($i=0;$i<$checkattachnumber;$i++){
		$k=$i+1;
		$num = (strlen($k) == 1 ? "0".$k : $k);
		$attachments[$i] = ($attachments[$i] ? $attachments[$i] : "");

		//choose file
		$text .= "
		<div style='padding:2px;'>
		".$num." ".$rs -> form_text("content_files".$i."", 60, $attachments[$i], 100)."
		".$rs -> form_button("button", '', CONTENT_ADMIN_ITEM_LAN_105, "onclick=\"expandit('divfile".$i."')\"")."
		<div id='divfile".$i."' style='display:none;'>";
		if(empty($filelist)){
			$text .= CONTENT_ADMIN_ITEM_LAN_122;
		}else{
			foreach($filelist as $file){
				$text .= CONTENT_ICON_FILE." <a href=\"javascript:insertext('".$file['fname']."','content_files".$i."','divfile".$i."')\">".$file['fname']."</a><br />";
			}
		}
		$text .= "</div></div>";
	}
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_IMAGES
global $row, $rs, $show, $checkimagesnumber, $imagelist;
if($show['images']===true){
	$imagestmp = explode("[img]", $row['content_image']);
	foreach($imagestmp as $key => $value) { 
		if($value == "") { 
			unset($imagestmp[$key]); 
		} 
	} 
	$imagesarray = array_values($imagestmp);
	for($i=0;$i<$checkimagesnumber;$i++){
		$k=$i+1;
		$num = (strlen($k) == 1 ? "0".$k : $k);
		$imagesarray[$i] = ($imagesarray[$i] ? $imagesarray[$i] : "");

		//choose image
		$text .= "
		<div style='padding:2px;'>
		".$num." ".$rs -> form_text("content_images".$i."", 60, $imagesarray[$i], 100)."
		".$rs -> form_button("button", '', CONTENT_ADMIN_ITEM_LAN_105, "onclick=\"expandit('divimage".$i."')\"")."
		<div id='divimage".$i."' style='display:none;'>";
		if(empty($imagelist)){
			$text .= CONTENT_ADMIN_ITEM_LAN_123;
		}else{
			foreach($imagelist as $image){
				if(file_exists($image['path']."thumb_".$image['fname'])){
					$img = "<img src='".$image['path']."thumb_".$image['fname']."' style='width:100px; border:0' alt='' />";
				}else{
					$img = "<img src='".$image['path'].$image['fname']."' style='width:100px; border:0' alt='' />";
				}
				$text .= "<a href=\"javascript:insertext('".$image['fname']."','content_images".$i."','divimage".$i."')\">".$img."</a> ";
			}
		}
		$text .= "</div></div>";								
	}
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_COMMENT
global $row, $rs, $show;
if($show['comment']===true){
	return $rs -> form_radio("content_comment", "1", ($row['content_comment'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85." ".$rs -> form_radio("content_comment", "0", ($row['content_comment'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86;
}
SC_END

SC_BEGIN CONTENTFORM_RATING
global $row, $rs, $show;
if($show['rating']===true){
	return $rs -> form_radio("content_rate", "1", ($row['content_rate'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85." ".$rs -> form_radio("content_rate", "0", ($row['content_rate'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86;
}
SC_END

SC_BEGIN CONTENTFORM_PEICON
global $row, $rs, $show;
if($show['pe']===true){
	return $rs -> form_radio("content_pe", "1", ($row['content_pe'] ? "1" : "0"), "", "").CONTENT_ADMIN_ITEM_LAN_85." ".$rs -> form_radio("content_pe", "0", ($row['content_pe'] ? "0" : "1"), "", "").CONTENT_ADMIN_ITEM_LAN_86;
}
SC_END

SC_BEGIN CONTENTFORM_VISIBILITY
global $row, $show;
if($show['visibility']===true){
	return r_userclass("content_class",$row['content_class'], "CLASSES");
}
SC_END

SC_BEGIN CONTENTFORM_SCORE
global $row, $rs, $show;
if($show['score']===true){
	$text = $rs -> form_select_open("content_score")."
	".$rs -> form_option(CONTENT_ADMIN_ITEM_LAN_41, 0, "none");
	for($a=1; $a<=100; $a++){
		$text .= $rs -> form_option($a, ($row['content_score'] == $a ? "1" : "0"), $a);
	}
	$text .= $rs -> form_select_close();
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_META
global $row, $rs, $show;
if($show['meta']===true){
	return $rs -> form_text("content_meta", 74, $row['content_meta'], 250);
}
SC_END

SC_BEGIN CONTENTFORM_LAYOUT
global $row, $rs, $show, $tp, $fl, $content_pref;
if($show['layout']===true){

	if(!isset($content_pref["content_theme"])){
		$dir = $plugindir."templates/default";
	}else{
		if(is_readable($tp->replaceConstants($content_pref["content_theme"])."content_content_template.php")){
			$dir = $tp->replaceConstants($content_pref["content_theme"]);
		}else{
			$dir = $plugindir."templates/default";
		}
	}
	//get_files($path, $fmask = '', $omit='standard', $recurse_level = 0, $current_level = 0, $dirs_only = FALSE)
	$rejectlist = array('$.','$..','/','CVS','thumbs.db','Thumbs.db','*._$', 'index', 'null*', '.bak');
	$templatelist = $fl->get_files($dir,"content_content",$rejectlist);

	//template
	$check = "";
	if(isset($row['content_layout']) && $row['content_layout'] != ""){
		$check = $row['content_layout'];
	}else{
		if(isset($content_pref["content_layout"])){
			$check = $content_pref["content_layout"];
		}
	}

	$text = $rs -> form_select_open("content_layout")."
	".$rs -> form_option(CONTENT_ADMIN_ITEM_LAN_94, 0, "none");
	foreach($templatelist as $template){
		$templatename = substr($template['fname'], 25, -4);
		$templatename = ($template['fname'] == "content_content_template.php" ? "default" : $templatename);
		$text .= $rs -> form_option($templatename, ($check == $template['fname'] ? "1" : "0"), $template['fname']);
	}
	$text .= $rs -> form_select_close();
	return $text;
}
SC_END

SC_BEGIN CONTENTFORM_CUSTOM
global $CONTENTFORM_CUSTOM;
return $CONTENTFORM_CUSTOM;
SC_END

SC_BEGIN CONTENTFORM_CUSTOM_KEY
global $CONTENTFORM_CUSTOM_KEY;
return $CONTENTFORM_CUSTOM_KEY;
SC_END

SC_BEGIN CONTENTFORM_CUSTOM_VALUE
global $CONTENTFORM_CUSTOM_VALUE;
return $CONTENTFORM_CUSTOM_VALUE;
SC_END

SC_BEGIN CONTENTFORM_PRESET
global $CONTENTFORM_PRESET;
return $CONTENTFORM_PRESET;
SC_END

SC_BEGIN CONTENTFORM_PRESET_KEY
global $CONTENTFORM_PRESET_KEY;
return $CONTENTFORM_PRESET_KEY;
SC_END

SC_BEGIN CONTENTFORM_PRESET_VALUE
global $CONTENTFORM_PRESET_VALUE;
return $CONTENTFORM_PRESET_VALUE;
SC_END

// ############################################################################
// ##### DEPRECATED SHORTCODES ! WILL BE REMOVED AFTER THE NEXT RELEASE -------
// ############################################################################

SC_BEGIN CONTENT_CONTENTMANAGER_ICONNEW
global $tp;
return $tp -> parseTemplate("{CM_ICON|manager_new}");
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONEDIT
global $tp;
return $tp -> parseTemplate("{CM_ICON|manager_edit}");
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_ICONSUBM
global $tp;
return $tp -> parseTemplate("{CM_ICON|manager_submit}");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|manager_link}");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_MANAGER_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|manager_link}");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_TOTAL
global $tp;
return $tp -> parseTemplate("{CM_AMOUNT|type}");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|type}");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|type}");
SC_END

SC_BEGIN CONTENT_TYPE_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|type}");
SC_END

SC_BEGIN CONTENT_TOP_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|top}");
SC_END

SC_BEGIN CONTENT_TOP_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|top}");
SC_END

SC_BEGIN CONTENT_TOP_TABLE_AUTHOR
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|top}");
SC_END

SC_BEGIN CONTENT_TOP_TABLE_RATING
global $tp;
return $tp -> parseTemplate("{CM_RATING|top}");
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|score}");
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|score}");
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_AUTHOR
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|score}");
SC_END

SC_BEGIN CONTENT_SCORE_TABLE_SCORE
global $tp;
return $tp -> parseTemplate("{CM_SCORE|score}");
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_TOTAL
global $tp;
return $tp -> parseTemplate("{CM_AMOUNT|author}");
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_NAME
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|author}");
SC_END

SC_BEGIN CONTENT_AUTHOR_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|author}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_SUMMARY
global $tp;
return $tp -> parseTemplate("{CM_SUMMARY|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_TEXT
global $tp;
return $tp -> parseTemplate("{CM_TEXT|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_DATE
global $tp;
return $tp -> parseTemplate("{CM_DATE|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EPICONS
global $tp;
return $tp -> parseTemplate("{CM_EPICONS|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_AUTHORDETAILS
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_EDITICON
global $tp;
return $tp -> parseTemplate("{CM_EDITICON|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_REFER
global $tp;
return $tp -> parseTemplate("{CM_REFER|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_RATING
global $tp;
return $tp -> parseTemplate("{CM_RATING|recent}");
SC_END

SC_BEGIN CONTENT_RECENT_TABLE_PARENT
global $tp;
return $tp -> parseTemplate("{CM_PARENT|recent}");
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|archive}");
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_DATE
global $tp;
return $tp -> parseTemplate("{CM_DATE|archive}");
SC_END

SC_BEGIN CONTENT_ARCHIVE_TABLE_AUTHOR
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|archive}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_FILE
global $tp;
return $tp -> parseTemplate("{CM_FILE|content}");
SC_END

SC_BEGIN CONTENT_PRINT_IMAGES
global $tp;
return $tp -> parseTemplate("{CM_IMAGES|print}");
SC_END

SC_BEGIN CONTENT_PDF_IMAGES
global $tp;
return $tp -> parseTemplate("{CM_IMAGES|pdf}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_IMAGES
global $tp;
return $tp -> parseTemplate("{CM_IMAGES|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_COMMENT
global $tp;
return $tp -> parseTemplate("{CM_COMMENT|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_PARENT
global $tp;
return $tp -> parseTemplate("{CM_PARENT|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_REFER
global $tp;
return $tp -> parseTemplate("{CM_REFER|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_DATE
global $tp;
return $tp -> parseTemplate("{CM_DATE|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_AUTHORDETAILS
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EPICONS
global $tp;
return $tp -> parseTemplate("{CM_EPICONS|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_EDITICON
global $tp;
return $tp -> parseTemplate("{CM_EDITICON|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_RATING
global $tp;
return $tp -> parseTemplate("{CM_RATING|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SCORE
global $tp;
return $tp -> parseTemplate("{CM_SCORE|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_SUMMARY
global $tp;
return $tp -> parseTemplate("{CM_SUMMARY|content}");
SC_END

SC_BEGIN CONTENT_CONTENT_TABLE_TEXT
global $tp;
return $tp -> parseTemplate("{CM_TEXT|content}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_DATE
global $tp;
return $tp -> parseTemplate("{CM_DATE|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_EPICONS
global $tp;
return $tp -> parseTemplate("{CM_EPICONS|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_TEXT
global $tp;
return $tp -> parseTemplate("{CM_TEXT|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_RATING
global $tp;
return $tp -> parseTemplate("{CM_RATING|cat}");
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|catlistsub}");
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|catlistsub}");
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|catlistsub}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUMMARY
global $tp;
return $tp -> parseTemplate("{CM_SUMMARY|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_TEXT
global $tp;
return $tp -> parseTemplate("{CM_TEXT|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_DATE
global $tp;
return $tp -> parseTemplate("{CM_DATE|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_EPICONS
global $tp;
return $tp -> parseTemplate("{CM_EPICONS|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_RATING
global $tp;
return $tp -> parseTemplate("{CM_RATING|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AUTHORDETAILS
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AUTHORDETAILS
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|cat}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_AMOUNT
global $tp;
return $tp -> parseTemplate("{CM_AMOUNT|cat}");
SC_END

SC_BEGIN CONTENT_CAT_LISTSUB_TABLE_AMOUNT
global $tp;
return $tp -> parseTemplate("{CM_AMOUNT|catlistsub}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_AMOUNT
global $tp;
return $tp -> parseTemplate("{CM_AMOUNT|catlist}");
SC_END

SC_BEGIN CONTENT_CAT_TABLE_COMMENT
global $tp;
return $tp -> parseTemplate("{CM_COMMENT|cat}");
SC_END

SC_BEGIN CONTENT_CAT_LIST_TABLE_COMMENT
global $tp;
return $tp -> parseTemplate("{CM_COMMENT|catlist}");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_ICON
global $tp;
return $tp -> parseTemplate("{CM_ICON|searchresult}");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_HEADING
global $tp;
return $tp -> parseTemplate("{CM_HEADING|searchresult}");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|searchresult}");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_DATE
global $tp;
return $tp -> parseTemplate("{CM_DATE|searchresult}");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_TEXT
global $tp;
return $tp -> parseTemplate("{CM_TEXT|searchresult}");
SC_END

SC_BEGIN CONTENT_SEARCHRESULT_TABLE_AUTHORDETAILS
global $tp;
return $tp -> parseTemplate("{CM_AUTHOR|searchresult}");
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_CATEGORY
global $tp;
return $tp -> parseTemplate("{CM_HEADING|manager}");
SC_END

SC_BEGIN CONTENT_CONTENTMANAGER_CATEGORY_SUBHEADING
global $tp;
return $tp -> parseTemplate("{CM_SUBHEADING|manager}");
SC_END

*/
?>