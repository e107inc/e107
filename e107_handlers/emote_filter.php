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
|     $Revision: 1.10 $
|     $Date: 2005-06-04 20:54:41 $
|     $Author: sweetas $
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
			$key = preg_replace("#!(\w{3,}?)$#si", ".\\1", $key);
			$key = preg_replace("#_(\w{3})$#", ".\\1", $key);
			$key =  str_replace("!", "_", $key);

			$value = trim(chop($value));

			$filename = e_IMAGE."emotes/" . $pref['emotepack'] . "/" . $key;

			if(file_exists($filename))
			{
				if(strstr($value, " "))
				{
					$tmp = explode(" ", $value);
					foreach($tmp as $code)
					{
						$this->search[] = " ".$code;
						$this->search[] = "\n".$code;
						$this->replace[] = " <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
						$this->replace[] = "\n <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
					}
					unset($tmp);
				}
				else
				{
					if($value)
					{
						$this->search[] = " ".$value;
						$this->search[] = "\n".$value;
						$this->replace[] = " <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
						$this->replace[] = "\n <img src='".$filename."' alt='' style='vertical-align:middle; border:0' /> ";
					}
				}
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
	
	
	
	
	
?>