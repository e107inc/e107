<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2013 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/sql/extended_timezones.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

if(!defined('e107_INIT'))
{
	exit;
}

/**
 * @file
 * This file is used with the extended user field 'predefined list' type. It is
 * invoked when the value field is 'timezones'.
 *
 * It is an example of an extended user field which access a predetermined list
 * of key-pair values. In this example all the data is loaded into memory; for
 * other applications the data may be read from a database, possibly with
 * caching.
 *
 * The objective is to provide a uniform interface to such data.
 *
 * The class name must be the same as the file name - i.e. the list name
 * prefixed with 'extended_'.
 *
 * The variable name must be 'timezones_list', and is an array of possible
 * values, each of which is a value => text pair.
 *
 * The text is displayed in a drop-down; the value is returned.
 *
 * If function timezones_value() exists, it is called to create the displayed
 * text.
 */


/**
 * Class extended_timezones.
 */
class extended_timezones
{

	/**
	 * @var array
	 */
	private $timezonesList = array();

	/**
	 * @var bool
	 */
	private $isEOF = false; // True if at last element of list.

	/**
	 * @var bool
	 */
	private $bufferValid = false;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->timezonesList = systemTimeZones();
	}

	/**
	 * Call before using the 'next' format option, to ensure the array is
	 * indexed from the beginning.
	 */
	public function pointerReset()
	{
		$this->isEOF = (false === reset($this->timezonesList));
		$this->bufferValid = true;
	}


	/**
	 * Return a formatted timezone value
	 *
	 * @param mixed $key
	 *  The key value to select.
	 * @param string $formatSpec
	 *  Defines format of return value.
	 *
	 * @return mixed
	 *  (according to $formatSpec).
	 *  false - if no value available.
	 *  'array' - a single-element array; key as passed, and value to match key
	 *  'next' - as 'array', but ignores the passed $key and moves to next value.
	 *  'default' - a string usable for display.
	 */
	public function getValue($key, $formatSpec = '')
	{
		if($formatSpec == 'next')
		{
			// Make sure buffer is defined.
			if(!$this->bufferValid)
			{
				$this->pointerReset();
			}

			if($this->isEOF)
			{
				return false;
			}

			$key = key($this->timezonesList);
			$val = current($this->timezonesList);

			if(false === $val)
			{
				$this->isEOF = true;
				return false;
			}

			$this->isEOF = (false === next($this->timezonesList));

			return array($key => $val);
		}

		$exists = isset($this->timezonesList[$key]);

		if(!$exists)
		{
			return false;
		}

		$val = $this->timezonesList[$key];

		if($formatSpec == 'array')
		{
			return array($key => $val);
		}

		// Default (as per earlier implementations) - can be specified with
		// 'display' format.
		return $val;
	}
}
