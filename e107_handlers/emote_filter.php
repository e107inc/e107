<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_handlers/emote_filter.php,v $
|     $Revision: 1.7 $
|     $Date: 2005-05-29 13:48:19 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
class e_emotefilter {
	var $search;
	var $replace;
	 
	function e_emotefilter() /* constructor */
	{
		global $sysprefs, $pref;
		$emotes = $sysprefs -> getArray("emote_".$pref['emotepack']);

		foreach($emotes as $key => $value)
		{

			$key =  str_replace("!", ".", $key);

			$filename = e_IMAGE."emotes/" . $pref['emotepack'] . "/" . $key;

			
			if(file_exists($filename))
			{
				if(strstr($value, " "))
				{
					$tmp = explode(" ", $value);
					foreach($tmp as $code)
					{
						if(trim(chop($code)))
						{
							$this->searcha[] = " ".$code;
							$this->searchb[] = "\n".$code;
							$this->replace[] = " <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
						}
					}
				}
				else
				{
					if(trim(chop($code)))
					{
						$this->searcha[] = " ".$code;
						$this->searchb[] = "\n".$code;
						$this->replace[] = " <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
					}
				}
			}
		}
	}
	 
	function filterEmotes($text)
	{	 
		$text = str_replace($this->searcha, $this->replace, $text);
		$text = str_replace($this->searchb, $this->replace, $text);
		return $text;
	}
	 
	function filterEmotesRev($text)
	{
		$text = str_replace($this->replace, $this->searcha, $text);
		return $text;
	}
}
	
	
	
	
	
?>