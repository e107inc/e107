<?php
/*
| ©Lolo Irie 2001-2004 (e107 Dev Team)
| http://etalkers.org
	
| Released under the terms and conditions of the
| GNU General Public License (http://gnu.org) for the e107 project.
*/
function sm_content() {
	$sql = new db;
	$sql2 = new db;
	$aj = new textparse;
	$texto .= "<div class='caption2' style='text-align: left;' >\n
		<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('contents_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"content.php\" >".LANSM_4."</a> <b class='smalltext' >".LANSM_36."</b>\n
		</div><br />\n";
	 
	if ($sql->db_Select("content", "content_id, content_heading, content_type, content_class", "content_type='1' ORDER BY content_heading ASC")) {
		$texto .= "<div class='cats' id='contents_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
			<b>".LANSM_38."</b><br />\n";
		$nbr_contents_cat = 0;
		while ($row = $sql->db_Fetch()) {
			extract($row);
			$row[1] = $aj->tpa($row[1]);
			if (check_class($row[3])) {
				$texto .= "<a  href='content.php?content.".$row[0]."' >".$row[1]."</a><br />\n";
				$nbr_contents_cat++;
			}
		}
		$texto .= "<br /></div>";
		 
	}
	return $texto;
}
?>