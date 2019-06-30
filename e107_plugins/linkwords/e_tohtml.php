<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2010 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 *
 *
 * $Source: /cvs_backup/e107_0.8/e107_plugins/linkwords/e_tohtml.php,v $
 * $Revision$
 * $Date$
 * $Author$
 */

/**
 *	e107 Linkword plugin
 *
 *	@package	e107_plugins
 *	@subpackage	linkwords
 *	@version 	$Id$;
 *
 *	'Hook' page
 *	The class is 'hooked' by the parser, to add linkword capability to any context where its enabled.
 *
 *	@todo Link to capability for clever display options on tooltips
 */

if (!defined('e107_INIT')) { exit; }
// if (!e107::isInstalled('linkwords')) exit; // This will completely break a site during  upgrades. 

define('LW_CACHE_ENABLE', FALSE);


class e_tohtml_linkwords
{
	protected $lw_enabled = FALSE;		// Default to disabled to start
	var $lwAjaxEnabled = FALSE;		// Adds in Ajax-compatible links
	var $utfMode	= '';			// Flag to enable utf-8 on regex
	protected $word_list 	= array();		// List of link words/phrases
	var $link_list	= array();		// Corresponding list of links to apply
	var $ext_list	= array();		// Flags to determine 'open in new window' for link
	var $tip_list 	= array();		// Store for tooltips
	var $LinkID		= array();		// Unique ID for each linkword
	var $area_opts	= array();		// Process flags for the various contexts
	var $block_list = array();		// Array of 'blocked' pages

	protected $word_class = array();

	protected $customClass  = '';
	protected $wordCount    = array();
	protected $word_limit   = array();
//	protected $maxPerWord   = 3;


	public function enable()
	{
		$this->lw_enabled = true;
	}



	public function setWordData($arr = array())
	{
		foreach($arr as $val)
		{
			$this->word_list[] = $val['word'];
			$this->link_list[] = $val['link'];
			$this->ext_list[] = $val['ext'];
			$this->tip_list[] = $val['tip'];
			$this->word_limit[] = $val['limit'];
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


	/* constructor */
	function __construct()
	{



		$tp = e107::getParser();
	    $pref = e107::pref('core');
	    $frm = e107::getForm();

	//	$this->maxPerWord       = vartrue($pref['lw_max_per_word'], 25);
		$this->customClass      = vartrue($pref['lw_custom_class'],'');
		$this->area_opts        = $pref['lw_context_visibility'];
		$this->utfMode          = (strtolower(CHARSET) == 'utf-8') ? 'u' : '';		// Flag to enable utf-8 on regex //@TODO utfMode probably obsolete
		$this->lwAjaxEnabled    = varset($pref['lw_ajax_enable'],0);

		// See whether they should be active on this page - if not, no point doing anything!
		if(e_ADMIN_AREA === true) { return; }
	//	if ((strpos(e_SELF, ADMINDIR) !== FALSE) || (strpos(e_PAGE, "admin_") !== FALSE)) return;   // No linkwords on admin directories

		// Now see if disabled on specific pages
		$check_url = e_SELF.(defined('e_QUERY') ? "?".e_QUERY : '');
		$this->block_list = explode("|",substr(varset($pref['lw_page_visibility'],''),2));    // Knock off the 'show/hide' flag

		foreach($this->block_list as $p)
		{
			if($p=trim($p))
			{
				if(substr($p, -1) == '!')
				{
					$p = substr($p, 0, -1);
					if(substr($check_url, strlen($p)*-1) == $p) return;
				}
				else 
				{
					if(strpos($check_url, $p) !== FALSE) return;
				}
			}
		} 

		// Will probably need linkwords on this page - so get the info
		define('LW_CACHE_TAG', 'nomd5_linkwords');		// Put it here to avoid conflict on admin pages

		if(LW_CACHE_ENABLE && ($temp = e107::getCache()->retrieve_sys(LW_CACHE_TAG)))
		{
			$ret = eval($temp);
			if ($ret)
			{
				echo "Error reading linkwords cache: {$ret}<br />";
				$temp = '';
			}
			else
			{
				$this->lw_enabled = TRUE;
			}
		}

		if(!vartrue($temp)) 	// Either cache disabled, or no info in cache (or error reading/processing cache)
		{
			$link_sql = e107::getDb('link_sql');

			if($link_sql->select("linkwords", "*", "linkword_active!=1"))
			{
				$this->lw_enabled = true;

				while($row = $link_sql->fetch())
				{

					$lw = $tp->ustrtolower($row['linkword_word']);					// It was trimmed when saved		*utf

					if($row['linkword_active'] == 2)
					{
						$row['linkword_link'] = '';		// Make sure linkword disabled
					}

					if($row['linkword_active'] < 2)
					{
						$row['linkword_tooltip'] = '';	// Make sure tooltip disabled
					}

					$lwID = max($row['linkword_tip_id'], $row['linkword_id']);		// If no specific ID defined, use the DB record ID


					if(strpos($lw,',')) // Several words to same link
					{
						$lwlist = explode(',',$lw);
						foreach ($lwlist as $lw)
						{
							$lw = trim($lw);

							if(empty($lw))
							{
								continue;
							}

							$this->word_list[]      = $lw;
							$this->word_class[]     = 'lw-'.$frm->name2id($lw);
							$this->word_limit[]     = vartrue($row['linkword_limit'],3);

							$this->link_list[]      = $row['linkword_link'];
							$this->tip_list[]       = $row['linkword_tooltip'];
							$this->ext_list[]       = $row['linkword_newwindow'];

							$this->LinkID[]         = $lwID;
						}
					}
					else
					{

						$lw = trim($lw);

						if(empty($lw))
						{
							continue;
						}

						$this->word_list[]      = $lw;
						$this->word_class[]     = 'lw-'.$frm->name2id($lw);
						$this->word_limit[]     = vartrue($row['linkword_limit'],3);
						$this->link_list[]      = $row['linkword_link'];
						$this->tip_list[]       = $row['linkword_tooltip'];
						$this->ext_list[]       = $row['linkword_newwindow'];

						$this->LinkID[]         = $lwID;
					}
				}

				if(LW_CACHE_ENABLE) // Write to file for next time
				{
					$temp = '';
					foreach (array('word_list', 'link_list', 'tip_list', 'ext_list', 'LinkID') as $var)
					{
						$temp .= '$this->'.$var.'='.var_export($this->$var, TRUE).";\n";
					}

					e107::getCache()->set_sys(LW_CACHE_TAG,$temp);
				}
			}
		}


	}


	function to_html($text,$area = 'olddefault')
	{

		if(is_string($this->area_opts))
		{
			$this->area_opts = e107::unserialize($this->area_opts);	
		}

		if($this->area_opts === null)
		{
			$this->area_opts = array();
		}


		if (!$this->lw_enabled || empty($this->area_opts) || !array_key_exists($area,$this->area_opts) || !$this->area_opts[$area])
		{
		//	e107::getDebug()->log("Link words skipped on ".substr($text, 0, 50));
		    return $text;		// No linkwords in disabled areas
		}
	
// Split up by HTML tags and process the odd bits here
		$ptext = "";
		$lflag = FALSE;

		// Shouldn't need utf-8 on next line - just looking for HTML tags
		$content = preg_split('#(<.*?>)#mis', $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

		foreach($content as $cont)
		{
			if ($cont[0] == "<")
			{  // Its some HTML
				$ptext .= $cont;
				if (substr($cont,0,2) == "<a") $lflag = TRUE;
				if (substr($cont,0,3) == "</a") $lflag = FALSE;
			} 
			else   // Its the text in between
			{
				if ($lflag) // Its probably within a link - leave unchanged
				{
					$ptext .= $cont;
				}
				else
				{
					if (trim($cont))
					{  // Some non-white space - worth word matching
						$ptext .= $this->linksproc($cont,0,count($this->word_list));
//						echo "Check linkwords: ".count($this->word_list).'<br />';
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
		$doSamePage = !e107::getPref('lw_notsamepage');

		// Consider next line - stripos is PHP5, and mb_stripos is PHP >= 5.2 - so may well often require handling
//		while (($first < $limit) && (stripos($text,$this->word_list[$first]) === FALSE))   { $first++; };		// *utf   (stripos is PHP5 - compatibility handler implements)



		while (($first < $limit) && (strpos($tp->ustrtolower($text),$this->word_list[$first]) === false))
		{
			$first++;
		}		// *utf

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
			if ($doSamePage || ($newLink != e_SELF.'?'.e_QUERY))
			{
				$linkwd = " href=\"".$newLink."\" ";
				if ($this->ext_list[$first]) { $linkrel[] = 'external'; }		// Determine external links
				$lwClass[] = 'lw-link '.$this->customClass;
			}
		}
		elseif(!empty($this->word_class[$first]))
		{
			$lwClass[] = $this->word_class[$first];
		}

		if (!count($lwClass))
		{
	//		return $this->linksproc($sl,$first+1,$limit);		// Nothing to do - move on to next word (shouldn't really get here)
		}
		if (count($linkrel))
		{
			$linkwd .= " rel='".implode(' ',$linkrel)."'";
		}

		// This splits the text into blocks, some of which will precisely contain a linkword
		$split_line = preg_split('#\b('.$lw.')(\s|\b)#i'.$this->utfMode, $text, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );		// *utf (selected)
	//	$class = "".implode(' ',$lwClass)."' ";
		$class = implode(' ',$lwClass);

		$hash = md5($lw);

		if(!isset($this->wordCount[$hash]))
		{
			$this->wordCount[$hash] = 0;
		}

		foreach ($split_line as $count=>$sl)
		{

			if ($tp->ustrtolower($sl) == $lw && $this->wordCount[$hash] < (int) $this->word_limit[$first])	// Do linkword replace		// We know the linkword is already lower case							// *utf
			{

				$this->wordCount[$hash]++;

				$classCount = " lw-".$this->wordCount[$hash];

				if(empty($linkwd))
				{
					$ret .= "<span class=\"".$class.$classCount."\" ".$tooltip.">".$sl."</span>";
				}
				else
				{
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
}




?>