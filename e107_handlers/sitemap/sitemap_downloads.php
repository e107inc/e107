<?php
/*
| ©Lolo Irie 2001-2004 (e107 Dev Team)
| http://etalkers.org
	
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org) for the e107 project.
*/
function sm_downloads() {
	$sql = new db;
	$sql2 = new db;
	$aj = new textparse;
	$texto .= "<div class='caption2' style='text-align: left;' >\n
		<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('downloads_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"download.php\" >".LANSM_12."</a> <b class='smalltext' >".LANSM_13."</b>\n
		</div><br />\n";
	 
	if ($sql->db_Select("download_category", "*", "download_category_parent='0' ORDER BY download_category_name ASC")) {
		$texto .= "<div class='cats' id='downloads_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
			<b>".LANSM_23."</b><br />\n";
		$nbr_downloads_cat = 0;
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$row[1] = $aj->tpa($row[1]);
			$row[2] = $aj->tpa($row[2]);
			if (check_class($row[5])) {
				$texto .= "<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('downloads_subcats_".$row[0]."');ejs_func_todo='view'\" class='smalltext' >".SM_ICO_EXP."</a> ".($row[3] != "" ? "<img src='".e_IMAGE."download_icons/".$row[3]."' alt='bullet' /> " : "" )."<a  href='download.php?' >".$row[1]."</a>\n";
				$nbr_downloads_cat++;
				if ($sql2->db_Select("download_category", "*", "download_category_parent='".$row[0]."' ORDER BY download_category_name ASC")) {
					$texto .= "<br /><br /><div class='subcats' id='downloads_subcats_".$row[0]."' style='display:none;' ><div style='margin: 0px 0px 0px 30px;' >\n
						<b>".LANSM_37."</b><br />";
					while ($row2 = $sql2->db_Fetch()) {
						extract($row2);
						$row2[1] = $aj->tpa($row2[1]);
						$row2[1] = $aj->tpa($row2[1]);
						if (check_class($row2[5])) {
							$texto .= ($row2[3] != "" ? "<img src='".e_IMAGE."download_icons/".$row2[3]."' alt='bullet' /> " : "" )."<a href=\"download.php?list.".$row2[0]."\" >".$row2[1]."</a><br />\n";
						}
					}
					$texto .= "<br /><br /></div></div>\n";
				} else {
					$texto .= "<br /><br />";
				}
			}
		}
		$texto .= "</div>";
		 
	}
	return $texto;
}
?>