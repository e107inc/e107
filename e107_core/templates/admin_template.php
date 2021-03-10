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



$ADMIN_TEMPLATE['nav']['start'] = '<ul class="nav nav-admin navbar-nav navbar-left">';

$ADMIN_TEMPLATE['nav']['start_other'] = '<ul class="nav nav-admin navbar-nav navbar-right">';

$ADMIN_TEMPLATE['nav']['button'] = '
	<li class="dropdown">
		<a class="dropdown-toggle navbar-admin-button"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" title="{LINK_DESCRIPTION}">
		 {LINK_IMAGE}
		{LINK_TEXT}
		</a> 
		{SUB_MENU}
	</li>
';



$ADMIN_TEMPLATE['nav']['button_active'] = '
	<li class="dropdown active">
		<a class="dropdown-toggle navbar-admin-button"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" title="{LINK_DESCRIPTION}">
		{LINK_IMAGE}
		{LINK_TEXT}
		</a>
		{SUB_MENU}
	</li>
';




// Leave Admin Area. 
$ADMIN_TEMPLATE['nav']['button_enav_home'] = '
	<li class="dropdown admin-nav-home">
		<a class="dropdown-toggle nav-home-main" title="'.ADLAN_53.'" href="'.e_HTTP.'" >
		 {LINK_IMAGE} {LINK_TEXT} 
		 </a><a class="dropdown-toggle nav-home-caret" title="'.ADLAN_53.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 <b class="caret"></b>
		</a>
		{SUB_MENU}
	</li>
';

// Change Language
$ADMIN_TEMPLATE['nav']['button_enav_language'] = '
	<li class="dropdown admin-nav-language">
		<a class="dropdown-toggle" title="'.LAN_CHANGE_LANGUAGE.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		  {LINK_TEXT} 
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
$ADMIN_TEMPLATE['nav']['button_enav_logout'] = '
	<li class="dropdown admin-nav-logout">
		<a class="dropdown-toggle admin-icon-avatar " title="'.$label.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_IMAGE} {LINK_TEXT} 
		<b class="caret"></b>
		</a> 
		{SUB_MENU}
	</li>
';


// Private Messaging - //TODO Discuss and make this work.. 
$ADMIN_TEMPLATE['nav']['button_pm'] = '
	<li class="dropdown">
		<a class="dropdown-toggle" title="Messages" role="button" data-toggle="dropdown" href="#" >
		<i class="icon-envelope active"></i> 3
		<b class="caret"></b>
		</a> 
		<div id="dropdown" class="dropdown-menu pull-right dropdown-menu-end float-right e-noclick" style="padding:10px;width:300px;">
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



$ADMIN_TEMPLATE['nav']['button_other'] = '
	<li>
		<a  role="button" href="{LINK_URL}" >
		{LINK_TEXT} 
		</a> 
	</li>
';

$ADMIN_TEMPLATE['nav']['start_sub'] = '
		<ul class="dropdown-menu {LINK_SUB_OVERSIZED}" role="menu" >
';

$ADMIN_TEMPLATE['nav']['start_other_sub'] = '
		<ul class="dropdown-menu dropdown-menu-end pull-right float-right" role="menu" >
';

$ADMIN_TEMPLATE['nav']['button_sub'] = '
			<li role="menuitem" class="{LINK_CLASS}">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_TEXT}</a>
			</li>
';
$ADMIN_TEMPLATE['nav']['button_active_sub'] = '
			<li role="menuitem" class="active">
				<a href="{LINK_URL}">{LINK_IMAGE}{LINK_TEXT}</a>
			</li>
';

$ADMIN_TEMPLATE['nav']['end_sub'] = '</ul>';

$ADMIN_TEMPLATE['nav']['end'] = '</ul>';

/*
   <div class="admin-navigation">
			<div id="nav">{ADMIN_NAVIGATION}</div>
			<div class="clear"><!-- --></div>
		</div>
 */

// $inverse = (e107::getPref('admincss') == "admin_light.css") ? "navbar-inverse" : "";
    

$ADMIN_TEMPLATE['modal'] = '
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
				<button type="button" id="e-modal-submit" class="btn btn-success" style="display:none;" data-loading-icon="fa-spinner"><!-- placeholder --></button>
				<button type="button" class="btn btn-primary" data-dismiss="modal">'.LAN_CLOSE.'</button>
			</div>
		</div>
	</div>
</div>
';


$ADMIN_TEMPLATE['header'] =  '
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
				<img class="admin-logo" src="'.e_THEME_ABS.'bootstrap3/images/logo.webp" alt="e107"  />
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
	<div class="row is-table-row {ADMIN_LEFTPANEL_TOGGLE}">
';

$adminstyle = e107::getConfig()->get('adminstyle', 'infopanel');
if(defset('e_PAGE') == 'admin.php' && $adminstyle == 'flexpanel' && varset($_GET['mode']) != 'customize')
{
	$ADMIN_TEMPLATE['header'] .= '
		<div class="col-sm-12">
			<div class="admin-main-content is-table-row {ADMIN_LEFTPANEL_TOGGLE}">
	';
}
else
{

	$ADMIN_TEMPLATE['header'] .= '
		<div class="admin-left-panel hidden-print col-md-3 col-lg-2">
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
			<div class="sidebar-toggle-panel">
	
			{ADMIN_SITEINFO=creditsonly}

			{SETSTYLE=lists}
			{ADMIN_LATEST=infopanel}
			{ADMIN_STATUS=infopanel}
				{SETSTYLE=admin_menu}

			{ADMIN_LOG=request}
			{ADMIN_MSG=request}
			{ADMIN_PLUGINS}
		
			<!--<div class="admin-copyright"><small>Copyright &copy; 2008-2017 e107.org</small></div>-->
			</div>
			{SETSTYLE=default}			
		</div>
		<div class="admin-right-panel col-md-9 col-lg-10">
			<div class="admin-main-content">
	';
}

// TODO - LANs
$ADMIN_TEMPLATE['footer'] = '
			</div>
		</div>
	</div><!--/.row-->
</div><!--/.fluid-container-->


';


/* NEW ADMIN MENU TEMPLATE
 * see function e107::getNav()->admin() in e107_admin/header.php
 */
$ADMIN_TEMPLATE['menu']['start'] = '
<div class="nav-panel-body">
<ul id="admin-ui-nav-menu" class="plugin-navigation nav nav-pills nav-stacked">
';

$ADMIN_TEMPLATE['menu']['button'] = '
	<li>
		<a class="link{LINK_CLASS}" {LINK_DATA} href="{LINK_URL}" {ID}{ONCLICK}><span class="e-tip" data-placement="right" title="{LINK_TEXT}">{LINK_IMAGE}</span><span class="sidebar-toggle-panel">&nbsp;{LINK_TEXT}{LINK_BADGE}</span></a>
		{SUB_MENU}
	</li>
';
$ADMIN_TEMPLATE['menu']['button_active'] = '
	<li class="active">
		<a class="link-active{LINK_CLASS}" {LINK_DATA} href="{LINK_URL}" {ID}{ONCLICK}><span class="e-tip" data-placement="right" title="{LINK_TEXT}">{LINK_IMAGE}</span><span class="sidebar-toggle-panel">&nbsp;{LINK_TEXT}{LINK_BADGE}</span></a>
		{SUB_MENU}
	</li>
';

$ADMIN_TEMPLATE['menu']['start_sub'] = '
		<ul class="plugin-navigation-sub{SUB_CLASS}" {SUB_ID}>
';

$ADMIN_TEMPLATE['menu']['button_sub'] = '
			<li>
				<a class="link" href="{LINK_URL}">&nbsp;{LINK_TEXT}{LINK_BADGE}</a>
				{SUB_MENU}
			</li>
';
$ADMIN_TEMPLATE['menu']['button_active_sub'] = '
			<li>
				<a class="link-active" href="{LINK_URL}">&nbsp;{LINK_TEXT}{LINK_BADGE}</a>
				{SUB_MENU}
			</li>
';

$ADMIN_TEMPLATE['menu']['end_sub'] = '
		</ul>
';

$ADMIN_TEMPLATE['menu']['end'] = '
</ul>
</div>
';

$ADMIN_TEMPLATE['menu']['heading'] = '<li class="nav-header sidebar-toggle-panel">{HEADING}</li>';

$ADMIN_TEMPLATE['menu']['divider'] = '<li role="separator" class="divider"><!-- --></li>';

$ADMIN_TEMPLATE['menu']['caption'] = '<span class="e-toggle-sidebar e-tip"  data-placement="right" title="Toggle" style="cursor:pointer">{ICON}</span><span class="sidebar-toggle-panel">{CAPTION}</span><span class="close e-toggle-sidebar sidebar-toggle-panel sidebar-toggle-switch">×</span>';
