<?php
/*
|	©Lolo Irie 2001-2004 (e107 Dev Team)
|	http://etalkers.org

|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org) for the e107 project.
*/
function sm_members(){
	$texto = "<div class='caption2' style='text-align: left;' >\n
	<a href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('members_cats');ejs_func_todo='view'\" >".SM_ICO_EXP."</a> <img src='".THEME."images/bullet2.gif' alt='bullet' /> <a class=\"sitemap2\"  href=\"javascript:void(0);\" onfocus=\"this.blur;\" onclick=\"expandit('members_cats');ejs_func_todo='view'\" >".LANSM_19."</a> <b class='smalltext' >".LANSM_41."</b>\n
	</div><br />\n";
		
	$texto .= "<div class='cats' id='members_cats' style='display:none; margin: 0px 0px 0px 10px;' >\n
	\n";
	
	// Links for guests
	if(!USER){$texto .= "<a href=\"signup.php\" >".LANSM_45."</a><br /><br />\n";}
	// Links for members
	if(USER){$texto .= "<a href=\"user.php\" >".LANSM_32."</a><br />\n";
	$texto .= "<a href=\"usersettings.php?".USERID."\" >".LANSM_21."</a><br />\n";
	$texto .= "<a href=\"userposts.php?0.comments.".USERID."\" >".LANSM_52."</a> <b class=\"smalltext\" >".LANSM_53."</b><br />\n";
	$texto .= "<a href=\"fpw.php\" >".LANSM_46."</a><br /><br />\n";
	}
	$texto .= "</div>";

	return $texto;
}
?>