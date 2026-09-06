<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Ip;

/**
 * One IP address as 32 lowercase hexadecimal characters: the 128-bit value
 * behind the colon-separated form e107 stores, with IPv4 mapped into
 * ::ffff:0:0/96 exactly as {@see \eIPHandler::ipEncode()} does.
 *
 * Every method is a pure function on that string. Ordering is string
 * ordering, which for fixed-width lowercase hex is numeric ordering; use
 * {@see Address::compare()} and never PHP's comparison operators, which read
 * a digits-and-e string such as 00000000000000001e00000000000000 as a float.
 *
 * <code>
 * Address::toHex('10.77.66.65');   // 00000000000000000000ffff0a4d4241
 * Address::toHex('2001:db8::1');   // 20010db8000000000000000000000001
 * Address::toDisplay('00000000000000000000ffff0a4d4241');  // 10.77.66.65
 * </code>
 */
class Address
{
	const LENGTH = 32;
	const MIN = '00000000000000000000000000000000';
	const MAX = 'ffffffffffffffffffffffffffffffff';
	const V4_PREFIX = '00000000000000000000ffff';

	/**
	 * Canonical hex of an address written in any form e107 has ever used.
	 *
	 * Dotted IPv4 (leading zeros allowed, as the ban-list help text promises),
	 * any textual IPv6 including the 39-character encoded form and an embedded
	 * IPv4 tail, 32 characters of packed hex, or the 8-character IPv4 hex of
	 * 0.7-era rows. Anything else, wildcards included, is not an address.
	 *
	 * @param string $text
	 * @return string|null 32 lowercase hex characters, or null
	 */
	public static function toHex($text)
	{
		if(!is_string($text))
		{
			return null;
		}

		$text = strtolower(trim($text));

		if($text === '')
		{
			return null;
		}

		if(strpos($text, ':') !== false)
		{
			if(filter_var($text, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false)
			{
				return null;
			}

			return bin2hex(inet_pton($text));
		}

		if(strpos($text, '.') !== false)
		{
			return self::fromDotted($text);
		}

		if(!ctype_xdigit($text))
		{
			return null;
		}

		if(strlen($text) === self::LENGTH)
		{
			return $text;
		}

		if(strlen($text) === 8)
		{
			return self::V4_PREFIX.$text;
		}

		return null;
	}

	/**
	 * @param string $text dotted quad
	 * @return string|null
	 */
	private static function fromDotted($text)
	{
		$octets = explode('.', $text);

		if(count($octets) !== 4)
		{
			return null;
		}

		$hex = self::V4_PREFIX;

		foreach($octets as $octet)
		{
			if($octet === '' || !ctype_digit($octet) || (int) $octet > 255)
			{
				return null;
			}

			$hex .= sprintf('%02x', (int) $octet);
		}

		return $hex;
	}

	/**
	 * The shortest textual form: dotted for an IPv4-mapped address, compressed
	 * IPv6 for everything else.
	 *
	 * @param string $hex
	 * @return string
	 */
	public static function toDisplay($hex)
	{
		if(self::isV4Mapped($hex))
		{
			return implode('.', array_map('hexdec', str_split((string) substr($hex, 24), 2)));
		}

		return inet_ntop(hex2bin($hex));
	}

	/**
	 * The colon-separated form e107 stores and compares: eight groups of four
	 * lowercase hex characters, as {@see \eIPHandler::ipEncode()} writes it.
	 *
	 * @param string $hex
	 * @return string 39 characters
	 */
	public static function toEncoded($hex)
	{
		return implode(':', str_split($hex, 4));
	}

	/**
	 * @param string $hex
	 * @return bool
	 */
	public static function isV4Mapped($hex)
	{
		return strpos($hex, self::V4_PREFIX) === 0;
	}

	/**
	 * @param string $a
	 * @param string $b
	 * @return int negative when $a is below $b, zero when equal, positive when above
	 */
	public static function compare($a, $b)
	{
		return strcmp($a, $b);
	}

	/**
	 * The address after $hex.
	 *
	 * @param string $hex
	 * @return string|null null past the all-ones address
	 */
	public static function succ($hex)
	{
		for($i = self::LENGTH - 1; $i >= 0; $i--)
		{
			if($hex[$i] !== 'f')
			{
				$hex[$i] = dechex(hexdec($hex[$i]) + 1);

				return $hex;
			}

			$hex[$i] = '0';
		}

		return null;
	}

	/**
	 * The address before $hex.
	 *
	 * @param string $hex
	 * @return string|null null below the all-zeros address
	 */
	public static function pred($hex)
	{
		for($i = self::LENGTH - 1; $i >= 0; $i--)
		{
			if($hex[$i] !== '0')
			{
				$hex[$i] = dechex(hexdec($hex[$i]) - 1);

				return $hex;
			}

			$hex[$i] = 'f';
		}

		return null;
	}

	/**
	 * $a minus $b, for $a at or above $b.
	 *
	 * @param string $a
	 * @param string $b
	 * @return string 32 lowercase hex characters
	 */
	public static function sub($a, $b)
	{
		$out = $a;
		$borrow = 0;

		for($i = self::LENGTH - 1; $i >= 0; $i--)
		{
			$digit = hexdec($a[$i]) - hexdec($b[$i]) - $borrow;
			$borrow = 0;

			if($digit < 0)
			{
				$digit += 16;
				$borrow = 1;
			}

			$out[$i] = dechex($digit);
		}

		return $out;
	}
}
