<?php

class e_profanityFilter
{
	var $profanityList;
	
	function e_profanityFilter(){
		global $pref;
		
		$words = explode(",",$pref['profanity_words']);
		foreach($words as $word)
		{
			$this -> profanityList .= trim(chop($word))."|";
		}
		$this -> profanityList = substr($this -> profanityList, 0, -1);
		unset($words);
		return TRUE;
	}

	function filterProfanities($text)
	{
		global $pref;
		if(!$this -> profanityList)
		{
			return $text;
		}
		if($pref['profanity_replace'])
		{
			return eregi_replace($this->profanityList, $pref['profanity_replace'], $text);
		}
		else
		{
			return preg_replace_callback("/".$this->profanityList."/is", array($this, 'replaceProfanities'), $text);
		}
	}
	
	function replaceProfanities($matches)
	{
		/*! 
			@function replaceProfanities callback
			@abstract replaces vowels in profanity words with stars
			@param text	string - text string to be filtered
			@result filtered text
		 */

		return eregi_replace("a|e|i|o|u", "*" , $matches[0]);
	}
}

?>