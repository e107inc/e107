<?php


require_once("../../class2.php");
$lan_file = e_PLUGIN.'content/languages/'.e_LANGUAGE.'/lan_content.php';
include_once(file_exists($lan_file) ? $lan_file : e_PLUGIN.'content/languages/English/lan_content.php');
require_once(e_ADMIN."auth.php");
require_once(e_HANDLER."form_handler.php");
$rs = new form;
require_once(e_PLUGIN."content/handlers/content_class.php");
$aa = new content;
global $tp;

if(isset($_POST['convert_table'])){
	$plugintable = "pcontent";
	$text = "";
	$textcontent = "";
	$textreview = "";
	$textarticle = "";
	$textreviewcat = "";
	$textarticlecat = "";

	$countcontent = "0";
	$countreview = "0";
	$countarticle = "0";
	$countreviewcat = "0";
	$countarticlecat = "0";

	if(!is_object($sql)){ $sql = new db; }
	if(!is_object($sql2)){ $sql2 = new db; }
	if(!is_object($sql3)){ $sql3 = new db; }
	if(!is_object($sql4)){ $sql4 = new db; }
	if(!is_object($sql5)){ $sql5 = new db; }
	if(!is_object($sql6)){ $sql6 = new db; }
	if(!is_object($sql7)){ $sql7 = new db; }

	// ##### STAGE 1 : ANALYSE OLD CONTENT --------------------------------------------------------
					//analyse old content table
					$totaloldcontentrows = $sql -> db_Count("content");
					$totaloldrowscat_content = $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '1'");
					$totaloldrowscat_article = $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '6'");
					$totaloldrowscat_review = $sql -> db_Count("content", "(*)", "WHERE content_parent = '0' AND content_type = '10'");
					$totaloldrowsitem_review = $sql -> db_Count("content", "(*)", "WHERE content_type = '3' || content_type = '16'");
					$totaloldrowsitem_article = $sql -> db_Count("content", "(*)", "WHERE content_type = '0' || content_type = '15'");

					//content page:		$content_parent == "0" && $content_type == "1"
					//review category:	$content_parent == "0" && $content_type == "10"
					//article category:	$content_parent == "0" && $content_type == "6"
					//review:	$content_type == "3" || $content_type == "16"
					//article:	$content_type == "0" || $content_type == "15"


	// ##### STAGE 2 : INSERT MAIN PARENT FOR CONTENT ---------------------------------------------
	if($totaloldrowscat_content > "0"){
					//insert a content main parent cat, then insert all content pages into this main parent
					if(!$sql2 -> db_Select($plugintable, "content_heading", "content_heading = 'content' AND content_parent = '0' ")){
						$sql2 -> db_Insert($plugintable, "'0', 'content', '', '', '', '1', '', '', '', '0', '0', '0', '0', '', '".time()."', '0', '0', '', '' ");

						//check if row is present in the db (is it a valid insert)
						if(!$sql3 -> db_Select($plugintable, "content_id", "content_heading = 'content' ")){
							$content_mainparent = CONTENT_ADMIN_CONVERSION_LAN_45;
						}else{
							$content_mainparent = CONTENT_ADMIN_CONVERSION_LAN_0." ".CONTENT_ADMIN_CONVERSION_LAN_7."<br />";
							$mainparentcontent = "1";

							//select main content parent id
							$sql3 -> db_Select($plugintable, "content_id", "content_heading = 'content' AND content_parent = '0' ");
							list($content_main_id) = $sql3 -> db_Fetch();

							//insert default prefs into the main content cat
							unset($content_pref, $tmp);
							$content_pref = $aa -> ContentDefaultPrefs($content_main_id);
							$tmp = addslashes(serialize($content_pref));
							$sql4 -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$content_main_id' ");
							$content_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_0." ".CONTENT_ADMIN_CONVERSION_LAN_8."<br />";
						}
					}
	}else{
		$content_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_9." ".CONTENT_ADMIN_CONVERSION_LAN_0." ".CONTENT_ADMIN_CONVERSION_LAN_10."<br />";
	}
	

	// ##### STAGE 3 : INSERT MAIN PARENT FOR REVIEW ----------------------------------------------
	if($totaloldrowscat_review > "0"){
					//insert a review main parent cat, then insert all review cats into this main parent
					if(!$sql2 -> db_Select($plugintable, "content_heading", "content_heading = 'review' AND content_parent = '0' ")){
						$sql2 -> db_Insert($plugintable, "'0', 'review', '', '', '', '1', '', '', '', '0', '0', '0', '0', '', '".time()."', '0', '0', '', '' ");

						//check if row is present in the db (is it a valid insert)
						if(!$sql3 -> db_Select($plugintable, "content_id", "content_heading = 'review' ")){
							$review_mainparent = CONTENT_ADMIN_CONVERSION_LAN_45;
						}else{
							$review_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_7."<br />";
							$mainparentreview = "1";

							//select main review parent id
							$sql3 -> db_Select($plugintable, "content_id", "content_heading = 'review' AND content_parent = '0' ");
							list($review_main_id) = $sql3 -> db_Fetch();

							//insert default prefs into the main review cat
							unset($content_pref, $tmp);
							$content_pref = $aa -> ContentDefaultPrefs($review_main_id);
							$tmp = addslashes(serialize($content_pref));
							$sql4 -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$review_main_id' ");
							$review_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_8."<br />";
						}
					}
	}else{
		$review_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_9." ".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_10."<br />";
	}
	

	// ##### STAGE 4 : INSERT MAIN PARENT FOR ARTICLE ---------------------------------------------
	if($totaloldrowscat_article > "0"){
					//insert a article main parent cat, then insert all article cats into this main parent
					if(!$sql2 -> db_Select($plugintable, "content_heading", "content_heading = 'article' AND content_parent = '0' ")){
						$sql2 -> db_Insert($plugintable, "'0', 'article', '', '', '', '1', '', '', '', '0', '0', '0', '0', '', '".time()."', '0', '0', '', '' ");

						//check if row is present in the db (is it a valid insert)
						if(!$sql3 -> db_Select($plugintable, "content_id", "content_heading = 'article' ")){
							$article_mainparent = CONTENT_ADMIN_CONVERSION_LAN_45;
						}else{
							$article_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_7."<br />";
							$mainparentarticle = "1";

							//select main article parent id
							$sql3 -> db_Select($plugintable, "content_id", "content_heading = 'article' AND content_parent = '0' ");
							list($article_main_id) = $sql3 -> db_Fetch();

							//insert default prefs into the main article cat
							unset($content_pref, $tmp);
							$content_pref = $aa -> ContentDefaultPrefs($article_main_id);
							$tmp = addslashes(serialize($content_pref));
							$sql4 -> db_Update($plugintable, "content_pref='$tmp' WHERE content_id='$article_main_id' ");
							$article_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_8."<br />";
						}
					}
	}else{
		$article_mainparent .= CONTENT_ADMIN_CONVERSION_LAN_9." ".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_10."<br />";
	}


	// ##### STAGE 5 : INSERT CONTENT -------------------------------------------------------------
	if(!$sql -> db_Select("content", "*", "content_parent = '0' AND content_type = '1' ORDER BY content_id " )){
		$content_present = "0";
	}else{
		$count = 1;
		$content_present = "1";
		while($row = $sql -> db_Fetch()){
		extract($row);
					//select main content parent id
					$sql5 -> db_Select($plugintable, "content_id", "content_heading = 'content' AND content_parent = '0' ");
					list($content_main_id2) = $sql5 -> db_Fetch();

					//summary can contain link to image in e107_images/link_icons/".$summary."
					$newcontent_heading = $tp -> toDB($content_heading);
					$newcontent_subheading = ($content_subheading ? $tp -> toDB($content_subheading) : "");
					$newcontent_summary = ($content_summary ? $tp -> toDB($content_summary) : "");
					$newcontent_text = $tp -> toDB($content_content);
					$newcontent_author = (is_numeric($content_author) ? $content_author : "0^".$content_author);
					$newcontent_icon = "";
					$newcontent_attach = "";
					$newcontent_images = "";
					$newcontent_parent = $content_main_id2.".".$content_main_id2;
					$newcontent_comment = $content_comment;
					$newcontent_rate = "0";
					$newcontent_pe = $content_pe_icon;
					$newcontent_refer = "0";
					$newcontent_starttime = $content_datestamp;
					$newcontent_endtime = "0";
					$newcontent_class = $content_class;
					$newcontent_pref = "";

					$sql6 -> db_Insert($plugintable, "'0', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."' ");

					if(!$sql6 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
						$bug_content_insert[] = $content_id." ".$content_heading;
					}else{
						$valid_content_insert[] = $content_id." ".$content_heading;
						$countcontent = $countcontent + 1;
					}
					$count = $count + 1;
		}
	}


	// ##### STAGE 6 : INSERT REVIEW CATEGORY -----------------------------------------------------
	if(!$sql -> db_Select("content", "*", "content_parent = '0' AND content_type = '10' ORDER BY content_id " )){
		$review_cat_present = "0";
	}else{
		$count = 1;
		$review_cat_present = "1";
		while($row = $sql -> db_Fetch()){
		extract($row);
					//select main review parent id
					$sql5 -> db_Select($plugintable, "content_id", "content_heading = 'review' AND content_parent = '0' ");
					list($review_main_id2) = $sql5 -> db_Fetch();

					//summary can contain link to image in e107_images/link_icons/".$summary."
					$newcontent_heading = $tp -> toDB($content_heading);
					$newcontent_subheading = ($content_subheading ? $tp -> toDB($content_subheading) : "");
					$newcontent_summary = ($content_summary ? $tp -> toDB($content_summary) : "");
					$newcontent_text = $tp -> toDB($content_content);
					$newcontent_author = (is_numeric($content_author) ? $content_author : "0^".$content_author);
					$newcontent_icon = "";
					$newcontent_attach = "";
					$newcontent_images = "";
					$newcontent_parent = "0.".$review_main_id2;										//make each review cat a first level subcat of the main review parent
					$newcontent_comment = $content_comment;
					$newcontent_rate = "0";
					$newcontent_pe = $content_pe_icon;
					$newcontent_refer = "0";
					$newcontent_starttime = $content_datestamp;
					$newcontent_endtime = "0";
					$newcontent_class = $content_class;
					$newcontent_pref = "";

					$sql6 -> db_Insert($plugintable, "'0', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."' ");

					if(!$sql6 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
						$bug_review_cat_insert[] = $content_id." ".$content_heading;
					}else{
						$valid_review_cat_insert[] = $content_id." ".$content_heading;
						$countreviewcat = $countreviewcat + 1;
					}
					$count = $count + 1;
		}
	}


	// ##### STAGE 7 : INSERT ARTICLE CATEGORY ----------------------------------------------------
	if(!$sql -> db_Select("content", "*", "content_parent = '0' AND content_type = '6' ORDER BY content_id " )){
		$article_cat_present = "0";
	}else{
		$count = 1;
		$article_cat_present = "1";
		while($row = $sql -> db_Fetch()){
		extract($row);
					//select main article parent id
					$sql5 -> db_Select($plugintable, "content_id", "content_heading = 'article' AND content_parent = '0' ");
					list($article_main_id2) = $sql5 -> db_Fetch();

					//summary can contain link to image in e107_images/link_icons/".$summary."
					$newcontent_heading = $tp -> toDB($content_heading);
					$newcontent_subheading = ($content_subheading ? $tp -> toDB($content_subheading) : "");
					$newcontent_summary = ($content_summary ? $tp -> toDB($content_summary) : "");
					$newcontent_text = $tp -> toDB($content_content);
					$newcontent_author = (is_numeric($content_author) ? $content_author : "0^".$content_author);
					$newcontent_icon = "";
					$newcontent_attach = "";
					$newcontent_images = "";
					$newcontent_parent = "0.".$article_main_id2;										//make each article cat a first level subcat of the main article parent
					$newcontent_comment = $content_comment;
					$newcontent_rate = "0";
					$newcontent_pe = $content_pe_icon;
					$newcontent_refer = "0";
					$newcontent_starttime = $content_datestamp;
					$newcontent_endtime = "0";
					$newcontent_class = $content_class;
					$newcontent_pref = "";

					$sql6 -> db_Insert($plugintable, "'0', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."' ");

					if(!$sql6 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
						$bug_article_cat_insert[] = $content_id." ".$content_heading;
					}else{
						$valid_article_cat_insert[] = $content_id." ".$content_heading;
						$countarticlecat = $countarticlecat + 1;
					}
					$count = $count + 1;
		}
	}


	// ##### STAGE 8 : INSERT REVIEWS -------------------------------------------------------------
	if(!$sql7 -> db_Select("content", "*", "content_type = '3' || content_type = '16' ORDER BY content_id " )){
		$review_present = "0";
	}else{
		$count = 1;
		$review_present = "1";
		while($row = $sql7 -> db_Fetch()){
		extract($row);					
					//select main review parent id
					$sql2 -> db_Select($plugintable, "content_id", "content_heading = 'review' AND content_parent = '0' ");
					list($review_main_id) = $sql2 -> db_Fetch();

					//review is in main review cat
					if($content_parent == "0"){
						$newcontent_parent = $review_main_id.".".$review_main_id;											//place each review in correct cat

					//review is in review subcat
					}else{
						//select old review cat heading
						if(!$sql3 -> db_Select("content", "content_id, content_heading", "content_id = '".$content_parent."' ")){
							$bug_article_oldcat[] = $content_id." ".$content_heading;
							$newcontent_parent = $review_main_id.".".$review_main_id;
						}else{
							list($old_review_cat_id, $old_review_cat_heading) = $sql3 -> db_Fetch();

							//select new review cat id from the cat with the old_review_cat_heading
							if(!$sql4 -> db_Select($plugintable, "content_id", "content_heading = '".$old_review_cat_heading."' AND content_parent = '0.".$review_main_id."' ")){
								$bug_review_newcat[] = $content_id." ".$content_heading;
								$newcontent_parent = $review_main_id.".".$review_main_id;
							}else{
								list($new_review_cat_id) = $sql4 -> db_Fetch();
								$newcontent_parent = $review_main_id.".".$review_main_id.".".$new_review_cat_id;					//place each review in correct cat
							}
						}
					}

					if (strstr($content_content, "{EMAILPRINT}")) {
						$content_content = str_replace("{EMAILPRINT}", "", $content_content);
					}

					$newcontent_heading = $tp -> toDB($content_heading);
					$newcontent_subheading = ($content_subheading ? $tp -> toDB($content_subheading) : "");
					$newcontent_summary = ($content_summary ? $tp -> toDB($content_summary) : "");
					$newcontent_text = $tp -> toDB($content_content);
					$newcontent_author = (is_numeric($content_author) ? $content_author : "0^".$content_author);
					$newcontent_icon = "";
					$newcontent_attach = "";
					$newcontent_images = "";
					$newcontent_comment = $content_comment;
					$newcontent_rate = "0";
					$newcontent_pe = $content_pe_icon;
					$newcontent_refer = ($content_type == "16" ? "sa" : "");
					$newcontent_starttime = $content_datestamp;
					$newcontent_endtime = "0";
					$newcontent_class = $content_class;
					
					$custom["content_custom_score"] = ($content_review_score != "none" && $content_review_score ? $content_review_score : "");
					$contentreviewprefvalue = addslashes(serialize($custom));
					$newcontent_pref = $contentreviewprefvalue;

					$sql5 -> db_Insert($plugintable, "'0', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."' ");

					if(!$sql5 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
						$bug_review_insert[] = $content_id." ".$content_heading;
					}else{
						$valid_review_insert[] = $content_id." ".$content_heading;
						$countreview = $countreview + 1;
					}
					$count = $count + 1;
		}
	}


	// ##### STAGE 9 : INSERT ARTICLES ------------------------------------------------------------
	if(!$sql7 -> db_Select("content", "*", "content_type = '0' || content_type = '15' ORDER BY content_id " )){
		$article_present = "0";
	}else{
		$count = 1;
		$article_present = "1";
		while($row = $sql7 -> db_Fetch()){
		extract($row);					
					//select main article parent id
					$sql2 -> db_Select($plugintable, "content_id", "content_heading = 'article' AND content_parent = '0' ");
					list($article_main_id) = $sql2 -> db_Fetch();

					//article is in main article cat
					if($content_parent == "0"){
						$newcontent_parent = $article_main_id.".".$article_main_id;											//place each article in correct cat

					//article is in article subcat
					}else{
						//select old article cat heading
						if(!$sql3 -> db_Select("content", "content_id, content_heading", "content_id = '".$content_parent."' ")){
							$bug_article_oldcat[] = $content_id." ".$content_heading;
							$newcontent_parent = $article_main_id.".".$article_main_id;
						}else{
							list($old_article_cat_id, $old_article_cat_heading) = $sql3 -> db_Fetch();

							//select new article cat id from the cat with the old_article_cat_heading
							if(!$sql4 -> db_Select($plugintable, "content_id", "content_heading = '".$old_article_cat_heading."' AND content_parent = '0.".$article_main_id."' ")){
								$bug_article_newcat[] = $content_id." ".$content_heading;
								$newcontent_parent = $article_main_id.".".$article_main_id;
							}else{
								list($new_article_cat_id) = $sql4 -> db_Fetch();
								$newcontent_parent = $article_main_id.".".$article_main_id.".".$new_article_cat_id;					//place each article in correct cat
							}
						}
					}

					if (strstr($content_content, "{EMAILPRINT}")) {
						$content_content = str_replace("{EMAILPRINT}", "", $content_content);
					}

					$newcontent_heading = $tp -> toDB($content_heading);
					$newcontent_subheading = ($content_subheading ? $tp -> toDB($content_subheading) : "");
					$newcontent_summary = ($content_summary ? $tp -> toDB($content_summary) : "");
					$newcontent_text = $tp -> toDB($content_content);
					$newcontent_author = (is_numeric($content_author) ? $content_author : "0^".$content_author);
					$newcontent_icon = "";
					$newcontent_attach = "";
					$newcontent_images = "";				
					$newcontent_comment = $content_comment;
					$newcontent_rate = "0";
					$newcontent_pe = $content_pe_icon;
					$newcontent_refer = ($content_type == "15" ? "sa" : "");
					$newcontent_starttime = $content_datestamp;
					$newcontent_endtime = "0";
					$newcontent_class = $content_class;
					$newcontent_pref = "";

					if(!is_object($sql5)){ $sql5 = new db; }

					$sql5 -> db_Insert($plugintable, "'0', '".$newcontent_heading."', '".$newcontent_subheading."', '".$newcontent_summary."', '".$newcontent_text."', '".$newcontent_author."', '".$newcontent_icon."', '".$newcontent_attach."', '".$newcontent_images."', '".$newcontent_parent."', '".$newcontent_comment."', '".$newcontent_rate."', '".$newcontent_pe."', '".$newcontent_refer."', '".$newcontent_starttime."', '".$newcontent_endtime."', '".$newcontent_class."', '".$newcontent_pref."', '".$count."' ");

					if(!$sql5 -> db_Select($plugintable, "content_id, content_heading", "content_heading = '".$newcontent_heading."' ")){
						$bug_article_insert[] = $content_id." ".$content_heading;
					}else{
						$valid_article_insert[] = $content_id." ".$content_heading;
						$countarticle = $countarticle + 1;
					}
					$count = $count + 1;
		}
	}




	$text = "
	<table class='fborder' style='width:95%; padding:3px;' >

	<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_11."</td></tr>
	<tr><td class='forumheader3' colspan='2'>
		".CONTENT_ADMIN_CONVERSION_LAN_12.": ".$totaloldcontentrows."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_13.": ".(count($valid_article_insert) + count($valid_article_cat_insert) + count($valid_review_insert) + count($valid_review_cat_insert) + count($valid_content_insert))."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_14.": ".(count($bug_article_oldcat) + count($bug_article_newcat) + count($bug_review_oldcat) + count($bug_review_newcat))."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_15.": ".(count($bug_article_insert) + count($bug_article_cat_insert) + count($bug_review_insert) + count($bug_review_cat_insert) + count($bug_content_insert))."<br />
	</td></tr>

	<tr><td class='forumheader3' colspan='2'><br /></td></tr>

	<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_16."</td></tr>
	<tr><td class='forumheader3' colspan='2'>
		".CONTENT_ADMIN_CONVERSION_LAN_17.": ".$totaloldcontentrows."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_0." ".CONTENT_ADMIN_CONVERSION_LAN_6.": ".$totaloldrowscat_content."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_4.": ".$totaloldrowscat_review."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_6.": ".$totaloldrowsitem_review."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_4.": ".$totaloldrowscat_article."<br />
		".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_6.": ".$totaloldrowsitem_article."<br />";

		if($totaloldcontentrows > ($totaloldrowscat_content + $totaloldrowscat_review + $totaloldrowsitem_review + $totaloldrowscat_article + $totaloldrowsitem_article) ){
			$text .= CONTENT_ADMIN_CONVERSION_LAN_18.": ".($totaloldcontentrows - ($totaloldrowscat_content + $totaloldrowscat_review + $totaloldrowsitem_review + $totaloldrowscat_article + $totaloldrowsitem_article))."<br />";
		}else{
			$text .= CONTENT_ADMIN_CONVERSION_LAN_19."<br />";
		}

	$text .= "
	</td>
	</tr>

	<tr><td class='forumheader3' colspan='2'><br /></td></tr>";

	//main parents
	$text .= "
	<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_20."</td></tr>
	<tr><td class='forumheader3' colspan='2'>".($mainparentcontent == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".$content_mainparent."</td></tr>
	<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_21."</td></tr>
	<tr><td class='forumheader3' colspan='2'>".($mainparentreview == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".$review_mainparent."</td></tr>
	<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_22."</td></tr>
	<tr><td class='forumheader3' colspan='2'>".($mainparentarticle == "1" ? CONTENT_ICON_OK : CONTENT_ICON_ERROR)." ".$article_mainparent."</td></tr>

	<tr><td class='forumheader3' colspan='2'><br /></td></tr>";

	//content
	if($content_present != "1"){
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_24."</td></tr>";
	}else{
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_25."</td></tr>";
		if(count($valid_content_insert) > 0 ){
			for($i=0;$i<count($valid_content_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_OK." ".$valid_content_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_0." ".CONTENT_ADMIN_CONVERSION_LAN_5." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td></tr>";
			}
		}
		if(count($bug_content_insert) > 0 ){
			for($i=0;$i<count($bug_content_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_ERROR." ".$bug_content_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_23."</td></tr>";
			}
		}
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
		<tr><td class='forumheader3' colspan='2'>
		".$totaloldrowscat_content." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
		".count($valid_content_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
		".count($bug_content_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
		</td></tr>";
	}

	$text .= "<tr><td class='forumheader3' colspan='2'><br /></td></tr>";

	//review category
	if($review_cat_present != "1"){
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_34."</td></tr>";
	}else{
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_35."</td></tr>";
		if(count($valid_review_cat_insert) > 0 ){
			for($i=0;$i<count($valid_review_cat_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_OK." ".$valid_review_cat_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_3." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td></tr>";
			}
		}
		if(count($bug_review_cat_insert) > 0 ){
			for($i=0;$i<count($bug_review_cat_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_ERROR." ".$bug_review_cat_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_23."</td></tr>";
			}
		}
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_3." ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
		<tr><td class='forumheader3' colspan='2'>
		".$totaloldrowscat_review." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
		".count($valid_review_cat_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
		".count($bug_review_cat_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
		</td></tr>";
	}

	$text .= "<tr><td class='forumheader3' colspan='2'><br /></td></tr>";

	//review pages
	if($review_present != "1"){
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_36."</td></tr>";
	}else{
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_37."</td></tr>";
		if(count($valid_review_insert) > 0 ){
			for($i=0;$i<count($valid_review_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_OK." ".$valid_review_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_6." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td></tr>";
			}
		}
		if(count($bug_review_oldcat) > 0 ){
			for($i=0;$i<count($bug_review_oldcat);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_WARNING." ".$bug_review_oldcat[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_32."</td></tr>";
			}
		}
		if(count($bug_review_newcat) > 0 ){
			for($i=0;$i<count($bug_review_newcat);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_WARNING." ".$bug_review_newcat[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_33."</td></tr>";
			}
		}
		if(count($bug_review_insert) > 0 ){
			for($i=0;$i<count($bug_review_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_ERROR." ".$bug_review_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_23."</td></tr>";
			}
		}
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_1." ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
		<tr><td class='forumheader3' colspan='2'>
		".$totaloldrowsitem_review." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
		".count($valid_review_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
		".count($bug_review_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
		".count($bug_review_oldcat)." ".CONTENT_ADMIN_CONVERSION_LAN_31.": ".CONTENT_ADMIN_CONVERSION_LAN_32."<br />
		".count($bug_review_newcat)." ".CONTENT_ADMIN_CONVERSION_LAN_31.": ".CONTENT_ADMIN_CONVERSION_LAN_33."<br />
		</td></tr>";
	}

	$text .= "<tr><td class='forumheader3' colspan='2'><br /></td></tr>";

	//article category
	if($article_cat_present != "1"){
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_38."</td></tr>";
	}else{
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_39."</td></tr>";
		if(count($valid_article_cat_insert) > 0 ){
			for($i=0;$i<count($valid_article_cat_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_OK." ".$valid_article_cat_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_3." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td></tr>";
			}
		}
		if(count($bug_article_cat_insert) > 0 ){
			for($i=0;$i<count($bug_article_cat_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_ERROR." ".$bug_article_cat_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_23."</td></tr>";
			}
		}
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_3." ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
		<tr><td class='forumheader3' colspan='2'>
		".$totaloldrowscat_article." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
		".count($valid_article_cat_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
		".count($bug_article_cat_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
		</td></tr>";
	}

	$text .= "<tr><td class='forumheader3' colspan='2'><br /></td></tr>";

	//article pages
	if($article_present != "1"){
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_40."</td></tr>";
	}else{
		$text .= "<tr><td class='fcaption' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_41."</td></tr>";
		if(count($valid_article_insert) > 0 ){
			for($i=0;$i<count($valid_article_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_OK." ".$valid_article_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_5." ".CONTENT_ADMIN_CONVERSION_LAN_26."</td></tr>";
			}
		}
		if(count($bug_article_oldcat) > 0 ){
			for($i=0;$i<count($bug_article_oldcat);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_WARNING." ".$bug_article_oldcat[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_32."</td></tr>";
				
			}
		}
		if(count($bug_article_newcat) > 0 ){
			for($i=0;$i<count($bug_article_newcat);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_WARNING." ".$bug_article_newcat[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_33."</td></tr>";
			}
		}
		if(count($bug_article_insert) > 0 ){
			for($i=0;$i<count($bug_article_insert);$i++){
				$text .= "<tr><td class='forumheader3'>".CONTENT_ICON_ERROR." ".$bug_article_insert[$i]."</td><td class='forumheader3' style='white-space:nowrap;'>".CONTENT_ADMIN_CONVERSION_LAN_23."</td></tr>";
			}
		}
		$text .= "
		<tr><td class='forumheader' colspan='2'>".CONTENT_ADMIN_CONVERSION_LAN_2." ".CONTENT_ADMIN_CONVERSION_LAN_27."</td></tr>
		<tr><td class='forumheader3' colspan='2'>
		".$totaloldrowsitem_article." ".CONTENT_ADMIN_CONVERSION_LAN_28."<br />
		".count($valid_article_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_29."<br />
		".count($bug_article_insert)." ".CONTENT_ADMIN_CONVERSION_LAN_30."<br />
		".count($bug_article_oldcat)." ".CONTENT_ADMIN_CONVERSION_LAN_31.": ".CONTENT_ADMIN_CONVERSION_LAN_32."<br />
		".count($bug_article_newcat)." ".CONTENT_ADMIN_CONVERSION_LAN_31.": ".CONTENT_ADMIN_CONVERSION_LAN_33."<br />
		</td></tr>";
	}

	$text .= "
	</table>";

	$caption = CONTENT_ADMIN_CONVERSION_LAN_42;
	$ns -> tablerender($caption, $text);

}






$totalnewcontent = $sql -> db_Count($plugintable);

$text = "
<div style='text-align:center'>
".$rs -> form_open("post", e_SELF, "dataform")."
<table class='fborder' style='width:95%;'>";

if($totalnewcontent != "0"){
	$text .= "<tr><td class='forumheader3' style='text-align:center;'>".CONTENT_ADMIN_CONVERSION_LAN_44."</td></tr>";
}

$text .= "
<tr><td class='forumheader3' style='text-align:center;'>".$rs -> form_button("submit", "convert_table", "convert table")."</td></tr>

</table>
</form>
</div>";

$caption = CONTENT_ADMIN_CONVERSION_LAN_43;
$ns -> tablerender($caption, $text);

require_once(e_ADMIN."footer.php");

?>