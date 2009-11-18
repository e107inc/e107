<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_admin/sql/extended_timezones.php,v $
 * $Revision: 1.4 $
 * $Date: 2009-11-18 01:04:42 $
 * $Author: e107coders $
 */

if (!defined('e107_INIT')) { exit; }

/*
This file is used with the extended user field 'predefined list' type. Its invoked when the value field is 'timezones'.
The variable name must be 'timezones_list', and is an array of possible values, each of which is a value => text pair
The text is displayed in a drop-down; the value is returned.
If function timezones_value() exists, it is called to create the displayed text
*/

//FIXME - remove globals. 
global $timezones_list;
if (!is_array($timezones_list))
{
$timezones_list = array(
  array('-12', "International DateLine West"),
  array('-11', "Samoa"),
  array('-10', "Hawaii"),
  array( '-9', "Alaska"),
  array( '-8', "Pacific Time (US and Canada)"),
  array( '-7', "Mountain Time (US and Canada)"),
  array( '-6', "Central Time (US and Canada), Central America"),
  array( '-5', "Eastern Time (US and Canada)"),
  array( '-4', "Atlantic Time (Canada)"),
  array( '-3.30', 'Newfoundland'),
  array( '-3', "Greenland, Brasilia, Buenos Aires, Georgetown"),
  array( '-2', "Mid-Atlantic"),
  array( '-1', "Azores, Cape Verde Islands"),
  array( '+0', "UK, Ireland, Lisbon"),
  array( '+1', "West Central Africa, Western Europe"),
  array( '+2', "Greece, Egypt, parts of Africa"),
  array( '+3', "Russia, Baghdad, Kuwait, Nairobi"),
  array( '+3.30', 'Tehran, Iran'),
  array( '+4', "Abu Dhabi, Kabul"),
  array( '+4.30', 'Afghanistan'),
  array( '+5', "Islamabad, Karachi"),
  array( '+5.30', "Mumbai, Delhi, Calcutta"),
  array( '+5.45', 'Kathmandu'),
  array( '+6', "Astana, Dhaka"),
  array( '+7', "Bangkok, Rangoon"),
  array( '+8', "Hong Kong, Singapore, Perth, Beijing"),
  array( '+9', "Tokyo, Seoul"),
  array( '+9.30', 'Darwin, Adelaide'),
  array('+10', "Brisbane, Canberra, Sydney, Melbourne"),
  array('+10.30', 'Lord Howe Island'),
  array('+11', "Soloman Islands"),
  array('+11.30', 'Norfolk Island'),
  array('+12', "New Zealand, Fiji, Marshall Islands"),
  array('+13', "Tonga, Nuku'alofa, Rawaki Islands"),
  array('+13.45', 'Chatham Island'),
  array('+14', 'Kiribati: Line Islands')
  );
}

if (!function_exists('timezones_value'))
{
  function timezones_value($key, $value)
  {
    return 'GMT'.$key.' - '.$value;
  }
}

?>