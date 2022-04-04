<?php


/**
 *
 */
class e_profanity
{

	protected $profanityList;
	private $pref;

	public function __construct()
	{

		$this->pref = e107::getPref();

		if(empty($this->pref['profanity_words']))
		{
			return null;
		}

		$words = explode(',', $this->pref['profanity_words']);
		$word_array = array();
		foreach($words as $word)
		{
			$word = trim($word);
			if($word != '')
			{
				$word_array[] = $word;
				if(strpos($word, '&#036;') !== false)
				{
					$word_array[] = str_replace('&#036;', '\$', $word);        // Special case - '$' may be 'in clear' or as entity
				}
			}
		}
		if(count($word_array))
		{
			$this->profanityList = str_replace('#', '\#', implode("\b|\b", $word_array));        // We can get entities in the string - confuse the regex delimiters
		}
		unset($words);

		return true;
	}

	/**
	 * @param $text
	 * @return string|string[]|null
	 */
	public function filterProfanities($text)
	{

		if(empty($this->profanityList))
		{
			return $text;
		}

		if(!empty($this->pref['profanity_replace']))
		{
			return preg_replace("#\b" . $this->profanityList . "\b#is", $this->pref['profanity_replace'], $text);
		}

		return preg_replace_callback("#\b" . $this->profanityList . "\b#is", array($this, 'replaceProfanities'), $text);
	}

	/**
	 *
	 * @param $matches
	 * @return string|string[]|null
	 */
	public function replaceProfanities($matches)
	{

		/*!
		@function replaceProfanities callback
		@abstract replaces vowels in profanity words with stars
		@param text string - text string to be filtered
		@result filtered text
		*/

		return preg_replace('#a|e|i|o|u#i', '*', $matches[0]);
	}
}