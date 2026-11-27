<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * token_get_all() with TOKEN_PARSE
 */

namespace e107\Shims\Internal;

/**
 *
 */
trait ParseErrorTrait
{
	/**
	 * Whether PHP can parse $code, through {@see token_get_all()} with TOKEN_PARSE, which PHP 5.6 lacks; nothing in $code runs.
	 * On PHP 7.0 to 7.2 a compile error the parser raises itself, such as a doubled modifier, still ends the request, as including the file would.
	 *
	 * @param string $code PHP source as a file holds it, opening tag included
	 * @return string|null '' when PHP can parse it, PHP's parse or compile error and its line when it cannot, null when this PHP cannot tell (5.6, or no tokenizer extension)
	 */
	public static function parse_error($code)
	{
		if(!defined('TOKEN_PARSE'))
		{
			return null;
		}

		$error = null;

		try
		{
			token_get_all((string) $code, TOKEN_PARSE);
		}
		catch(\ParseError $e)
		{
			$error = $e;
		}
		catch(\CompileError $e)
		{
			$error = $e;
		}

		return $error ? $error->getMessage().' on line '.$error->getLine() : '';
	}
}
