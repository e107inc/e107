<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/search_menu/search_menu.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if (!defined('e107_INIT')) { exit; }

// include_lan(e_PLUGIN."search_menu/languages/".e_LANGUAGE.".php");


if (strpos(e_PAGE, "news.php") !== false) {
	 $page = 0;
} elseif(strpos(e_PAGE, "comment.php") !== false) {
	 $page = 1;
} elseif(strpos(e_PAGE, "content.php") !== false && strpos(e_QUERY, "content") !== false) {
	 $page = 2;
} elseif(strpos(e_PAGE, "content.php") !== false && strpos(e_QUERY, "review") !== false) {
	 $page = 3;
} elseif(strpos(e_PAGE, "content.php") !== false && strpos(e_QUERY, "content") !== false) {
	 $page = 4;
} elseif(strpos(e_PAGE, "chat.php") !== false) {
	 $page = 5;
} elseif(strpos(e_PAGE, "links.php") !== false) {
	 $page = 6;
} elseif(strpos(e_PAGE, "forum") !== false) {
	 $page = 7;
} elseif(strpos(e_PAGE, "user.php") !== false || strpos(e_PAGE, "usersettings.php") !== false) {
	 $page = 8;
} elseif(strpos(e_PAGE, "download.php") !== false) {
	 $page = 9;
} else {
	 $page = 99;
}

if (isset($custom_query[1]) && $custom_query[1] != '') 
{
	$image_file 	= ($custom_query[1] != 'default') ? $custom_query[1] : e_PLUGIN_ABS.'search_menu/images/search.png';
	$width 			= (isset($custom_query[2]) && $custom_query[2]) ? $custom_query[2] : '16';
	$height 		= (isset($custom_query[3]) && $custom_query[3]) ? $custom_query[3] : '16';
	$search_button 	= "<input type='image' src='".$image_file."' value='".LAN_SEARCH."' style='width: ".$width."px; height: ".$height."px; border: 0px; vertical-align: middle' name='s' />";
} 
else 
{
	$search_button = "<input class='btn btn-default btn-secondary button search' type='submit' name='s' value='".LAN_SEARCH."' />";
}

if (isset($custom_query[5]) && $custom_query[5]) {
	$value_text = "value='".$custom_query[5]."' onclick=\"this.value=''\"";
} else {
	$value_text = "value=''";
}

$search_form_url = e107::getUrl()->create('search');
	
if(deftrue('BOOTSTRAP'))
{
	$text = '
	<form class="form-inline" method="get" action="'.$search_form_url.'">
	<div class="input-group">
		<input class="form-control search" type="text" name="q" size="20" maxlength="50" '.$value_text.' />
		<input type="hidden" name="r" value="0" />';
	
	if (isset($custom_query[4]) && $custom_query[4] != '') 
	{
		$text .= "<input type='hidden' name='ref' value='".$custom_query[4]."' />";
	}	
		
	$text .= '
         <span class="input-group-btn">
         <button class="btn btn-default btn-secondary" type="submit" name="s">'.$tp->toGlyph('fa-search').'</button>
         </span>
    </div>
    </form>';
}	
else // Legacy v1 code. 
{
	$text = "<form class='form-inline' method='get' action='".$search_form_url."'>";

	$text .= "
	<div>
	<input class='tbox search' type='text' name='q' size='20' ".$value_text." maxlength='50' />
	<input type='hidden' name='r' value='0' />";
	
	
	
	if (isset($custom_query[4]) && $custom_query[4] != '') 
	{
		$text .= "<input type='hidden' name='ref' value='".$custom_query[4]."' />";
	}
	
	$text .= $search_button."
	</div>
	</form>";		
}
	
	
	
if (isset($searchflat) && $searchflat)
{
	echo $text;
}
else
{
	$ns->tablerender(LAN_SEARCH." ".SITENAME, "<div class='search-menu'>".$text."</div>", 'search');
}

