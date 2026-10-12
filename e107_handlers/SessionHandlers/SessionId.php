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
 * The characters a session id may have.
 */
final class SessionId
{
	/**
	 * The characters, as the inside of a regular expression's character class.
	 */
	const CHARACTERS = '0-9a-zA-Z,-';

	/**
	 * @param mixed $id
	 * @return bool whether $id is a non-empty string of those characters alone, and so safe in a file name
	 */
	public static function isWellFormed($id)
	{
		return is_string($id) && 1 === preg_match('#^['.self::CHARACTERS.']+$#', $id);
	}
}
