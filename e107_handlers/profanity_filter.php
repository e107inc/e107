<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2011 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @DEPRECATED FILE
 */

if (!defined('e107_INIT')) { exit; }

/*
class e_profanityFilter 
{
	var $profanityList;

	function e_profanityFilter() 
	{
		global $pref;

		$words = explode(",", $pref['profanity_words']);
        $word_array = array();
		foreach($words as $word) 
		{
			$word = trim($word);
			if($word != "")
			{
				$word_array[] = $word;
				if (strpos($word, '&#036;') !== FALSE)
				{
					$word_array[] = str_replace('&#036;', '\$', $word);		// Special case - '$' may be 'in clear' or as entity
				}
			}
		}
		if(count($word_array))
		{
			$this->profanityList = str_replace('#','\#',implode("\b|\b", $word_array));		// We can get entities in the string - confuse the regex delimiters
		}
		unset($words);
		return TRUE;
	}

	function filterProfanities($text) 
	{
		global $pref;
		if (!$this->profanityList) 
		{
			return $text;
		}
		if ($pref['profanity_replace']) 
		{
			return preg_replace("#\b".$this->profanityList."\b#is", $pref['profanity_replace'], $text);
		} 
		else 
		{
			return preg_replace_callback("#\b".$this->profanityList."\b#is", array($this, 'replaceProfanities'), $text);
		}
	}
 * 
 * 
	// @function replaceProfanities callback
		@abstract replaces vowels in profanity words with stars
		@param text string - text string to be filtered
		@result filtered text
	
	function replaceProfanities($matches) 
	{


		return preg_replace("#a|e|i|o|u#i", "*" , $matches[0]);
	}
}
*/


?>
