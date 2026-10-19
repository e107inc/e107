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
 * Answers one route; e107's own contract, not PSR-15's, though handle() mirrors its name.
 */
interface RequestHandlerInterface
{
	/**
	 * @param Request $request
	 * @return Response
	 */
	public function handle(Request $request);
}
