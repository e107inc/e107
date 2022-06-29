<?php
/*
 * Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
 *
 * Navigation Template
 */

/* it should be templated this way:
- main........horizontal with dropdown 	and ul/li/a version, ul.navbar-nav + li.nav-item + a.nav-link
- main-alt....horizontal with dropdown 	and div/a version, div.navbar-nav + a.nav-link
- side........vertical with list-group 	and ul/li/a version, ul.list-group + li.list-group-item + a
- side-alt....vertical with list-group 	and div/a version, 	 div.list-group + a.list-group-item.list-group-item-action
- footer......horizontal with dropdown 	and ul/li/a version, ul.nav + li.nav-item + a.nav-link.
- footer-alt..horizontal with dropdown 	and nav/a version, nav.nav + a.nav-link
- alt.........vertical with flex-column and ul/li/a version, ul.nav.flex-column + li.nav-item + a.nav-link
- alt5........vertical with flex-column 	and nav/a version, nav.nav.flex-column + a.nav-link
- alt6........ul/li/a list
 */

// TEMPLATE FOR {NAVIGATION=main}
$NAVIGATION_TEMPLATE['main']['start'] = "<ul class='navbar-nav {NAV_CLASS}'>";

// Main Link
$NAVIGATION_TEMPLATE['main']['item'] = "<li class='nav-item'><a class='nav-link' role='button' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";

// Main Link - active state
$NAVIGATION_TEMPLATE['main']['item_active'] = "<li class='nav-item'><a class='nav-link active' aria-current='page' role='button' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";

$NAVIGATION_TEMPLATE['main']['end'] = '</ul>';

// Main Link which has a sub menu.
$NAVIGATION_TEMPLATE['main']['item_submenu'] = "
	<li class='nav-item dropdown'>
		<a class='nav-link dropdown-toggle' role='button' data-toggle='dropdown' data-bs-toggle='dropdown' id='navbarDropdownMenuLink-{NAV_LINK_ID}' data-target='#' aria-haspopup='true' aria-expanded='false' href='#' title='{NAV_LINK_DESCRIPTION}'>
		 {NAV_LINK_ICON}{NAV_LINK_NAME}
		</a>
		{NAV_LINK_SUB}
	</li>
	";

// Main Link which has a sub menu - active state.
$NAVIGATION_TEMPLATE['main']['item_submenu_active'] = '
	<li class="nav-item dropdown active {NAV_LINK_IDENTIFIER}">
		<a class="nav-link dropdown-toggle" role="button" data-toggle="dropdown" data-bs-toggle="dropdown" id="navbarDropdownMenuLink-{NAV_LINK_ID}" aria-haspopup="true" aria-expanded="true" data-target="#" href="#">
		 {NAV_LINK_ICON}{NAV_LINK_NAME}
		</a>
		{NAV_LINK_SUB}
	</li>
';

// Sub menu BUG: aria-labelledby= - it should be the same as navbarDropdownMenuLink-{NAV_LINK_ID} from submenu parent but LINK_PARENT returns 0
$NAVIGATION_TEMPLATE['main']['submenu_start'] = '<div class="dropdown-menu submenu-start submenu-level-{NAV_LINK_DEPTH}" aria-labelledby="navbarDropdownMenuLink-{NAV_LINK_PARENT}">';
$NAVIGATION_TEMPLATE['main']['submenu_item'] = '<a class="dropdown-item" href="{NAV_LINK_URL}" {NAV_LINK_OPEN}>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>';
$NAVIGATION_TEMPLATE['main']['submenu_item_active'] = '<a class="dropdown-item active" href="{NAV_LINK_URL}" {NAV_LINK_OPEN}>{NAV_LINK_ICON}{NAV_LINK_NAME}</a> ';
$NAVIGATION_TEMPLATE['main']['submenu_end'] = '</div>';

// 3rd Sub menu
$NAVIGATION_TEMPLATE['main']['submenu_lowerstart'] = '';
$NAVIGATION_TEMPLATE['main']['submenu_loweritem'] = $NAVIGATION_TEMPLATE['main']['submenu_item'];
$NAVIGATION_TEMPLATE['main']['submenu_loweritem_active'] = $NAVIGATION_TEMPLATE['main']['submenu_item_active'];
$NAVIGATION_TEMPLATE['main']['submenu_lowerend'] = '';

/*
ALTERNATIVE MARKUP for main navigation:
{NAVIGATION: layout=main-alt&type=any}
div/a version with dropdown, 2 levels
see https://getbootstrap.com/docs/4.5/components/navbar/#nav
 */

$NAVIGATION_TEMPLATE['main_alt'] = $NAVIGATION_TEMPLATE['main'];

$NAVIGATION_TEMPLATE['main-alt']['start'] = "<div class='navbar-nav {NAV_CLASS}'>";
$NAVIGATION_TEMPLATE['main-alt']['item'] = "<a class='nav-link' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE['main-alt']['item_active'] = "<a class='nav-link active' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE['main-alt']['end'] = '</div>';

/*
DEFAULT LIST-GROUP SIDE TEMPLATE FOR:
{NAVIGATION: layout=side&type=any}
{NAVIGATION=side};
ul/liv version with list-group, 2 levels
see https://getbootstrap.com/docs/4.1/components/list-group/#basic-example
 */

$NAVIGATION_TEMPLATE['side']['start'] = "<ul class='list-group {NAV_CLASS}' >";
$NAVIGATION_TEMPLATE['side']['item'] = "<li class='list-group-item'><a href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['side']['item_active'] = "<li class='list-group-item active'><a href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['side']['end'] = '</ul>';

// 2rd Sub menu
$NAVIGATION_TEMPLATE['side']['item_submenu'] = "<li class='list-group-item disabled'>{NAV_LINK_ICON}{NAV_LINK_NAME}{NAV_LINK_SUB}</li>";
$NAVIGATION_TEMPLATE['side']['item_submenu_active'] = "<li v>{NAV_LINK_ICON}{NAV_LINK_NAME}{NAV_LINK_SUB}</li>";
$NAVIGATION_TEMPLATE['side']['submenu_start'] = "";
$NAVIGATION_TEMPLATE['side']['submenu_item'] = "<li class='list-group-item'><a href='{NAV_LINK_URL}' {NAV_LINK_OPEN}>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['side']['submenu_item_active'] = "<li class='list-group-item active'><a href='{NAV_LINK_URL}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
// 3rd Sub menu
$NAVIGATION_TEMPLATE['side']['submenu_lowerstart'] = "";
$NAVIGATION_TEMPLATE['side']['submenu_loweritem'] = $NAVIGATION_TEMPLATE['side']['submenu_item'];
$NAVIGATION_TEMPLATE['side']['submenu_loweritem_active'] = $NAVIGATION_TEMPLATE['side']['submenu_item_active'];
$NAVIGATION_TEMPLATE['side']['submenu_lowerend'] = "";

/*
ALTERNATIVE LIST-GROUP SIDE TEMPLATE FOR:
{NAVIGATION: layout=side-alt&type=any}
div/a version with list-group, 2 levels
see https://getbootstrap.com/docs/4.1/components/list-group/#links-and-buttons
 */

$NAVIGATION_TEMPLATE['side-alt'] = $NAVIGATION_TEMPLATE['side'];

$NAVIGATION_TEMPLATE['side-alt']['start'] = "<div class='list-group {NAV_CLASS}' >";
$NAVIGATION_TEMPLATE['side-alt']['item'] = "<a class='list-group-item list-group-item-action' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";

$NAVIGATION_TEMPLATE['side-alt']['item_active'] = "<a class='list-group-item list-group-item-action active' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE['side-alt']['end'] = '</div>';

$NAVIGATION_TEMPLATE['side-alt']['item_submenu'] = "<a class='list-group-item list-group-item-action disabled'> {NAV_LINK_ICON}{NAV_LINK_NAME}</a>{NAV_LINK_SUB}";
$NAVIGATION_TEMPLATE['side-alt']['submenu_item'] = "<a class='list-group-item list-group-item-action' href='{NAV_LINK_URL}' {NAV_LINK_OPEN}>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE['side-alt']['submenu_item_active'] = "<a class='list-group-item list-group-item-action active' href='{NAV_LINK_URL}' {NAV_LINK_OPEN}>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";

/*
DEFAULT HORIZONTAL FOOTER TEMPLATE FOR:
{NAVIGATION: layout=footer&type=any}
{NAVIGATION=footer};
ul/li/a version - only 1-level
see https://getbootstrap.com/docs/4.1/components/navs/#base-nav
 */

$NAVIGATION_TEMPLATE['footer']['start'] = "<ul class='nav {NAV_CLASS}'>";
$NAVIGATION_TEMPLATE['footer']['item'] = "<li class='nav-item'>
 <a class='nav-link' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['footer']['item_active'] = "<li class='nav-item active' {NAV_LINK_OPEN}>
	<a class='nav-link' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['footer']['end'] = '</ul>';

// 2rd Sub menu data-toggle="dropdown" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Dropdown</a>
$NAVIGATION_TEMPLATE['footer']['item_submenu'] = "
<li class='nav-item dropdown'>
	<a class='nav-link dropdown-toggle' role='button' data-toggle='dropdown' data-bs-toggle='dropdown' data-target='#' aria-haspopup='true'
	 aria-expanded='false' href='#' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>
	{NAV_LINK_SUB}
</li>";
$NAVIGATION_TEMPLATE['footer']['item_submenu_active'] = "
<li class='nav-item dropdown active'>
	<a class='nav-link dropdown-toggle' role='button' data-toggle='dropdown' data-bs-toggle='dropdown' data-target='#' aria-haspopup='true' aria-expanded='false' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>
	 {NAV_LINK_ICON}{NAV_LINK_NAME}
	</a>
	{NAV_LINK_SUB}
</li>";

$NAVIGATION_TEMPLATE['footer']['submenu_start'] = "<div class='dropdown-menu'>";
$NAVIGATION_TEMPLATE['footer']['submenu_item'] = "<a class='dropdown-item' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE['footer']['submenu_item_active'] = "<a class='dropdown-item active' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE['footer']['submenu_end'] = "</div>";

// 3rd Sub menu
$NAVIGATION_TEMPLATE['footer']['submenu_lowerstart'] = "";
$NAVIGATION_TEMPLATE['footer']['submenu_loweritem'] = $NAVIGATION_TEMPLATE['footer']['submenu_item'];
$NAVIGATION_TEMPLATE['footer']['submenu_loweritem_active'] = $NAVIGATION_TEMPLATE['footer']['submenu_item_active'];
$NAVIGATION_TEMPLATE['footer']['submenu_lowerend'] = "";

/*
ALTERNATIVE HORIZONTAL FOOTER TEMPLATE FOR:
{NAVIGATION: layout=footer-alt&type=any}
nav/a -
see https://getbootstrap.com/docs/4.1/components/navs/#base-nav
 */

$NAVIGATION_TEMPLATE['footer-alt'] = $NAVIGATION_TEMPLATE['footer'];

$NAVIGATION_TEMPLATE["footer-alt"]["start"] = "<nav class='nav {NAV_CLASS}'>";
$NAVIGATION_TEMPLATE["footer-alt"]["item"] = "<a class='nav-link' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE["footer-alt"]["item_active"] = "<a class='nav-link active' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a>";
$NAVIGATION_TEMPLATE["footer-alt"]["end"] = "</nav>";

/*
DEFAULT VERTICAL TEMPLATE FOR:
{NAVIGATION: layout=alt&type=any}
ul/li/a version with nav-link and flex-column
see https://getbootstrap.com/docs/4.1/components/navs/#vertical
 */

$NAVIGATION_TEMPLATE['alt'] = $NAVIGATION_TEMPLATE['footer'];
$NAVIGATION_TEMPLATE['alt']['start'] = "<ul class='nav flex-column {NAV_CLASS}'>";

/*
ALTERNATIVE VERTICAL TEMPLATE FOR:
{NAVIGATION: layout=alt5&type=any}
nav/a version with nav-link and flex-column
see https://getbootstrap.com/docs/4.1/components/navs/#vertical
 */

$NAVIGATION_TEMPLATE['alt5'] = $NAVIGATION_TEMPLATE['footer-alt'];
$NAVIGATION_TEMPLATE['alt5']['start'] = "<nav class='nav flex-column {NAV_CLASS}'>";

/*
CLEAN TEMPLATE FOR:
{NAVIGATION: layout=alt6&type=any}
ul/li/a version without bootstrap classes
 */

$NAVIGATION_TEMPLATE['alt6']['start'] = "<ul class='{NAV_CLASS}'>";
$NAVIGATION_TEMPLATE['alt6']['item'] = "<li>
 <a href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['alt6']['item_active'] = "<li {NAV_LINK_OPEN}>
	<a class='active' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['alt6']['end'] = '</ul>';

$NAVIGATION_TEMPLATE['alt6']['item_submenu'] = "<li><a href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a> {NAV_LINK_SUB}</li>";
$NAVIGATION_TEMPLATE['alt6']['item_submenu_active'] = "
<li><a role='button' href='{NAV_LINK_URL}' title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME</a>{NAV_LINK_SUB}</li>";
$NAVIGATION_TEMPLATE['alt6']['submenu_start'] = "<ul>";
$NAVIGATION_TEMPLATE['alt6']['submenu_item'] = "<li><a href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['alt6']['submenu_item_active'] = "<li><a class='active' href='{NAV_LINK_URL}' {NAV_LINK_OPEN} title='{NAV_LINK_DESCRIPTION}'>{NAV_LINK_ICON}{NAV_LINK_NAME}</a></li>";
$NAVIGATION_TEMPLATE['alt6']['submenu_end'] = "</ul>";

// 3rd Sub menu
$NAVIGATION_TEMPLATE['alt6']['submenu_lowerstart'] = "";
$NAVIGATION_TEMPLATE['alt6']['submenu_loweritem'] = $NAVIGATION_TEMPLATE['footer']['submenu_item'];
$NAVIGATION_TEMPLATE['alt6']['submenu_loweritem_active'] = $NAVIGATION_TEMPLATE['footer']['submenu_item_active'];
$NAVIGATION_TEMPLATE['alt6']['submenu_lowerend'] = "";

$NAVIGATION_INFO['main']['title'] = 'Main - Top Default';
$NAVIGATION_INFO['main-alt']['title'] = 'Main - Top Alternative';
$NAVIGATION_INFO['side']['title'] = 'Side - List group';
$NAVIGATION_INFO['side-alt']['title'] = 'Side - List group Alternative';
$NAVIGATION_INFO['footer']['title'] = 'Footer - Horizontal Default';
$NAVIGATION_INFO['footer-alt']['title'] = 'Footer - Horizontal Alternative';
$NAVIGATION_INFO['alt']['title'] = 'Alt - Vertical Default';
$NAVIGATION_INFO['alt5']['title'] = 'Alt5 - Vertical Alternative';
$NAVIGATION_INFO['alt6']['title'] = 'Alt6 - Not styled list';
