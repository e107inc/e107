<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\SessionHandlers;

/**
 * New session ids for core's save handlers, which PHP asks for by name on every version e107 runs on.
 *
 * @internal Only the handlers in this namespace use it.
 */
trait CreateSidTrait
{
	/**
	 * @return string|false
	 */
	#[\ReturnTypeWillChange]
	public function create_sid()
	{
		return \eShims::session_create_id();
	}
}
