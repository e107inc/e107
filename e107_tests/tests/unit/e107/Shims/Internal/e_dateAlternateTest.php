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

/**
 * @param string $extension
 * @return bool
 */
function extension_loaded($extension)
{
	if (e_dateAlternateTest::$alternate_formatter) return false;

	return \extension_loaded($extension);
}

/**
 * @param string $constant_name
 * @return bool
 */
function defined($constant_name)
{
	if (e_dateAlternateTest::$alternate_locale) return false;

	return \defined($constant_name);
}

/**
 * @param int              $category
 * @param array|string|int $locales
 * @param string           ...$rest
 * @return false|string
 *//*
function setlocale($category, $locales, ...$rest)
{
	if (e_dateAlternateTest::$alternate_locale) return 'nl_NL';

	return \setlocale($category, $locales, ...$rest);
}*/


class e_dateAlternateTest extends \e_dateTest
{
	/**
	 * @var bool
	 */
	public static $alternate_formatter;
	/**
	 * @var bool
	 */
	public static $alternate_locale;

	public function _before()
	{
		self::$alternate_formatter = true;
		parent::_before();
	}

	public function _after()
	{
		parent::_after();
		self::$alternate_formatter = false;
	}

	public function testConvert_dateDutch()
	{
		self::$alternate_formatter = false;
		self::$alternate_locale = true;

		try
		{
			setlocale(LC_ALL,'nl_NL.utf8', 'nl');
			$actual = $this->dateObj->convert_date(mktime(12, 45, 03, 2, 5, 2018), 'long');
			$expected = 'maandag 05 februari 2018 - 12:45:03';
			$this->assertEquals($expected, $actual);

			setlocale(LC_ALL,'es_SP.utf8', 'es');

			$actual = $this->dateObj->convert_date(mktime(12, 45, 03, 2, 5, 2018), 'inputdate');
			$expected = 'lunes, 05 feb, 2018';
			$this->assertEquals($expected, $actual);
		}
		finally
		{
			self::$alternate_locale = false;
			setlocale(LC_ALL, 'en_GB.UTF-8', 'en_GB.utf8', 'en');
		}


	}
}
