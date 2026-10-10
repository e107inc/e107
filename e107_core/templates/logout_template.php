<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Logout confirmation template; e107\User\LogoutConfirmation wraps the body in the form that submits it and fills {LOGOUT_CANCEL_URL}.
 *
*/

if (!defined('e107_INIT')) { exit; }


$LOGOUT_TEMPLATE['confirm']['caption'] = LAN_LOGOUT;

$LOGOUT_TEMPLATE['confirm']['body'] = "
	<p>".defset('LAN_LOGOUT_CONFIRM_QUESTION', 'Are you sure you want to log out?')."</p>
	<button type='submit' class='btn btn-primary button'>{LAN=LAN_LOGOUT}</button>
	<a class='btn btn-default btn-secondary button' href='{LOGOUT_CANCEL_URL}'>{LAN=LAN_CANCEL}</a>
";
