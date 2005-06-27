<?php

global $sc_style, $list_shortcodes;
$sc_style['LIST_DATE']['pre'] = "";
$sc_style['LIST_DATE']['post'] = " ";

$sc_style['LIST_ICON']['pre'] = "";
$sc_style['LIST_ICON']['post'] = " ";

$sc_style['LIST_HEADING']['pre'] = "";
$sc_style['LIST_HEADING']['post'] = " ";

$sc_style['LIST_AUTHOR']['pre'] = LIST_MENU_2.": ";
$sc_style['LIST_AUTHOR']['post'] = " ";

$sc_style['LIST_CATEGORY']['pre'] = LIST_MENU_4.": ";
$sc_style['LIST_CATEGORY']['post'] = " ";

$sc_style['LIST_INFO']['pre'] = "";
$sc_style['LIST_INFO']['post'] = " ";


//LIST_MENU_NEW TEMPLATE -------------------------------------------------------------------------
$LIST_MENU_NEW_START = "
<div class='fcaption' style='cursor:pointer;' onclick='expandit(this);'>{LIST_CAPTION}</div>
<div class='forumheader' style='margin-bottom:5px; display:{LIST_DISPLAYSTYLE};'>\n";
$LIST_MENU_NEW = "
<div>
{LIST_ICON} {LIST_DATE} {LIST_HEADING} {LIST_AUTHOR} {LIST_CATEGORY}
</div>";
$LIST_MENU_NEW_END = "
</div>\n";

//LIST_MENU_RECENT TEMPLATE -------------------------------------------------------------------------
$LIST_MENU_RECENT_START = "
<div class='fcaption' style='cursor:pointer;' onclick='expandit(this);'>{LIST_CAPTION}</div>
<div class='forumheader' style='margin-bottom:5px; display:{LIST_DISPLAYSTYLE};'>\n";
$LIST_MENU_RECENT = "
<div>
{LIST_ICON} {LIST_DATE} {LIST_HEADING} {LIST_AUTHOR} {LIST_CATEGORY}
</div>";
$LIST_MENU_RECENT_END = "
</div>\n";


//PAGE TEMPLATE -------------------------------------------------------------------------
$LIST_PAGE_RECENT_START = "
<div class='fcaption' style='cursor:pointer;' onclick='expandit(this);'>{LIST_CAPTION}</div>
<div class='forumheader' style='margin-bottom:10px; display:{LIST_DISPLAYSTYLE};'>\n";
$LIST_PAGE_RECENT = "
<div>
{LIST_ICON} {LIST_DATE} {LIST_HEADING} {LIST_AUTHOR} {LIST_CATEGORY} {LIST_INFO}
</div>";
$LIST_PAGE_RECENT_END = "
</div>\n";


//NEW TEMPLATE -------------------------------------------------------------------------
$LIST_PAGE_NEW_START = "
<div class='fcaption' style='cursor:pointer;' onclick='expandit(this);'>{LIST_CAPTION}</div>
<div class='forumheader' style='margin-bottom:10px; display:{LIST_DISPLAYSTYLE};'>\n";
$LIST_PAGE_NEW = "
<div>
{LIST_ICON} {LIST_DATE} {LIST_HEADING} {LIST_AUTHOR} {LIST_CATEGORY} {LIST_INFO}
</div>";
$LIST_PAGE_NEW_END = "
</div>\n";


//MULTI COLOMNS LAYOUT MASTER -----------------------------------------------------------
$LIST_COL_START = "
<div style='text-align:center;'>
<table class='fborder' style='width:100%;' cellspacing='0' cellpadding='0'>
<tr>";
$LIST_COL_WELCOME = "<td colspan='{LIST_COL_COLS}' class='forumheader'>{LIST_COL_WELCOMETEXT}<br /><br /></td>";
$LIST_COL_ROWSWITCH = "</tr><tr>";
$LIST_COL_CELL_START = "<td style='width:{LIST_COL_CELLWIDTH}%; padding-right:5px; vertical-align:top;'>";
$LIST_COL_CELL_END = "</td>";
$LIST_COL_END = "</tr></table></div>";

//TIMELAPSE SELECT -----------------------------------------------------------
$LIST_TIMELAPSE_TABLE = "<div class='forumheader3' style='margin-bottom:20px;'>{LIST_TIMELAPSE}</div>";

?>