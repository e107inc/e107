<?php
/*
* e107 website system
*
* Copyright (C) 2008-2010 e107 Inc (e107.org)
* Released under the terms and conditions of the
* GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
*
* Tree menu
*
* $URL$
* $Id$
*
*/

if (!defined('e107_INIT')) { exit; }

/* Modification to keep menu status during navigation on the site
- Call the language file (only used for title !!! Maybe this title can be included in the default language file)
- Add a HTML id to the span tags (menus) : span_$link_name
- Add a javascript function to write a cookie when menu is opened (updatecook)
- Add a javascript function if menu is closed or no subitem (clearcook)
- Add event onclick for div without subitem, and modify the existing events for items WITH subitems
- Add a PHP function to read cookie (if existing) when page is loaded and restore menu status (writing or not window.onload js function)
*/

/* changes by jalist
26/01/2005
+ complete rewrite
+ now uses single db query, links and sublinks are built into array
*/


global $tp;
include_lan(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");

// Many thanks to Lolo Irie for fixing the javascript that drives this menu item
unset($text);

$query = "SELECT * FROM #links WHERE link_class IN (".USERCLASS_LIST.") ORDER BY link_order ASC";
$sql -> db_Select_gen($query);
$linkArray = $sql->db_getList();

// all main links now held in array, we now need to loop through them and assign the sublinks to the correct parent links ...

$mainLinkArray = array();
foreach($linkArray as $links) 
{
// Updated to stop using the deprecated method of splitting the link-name in 3.
// Now uses uses the link parent to determine the 'tree'.

	extract ($links);
		if ($link_parent == 0)
		{
			// main link - add to main array ...
			$mainLinkArray[$link_id]['id'] = $link_id;
			$mainLinkArray[$link_id]['name'] = $tp->toHtml(strip_tags($link_name),"","defs");
			$mainLinkArray[$link_id]['url'] = $link_url;
			$mainLinkArray[$link_id]['description'] = $link_description;
			$mainLinkArray[$link_id]['image'] = $link_button;
			$mainLinkArray[$link_id]['openMethod'] = $link_open;
			$mainLinkArray[$link_id]['class'] = $link_class;
		}
		else
		{
			// submenu - add to parent's array entry ...
			$tmp = explode(".", $link_name);
			$submenu_name = ($tmp[2]) ? $tmp[2] : $link_name;

			$mainLinkArray[$link_parent]['sublink'][$link_id]['parent_name'] = $link_parent;
			$mainLinkArray[$link_parent]['sublink'][$link_id]['id'] = $link_id;
			$mainLinkArray[$link_parent]['sublink'][$link_id]['name'] = $tp->toHtml(strip_tags($submenu_name),"","defs");
			$mainLinkArray[$link_parent]['sublink'][$link_id]['url'] = $link_url;
			$mainLinkArray[$link_parent]['sublink'][$link_id]['description'] = $links['link_description'];
			$mainLinkArray[$link_parent]['sublink'][$link_id]['image'] = $link_button;
			$mainLinkArray[$link_parent]['sublink'][$link_id]['openMethod'] = $link_open;
			$mainLinkArray[$link_parent]['sublink'][$link_id]['class'] = $link_class;
		}

}

// ok, now all mainlinks and sublinks are held in the array, now we have to loop through and build the text to send to screen ...

$text = "";
foreach($mainLinkArray as $links) {
	extract ($links);
	if (array_key_exists("sublink", $links) && $links['name'] != "") {
		// sublinks found ...

		$url = "javascript:void(0);";
		$spanName = $id;
		$image = ($image ? "<img src='".e_IMAGE_ABS."icons/".$image."' alt='' style='vertical-align:middle;' />" : "&raquo;");
		$plink = "<div".($menu_pref['tm_class2'] ? " class='{$menu_pref['tm_class2']}'" : "")." style='width:100%; cursor: pointer;' onclick='expandit(\"span_".$spanName."\");updatecook(\"".$spanName."\");'>".$image." ".setLink($name, $url, $openMethod, $description)."</div>\n";
		$text .= ($menu_pref['tm_spacer'] ? "<div class='spacer'>\n".$plink."\n</div>\n" : $plink);
	} else {
		// no sublinks found ...
		if($links['name'])
		{
			$linkName = $url;
			$spanName = "";
			$image = ($image ? "<img src='".e_IMAGE_ABS."icons/".$image."' alt='' style='vertical-align:middle;' />" : "&middot;");
			$plink = "<div".($menu_pref['tm_class1'] ? " class='{$menu_pref['tm_class1']}'" : "")." style='width:100%; cursor: pointer;'>".$image." ".setLink($name, $url, $openMethod, $description)."</div>";
			$text .= ($menu_pref['tm_spacer'] ? "<div class='spacer'>\n".$plink."\n</div>\n" : $plink);
		}
	}

	$c = 0;
	if (array_key_exists("sublink", $links) && $links['name'] != "" ) {

		$text .= "\n<span style=\"display:none\" id=\"span_".$spanName."\">\n";
		foreach($sublink as $link) {
			extract($link);
			$image = ($image ? "<img src='".e_IMAGE_ABS."icons/".$image."' alt='' style='vertical-align:middle' />  " : "&middot; ");
			$spanName = $parent_name;

			$plink = $image." ".setLink($name, $url, $openMethod, $description)."<br />\n";
			$text .=($menu_pref['tm_class3'] ? "<span".($menu_pref['tm_class3'] ? " class='{$menu_pref['tm_class3']}'" : "").">".$plink."</span>\n\n" : $plink);
		}
		$text .= "</span>\n";
	}

}

function setlink($link_name, $link_url, $link_open, $link_description) 
{
	global $tp;
	if (!strstr($link_url, "http:") && !strstr($link_url, "void") && strpos($link_url, "mailto:") !== 0) 
	{
		$link_url = e_BASE.$link_url;
	}
	$link_url =	$tp->replaceConstants($link_url, $nonrelative = TRUE, $all = false);
    $href = " href='".$link_url."'";
	switch ($link_open) 
	{
		case 1:
		$link_append = " rel='external'";
		break;
		case 2:
		$link_append = "";
		break;
		case 3:
		$link_append = "";
		break;
		case 4 :
		case 5 :
            $dimen = ($link_open == 4) ? '600,400' : '800,600';
            $href = " href=\"javascript:open_window('".$link_url."',{$dimen})\"";
			break;
		default:
		$link_append = '';
	}

	$link = "<a style='text-decoration:none' title='".$link_description."'{$link_append}{$href} >".$link_name."</a>\n";
	return $link;
}



(isset($_COOKIE["treemenustatus"]) && $_COOKIE["treemenustatus"]) ? $treemenustatus = $_COOKIE["treemenustatus"] : $treemenustatus = "0";
$text .= "
	<script type='text/javascript'>
	<!--
	function updatecook(itemmenu){
	cookitem='span_'+itemmenu;
	if (document.getElementById(cookitem).style.display!='none'){
	var expireDate = new Date;
	expireDate.setMinutes(expireDate.getMinutes()+10);
	document.cookie = \"treemenustatus=\" + itemmenu + \"; expires=\" + expireDate.toGMTString()+\";path=/\";
	}
	else{
	clearcook();
	}
	}\n

	function clearcook(){
	var expireDate = new Date;
	expireDate.setMinutes(expireDate.getMinutes()+10);
	document.cookie = \"treemenustatus=\" + \"0\" + \"; expires=\" + expireDate.toGMTString();
	}\n
	//-->\n
	";

(($treemenustatus != "0" && isset($treemenustatus))?$text .= "window.onload=document.getElementById('span_".$treemenustatus."').style.display=''":"");

$text .= "</script>
	";
$ns->tablerender(LAN_SITELINKS_183, $text, 'tree_menu');

?>