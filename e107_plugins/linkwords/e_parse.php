<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2021 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */



if (!defined('e107_INIT')) { exit; }


class linkwords_parse
{
	protected $lw_enabled = FALSE;		// Default to disabled to start
	protected $lwAjaxEnabled = FALSE;		// Adds in Ajax-compatible links
	protected $utfMode	= '';			// Flag to enable utf-8 on regex
	protected $cache = true;
	protected $suppressSamePageLink = false;
	protected $hash;
	protected $admin = false;

	protected $word_list 	= array();		// List of link words/phrases
	private $link_list	= array();		// Corresponding list of links to apply
	private $ext_list	= array();		// Flags to determine 'open in new window' for link
	private $tip_list 	= array();		// Store for tooltips
	private $rel_list   = array();
	private $LinkID		= array();		// Unique ID for each linkword
	private $area_opts;		// Process flags for the various contexts
	private $block_list;		// Array of 'blocked' pages

	protected $word_class = array();

	protected $customClass  = '';
	protected $wordCount    = array();
	protected $intLinks = 0;
	protected $extLinks = 0;
	protected $word_limit   = array();

	const LW_CACHE_TAG = 'linkwords';
//	protected $maxPerWord   = 3;


	/* constructor */
	function __construct()
	{

		// See whether they should be active on this page - if not, no point doing anything!
		if(e_ADMIN_AREA === true && empty($_POST['runLinkwordTest']))
		{
			return;
		}

		$this->init();

	}

	public function enable()
	{
		$this->lw_enabled = true;
	}

	public function cache($var)
	{
		$this->cache = (bool) $var;
	}


	public function setWordData($arr = array())
	{
		foreach($arr as $val)
		{
			$this->word_list[]  = $val['word'];
			$this->link_list[]  = varset($val['link']);
			$this->ext_list[]   = varset($val['ext']);
			$this->tip_list[]   = varset($val['tip']);
			$this->word_limit[] = varset($val['limit']);
			$this->LinkID[]     = varset($val['id']);
		}
	}

	public function setAreaOpts($arr = array())
	{
		$this->area_opts = $arr;
	}


	public function setLink($arr)
	{
		$this->word_list = $arr;
	}



	private function loadRow($lw, $row)
	{
		$lw = trim($lw);

		if(empty($lw))
		{
			return;
		}

		$frm = e107::getForm();

		$this->word_list[]      = $lw;
		$this->word_class[]     = 'lw-'.$frm->name2id($lw);
		$this->word_limit[]     = vartrue($row['linkword_limit'],3);
		$this->link_list[]      = $row['linkword_link'];
		$this->tip_list[]       = $row['linkword_tooltip'];
		$this->ext_list[]       = $row['linkword_newwindow'];
		$this->rel_list[]       = str_replace(",", ' ', $row['linkword_rel']);
		$this->LinkID[]         = max($row['linkword_tip_id'], $row['linkword_id']);		// If no specific ID defined, use the DB record ID

	}


	public function toHTML($text,$area = 'olddefault')
	{

		if(is_string($this->area_opts))
		{
			$this->area_opts = e107::unserialize($this->area_opts);
		}

		if($this->area_opts === null)
		{
			$this->area_opts = array();
		}

		if (!$this->lw_enabled || empty($this->area_opts) || !isset($this->area_opts[$area]))
		{
			// e107::getDebug()->log("Link words skipped on ".substr($text, 0, 50));
		    return $text;		// No linkwords in disabled areas
		}

		// Split up by HTML tags and process the odd bits here
		$ptext = "";
		$lflag = false;
		$cflag = false; // commented code prsent.

		// Shouldn't need utf-8 on next line - just looking for HTML tags
		$content = preg_split('#(<.*?>)#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

		$range = range(1,5);

		foreach($content as $cont)
		{
			if(strpos($cont, '-->') !== false)
			{
				$cflag = false; // end of commented code
				$ptext .= $cont;
				continue;
			}

			if ($cont[0] === "<")  // Its some HTML
			{
				$ptext .= $cont;
				if (strpos($cont, "<!--") === 0) $cflag = true; // start of commented code.
				if (strpos($cont, "<a") === 0) $lflag = true; // start of link
				if (strpos($cont, "</a") === 0) $lflag = false; // end of link.

				if($area === 'BODY' && !isset($this->area_opts['TITLE'])) // disable linking on header tag content unless enabled in prefs.
				{
					// loop thru <h1>, <h2> etc.
					foreach($range as $c)
					{
						$hOpenTag = '<h'.$c;
						$hCloseTag = '</h'.$c;
						if (strpos($cont, $hOpenTag) === 0) $lflag = true;
						if (strpos($cont, $hCloseTag) === 0) $lflag = false;
					}
				}

			}
			else   // Its the text in between
			{
				if ($lflag || $cflag) // Its probably within a link - leave unchanged
				{
					$ptext .= $cont;
				}
				else
				{
					if (trim($cont))
					{
						// Some non-white space - worth word matching
						$ptext .= $this->linksproc($cont,0,count($this->word_list));
						// echo "Check linkwords: ".count($this->word_list).'<br />';
					}
					else
					{
						$ptext .= $cont;
					}
				}
			}
		}

	//	print_a($this->wordCount);
		return $ptext;
	}


	/**
	 * This function is called recursively - it splits the text up into blocks - some containing a particular linkword
	 * @param $text
	 * @param $first
	 * @param $limit
	 * @return string
	 */
	function linksproc($text,$first,$limit)
	{
		$tp = e107::getParser();

		for (; $first < $limit; $first ++)
		{
			if (empty($this->word_list[$first])) continue;
			if (strpos($tp->ustrtolower($text), $tp->ustrtolower($this->word_list[$first])) !== false) break;
		}

		if ($first == $limit)
		{
			 return $text;		// Return if no linkword found
		}

		// There's at least one occurrence of the linkword in the text
		// Prepare all info once only
		// If supporting Ajax, use the following:
		// <a href='link url' rel='external linkwordId::122' class='linkword-ajax'>
		// linkwordId::122 is a unique ID

		$ret = '';
		$linkwd = '';
		$linkrel = array();
		//		$linkwd = "href='#' ";				// Not relevant for Prototype, but needed with 'pure' JS to make tooltip stuff work - doesn't find link elements without href
		$lwClass  = array();
		$lw = $this->word_list[$first];		// This is the word we're matching - in lower case in our 'master' list
		$tooltip = '';

		if ($this->tip_list[$first])
		{	// Got tooltip
			if ($this->lwAjaxEnabled)
			{
				$linkrel[] = 'linkwordID::'.$this->LinkID[$first];
				$lwClass[] = 'lw-ajax '.$this->customClass;
			}
			else
			{
				$tooltip = " title=\"{$this->tip_list[$first]}\" ";
				$lwClass[] = 'lw-tip '.$this->customClass;
			}
		}

		if ($this->link_list[$first])  // Got link
		{
			$newLink = $tp->replaceConstants($this->link_list[$first], 'full');

			if (!$this->suppressSamePageLink || (($newLink !== e_REQUEST_URL) && $newLink !== e_REQUEST_URI))
			{
				$linkwd = " href=\"".$newLink."\" ";

				if(!empty($this->rel_list[$first]))
				{
					$linkrel[] = $this->rel_list[$first];
				}
				elseif($this->ext_list[$first]) // Determine external links
				{
					$linkrel[] = 'noopener external';
				}

				$lwClass[] = 'lw-link '.$this->customClass;
			}
		}
		elseif(!empty($this->word_class[$first]))
		{
			$lwClass[] = $this->word_class[$first];
		}

	//	if (!count($lwClass))
	//	{
			//	return $this->linksproc($sl,$first+1,$limit);		// Nothing to do - move on to next word (shouldn't really get here)
	//	}

		if (count($linkrel))
		{
			$linkwd .= " rel='".implode(' ',$linkrel)."'";
		}

		// This splits the text into blocks, some of which will precisely contain a linkword
		$split_line = preg_split('#\b('.$lw.')(\s|\b)#i'.$this->utfMode, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );		// *utf (selected)
		//	$class = "".implode(' ',$lwClass)."' ";

		$class = implode(' ',$lwClass);

		$hash = md5($lw);
		$this->hash = $hash;

		if(!isset($this->wordCount[$hash]))
		{
			$this->wordCount[$hash] = 0;
		}

		foreach ($split_line as $count=>$sl)
		{
			if ($this->wordCount[$hash] < (int) $this->word_limit[$first] && ($tp->ustrtolower($sl) === $tp->ustrtolower($lw)))	// Do linkword replace		// We know the linkword is already lower case							// *utf
			{
				$this->wordCount[$hash]++;



				$classCount = " lw-".$this->wordCount[$hash];

				if(empty($linkwd))
				{
					$ret .= "<span class=\"".$class.$classCount."\" ".$tooltip.">".$sl."</span>";
				}
				else
				{
					if(strpos($linkwd,'http')!==false)
					{
						$this->extLinks++;
					}
					else
					{
						$this->intLinks++;
					}

					$ret .= "<a class=\"".$class.$classCount."\" ".$linkwd.$tooltip.">".$sl."</a>";
				}

			}
			elseif (trim($sl)) // Something worthwhile left - look for more linkwords in it
			{
				$ret .= $this->linksproc($sl,$first+1,$limit);
			}
			else
			{
				$ret .= $sl;   // Probably just some white space
			}
		}

		return $ret;
	}

	function getStats()
	{
		return [
			'internal'  => $this->intLinks,
			'external'  => $this->extLinks,
		];

	}

	/**
	 * @return void
	 */
	public function init()
	{

		$tp = e107::getParser();
		$pref = e107::pref('linkwords');
		$frm = e107::getForm();

		//	$this->maxPerWord       = vartrue($pref['lw_max_per_word'], 25);
		$this->customClass = vartrue($pref['lw_custom_class']);
		$this->area_opts = (array) varset($pref['lw_context_visibility']);
		$this->utfMode = (strtolower(CHARSET) === 'utf-8') ? 'u' : '';
		$this->lwAjaxEnabled = varset($pref['lw_ajax_enable'], 0);
		$this->suppressSamePageLink = (bool) vartrue($pref['lw_notsamepage'], false);


		// Now see if disabled on specific pages
		$check_url = e_SELF . (defined('e_QUERY') ? "?" . e_QUERY : '');
		$this->block_list = explode("|", substr(varset($pref['lw_page_visibility']), 2));    // Knock off the 'show/hide' flag

		foreach($this->block_list as $p)
		{
			if($p = trim($p))
			{
				if(substr($p, -1) === '!')
				{
					$p = substr($p, 0, -1);
					if(substr($check_url, strlen($p) * -1) == $p)
					{
						return;
					}
				}
				else
				{
					if(strpos($check_url, $p) !== false)
					{
						return;
					}
				}
			}
		}


		if($this->cache && ($temp = e107::getCache()->retrieve(self::LW_CACHE_TAG, false, true, true)))
		{
			if($data = e107::unserialize($temp))
			{
				foreach($data as $key => $val)
				{
					$this->$key = $val;
				}

				$this->lw_enabled = true;
			}
			else
			{
				trigger_error("Error reading linkwords cache: " . self::LW_CACHE_TAG);
			}

		}

		if(empty($temp))    // Either cache disabled, or no info in cache (or error reading/processing cache)
		{
			$link_sql = e107::getDb('link_sql');

			if($link_sql->select("linkwords", "*", "linkword_active!=1"))
			{
				$this->lw_enabled = true;

				while($row = $link_sql->fetch())
				{

					$lw = $tp->ustrtolower($row['linkword_word']);                    // It was trimmed when saved		*utf
					$lw = str_replace('&#039;', "'", $lw); // Fix for apostrophies.

					if($row['linkword_active'] == 2)
					{
						$row['linkword_link'] = '';        // Make sure linkword disabled
					}

					if($row['linkword_active'] < 2)
					{
						$row['linkword_tooltip'] = '';    // Make sure tooltip disabled
					}


					if(strpos($lw, ',')) // Several words to same link
					{
						$lwlist = explode(',', $lw);
						foreach($lwlist as $lw)
						{
							$this->loadRow($lw, $row);
						}
					}
					else
					{
						$this->loadRow($lw, $row);
					}
				}

				if($this->cache) // Write to file for next time
				{
					$temp = [];
					foreach(array('word_list', 'word_class', 'word_limit', 'link_list', 'tip_list', 'ext_list', 'rel_list', 'LinkID') as $var)
					{
						$temp[$var] = $this->$var;
					}

					e107::getCache()->set(self::LW_CACHE_TAG, e107::serialize($temp, 'json'), true, true, true);
				}
			}
		}
	}
}




