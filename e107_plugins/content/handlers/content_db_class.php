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
|		$Revision: 1.7 $
|		$Date: 2005-02-10 20:11:34 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

//$plugintable = "pcontent";		//name of the table used in this plugin

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

class contentdb{

			function dbContentUpdate($mode){
						global $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache;
						global $type, $type_id, $action, $sub_action, $id;

						$_POST['content_heading'] = $tp -> toDB($_POST['content_heading']);
						$_POST['content_subheading'] = $tp -> toDB($_POST['content_subheading']);
						$_POST['content_text'] = $tp -> toDB($_POST['content_text']);
						$_POST['parent'] = ($_POST['parent'] ? $_POST['parent'] : "0");

						if(USER){
							if(!($_POST['content_author_id'] == USERID && $_POST['content_author_name'] == USERNAME && $_POST['content_author_email'] == USEREMAIL) ){
									$author = $_POST['content_author_id']."^".$_POST['content_author_name']."^".$_POST['content_author_email'];
							}else{
								$author = USERID;
							}
						}else{
							$author = "0^".$_POST['content_author_name']."^".$_POST['content_author_email'];
						}

						$content_pref = $aa -> getContentPref($type_id);
						$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
						$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
						$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
						$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
						$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

						if($_POST['uploadtype1'] == "icon"){
								$_FILES['file_userfile'] = $_FILES['file_userfile1'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pathicon = $content_icon_path;
								$uploadedicon = file_upload($pathicon);
								$newpid = $_POST['content_id'];
								if($_POST['content_icon'] && !$uploadedicon){
									$icon = $_POST['content_icon'];
								} else {
									//require_once(e_HANDLER."resize_handler.php");
									//resize_image($pathicon.$uploadedicon[0]['name'], $pathicon.$uploadedicon[0]['name'], 250, "copy");
									$fileorgicon = $uploadedicon[0]['name'];
									$fileext2icon = substr(strrchr($fileorgicon, "."), 0);
									if($fileorgicon){
										$icon = $newpid."_contenticon".$fileext2icon;
										rename($pathicon.$fileorgicon , $pathicon.$icon);
									} else {
										$icon = "";
									}
								}
						}
						if($_POST['uploadtype2'] == "file"){
								$_FILES['file_userfile'] = $_FILES['file_userfile2'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pathattach = $content_file_path;
								$uploadedattach = file_upload($pathattach);
								$newpid = $_POST['content_id'];
								$sumf=0;
								foreach($_POST as $k => $v){
									if(preg_match("#^content_files#",$k)){
										$sumf = $sumf+1;
									}
								}
								if(is_array($uploadedattach)){
									$sumf = $sumf + count($uploadedattach);
								}
								for($i=0;$i<$sumf;$i++){
									$n = $i+1;
									if($_POST["content_files{$i}"] && !$uploadedattach[$i]['name']){
										$attach{$i} = $_POST["content_files{$i}"];
										$totalattach .= "[file]".$attach{$i};
									} else {
										$fileorgattach[$i] = $uploadedattach[$i]['name'];
										$fileext2attach[$i] = substr(strrchr($fileorgattach[$i], "."), 0);
										if($fileorgattach[$i]){
											$attach{$i} = $newpid."_contentfile_".$n."".$fileext2attach[$i]."";
											rename($pathattach.$fileorgattach[$i] , $pathattach.$attach{$i});
											$totalattach .= "[file]".$attach{$i};
										} else {
											$attach{$i} = "";
											$totalattach .= "";
										}
									}
								}
						}
						if($_POST['uploadtype3'] == "image"){
								$_FILES['file_userfile'] = $_FILES['file_userfile3'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pathimage = $content_image_path;
								$uploadedimage = file_upload($pathimage);
								$newpid = $_POST['content_id'];
								$sumi=0;
								foreach($_POST as $k => $v){
									if(preg_match("#^content_images#",$k)){
										$sumi = $sumi+1;
									}
								}
								if(is_array($uploadedimage)){
									$sumi = $sumi + count($uploadedimage);
								}
								for($i=0;$i<$sumi;$i++){
									$n = $i+1;
									if($_POST["content_images{$i}"] && !$uploadedimage[$i]['name']){
										$images{$i} = $_POST["content_images{$i}"];
										$totalimages .= "[img]".$images{$i};
									} else {
										$fileorgimage[$i] = $uploadedimage[$i]['name'];
										$fileext2image[$i] = substr(strrchr($fileorgimage[$i], "."), 0);

										if($fileorgimage[$i]){
											$images{$i} = $newpid."_contentimage_".$n.$fileext2image[$i];
											rename($pathimage.$fileorgimage[$i] , $pathimage.$images{$i});
											$totalimages .= "[img]".$images{$i};
										} else {
											$images{$i} = "";
											$totalimages .= "";
										}
									}
								}
						}
						$contentrefer = ($_POST['content_refer'] && $_POST['content_refer'] != "sa" ? $_POST['content_refer'] : "");

						if($_POST['ne_day'] != "none" && $_POST['ne_month'] != "none" && $_POST['ne_year'] != "none"){
							$starttime = mktime( 0, 0, 0, $_POST['ne_month'], $_POST['ne_day'], $_POST['ne_year']);
						}else{
							$starttime = ($_POST['update_datestamp'] ? time() : $_POST['content_datestamp'] );
						}
						if($_POST['end_day'] != "none" && $_POST['end_month'] != "none" && $_POST['end_year'] != "none"){
							$endtime = mktime( 0, 0, 0, $_POST['end_month'], $_POST['end_day'], $_POST['end_year']);
						}else{
							$endtime = "0";
						}

						$custom["content_custom_score"] = ($_POST['content_score'] != "none" && $_POST['content_score'] ? $_POST['content_score'] : "");
						$custom["content_custom_meta"] = ($_POST['content_meta'] ? $_POST['content_meta'] : "");

						for($i=0;$i<$content_pref["content_admin_custom_number_{$type_id}"];$i++){
							if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
								$keystring = $_POST["content_custom_key_{$i}"];
								$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
							}
						}
						$contentprefvalue = addslashes(serialize($custom));

						$sql -> db_Update($plugintable, "content_heading = '".$_POST['content_heading']."', content_subheading = '".$_POST['content_subheading']."', content_summary = '".$_POST['content_summary']."', content_text = '".$_POST['content_text']."', content_author = '".$author."', content_icon = '".$icon."', content_file = '".$totalattach."', content_image = '".$totalimages."', content_parent = '".$_POST['parent']."', content_comment = '".$_POST['content_comment']."', content_rate = '".$_POST['content_rate']."', content_pe = '".$_POST['content_pe']."', content_refer = '".$contentrefer."', content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".$_POST['content_class']."', content_pref = '".$contentprefvalue."' WHERE content_id = '".$_POST['content_id']."' ");

						if($mode == "admin"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?".$type.".".$type_id.".create.edit.".$_POST['content_id'].".cu"); exit;
						}elseif($mode == "contentmanager"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?u"); exit;
						}
		}


		function dbContentCreate($mode){
						global $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache;
						global $type, $type_id, $action, $sub_action, $id;

						$_POST['content_heading'] = $tp -> toDB($_POST['content_heading']);
						$_POST['content_subheading'] = $tp -> toDB($_POST['content_subheading']);
						$_POST['content_text'] = $tp -> toDB($_POST['content_text']);
						$_POST['parent'] = ($_POST['parent'] ? $_POST['parent'] : "0");

						if(USER){
							if(!($_POST['content_author_id'] == USERID && $_POST['content_author_name'] == USERNAME && $_POST['content_author_email'] == USEREMAIL) ){
								$author = $_POST['content_author_id']."^".$_POST['content_author_name']."^".$_POST['content_author_email'];
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

						$content_pref = $aa -> getContentPref($type_id);
						$content_cat_icon_path_large = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_large_{$type_id}"]);
						$content_cat_icon_path_small = $aa -> parseContentPathVars($content_pref["content_cat_icon_path_small_{$type_id}"]);
						$content_icon_path = $aa -> parseContentPathVars($content_pref["content_icon_path_{$type_id}"]);
						$content_image_path = $aa -> parseContentPathVars($content_pref["content_image_path_{$type_id}"]);
						$content_file_path = $aa -> parseContentPathVars($content_pref["content_file_path_{$type_id}"]);

						$sql -> db_select($plugintable, "MAX(content_id) as aid", "content_id!='0' ");
						list($aid) = $sql -> db_Fetch();
						$newpid = $aid+1;

						if($_POST['uploadtype1'] == "icon"){
								$_FILES['file_userfile'] = $_FILES['file_userfile1'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pathicon = $content_icon_path;
								$uploadedicon = file_upload($pathicon);
								if($_POST['content_icon'] && !$uploadedicon){
									$icon = $_POST['content_icon'];
								} else {
									//require_once(e_HANDLER."resize_handler.php");
									//resize_image($pathicon.$uploadedicon[0]['name'], $pathicon.$uploadedicon[0]['name'], 250, "copy");
									$fileorgicon = $uploadedicon[0]['name'];
									$fileext2icon = substr(strrchr($fileorgicon, "."), 0);
									if($fileorgicon){
										$icon = $newpid."_contenticon".$fileext2icon;
										rename($pathicon.$fileorgicon , $pathicon.$icon);
									} else {
										$icon = "";
									}
								}
						}
						if($_POST['uploadtype2'] == "file"){
								$_FILES['file_userfile'] = $_FILES['file_userfile2'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pathattach = $content_file_path;
								$uploadedattach = file_upload($pathattach);
								$sumf=0;
								foreach($_POST as $k => $v){
									if(preg_match("#^content_files#",$k)){
										$sumf = $sumf+1;
									}
								}
								if(is_array($uploadedattach)){
									$sumf = $sumf + count($uploadedattach);
								}						
								for($i=0;$i<$sumf;$i++){
									$n = $i+1;
									if($_POST["content_files{$i}"] && !$uploadedattach[$i]['name']){
										$attach{$i} = $_POST["content_files{$i}"];
										$totalattach .= "[file]".$attach{$i};
									} else {
										$attachorgattach[$i] = $uploadedattach[$i]['name'];
										$attachext2attach[$i] = substr(strrchr($attachorgattach[$i], "."), 0);
										if($attachorgattach[$i]){
											$attach{$i} = $newpid."_contentfile_".$n."".$attachext2attach[$i]."";
											rename($pathattach.$attachorgattach[$i] , $pathattach.$attach{$i});
											$totalattach .= "[file]".$attach{$i};
										} else {
											$attach{$i} = "";
											$totalattach .= "";
										}
									}
								}
						}
						if($_POST['uploadtype3'] == "image"){
								$_FILES['file_userfile'] = $_FILES['file_userfile3'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pathimage = $content_image_path;
								$uploadedimage = file_upload($pathimage);
								$sumi=0;
								foreach($_POST as $k => $v){
									if(preg_match("#^content_images#",$k)){
										$sumi = $sumi+1;
									}
								}
								if(is_array($uploadedimage)){
									$sumi = $sumi + count($uploadedimage);
								}						
								for($i=0;$i<$sumi;$i++){
									$n = $i+1;
									if($_POST["content_images{$i}"] && !$uploadedimage[$i]['name']){
										$image{$i} = $_POST["content_images{$i}"];
										$totalimages .= "[img]".$image{$i};
									} else {
										$fileorgimage[$i] = $uploadedimage[$i]['name'];
										$fileext2image[$i] = substr(strrchr($fileorgimage[$i], "."), 0);
										if($fileorgimage[$i]){
											$image{$i} = $newpid."_contentimage_".$n."".$fileext2image[$i]."";
											rename($pathimage.$fileorgimage[$i] , $pathimage.$image{$i});
											$totalimages .= "[img]".$image{$i};
										} else {
											$image{$i} = "";
											$totalimages .= "";
										}
									}
								}
						}

						$custom["content_custom_score"] = ($_POST['content_score'] != "none" && $_POST['content_score'] ? $_POST['content_score'] : "");
						$custom["content_custom_meta"] = ($_POST['content_meta'] ? $_POST['content_meta'] : "");

						for($i=0;$i<$content_pref["content_admin_custom_number_{$type_id}"];$i++){
							if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
								$keystring = $_POST["content_custom_key_{$i}"];
								$custom["content_custom_{$keystring}"] = $_POST["content_custom_value_{$i}"];
							}
						}
						$contentprefvalue = addslashes(serialize($custom));

						if($mode == "submit"){
							$refer = ($content_pref["content_submit_directpost_{$type_id}"] ? "" : "sa");
						}else{
							$refer = "";
						}

						$sql -> db_Insert($plugintable, "'0', '".$_POST['content_heading']."', '".$_POST['content_subheading']."', '".$_POST['content_summary']."', '".$_POST['content_text']."', '".$author."', '".$icon."', '".$totalattach."', '".$totalimages."', '".$_POST['parent']."', '".$_POST['content_comment']."', '".$_POST['content_rate']."', '".$_POST['content_pe']."', '".$refer."', '".$starttime."', '".$endtime."', '".$_POST['content_class']."', '".$contentprefvalue."', '0' ");
						
						if($mode == "admin"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?".$type.".".$type_id.".create.cc"); exit;

						}elseif($mode == "contentmanager"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?c"); exit;

						}elseif($mode == "submit"){
							$e107cache->clear($plugintable);
							if($content_pref["content_submit_directpost_{$type_id}"]){
								header("location:".e_SELF."?s"); exit;
							}else{
								header("location:".e_SELF."?d"); exit;
							}							
						}
		}


		function dbCategoryUpdate($mode){
						global $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache;
						global $type, $type_id, $action, $sub_action, $id, $id2;

						$_POST['cat_heading'] = $tp -> toDB($_POST['cat_heading']);
						$_POST['cat_subheading'] = $tp -> toDB($_POST['cat_subheading']);
						$_POST['cat_text'] = $tp -> toDB($_POST['cat_text']);
						$_POST['parent'] = ($_POST['parent'] == "none" ? "0" : $_POST['parent']);

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

						$sql -> db_Update($plugintable, "content_heading = '".$_POST['cat_heading']."', content_subheading = '".$_POST['cat_subheading']."', content_summary = '', content_text = '".$_POST['cat_text']."', content_author = '".ADMINID."', content_icon = '".$_POST['cat_icon']."', content_image = '', content_parent = '".$_POST['parent']."', content_comment = '".$_POST['cat_comment']."', content_rate = '".$_POST['cat_rate']."', content_pe = '".$_POST['cat_pe']."', content_refer = '0', content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".$_POST['cat_class']."', content_pref = '' WHERE content_id = '".$_POST['cat_id']."' ");

						if($mode == "admin"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?".$type.".".$type_id.".".$action.".edit.".$_POST['cat_id'].".pu"); exit;
						}
		}


		function dbCategoryCreate($mode){
						global $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache;
						global $type, $type_id, $action, $sub_action, $id, $id2;

						$_POST['cat_heading'] = $tp -> toDB($_POST['cat_heading']);
						$_POST['cat_subheading'] = $tp -> toDB($_POST['cat_subheading']);
						$_POST['cat_text'] = $tp -> toDB($_POST['cat_text']);
						$_POST['parent'] = ($_POST['parent'] == "none" ? "0" : $_POST['parent']);

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

						$sql -> db_Insert($plugintable, "'0', '".$_POST['cat_heading']."', '".$_POST['cat_subheading']."', '', '".$_POST['cat_text']."', '".ADMINID."', '".$_POST['cat_icon']."', '', '', '".$_POST['parent']."', '".$_POST['cat_comment']."', '".$_POST['cat_rate']."', '".$_POST['cat_pe']."', '', '".$starttime."', '".$endtime."', '".$_POST['cat_class']."', '', '0' ");

						// check and insert default pref values if new main parent + create menu file
						if($_POST['parent'] == "0"){
							$sql -> db_Select($plugintable, "content_id", "content_parent = '0' ORDER BY content_datestamp DESC LIMIT 1");
							list($parent_id) = $sql -> db_Fetch();
							$content_pref = $aa -> getContentPref($parent_id);
							$aa -> CreateParentMenu($parent_id);
						}

						if($mode == "admin"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?".$type.".".$type_id.".".$action.".".$sub_action.".pc"); exit;
						}
		}


		function dbAssignAdmins($mode){
						global $plugintable, $sql, $_POST;

						if($mode == "admin"){
							$sql -> db_Update($plugintable, "content_pref = '".$_POST['class_id']."' WHERE content_id = '".$_POST['cat_id']."' ");
							$message = CONTENT_ADMIN_CAT_LAN_34;
							return $message;
						}else{
							return FALSE;
						}						
		}


		function dbDelete($mode, $cat, $del_id){
						global $plugintable, $sql, $_POST;

						if($mode == "admin"){
							if($cat == "cat"){
								if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
									$message = CONTENT_ADMIN_CAT_LAN_23;
									return $message;
								}
							}elseif($cat == "content"){
								if($sql -> db_Delete($plugintable, "content_id='$del_id' ")){
									$e107cache->clear("content");
									$message = CONTENT_ADMIN_ITEM_LAN_3;
									return $message;
								}
							}

						}else{
							return FALSE;
						}						

		}

		
		function dbSetOrder($mode, $id){
						global $plugintable, $sql, $_POST, $type_id, $sub_action;

						if($mode == "inc"){
							$qs = explode("-", $id);
							$ctype = $qs[0];
							$cid = $qs[1];
							$corder = $qs[2];
							$corderitem = $qs[3];

							if($ctype == "cc"){				//category order
								$query = ($type_id == $cid ? "content_parent = '0' && content_id = '".$type_id."'" : "LEFT(content_parent, ".(strlen($type_id)+2).") = '0.".$type_id."'" );
								$sql->db_Update($plugintable, "content_order=content_order+1 WHERE ".$query." AND content_order='".($corder-1)."' " );
								$sql->db_Update($plugintable, "content_order=content_order-1 WHERE content_id='".$cid."' " );
							
							}elseif($ctype == "ci"){		//order of items in category
								$cat = str_replace("-", ".", $sub_action);
								$sql->db_Update($plugintable, "content_order='".$corder.".".$corderitem."' WHERE content_parent = '".$cat."' AND content_order='".($corder-1).".".$corderitem."' " );
								$sql->db_Update($plugintable, "content_order='".($corder-1).".".$corderitem."' WHERE content_parent = '".$cat."' AND content_id='".$cid."' " );

							}elseif($ctype == "ai"){		//global order of items
								$cat = str_replace("-", ".", $sub_action);
								$sql->db_Update($plugintable, "content_order=content_order+1 WHERE content_order='".($corder-1)."' " );
								$sql->db_Update($plugintable, "content_order=content_order-1 WHERE content_id='".$cid."' " );
							
							}
							$message = CONTENT_ADMIN_ORDER_LAN_0;
							
						}elseif($mode == "dec"){
							$qs = explode("-", $id);
							$ctype = $qs[0];
							$cid = $qs[1];
							$corder = $qs[2];
							$corderitem = $qs[3];

							if($ctype == "cc"){				//category order
								$query = ($type_id == $cid ? "content_parent = '0' && content_id = '".$type_id."'" : "LEFT(content_parent, ".(strlen($type_id)+2).") = '0.".$type_id."'" );
								$sql->db_Update($plugintable, "content_order=content_order-1 WHERE ".$query." AND content_order='".($corder+1)."' " );
								$sql->db_Update($plugintable, "content_order=content_order+1 WHERE content_id='".$cid."' " );
							
							}elseif($ctype == "ci"){		//order of items in category
								$cat = str_replace("-", ".", $sub_action);
								$sql->db_Update($plugintable, "content_order='".$corder.".".$corderitem."' WHERE content_parent = '".$cat."' AND content_order='".($corder+1).".".$corderitem."' " );
								$sql->db_Update($plugintable, "content_order='".($corder+1).".".$corderitem."' WHERE content_parent = '".$cat."' AND content_id='".$cid."' " );

							}elseif($ctype == "ai"){		//global order of items
								$cat = str_replace("-", ".", $sub_action);
								$sql->db_Update($plugintable, "content_order=content_order+1 WHERE content_order='".($corder-1)."' " );
								$sql->db_Update($plugintable, "content_order=content_order-1 WHERE content_id='".$cid."' " );
							
							}
							$message = CONTENT_ADMIN_ORDER_LAN_1;
							
						}elseif($mode == "all"){

							foreach ($_POST['order'] as $cid){
								
								$tmp = explode(".", $cid);
								$iid = $tmp[0];
								$neworder = $tmp[1];
								$style = $tmp[2];
								$tmp1 = explode("-", $tmp[3]);
								$oldordercat = $tmp1[0];
								$oldorderitem = $tmp1[1];

								if($style == "cat"){
									$sql->db_Update($plugintable, "content_order='".$tmp[1]."' WHERE content_id='".$tmp[0]."' " );

								}elseif($style == "catitem"){
									$oldorderitem = ($oldorderitem == "" ? "0" : $oldorderitem);
									$sql->db_Update($plugintable, "content_order='".$neworder.".".$oldorderitem."' WHERE content_id='".$iid."' " );

								}elseif($style == "allitem"){
									$sql->db_Update($plugintable, "content_order='".$oldordercat.".".$neworder."' WHERE content_id='".$iid."' " );
								}

								$message = CONTENT_ADMIN_ORDER_LAN_2;
							}

						}
						return $message;

		}
}

?>