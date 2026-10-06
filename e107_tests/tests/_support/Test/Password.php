<?php
namespace Test;

/**
 * Password hashes for the accounts a test seeds, at bcrypt's lowest cost.
 */
final class Password
{
	const COST = 4;

	/**
	 * @param string $password
	 * @return string
	 */
	public static function hash($password)
	{
		return password_hash($password, PASSWORD_BCRYPT, array('cost' => self::COST));
	}

	/**
	 * Whether a stored hash costs more to check than {@see Password::hash()} would.
	 *
	 * @param string $stored
	 * @return bool
	 */
	public static function isCostly($stored)
	{
		$info = password_get_info($stored);

		return !empty($info['algo']) && isset($info['options']['cost']) && $info['options']['cost'] > self::COST;
	}
}
