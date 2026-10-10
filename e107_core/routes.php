<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Core routes in the order they are tried, each with the factory that composes its handler.
 *
 */

if (!defined('e107_INIT')) { exit; }

return call_user_func(function ()
{
	return array(
		\e107\Routing\Route::queryWord('user/logout', \e107\User\LogoutConfirmation::QUERY_WORD, function ()
		{
			$redirect = e107::getRedirect();
			$tp = e107::getParser();

			return new \e107\User\LogoutConfirmation(
				e107::getUser()->isUser(),
				e107::getCoreTemplate('logout', 'confirm', true, true),
				e_HTTP.'index.php',
				e107::getSession()->getFormToken(),
				SITEURL,
				function ($url) use ($redirect) { return !$redirect->leavesThisSite($url); },
				function ($markup, array $vars) use ($tp) { return $tp->parseTemplate($markup, true, null, new e_vars($vars)); }
			);
		}),
	);
});
