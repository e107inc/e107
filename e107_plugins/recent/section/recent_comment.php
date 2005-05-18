<?php

	global $sql;
	global $RECENT_MENU, $RECENT_MENU_START, $RECENT_MENU_END, $RECENT_PAGE_START, $RECENT_PAGE, $RECENT_PAGE_END;
	global $RECENT_ICON, $RECENT_DATE, $HEADING, $RECENT_AUTHOR, $CATEGORY, $RECENT_INFO;
	global $RECENT_DISPLAYSTYLE, $RECENT_CAPTION, $RECENT_STYLE_CAPTION, $RECENT_STYLE_BODY;

	$RECENT_CAPTION = $arr[0];
	$RECENT_DISPLAYSTYLE = ($arr[2] ? "" : "none");

	$bullet = $this -> getBullet($arr[6], $mode);

	$sql2 = new db;
	if(!$sql -> db_Select("comments", "*", "ORDER BY comment_datestamp DESC LIMIT 0, ".$arr[7]."", "mode=no_where")){
		$RECENT_DATA = RECENT_COMMENT_2;
	}else{		
		while($row = $sql -> db_Fetch()){

			if($arr[3]){
				$comment_author_id = substr($row['comment_author'] , 0, strpos($row['comment_author'] , "."));
				$comment_author_name = substr($row['comment_author'] , (strpos($row['comment_author'] , ".")+1));
				$AUTHOR = (USERID ? "<a href='".e_BASE."user.php?id.".$comment_author_id."'>".$comment_author_name."</a>" : $comment_author_name);
			}else{
				$AUTHOR = "";
			}

			$DATE = ($arr[5] ? $this -> getRecentDate($row['comment_datestamp'], $mode) : "");
			$ICON = $bullet;
			$INFO = "";

			if($row['comment_type'] == "0"){	// news
					$sql2 -> db_Select("news", "*", "news_id='".$row['comment_item_id']."' AND news_class REGEXP '".e_CLASS_REGEXP."' ");
					$row2 = $sql2 -> db_Fetch();
					$rowheading = $this -> parse_heading($row2['news_title'], $mode);
					$HEADING = "<a href='".e_BASE."comment.php?comment.news.".$row['comment_item_id']."' title='".$row2['news_title']."'>".$rowheading."</a>";
					$CATEGORY = ($arr[4] ? "<a href='".e_BASE."news.php'>".RECENT_COMMENT_3."</a>" : "");

			}elseif($row['comment_type'] == "1"){	//	article, review or content page

			}elseif($row['comment_type'] == "2"){	//	downloads
					$mp = MPREFIX;
					$qry = "SELECT download_name, {$mp}download_category.download_category_class, {$mp}download_category.download_category_id, {$mp}download_category.download_category_name FROM {$mp}download LEFT JOIN {$mp}download_category ON {$mp}download.download_category={$mp}download_category.download_category_id WHERE {$mp}download.download_id={$row['comment_item_id']} AND {$mp}download_category.download_category_class REGEXP '".e_CLASS_REGEXP."' ";
					$sql2->db_Select_gen($qry);
					$row2 = $sql2->db_Fetch();
					$rowheading = $this -> parse_heading($row2['download_name'], $mode);
					$HEADING = "<a href='".e_BASE."download.php?view.".$row['comment_item_id']."' title='".$row2['download_name']."'>".$tp -> toHTML($rowheading, "admin")."</a>";
					$CATEGORY = ($arr[4] ? "<a href='".e_BASE."download.php?list.".$row2['download_category_id']."'>".$row2['download_category_name']."</a>" : "");

			}elseif($row['comment_type'] == "3"){	//	faq
					$sql2 -> db_Select("faq", "faq_question", "faq_id='".$row['comment_item_id']."' ");
					$row2 = $sql2 -> db_Fetch();
					$rowheading = $this -> parse_heading($row2['faq_question'], $mode);
					$HEADING = "<a href='".e_PLUGIN."faq/faq.php?view.".$row2['comment_item_id']."' title='".$row2['faq_question']."'>".$tp -> toHTML($rowheading, "admin")."</a>";
					$CATEGORY = ($arr[4] ? "<a href='".e_PLUGIN."faq/faq.php'>".RECENT_COMMENT_4."</a>" : "");

			}elseif($row['comment_type'] == "4"){	//	poll comment
					$sql2 -> db_Select("poll", "*", "poll_id='".$row['comment_item_id']."' ");
					$row2 = $sql2 -> db_Fetch();
					$rowheading = $this -> parse_heading($row2['poll_title'], $mode);
					$HEADING = "<a href='".e_BASE."comment.php?comment.poll.".$row['comment_item_id']."' title='".$row2['poll_title']."'>".$tp -> toHTML($rowheading, "admin")."</a>";
					$CATEGORY = ($arr[4] ? RECENT_COMMENT_5 : "");

			}elseif($row['comment_type'] == "5"){	//	docs
					/*
					$str .= $bullet." <b><a href='http://e107.org/docs/main.php?$comment_item_id'>Doc $comment_item_id</a></b><br />";
					$str .= ($recent_pref['comments_cat'] ? $style_pre."".RECENT_31."<br />" : "").($recent_pref['comments_author'] ? $style_pre."".($comment_author_id == 0 ? $comment_author_name : "<a href='".e_BASE."user.php?id.".$comment_author_name."'>".$comment_author_name."</a>" )."<br />" : "").($recent_pref['comments_date'] ? $style_pre."".$datestamp."<br />" : "");
					*/

			}elseif($row['comment_type'] == "6"){	//	bugtracker
					$sql2 -> db_Select("bugtrack", "bugtrack_summary", "bugtrack_id='".$row['comment_item_id']."' ");
					$row2 = $sql2 -> db_Fetch();
					$rowheading = $this -> parse_heading($row2['bugtrack_summary'], $mode);
					$HEADING = "<a href='".e_PLUGIN."bugtracker/bugtracker.php?show.".$row['comment_item_id']."' title='".$row2['bugtrack_summary']."'>".$rowheading."</a>";
					$CATEGORY = ($arr[4] ? RECENT_COMMENT_7 : "");

			}elseif($row['comment_type'] == "pcontent"){	//	pcontent
					$sql2 -> db_Select("pcontent", "content_heading, content_parent, content_class", "content_id='".$row['comment_item_id']."' AND content_class REGEXP '".e_CLASS_REGEXP."' ");
					$row2 = $sql2 -> db_Fetch();
					$tmp = explode(".", $row2['content_parent']);
					$type_id = $tmp[0];
					$rowheading = $this -> parse_heading($row2['content_heading'], $mode);
					$HEADING = "<a href='".e_PLUGIN."content/content.php?type.".$type_id.".content.".$row['comment_item_id']."' title='".$row2['content_heading']."'>".$tp -> toHTML($rowheading)."</a>";
					$CATEGORY = ($arr[4] ? "<a href='".e_PLUGIN."content/content.php?type.".$type_id."'>".RECENT_COMMENT_8."</a>" : "");

			}else{

				$handle=opendir(e_PLUGIN);
				while(false !== ($file = readdir($handle))){
					if($file != "." && $file != ".." && is_dir(e_PLUGIN.$file)){
						$plugin_handle=opendir(e_PLUGIN.$file."/");
						while(false !== ($file2 = readdir($plugin_handle))){

							if ($file2 == "e_comment.php") {
								$nid = $row['comment_item_id'];
								include(e_PLUGIN.$file."/".$file2);
								if ($row['comment_type'] == $e_plug_table) {

									$sql2 -> db_Select("".$db_table."", "".$link_name."", "".$db_id." = '".$row['comment_item_id']."' ");
									$row2 = $sql2 -> db_Fetch();								
									$rowheading = $this -> parse_heading($row2[0], $mode);
									
									$HEADING = "<a href='".$reply_location."' title='".$row2[0]."'>".$tp -> toHTML($rowheading)."</a>";
									$CATEGORY = ($arr[4] ? $plugin_name : "");

									break 2;
								}
							}
						}
					}
				}
			}
			$RECENT_DATA[$mode][] = array( $ICON, $HEADING, $AUTHOR, $CATEGORY, $DATE, $INFO );
		}
	}


?>