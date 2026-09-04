<?php

// Do not use constants.. use {LAN=xxx} instead.
// Template compatible with Bootstrap 5 only.

$SIGNIN_TEMPLATE = [];


$SIGNIN_WRAPPER['signin']['SIGNIN_SIGNUP_HREF'] = '<li class="nav-item"><a class="nav-link" href="{---}">{LAN=LAN_SIGNIN_SIGNUP}</a></li>';

$SIGNIN_TEMPLATE['signin'] = '
			<ul class="navbar-nav nav-right">
				{SIGNIN_SIGNUP_HREF}
				<li class="divider-vertical"></li>
				<li class="nav-item dropdown">
				<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" data-toggle="dropdown" role="button" aria-expanded="false">{LAN=LAN_SIGNIN_SIGNIN} <strong class="caret" aria-hidden="true"></strong></a>
					<div class="dropdown-menu dropdown-menu-end col-sm-12" style="min-width:250px; padding: 15px; padding-bottom: 0px;">
					
					{SIGNIN_FORM=start}
					<p>{SIGNIN_INPUT_USERNAME}</p>
					<p>{SIGNIN_INPUT_PASSWORD}</p>
	
					<div class="form-group"></div>
					{SIGNIN_IMAGECODE_NUMBER}
					{SIGNIN_IMAGECODE_BOX}
					
					<div class="d-grid gap-2" style="padding-bottom:15px">
					<input class="btn btn-primary btn-block" type="submit" name="userlogin" id="bs3-userlogin" value="{LAN=LAN_SIGNIN_SIGNIN}">			
					<a href="{SIGNIN_FPW_HREF}" class="btn btn-default btn-secondary btn-sm  btn-block">{LAN=LAN_SIGNIN_FPW}</a>
					<a href="{SIGNIN_RESEND_LINK=href}" class="btn btn-default btn-secondary btn-sm  btn-block">{LAN=LAN_SIGNIN_RESEND}</a>
					</div>
					{SIGNIN_FORM=end}
					</div>
				
				</li>
	
			</ul>';



$SIGNIN_WRAPPER['signout']['SIGNIN_ADMIN_HREF'] = '<li><a class="dropdown-item signin-sc admin" id="signin-sc-admin" href="{---}"><span class="fa fa-cogs" aria-hidden="true"></span> {LAN=LAN_SIGNIN_ADMIN}</a></li>';
$SIGNIN_WRAPPER['signout']['SIGNIN_PM_NAV'] = '<li class="dropdown dropdown-pm">{---}</li>';


$SIGNIN_TEMPLATE['signout'] = '

		<ul class="navbar-nav navbar-right">
			{SIGNIN_PM_NAV}
			<li class="dropdown dropdown-avatar"><a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" data-toggle="dropdown" role="button" aria-expanded="false">{USER_AVATAR: w=30&h=30&crop=1&shape=circle} {SIGNIN_USERNAME} <b class="caret" aria-hidden="true"></b></a>
				<ul class="dropdown-menu dropdown-menu-end">
				<li>
					<a class="dropdown-item" href="{SIGNIN_USERSETTINGS_HREF}"><span class="fa fa-cog" aria-hidden="true"></span> {LAN=LAN_SETTINGS}</a>
				</li>
				<li>
					<a class="dropdown-item" role="button" href="{SIGNIN_PROFILE_HREF}"><span class="fa fa-user" aria-hidden="true"></span> {LAN=LAN_SIGNIN_PROFILE}</a>
				</li>
				<li class="divider"></li>
				{SIGNIN_ADMIN_HREF}
				<li><a class="dropdown-item" href="{SIGNIN_LOGOUT_HREF}"><span class="fa fa-power-off" aria-hidden="true"></span> {LAN=LAN_LOGOUT}</a></li>
				</ul>
			</li>
		</ul>
		
		';