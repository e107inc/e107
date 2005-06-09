<?php

global $sc_style, $recent_shortcodes;
$sc_style['RECENT_DATE']['pre'] = "";
$sc_style['RECENT_DATE']['post'] = " ";

$sc_style['RECENT_ICON']['pre'] = "";
$sc_style['RECENT_ICON']['post'] = " ";

$sc_style['RECENT_HEADING']['pre'] = "";
$sc_style['RECENT_HEADING']['post'] = " ";

$sc_style['RECENT_AUTHOR']['pre'] = RECENT_MENU_2.": ";
$sc_style['RECENT_AUTHOR']['post'] = " ";

$sc_style['RECENT_CATEGORY']['pre'] = RECENT_MENU_4.": ";
$sc_style['RECENT_CATEGORY']['post'] = " ";

$sc_style['RECENT_INFO']['pre'] = "";
$sc_style['RECENT_INFO']['post'] = " ";


//MENU TEMPLATE -------------------------------------------------------------------------
$RECENT_MENU_START = "
<div class='fcaption' style='padding:2px; cursor:pointer; border:0px solid #000;' onclick='expandit(this);'>{RECENT_CAPTION}</div>
<div class='forumheader2' style='padding:2px; display:{RECENT_DISPLAYSTYLE}; border:0px solid #000; border-top:0px solid #000;'>\n";
$RECENT_MENU = "
<div>
{RECENT_ICON} {RECENT_DATE} {RECENT_HEADING} {RECENT_AUTHOR} {RECENT_CATEGORY}
</div>";
$RECENT_MENU_END = "
</div><div style='height:5px;'></div>\n";



//PAGE TEMPLATE -------------------------------------------------------------------------
$RECENT_PAGE_START = "
<div class='fcaption' style='padding:2px; cursor:pointer; border:1px solid #000;' onclick='expandit(this);'>{RECENT_CAPTION}</div>
<div class='forumheader3' style='padding:2px; display:{RECENT_DISPLAYSTYLE}; border:1px solid #000; border-top:0px solid #000;'>\n";
$RECENT_PAGE = "
<div style='border:0px solid #000;'>
{RECENT_ICON}
{RECENT_DATE}
{RECENT_HEADING}
{RECENT_AUTHOR}
{RECENT_CATEGORY}
{RECENT_INFO}
</div>";
$RECENT_PAGE_END = "
</div><div style='margin-bottom:20px;'></div>\n";



//MULTI COLOMNS LAYOUT MASTER -----------------------------------------------------------
$RECENT_PAGE_TABLE_START = "
<div style='text-align:center'>
<table class='fborder' style='width:100%; border:1px solid #000;' border='1' cellspacing='0' cellpadding='0'>
<tr>";
$RECENT_PAGE_TABLE_WELCOME = "<td colspan='{RECENT_PAGE_TABLE_COLS}' class='forumheader'>{RECENT_PAGE_TABLE_WELCOMETEXT}<br /><br /></td>";
$RECENT_PAGE_TABLE_ROWSWITCH = "</tr><tr>";
$RECENT_PAGE_TABLE_CELL_START = "<td style='width:{RECENT_PAGE_TABLE_CELLWIDTH}%; padding-right:5px;'>";
$RECENT_PAGE_TABLE_CELL_END = "</td>";
$RECENT_PAGE_TABLE_END = "</tr></table></div>";


?>