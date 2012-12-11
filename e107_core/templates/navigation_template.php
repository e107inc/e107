<?php
/*
* Copyright (c) 2012 e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
* $Id: e_shortcode.php 12438 2011-12-05 15:12:56Z secretr $
*
* Gallery Template 
*/

  

$NAVIGATION_TEMPLATE['START'] = '<ul class="nav nav-pills">';

// Main Link
$NAVIGATION_TEMPLATE['ITEM'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" href="{LINK_URL}" >
		 {LINK_NAME} 
		</a> 
	</li>
';

// Main Link which has a sub menu. 
$NAVIGATION_TEMPLATE['ITEM_SUBMENU'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_NAME} 
		<b class="caret"></b>
		</a> 
		{LINK_SUB}
	</li>
';

$NAVIGATION_TEMPLATE['ITEM_SUBMENU_ACTIVE'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}">
		 {LINK_IMAGE} {LINK_NAME}
		<b class="caret"></b>
		</a>
		{LINK_SUB}
	</li>
';	

$NAVIGATION_TEMPLATE['ITEM_ACTIVE'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}">
		 {LINK_IMAGE} {LINK_NAME}
		</a>
	</li>
';	

$NAVIGATION_TEMPLATE['END'] = '</ul>';	


$NAVIGATION_TEMPLATE['SUBMENU_START'] = '
		<ul class="dropdown-menu" role="menu" >
';


$NAVIGATION_TEMPLATE['SUBMENU_ITEM'] = '
			<li role="menuitem" >
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
			</li>
';



$NAVIGATION_TEMPLATE['SUBMENU_ITEM_ACTIVE'] = '
			<li role="menuitem" class="active">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_NAME}</a>
			</li>
';

$NAVIGATION_TEMPLATE['SUBMENU_END'] = '</ul>';



?>