<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Routing;

use e107\Http\Request;
use e107\Http\RequestHandlerInterface;
use e107\Http\Response;

/**
 * A handler that answers every request with a redirect home.
 */
final class RedirectHomeHandler implements RequestHandlerInterface
{
	public function handle(Request $request)
	{
		return Response::redirect('/');
	}
}
