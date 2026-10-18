<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\SocialLogin;

/**
 * {@see \Hybridauth\Storage\Session} that keeps objects as they are and never unserialises what a provider sent.
 */
final class SessionStorage extends \Hybridauth\Storage\Session
{
	/**
	 * {@inheritdoc}
	 */
	public function get($key)
	{
		$key = $this->keyPrefix . strtolower($key);

		if (!isset($_SESSION[$this->storeNamespace][$key]))
		{
			return null;
		}

		$value = $_SESSION[$this->storeNamespace][$key];

		return (is_array($value) && array_key_exists('lateObject', $value)) ? null : $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($key, $value)
	{
		$_SESSION[$this->storeNamespace][$this->keyPrefix . strtolower($key)] = $value;
	}
}
