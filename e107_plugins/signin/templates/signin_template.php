<?php

// Do not use constants.. use {LAN=xxx} instead.
// Template compatible with Bootstrap 5 only.

$SIGNIN_TEMPLATE = [];


$SIGNIN_WRAPPER['signin']['SIGNIN_SIGNUP_HREF'] = '<li class="nav-item"><a class="nav-link" href="{---}">{LAN=LAN_LOGINMENU_3}</a></li>';

$SIGNIN_TEMPLATE['signin'] = '
			<ul class="navbar-nav nav-right">
				{SIGNIN_SIGNUP_HREF}
				<li class="divider-vertical"></li>
				<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" data-toggle="dropdown">{LAN=LAN_LOGINMENU_51} <strong class="caret"></strong></a>
					<div class="dropdown-menu col-sm-12" style="min-width:250px; padding: 15px; padding-bottom: 0px;">
					
					{SIGNIN_FORM=start}
					<p>{SIGNIN_INPUT_USERNAME}</p>
					<p>{SIGNIN_INPUT_PASSWORD}</p>
	
					<div class="form-group"></div>
					{SIGNIN_IMAGECODE_NUMBER}
					{SIGNIN_IMAGECODE_BOX}
					
					<div class="checkbox">		
					<label class="string optional" for="bs3-autologin"><input style="margin-right: 10px;" type="checkbox" name="autologin" id="bs3-autologin" value="1">
					{LAN=LAN_LOGINMENU_6}</label>
					</div>
					<div class="d-grid gap-2" style="padding-bottom:15px">
					<input class="btn btn-primary btn-block" type="submit" name="userlogin" id="bs3-userlogin" value="{LAN=LAN_LOGINMENU_51}">			
					<a href="{SIGNIN_FPW_HREF}" class="btn btn-default btn-secondary btn-sm  btn-block">{LAN=LAN_LOGINMENU_4}</a>
					<a href="{SIGNIN_RESEND_LINK=href}" class="btn btn-default btn-secondary btn-sm  btn-block">{LAN=LAN_LOGINMENU_40}</a>
					</div>
					{SIGNIN_FORM=end}
					</div>
				
				</li>
	
			</ul>';



$SIGNIN_WRAPPER['signout']['SIGNIN_ADMIN_HREF'] = '<li><a class="dropdown-item signin-sc admin" id="signin-sc-admin" href="{---}"><span class="fa fa-cogs"></span> {LAN=LAN_LOGINMENU_11}</a></li>';
$SIGNIN_WRAPPER['signout']['SIGNIN_PM_NAV'] = '<li class="dropdown dropdown-pm">{---}</li>';


$SIGNIN_TEMPLATE['signout'] = '

		<ul class="navbar-nav navbar-right">
			{SIGNIN_PM_NAV}
			<li class="dropdown dropdown-avatar"><a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-toggle="dropdown">{USER_AVATAR: w=30&h=30&crop=1&shape=circle} {SIGNIN_USERNAME} <b class="caret"></b></a>
				<ul class="dropdown-menu dropdown-menu-end">
				<li>
					<a class="dropdown-item" href="{SIGNIN_USERSETTINGS_HREF}"><span class="fa fa-cog"></span> {LAN=LAN_SETTINGS}</a>
				</li>
				<li>
					<a class="dropdown-item" role="button" href="{SIGNIN_PROFILE_HREF}"><span class="fa fa-user"></span> {LAN=LAN_LOGINMENU_13}</a>
				</li>
				<li class="divider"></li>
				{SIGNIN_ADMIN_HREF}
				<li><a class="dropdown-item" href="{SIGNIN_LOGOUT_HREF}"><span class="fa fa-power-off"></span> {LAN=LAN_LOGOUT}</a></li>
				</ul>
			</li>
		</ul>
		
		';

