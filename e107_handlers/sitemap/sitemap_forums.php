<?php
/*
|	©Lolo Irie 2001-2004 (e107 Dev Team)
|	http://etalkers.org

|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org) for the e107 project.
*/
function sm_forums(){
	$sql = new db;
	$sql2 = new db;
	$aj = new textparse;
	$texto .= "<div class='caption2' style='text-align: left;' >
	<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('forum_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"forum.php\" >".LANSM_10."</a> <b class='smalltext' >".LANSM_11."</b>\n
	</div><br />\n";
	
	if($sql -> db_Select("forum","forum_id, forum_name, forum_class","forum_parent='0' ORDER BY forum_order")){
		$texto .= "<div class='cats' id='forum_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
		<b>".LANSM_23."</b><br />\n";
		$nbr_forum_cat = 0;
		while($row = $sql -> db_Fetch()){
			extract($row);
			$row[1] = $aj -> tpa($row[1]);
			if(check_class($row[2])){
				$texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('forum_subcats_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> <a href=\"forum.php\" class='smalltext' >".$row[1]."</a>\n";
				$nbr_forum_cat++;
				if($sql2 -> db_Select("forum","forum_id, forum_name, forum_threads, forum_replies, forum_class","forum_parent='".$row[0]."' ORDER BY forum_order ASC")){
						$texto .= "<br /><br /><div class='subcats' id='forum_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
						<b>".LANSM_10."</b><br />";
						while($row2 = $sql2 -> db_Fetch()){
							extract($row2);
							$row2[1] = $aj -> tpa($row2[1]);
							if(check_class($row2[4])){
								$texto .= "<a href=\"forum_viewforum.php?".$row2[0]."\" >".$row2[1]."</a> ".LANSM_39.": ".$row2[2]."/".$row2[3].")<br />\n";
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