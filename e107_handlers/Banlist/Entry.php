<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Banlist;

use e107\Ip\Address;
use e107\Ip\Range;

/**
 * What a ban-list entry means and how it is stored.
 *
 * The banlist_ip column has always held four kinds of value under one name:
 * a single address, which is stored in e107's encoded form so the exact-match
 * lookups keep finding it; an address range, stored as written; an email or
 * email-domain pattern; and a host-name pattern for reverse-DNS bans. This is
 * the one place that tells them apart, for the admin form, the importer and
 * the file writer alike.
 *
 * <code>
 * $entry = Entry::fromText('10.77.66.*');
 * $entry->kind();     // Entry::RANGE
 * $entry->stored();   // 10.77.66.*
 * Entry::fromText('10.77.66.65')->stored();   // 0000:0000:0000:0000:0000:ffff:0a4d:4241
 * Entry::fromText('bad.cc')->kind();          // Entry::INVALID
 * </code>
 */
class Entry
{
	const ADDRESS = 'address';
	const RANGE = 'range';
	const EMAIL = 'email';
	const HOST = 'host';
	const INVALID = 'invalid';

	const HOSTNAME = '(?=.{1,253}$)[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)*';

	/** @var string */
	private $kind;

	/** @var string|null */
	private $stored;

	/** @var Range|null */
	private $range;

	/**
	 * @param string $kind
	 * @param string|null $stored
	 * @param Range|null $range
	 */
	private function __construct($kind, $stored, $range = null)
	{
		$this->kind = $kind;
		$this->stored = $stored;
		$this->range = $range;
	}

	/**
	 * @param string $text as typed or as stored
	 * @return Entry
	 */
	public static function fromText($text)
	{
		$text = is_string($text) ? trim($text) : '';
		$range = Range::fromString($text);

		if($range !== null)
		{
			if($range->isSingle())
			{
				return new self(self::ADDRESS, Address::toEncoded($range->start()), $range);
			}

			return new self(self::RANGE, preg_replace('/\s+/', '', strtolower($text)), $range);
		}

		if(strpos($text, '@') !== false)
		{
			return self::fromEmail($text);
		}

		if(strpos($text, '*.') === 0 && preg_match('/^\*\.'.self::HOSTNAME.'$/i', $text))
		{
			return new self(self::HOST, strtolower($text));
		}

		return new self(self::INVALID, null);
	}

	/**
	 * @param string $text
	 * @return Entry
	 */
	private static function fromEmail($text)
	{
		if(strpos($text, '*@') === 0 && preg_match('/^\*@'.self::HOSTNAME.'$/i', $text))
		{
			return new self(self::EMAIL, strtolower($text));
		}

		if(filter_var($text, FILTER_VALIDATE_EMAIL) !== false)
		{
			return new self(self::EMAIL, $text);
		}

		return new self(self::INVALID, null);
	}

	/**
	 * @return string one of the class constants
	 */
	public function kind()
	{
		return $this->kind;
	}

	/**
	 * @return string|null the banlist_ip value to store, or null when invalid
	 */
	public function stored()
	{
		return $this->stored;
	}

	/**
	 * @return Range|null the addresses covered, for an address or range entry
	 */
	public function range()
	{
		return $this->range;
	}
}
