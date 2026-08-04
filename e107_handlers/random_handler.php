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
 * v2 compatibility layer for the e107 CSPRNG API.
 *
 * The implementation lives in the namespaced classes under
 * e107_handlers/Security/ (the e107\Security tree), which new code should
 * reference directly; the e107 namespaced autoloader picks them up with no
 * registration. Loading this file registers the v2-style class names as true
 * aliases, so `e_random::hex()`, `catch (e_random_exception ...)`, type hints
 * and string class names all behave identically to the namespaced names.
 *
 * The namespaced files are required directly rather than autoloaded so the
 * aliases also work in bootstrap contexts that run without the e107
 * autoloader (the installer, MYSQL_LIGHT).
 *
 * Kept API-identical to the `e_random` class on release/v2.3.x so a fix can
 * be moved between the branches unchanged. See e_db_interface.php for the
 * same pattern applied to the SQL API.
 */

if(!defined('e107_INIT'))
{
	exit;
}

require_once(__DIR__ . '/Security/RandomException.php');
class_alias(\e107\Security\RandomException::class, 'e_random_exception');

require_once(__DIR__ . '/Security/Random.php');
class_alias(\e107\Security\Random::class, 'e_random');
