<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Security;

use Exception;

/**
 * Raised when the platform cannot supply cryptographically secure randomness,
 * or when a caller asks {@see Random} for something it cannot produce.
 *
 * Aliased to the v2-style name `e_random_exception` by
 * e107_handlers/random_handler.php.
 */
class RandomException extends Exception
{
}
