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
 * An inclusive range of addresses, both ends in the form {@see Address} uses.
 *
 * {@see Range::fromString()} is the one definition of what a ban-list entry
 * means as an address: a single address in any spelling, dotted IPv4 with
 * trailing wildcard octets, the encoded form with a trailing run of x
 * nibbles, CIDR notation, or two addresses joined by a hyphen. Anything it
 * returns null for is not enforced by address, whatever else it may be.
 *
 * <code>
 * $r = Range::fromString('10.77.66.0/24');
 * $r->contains(Address::toHex('10.77.66.65'));   // true
 * $r->toDisplay();                               // 10.77.66.0 - 10.77.66.255
 * Range::fromString('10.77.66.1-10.77.66.9')->toCidr();   // 10.77.66.1/32, 10.77.66.2/31, ...
 * </code>
 */
class Range
{
	const BITS = 128;
	const V4_BITS = 96;

	/** @var string */
	private $start;

	/** @var string */
	private $end;

	/**
	 * @param string $start
	 * @param string $end
	 */
	private function __construct($start, $end)
	{
		$this->start = $start;
		$this->end = $end;
	}

	/**
	 * @param string $start hex, at or below $end
	 * @param string $end hex
	 * @return Range|null null when the bounds are reversed
	 */
	public static function between($start, $end)
	{
		if(Address::compare($start, $end) > 0)
		{
			return null;
		}

		return new self($start, $end);
	}

	/**
	 * @param string $text
	 * @return Range|null
	 */
	public static function fromString($text)
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

		if(strpos($text, '-') !== false)
		{
			return self::fromHyphenated($text);
		}

		if(strpos($text, '/') !== false)
		{
			return self::fromCidr($text);
		}

		if(strpos($text, '*') !== false || strpos($text, 'x') !== false)
		{
			return self::fromWildcard($text);
		}

		$hex = Address::toHex($text);

		return $hex === null ? null : new self($hex, $hex);
	}

	/**
	 * @param string $text
	 * @return Range|null
	 */
	private static function fromHyphenated($text)
	{
		if(!preg_match('/^(\S+)\s*-\s*(\S+)$/', $text, $parts))
		{
			return null;
		}

		$start = Address::toHex($parts[1]);
		$end = Address::toHex($parts[2]);

		if($start === null || $end === null || Address::isV4Mapped($start) !== Address::isV4Mapped($end))
		{
			return null;
		}

		return self::between($start, $end);
	}

	/**
	 * @param string $text
	 * @return Range|null
	 */
	private static function fromCidr($text)
	{
		$parts = explode('/', $text);

		if(count($parts) !== 2 || $parts[1] === '' || !ctype_digit($parts[1]) || preg_match('/\s/', $text))
		{
			return null;
		}

		$start = Address::toHex($parts[0]);

		if($start === null)
		{
			return null;
		}

		$prefix = (int) $parts[1];

		if(strpos($parts[0], ':') === false)
		{
			if($prefix > self::BITS - self::V4_BITS)
			{
				return null;
			}

			$prefix += self::V4_BITS;
		}

		if($prefix > self::BITS)
		{
			return null;
		}

		$hostBits = self::BITS - $prefix;

		if(self::clearLowBits($start, $hostBits) !== $start)
		{
			return null;
		}

		return new self($start, self::setLowBits($start, $hostBits));
	}

	/**
	 * @param string $text
	 * @return Range|null
	 */
	private static function fromWildcard($text)
	{
		if(preg_match('/\s/', $text))
		{
			return null;
		}

		if(strpos($text, '.') !== false && strpos($text, ':') === false)
		{
			return self::fromDottedWildcard($text);
		}

		$hex = str_replace(':', '', $text);

		if(strlen($hex) !== Address::LENGTH || !preg_match('/^[0-9a-f]*x+$/', $hex))
		{
			return null;
		}

		if(strpos($text, ':') !== false && !preg_match('/^[0-9a-fx]{4}(:[0-9a-fx]{4}){7}$/', $text))
		{
			return null;
		}

		return new self(str_replace('x', '0', $hex), str_replace('x', 'f', $hex));
	}

	/**
	 * @param string $text
	 * @return Range|null
	 */
	private static function fromDottedWildcard($text)
	{
		$octets = explode('.', $text);

		if(count($octets) !== 4)
		{
			return null;
		}

		$start = Address::V4_PREFIX;
		$end = Address::V4_PREFIX;
		$wild = false;

		foreach($octets as $octet)
		{
			if($octet === '*' || $octet === 'x')
			{
				$wild = true;
				$start .= '00';
				$end .= 'ff';
				continue;
			}

			if($wild || $octet === '' || !ctype_digit($octet) || (int) $octet > 255)
			{
				return null;
			}

			$start .= sprintf('%02x', (int) $octet);
			$end .= sprintf('%02x', (int) $octet);
		}

		return new self($start, $end);
	}

	/**
	 * @param string $hex
	 * @param int $bits
	 * @return string $hex with its low $bits bits set to zero
	 */
	private static function clearLowBits($hex, $bits)
	{
		$nibbles = $bits >> 2;
		$rest = $bits % 4;
		$out = substr($hex, 0, Address::LENGTH - $nibbles).str_repeat('0', $nibbles);

		if($rest > 0)
		{
			$i = Address::LENGTH - $nibbles - 1;
			$out[$i] = dechex(hexdec($out[$i]) & (0xf << $rest) & 0xf);
		}

		return $out;
	}

	/**
	 * @param string $hex
	 * @param int $bits
	 * @return string $hex with its low $bits bits set to one
	 */
	private static function setLowBits($hex, $bits)
	{
		$nibbles = $bits >> 2;
		$rest = $bits % 4;
		$out = substr($hex, 0, Address::LENGTH - $nibbles).str_repeat('f', $nibbles);

		if($rest > 0)
		{
			$i = Address::LENGTH - $nibbles - 1;
			$out[$i] = dechex(hexdec($out[$i]) | ((1 << $rest) - 1));
		}

		return $out;
	}

	/**
	 * @return string
	 */
	public function start()
	{
		return $this->start;
	}

	/**
	 * @return string
	 */
	public function end()
	{
		return $this->end;
	}

	/**
	 * @return bool
	 */
	public function isSingle()
	{
		return $this->start === $this->end;
	}

	/**
	 * @param string $hex
	 * @return bool
	 */
	public function contains($hex)
	{
		return Address::compare($this->start, $hex) <= 0 && Address::compare($hex, $this->end) <= 0;
	}

	/**
	 * @return string end minus start, comparable with {@see Address::compare()}
	 */
	public function width()
	{
		return Address::sub($this->end, $this->start);
	}

	/**
	 * @return string a single address, or both ends joined by a hyphen; parses back with {@see Range::fromString()}
	 */
	public function toDisplay()
	{
		if($this->isSingle())
		{
			return Address::toDisplay($this->start);
		}

		return Address::toDisplay($this->start).' - '.Address::toDisplay($this->end);
	}

	/**
	 * The fewest CIDR blocks that cover exactly this range, in display notation.
	 *
	 * @return string[]
	 */
	public function toCidr()
	{
		$blocks = array();
		$cursor = $this->start;

		while($cursor !== null && Address::compare($cursor, $this->end) <= 0)
		{
			$bits = self::trailingZeroBits($cursor);

			while($bits > 0 && Address::compare(self::setLowBits($cursor, $bits), $this->end) > 0)
			{
				$bits--;
			}

			$blocks[] = self::cidrNotation($cursor, self::BITS - $bits);
			$cursor = Address::succ(self::setLowBits($cursor, $bits));
		}

		return $blocks;
	}

	/**
	 * @param string $hex
	 * @return int
	 */
	private static function trailingZeroBits($hex)
	{
		$bits = 0;

		for($i = Address::LENGTH - 1; $i >= 0; $i--)
		{
			$nibble = hexdec($hex[$i]);

			if($nibble === 0)
			{
				$bits += 4;
				continue;
			}

			while(($nibble & 1) === 0)
			{
				$bits++;
				$nibble >>= 1;
			}

			break;
		}

		return $bits;
	}

	/**
	 * @param string $hex block start
	 * @param int $prefix
	 * @return string
	 */
	private static function cidrNotation($hex, $prefix)
	{
		if(Address::isV4Mapped($hex) && $prefix >= self::V4_BITS)
		{
			return Address::toDisplay($hex).'/'.($prefix - self::V4_BITS);
		}

		return inet_ntop(hex2bin($hex)).'/'.$prefix;
	}
}
