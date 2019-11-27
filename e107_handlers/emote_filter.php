<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @DEPRECATED FILE
 */

if (!defined('e107_INIT')) { exit; }
/*

class e_emotefilter {
	var $search;
	var $replace;
	var $emotes;
	 
	function e_emotefilter() 
	{		
		$pref = e107::getPref();
		
		if(!$pref['emotepack'])	
		{	
			$pref['emotepack'] = "default";
			save_prefs();
		}
			
		$this->emotes = e107::getConfig("emote")->getPref();
		
		if(!vartrue($this->emotes))
		{
			return;
		}

		foreach($this->emotes as $key => $value)
		{
		  $value = trim($value);

		  if ($value)
		  {	// Only 'activate' emote if there's a substitution string set
			$key = preg_replace("#!(\w{3,}?)$#si", ".\\1", $key);
			// Next two probably to sort out legacy issues - may not be required any more
			$key = preg_replace("#_(\w{3})$#", ".\\1", $key);
			$key = str_replace("!", "_", $key);

			  $filename = e_IMAGE."emotes/" . $pref['emotepack'] . "/" . $key;
			  
			  $fileloc = SITEURLBASE.e_IMAGE_ABS."emotes/" . $pref['emotepack'] . "/" . $key;

			  if(file_exists($filename))
			  {
				if(strstr($value, " "))
				{
					$tmp = explode(" ", $value);
					foreach($tmp as $code)
					{
						$this->search[] = " ".$code;
						$this->search[] = "\n".$code;
						//TODO CSS class?
						$this->replace[] = " <img src='".$fileloc."' alt='' style='vertical-align:middle; border:0' /> ";
						$this->replace[] = "\n <img src='".$fileloc."' alt='' style='vertical-align:middle; border:0' /> ";
					}
					unset($tmp);
				}
				else
				{
					if($value)
					{
						$this->search[] = " ".$value;
						$this->search[] = "\n".$value;
						//TODO CSS class?
						$this->replace[] = " <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
						$this->replace[] = "\n <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
					}
				}
			  }
		  }
		  else
		  {
			unset($this->emotes[$key]);
		  }
		}
	}
	 
	function filterEmotes($text)
	{	 
		$text = str_replace($this->search, $this->replace, $text);
		return $text;
	}
	 
	function filterEmotesRev($text)
	{
		$text = str_replace($this->replace, $this->search, $text);
		return $text;
	}
}

*/

	
	
	
	
	
?>