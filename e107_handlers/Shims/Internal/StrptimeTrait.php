<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 * strptime()
 */

namespace e107\Shims\Internal;

trait StrptimeTrait
{
	/**
	 * Parse a time/date generated with strftime()
	 *
	 * Resilient replacement for PHP internal strptime()
	 *
	 * @see https://www.php.net/manual/en/function.strptime.php what this function approximates
	 * @param string $date The string to parse (e.g. returned from strftime()).
	 * @param string $format The format used in date (e.g. the same as used in strftime()).
	 * @return array|bool Returns FALSE on failure.
	 * 	The following parameters are returned in the array:
	 *  	"tm_sec"        Seconds after the minute (0-61)
	 *  	"tm_min"        Minutes after the hour (0-59)
	 *  	"tm_hour"       Hour since midnight (0-23)
	 *  	"tm_mday"       Day of the month (1-31)
	 *  	"tm_mon"        Months since January (0-11)
	 *  	"tm_year"       Years since 1900
	 *  	"tm_wday"       Days since Sunday (0-6)
	 *  	"tm_yday"       Days since January 1 (0-365)
	 *  	"unparsed"      the date part which was not recognized using the specified format
	 */
	public static function strptime($date, $format)
	{
		$result = false;
		if (function_exists('strptime') && (new \ReflectionFunction('strptime'))->isInternal())
			$result = strptime($date, $format);
		if (!is_array($result))
			$result = self::strptime_alt($date, $format);
		return $result;
	}

	/**
	 * Parse a time/date generated with strftime()
	 *
	 * Alternative implementation based on public domain library:
	 * https://github.com/Polycademy/upgradephp/
	 *
	 * @param string $date
	 * @param string $format
	 * @return array|bool
	 */
	public static function strptime_alt($date, $format)
	{
		static $expand = array('%D' => '%m/%d/%y', '%T' => '%H:%M:%S',);
		static $map_r = array(
			'%S' => 'tm_sec',
			'%M' => 'tm_min',
			'%H' => 'tm_hour',
			'%I' => 'tm_hour',
			'%d' => 'tm_mday',
			'%m' => 'tm_mon',
			'%Y' => 'tm_year',
			'%y' => 'tm_year',
			'%W' => 'tm_wday',
			'%D' => 'tm_yday',
			'%u' => 'unparsed',
		);

		$fullmonth = array();
		$abrevmonth = array();

		for ($i = 1; $i <= 12; $i++)
		{
			$k = strftime('%B', mktime(0, 0, 0, $i));
			$fullmonth[$k] = $i;

			$j = strftime('%b', mktime(0, 0, 0, $i));
			$abrevmonth[$j] = $i;
		}


		#-- transform $format into extraction regex
		$format = str_replace(array_keys($expand), array_values($expand), $format);
		$preg = preg_replace('/(%\w)/', '(\w+)', preg_quote($format));

		#-- record the positions of all STRFCMD-placeholders
		preg_match_all('/(%\w)/', $format, $positions);
		$positions = $positions[1];

		$vals = array();

		#-- get individual values
		if (preg_match("#$preg#", $date, $extracted))
		{
			#-- get values
			foreach ($positions as $pos => $strfc)
			{
				$v = $extracted[$pos + 1];
				#-- add
				if (isset($map_r[$strfc]))
				{
					$n = $map_r[$strfc];
					$vals[$n] = ($v > 0) ? (int)$v : $v;
				}
				else
				{
					if (!isset($vals['unparsed'])) $vals['unparsed'] = '';
					$vals['unparsed'] .= $v . ' ';
				}
			}

			#-- fixup some entries
			//$vals["tm_wday"] = $names[ substr($vals["tm_wday"], 0, 3) ];
			if ($vals['tm_year'] >= 1900)
			{
				$vals['tm_year'] -= 1900;
			}
			elseif ($vals['tm_year'] > 0)
			{
				$vals['tm_year'] += 100;
			}

			if ($vals['tm_mon'])
			{
				$vals['tm_mon'] -= 1;
			}

			if (!isset($vals['tm_sec']))
			{
				$vals['tm_sec'] = 0;
			}

			if (!isset($vals['tm_min']))
			{
				$vals['tm_min'] = 0;
			}

			if (!isset($vals['tm_hour']))
			{
				$vals['tm_hour'] = 0;
			}


			if (!isset($vals['unparsed']))
			{
				$vals['unparsed'] = '';
			}

			$unxTimestamp = mktime($vals['tm_hour'], $vals['tm_min'], $vals['tm_sec'], ($vals['tm_mon'] + 1), $vals['tm_mday'], ($vals['tm_year'] + 1900));

			$vals['tm_wday'] = (int)strftime('%w', $unxTimestamp); // Days since Sunday (0-6)
			$vals['tm_yday'] = (strftime('%j', $unxTimestamp) - 1); // Days since January 1 (0-365)
		}

		return !empty($vals) ? $vals : false;

	}
}