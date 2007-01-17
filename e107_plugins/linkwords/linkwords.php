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
|     $Revision: 1.3 $
|     $Date: 2007-01-17 21:29:28 $
|     $Author: e107steved $
|
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }


class e_linkwords
{
	var $word_list = array();		// List of link words/phrases
	var $link_list = array();		// Corresponding list of links to apply
	var $tip_list  = array();
	var $area_opts = array(
			'title' => FALSE,
			'summary' => FALSE,
			'content' => TRUE,
			'description' => TRUE
			);						// We can set this from prefs later
	var $block_list = array(
//			'page.php?3',
//			'page.php?31!'
			);
	
	function e_linkwords()
	{
		/* constructor */
		$link_sql = new db;
		if($link_sql -> db_Select("linkwords", "*", "linkword_active=0"))
		{
		  while ($row = $link_sql->db_Fetch())
		  {
		    $lw = trim(strtolower($row['linkword_word']));
			if (strpos($lw,','))
			{  // Several words to same link
			  $lwlist = explode(',',$lw);
			  foreach ($lwlist as $lw)
			  {
		        $this->word_list[] = trim($lw);
			    $this->link_list[] = $row['linkword_link'];
			  }
			}
			else
			{
		      $this->word_list[] = $lw;
			  $this->link_list[] = $row['linkword_link'];
			}
		  }
		}
	}


	function linkwords($text,$area = '')
	{
	  if ((strpos(e_SELF, ADMINDIR) !== FALSE) || (strpos(e_PAGE, "admin_") !== FALSE)) return $text;   // No linkwords on admin directories
	  if (($area != '') && (!array_key_exists($area,$this->area_opts) || !$this->area_opts[$area])) return $text;		// No linkwords in disabled areas

// Now see if disabled on specific pages
	  $check_url = e_SELF.(e_QUERY ? "?".e_QUERY : '');
	  foreach ($this->block_list as $p)
	  {
		if(substr($p, -1) == '!')
		{
		  $p = substr($p, 0, -1);
		  if(substr($check_url, strlen($p)*-1) == $p) return $text;
		}
		else 
		{
		  if(strpos($check_url, $p) !== FALSE) return $text;
		}
	  }

// Split up by HTML tags and process the odd bits here
	  $ptext = "";
	  $lflag = FALSE;

	  $content = preg_split('#(<.*?>)#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	  foreach($content as $cont)
	  {
		if (strpos($cont, "<") !== FALSE)
		{  // Its some HTML
			$ptext .= $cont;
			if (strpos($cont,"<a") !== FALSE) $lflag = TRUE;
			if (strpos($cont,"</a") !== FALSE) $lflag = FALSE;
		} 
		else 
		{  // Its the text in between
		  if ($lflag)
		  {  // Its probably within a link - leave unchanged
		    $ptext .= $cont;
		  }
		  else
		  {
			if (trim($cont))
			{  // Some non-white space - worth word matching
			  $ptext .= $this->linksproc($cont,0,count($this->word_list));
			}
			else
			{
			  $ptext .= $cont;
			}
		  }
		}
	  }
	  return $ptext;
	}
	
	function linksproc($text,$first,$limit)
	{  // This function is called recursively - it splits the text up into blocks - some containing a particular linkword
	  while (($first < $limit) && (stripos($text,$this->word_list[$first]) === FALSE))   { $first++; };
	  if ($first == $limit) return $text;		// Return if no linkword found
	  
	  // There's at least one occurrence of the linkword in the text
	  $ret = '';
	  $lw = $this->word_list[$first];
	  // This splits the text into blocks, some of which will precisely contain a linkword
	  $split_line = preg_split('#\b('.$lw.')\b#i', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	  foreach ($split_line AS $sl)
	  {
	    if (strcasecmp($sl,$lw) == 0)
		{  // Do linkword replace
		  $ret .= " <a href='".$this->link_list[$first]."' rel='external'>{$sl}</a>";
		}
		elseif (trim($sl))
		{  // Something worthwhile left - look for more linkwords in it
		  $ret .= $this->linksproc($sl,$first+1,$limit);
		}
		else
		{
		  $ret .= $sl;   // Probably just some white space
		}
	  }
	  return $ret;
	}
}

?>