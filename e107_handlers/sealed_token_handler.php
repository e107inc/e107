<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * v2 compatibility layer for the e107 sealed token API.
 *
 * The implementation lives in the namespaced classes under
 * e107_handlers/Security/ (the e107\Security tree), which new code should
 * reference directly; the e107 namespaced autoloader picks them up with no
 * registration. Loading this file registers the v2-style class names as true
 * aliases, so `e_sealed_token::provision()`,
 * `catch (e_sealed_token_exception ...)`, type hints and string class names
 * all behave identically to the namespaced names.
 *
 * Only the two public names are aliased. The format internals under
 * e107\Security\Jwe\ and e107\Security\Cipher\ get no v2 name at all,
 * because nothing written before v3 can be naming them and a later move to
 * another token format has to be free to delete them.
 *
 * The namespaced files are required directly rather than autoloaded so the
 * aliases also work in bootstrap contexts that run without the e107
 * autoloader (the installer, MYSQL_LIGHT), and in dependency order so each
 * file's own require_once calls are already satisfied when it is read.
 *
 * See random_handler.php for the same pattern applied to the CSPRNG API.
 */

if(!defined('e107_INIT'))
{
	exit;
}

require_once(__DIR__ . '/Security/RandomException.php');
require_once(__DIR__ . '/Security/Random.php');
require_once(__DIR__ . '/Security/Hkdf.php');
require_once(__DIR__ . '/Security/Cipher/CbcCipherInterface.php');
require_once(__DIR__ . '/Security/Cipher/CipherFactory.php');
require_once(__DIR__ . '/Security/Jwe/AesCbcHmacSha2.php');
require_once(__DIR__ . '/Security/Jwe/Compact.php');

require_once(__DIR__ . '/Security/SealedTokenException.php');
class_alias(\e107\Security\SealedTokenException::class, 'e_sealed_token_exception');

require_once(__DIR__ . '/Security/SealedToken.php');
class_alias(\e107\Security\SealedToken::class, 'e_sealed_token');
