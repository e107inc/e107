<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
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




// include_lan(e_THEME."_blank/languages/".e_LANGUAGE.".php");



$E_ADMIN_NAVIGATION['start'] = '<ul class="nav navbar-nav navbar-left">';

$E_ADMIN_NAVIGATION['start_other'] = '<ul class="nav navbar-nav navbar-right">';

$E_ADMIN_NAVIGATION['button'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_TEXT} 
		<b class="caret"></b>
		</a> 
		{SUB_MENU}
	</li>
';



$E_ADMIN_NAVIGATION['button_active'] = '
	<li class="dropdown">
		<a class="dropdown-toggle"  role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}">
		 {LINK_IMAGE} {LINK_TEXT}
		<b class="caret"></b>
		</a>
		{SUB_MENU}
	</li>
';


// Leave Admin Area. 
$E_ADMIN_NAVIGATION['button_home'] = '
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
$E_ADMIN_NAVIGATION['button_language'] = '
	<li class="dropdown">
		<a class="dropdown-toggle" title="Change Language" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
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
$E_ADMIN_NAVIGATION['button_logout'] = '
	<li class="dropdown">
		<a class="dropdown-toggle" title="'.$label.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
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
		<ul class="dropdown-menu" role="menu" >
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

$inverse = (e107::getPref('admincss') == "admin_light.css") ? "navbar-inverse" : "";
    
    
 /*   
$ADMIN_HEADER = '<div class="navbar '.$inverse.' navbar-nav navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid"> 
        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a> 
          <a class="brand " href="'.e_ADMIN_ABS.'admin.php" title="Return to Front Panel"><img class="admin-logo" src="'.e_THEME_ABS.'bootstrap/images/e107_adminlogo.png" alt="e107" /></a>
          <div class="nav-collapse collapse">
            
            
			<div class="dropdown nav">
			{ADMIN_NAVIGATION=no-main}        	 
   			 </div>
   			 <div class="dropdown nav pull-right navbar-text ">
   			 <li>{ADMIN_COREUPDATE=icon}</li>
            {ADMIN_PM}
            {ADMIN_NAVIGATION=home}
			{ADMIN_NAVIGATION=language}
			{ADMIN_NAVIGATION=logout}
		
            </div>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>';
*/


/*
$ADMIN_MODAL =  '<div id="uiModal" class="modal hide fade" tabindex="-1" role="dialog"  aria-hidden="true">
            <div class="modal-header">
            	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
             	<h4 class="modal-caption">&nbsp;</h4>
             </div>
             <div class="modal-body">
             <p>Loading…</p>
             </div>
             <div class="modal-footer">
                <a href="#" data-dismiss="modal" class="btn btn-primary">Close</a>
            </div>
        </div>';*/


	$ADMIN_MODAL =  '<div id="uiModal" class="modal fade">
  <div id="admin-ui-modal" class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title modal-caption">&nbsp;</h4>
      </div>
      <div class="modal-body">
        <p>Loading...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>

      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';



//	 <li>{ADMIN_COREUPDATE=icon}</li>

 $ADMIN_HEADER = $ADMIN_MODAL. '

<div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container" >
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
         <a class="brand navbar-brand" href="'.e_ADMIN_ABS.'admin.php" title="Return to Front Panel"><img class="admin-logo" src="'.e_THEME_ABS.'bootstrap3/images/e107_adminlogo.png" alt="e107" /></a>
        </div>
        <div class="navbar-collapse collapse">

				{ADMIN_NAVIGATION=no-main}        	 
			<div>
				{ADMIN_NAVIGATION=logout}
				{ADMIN_NAVIGATION=language}
				 {ADMIN_NAVIGATION=home}
	            {ADMIN_PM}
	            {ADMIN_DEBUG}
			</div>

		  
		  
		  
        </div><!--/.navbar-collapse -->
      </div>
    </div>';
 
    
	
	
	
	
	
$ADMIN_HEADER .= '<div class="container-fluid">

      <div class="row">
        <div class="col-md-3 col-lg-2" id="left-panel">
        	{SETSTYLE=admin_menu}
		
			{ADMIN_MENU}

			
		
			{ADMIN_PWORD}
			{ADMIN_MENUMANAGER}

			<div class="e-scroll-fixed">

			
			{SETSTYLE=site_info}
			
			{ADMINUI_HELP}
			{ADMIN_HELP}

			</div>

			{ADMIN_SITEINFO=creditsonly}
			{SETSTYLE=admin_menu}
			
			{ADMIN_LATEST=infopanel}
			{ADMIN_STATUS=infopanel}
	
			{ADMIN_LOG=request}
			{ADMIN_MSG=request}
			{ADMIN_PLUGINS}
			
		
			
			{SETSTYLE=default}
			
         </div>
        <div class="col-md-9 col-lg-10" id="right-panel" >
         <div class="sidebar-toggle"><a href="#" title="Toggle Sidebar" data-toggle-sidebar="true">&nbsp;</a></div>
        	<div>
        
        ';


$ADMIN_FOOTER = '
		</div><!--/row-->
        </div><!--/span-->
      </div><!--/row-->
     

    </div><!--/.fluid-container-->
    <footer class="center mute"> 
		Copyright &copy; 2008-2015 e107 Inc (e107.org)<br />
      </footer>';



//{FS_ADMIN_ALT_NAV}
/*
$ADMIN_HEADER = "
<div class='admin-wrapper'>
	<div class='admin-header'>
		<div class='admin-header-content'>
			<div class='f-right'><!-- -->{ADMIN_LANG=nobutton&nomenu}</div>
			{ADMIN_LOGO}
			{ADMIN_LOGGED}
			{ADMIN_SEL_LAN}

		</div>
		<div style='height: 20px;'><!-- --></div>
		<div class='admin-navigation'>
			<div id='nav'>{ADMIN_NAVIGATION}</div>
			<div class='clear'><!-- --></div>
		</div>
	</div>
	<div class='admin-page-body'>
		<table class='main-table'>
			<tr>
			
				<td class='col-left'>
				
						{SETSTYLE=admin_menu}
						{ADMIN_MENU}
						{ADMIN_MENUMANAGER} 
						{ADMIN_PRESET}
						{ADMIN_LANG}
						{SETSTYLE=none}
						{ADMIN_PWORD}
						{ADMIN_STATUS=request}
						{ADMIN_LATEST=request}
						{ADMIN_LOG=request}
						{ADMIN_MSG}
						{ADMIN_PLUGINS}
						{ADMIN_UPDATE}
						
						{SETSTYLE=site_info}
						{ADMIN_SITEINFO}
						{ADMIN_HELP}
				
				
				
				
				</td>
				<td>
					<div class='col-main'>
						<div class='inner-wrapper'>
						{SETSTYLE=admin_content}
";
*/
/*
	{SETSTYLE=admin_menu}
	<!--
	{ADMIN_NAV}
	-->
		{ADMIN_LANG}

		{ADMIN_SITEINFO}

		{ADMIN_DOCS}
 */
 
/*
$ADMIN_FOOTER = "
						</div>
					</div>
				</td>
				<!--
				<td class='col-right'>
					<div class='col-right'>



					</div>
				</td>
				-->
			</tr>
		</table>
	</div>
	<div class='admin-footer'>
		<!-- -->
	</div>
</div>
";
*/
/* NEW ADMIN MENU TEMPLATE
 * see function e107::getNav()->admin() in e107_admin/header.php
 */
$E_ADMIN_MENU['start'] = '
<ul id="admin-ui-nav-menu" class="plugin-navigation nav nav-list">
';

$E_ADMIN_MENU['button'] = '
	<li>
		<a class="link{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>&nbsp;{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_MENU['button_active'] = '
	<li class="active">
		<a class="link-active{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}>&nbsp;{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';

$E_ADMIN_MENU['start_sub'] = '
		<ul class="plugin-navigation-sub{SUB_CLASS}"{SUB_ID}>
';

$E_ADMIN_MENU['button_sub'] = '
			<li>
				<a class="link" href="{LINK_URL}">&nbsp;{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';
$E_ADMIN_MENU['button_active_sub'] = '
			<li>
				<a class="link-active" href="{LINK_URL}">&nbsp;{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';

$E_ADMIN_MENU['end_sub'] = '
		</ul>
';

$E_ADMIN_MENU['end'] = '
</ul>
';

$E_ADMIN_MENU['divider'] = '<li role="separator" class="divider"></li>';


/* NEW ADMIN SLIDE DOWN MENU TEMPLATE
 * see function admin_navigation() in e107_files/shortcodes/admin_navigation.php
 * TODO move it together with menu.css/menu.js to the theme templates/e107_files folder (default menu render)
 */
 
 /*
$E_ADMIN_NAVIGATION['start'] = '
<ul id="nav nav-links">
';

$E_ADMIN_NAVIGATION['button'] = '
	<li>
		<a class="menuButton" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_NAVIGATION['button_active'] = '
	<li>
		<a class="menuButton active" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';

$E_ADMIN_NAVIGATION['start_sub'] = '
		<ul class="menu"{SUB_ID}>
';

$E_ADMIN_NAVIGATION['button_sub'] = '
			<li>
				<a class="menuItem{SUB_CLASS}" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';
$E_ADMIN_NAVIGATION['button_active_sub'] = '
			<li>
				<a class="menuItem{SUB_CLASS}" href="{LINK_URL}"{ONCLICK}>{LINK_IMAGE}{LINK_TEXT}</a>
				{SUB_MENU}
			</li>
';

$E_ADMIN_NAVIGATION['end_sub'] = '
		</ul>
';

$E_ADMIN_NAVIGATION['end'] = '
</ul>
';

  */
?>
