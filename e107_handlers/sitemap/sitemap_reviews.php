<?php
/*
|	©Lolo Irie 2001-2004 (e107 Dev Team)
|	http://etalkers.org

|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org) for the e107 project.
*/
function sm_reviews(){
	$sql = new db;
	$sql2 = new db;
	$aj = new textparse;
	$texto .= "<div class='caption2' style='text-align: left;' >\n
	<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('reviews_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"content.php?review\" >".LANSM_16."</a> <b class='smalltext' >".LANSM_17."</b>\n
	</div><br />\n";
	
	if($sql -> db_Select("content","content_id, content_heading, content_type, content_class","content_parent='0' AND (content_type='3' OR content_type='10') ORDER BY content_heading ASC")){
		$texto .= "<div class='cats' id='reviews_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
		<b>".LANSM_23."</b><br />\n";
		$nbr_reviews_cat = 0;
		while($row = $sql -> db_Fetch()){
			extract($row);
			$row[1] = $aj -> tpa($row[1]);
			if(check_class($row[3]) && $row[2]=="3"){
				$texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('reviews_subcats2_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> <a  href='content.php?review.cat.0' >".LANSM_40."</a>\n";
				$nbr_reviews_cat++;
				if($sql2 -> db_Select("content","content_id, content_heading, content_class","content_parent='0' AND content_type='3' ORDER BY content_heading ASC")){
						$texto .= "<br /><br /><div class='subcats' id='reviews_subcats2_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
					<b>".LANSM_38."</b><br />";
						while($row2 = $sql2 -> db_Fetch()){
							extract($row2);
							$row2[1] = $aj -> tpa($row2[1]);
							if(check_class($row2[2])){
								$texto .= "<a href=\"content.php?item.".$row2[0]."\" >".$row2[1]."</a><br />\n";
							}
						}
						$texto .= "<br /><br /></div></div>\n";
				}else{
					$texto .= "<br /><br />";
				}
			}else if(check_class($row[3])){
				$texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('reviews_subcats_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> <a  href='content.php?review.cat.".$row[0]."' >".$row[1]."</a>\n";
				$nbr_reviews_cat++;
				if($sql2 -> db_Select("content","content_id, content_heading, content_class","content_parent='".$row[0]."' ORDER BY content_heading ASC")){
						$texto .= "<br /><br /><div class='subcats' id='reviews_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
					<b>".LANSM_38."</b><br />";
						while($row2 = $sql2 -> db_Fetch()){
							extract($row2);
							if(check_class($row2[2])){
								$texto .= "<a href=\"content.php?item.".$row2[0]."\" >".$row2[1]."</a><br />\n";
							}
						}
						$texto .= "<br /><br /></div></div>\n";
				}else{
					$texto .= "<br /><br />";
				}
			}
		}
		$texto .= "</div>";
		
	}
	return $texto;
}
?>