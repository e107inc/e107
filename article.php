<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/article.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).

	Heavily updated by McFly

+---------------------------------------------------------------+
*/
require_once("class2.php");
require_once(HEADERF);

require_once(e_HANDLER."comment_class.php");
@include(e_LANGUAGEDIR.$language."/lan_comment.php");

$cobj = new comment;
if(IsSet($_POST['commentsubmit'])){
	$tmp = explode(".", e_QUERY);
	$cobj -> enter_comment($_POST['author_name'], $_POST['comment'], "content", $tmp[0]);
	$sql -> db_Delete("cache", "cache_url='$page?".e_QUERY."' ");
}

if($sql -> db_Select("cache", "*", "cache_url='$page?".e_QUERY."' ")){
	$row = $sql -> db_Fetch(); extract($row);
	$aj = new textparse;
	echo $aj -> formtparev($cache_data);
	$CACHE = TRUE;
}

$urlpage = $page;
ob_start();

$aj = new textparse;

$itemview = "10";

if(!e_QUERY){
	$text = "<a href='".e_BASE."article.php?0.list.0'>".LAN_100."</a><br /><a href='".e_HTTP."article.php?0.list.3'>".LAN_190."</a>";
	$ns -> tablerender("<div style='text-align:center'>".LAN_313."</div>", $text);
	require_once(FOOTERF);
	exit;
}else{
	$ar = explode(".", e_QUERY);
	$id = $ar[0];
	$page = $ar[1];
}
if($ar[1] == "list"){
	$id="list";
	$from=$ar[0];
	$type=$ar[2];
	$parent=$ar[3];
}

// list articles -------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($id == "list"){
	if($type=="3"){ // REVIEW
		if($total = $sql -> db_Select("content", "*", "content_type='3'")){
			$sql -> db_Select("content", "*", "content_type='3' ORDER BY content_datestamp DESC LIMIT $from, $itemview ");
	
			$gen = new convert;
			$sql2 = new db;
			while($row = $sql-> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					if($content_summary == "0"){ $content_summary = LAN_398; }
					$datestamp = $gen->convert_date($content_datestamp, "short");
		
					$content_heading = $aj -> tpa($content_heading);
					$content_summary = $aj -> tpa($content_summary);
		
					$text .= "<a href='".e_BASE."article.php?".$content_id.".0'><b>".$content_heading."</b></a> <span class='smalltext'>".$datestamp."</span>
					<br />".$content_summary."<br />";
					if($comments = $sql2 -> db_Select("comments", "*", "comment_type='1' AND comment_item_id='$content_id' ")){
						$text .= "<span class='smalltext'>".LAN_99.": ".$comments."</span><br />";
					}
					$text .= "<br />";
				}
			}
				
			$ns -> tablerender("<div style='text-align:center'>".LAN_190."</div>", $text);
	
			require_once(e_HANDLER."np_class.php");
			$ix = new nextprev("article.php", $from, $itemview, $total, LAN_190, "list.3");
	
			require_once(FOOTERF);
			exit;
		}
	}

// ---------------------------------------------------------------------------------------------------------------
	if($type=="0" and !$CACHE){ //ARTICLE
		if($parent){
			$sql2 = new db;
			if($parent > 0){
				$sql -> db_Select("content", "*", "content_type=6 AND content_id='{$parent}'");
				$row=$sql -> db_Fetch();
				extract($row);
				if(check_class($content_class)){
					$text .= "<table style='width:90%'>
					<tr>
					<td style='width:5%; text-align:center; vertical-align:middle'><img src='".e_IMAGE."generic/article.png' alt='' /></td>
					<td style='width:95%; vertical-align:middle'><div class='mediumtext'><b>$content_heading</b></div><div class='smalltext'>$content_subheading</div><br /></td>
					</tr>";
				} else {
					$text.="<div style='text-align:center'>".LAN_2."</div>";
				}
			} else {
				$text .= "<table style='width:90%'>
				<tr>
				<td style='width:5%; text-align:center; vertical-align:middle'><img src='".e_IMAGE."generic/article.png' alt='' /></td>
				<td style='width:95%; vertical-align:middle'><div class='mediumtext'><b>No Parent</b></div></td>
				</tr>";
				$content_id=0;
			}
		
			$total = $sql2 -> db_Select("content", "*", "content_type='0' AND content_page='$content_id' ");
			$sql2 -> db_Select("content", "*", "content_type='0' AND content_page='$content_id' ORDER BY content_datestamp DESC LIMIT $from, $itemview ");
			while($row = $sql2 -> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					$text .= "<tr><td style='width:5%; text-align:center; vertical-align:middle'>&nbsp;</td>
					<td style='width:95%; vertical-align:middle'><b><a href='article.php?$content_id.0'>$content_heading</a></b><br />$content_summary</td></tr>";
				}
			}
			$text .= "</table><br />";
		} else {
			$sql2 = new db;
			$sql -> db_Select("content", "*", "content_type=6");
			while($row = $sql -> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					$text .= "<table style='width:90%'>
					<tr>
					<td style='width:5%; text-align:center; vertical-align:middle'><img src='".e_IMAGE."generic/article.png' alt='' /></td>
					<td style='width:95%; vertical-align:middle'><div class='mediumtext'><b>$content_heading</b></div></td>
					</tr>";
			
					$sql2 -> db_Select("content", "*", "content_page=$content_id");
					while($row = $sql2 -> db_Fetch()){
						extract($row);
						if(check_class($content_class)){
							$text .= "<tr><td style='width:5%; text-align:center; vertical-align:middle'>&nbsp;</td>
							<td style='width:95%; vertical-align:middle'><b><a href='article.php?$content_id.0'>$content_heading</a></b><br />$content_summary</td></tr>";
						}
					}
					$text .= "</table><br />";
				}
			}
			
			$i=$sql -> db_Select("content", "*", "content_type=0 AND content_page=0");
			if($i){
				$text.="
				<table style='width:90%'>
				<tr>
				<td style='width:5%; text-align:center; vertical-align:middle'><img src='".e_IMAGE."generic/article.png' alt='' /></td>
				<td style='width:95%; vertical-align:middle'> ---------------------------------------------------------------------- </td>
				</tr>
				</table>
				";
			}

			while($row = $sql -> db_Fetch()){
				extract($row);
				if(check_class($content_class)){
					$text .= "<table style='width:90%'>
					<tr>
					<td style='width:5%; text-align:center; vertical-align:middle'>&nbsp;</td>
					<td style='width:95%; vertical-align:middle'><b><a href='article.php?$content_id.0'>$content_heading</a></b><br />$content_summary</td>
					</tr></table>";
				}
			}
		}
		
		$ns -> tablerender(LAN_100.$title,$text);	
		require_once(e_HANDLER."np_class.php");
		$ix = new nextprev("article.php", $from, $itemview, $total, LAN_100, "list.0.".$parent);
		require_once(FOOTERF);
		exit;
	}
}

if(!$sql -> db_Select("content", "*", " content_id='$id' ")){
	header("location: ".e_BASE."index.php");
	exit;
}

$row = $sql -> db_Fetch(); extract($row);

if(!check_class($content_class)){
	$content_type==3 ? $caption=LAN_190 : $caption=LAN_100;
	if($content_type == 1){$caption="";}
	$ns->tablerender($caption, "<div style='text-align:center'>".LAN_2."</div>");
	require_once(FOOTERF);
	exit;
}

if($page == 255){
// content page -------------------------------------------------------------------------------------------------------------------------------------------------------------------
	
	$text = ($content_page ? $aj -> tpa($content_content, "nobreak") : $aj -> tpa($content_content));
	$caption = $aj -> tpa($content_subheading);

	$ns -> tablerender($caption, $text);
	unset($text);
	if($content_comment){ $cpcomments = TRUE; }
}else{

// article or review  -------------------------------------------------------------------------------------------------------------------------------------------------------------------

	$content_heading  = $aj -> tpa($content_heading );
	$content_subheading = $aj -> tpa($content_subheading);
	if($content_author == 0){
		$admin_email = "e107@jalist.com";
		$admin_name = "jalist";
	}else{
		$sql -> db_Select("user", "*", "user_id='$content_author'");
		list($admin_id, $admin_name, $null, $null, $admin_email) = $sql-> db_Fetch();
	}
	$obj = new convert;
	$datestamp = $obj->convert_date($content_datestamp, "long");

//	$caption = $main_content_heading."<br />";
	$content_content .= "<br />";
		if($content_subheading != ""){
			$text = "<i>".$content_subheading."</i>
	<br />";
		}
	$text .= "<i>by <a href='mailto:".$admin_email."'>".$admin_name."</a></i>
	<br />
	<span class='smalltext'>".
	$datestamp."
	</span>
	<br /><br />";

	$ep = "<div style='text-align:right'>
	<a href='email.php?article.".$id."'><img src='".e_IMAGE."generic/friend.gif' style='border:0' alt='email to someone' /></a>
	<a href='print.php?content.".$id."'><img src='".e_IMAGE."generic/printer.gif' style='border:0' alt='printer friendly' /></a>
	</div>";

	$articlepages = explode("[newpage]",$content_content);
	$totalpages = count($articlepages);

	// multi page article -------------------------------------------------------------------------------------------------------------------------------------------------------------------

	if($totalpages > 1 && !$CACHE){
		$text .=  $aj -> tpa($articlepages[$page]."<br /><br />");
		if($page != 0){ $text .= "<a href='article.php?$id.".($page-1)."'>".LAN_25." <<</a> "; }
		for($c=1; $c<= $totalpages; $c++){
			$text .= ($c == ($page+1) ? "<u>$c</u>&nbsp;&nbsp;" : "<a href='article.php?$id.".($c-1)."'>$c</a>&nbsp;&nbsp;");
		}
		if(($page+1) != $totalpages){ $text .= "<a href='article.php?$id.".($page+1)."'>>> ".LAN_26."</a> "; }
		if(strstr($text, "{EMAILPRINT}")){ $text = str_replace("{EMAILPRINT}", $ep, $text); }
		$ns -> tablerender($content_heading.", page ".($page+1), $text);
	}else{
	// single page article -------------------------------------------------------------------------------------------------------------------------------------------------------------------
		if(!$CACHE){
			$text .= $aj ->tpa($content_content);
			if(strstr($text, "{EMAILPRINT}")){ $text = str_replace("{EMAILPRINT}", $ep, $text); }

			if($content_page && $content_type=="3"){
				$text .= "<br /><br /><b>".LAN_399."</b>: ".$content_page."%";
			}

			$ns -> tablerender($content_heading , $text);
		}
	}

	if($pref['cachestatus'] && !$CACHE){
		$cache = $aj -> formtpa(ob_get_contents(), "admin");
		$sql -> db_Insert("cache", "'$urlpage?".e_QUERY."', '".time()."', '$cache' ");
	}


}



// render comments -------------------------------------------------------------------------------------------------------------------------------------------------------------------

if($content_comment && ($cpcomments || ($page+1) == $totalpages)){
	unset($text);
	if($comment_total = $sql -> db_Select("comments", "*",  "comment_item_id='$id' AND comment_type='1' ORDER BY comment_datestamp")){
		while($row = $sql -> db_Fetch()){
			$text .= $cobj -> render_comment($row);
		}
		$ns -> tablerender(LAN_5, $text);
		if(ADMIN && getperms("B")){
			echo "<div style='text-align:right'><a href='".e_ADMIN."modcomment.php?content.$id'>moderate comments</a></div><br />";
		}
	}
	$cobj -> form_comment();
}

// end -------------------------------------------------------------------------------------------------------------------------------------------------------------------


require_once(FOOTERF);
?>