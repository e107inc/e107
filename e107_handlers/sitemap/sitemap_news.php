<?php
/*
|	©Lolo Irie 2001-2004 (e107 Dev Team)
|	http://etalkers.org

|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org) for the e107 project.
*/
function sm_news(){
	$sql = new db;
	$sql2 = new db;
	$aj = new textparse;
	if($sql -> db_Select("news","news_id")){

		$texto = "<div class='caption2' style='text-align: left;' >\n
		<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('news_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"news.php\" >".LANSM_5."</a> <b class='smalltext' >".LANSM_6."</b>\n
		</div><br />\n";
		
		if($sql -> db_Select("news_category","*","category_id!='-1' ORDER BY category_name")){
			$texto .= "<div class='cats' id='news_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
			<b>".LANSM_23."</b><br />\n";
			$nbr_news_cat = 0;
			while($row = $sql -> db_Fetch()){
				extract($row);
				$row[1] = $aj -> tpa($row[1]);
				$texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('news_subcats_".$row[0]."');ejs_func_todo='view';\" class='smalltext' >".SM_ICO_EXP."</a> <img src='".e_IMAGE."link_icons/".$row[2]."' alt='bullet' /> <a  href='news.php?cat.".$row[0]."' >".$row[1]."</a>\n";
				$nbr_news_cat++;
				if($sql2 -> db_Select("news","news_id, news_title, news_datestamp, news_class","news_category='".$row[0]."' ORDER BY news_datestamp DESC")){
						$texto .= "<br /><br /><div class='subcats' id='news_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
					<b>".LANSM_38."</b><br />";
						while($row2 = $sql2 -> db_Fetch()){
							extract($row2);
							$row2[1] = $aj -> tpa($row2[1]);
							if(check_class($row2[3])){
								$news_date = new convert;
								$news_date = $news_date->convert_date($row2[2], "short");
								$texto .= "<a href=\"news.php?item.".$row2[0]."\" >".$row2[1]."</a> ".$news_date."<br />\n";
							}
						}
						$texto .= "<br /><br /></div></div>\n";
				}else{
					$texto .= "<br /><br />";
				}
				
			}
			$texto .= "</div>";
			
		}
	}
	return $texto;
}
?>