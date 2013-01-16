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



$E_ADMIN_NAVIGATION['start'] = '<ul class="nav nav-pills">';

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
		<a class="dropdown-toggle" title="'.ADLAN_53.'" role="button" data-toggle="dropdown" data-target="#" href="{LINK_URL}" >
		 {LINK_IMAGE} {LINK_TEXT} 
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
		<i class="icon-envelope icon-white" class="active"></i> 3
		<b class="caret"></b>
		</a> 
		<div id="dropdown" class="dropdown-menu pull-right e-noclick" style="padding:10px;width:300px">
		    <ul class="nav-list">
	    <li class="nav-header">Unread Messages</li>
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
 
   
$ADMIN_HEADER = '<div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">  
          <a class="brand " href="'.e_ADMIN_ABS.'admin.php" title="Return to Front Panel"><img class="admin-logo" src="'.THEME.'images/e107_adminlogo.png" alt="e107" /></a>
          <div class="nav-collapse collapse">
            <div class="dropdown nav pull-right navbar-text ">
            {ADMIN_PM}
            {ADMIN_NAVIGATION=home}
			{ADMIN_NAVIGATION=language}
			{ADMIN_NAVIGATION=logout}
		
            </div>
            
			<div class="dropdown nav">
			{ADMIN_NAVIGATION=no-main}
			
			<!--
				<ul class="nav" role="navigation">
                    <li class="dropdown">
                      <a id="drop1" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Dropdown <b class="caret"></b></a>
                      <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
                        <li><a href="http://google.com">Action</a></li>
                        <li><a href="#anotherAction">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li><a href="#">Separated link</a></li>
                      </ul>
                    </li>
                    <li class="dropdown">
                      <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown">Dropdown 2 <b class="caret"></b></a>
                      <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
                        <li><a tabindex="-1" href="#">Action</a></li>
                        <li><a tabindex="-1" href="#">Another action</a></li>
                        <li><a tabindex="-1" href="#">Something else here</a></li>
                        <li class="divider"></li>
                        <li><a tabindex="-1" href="#">Separated link</a></li>
                      </ul>
                    </li>
                  </ul>
             -->    
          
        	 
   			 </div>
          </div><!--/.nav-collapse -->
        </div>
      </div>
    </div>';

$ADMIN_HEADER .= '<div class="container-fluid">
      <div class="row-fluid">
        <div class="span2">
        	{SETSTYLE=admin_menu}
		
			{ADMIN_MENU}
			{ADMIN_MENUMANAGER} 
			{SETSTYLE=site_info}
		
			{ADMIN_PWORD}
			{ADMIN_HELP}
			{ADMIN_STATUS=request}
			{ADMIN_LATEST=request}
			{ADMIN_LOG=request}
			{ADMIN_MSG}
			{ADMIN_PLUGINS}
			
		
			{SETSTYLE=site_info}
			{ADMIN_SITEINFO}
			{SETSTYLE=none}
			
         </div>
        <div class="span10">';


$ADMIN_FOOTER = '
        </div><!--/span-->
      </div><!--/row-->

      <hr>

      <footer class="center"> 
		Copyright &copy; 2008-2012 e107 Inc (e107.org)<br />
      </footer>

    </div><!--/.fluid-container-->';



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
<ul class="plugin-navigation nav nav-list">
';

$E_ADMIN_MENU['button'] = '
	<li>
		<a class="link{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}><i class="icon-chevron-right"></i>&nbsp;{LINK_TEXT}</a>
		{SUB_MENU}
	</li>
';
$E_ADMIN_MENU['button_active'] = '
	<li class="active">
		<a class="link-active{LINK_CLASS}" href="{LINK_URL}"{ID}{ONCLICK}><i class="icon-chevron-right"></i>&nbsp;{LINK_TEXT}</a>
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