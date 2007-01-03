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
|     $Source: /cvs_backup/e107_0.8/e107_plugins/linkwords/linkwords.php,v $
|     $Revision: 1.2 $
|     $Date: 2007-01-03 20:50:47 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e_linkwords
{
	var $linkwords = array();
	var $linkhold  = array();  // Placeholders in case url's contain linkwords
	var $linkurls = array();

	function e_linkwords()
	{
		/* constructor */
		$sql = new db;
		if($sql -> db_Select("linkwords", "*", "linkword_active=0"))
		{
			$linkWords = $sql -> db_getList();
			$placeprefix="|*#!|"; // A highly unusual string
			$iPlace = 1;
			foreach($linkWords as $words)
			{
				$word = $words['linkword_word'];
				$this -> linkwords[] = $word;
				$this -> linkurls[] = " <a href='".$words['linkword_link']."' rel='external'>$word</a>";
				$this -> linkhold[] = $placeprefix.$iPlace++.$placeprefix;
				$word2 = substr_replace($word, strtoupper($word[0]), 0, 1);
				$this -> linkwords[] = $word2;
				$this -> linkhold[] = $placeprefix.$iPlace++.$placeprefix;
				$this -> linkurls[] = " <a href='".$words['linkword_link']."' rel='external'>$word2</a>";

			}
		}
	}

	function linkwords($text)
	{
		$content = preg_split('#(<.*?>)#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
		$ptext = "";
		foreach($content as $cont)
		{
			if (strstr($cont, "<"))
			{
				$ptext .= $cont;
			} else {
				$cont2=str_replace($this -> linkwords, $this -> linkhold, $cont);
				$cont2=str_replace($this -> linkhold, $this -> linkurls, $cont2);
				$ptext .= $cont2;
			}
		}
		return $ptext;
	}
}

?>