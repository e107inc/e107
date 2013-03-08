<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/

 
// TEMPLATE FOR {NAVIGATION=main}
$NAVIGATION_TEMPLATE['main']['start'] = '<ul class="nav nav-pills">';

// Main Link
$NAVIGATION_TEMPLATE['main']['item'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" href="{LINK_URL}" >
		 {LINK_NAME} 
		</a> 
	</li>
';

// Main Link which has a sub menu. 
$NAVIGATION_TEMPLATE['main']['item_submenu'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_NAME} 
		<b class="caret"></b>
		</a> 
		{LINK_SUB}
	</li>
';

$NAVIGATION_TEMPLATE['main']['item_submenu_active'] = '
	<li class="dropdown active">
		<a class="dropdown-toggle" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}">
		 {LINK_IMAGE} {LINK_NAME}
		<b class="caret"></b>
		</a>
		{LINK_SUB}
	</li>
';	

$NAVIGATION_TEMPLATE['main']['item_active'] = '
	<li class="dropdown active">
		<a class="dropdown-toggle" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}">
		 {LINK_IMAGE} {LINK_NAME}
		</a>
	</li>
';	

$NAVIGATION_TEMPLATE['main']['end'] = '</ul>';	


$NAVIGATION_TEMPLATE['main']['submenu_start'] = '
		<ul class="dropdown-menu" role="menu" >
';


$NAVIGATION_TEMPLATE['main']['submenu_item'] = '
			<li role="menuitem" >
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
				{LINK_SUB}
			</li>
';

$NAVIGATION_TEMPLATE['main']['submenu_loweritem'] = '
			<li role="menuitem" class="dropdown-submenu">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
				{LINK_SUB}
			</li>
';

$NAVIGATION_TEMPLATE['main']['submenu_loweritem_active'] = '
			<li role="menuitem" class="dropdown-submenu active">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
				{LINK_SUB}
			</li>
';

$NAVIGATION_TEMPLATE['main']['submenu_item_active'] = '
			<li role="menuitem" class="active">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
				{LINK_SUB}
			</li>
';

$NAVIGATION_TEMPLATE['main']['submenu_end'] = '</ul>';


// TEMPLATE FOR {NAVIGATION=side}

$NAVIGATION_TEMPLATE['side']['start'] 				= '<ul class="nav nav-list"><li class="nav-header">Sidebar</li>
														';

$NAVIGATION_TEMPLATE['side']['item'] 				= '<li><a href="{LINK_URL}">{LINK_NAME}</a></li>
														';

$NAVIGATION_TEMPLATE['side']['item_submenu'] 		= '<li class="nav-header">{LINK_NAME}{LINK_SUB}</li>
														';

$NAVIGATION_TEMPLATE['side']['item_active'] 		= '<li class="active"><a href="{LINK_URL}">{LINK_NAME}</a></li>
														';

$NAVIGATION_TEMPLATE['side']['end'] 				= '</ul>
														';

$NAVIGATION_TEMPLATE['side']['submenu_start'] 		= '';

$NAVIGATION_TEMPLATE['side']['submenu_item']		= '<li><a href="{LINK_URL}">{LINK_NAME}</a></li>';

$NAVIGATION_TEMPLATE['side']['submenu_loweritem'] = '
			<li role="menuitem" class="dropdown-submenu">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
				{LINK_SUB}
			</li>
';

$NAVIGATION_TEMPLATE['side']['submenu_item_active'] = '<li class="active"><a href="{LINK_URL}">{LINK_NAME}</a></li>';

$NAVIGATION_TEMPLATE['side']['submenu_end'] 		= '';


$NAVIGATION_TEMPLATE['footer'] 						= $NAVIGATION_TEMPLATE['side'];
$NAVIGATION_TEMPLATE['alt'] 						= $NAVIGATION_TEMPLATE['side'];

?>