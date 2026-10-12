<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The sealed token a quote carries a [hide] block in
 */

if(!defined('e107_INIT'))
{
	exit;
}

/**
 * A {@see \e107\Security\SealedToken} bound to the site's key alone, so a quote sealed before a move of host or scheme, or on another of the site's trusted hosts, still opens.
 */
class forum_hidden_token extends \e107\Security\SealedToken
{
	/**
	 * {@inheritdoc}
	 */
	protected function issuer()
	{
		return null;
	}
}
