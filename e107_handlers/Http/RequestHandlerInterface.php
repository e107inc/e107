<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Http;

/**
 * Answers one route.
 */
interface RequestHandlerInterface
{
	/**
	 * @param Request $request
	 * @return Response
	 */
	public function handle(Request $request);
}
