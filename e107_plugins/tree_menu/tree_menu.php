<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|     /tree_menu.php
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_plugins/tree_menu/tree_menu.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-01-26 12:24:15 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

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

include(e_LANGUAGEDIR.e_LANGUAGE."/lan_sitelinks.php");

// Many thanks to Lolo Irie for fixing the javascript that drives this menu item
unset($text);

$sql -> db_Select("links", "*", "ORDER BY link_order", "nowhere");	// get main category links
$linkArray = $sql -> db_getList();

// all main links now held in array, we now need to loop through them and assign the sublinks to the correct parent links ...

$mainLinkArray = array();
foreach($linkArray as $links)
{
	extract ($links);
	if(check_class($link_class))
	{
		if(!strstr($link_name, "submenu"))
		{
			// main link - add to main array ...
			$mainLinkArray[$link_name]['id'] = $link_id;
			$mainLinkArray[$link_name]['name'] = strip_tags($link_name);
			$mainLinkArray[$link_name]['url'] = $link_url;
			$mainLinkArray[$link_name]['description'] = $link_description;
			$mainLinkArray[$link_name]['image'] = $link_button;
			$mainLinkArray[$link_name]['openMethod'] = $link_open;
			$mainLinkArray[$link_name]['class'] = $link_class;
		}
		else
		{
			// submenu - add to parent's array entry ...
			list($null, $parent_name, $submenu_name) = explode(".", $link_name);	 // get parent name ...
			$mainLinkArray[$parent_name]['sublink'][$link_id]['parent_name'] = $parent_name;
			$mainLinkArray[$parent_name]['sublink'][$link_id]['id'] = $link_id;
			$mainLinkArray[$parent_name]['sublink'][$link_id]['name'] = strip_tags($submenu_name);
			$mainLinkArray[$parent_name]['sublink'][$link_id]['url'] = $link_url;
			$mainLinkArray[$parent_name]['sublink'][$link_id]['description'] = $link_description;
			$mainLinkArray[$parent_name]['sublink'][$link_id]['image'] = $link_button;
			$mainLinkArray[$parent_name]['sublink'][$link_id]['openMethod'] = $link_open;
			$mainLinkArray[$parent_name]['sublink'][$link_id]['class'] = $link_class;
		}
	}
}

// ok, now all mainlinks and sublinks are held in the array, now we have to loop through and build the text to send to screen ...

$text = "";
foreach($mainLinkArray as $links)
{
	extract ($links);
	if(array_key_exists("sublink", $links))
	{
		// sublinks found ...
		$url = "javascript: void(0);";
		$spanName = str_replace(" ","_",$name);
		$image = ($image ? "<img src='".e_IMAGE."link_icons/".$image."' alt='' style='vertical-align:middle;' />" : "&raquo;");
		$text .= "<div class='spacer'>\n<div class='button' style='width:100%; cursor: pointer;' onclick='expandit(\"span_".$spanName."\");updatecook(\"".$name."\");'>".$image.setLink($name, $url, $openMethod, $description)."</div>\n</div>\n";
	}
	else
	{
		// no sublinks found ...
		$linkName = $url;
		$spanName = "";
		$image = ($image ? "<img src='".e_IMAGE."link_icons/".$image."' alt='' style='vertical-align:middle;' />" : "&middot;");
		$text .= "<div class='spacer'>\n<div class='button' style='width:100%; cursor: pointer;'>".$image.setLink($name, $url, $openMethod, $description)."</div>\n</div>\n";
	}

	$c=0;
	if(array_key_exists("sublink", $links))
	{
		$text .= "<span style=\"display:none\" id=\"span_".$spanName."\">\n";
		foreach($sublink as $link)
		{
			extract($link);
			$image = ($image ? "<img src='".e_IMAGE."link_icons/".$image."' alt='' style='vertical-align:middle' />  " : "&middot; ");
			$spanName = str_replace(" ","_",$parent_name);

			$text .= $image.setLink($name, $url, $openMethod, $description)."<br />\n";
		}
		$text .= "</span>\n";
	}

}

function setlink($link_name, $link_url, $link_open, $link_description)
{
	switch ($link_open)
	{
		case 1:
			$link_append = "rel='external'";
		break;
		case 2:
			$link_append = "";
		break;
		case 3:
			$link_append = "";
		break;
		default:
			unset($link_append);
		}
		if(!strstr($link_url, "http:"))
		{
			$link_url = e_BASE.$link_url;
		}
		if($link_open == 4)
		{
			$link =  "<a style='text-decoration:none' title='".$link_description."' href=\"javascript:open_window('".$link_url."')\">".$link_name."</a>\n";
		}
		else
		{
			$link =  "<a style='text-decoration:none' title='".$link_description."' href=\"".$link_url."\" ".$link_append.">".$link_name."</a>\n";
		}
        return $link;
}

($_COOKIE["treemenustatus"] ? $treemenustatus = $_COOKIE["treemenustatus"] : $treemenustatus="0");

$text .= "
<script type='text/javascript'>
<!--
function updatecook(itemmenu){
        cookitem='span_'+itemmenu;
        if(document.getElementById(cookitem).style.display!='none'){
                var expireDate = new Date;
                expireDate.setMinutes(expireDate.getMinutes()+10);
                document.cookie = \"treemenustatus=\" + itemmenu + \"; expires=\" + expireDate.toGMTString();
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

(($treemenustatus!="0" && isset($treemenustatus))?$text .= "window.onload=document.getElementById('span_".$treemenustatus."').style.display=''":"");

$text .= "</script>
";
$ns -> tablerender(LAN_183, $text, 'tree_menu');

?>