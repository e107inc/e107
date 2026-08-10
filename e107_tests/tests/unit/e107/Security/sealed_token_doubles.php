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

/**
 * Test doubles for {@see SealedToken}.
 *
 * Two of its inputs cannot be reached from outside without them. The clock
 * decides whether a token is expired or not yet valid, and time() only ever
 * moves forwards; the key ring decides whether sealing is possible at all,
 * and a host that can seal has by definition acquired a key. Both are
 * protected single-expression methods for that reason.
 */
class SealedTokenAtTime extends SealedToken
{
	/**
	 * Seconds to add to the real clock. Negative moves the instance into the
	 * past, so what it seals is already expired.
	 *
	 * @var int
	 */
	public $offset = 0;

	/**
	 * {@inheritdoc}
	 */
	protected function now()
	{
		return time() + $this->offset;
	}
}

/**
 * A host whose token_secret preference cannot be read and cannot be written.
 */
class KeylessSealedToken extends SealedToken
{
	/**
	 * {@inheritdoc}
	 */
	protected function secret()
	{
		return false;
	}
}

/**
 * Another site, which happens to hold the same secret. Only the issuer claim
 * tells the two apart.
 */
class SealedTokenElsewhere extends SealedToken
{
	/**
	 * {@inheritdoc}
	 */
	protected function issuer()
	{
		return 'https://not-this-site.example/';
	}
}
