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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/linkwords/linkwords.php,v $
|     $Revision: 1.2 $
|     $Date: 2005-03-16 03:40:40 $
|     $Author: mcfly_e107 $
+----------------------------------------------------------------------------+
*/
	
class e_linkwords
{
	var $linkwords = array();
	var $linkurls = array();
	 
	function e_linkwords()
	{
		/* constructor */
		global $sql;
		if($sql -> db_Select("linkwords", "*", "linkword_active=0"))
		{
			$linkWords = $sql -> db_getList();
			foreach($linkWords as $words)
			{
				$word = $words['linkword_word'];
				$this -> linkwords[] = $word;
				$this -> linkurls[] = "<a href='".$words['linkword_link']."' rel='external'>$word</a>";
			}
		}
	}

	function linkwords($text)
	{
		return str_replace($this -> linkwords, $this -> linkurls, $text);
	}

}
	
?>