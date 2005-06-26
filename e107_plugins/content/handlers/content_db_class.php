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
|		$Source: /cvs_backup/e107_0.7/e107_plugins/content/handlers/content_db_class.php,v $
|		$Revision: 1.31 $
|		$Date: 2005-06-26 22:41:11 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

class contentdb{

			//function js_location($qry){
			//	echo "<script type='text/javascript'>document.location.href='".$qry."'</script>\n";
			//}

			function dbContentUpdate($mode){
						global $pref, $qs, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache, $eArrayStorage;

						$_POST['content_heading']		= $tp -> toDB($_POST['content_heading']);
						$_POST['content_subheading']	= $tp -> toDB($_POST['content_subheading']);
						$_POST['content_summary']		= $tp -> toDB($_POST['content_summary']);
						$_POST['content_text']			= $tp -> toDB($_POST['content_text']);
						$_POST['parent']				= ($_POST['parent'] ? $_POST['parent'] : "0");
						$_POST['content_class']			= ($_POST['content_class'] ? $_POST['content_class'] : "0");
						$_POST['content_meta']			= $tp -> toDB($_POST['content_meta']);

						if(USER){
							if(!($_POST['content_author_id'] == USERID && $_POST['content_author_name'] == USERNAME && $_POST['content_author_email'] == USEREMAIL) ){
									//$author = $_POST['content_author_id']."^".$_POST['content_author_name']."^".$_POST['content_author_email'];
									$author = "0^".$_POST['content_author_name']."^".$_POST['content_author_email'];
							}else{
								$author = USERID;
							}
						}else{
							$author = "0^".$_POST['content_author_name']."^".$_POST['content_author_email'];
						}

						$mainparent						= $aa -> getMainParent($_POST['parent']);
						$content_pref					= $aa -> getContentPref($mainparent);
						$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
						$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
						$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
						$content_image_path				= $tp -> replaceConstants($content_pref["content_image_path_{$mainparent}"]);
						$content_file_path				= $tp -> replaceConstants($content_pref["content_file_path_{$mainparent}"]);
						$content_tmppath_icon			= e_PLUGIN."content/images/icon/tmp/";
						$content_tmppath_file			= e_PLUGIN."content/images/file/tmp/";
						$content_tmppath_image			= e_PLUGIN."content/images/image/tmp/";

						//move icon to correct folder
						if($_POST['content_icon']){
							$icon = $_POST['content_icon'];							
							if(file_exists($content_tmppath_icon.$icon)){
								rename($content_tmppath_icon.$icon, $content_icon_path.$icon);
							}
						}

						$sumf = 0;
						$sumi = 0;
						foreach($_POST as $k => $v){
							if(preg_match("#^content_files#",$k)){
								$sumf = $sumf+1;
							}
							if(preg_match("#^content_images#",$k)){
								$sumi = $sumi+1;
							}
						}
						//move attachments to correct folder
						$totalattach = "";
						for($i=0;$i<$sumf;$i++){
							$attach{$i} = $_POST["content_files{$i}"];
							if(file_exists($content_tmppath_file.$attach{$i})){
								rename($content_tmppath_file.$attach{$i}, $content_file_path.$attach{$i});
							}
							$totalattach .= "[file]".$attach{$i};
						}
						//move images to correct folder
						$totalimages = "";
						for($i=0;$i<$sumi;$i++){
							$image{$i} = $_POST["content_images{$i}"];
							if(file_exists($content_tmppath_image.$image{$i})){
								rename($content_tmppath_image.$image{$i}, $content_image_path.$image{$i});
							}
							if(file_exists($content_tmppath_image."thumb_".$image{$i})){
								rename($content_tmppath_image."thumb_".$image{$i}, $content_image_path."thumb_".$image{$i});
							}
							$totalimages .= "[img]".$image{$i};
						}

						if($_POST['update_datestamp']){
							$starttime = time();
						}else{
							if(isset($_POST['content_datestamp']) && $_POST['content_datestamp'] != "" && $_POST['content_datestamp'] != "0"){
								$starttime = $_POST['content_datestamp'];
							}else{
								$starttime = time();
							}
						}

						if($_POST['end_day'] != "none" && $_POST['end_month'] != "none" && $_POST['end_year'] != "none"){
							$endtime = mktime( 0, 0, 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);
						}else{
							$endtime = "0";
						}

						//custom additional data tags
						for($i=0;$i<$content_pref["content_admin_custom_number_{$mainparent}"];$i++){
							if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
								$keystring = $tp->toDB($_POST["content_custom_key_{$i}"]);
								$custom["content_custom_{$keystring}"] = $tp->toDB($_POST["content_custom_value_{$i}"]);
							}
						}
						//preset additional data tags
						if(isset($_POST['content_custom_preset_key']) && $_POST['content_custom_preset_key']){
							$custom['content_custom_presettags'] = $_POST['content_custom_preset_key'];
							$contentprefvalue = $eArrayStorage->WriteArray($custom);
						}else{
							$contentprefvalue = "";
						}

						$_POST['content_layout'] = (!$_POST['content_layout'] || $_POST['content_layout'] == "content_content_template.php" ? "" : $_POST['content_layout']);
						
						//content_order + content_refer : are not added in the sql
						$sql -> db_Update($plugintable, "content_heading = '".$_POST['content_heading']."', content_subheading = '".$_POST['content_subheading']."', content_summary = '".$_POST['content_summary']."', content_text = '".$_POST['content_text']."', content_author = '".$author."', content_icon = '".$icon."', content_file = '".$totalattach."', content_image = '".$totalimages."', content_parent = '".$_POST['parent']."', content_comment = '".$_POST['content_comment']."', content_rate = '".$_POST['content_rate']."', content_pe = '".$_POST['content_pe']."', content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".$_POST['content_class']."', content_pref = '".$contentprefvalue."', content_score='".$_POST['content_score']."', content_meta='".$_POST['content_meta']."', content_layout='".$_POST['content_layout']."' WHERE content_id = '".$_POST['content_id']."' ");

						$e107cache->clear("comment.$plugintable.{$_POST['content_id']}");
						$e107cache->clear("$plugintable.content.{$_POST['content_id']}");
						if($mode == "admin"){
							js_location(e_SELF."?".e_QUERY.".cu");
						}elseif($mode == "contentmanager"){
							js_location(e_SELF."?u");
						}
		}


		function dbContentCreate($mode){
						global $pref, $qs, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache, $eArrayStorage;

						$_POST['content_heading']		= $tp -> toDB($_POST['content_heading']);
						$_POST['content_subheading']	= $tp -> toDB($_POST['content_subheading']);
						$_POST['content_summary']		= $tp -> toDB($_POST['content_summary']);
						$_POST['content_text']			= $tp -> toDB($_POST['content_text']);
						$_POST['parent']				= ($_POST['parent'] ? $_POST['parent'] : "");
						$_POST['content_class']			= ($_POST['content_class'] ? $_POST['content_class'] : "0");
						$_POST['content_meta']			= $tp -> toDB($_POST['content_meta']);

						if(USER){
							if(!($_POST['content_author_id'] == USERID && $_POST['content_author_name'] == USERNAME && $_POST['content_author_email'] == USEREMAIL) ){
								//$author = $_POST['content_author_id']."^".$_POST['content_author_name']."^".$_POST['content_author_email'];
								$author = "0^".$_POST['content_author_name']."^".$_POST['content_author_email'];
							}else{
								$author = USERID;
							}
						}else{
							$author = "0^".$_POST['content_author_name']."^".$_POST['content_author_email'];
						}

						if($_POST['ne_day'] != "none" && $_POST['ne_month'] != "none" && $_POST['ne_year'] != "none"){
							$starttime = mktime( 0, 0, 0, $_POST['ne_month'], $_POST['ne_day'], $_POST['ne_year']);
						}else{
							$starttime = time();
						}
						if($_POST['end_day'] != "none" && $_POST['end_month'] != "none" && $_POST['end_year'] != "none"){
							$endtime = mktime( 0, 0, 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);
						}else{
							$endtime = "0";
						}

						$mainparent						= $aa -> getMainParent($_POST['parent']);
						$content_pref					= $aa -> getContentPref($mainparent);
						$content_cat_icon_path_large	= $tp -> replaceConstants($content_pref["content_cat_icon_path_large_{$mainparent}"]);
						$content_cat_icon_path_small	= $tp -> replaceConstants($content_pref["content_cat_icon_path_small_{$mainparent}"]);
						$content_icon_path				= $tp -> replaceConstants($content_pref["content_icon_path_{$mainparent}"]);
						$content_image_path				= $tp -> replaceConstants($content_pref["content_image_path_{$mainparent}"]);
						$content_file_path				= $tp -> replaceConstants($content_pref["content_file_path_{$mainparent}"]);
						$content_tmppath_icon			= e_PLUGIN."content/images/icon/tmp/";
						$content_tmppath_file			= e_PLUGIN."content/images/file/tmp/";
						$content_tmppath_image			= e_PLUGIN."content/images/image/tmp/";

						//move icon to correct folder
						if($_POST['content_icon']){
							$icon = $_POST['content_icon'];							
							if(file_exists($content_tmppath_icon.$icon)){
								rename($content_tmppath_icon.$icon, $content_icon_path.$icon);
							}
						}

						$sumf = 0;
						$sumi = 0;
						foreach($_POST as $k => $v){
							if(preg_match("#^content_files#",$k)){
								$sumf = $sumf+1;
							}
							if(preg_match("#^content_images#",$k)){
								$sumi = $sumi+1;
							}
						}
						//move attachments to correct folder
						$totalattach = "";
						for($i=0;$i<$sumf;$i++){
							$attach{$i} = $_POST["content_files{$i}"];
							if(file_exists($content_tmppath_file.$attach{$i})){
								rename($content_tmppath_file.$attach{$i}, $content_file_path.$attach{$i});
							}
							$totalattach .= "[file]".$attach{$i};
						}
						//move images to correct folder
						$totalimages = "";
						for($i=0;$i<$sumi;$i++){
							$image{$i} = $_POST["content_images{$i}"];
							if(file_exists($content_tmppath_image.$image{$i})){
								rename($content_tmppath_image.$image{$i}, $content_image_path.$image{$i});
							}
							if(file_exists($content_tmppath_image."thumb_".$image{$i})){
								rename($content_tmppath_image."thumb_".$image{$i}, $content_image_path."thumb_".$image{$i});
							}
							$totalimages .= "[img]".$image{$i};
						}

						//custom additional data tags
						for($i=0;$i<$content_pref["content_admin_custom_number_{$mainparent}"];$i++){
							if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
								$keystring = $tp->toDB($_POST["content_custom_key_{$i}"]);
								$custom["content_custom_{$keystring}"] = $tp->toDB($_POST["content_custom_value_{$i}"]);
							}
						}
						//preset additional data tags
						$custom['content_custom_presettags'] = $_POST['content_custom_preset_key'];
						$contentprefvalue = $eArrayStorage->WriteArray($custom);

						if($mode == "submit"){
							$refer = ($content_pref["content_submit_directpost_{$mainparent}"] ? "" : "sa");
						}else{
							$refer = "";
						}

						$sql -> db_Insert($plugintable, "'0', '".$_POST['content_heading']."', '".$_POST['content_subheading']."', '".$_POST['content_summary']."', '".$_POST['content_text']."', '".$author."', '".$icon."', '".$totalattach."', '".$totalimages."', '".$_POST['parent']."', '".$_POST['content_comment']."', '".$_POST['content_rate']."', '".$_POST['content_pe']."', '".$refer."', '".$starttime."', '".$endtime."', '".$_POST['content_class']."', '".$contentprefvalue."', '0', '".$_POST['content_score']."', '".$_POST['content_meta']."', '".$_POST['content_layout']."' ");
						
						$e107cache->clear($plugintable);
						if($mode == "admin"){
							js_location(e_SELF."?".e_QUERY.".cc");
						}elseif($mode == "contentmanager"){
							js_location(e_SELF."?c");
						}elseif($mode == "submit"){
							if($content_pref["content_submit_directpost_{$mainparent}"]){
								js_location(e_SELF."?s");
							}else{
								js_location(e_SELF."?d");
							}							
						}
						
		}


		function dbCategoryUpdate($mode){
						global $pref, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache;

						$_POST['cat_heading']		= $tp -> toDB($_POST['cat_heading']);
						$_POST['cat_subheading']	= $tp -> toDB($_POST['cat_subheading']);
						$_POST['cat_text']			= $tp -> toDB($_POST['cat_text']);
						$_POST['parent']			= ($_POST['parent'] == "0" ? "0" : "0.".$_POST['parent']);
						$_POST['cat_class']			= ($_POST['cat_class'] ? $_POST['cat_class'] : "0");

						if($_POST['ne_day'] != "none" && $_POST['ne_month'] != "none" && $_POST['ne_year'] != "none"){
							$starttime = mktime( 0, 0, 0, $_POST['ne_month'], $_POST['ne_day'], $_POST['ne_year']);
						}else{
							$starttime = time();
						}
						if($_POST['end_day'] != "none" && $_POST['end_month'] != "none" && $_POST['end_year'] != "none"){
							$endtime = mktime( 0, 0, 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);
						}else{
							$endtime = "0";
						}

						$sql -> db_Update($plugintable, "content_heading = '".$_POST['cat_heading']."', content_subheading = '".$_POST['cat_subheading']."', content_summary = '', content_text = '".$_POST['cat_text']."', content_author = '".ADMINID."', content_icon = '".$_POST['cat_icon']."', content_image = '', content_parent = '".$_POST['parent']."', content_comment = '".$_POST['cat_comment']."', content_rate = '".$_POST['cat_rate']."', content_pe = '".$_POST['cat_pe']."', content_refer = '0', content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".$_POST['cat_class']."' WHERE content_id = '".$_POST['cat_id']."' ");
						//, content_pref = ''

						// check and insert default pref values if new main parent + create menu file
						if($_POST['parent'] == "0"){
							$content_pref = $aa -> getContentPref($_POST['cat_id']);
							$aa -> CreateParentMenu($_POST['cat_id']);
						}

						if($mode == "admin"){
							$e107cache->clear($plugintable);
							js_location(e_SELF."?".e_QUERY.".pu");
						}
		}

		function dbCategoryCreate($mode){
						global $pref, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache, $content_cat_icon_path_large, $content_cat_icon_path_small;

						$_POST['cat_heading']		= $tp -> toDB($_POST['cat_heading']);
						$_POST['cat_subheading']	= $tp -> toDB($_POST['cat_subheading']);
						$_POST['cat_text']			= $tp -> toDB($_POST['cat_text']);
						$_POST['cat_class']			= ($_POST['cat_class'] ? $_POST['cat_class'] : "0");
						$_POST['parent']			= ($_POST['parent'] == "0" ? "0" : "0.".$_POST['parent']);

						if($_POST['ne_day'] != "none" && $_POST['ne_month'] != "none" && $_POST['ne_year'] != "none"){
							$starttime = mktime( 0, 0, 0, $_POST['ne_month'], $_POST['ne_day'], $_POST['ne_year']);
						}else{
							$starttime = time();
						}
						if($_POST['end_day'] != "none" && $_POST['end_month'] != "none" && $_POST['end_year'] != "none"){
							$endtime = mktime( 0, 0, 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);
						}else{
							$endtime = "0";
						}

						$sql -> db_Insert($plugintable, "'0', '".$_POST['cat_heading']."', '".$_POST['cat_subheading']."', '', '".$_POST['cat_text']."', '".ADMINID."', '".$_POST["cat_icon"]."', '', '', '".$_POST['parent']."', '".$_POST['cat_comment']."', '".$_POST['cat_rate']."', '".$_POST['cat_pe']."', '', '".$starttime."', '".$endtime."', '".$_POST['cat_class']."', '', '0', '', '', '' ");

						// check and insert default pref values if new main parent + create menu file
						if($_POST['parent'] == "0"){
							$sql -> db_Select($plugintable, "content_id", "content_parent = '0' ORDER BY content_datestamp DESC LIMIT 1");
							list($parent_id) = $sql -> db_Fetch();
							$content_pref = $aa -> getContentPref($parent_id);
							$aa -> CreateParentMenu($parent_id);
						}

						if($mode == "admin"){
							$e107cache->clear($plugintable);
							js_location(e_SELF."?".e_QUERY.".pc");
						}
		}


		function dbAssignAdmins($mode, $id, $value){
						global $plugintable, $sql, $eArrayStorage;

						if($mode == "admin"){
							$sql -> db_Select($plugintable, "content_pref", "content_id = '".$id."' ");
							$row = $sql -> db_Fetch();

							//get current preferences
							$content_pref = $eArrayStorage->ReadArray($row['content_pref']);

							//assign new preferences
							if($value == "clear"){
								$content_pref["content_manager_allowed_{$id}"] = "";
							}else{
								$content_pref["content_manager_allowed_{$id}"] = $value;
							}
							
							//create new array of preferences
							$tmp = $eArrayStorage->WriteArray($content_pref);

							$sql -> db_Update($plugintable, "content_pref = '{$tmp}' WHERE content_id = '".$id."' ");
							$message = CONTENT_ADMIN_CAT_LAN_34;
							return $message;
						}else{
							return FALSE;
						}						
		}


		function dbDelete($mode, $cat, $del_id){
						global $plugintable, $sql, $_POST, $e107cache;

						if($mode == "admin"){
							if($cat == "cat"){
								if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
									$e107cache->clear($plugintable);
									$message = CONTENT_ADMIN_CAT_LAN_23;
									return $message;
								}
							}elseif($cat == "content"){
								if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
									$e107cache->clear($plugintable);
									$message = CONTENT_ADMIN_ITEM_LAN_3;
									return $message;
								}
							}

						}else{
							return FALSE;
						}						

		}

		
		function dbSetOrder($mode, $type, $order){
						global $plugintable, $sql, $aa, $qs, $_POST, $e107cache;
						//$mode		:	all, inc, dec
						//$type		:	cc (category order), ai (global all items), ci (items in category)
						//$order	:	posted values or id-currentorder

						if($mode == "all"){
							foreach ($order as $cid){
								//each order value in the db has two numbers (a-b) where a = category item order, and b = global item order
								//146.3.cat		:	category order
								//35.3.ci.1-0	:	category item order
								//35.3.ai.1-0	:	global item order
								
								$tmp		= explode(".", $cid);
								$old		= explode("-", $tmp[3]);
								$old[0]		= ($old[0] == "" ? "0" : $old[0]);
								$old[1]		= ($old[1] == "" ? "0" : $old[1]);								

								if($tmp[2] == "cat"){
									$sql->db_Update($plugintable, "content_order='".$tmp[1]."' WHERE content_id='".$tmp[0]."' " );

								}elseif($tmp[2] == "ci"){
									$sql->db_Update($plugintable, "content_order='".$tmp[1].".".$old[1]."' WHERE content_id='".$tmp[0]."' " );

								}elseif($tmp[2] == "ai"){
									$sql->db_Update($plugintable, "content_order='".$old[0].".".$tmp[1]."' WHERE content_id='".$tmp[0]."' " );
								}

								$message = CONTENT_ADMIN_ORDER_LAN_2;
							}

						}elseif($mode == "inc"){

							$tmp = explode("-", $order);
							if($type == "cc"){
								
								$mainparent		= $aa -> getMainParent($tmp[0]);
								$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
								$validparent	= implode(",", array_keys($array));
								$qry			= " content_id REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND content_order='".($tmp[1]-1)."' ";
								$sql->db_Update($plugintable, "content_order=content_order+1 WHERE ".$qry." " );
								$sql->db_Update($plugintable, "content_order=content_order-1 WHERE content_id='".$tmp[0]."' " );

							}elseif($type == "ci"){

								$sql->db_Update($plugintable, "content_order='".$tmp[1].".".$tmp[2]."' WHERE content_parent = '".$qs[2]."' AND SUBSTRING_INDEX(content_order, '.', 1) = '".($tmp[1]-1)."' " );
								$sql->db_Update($plugintable, "content_order='".($tmp[1]-1).".".$tmp[2]."' WHERE content_id='".$tmp[0]."' " );

							}elseif($type == "ai"){

								$array			= $aa -> getCategoryTree("", $qs[1], TRUE);
								$validparent	= implode(",", array_keys($array));
								$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND SUBSTRING_INDEX(content_order, '.', -1) = '".($tmp[2]-1)."' ";
								$sql->db_Update($plugintable, " content_order=content_order+0.1 WHERE ".$qry." " );
								$sql->db_Update($plugintable, "content_order='".$tmp[1].".".($tmp[2]-1)."' WHERE content_id='".$tmp[0]."' " );

							}
							$message = CONTENT_ADMIN_ORDER_LAN_0;

						}elseif($mode == "dec"){

							$tmp = explode("-", $order);
							if($type == "cc"){
								
								$mainparent		= $aa -> getMainParent($tmp[0]);
								$array			= $aa -> getCategoryTree("", $mainparent, TRUE);
								$validparent	= implode(",", array_keys($array));
								$qry			= " content_id REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND content_order='".($tmp[1]+1)."' ";
								$sql->db_Update($plugintable, "content_order=content_order-1 WHERE ".$qry." " );
								$sql->db_Update($plugintable, "content_order=content_order+1 WHERE content_id='".$tmp[0]."' " );

							}elseif($type == "ci"){

								$sql->db_Update($plugintable, "content_order='".$tmp[1].".".$tmp[2]."' WHERE content_parent = '".$qs[2]."' AND SUBSTRING_INDEX(content_order, '.', 1) = '".($tmp[1]+1)."' " );
								$sql->db_Update($plugintable, "content_order='".($tmp[1]+1).".".$tmp[2]."' WHERE content_id='".$tmp[0]."' " );

							}elseif($type == "ai"){

								$array			= $aa -> getCategoryTree("", $qs[1], TRUE);
								$validparent	= implode(",", array_keys($array));
								$qry			= " content_parent REGEXP '".$aa -> CONTENTREGEXP($validparent)."' AND SUBSTRING_INDEX(content_order, '.', -1) = '".($tmp[2]+1)."' ";
								$sql->db_Update($plugintable, "content_order=content_order-0.1 WHERE ".$qry." " );
								$sql->db_Update($plugintable, "content_order='".$tmp[1].".".($tmp[2]+1)."' WHERE content_id='".$tmp[0]."' " );

							}
							$message = CONTENT_ADMIN_ORDER_LAN_1;
						}
						$e107cache->clear($plugintable);
						
						return $message;

		}
}

?>