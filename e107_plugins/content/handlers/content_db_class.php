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
|		$Revision: 1.25 $
|		$Date: 2005-06-14 08:34:02 $
|		$Author: lisa_ $
+---------------------------------------------------------------+
*/

$plugindir		= e_PLUGIN."content/";
$plugintable	= "pcontent";		//name of the table used in this plugin (never remove this, as it's being used throughout the plugin !!)
$datequery		= " AND (content_datestamp=0 || content_datestamp < ".time().") AND (content_enddate=0 || content_enddate>".time().") ";

if (!defined('ADMIN_WIDTH')) { define("ADMIN_WIDTH", "width:98%;"); }

class contentdb{

			function dbContentUpdate($mode){
						global $pref, $qs, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache, $eArrayStorage;

						$_POST['content_heading']		= $tp -> toDB($_POST['content_heading']);
						$_POST['content_subheading']	= $tp -> toDB($_POST['content_subheading']);
						$_POST['content_summary']		= $tp -> toDB($_POST['content_summary']);
						$_POST['content_text']			= $tp -> toDB($_POST['content_text']);
						$_POST['parent']				= ($_POST['parent'] ? $_POST['parent'] : "0");
						$_POST['content_class']			= ($_POST['content_class'] ? $_POST['content_class'] : "0");

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
									$fileorgicon = $uploadedicon[0]['name'];
									$fileext2icon = substr(strrchr($fileorgicon, "."), 0);
									$fileorgiconname = substr($fileorgicon, 0, -(strlen($fileext2icon)) );
									
									if($fileorgicon){
										$icon = $newpid."_".$fileorgiconname."".$fileext2icon;
										rename($pathicon.$fileorgicon , $pathicon.$icon);
										require_once(e_HANDLER."resize_handler.php");
										resize_image($pathicon.$icon, $pathicon.$icon, '100', "nocopy");
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
										$fileorgattachname[$i] = substr($fileorgattach[$i], 0, -(strlen($fileext2attach[$i])) );

										if($fileorgattach[$i]){
											$attach{$i} = $newpid."_".$n."_".$fileorgattachname[$i]."".$fileext2attach[$i]."";
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
										$fileorgimagename[$i] = substr($fileorgimage[$i], 0, -(strlen($fileext2image[$i])) );

										if($fileorgimage[$i]){
											$images{$i} = $newpid."_".$n."_".$fileorgimagename[$i]."".$fileext2image[$i];
											rename($pathimage.$fileorgimage[$i] , $pathimage.$images{$i});
											require_once(e_HANDLER."resize_handler.php");
											resize_image($pathimage.$images{$i}, $pathimage.$images{$i}, '500', "nocopy");
											resize_image($pathimage.$images{$i}, $pathimage.$images{$i}, '100', "copy");

											$totalimages .= "[img]".$images{$i};
										} else {
											$images{$i} = "";
											$totalimages .= "";
										}
									}
								}
						}
						$contentrefer = ($_POST['content_refer'] && $_POST['content_refer'] != "sa" ? $_POST['content_refer'] : "");

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

						$custom["content_custom_score"]		= ($_POST['content_score'] != "none" && $_POST['content_score'] ? $_POST['content_score'] : "");
						$custom["content_custom_meta"]		= ($_POST['content_meta'] ? $tp->toDB($_POST['content_meta']) : "");
						$custom["content_custom_template"]	= ($_POST['content_template'] && $_POST['content_template'] != "none" ? $_POST['content_template'] : "");

						//custom additional data tags
						for($i=0;$i<$content_pref["content_admin_custom_number_{$mainparent}"];$i++){
							if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
								$keystring = $tp->toDB($_POST["content_custom_key_{$i}"]);
								$custom["content_custom_{$keystring}"] = $tp->toDB($_POST["content_custom_value_{$i}"]);
							}
						}
						//preset additional data tags
						if(is_array($_POST['content_custom_preset_key'])){
							for($i=0;$i<count($_POST['content_custom_preset_key']);$i++){
								if(isset($_POST['content_custom_preset_value'][$i]) && $_POST['content_custom_preset_value'][$i] != ""){
									$keystring = $tp->toDB($_POST['content_custom_preset_key'][$i]);
									$custom["content_custom_preset_{$keystring}"] = $tp->toDB($_POST['content_custom_preset_value'][$i]);
								}
							}
						}

						$contentprefvalue = $eArrayStorage->WriteArray($custom);

						$sql -> db_Update($plugintable, "content_heading = '".$_POST['content_heading']."', content_subheading = '".$_POST['content_subheading']."', content_summary = '".$_POST['content_summary']."', content_text = '".$_POST['content_text']."', content_author = '".$author."', content_icon = '".$icon."', content_file = '".$totalattach."', content_image = '".$totalimages."', content_parent = '".$_POST['parent']."', content_comment = '".$_POST['content_comment']."', content_rate = '".$_POST['content_rate']."', content_pe = '".$_POST['content_pe']."', content_refer = '".$contentrefer."', content_datestamp = '".$starttime."', content_enddate = '".$endtime."', content_class = '".$_POST['content_class']."', content_pref = '".$contentprefvalue."' WHERE content_id = '".$_POST['content_id']."' ");

						$e107cache->clear($plugintable);
						if($mode == "admin"){
							header("location:".e_SELF."?".e_QUERY.".cu"); exit;
						}elseif($mode == "contentmanager"){
							header("location:".e_SELF."?u"); exit;
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

						$sql -> db_select($plugintable, "MAX(content_id) as aid", "content_id!='0' ");
						list($aid) = $sql -> db_Fetch();
						$newpid = $aid+1;

						if($_POST['uploadtype1'] == "icon"){
								$_FILES['file_userfile'] = $_FILES['file_userfile1'];
								$pref['upload_storagetype'] = "1";
								require_once(e_HANDLER."upload_handler.php");
								$pref['upload_storagetype'] = "1";
								$pathicon = $content_icon_path;
								$uploadedicon = file_upload($pathicon);
								if($_POST['content_icon'] && !$uploadedicon){
									$icon = $_POST['content_icon'];
								} else {
									$fileorgicon = $uploadedicon[0]['name'];
									$fileext2icon = substr(strrchr($fileorgicon, "."), 0);
									$fileorgiconname = substr($fileorgicon, 0, -(strlen($fileext2icon)) );

									if($fileorgicon){
										$icon = $newpid."_".$fileorgiconname."".$fileext2icon;
										rename($pathicon.$fileorgicon , $pathicon.$icon);
										require_once(e_HANDLER."resize_handler.php");
										resize_image($pathicon.$icon, $pathicon.$icon, '100', "nocopy");
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
										$attachorgname[$i] = substr($attachorgattach[$i], 0, -(strlen($attachext2attach[$i])) );
										if($attachorgattach[$i]){
											$attach{$i} = $newpid."_".$n."_".$attachorgname[$i]."".$attachext2attach[$i]."";
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
										$images{$i} = $_POST["content_images{$i}"];
										$totalimages .= "[img]".$images{$i};
									} else {
										$fileorgimage[$i] = $uploadedimage[$i]['name'];
										$fileext2image[$i] = substr(strrchr($fileorgimage[$i], "."), 0);
										$fileorgimagename[$i] = substr($fileorgimage[$i], 0, -(strlen($fileext2image[$i])) );

										if($fileorgimage[$i]){
											$images{$i} = $newpid."_".$n."_".$fileorgimagename[$i]."".$fileext2image[$i];
											rename($pathimage.$fileorgimage[$i] , $pathimage.$images{$i});
											require_once(e_HANDLER."resize_handler.php");
											resize_image($pathimage.$images{$i}, $pathimage.$images{$i}, '500', "nocopy");
											resize_image($pathimage.$images{$i}, $pathimage.$images{$i}, '100', "copy");

											$totalimages .= "[img]".$images{$i};
										} else {
											$images{$i} = "";
											$totalimages .= "";
										}
									}
								}
						}

						$custom["content_custom_score"]		= ($_POST['content_score'] != "none" && $_POST['content_score'] ? $_POST['content_score'] : "");
						$custom["content_custom_meta"]		= ($_POST['content_meta'] ? $tp->toDB($_POST['content_meta']) : "");
						$custom["content_custom_template"]	= ($_POST['content_template'] && $_POST['content_template'] != "none" ? $_POST['content_template'] : "");

						//custom additional data tags
						for($i=0;$i<$content_pref["content_admin_custom_number_{$mainparent}"];$i++){
							if(isset($_POST["content_custom_key_{$i}"]) && isset($_POST["content_custom_value_{$i}"]) && $_POST["content_custom_value_{$i}"] != ""){
								$keystring = $tp->toDB($_POST["content_custom_key_{$i}"]);
								$custom["content_custom_{$keystring}"] = $tp->toDB($_POST["content_custom_value_{$i}"]);
							}
						}
						//preset additional data tags
						if(is_array($_POST['content_custom_preset_key'])){
							for($i=0;$i<count($_POST['content_custom_preset_key']);$i++){
								if(isset($_POST['content_custom_preset_value'][$i]) && $_POST['content_custom_preset_value'][$i] != ""){
									$keystring = $tp->toDB($_POST['content_custom_preset_key'][$i]);
									$custom["content_custom_preset_{$keystring}"] = $tp -> toDB($_POST['content_custom_preset_value'][$i]);
								}
							}
						}
						$contentprefvalue = $eArrayStorage->WriteArray($custom);

						if($mode == "submit"){
							$refer = ($content_pref["content_submit_directpost_{$mainparent}"] ? "" : "sa");
						}else{
							$refer = "";
						}

						$sql -> db_Insert($plugintable, "'0', '".$_POST['content_heading']."', '".$_POST['content_subheading']."', '".$_POST['content_summary']."', '".$_POST['content_text']."', '".$author."', '".$icon."', '".$totalattach."', '".$totalimages."', '".$_POST['parent']."', '".$_POST['content_comment']."', '".$_POST['content_rate']."', '".$_POST['content_pe']."', '".$refer."', '".$starttime."', '".$endtime."', '".$_POST['content_class']."', '".$contentprefvalue."', '0' ");
						
						$e107cache->clear($plugintable);
						if($mode == "admin"){
							header("location:".e_SELF."?".e_QUERY.".cc"); exit;
						}elseif($mode == "contentmanager"){
							header("location:".e_SELF."?c"); exit;
						}elseif($mode == "submit"){
							if($content_pref["content_submit_directpost_{$mainparent}"]){
								header("location:".e_SELF."?s"); exit;
							}else{
								header("location:".e_SELF."?d"); exit;
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
							header("location:".e_SELF."?".e_QUERY.".pu"); exit;
						}
		}


		function dbCategoryCreate($mode){
						global $pref, $sql, $ns, $rs, $aa, $tp, $plugintable, $e107cache, $content_cat_icon_path_large;

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

						
						$pref['upload_storagetype'] = "1";
						require_once(e_HANDLER."upload_handler.php");
						//$pref['upload_storagetype'] = "1";
						$pathicon = $content_cat_icon_path_large;
						$uploadedicon = file_upload($pathicon);
						if($_POST['cat_icon'] && !$uploadedicon){
							$icon = $_POST['cat_icon'];
						} elseif($uploadedicon) {
							$fileorgicon = $uploadedicon[0]['name'];
							$fileext2icon = substr(strrchr($fileorgicon, "."), 0);
							$fileorgiconname = substr($fileorgicon, 0, -(strlen($fileext2icon)) );

							if($fileorgicon){
								//$icon = $newpid."_".$fileorgiconname."".$fileext2icon;
								$icon = $fileorgicon;
								//rename($pathicon.$fileorgicon , $pathicon.$icon);
								//require_once(e_HANDLER."resize_handler.php");
								//resize_image($pathicon.$icon, $pathicon.$icon, '100', "nocopy");
							} else {
								$icon = "";
							}
						}else{
							$icon = "";
						}
						

						$sql -> db_Insert($plugintable, "'0', '".$_POST['cat_heading']."', '".$_POST['cat_subheading']."', '', '".$_POST['cat_text']."', '".ADMINID."', '".$icon."', '', '', '".$_POST['parent']."', '".$_POST['cat_comment']."', '".$_POST['cat_rate']."', '".$_POST['cat_pe']."', '', '".$starttime."', '".$endtime."', '".$_POST['cat_class']."', '', '0' ");

						// check and insert default pref values if new main parent + create menu file
						if($_POST['parent'] == "0"){
							$sql -> db_Select($plugintable, "content_id", "content_parent = '0' ORDER BY content_datestamp DESC LIMIT 1");
							list($parent_id) = $sql -> db_Fetch();
							$content_pref = $aa -> getContentPref($parent_id);
							$aa -> CreateParentMenu($parent_id);
						}

						if($mode == "admin"){
							$e107cache->clear($plugintable);
							header("location:".e_SELF."?".e_QUERY.".pc"); exit;
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

		
		function dbSetOrder($mode, $type, $order){
						global $plugintable, $sql, $aa, $qs, $_POST;
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
						
						return $message;

		}
}

?>