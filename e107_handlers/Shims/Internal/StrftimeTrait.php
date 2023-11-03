<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2021 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shims\Internal;

use DateTimeInterface;
use DateTimeZone;

/**
 *
 */
trait StrftimeTrait
{
	/**
	 * Polyfill for {@see strftime()}, which was deprecated in PHP 8.1
	 *
	 * The implementation is an approximation that may be wrong for some obscure formatting characters.
	 *
	 * This function will attempt to use the locale features provided by
	 * {@link https://www.php.net/manual/en/book.intl.php ext-intl} and fall back to the PHP-builtin {@see DateTime}.
	 * Note that without ext-intl, formatted times will be in English.
	 *
	 * @param string   $format    The old {@see strftime()} format string
	 * @param int|null $timestamp A Unix epoch timestamp. If null, defaults to the value of {@see time()}.
	 * @return string Datetime formatted according to the provided arguments
	 */
	public static function strftime($format, $timestamp = null)
	{
		if ($timestamp === null) $timestamp = time();
		$datetime = date_create("@$timestamp");
		$datetime->setTimezone(new DateTimeZone(date_default_timezone_get()));

		foreach (self::getFormatMap() as $strftime_key => $date_format_key)
		{
			if (!is_string($date_format_key) && is_callable($date_format_key))
			{
				$replacement = self::escapeDateTimePattern($date_format_key($datetime));
			}
			else
			{
				$replacement = $date_format_key;
			}
			$format = str_replace($strftime_key, $replacement, $format);
		}

		return self::date_format($datetime, $format);
	}

	/**
	 * @param DateTimeInterface $datetime
	 * @param string            $format
	 * @return string
	 */
	protected static function date_format($datetime, $format)
	{
		if (!extension_loaded('intl'))
		{
			return date_format($datetime, $format);
		}


		$timezone = 'GMT'.date('P');
		$formatter = new \IntlDateFormatter(
			self::getSensibleLocale(),
			\IntlDateFormatter::NONE,
			\IntlDateFormatter::NONE,
			null, // More accurate timezone. @see https://stackoverflow.com/questions/31707395/why-php-intldateformatter-returns-wrong-date-1-hour
			null,
			$format
		);

		datefmt_set_timezone($formatter, $timezone);

		return $formatter->format($datetime);
	}

	/**
	 * Try to figure out the e107 locale, falling back to the {@see setlocale()} value, and falling back again to "C"
	 *
	 * @return string An {@link http://www.faqs.org/rfcs/rfc1766 RFC 1766} language tag such as "en_US"
	 */
	protected static function getSensibleLocale()
	{
		if (defined('CORE_LC') && defined('CORE_LC2'))
		{
			return strtolower(CORE_LC) . "_" . strtoupper(CORE_LC2);
		}

		$setlocale = setlocale(LC_ALL, "0");
		return $setlocale ?: 'C';
	}

	/**
	 * Escape a literal string for use inside a datetime pattern
	 *
	 * Implementation differs depending on whether {@link https://www.php.net/manual/en/book.intl.php ext-intl} is
	 * enabled
	 *
	 * @param string $input
	 * @return string
	 */
	protected static function escapeDateTimePattern($input)
	{
		if (extension_loaded('intl'))
		{
			return "'" . str_replace("'", "''", $input) . "'";
		}

		return chunk_split($input, 1, "\\");
	}

	/**
	 * Get the {@see strftime()} format to date format pattern mapping depending on if
	 * {@link https://www.php.net/manual/en/book.intl.php ext-intl} is enabled
	 *
	 * @return array<string, string|callable>
	 */
	protected static function getFormatMap()
	{
		if (extension_loaded('intl'))
		{
			return self::getFormatMapForIntlDateFormatter();
		}

		return self::getFormatMapForDateTime();
	}

	/**
	 * @return array
	 */
	protected static function getFormatMapForIntlDateFormatter()
	{
		return [
			'%a' => 'eee',
			'%A' => 'eeee',
			'%d' => 'dd',
			'%e' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'd'), 2, " ", STR_PAD_LEFT);
			},
			'%j' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'D'), 3, "0", STR_PAD_LEFT);
			},
			'%u' => 'e',
			'%w' => function($datetime)
			{
				return date_format($datetime, 'w');
			},
			'%U' => 'w',
			'%V' => 'ww',
			'%W' => function($datetime)
			{
				return date_format($datetime, 'W');
			},
			'%b' => 'MMM',
			'%B' => 'MMMM',
			'%h' => 'MMM',
			'%m' => 'MM',
			'%C' => function($datetime)
			{
				return (string) ((int) self::date_format($datetime, 'y') / 100);
			},
			'%g' => 'yy',
			'%G' => 'y',
			'%y' => 'yy',
			'%Y' => 'y',
			'%H' => 'HH',
			'%k' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'H'), 2, " ", STR_PAD_LEFT);
			},
			'%I' => 'hh',
			'%l' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'h'), 2, " ", STR_PAD_LEFT);
			},
			'%M' => 'mm',
			'%p' => function($datetime)
			{
				return strtoupper(self::date_format($datetime, 'a'));
			},
			'%P' => 'a',
			'%r' => 'hh:mm:ss a',
			'%R' => 'HH:mm',
			'%S' => 'ss',
			'%T' => 'HH:mm:ss',
			'%X' => 'HH:mm:ss',
			'%z' => 'Z',
			'%Z' => 'z',
			'%c' => function($datetime)
			{
				/** @noinspection PhpComposerExtensionStubsInspection */
				return \IntlDateFormatter::formatObject($datetime);
			},
			'%D' => 'MM/dd/yy',
			'%F' => 'y-MM-dd',
			'%s' => function($datetime)
			{
				return date_timestamp_get($datetime);
			},
			'%x' => function($datetime)
			{
				/** @noinspection PhpComposerExtensionStubsInspection */
				return \IntlDateFormatter::formatObject($datetime, \IntlDateFormatter::SHORT);
			},
			'%n' => "\n",
			'%t' => "\t",
			'%%' => "'%'",
		];
	}

	/**
	 * @return array
	 */
	protected static function getFormatMapForDateTime()
	{
		return [
			'%a' => 'D',
			'%A' => 'l',
			'%d' => 'd',
			'%e' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'n'), 2, " ", STR_PAD_LEFT);
			},
			'%j' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'z'), 3, "0", STR_PAD_LEFT);
			},
			'%u' => 'N',
			'%w' => 'w',
			'%U' => 'W',
			'%V' => 'W',
			'%W' => 'W',
			'%b' => 'M',
			'%B' => 'F',
			'%h' => 'M',
			'%m' => 'm',
			'%C' => function($datetime)
			{
				return (string) ((int) self::date_format($datetime, 'Y') / 100);
			},
			'%g' => 'y',
			'%G' => 'Y',
			'%y' => 'y',
			'%Y' => 'Y',
			'%H' => 'H',
			'%k' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'G'), 2, " ", STR_PAD_LEFT);
			},
			'%I' => 'h',
			'%l' => function($datetime)
			{
				return str_pad(self::date_format($datetime, 'g'), 2, " ", STR_PAD_LEFT);
			},
			'%M' => 'i',
			'%p' => 'A',
			'%P' => 'a',
			'%r' => 'h:i:s A',
			'%R' => 'H:i',
			'%S' => 's',
			'%T' => 'H:i:s',
			'%X' => 'H:i:s',
			'%z' => 'O',
			'%Z' => 'T',
			'%c' => 'r',
			'%D' => 'm/d/y',
			'%F' => 'Y-m-d',
			'%s' => 'U',
			'%x' => 'Y-m-d',
			'%n' => "\n",
			'%t' => "\t",
			'%%' => '\%',
		];
	}
}
