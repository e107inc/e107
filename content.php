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
		$caption = $aj -> tpa($content_subheading, "off", "admin");
		$ns -> tablerender($caption, $text);
		
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
			if($sql -> db_Select("content", "*", "content_id=$sub_action")){	
				$row = $sql -> db_Fetch(); extract($row);

				if(!check_class($content_class)){
					$ns -> tablerender(LAN_52, "<div style='text-align:center'>".LAN_51."</div>");
					require_once(FOOTERF);
					exit;
				}				
				$textemailprint = $ep -> render_emailprint("article",$sub_action);
				$category = $content_parent;
				$sql2 = new db;
				$gen = new convert; 
				$sql2 -> db_Select("content", "content_id, content_summary", "content_id=$category");
				list($content_id_, $content_summary_) = $sql2-> db_Fetch();
				$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
				if(is_numeric($content_author)){
					$sql2 -> db_Select("user", "*", "user_id=$content_author");
					$row = $sql2 -> db_Fetch(); extract($row);
				}else{
					$tmp = explode("^", $content_author);
					$user_name = $tmp[0];
					$user_email = $tmp[1];
				}

				$text .= ($content_summary_ ? "<a href='".e_SELF."?article.cat.$content_id_'><img src='".e_IMAGE."link_icons/".$content_summary_."' alt='' style='float:left; border:0' /></a>" : "")."
				<span class='mediumtext'><b>$content_heading</b></span>
				<br />
				<span class='smalltext'>".LAN_43."<b>$user_name</b>".LAN_44."$datestamp</span>
				<br /><br />
				$content_summary
				<br /><br />";
			
				$content_content = $aj -> tpa($content_content, "off", "admin", $highlight_search);
				$articlepages = explode("[newpage]",$content_content);
				$totalpages = count($articlepages);
				if(strstr($content_content, "{EMAILPRINT}") || $content_pe_icon){
					$content_content = str_replace("{EMAILPRINT}", $textemailprint, $content_content);
					$epflag = TRUE;
				}

				if($totalpages > 1){
					$text .=  $articlepages[(!$id ? 0 : $id)]."<br /><br />";
					if($id != 0){ $text .= "<a href='content.php?article.$sub_action.".($id-1)."'>".LAN_25." <<</a> "; }
					for($c=1; $c<= $totalpages; $c++){
						$text .= ($c == ($id+1) ? "<span style='text-decoration: underline;'>$c</span>&nbsp;&nbsp;" : "<a href='content.php?article.$sub_action.".($c-1)."'>$c</a>&nbsp;&nbsp;");
					}
					if(($id+1) != $totalpages){ $text .= "<a href='content.php?article.$sub_action.".($id+1)."'>>> ".LAN_26."</a> "; }
					if($epflag){ $text .= $textemailprint; }
					$content_heading .= ", ".LAN_63." ".($id+1);
					$cachestr = ($id ? "article.item.$sub_action.$id" : "article.item.$sub_action");

				}else{
					$text .= $content_content."\n<br />\n";
					if($epflag){ $text .= $textemailprint; }
					$cachestr = "article.item.$sub_action";
					$comflag = TRUE;
				}
			}
			$text .= "<br /><div style='text-align:right'><a href='".e_SELF."?article.cat.$content_id_'>>> ".LAN_36."</a><br />
			<a href='".e_SELF."?article'><< ".LAN_37."</a></div>";
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


		if($comflag){
			unset($text);
			if($ratearray = $rater -> getrating("article", $sub_action)){
				$text = LAN_64;
				for($c=1; $c<= $ratearray[1]; $c++){
					$text .= "<img src='".e_IMAGE."rate/box.png' alt='' style='vertical-align:middle' />";
				}

				if($ratearray[1] < 10){
					for($c=9; $c>=$ratearray[1]; $c--){
						$text .= "<img src='".e_IMAGE."rate/empty.png' alt='' style='vertical-align:middle' />";
					}
				}
				$text .= "<img src='".e_IMAGE."rate/boxend.png' alt='' style='vertical-align:middle' />";

				if($ratearray[2] == ""){ $ratearray[2] = 0; }
				$text .= "&nbsp;".$ratearray[1].".".$ratearray[2]." - ".$ratearray[0]."&nbsp;";
				$text .= ($ratearray[0] == 1 ? LAN_38 : LAN_39);
			}else{
				$text .= LAN_65;
			}

			if(!$rater -> checkrated("article", $sub_action) && USER){
				$text .= "<br />\n<div class='smalltext' style='text-align:right'>".
				$rater -> rateselect("&nbsp;&nbsp;&nbsp;&nbsp; ".LAN_40, "article", $sub_action)."</div>";
			}else if(USER){
				$text .= " - ".LAN_41;
			}
			$ns -> tablerender(LAN_42, $text);
		}
	
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

		if($cache = retrieve_cache("article.cat.$id")){
			echo $aj -> formtparev($cache);
		}else{
			ob_start();
			if($sql -> db_Select("content", "*", "content_id=$id") || !$id){
				$row = $sql -> db_Fetch(); extract($row);
				$caption = LAN_47.": ".$content_heading;
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
							$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
							$text .= "<tr><td style='width:5%; text-align:center; vertical-align:top'>".($icon ? "<img src='".e_IMAGE."link_icons/".$icon."' alt='' />" : "&nbsp;")."</td>
							<td style='width:95%'>
							<b><span class='mediumtext'><a href='".e_SELF."?article.$content_id'>$content_heading</a></span></b>
							<br />
							<span class='smalltext'>".LAN_43." $user_name ".LAN_44." $datestamp</span>
							<br />
							$content_summary
							<br /><br />\n</td></tr>\n";
						}
					}
				}else{
					$text .= "<table><tr><td>".LAN_45."</td></tr>";
				}
				$text .= "</table>\n<div style='text-align:right'><a href='".e_SELF."?article'><< ".LAN_37."</a></div>";
				$ns -> tablerender($caption, $text);
				if($pref['cachestatus']){
					$cache = $aj -> formtpa(ob_get_contents(), "admin");
					 set_cache("article.cat.$id", $cache);
				}
			}
		}

		unset($text);
		if($sql -> db_Select("content", "content_id, content_heading, content_datestamp ", "content_parent=$id AND content_type=0 ORDER BY content_datestamp DESC LIMIT 10,200")){
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(!is_object($gen)){ $gen = new convert; }
				$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
				$text .= "<img src='".e_IMAGE."generic/hme.png' alt='' style='vertical-align:middle' /> <a href='".e_SELF."?article.$content_id'>$content_heading</a> ($datestamp)<br />";
			}
			$ns -> tablerender(LAN_46.": ".$category, $text);
		}
		require_once(FOOTERF);
		exit;
	}

	if($cache = retrieve_cache("article.main")){
		echo $aj -> formtparev($cache);
	}else{
		ob_start();

		if($sql -> db_Select("content", "*", "content_type=0 ORDER BY content_datestamp DESC LIMIT 0,10")){
			$text = "<br />
			<table style='width:95%'>";
			
			$sql2 = new db;
			$gen = new convert;
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					$summary = $content_summary;
					$rev_id = $content_id;
					$category = $content_parent;

					$sql2 -> db_Select("content", "content_id, content_summary", "content_id=$category");
					$row = $sql2 -> db_Fetch(); extract($row);

					if(is_numeric($content_author)){
						$sql2 -> db_Select("user", "*", "user_id=$content_author");
						$row = $sql2 -> db_Fetch(); extract($row);
					}else{
						$tmp = explode("^", $content_author);
						$user_name = $tmp[0];
						$user_email = $tmp[1];
					}
					$datestamp = ereg_replace(" -.*", "", $gen->convert_date($content_datestamp, "long"));
					$text .= "<tr>\n<td style='width:5%; text-align:center; vertical-align:top'>\n";
					$text .= ($content_summary && $content_parent ? "<a href='".e_SELF."?article.cat.$content_id'><img src='".e_IMAGE."link_icons/".$content_summary."' alt='' style='border:0' /></a>" : "&nbsp;")."
					</td>\n<td style='width:95%'>
					<b><span class='mediumtext'><a href='".e_SELF."?article.$rev_id'>$content_heading</a></span></b>
					<br />
					<span class='smalltext'>".LAN_43."$user_name".LAN_44."$datestamp</span>
					<br />
					$summary
					<br /><br />\n</td></tr>\n";
				}
			}
		}else{
			$ns -> tablerender(LAN_47, LAN_56);
			require_once(FOOTERF);
			exit;
		}
		$text .= "</table>";
		$ns -> tablerender(LAN_47, $text);

		if($sql -> db_Select("content", "*", "content_type=6")){
			$text = "<div style='text-align:center'>
			<table class='fborder' style='width:95%'>\n";
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
				}
				$text .= "<tr>
				<td class='forumheader3' style='width:10%; text-align:center' rowspan='2'>
				".($content_summary ? "<a href='".e_SELF."?article.cat.$content_id'><img src='".e_IMAGE."link_icons/".$content_summary."' alt='' style='vertical-align:middle; border:0' /></a>" : "&nbsp;")."
				</td>
				<td class='forumheader' style='width:90%'><b><a href='".e_SELF."?article.cat.$content_id'>$content_heading</a></b></td>
				</tr>
				<tr>
				<td class='forumheader3'>$content_subheading  <span class='smalltext'>( $total ".($total==1 ? LAN_49 : LAN_48)." )</span></td>
				</tr>\n";
			}

			    $total = $sql2 -> db_Select("content", "*", "content_type=0 AND content_parent=0");
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
				<td class='forumheader' style='width:90%'><b><a href='".e_SELF."?article.cat.0'>".LAN_61."</a></b></td>
				</tr>
				<tr>
				<td class='forumheader3'><span class='smalltext'>( $total ".($total==1 ? LAN_49 : LAN_48)." )</span></td>
				</tr>\n";

			$text .= "</table>\n</div>\n";
			$ns -> tablerender(LAN_50, $text);
			if($pref['cachestatus']){
				$cache = $aj -> formtpa(ob_get_contents(), "admin");
				 set_cache("article.main", $cache);
			}
		}
	}
}

// ##### End ---------------------------------------------------------------------------------------------------------------------------------------------------------------------




require_once(FOOTERF);
?>