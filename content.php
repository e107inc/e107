<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/admin/review.php
|
|	©Steve Dunstan 2001-2002
|	http://e107.org
|	jalist@e107.org
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
require_once("class2.php");
if(e_QUERY){
	$tmp = explode(".", e_QUERY);
	$action = $tmp[0];
	$sub_action = $tmp[1];
	$id = $tmp[2];
	unset($tmp);
}else{	
	require_once(HEADERF);
	$text = "<a href='".e_SELF."?article'>".LAN_59." ".LAN_57."</a>\n<br />\n<a href='".e_SELF."?review'>".LAN_59." ".LAN_58."</a>";
	$ns -> tablerender(LAN_60, $text);
	require_once(FOOTERF);
	exit;
}
$query="";
if($action == "content"){
	$sub_action=intval($sub_action);
	$query = "content_id='".$sub_action."' ";
	$page = LAN_60." /";
	}
if($action == "article"){
	if(is_numeric($sub_action)){
		$query = "content_id='".$sub_action."' ";
		$page = LAN_1." /";
	}elseif($sub_action == "cat" ){
		if($id == "0"){
			$page = LAN_57." / ".LAN_61;
		}else{
			$query = "content_id='".$id."' ";
			$page = LAN_1." / ".LAN_3." /";
		}
	}else{		
		$page = LAN_50;
	}
}
if($action == "review"){
	if(is_numeric($sub_action)){
		$page = LAN_2." /";
		$query = "content_id='".$sub_action."' ";
	}elseif($sub_action == "cat"){
		$page = LAN_2." / ".LAN_3." /";
		$query = "content_id='".$id."' ";
	}else{		
		$page = LAN_35;
	}
}
if($query){
//	echo $query; exit;
if($sql -> db_Select("content", "*", $query)){ 
	$row = $sql -> db_Fetch(); extract($row);
	define("e_PAGETITLE", $page." ".$content_heading);
	}
}else{
	define("e_PAGETITLE", $page);
}

$highlight_search = FALSE;
if(IsSet($_POST['highlight_search'])){
	$highlight_search = TRUE;
}
require_once(HEADERF);


require_once(e_HANDLER."emailprint_class.php");
$ep = new emailprint;
/*
$ep = "<div style='text-align:right'>
<a href='email.php?article.".$sub_action."'><img src='".e_IMAGE."generic/friend.gif' style='border:0' alt='email to someone' /></a>
<a href='print.php?content.".$sub_action."'><img src='".e_IMAGE."generic/printer.gif' style='border:0' alt='printer friendly' /></a>
</div>";
*/


require_once(e_HANDLER."comment_class.php");
$cobj = new comment;
require_once(e_HANDLER."rate_class.php");
$rater = new rater;
if(IsSet($_POST['commentsubmit'])){
	$tmp = explode(".", e_QUERY);

	if(!$sql -> db_Select("content", "content_comment", "content_id='$sub_action' ")){
		header("location:".e_BASE."index.php");
		exit;
	}else{
		$row = $sql -> db_Fetch();
		if($row[0] && (ANON===TRUE || USER===TRUE)){
			$cobj -> enter_comment($_POST['author_name'], $_POST['comment'], "content", $sub_action, $pid, $_POST['subject']);
			clear_cache("comment.content.{$sub_action}");
		}
	}
}
// content page -------------------------------------------------------------------------------------------------------------------------------------------------------------------
if($action == "content"){

	if(!$sql -> db_Select("content", "*", "content_id=$sub_action AND content_type=1")){
		header("location: ".e_BASE."index.php");
		exit;
	}
	$row = $sql -> db_Fetch(); extract($row);

	if(!check_class($content_class)){
		$ns->tablerender(LAN_52, "<div style='text-align:center'>".LAN_54."</div>");
		require_once(FOOTERF);
		exit;
	}


	if($cache = retrieve_cache("content.$sub_action")){
		echo $aj -> formtparev($cache);
	}else{
		ob_start();		
		$textemailprint = $ep -> render_emailprint("content",$sub_action);
		if(strstr($content_content, "{EMAILPRINT}")){
			$content_content = str_replace("{EMAILPRINT}", $textemailprint, $content_content);
			$epflag = TRUE;
		}elseif($content_pe_icon){
			$content_content = $content_content."<br /><br />".$textemailprint;
		}
		$text = ($content_parent ? $aj -> tpa($content_content, "nobreak", "admin", $highlight_search) : $aj -> tpa($content_content, "off", "admin", $highlight_search));
		
		if($info = preg_split("#\[section=(.*?)\]#",$text,-1,PREG_SPLIT_DELIM_CAPTURE))
		{
			for($sec=0; $sec < count($info); $sec+=2 )
			{
				if($sec == 0)
				{
					$caption = $aj -> tpa($content_subheading, "off", "admin");
				} else 
				{
					$caption = $aj -> tpa($info[$sec-1], "off", "admin");
				}
				$ns -> tablerender($caption, $info[$sec]);
			}
		} else 
		{
			$caption = $aj -> tpa($content_subheading, "off", "admin");
			$ns -> tablerender($caption, $text);
		}
		
		if($pref['cachestatus']){
			$cache = $aj -> formtpa(ob_get_contents(), "admin");
			set_cache("content.$sub_action", $cache);
		}
	}

	if($content_comment){
		if($cache = retrieve_cache("comment.content.$sub_action")){
			echo $aj -> formtparev($cache);
		}else{
			ob_start();
			unset($text);
					if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$sub_action' AND comment_type='1' AND comment_pid='0' ORDER BY comment_datestamp")){
					$width = 0;
					while($row = $sql -> db_Fetch()){
					if($pref['nested_comments']){
						$text = $cobj -> render_comment($row, "content" , "comment", $sub_action, $width, $content_heading);			
						$ns -> tablerender(LAN_5, $text);	
						}else{
							$text .= $cobj -> render_comment($row, "content" , "comment", $sub_action, $width, $content_heading);	
						}
				}
				if(!$pref['nested_comments']){$ns -> tablerender(LAN_5, $text);	}
				if($pref['cachestatus']){
					$cache = $aj -> formtpa(ob_get_contents(), "admin");
					set_cache("comment.content.$sub_action", $cache);
				}
			}
		}
		if(ADMIN && getperms("B") && $comment_total){
			echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?content.$sub_action'>".LAN_29."</a></div><br />";
		}
		$cobj -> form_comment("comment", "content", $sub_action, $content_heading);
	}
}
	

// ##### Review List -----------------------------------------------------------------------------------------------------------------------------------------------------------

if($action == "review"){

	if(is_numeric($sub_action)){
		$cachestr = ($id ? "review.item.$sub_action.$id" : "review.item.$sub_action");
		if($cache = retrieve_cache($cachestr)){
			echo $aj -> formtparev($cache);
		}else{
			ob_start();
			if($sql -> db_Select("content", "*", "content_id=$sub_action")){	
				$row = $sql -> db_Fetch(); extract($row);

				if(!check_class($content_class)){
					$ns -> tablerender(LAN_52, "<div style='text-align:center'>".LAN_53."</div>");
					require_once(FOOTERF);
					exit;
				}
				
				$sql2 = new db;
				$gen = new convert; 
				$sql2 -> db_Select("content", "content_id, content_summary", "content_id=$content_parent");
				list($content_id_, $content_summary_) = $sql2-> db_Fetch();
				$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
				$sql2 -> db_Select("user", "*", "user_id=$content_author");
				$row = $sql2 -> db_Fetch(); extract($row);
				if(is_numeric($content_author)){
					$sql2 -> db_Select("user", "*", "user_id=$content_author");
					$row = $sql2 -> db_Fetch(); extract($row);
				}else{
					$tmp = explode("^", $content_author);
					$user_name = $tmp[0];
					$user_email = $tmp[1];
				}

				$text .= ($content_summary_ ? "<a href='".e_SELF."?review.cat.$content_id_'><img src='".e_IMAGE."link_icons/".$content_summary_."' alt='' style='float:left; border:0' /></a>" : "")."
				<span class='mediumtext'><b>$content_heading</b></span>
				<br />
				<span class='smalltext'>".LAN_43."$user_name".LAN_44."$datestamp</span>
				<br /><br />
				$content_summary
				<br /><br />";
			
				$content_content = $aj -> tpa($content_content, "off", "admin", $highlight_search);
				$reviewpages = explode("[newpage]",$content_content);
				$totalpages = count($reviewpages);
				if(strstr($content_content, "{EMAILPRINT}") || $content_pe_icon){
					$content_content = str_replace("{EMAILPRINT}", $textemailprint, $content_content);
					$epflag = TRUE;
				}

				if($totalpages > 1){
					$text .=  $reviewpages[(!$id ? 0 : $id)]."<br /><br />";
					if($id != 0){ $text .= "<a href='content.php?review.$sub_action.".($id-1)."'>".LAN_25." <<</a> "; }
					for($c=1; $c<= $totalpages; $c++){
						$text .= ($c == ($id+1) ? "<span style='text-decoration: underline;'>$c</span>&nbsp;&nbsp;" : "<a href='content.php?review.$sub_action.".($c-1)."'>$c</a>&nbsp;&nbsp;");
					}
					if(($id+1) != $totalpages){ $text .= "<a href='content.php?review.$sub_action.".($id+1)."'>>> ".LAN_26."</a> "; }
					if($epflag){ $text .= $textemailprint; }
					$content_heading .= ", ".LAN_63." ".($id+1);
					$cachestr = ($id ? "review.item.$sub_action.$id" : "review.item.$sub_action");

				}else{
					$text .= $content_content."\n<br />\n";
					if($epflag){ $text .= $textemailprint; }
					$cachestr = "review.item.$sub_action";
					$comflag = TRUE;
				}
				$text .= "<br /><br />
				".LAN_42.": 
				<table style='width:".($content_review_score*2)."px'>
				<tr class='border'>
				<td class='caption' style='width:100%; text-align:right'>$content_review_score%</td>
				</tr>
				</table>\n";
			}
			$text .= "<div style='text-align:right'><a href='".e_SELF."?review.cat.$content_id_'>>> ".LAN_27."</a><br />
			<a href='".e_SELF."?review'><< ".LAN_28."</a></div>";
			$ns -> tablerender($caption, $text);
			if($pref['cachestatus']){
				$cache = $aj -> formtpa(ob_get_contents(), "admin");
				set_cache("review.item.$sub_action", $cache);
			}
		}

		if($sql -> db_Select("content", "*", "content_id=$sub_action")){	
			$row = $sql -> db_Fetch(); extract($row);
		}

		if($content_comment){
			if($cache = retrieve_cache("comment.content.$sub_action")){
				echo $aj -> formtparev($cache);
			}else{
				ob_start();
				unset($text);
			if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$sub_action' AND comment_type='1' AND comment_pid='0' ORDER BY comment_datestamp")){
					$width = 0;
					while($row = $sql -> db_Fetch()){
					if($pref['nested_comments']){
						$text = $cobj -> render_comment($row, "content" , "comment", $sub_action, $width, $content_heading);		
						$ns -> tablerender(LAN_5, $text);	
						}else{
							$text .= $cobj -> render_comment($row, "content" , "comment", $sub_action, $width, $content_heading);	
						}
				}
				 if(!$pref['nested_comments']){$ns -> tablerender(LAN_5, $text);	}
					if($pref['cachestatus']){
						$cache = $aj -> formtpa(ob_get_contents(), "admin");
						set_cache("comment.content.$sub_action", $cache);
					}
				}
			}
			if(ADMIN && getperms("B")){
				echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?content.$sub_action'>".LAN_29."</a></div><br />";
			}
		$cobj -> form_comment("comment", "content", $sub_action, $content_heading);
		}
		require_once(FOOTERF);
		exit;
	}

	if($sub_action == "cat"){

		if($id){
			$query = "content_parent=$id AND content_type=3 ORDER BY content_datestamp DESC LIMIT 0,10";
		}else{
			$query = "content_parent=0 AND content_type=3 ORDER BY content_datestamp DESC LIMIT 0,10";
		}

		if($cache = retrieve_cache("review.cat.$id")){
			echo $aj -> formtparev($cache);
		}else{
			ob_start();
			if($sql -> db_Select("content", "*", "content_id=$id") || !$id){
				$row = $sql -> db_Fetch(); extract($row);
				$category = $content_heading;
				if($sql -> db_Select("content", "*", $query)){
					$text = "<br />";
					$icon = $content_summary;
					$cat_id = $content_id;
					$sql2 = new db;
					$gen = new convert; 
					$text .= "<table style='width:95%'>\n";
					while($row = $sql -> db_Fetch()){
						extract($row);
						if(check_class($content_class)){
							if(is_numeric($content_author)){
								$sql2 -> db_Select("user", "*", "user_id=$content_author");
								$row = $sql2 -> db_Fetch(); extract($row);
							}else{
								$tmp = explode("^", $content_author);
								$user_name = $tmp[0];
								$user_email = $tmp[1];
							}
							$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
							$text .= "<tr><td style='width:5%; text-align:center; vertical-align:top'>".($icon ? "<img src='".e_IMAGE."link_icons/".$icon."' alt='' />" : "&nbsp;")."</td>
							<td style='width:95%'>
							<b><span class='mediumtext'><a href='".e_SELF."?review.$content_id'>$content_heading</a></span></b>
							<br />
							<span class='smalltext'>".LAN_43."$user_name".LAN_44."$datestamp</span>
							<br />
							$content_summary
							<br />
							<table style='width:".($content_review_score*2)."px'>
							<tr  class='border'>
							<td class='caption' style='width:100%; text-align:right'>$content_review_score%</td>
							</tr>
							</table>\n<br />\n</td></tr>\n";
						}
					}
				}else{
					$text .= "<table><tr><td>".LAN_45."</td></tr>";
				}
				$text .= "</table><div style='text-align:right'><a href='".e_SELF."?review'><< ".LAN_30."</a></div>";
				$ns -> tablerender(LAN_32.": ".$category, $text);

				if($pref['cachestatus']){
					$cache = $aj -> formtpa(ob_get_contents(), "admin");
					 set_cache("review.cat.$id", $cache);
				}



				unset($text);
				if($sql -> db_Select("content", "content_id, content_heading, content_datestamp ", "content_parent=$id AND content_type=3 ORDER BY content_datestamp DESC LIMIT 10,200")){
					while($row = $sql -> db_Fetch()){
						extract($row);
						if(!is_object($gen)){ $gen = new convert; }
						$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
						$text .= "<img src='".e_IMAGE."generic/hme.png' alt='' style='vertical-align:middle' /> <a href='".e_SELF."?review.$content_id'>$content_heading</a> ($datestamp)<br />";
					}
					$ns -> tablerender(LAN_46.": ".$category, $text);
				}



			}
		}

		unset($text);
		if($sql -> db_Select("content", "content_id, content_heading, content_datestamp ", "content_subheading REGEXP('^-$id-') AND content_type=3 ORDER BY content_datestamp DESC LIMIT 10,200")){
			while($row = $sql -> db_Fetch()){
				extract($row);
				$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
				$text .= "<img src='".e_IMAGE."generic/hme.png' alt='' style='vertical-align:middle' /> <a href='".e_SELF."?review.$content_id'>$content_heading</a> ($datestamp)<br />";
			}
			$ns -> tablerender(LAN_62.": ".$category, $text);
		}
		require_once(FOOTERF);
		exit;
	}



	if($cache = retrieve_cache("review.main")){
		echo $aj -> formtparev($cache);
	}else{
		ob_start();
		if($sql -> db_Select("content", "*", "content_type=3 ORDER BY content_datestamp DESC LIMIT 0,10")){
			$text = "<br />";
			$sql2 = new db;
			$gen = new convert; 
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					$summary = $content_summary;
					$rev_id = $content_id;
					$category = $content_parent;

					if(is_numeric($content_author)){
						$sql2 -> db_Select("user", "*", "user_id=$content_author");
						$row = $sql2 -> db_Fetch(); extract($row);
					}else{
						$tmp = explode("^", $content_author);
						$user_name = $tmp[0];
						$user_email = $tmp[1];
					}

					$sql2 -> db_Select("content", "content_id, content_summary", "content_id=$category");
					$row = $sql2 -> db_Fetch(); extract($row);
					$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));

					$text .= (file_exists(e_IMAGE."link_icons/$content_summary") ? "<a href='".e_SELF."?review.cat.$content_id'><img src='".e_IMAGE."link_icons/".$content_summary."' alt='' style='float:left; border:0' /></a>" : "&nbsp;")."
					<b><span class='mediumtext'><a href='".e_SELF."?review.$rev_id'>$content_heading</a></span></b>
					<br />
					<span class='smalltext'>".LAN_43."<b>$user_name</b>".LAN_44."$datestamp</span>
					<br />
					$summary
					<br />
					<table style='width:".($content_review_score*2)."px'>
					<tr  class='border'>
					<td class='caption' style='width:100%; text-align:right'>$content_review_score%</td>
					</tr>
					</table>\n<br />\n";
				}
			}
		}else{
			$ns -> tablerender(LAN_32, LAN_55);
			require_once(FOOTERF);
			exit;
		}
		$ns -> tablerender(LAN_32, $text);

		if($sql -> db_Select("content", "*", "content_type=10")){
			$text = "<div style='text-align:center'>
			<table class='fborder' style='width:95%'>\n";

			while($row = $sql -> db_Fetch()){
				extract($row);
				$total = $sql2 -> db_Select("content", "content_class", "content_parent=$content_id AND content_type=3");
				if($total){
					while($row2 = $sql2 -> db_Fetch()){
						extract($row2);
						if(!check_class($content_class)){
							$total = $total - 1;
						}
					}
				}
				$text .= "<tr>
				<td class='forumheader3' style='width:10%; text-align:center' rowspan='2'>
				".($content_summary ? "<a href='".e_SELF."?review.cat.$content_id'><img src='".e_IMAGE."link_icons/".$content_summary."' alt='' style='vertical-align:middle; border:0' /></a>" : "&nbsp;")."
				</td>
				<td class='forumheader' style='width:90%'><b><a href='".e_SELF."?review.cat.$content_id'>$content_heading</a></b></td>
				</tr>
				<tr>
				<td class='forumheader3'>$content_subheading  <span class='smalltext'>( $total ".($total==1 ? LAN_34 : LAN_33)." )</span></td>
				</tr>\n";
			}
			$total = $sql2 -> db_Select("content", "*", "content_type=3 AND content_parent=0");
				if($total){
					while($row2 = $sql2 -> db_Fetch()){
						extract($row2);
						if(!check_class($content_class)){
							$total = $total - 1;
						}
					}
				}
				$text .= "<tr>
				<td class='forumheader3' style='width:10%; text-align:center' rowspan='2'>
				&nbsp;
				</td>
				<td class='forumheader' style='width:90%'><b><a href='".e_SELF."?review.cat.0'>".LAN_61."</a></b></td>
				</tr>
				<tr>
				<td class='forumheader3'><span class='smalltext'>( $total ".($total == 1 ? LAN_34 : LAN_33)." )</span></td>
				</tr>\n";
			$text .= "</table>\n</div>\n";
			$ns -> tablerender(LAN_35, $text);

			if($pref['cachestatus']){
				$cache = $aj -> formtpa(ob_get_contents(), "admin");
				 set_cache("review.main", $cache);
			}
		}
	}
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------


// ##### Article List -----------------------------------------------------------------------------------------------------------------------------------------------------------
if($action == "article"){
	unset($text);
	if(is_numeric($sub_action)){
		$cachestr = ($id ? "article.item.$sub_action.$id" : "article.item.$sub_action");
		if($cache = retrieve_cache($cachestr)){
			echo $aj -> formtparev($cache);
		}else{
			ob_start();
			if(!$CONTENT_ARTICLE_TABLE){
				require_once(e_BASE.$THEMES_DIRECTORY."templates/content_template.php");
			}
			if($sql -> db_Select("content", "*", "content_id=$sub_action")){	
				$row = $sql -> db_Fetch(); extract($row);
				
				if(!check_class($content_class)){
					$ns -> tablerender(LAN_52, "<div style='text-align:center'>".LAN_51."</div>");
					require_once(FOOTERF);
					exit;
				}
				$caption = $content_heading;
			}

			$content_article_table_string .= parse_content_article_table($row);
			$content_article_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARTICLE_TABLE_START);
			$content_article_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARTICLE_TABLE_END);
			$text .= $content_article_table_start."".$content_article_table_string."".$content_article_table_end;
			
			$ns -> tablerender($caption, $text);

			if($pref['cachestatus']){
				$cache = $aj -> formtpa(ob_get_contents(), "admin");
				set_cache($cachestr, $cache);
			}
		}

		if($sql -> db_Select("content", "*", "content_id=$sub_action")){	
			$row = $sql -> db_Fetch(); extract($row);
		}
		
		$totalpages = substr_count($content_content, "[newpage]");
		$comflag = ($totalpages == $id ? TRUE : FALSE);

		// article content rating table
		$content_article_rating_table_string .= parse_content_article_rating_table($row);
		$content_article_rating_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARTICLE_RATING_TABLE_START);
		$content_article_rating_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARTICLE_RATING_TABLE_END);
		unset($text);
		$text .= $content_article_rating_table_start."".$content_article_rating_table_string."".$content_article_rating_table_end;
		$ns -> tablerender(LAN_42, $text);
	
		if($content_comment && $comflag){
			if($cache = retrieve_cache("comment.content.$sub_action")){
				echo $aj -> formtparev($cache);
			}else{
				ob_start();
				unset($text);
				if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$sub_action' AND comment_type='1' AND comment_pid='0' ORDER BY comment_datestamp")){
					$width = 0;
					while($row = $sql -> db_Fetch()){
					if($pref['nested_comments']){
						$text = $cobj -> render_comment($row, "content" , "comment", $sub_action, $width, $content_heading);		
						$ns -> tablerender(LAN_5, $text);	
						}else{
							$text .= $cobj -> render_comment($row, "content" , "comment", $sub_action, $width, $content_heading);	
						}
				}
				 if(!$pref['nested_comments']){$ns -> tablerender(LAN_5, $text);	}
						if($pref['cachestatus']){
						$cache = $aj -> formtpa(ob_get_contents(), "admin");
						set_cache("comment.content.$sub_action", $cache);
							}
						}
					}
			if(ADMIN && getperms("B")){
				echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?content.$sub_action'>".LAN_29."</a></div><br />";
			}
		$cobj -> form_comment("comment", "content", $sub_action, $content_heading);
		}

		require_once(FOOTERF);
		exit;
	}

	if($sub_action == "cat"){

		if($id){
			$query = "content_parent=$id AND content_type=0 ORDER BY content_datestamp DESC LIMIT 0,10";
		}else{
			$query = "content_parent=0 AND content_type=0 ORDER BY content_datestamp DESC LIMIT 0,10";
		}

		// ##### category -------------------------------------------------------------------------
		if($cache = retrieve_cache("article.cat.$id")){
			echo $aj -> formtparev($cache);
		}else{
			ob_start();
			if(!$CONTENT_RECENT_TABLE){
				require_once(e_BASE.$THEMES_DIRECTORY."templates/content_template.php");
			}
			$sql = new db; $sql2 = new db;
			if($sql -> db_Select("content", "*", "content_id=$id") || !$id){
				$row = $sql -> db_Fetch(); extract($row);
				$caption = LAN_47.": ".$content_heading;

				if($sql -> db_Select("content", "*", $query)){
					$sql2 = new db;
					$gen = new convert;

					$summary = $content_summary;
					$rev_id = $content_id;
					$category = $content_parent;
					
					while($row = $sql -> db_Fetch()){
						extract($row);
						if(check_class($content_class)){
							$content_recent_table_string .= parse_content_recent_table($row);
						}
					}
					$content_recent_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_START);
					$content_recent_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_END);

					$text .= $content_recent_table_start."".$content_recent_table_string."".$content_recent_table_end;

				}else{
					$text .= "<tr><td>".LAN_45."</td></tr>";
				}
				$ns -> tablerender($caption, $text);
				if($pref['cachestatus']){
					$cache = $aj -> formtpa(ob_get_contents(), "admin");
					 set_cache("article.cat.$id", $cache);
				}
			}
		}
		// ##### ----------------------------------------------------------------------------------

		// ##### archive --------------------------------------------------------------------------
		unset($text);
		if(!$CONTENT_ARCHIVE_TABLE){
			require_once(e_BASE.$THEMES_DIRECTORY."templates/content_template.php");
		}
		if($sql -> db_Select("content", "content_id, content_heading, content_datestamp ", "content_parent=$id AND content_type=0 ORDER BY content_datestamp DESC LIMIT 10,200")){
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(!is_object($gen)){ $gen = new convert; }
				if(check_class($content_class)){
					$content_archive_table_string .= parse_content_archive_table($row);
				}
			}
			$sql2 = new db;
			$sql2 -> db_Select("content", "content_heading", "content_id=$id");
			while(list($content_heading) = $sql2 -> db_Fetch()){
				$category = $content_heading;
			}
			$content_archive_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARCHIVE_TABLE_START);
			$content_archive_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARCHIVE_TABLE_END);

			$text .= $content_archive_table_start."".$content_archive_table_string."".$content_archive_table_end;
			$ns -> tablerender(LAN_46.": ".$category, $text);
		}
		// ##### ----------------------------------------------------------------------------------
		require_once(FOOTERF);
		exit;
	}

	if($cache = retrieve_cache("article.main")){
		echo $aj -> formtparev($cache);
	}else{
		ob_start();

		// ##### recent articles ------------------------------------------------------------------
		if(!$CONTENT_RECENT_TABLE){
			require_once(e_BASE.$THEMES_DIRECTORY."templates/content_template.php");
		}
		$sql = new db; $sql2 = new db;
		if($sql -> db_Select("content", "*", "content_type=0 ORDER BY content_datestamp DESC LIMIT 0,10")){
			$gen = new convert;
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					$content_recent_table_string .= parse_content_recent_table($row);
				}
			}
			$content_recent_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_START);
			$content_recent_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE_END);

			$text .= $content_recent_table_start."".$content_recent_table_string."".$content_recent_table_end;
		}else{
			$text .= "<tr><td>".LAN_45."</td></tr>";
		}
		// ##### ----------------------------------------------------------------------------------
		$ns -> tablerender(LAN_47, $text);

		// ##### category table -------------------------------------------------------------------
		if(!$CONTENT_CATEGORY_TABLE){
			require_once(e_BASE.$THEMES_DIRECTORY."templates/content_template.php");
		}

		if($sql -> db_Select("content", "*", "content_type=6")){
			while($row = $sql -> db_Fetch()){
				extract($row);
				$total = $sql2 -> db_Select("content", "content_class", "content_parent=$content_id AND content_type=0");
				if($total){
					while($row2 = $sql2 -> db_Fetch()){
						extract($row2);
						if(!check_class($content_class)){
							$total = $total - 1;
						}
					}
					$content_category_table_string .= parse_content_category_table($row, $total);
				}
			}

			// uncategorized
			$untotal = $sql2 -> db_Select("content", "*", "content_type=0 AND content_parent=0");
			if($untotal){
				while($row2 = $sql2 -> db_Fetch()){
					extract($row2);
					if(!check_class($content_class)){
						$untotal = $untotal - 1;
					}
				}
				$content_category_table_string .= parse_content_category_table($row, $untotal);
			}

			$content_category_table_start = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CATEGORY_TABLE_START);
			$content_category_table_end = preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CATEGORY_TABLE_END);

			$text2 .= $content_category_table_start."".$content_category_table_string."".$content_category_table_end;
			// ##### ----------------------------------------------------------------------------------
			$ns -> tablerender(LAN_50, $text2);

			if($pref['cachestatus']){
				$cache = $aj -> formtpa(ob_get_contents(), "admin");
				 set_cache("article.main", $cache);
			}
		}
	}
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------

function parse_content_article_table($row){
		global $CONTENT_ARTICLE_TABLE, $rater, $aj, $ep, $id;
		extract($row);

		$category = $content_parent;
 		$sub_action = $content_id;

		$sql = new db; $sql2 = new db;
		$gen = new convert;

		$textemailprint = $ep -> render_emailprint("article",$sub_action);
		$category = $content_parent;

		if(is_numeric($content_author)){
			$sql2 -> db_Select("user", "*", "user_id=$content_author");
			$row = $sql2 -> db_Fetch(); extract($row);
		}else{
			$tmp = explode("^", $content_author);
			$user_name = $tmp[0];
			$user_email = $tmp[1];
		}
		$CONTENT_ARTICLE_AUTHOR = ($user_name != "" ? "<a href='mailto:$user_email'>$user_name</a>" : "");
			$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
		$CONTENT_ARTICLE_DATESTAMP = ($datestamp != "" ? $datestamp : "");

		$sql2 -> db_Select("content", "content_id, content_summary", "content_id=$category");
		list($content_id_, $content_summary_) = $sql2-> db_Fetch();

		$CONTENT_ARTICLE_CAT_ICON = ($content_summary_ ? "<a href='".e_SELF."?article.cat.$content_id_'><img src='".e_IMAGE."link_icons/".$content_summary_."' alt='' style='float:left; border:0' /></a>" : "");

		$CONTENT_ARTICLE_SUBHEADING = ($content_subheading != "" ? $content_subheading : "");
		$CONTENT_ARTICLE_SUMMARY = ($content_summary != "" ? $content_summary : "");
		$CONTENT_ARTICLE_HEADING = $content_heading;

		$content_content = $aj -> tpa($content_content, "off", "admin", $highlight_search);
		$articlepages = explode("[newpage]",$content_content);
		$totalpages = count($articlepages);
		if(strstr($content_content, "{EMAILPRINT}") || $content_pe_icon){
			$content_content = str_replace("{EMAILPRINT}", $textemailprint, $content_content);
			$epflag = TRUE;
		}

		$CONTENT_ARTICLE_PAGES = "";
		if($totalpages > 1){
			$CONTENT_ARTICLE_PAGES .=  $articlepages[(!$id ? 0 : $id)]."<br /><br />";
 			if($id != 0){ $CONTENT_ARTICLE_PAGES .= "<a href='content.php?article.$sub_action.".($id-1)."'>".LAN_25." &lt;&lt;</a> "; }
			for($c=1; $c<= $totalpages; $c++){
				$CONTENT_ARTICLE_PAGES .= ($c == ($id+1) ? "<span style='text-decoration: underline;'>$c</span>&nbsp;&nbsp;" : "<a href='content.php?article.$sub_action.".($c-1)."'>$c</a>&nbsp;&nbsp;");
			}
 			if(($id+1) != $totalpages){ $CONTENT_ARTICLE_PAGES .= "<a href='content.php?article.$sub_action.".($id+1)."'>&gt;&gt; ".LAN_26."</a> "; }
			if($epflag){
				$CONTENT_ARTICLE_EMAILPRINT = $textemailprint;
			}
			$CONTENT_ARTICLE_HEADING .= ", ".LAN_63." ".($id+1);
			$cachestr = ($id ? "article.item.$sub_action.$id" : "article.item.$sub_action");

		}else{
			$CONTENT_ARTICLE_CONTENT = $content_content;
			if($epflag){
				$CONTENT_ARTICLE_EMAILPRINT = $textemailprint;
			}
			$cachestr = "article.item.$sub_action";
			$comflag = TRUE;
		}

		$CONTENT_ARTICLE_LINK_CAT = "<a href='".e_SELF."?article.cat.$content_id_'>>> ".LAN_36."</a>";
		$CONTENT_ARTICLE_LINK_MAIN = "<a href='".e_SELF."?article'><< ".LAN_37."</a>";

					
		return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARTICLE_TABLE));
}


function parse_content_article_rating_table($row){
		global $CONTENT_ARTICLE_RATING_TABLE, $rater, $comflag, $sub_action;
		extract($row);

			if($comflag){
				unset($text);
				if($ratearray = $rater -> getrating("article", $sub_action)){
					$CONTENT_ARTICLE_RATING .= LAN_64;
					for($c=1; $c<= $ratearray[1]; $c++){
						$CONTENT_ARTICLE_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='vertical-align:middle' />";
					}

					if($ratearray[1] < 10){
						for($c=9; $c>=$ratearray[1]; $c--){
							$CONTENT_ARTICLE_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='vertical-align:middle' />";
						}
					}
					$CONTENT_ARTICLE_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='vertical-align:middle' />";

					if($ratearray[2] == ""){ $ratearray[2] = 0; }
					$CONTENT_ARTICLE_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
					$CONTENT_ARTICLE_RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
				}else{
					$CONTENT_ARTICLE_RATING .= LAN_65;
				}

				if(!$rater -> checkrated("article", $sub_action) && USER){
					$CONTENT_ARTICLE_RATING .= "<br />\n<div class='smalltext' style='text-align:right'>".
					$rater -> rateselect("&nbsp;&nbsp;&nbsp;&nbsp; ".LAN_40, "article", $sub_action)."</div>";
				}else if(USER){
					$CONTENT_ARTICLE_RATING .= " - ".LAN_41;
				}
			}

		return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARTICLE_RATING_TABLE));
}


function parse_content_recent_table($row){
		global $CONTENT_RECENT_TABLE, $rater, $aj;
		extract($row);

		$summary = $content_summary;
		$rev_id = $content_id;
		$category = $content_parent;

		$sql = new db; $sql2 = new db;
		$gen = new convert;

		$sql2 -> db_Select("content", "content_id, content_summary", "content_id=$category");
		$row = $sql2 -> db_Fetch(); extract($row);

		// author
		if(is_numeric($content_author)){
			$sql2 -> db_Select("user", "*", "user_id=$content_author");
			$row = $sql2 -> db_Fetch(); extract($row);
		}else{
			$tmp = explode("^", $content_author);
			$user_name = $tmp[0];
			$user_email = $tmp[1];
		}

		$summary = $content_summary;
		
		$CONTENT_RECENT_SUBHEADING = ($content_subheading != "" ? $content_subheading."<br />" : "");
		$CONTENT_RECENT_SUMMARY = ($content_summary != "" ? $content_summary."<br />" : "");
		$CONTENT_RECENT_AUTHOR = ($user_name != "" ? $user_name : "");
		$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
		$CONTENT_RECENT_DATE = ($datestamp != "" ? $datestamp : "");
		$CONTENT_RECENT_ICON = ($content_summary && $content_parent ? "<a href='".e_SELF."?article.cat.$content_id'><img src='".e_IMAGE."link_icons/".$content_summary."' alt='' style='border:0' /></a>" : "&nbsp;");
		
		$CONTENT_RECENT_HEADING = "<a href='".e_BASE."content.php?article.$rev_id'>".$content_heading."</a>";

		// ------------ BEGIN RATING SYSTEM ------------------------------------------
		$CONTENT_RECENT_RATING .= "<span class='smalltext' style='width:280px; text-align:left;'>";

		if($ratearray = $rater -> getrating("article", $rev_id)){
			for($c=1; $c<= $ratearray[1]; $c++){
				$CONTENT_RECENT_RATING .= "<img src='".e_IMAGE."rate/box.png' alt='' style='vertical-align:middle' />";
			}

			if($ratearray[1] < 10){
				for($c=9; $c>=$ratearray[1]; $c--){
					$CONTENT_RECENT_RATING .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='vertical-align:middle' />";
				}
			}
			$CONTENT_RECENT_RATING .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='vertical-align:middle' />";

			if($ratearray[2] == ""){ $ratearray[2] = 0; }
			$CONTENT_RECENT_RATING .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
			$CONTENT_RECENT_RATING .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
		}else{
			$CONTENT_RECENT_RATING .= "".LAN_65."";
		}

		if(!$rater -> checkrated("article", $rev_id) && USER){
			$CONTENT_RECENT_RATING .= "</span><span class='smalltext' style='width:240px; text-align:right;'>".
			$rater -> rateselect("&nbsp;&nbsp;&nbsp;&nbsp; ".LAN_40, "article", $rev_id)."";
		}else if(USER){
			$CONTENT_RECENT_RATING .= " - ".LAN_41;
		}
		
		$CONTENT_RECENT_RATING .= "</span><br />";
		// ---------------------------------------------------------------------------
					
		return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_RECENT_TABLE));
}




function parse_content_category_table($row, $total){
		global $CONTENT_CATEGORY_TABLE;
		extract($row);

		if($content_type==0 && $content_parent==0){
			$CONTENT_CATEGORY_HEADING = "<a href='".e_SELF."?article.cat.0'>".LAN_61."</a>";
		}else{
			$CONTENT_CATEGORY_HEADING = "<a href='".e_SELF."?article.cat.$content_id'>".$content_heading."</a>";
		}
		$CONTENT_CATEGORY_ICON = ($content_summary ? "<a href='".e_SELF."?article.cat.$content_id'><img src='".e_IMAGE."link_icons/".$content_summary."' alt='' style='vertical-align:middle; border:0' /></a>" : "&nbsp;");
		$CONTENT_CATEGORY_SUBHEADING = $content_subheading;
		$CONTENT_CATEGORY_NUMBER = "".$total." ".($total==1 ? LAN_49 : LAN_48)."";

		return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_CATEGORY_TABLE));
}

function parse_content_archive_table($row){
		global $gen, $CONTENT_ARCHIVE_TABLE;
		extract($row);

		$CONTENT_ARCHIVE_DATESTAMP = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
		$CONTENT_ARCHIVE_ICON = "<img src='".e_IMAGE."generic/hme.png' alt='' style='vertical-align:middle' />";
		$CONTENT_ARCHIVE_HEADING = "<a href='".e_SELF."?article.$content_id'>$content_heading</a>";

		return(preg_replace("/\{(.*?)\}/e", '$\1', $CONTENT_ARCHIVE_TABLE));
}


require_once(FOOTERF);
?>
