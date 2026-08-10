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
 * Raised when the server cannot do its half of the job: no CSPRNG to mint a
 * secret with, no AES backend to encrypt with, or a preference row that
 * refuses to hold the key.
 *
 * It is never raised in response to anything a visitor sent. A token that is
 * expired, forged, truncated, issued for another purpose or complete
 * gibberish is answered with false by {@see SealedToken::open()}, because
 * that is an ordinary result and not a fault. The distinction matters: an
 * exception here means an operator has something to fix, so nothing should
 * be able to provoke one from the outside.
 *
 * {@see SealedToken::seal()} and {@see SealedToken::open()} catch this
 * themselves and answer false, since none of their call sites is written to
 * catch. It reaches a caller only from {@see SealedToken::provision()},
 * which the installer and the upgrade routines call deliberately so they can
 * report the problem while an operator is watching.
 *
 * Aliased to the v2-style name `e_sealed_token_exception` by
 * e107_handlers/sealed_token_handler.php.
 */
class SealedTokenException extends Exception
{
}
