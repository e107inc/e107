<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Admin template - _blank theme
 *
 * $Source: /cvs_backup/e107_0.8/e107_themes/_blank/admin_template.php,v $
 * $Revision$
 * $Date$
 * $Author$
 *
*/

if (!defined('e107_INIT')) { exit(); }

//e107::lan('theme', 'admin',true);


$E_ADMIN_NAVIGATION['start'] = '<ul class="nav nav-admin navbar-nav navbar-left">';

$E_ADMIN_NAVIGATION['start_other'] = '<ul class="nav nav-admin navbar-nav navbar-right">';

$E_ADMIN_NAVIGATION['button'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" title="{LINK_TEXT}">
		 {LINK_IMAGE} <span class="hidden-lg">{LINK_TEXT}</span>

		</a> 
		{SUB_MENU}
	</li>
';



$E_ADMIN_NAVIGATION['button_active'] = '
	<li class="dropdown active">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" title="{LINK_TEXT}">
		 {LINK_IMAGE}  <span class="hidden-lg">{LINK_TEXT}</span>

		</a>
		{SUB_MENU}
	</li>
';




// Leave Admin Area. 
$E_ADMIN_NAVIGATION['button_enav_home'] = '
	<li class="dropdown">
		<a class="dropdown-toggle" style="display:inline-block; margin-right:0;" title="'.ADLAN_53.'" href="'.e_HTTP.'" >
		 {LINK_IMAGE} {LINK_TEXT} 
		 </a><a style="display:inline-block;border-left:0;margin-left:0;padding-left:4px" class="dropdown-toggle" title="'.ADLAN_53.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 <b class="caret"></b>
		</a>
		{SUB_MENU}
	</li>
';

// Change Language
$E_ADMIN_NAVIGATION['button_enav_language'] = '
	<li class="dropdown">
		<a class="dropdown-toggle" title="'.LAN_CHANGE_LANGUAGE.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_IMAGE} {LINK_TEXT} 
		<b class="caret"></b>
		</a> 
		{SUB_MENU}
	</li>
';

			$str = str_replace('.', '', ADMINPERMS);
			
			if ($str == '0')
			{
				$label = ADLAN_48.': '.ADMINNAME.' ('.ADLAN_49.') ';
			}
			else
			{
				$label = ADLAN_48.': '.ADMINNAME.' ';
			}	


// Logout / Settings / Personalize 			
$E_ADMIN_NAVIGATION['button_enav_logout'] = '
	<li class="dropdown">
		<a class="dropdown-toggle admin-icon-avatar " title="'.$label.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_IMAGE} {LINK_TEXT} 
		<b class="caret"></b>
		</a> 
		{SUB_MENU}
	</li>
';


// Private Messaging - //TODO Discuss and make this work.. 
$E_ADMIN_NAVIGATION['button_pm'] = '
	<li class="dropdown">
		<a class="dropdown-toggle" title="Messages" role="button" data-toggle="dropdown" href="#" >
		<i class="icon-envelope" class="active"></i> 3
		<b class="caret"></b>
		</a> 
		<div id="dropdown" class="dropdown-menu pull-right e-noclick" style="padding:10px;width:300px">
		    <ul class="nav-list">
	    <li class="dropdown-header nav-header">Unread Messages</li>
	    <li><a href="#">Incoming Message Number 1</a></li>
	      <li><a href="#">Incoming Message Number 2</a></li>
	        <li><a href="#">Incoming Message Number 3</a></li>
	         <li class="divider"></li>
	    </ul>
		<textarea class="e-tip input-block-level" title="Example Only"></textarea>
		<button class="dropdown-toggle btn btn-primary" >Send</button>	
		</div>
	</li>
';



$E_ADMIN_NAVIGATION['button_other'] = '
	<li>
		<a  role="button" href="{LINK_URL}" >
		{LINK_TEXT} 
		</a> 
	</li>
';

$E_ADMIN_NAVIGATION['start_sub'] = '
		<ul class="dropdown-menu {LINK_SUB_OVERSIZED}" role="menu" >
';

$E_ADMIN_NAVIGATION['start_other_sub'] = '
		<ul class="dropdown-menu pull-right" role="menu" >
';

$E_ADMIN_NAVIGATION['button_sub'] = '
			<li role="menuitem" class="{LINK_CLASS}">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_TEXT}</a>
			</li>
';
$E_ADMIN_NAVIGATION['button_active_sub'] = '
			<li role="menuitem" class="active">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_TEXT}</a>
			</li>
';

$E_ADMIN_NAVIGATION['end_sub'] = '</ul>';

$E_ADMIN_NAVIGATION['end'] = '</ul>';

/*
   <div class="admin-navigation">
			<div id="nav">{ADMIN_NAVIGATION}</div>
			<div class="clear"><!-- --></div>
		</div>
 */

// $inverse = (e107::getPref('admincss') == "admin_light.css") ? "navbar-inverse" : "";
    

$ADMIN_MODAL = '
<div id="uiModal" class="modal fade">
	<div id="admin-ui-modal" class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title modal-caption">&nbsp;</h4>
			</div>
			<div class="modal-body">
				<p>'.LAN_LOADING.'</p>
			</div>
			<div class="modal-footer">
				<button type="button" id="e-modal-submit" class="btn btn-success" style="display:none" data-loading-icon="fa-spinner"><!-- placeholder --></button>
				<button type="button" class="btn btn-primary" data-dismiss="modal">'.LAN_CLOSE.'</button>
			</div>
		</div>
	</div>
</div>
';


$ADMIN_HEADER = $ADMIN_MODAL . '
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="brand navbar-brand" href="'.e_ADMIN_ABS.'admin.php" title="'.LAN_RETURN_TO_FRONT_PANEL.'">
				<img class="admin-logo" src="'.e_THEME_ABS.'bootstrap3/images/e107_adminlogo.png" alt="e107"/>
			</a>
		</div>
		<div class="navbar-collapse collapse">
			{ADMIN_NAVIGATION=no-main}
			{ADMIN_NAVIGATION=enav_popover}
			<div>
				{ADMIN_NAVIGATION=enav_logout}
				{ADMIN_NAVIGATION=enav_language}
				{ADMIN_NAVIGATION=enav_home}
				{ADMIN_MULTISITE}
				{ADMIN_PM}
				{ADMIN_DEBUG}
				{ADMIN_UPDATE}
			</div>
		</div>
	</div>
</div>
<div class="admin-container container-fluid">
	<div class="row is-table-row">
';

$adminstyle = e107::getConfig()->get('adminstyle', 'infopanel');
if(defset('e_PAGE') == 'admin.php' && $adminstyle == 'flexpanel' && varset($_GET['mode']) != 'customize')
{
	$ADMIN_HEADER .= '
		<div class="col-sm-12">
			<div class="admin-main-content is-table-row">
	';
}
else
{

	$ADMIN_HEADER .= '
		<div class="col-md-3 col-lg-2 admin-left-panel hidden-print">
			{SETSTYLE=warning}
			{ADMIN_ADDON_UPDATES}
			{SETSTYLE=site_info}
			{ADMIN_PWORD}
			{SETSTYLE=admin_menu}
			{ADMIN_MENU}


			{ADMIN_MENUMANAGER}
	

				{SETSTYLE=site_info}
				{ADMINUI_HELP}
				{ADMIN_HELP}

	
			{ADMIN_SITEINFO=creditsonly}

			{SETSTYLE=lists}
			{ADMIN_LATEST=infopanel}
			{ADMIN_STATUS=infopanel}
				{SETSTYLE=admin_menu}

			{ADMIN_LOG=request}
			{ADMIN_MSG=request}
			{ADMIN_PLUGINS}
		
			<!--<div class="admin-copyright"><small>Copyright &copy; 2008-2017 e107.org</small></div>-->
		
			{SETSTYLE=default}			
		</div>
		<div class="col-md-9 col-lg-10 admin-right-panel">
			<div class="sidebar-toggle">
				<a href="#" title="Toggle Sidebar" data-toggle-sidebar="true">&nbsp;</a>
			</div>
			<div class="admin-main-content">
	';
}

// TODO - LANs
$ADMIN_FOOTER = '
			</div>
		</div>
	</div><!--/.row-->
</div><!--/.fluid-container-->


';


/* NEW ADMIN MENU TEMPLATE
 * see function e107::getNav()->admin() in e107_admin/header.php
 */
$E_ADMIN_MENU['start'] = '
<div class="nav-panel-body">
<ul id="admin-ui-nav-menu" class="plugin-navigation nav nav-pills nav-stacked">
';

$E_ADMIN_MENU['button'] = '
	<li>
		<a class="link{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>&nbsp;{LINK_TEXT}{LINK_BADGE}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_MENU['button_active'] = '
	<li class="active">
		<a class="link-active{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>&nbsp;{LINK_TEXT}{LINK_BADGE}</a>
		{SUB_MENU}
	</li>
';

$E_ADMIN_MENU['start_sub'] = '
		<ul class="plugin-navigation-sub{SUB_CLASS}"{SUB_ID}>
';

$E_ADMIN_MENU['button_sub'] = '
			<li>
				<a class="link" href="{LINK_URL}">&nbsp;{LINK_TEXT}{LINK_BADGE}</a>
				{SUB_MENU}
			</li>
';
$E_ADMIN_MENU['button_active_sub'] = '
			<li>
				<a class="link-active" href="{LINK_URL}">&nbsp;{LINK_TEXT}{LINK_BADGE}</a>
				{SUB_MENU}
			</li>
';

$E_ADMIN_MENU['end_sub'] = '
		</ul>
';

$E_ADMIN_MENU['end'] = '
</ul>
</div>
';

$E_ADMIN_MENU['divider'] = '<li role="separator" class="divider"></li>';


?>