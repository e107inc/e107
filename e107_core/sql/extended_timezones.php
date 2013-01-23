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

if (!defined('e107_INIT')) { exit; }

/*
This file is used with the extended user field 'predefined list' type. It is invoked when the value field is 'timezones'.

It is an example of an extended user field which access a predetermined list of key-pair values. In this example all the data is loaded
into memory; for other applications the data may be read from a database, possibly with caching.

The objective is to provide a uniform interface to such data.

The class name must be the same as the file name - i.e. the list name prefixed with 'extended_'.

The variable name must be 'timezones_list', and is an array of possible values, each of which is a value => text pair
The text is displayed in a drop-down; the value is returned.
If function timezones_value() exists, it is called to create the displayed text
*/

class extended_timezones
{

	private $timezonesList = array(
		'-12' => "International DateLine West",
		'-11' => "Samoa",
		'-10' => "Hawaii",
		 '-9' => "Alaska",
		 '-8' => "Pacific Time (US and Canada)",
		 '-7' => "Mountain Time (US and Canada)",
		 '-6' => "Central Time (US and Canada), Central America",
		 '-5' => "Eastern Time (US and Canada)",
		 '-4' => "Atlantic Time (Canada)",
		 '-3.30' => 'Newfoundland',
		 '-3' => "Greenland, Brasilia, Buenos Aires, Georgetown",
		 '-2' => "Mid-Atlantic",
		 '-1' => "Azores, Cape Verde Islands",
		 '+0' => "UK, Ireland, Lisbon",
		 '+1' => "West Central Africa, Western Europe",
		 '+2' => "Greece, Egypt, parts of Africa",
		 '+3' => "Russia, Baghdad, Kuwait, Nairobi",
		 '+3.30' => 'Tehran, Iran',
		 '+4' => "Abu Dhabi, Kabul",
		 '+4.30' => 'Afghanistan',
		 '+5' => "Islamabad, Karachi",
		 '+5.30' => "Mumbai, Delhi, Calcutta",
		 '+5.45' => 'Kathmandu',
		 '+6' => "Astana, Dhaka",
		 '+7' => "Bangkok, Rangoon",
		 '+8' => "Hong Kong, Singapore, Perth, Beijing",
		 '+9' => "Tokyo, Seoul",
		 '+9.30' => 'Darwin, Adelaide',
		'+10' => "Brisbane, Canberra, Sydney, Melbourne",
		'+10.30' => 'Lord Howe Island',
		'+11' => "Soloman Islands",
		'+11.30' => 'Norfolk Island',
		'+12' => "New Zealand, Fiji, Marshall Islands",
		'+13' => "Tonga, Nuku'alofa, Rawaki Islands",
		'+13.45' => 'Chatham Island',
		'+14' => 'Kiribati: Line Islands'
		);


	private $isEOF = FALSE;					// True if at last element of list
	private $bufferValid = FALSE;


	/**
	 *	Call before using the 'next' format option, to ensure the array is indexed from the beginning
	 */
	public function pointerReset()
	{
		$this->isEOF = (FALSE === reset($this->timezonesList));
		$this->bufferValid = TRUE;
	}


	/**
	 *	Return a formatted timezone value
	 *
	 *	@param mixed $key - the key value to select
	 *	@param string $formatSpec - defines format of return value
	 *
	 *	@return mixed (according to $formatSpec). FALSE if no value available
	 *		'array' - a single-element array; key as passed, and value to match key
	 *		'next' - as 'array', but ignores the passed $key and moves to next value.
	 *		default - a string usable for display
	 */
	public function getValue($key, $formatSpec = '')
	{
		if ($formatSpec == 'next')
		{
			if (!$this->bufferValid) $this->pointerReset;		// Make sure buffer is defined
			if ($this->isEOF) return FALSE;
			$key = key($this->timezonesList);
			$val = current($this->timezonesList);
			if (FALSE === $val)
			{
				$this->isEOF = TRUE;
				return FALSE;
			}
			$this->isEOF = (FALSE === next($this->timezonesList));
			return array($key => $val);
		}

		$exists = isset($this->timezonesList[$key]);
		if (!$exists) return FALSE;

		$val = $this->timezonesList[$key];
		if ($formatSpec == 'array')
		{
			return array($key => $val);
		}
		
		// Default (as per earlier implementations) - can be specified with 'display' format
		return 'GMT'.$key.' - '.$val;
	}
}


?>