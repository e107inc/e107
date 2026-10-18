<?php
/*
 * e107 website system
 *
 * Copyright (C) e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * $URL$
 * $Id$
 */

/**
 *
 * @package     e107
 * @category	e107_handlers
 * @version     $Id$
 * @author      e107inc
 *
 *	bbcode_handler - processes bbcodes within strings.
 *
 *	Separate processing (via class-based bbcodes) for pre-save and pre-display
 */

if (!defined('e107_INIT')) { exit; }


/**
 *
 */
class e_bbcode
{
	private $bbList;			// Caches the file contents for each bbcode processed
	private $bbLocation = array();		// Location for each file - 'core' or a plugin name
	private $preProcess = false;	// Set when processing bbcodes prior to saving
	private $core_bb = array();
	private $class = false;
	private $resizePrefs = array();
	private static $bbPattern = '#^\[' . e_parse::BBCODE_TAG_LEAVES_NO_HTML_TAG_OPEN . '(/?)([A-Za-z_]+)(\d*)([=:]?)(.*?)]$#i';	// Pattern to split up bbcodes

	function __construct()
	{
		$pref = e107::getPref();

		$this->resizePrefs = varset($pref['resize_dimensions']);

		$this->core_bb = array(
			'alert',
			'blockquote', 'img', 'i', 'u', 'center',
			'_br', 'color', 'size', 'code',
			'flash', 'link', 'email',
			'url', 'quote', 'left', 'right',
			'b', 'justify', 'file', 'stream',
			'textarea', 'list', 'time',
			'spoiler', 'hide', 'youtube', 'sanitised',
			'p', 'h', 'nobr', 'block', 'table', 'th', 'tr', 'tbody', 'td', 'video', 'glyph'
		);

		foreach($this->core_bb as $c)
		{
			$this->bbLocation[$c] = 'core';
		}

		// grab list of plugin bbcodes.
		if(isset($pref['bbcode_list']) && $pref['bbcode_list'] != '')
		{
			foreach($pref['bbcode_list'] as $path=>$namearray)
			{
				foreach($namearray as $code=>$uclass)
				{
					$this->bbLocation[$code] = $path;
				}
			}
		}

		// Eliminate duplicates
		$this->bbLocation = array_diff($this->bbLocation, array(''));
		krsort($this->bbLocation);
	}


	/**
	 * Whether the body of an opening bbcode tag, digits and parameter and all as in size2 or youtube=600, names a code in the register. {@see e_parse::isBBcode()}
	 *
	 * @param string $code
	 * @return bool
	 */
	public function isRegistered($code)
	{

		if (!is_string($code) || !preg_match(self::$bbPattern, '['.$code.']', $matches) || $matches[1] !== '')
		{
			return false;
		}

		return array_key_exists(strtolower($matches[2]), $this->bbLocation);
	}


	/**
	 * The outermost [hide] blocks in $text: every span that {@see e_bbcode::parseBBCodes()} pairs as a hide block as typed, or with any tag beginning [hide or [/hide read as one, the second time with a closer taken by the oldest open opener spelled otherwise than plain; a [code] that is never closed, or that holds the [/hide] of a block left open, shields no tag from there on.
	 *
	 * @param string $text
	 * @return array[] in order of position, each with the keys start and length (bytes into $text), open, content and close
	 */
	public function hideBlocks($text)
	{
		$outer = array();

		foreach ($this->hideBlockTree($text) as $block)
		{
			if ($block['parent'] === -1)
			{
				$outer[] = array(
					'start'   => $block['start'],
					'length'  => $block['length'],
					'open'    => $block['open'],
					'close'   => $block['close'],
					'content' => (string) substr($text, $block['start'] + strlen($block['open']), $block['length'] - strlen($block['open']) - strlen($block['close'])),
				);
			}
		}

		return $outer;
	}


	/**
	 * Every [hide] block in $text, those inside another included: the outermost as {@see e_bbcode::hideBlocks()} reports them, and inside the content of each, the blocks the same pairs make there, found the same way.
	 *
	 * @param string $text
	 * @return array[] in order of position, each with the keys start and length (bytes into $text), open, close, and parent (the index of the block whose content holds it, or -1)
	 */
	public function hideBlockTree($text)
	{
		if (!is_string($text) || stripos($text, '[hide') === false)
		{
			return array();
		}

		$pairs = array();

		foreach (array(array(false, false), array(true, false), array(true, true)) as $reading)
		{
			foreach ($this->pairsOfBlockCode($text, 'hide', $reading[0], $reading[1]) as $pair)
			{
				$span = $pair['start'] . ':' . $pair['length'];

				if (!isset($pairs[$span]))
				{
					$pairs[$span] = $pair;
				}
			}
		}

		$pairs = array_values($pairs);
		$starts = array();
		$lengths = array();

		foreach ($pairs as $pair)
		{
			$starts[] = $pair['start'];
			$lengths[] = -$pair['length'];
		}

		array_multisort($starts, SORT_ASC, SORT_NUMERIC, $lengths, SORT_ASC, SORT_NUMERIC, $pairs);

		$blocks = array();
		$open = array();

		foreach ($pairs as $pair)
		{
			$end = $pair['start'] + $pair['length'];

			while ($open && ($blocks[$open[count($open) - 1]]['end'] <= $pair['start']))
			{
				array_pop($open);
			}

			$low = 0;
			$high = count($open);

			while ($low < $high)
			{
				$middle = ($low + $high) >> 1;
				$holder = $blocks[$open[$middle]];

				if (($pair['start'] >= $holder['contentStart']) && ($end <= $holder['contentEnd']))
				{
					$low = $middle + 1;
				}
				else
				{
					$high = $middle;
				}
			}

			if ($low === count($open))
			{
				$open[] = count($blocks);
				$blocks[] = array(
					'start'        => $pair['start'],
					'length'       => $pair['length'],
					'open'         => $pair['open'],
					'close'        => $pair['close'],
					'parent'       => $low ? $open[$low - 1] : -1,
					'end'          => $end,
					'contentStart' => $pair['start'] + strlen($pair['open']),
					'contentEnd'   => $end - strlen($pair['close']),
				);
			}
			elseif ($end > $blocks[$open[$low]]['end'])
			{
				$index = $open[$low];
				$blocks[$index]['length'] = $end - $blocks[$index]['start'];
				$blocks[$index]['close'] = $pair['close'];
				$blocks[$index]['end'] = $end;
				$blocks[$index]['contentEnd'] = $end - strlen($pair['close']);
			}
		}

		foreach ($blocks as $index => $block)
		{
			unset($blocks[$index]['end'], $blocks[$index]['contentStart'], $blocks[$index]['contentEnd']);
		}

		return $blocks;
	}


	/**
	 * Every pair {@see e_bbcode::parseBBCodes()} makes of $code in $text, read again once from the first [code] left open if there is one.
	 *
	 * @param string $text
	 * @param string $code
	 * @param bool   $anySpelling whether any tag beginning with the code is read as it
	 * @param bool   $outermost whether a closing tag pairs with the oldest open code spelled otherwise than plain, rather than the newest
	 * @return array[] each with the keys start, length, open and close
	 */
	private function pairsOfBlockCode($text, $code, $anySpelling, $outermost)
	{
		$find = array('code' => $code, 'anySpelling' => $anySpelling, 'outermost' => $outermost, 'codeLeftOpen' => null);
		$read = $this->walkBBCodes($text, true, false, array(), $find);

		if ($read['codeLeftOpen'] !== null)
		{
			$find['codeLeftOpen'] = $read['codeLeftOpen'];
			$read = $this->walkBBCodes($text, true, false, array(), $find);
		}

		return $read['pairs'];
	}


	/**
	 *	Parse a string for bbcodes.
	 *	Process using the 'pre-save' or 'display' routines as appropriate
	 *
	 *	@var string $value - the string to be processed
	 *	@var int $p_ID - ID of a user (the 'post ID') needed by some bbcodes in display mode
	 *	@var string|boolean $force_lower - determines whether bbcode detection is case-insensitive
	 *			TRUE - case-insensitive
	 *			'default' - case-insensitive
	 *			FALSE - case-sensitive (only lower case bbcodes processed)
	 *	@var string|boolean $bbStrip - determines action when a bbcode is encountered.
	 *			TRUE (boolean or word), all bbcodes are stripped.
	 *			FALSE - normal display processing of all bbcodes
	 *			comma separated (lower case) list - only the listed codes are stripped (and the rest are processed)
	 *			A stripped code is paired like any other and leaves its text; an opening tag that nothing closes is left as typed
	 *			If the first word is 'PRE', sets pre-save mode. Any other parameters follow, comma separated
	 *
	 *	@return string processed data
	 *
	 *	Code uses a crude stack-based syntax analyser to handle nested bbcodes (including nested 'size' bbcodes, for example)
	 */
	function parseBBCodes($value, $p_ID='', $force_lower = 'default', $bbStrip = FALSE)
	{
		global $postID;
		$postID = $p_ID;


		if (strlen($value) <= 6) return $value;     		// Don't waste time on trivia!
		if ($force_lower == 'default') $force_lower = TRUE;	// Set the default behaviour if not overridden
		$outerPreProcess = $this->preProcess;
		$this->preProcess = FALSE;

		$strip_array = array();
		if (!is_bool($bbStrip))
		{
			$strip_array = explode(',',$bbStrip);
			if ($strip_array[0] == 'PRE')
			{
				$this->preProcess = "toDB";
				unset($strip_array[0]);
				if (count($strip_array) == 0)
				{
					$bbStrip = FALSE;
				}
				elseif (in_array('TRUE', $strip_array))
				{
					$bbStrip = TRUE;
				}

			}
		}

		$result = $this->walkBBCodes($value, $force_lower, $bbStrip, $strip_array, null);
		$this->preProcess = $outerPreProcess;

		return $result;
	}


	/**
	 * Every registered bbcode, a single code such as _br with its leading underscore, mapped to 'core' or to the folder of the plugin whose handler renders it.
	 *
	 * @return array
	 */
	public function getRegistered()
	{
		return $this->bbLocation;
	}


	/**
	 * Read the bbcodes in $value as {@see e_bbcode::parseBBCodes()} pairs them and render them, or, when $find names a code, record where each pair of that code lies and render nothing; a closing tag takes its opener off the stack and leaves those above it open.
	 *
	 * @param string     $value
	 * @param bool       $forceLower whether a bbcode word is read in any case
	 * @param bool       $bbStrip whether every bbcode is discarded
	 * @param string[]   $stripArray the bbcodes discarded
	 * @param array|null $find null to render; otherwise the keys code, anySpelling and outermost as {@see e_bbcode::pairsOfBlockCode()} takes them, and codeLeftOpen, the offset from which [code] shields nothing, or null
	 * @return string|array the rendered text; under $find, the keys pairs (each with start, length, open and close) and codeLeftOpen (the offset to read again from, or null)
	 */
	private function walkBBCodes($value, $forceLower, $bbStrip, $stripArray, $find)
	{
		$render = ($find === null);
		$blockCode = $render ? null : $find['code'];
		$codeLeftOpen = $render ? null : $find['codeLeftOpen'];
		$anySpelling = !$render && $find['anySpelling'];
		$outermost = !$render && $find['outermost'];

		$entries = array();
		$stacked = 0;
		$newest = -1;
		$byKey = array();
		$spelledOtherwise = array();
		$otherwiseFrom = array();
		$openBlockCodes = 0;
		$pairs = array();

		$result = '';										// Accumulates fully processed text
		$stacktext = '';									// Accumulates text which might be subject to one or more bbcodes
		$nopro = false;										// Blocks processing within [code]...[/code] tags
		$noproFrom = 0;										// Where the [code] that set $nopro begins
		$swallowedFrom = null;								// Where a [code] begins that took in the closer of an open $blockCode
		$bbsep = '';
		$pattern = self::$bbPattern;
		// $matches[1] - '/' for a closing tag. Otherwise empty string
		// $matches[2] - the bbcode word
		// $matches[3] - any digits immediately following the bbcode word
		// $matches[4] - '=' or ':' according to the separator used
		// $matches[5] - any parameter

		$content = preg_split('#(\[(?:\w|/\w).*?\])#ms', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		$offset = 0;

		foreach ($content as $cont)
		{  // Each chunk is either a bbcode or a piece of text
			$contPos = $offset;
			$offset += strlen($cont);
			$is_proc = false;

			while (!$is_proc)
			{
				$oddtext = '';

				if ($cont[0] === '[')
				{  // We've got a bbcode - split it up and process it
					$matches = array();
					preg_match($pattern, $cont, $matches);
					$bbparam = isset($matches[5]) ? $matches[5] : '';
					$bbword = isset($matches[2]) ? $matches[2] : '';
					$closing = isset($matches[1]) && ($matches[1] === '/');

					if (substr($cont, 1, 1) !== '/')
					{
						$bbsep = isset($matches[4]) ? $matches[4] : '';
					}

					if ($forceLower)
					{
						$bbword = strtolower($bbword);
					}

					if (($bbword !== '') && $anySpelling && (stripos($cont, '['.$matches[1].$blockCode) === 0))
					{
						$bbword = $blockCode;
						$matches[3] = '';
					}

					if ($nopro && ($bbword === 'code') && $closing)
					{
						$nopro = false;		// End of code block
					}

					if ($nopro && ($bbword === $blockCode) && $closing && ($swallowedFrom === null) && ($openBlockCodes > 0))
					{
						$swallowedFrom = $noproFrom;
					}

					if (($bbword !== '') && !$nopro)
					{  // Got a code to process here
						$key = $bbword.':'.self::numbersKey($matches[3]);

						$strip = ($bbStrip === true) || in_array($bbword, $stripArray);

						if ($strip && !$closing && array_key_exists('_'.$bbword, $this->bbLocation))
						{
							$is_proc = true;		// Just discard this single bbcode
						}
						elseif ($closing)
						{  // Closing code to process
							$taken = -1;

							if ($outermost && ($bbword === $blockCode) && isset($spelledOtherwise[$key]))
							{
								while (isset($spelledOtherwise[$key][$otherwiseFrom[$key]]) && !isset($entries[$spelledOtherwise[$key][$otherwiseFrom[$key]]]))
								{
									$otherwiseFrom[$key]++;
								}

								if (isset($spelledOtherwise[$key][$otherwiseFrom[$key]]))
								{
									$taken = $spelledOtherwise[$key][$otherwiseFrom[$key]];
								}
							}

							if (($taken < 0) && isset($byKey[$key]))
							{
								$last = count($byKey[$key]) - 1;

								while (($last >= 0) && !isset($entries[$byKey[$key][$last]]))
								{
									array_pop($byKey[$key]);
									$last--;
								}

								if ($last >= 0)
								{
									$taken = $byKey[$key][$last];
								}
							}

							if ($taken >= 0)
							{
								$entry = $entries[$taken];

								if (!$render)
								{
									if ($bbword === $blockCode)
									{
										$pairs[] = array(
											'start'  => $entry['pos'],
											'length' => $contPos + strlen($cont) - $entry['pos'],
											'open'   => $entry['block'],
											'close'  => $cont,
										);
									}

									if ($entry['code'] === $blockCode)
									{
										$openBlockCodes--;
									}

									self::unstack($taken, $entries, $newest);
								}
								else
								{
									if ($this->preProcess === 'toDB')
									{  // On save, what lies above it stays inside the pair, as typed
										$above = array();

										for ($id = $entries[$taken]['newer']; $id >= 0; $id = $entries[$id]['newer'])
										{
											$above[] = self::typed($entries[$id]);
										}

										while ($newest !== $taken)
										{
											self::unstack($newest, $entries, $newest);
										}

										if ($above)
										{
											$stacktext = implode('', $above).$stacktext;
										}
									}

									if (!$strip)
									{
										$stacktext = $this->proc_bbcode($bbword, $entry['param'], $stacktext, $bbparam, $entry['bbsep'], $entry['block'].$stacktext.$cont);
									}

									self::unstack($taken, $entries, $newest);

									while (($newest >= 0) && ($entries[$newest]['type'] === 'text'))
									{
										$stacktext = $entries[$newest]['code'].$stacktext;   // Need to insert text at front
										self::unstack($newest, $entries, $newest);
									}

									if ($newest < 0)
									{
										$result .= $stacktext;
										$stacktext = '';
									}
								}

								$is_proc = true;
							}
						}
						elseif (array_key_exists('_'.$bbword, $this->bbLocation))
						{  // Single code to process
							if ($render)
							{
								$single = $this->proc_bbcode('_'.$bbword,$bbparam,'','','',$cont);

								if ($newest < 0)
								{
									$result .= $single;
								}
								else
								{
									$stacktext .= $single;
								}
							}

							$is_proc = true;
						}
						elseif ($strip || array_key_exists($bbword,$this->bbLocation))
						{
							if ($render && ($stacktext != ''))
							{ // Stack the text we've accumulated so far
								self::stack($stacked++, array('type' => 'text', 'code' => $stacktext), $entries, $newest);
								$stacktext = '';
							}

							$id = $stacked++;
							self::stack($id, array('type' => 'bbcode', 'code' => $bbword, 'numbers' => $matches[3], 'param' => $bbparam, 'bbsep' => $bbsep, 'block' => $cont, 'pos' => $contPos), $entries, $newest);
							$byKey[$key][] = $id;

							if ($bbword === $blockCode)
							{
								$openBlockCodes++;

								if ($outermost && !preg_match('#^\['.preg_quote($blockCode, '#').'(?![A-Za-z_\d])#i', $cont))
								{
									$spelledOtherwise[$key][] = $id;

									if (!isset($otherwiseFrom[$key]))
									{
										$otherwiseFrom[$key] = 0;
									}
								}
							}

							if (($bbword === 'code') && !$strip)
							{
								$nopro = ($codeLeftOpen === null) || ($contPos < $codeLeftOpen);
								$noproFrom = $contPos;
							}

							$is_proc = true;
						}
					}

					// Next lines could be deleted - but gives better rejection of 'stray' opening brackets
					if ((!$is_proc) && (($temp = strrpos($cont,"[")) !== 0))
					{
						$oddtext = (string) substr($cont,0,$temp);
						$cont = (string) substr($cont,$temp);
						$contPos += $temp;
					}
				}

				if (!$is_proc)
				{  // We've got some text between bbcodes (or possibly text in front of a bbcode)
					if ($oddtext === '') { $oddtext = $cont; $is_proc = true; }

					if (!$render)
					{
						continue;
					}

					if ($newest < 0)
					{  // Can just add text to answer
						$result .= $oddtext;
					}
					else
					{  // Add to accumulator at this level
						$stacktext .= $oddtext;
					}
				}
			}
		}

		if (!$render)
		{
			$left = null;

			if ($codeLeftOpen === null)
			{
				$left = $nopro ? $noproFrom : null;

				for ($id = $newest; ($swallowedFrom !== null) && ($id >= 0); $id = $entries[$id]['older'])
				{
					if (($entries[$id]['code'] === $blockCode) && ($entries[$id]['pos'] < $swallowedFrom))
					{
						$left = $swallowedFrom;
						break;
					}
				}
			}

			return array('pairs' => $pairs, 'codeLeftOpen' => $left);
		}

		// Basically done - just tidy up now: whatever is still open is written back as typed
		$open = array();

		for ($id = $newest; $id >= 0; $id = $entries[$id]['older'])
		{
			$open[] = self::typed($entries[$id]);
		}

		return $result.implode('', array_reverse($open)).$stacktext;
	}


	/**
	 * A key two runs of digits after a bbcode word share exactly when the closing-tag search takes them as the same number, comparing them loosely as PHP does.
	 *
	 * @param string $digits
	 * @return string
	 */
	private static function numbersKey($digits)
	{
		if ($digits === '')
		{
			return '';
		}

		$canonical = ltrim($digits, '0');
		$canonical = ($canonical === '') ? '0' : $canonical;

		return ($digits == $canonical) ? 'n'.$canonical : 's'.$digits;
	}


	/**
	 * $entry of the stack {@see e_bbcode::walkBBCodes()} keeps, as it was typed.
	 *
	 * @param array $entry
	 * @return string
	 */
	private static function typed($entry)
	{
		return ($entry['type'] === 'text') ? $entry['code'] : $entry['block'];
	}


	/**
	 * Put $entry on top of the stack {@see e_bbcode::walkBBCodes()} keeps, as entry $id linked to the one below it.
	 *
	 * @param int     $id
	 * @param array   $entry
	 * @param array[] $entries
	 * @param int     $newest the id on top, -1 for none
	 * @return void
	 */
	private static function stack($id, $entry, &$entries, &$newest)
	{
		$entry['older'] = $newest;
		$entry['newer'] = -1;
		$entries[$id] = $entry;

		if ($newest >= 0)
		{
			$entries[$newest]['newer'] = $id;
		}

		$newest = $id;
	}


	/**
	 * Take entry $id off the stack {@see e_bbcode::walkBBCodes()} keeps, linking the entries either side of it.
	 *
	 * @param int     $id
	 * @param array[] $entries
	 * @param int     $newest
	 * @return void
	 */
	private static function unstack($id, &$entries, &$newest)
	{
		$below = $entries[$id]['older'];
		$above = $entries[$id]['newer'];

		if ($below >= 0)
		{
			$entries[$below]['newer'] = $above;
		}

		if ($above >= 0)
		{
			$entries[$above]['older'] = $below;
		}
		else
		{
			$newest = $below;
		}

		unset($entries[$id]);
	}


	/**
	 *    Process a bbcode
	 *
	 * @var string $code - textual value of the bbcode (already begins with '_' if a single code)
	 * @var string $param1 - any text after '=' in the opening code
	 * @var string $code_text_par - text between the opening and closing codes
	 * @var string $param2 - any text after '=' for the closing code
	 * @var char $sep - character separating bbcode name and any parameters
	 * @var string $full_text - the 'raw' text between, and including, the opening and closing bbcode tags
	 * @return string
	 */
	private function proc_bbcode($code, $param1='', $code_text_par='', $param2='', $sep='', $full_text='')
	{
		global $tp, $postID, $code_text, $parm;

		$parm = $param1;

		$code_text = $code_text_par;

		$className = null;
		$debugFile = null;

		if (is_array($this->bbList) && array_key_exists($code, $this->bbList))
		{	// Check the bbcode 'cache'
			$bbcode = $this->bbList[$code];
			$debugFile = "(cached)";
		}
		else
		{	// Find the file
			if ($this->bbLocation[$code] == 'core')
			{
				$bbPath = e_CORE.'bbcodes/';
				$bbFile = strtolower(str_replace('_', '', $code));
				$debugFile = $bbFile;
			}
			else
			{	// Add code to check for plugin bbcode addition
				$bbPath = e_PLUGIN.$this->bbLocation[$code].'/';
				$bbFile = strtolower($code);
				$debugFile = $bbFile;
			}
			if (file_exists($bbPath.'bb_'.$bbFile.'.php'))
			{	// Its a bbcode class file
				require_once($bbPath.'bb_'.$bbFile.'.php');
				//echo "Load: {$bbFile}.php<br />";
				$className = 'bb_'.$code;
				$this->bbList[$code] = new $className();
				$debugFile = $bbPath.'bb_'.$bbFile.'.php';
			}
			elseif (file_exists($bbPath.$bbFile.'.bb'))
			{
				$bbcode = file_get_contents($bbPath.$bbFile.'.bb');
				$this->bbList[$code] = $bbcode;
				$debugFile = $bbPath.$bbFile.'.bb';
			}
			else
			{
				$this->bbList[$code] = '';
				//echo "<br />File not found: {$bbFile}.php<br />";
				return false;
			}
		}
		
		if (E107_DEBUG_LEVEL)
		{
			$info = array(
				'class' =>$className,
				'path'	=> $debugFile,
			//	'text' => $full_text
			);
			
			e107::getDebug()->logCode(1, $code, $parm, print_a($info,true));
		}

		if (is_object($this->bbList[$code]))
		{
			if ($this->preProcess == 'toDB')
			{
				//echo "Preprocess: ".htmlspecialchars($code_text).", params: {$param1}<br />";
				return $this->bbList[$code]->bbPreSave($code_text, $param1);
			}
		//	if($this->preProcess == 'toWYSIWYG')//XXX FixMe NOT working - messes with default toHTML behavior.
		//	{
			// 	return $this->bbList[$code]->bbWYSIWYG($code_text, $param1);					
		//	}
			return $this->bbList[$code]->bbPreDisplay($code_text, $param1);
		}
		if ($this->preProcess == 'toDB') return $full_text;		// No change

		/**
		 *	@todo - capturing output deprecated
		 */
		ob_start();
		try
	    {
			$bbcode = isset($bbcode) && is_string($bbcode) ? $bbcode : '';
			$bbcode_return = eval($bbcode); //FIXME notice removal
	    }
		catch (ParseError $e)
		{
			$error = $debugFile." -- ".$e->getMessage();
		}

		$bbcode_output = ob_get_clean();

		if(!empty($error))
		{
			trigger_error($error, E_USER_NOTICE);
		}

		$bbcode_return = isset($bbcode_return) ? $bbcode_return : '';
		/* added to remove possibility of nested bbcode exploits ... */
		if(strpos($bbcode_return, "[") !== FALSE)
		{
			$exp_search = array("eval", "expression");
			$exp_replace = array("ev<b></b>al", "expres<b></b>sion");
			$bbcode_return = str_replace($exp_search, $exp_replace, $bbcode_return);
		}
		return $bbcode_output.$bbcode_return;
	}


	/** Grab a list of bbcode content . ie. all [img]xxxx[/img] within a block of text. 
	 * @var string $type  - bbcode eg. 'img' or 'youtube'
	 * @var string $text  - text to be processed for bbcode content
	 * @var string $path - optional path to prepend to output if http or {e_xxxx} is not found. 
	 * @return array|null
	 */
	function getContent($type,$text,$path='')
	{

		if(!in_array($type,$this->core_bb))
		{
			return null;
		}

		if(strpos(ltrim($text), '[html]') === 0 && $type == 'img') // support for html img tags inside [html] bbcode.
		{

			$tmp = e107::getParser()->getTags($text,'img');

			if(!empty($tmp['img']))
			{
				$mtch = array();
				foreach($tmp['img'] as $k)
				{
					$mtch[1][] = str_replace('"','',trim($k['src']));
					// echo $k['src']."<br />";
				}

			}

		}
		else // regular bbcode;
		{
			preg_match_all("/\[".$type.e_parse::BBCODE_TAG_LEAVES_NO_HTML_TAG_OPEN."(?:[^\]]*)?]([^\[]*)(?:\[\/".$type."])/im",$text,$mtch);
		}



		$ret = array();
		
		if(!empty($mtch) && is_array($mtch[1]))
		{
			$tp = e107::getParser();
			foreach($mtch[1] as $i)
			{

				if(strpos($i,'http') === 0)
				{
					$ret[] = $i;
				}
				elseif(strpos($i,"{e_") === 0)
				{
					$ret[] = $tp->replaceConstants($i,'full');
				}
				elseif(strpos($i,'thumb.php')!==false || strpos($i,'media/img/')!==false || strpos($i,'theme/img/')!==false) // absolute path.
				{
					$ret[] = SITEURLBASE.$i;
				}
				else
				{
					$ret[] = $path.$i;	
				}
				
			}			
		}
		
		return $ret;
	}
	
	//Set the class type for a bbcode eg. news | page | user | {plugin-folder}

	/**
	 * @param $mode
	 * @return void
	 */
	function setClass($mode=false)
	{
		$this->class = $mode;	
	}
	
	// return the Mode used by the class.  eg. news | page | user | {plugin-folder}

	/**
	 * @return bool
	 */
	function getMode()
	{
		return $this->class; 	
	}


	/**
	 * @return false|int
	 */
	function resizeWidth()
	{
		if($this->class && !empty($this->resizePrefs[$this->class.'-bbcode']['w']))
		{
			return (int) $this->resizePrefs[$this->class.'-bbcode']['w'];
		}

		return false;	
	}

	/**
	 * @return false|int
	 */
	function resizeHeight()
	{
		if($this->class && !empty($this->resizePrefs[$this->class.'-bbcode']['h']))
		{
			return (int) $this->resizePrefs[$this->class.'-bbcode']['h'];
		}

		return false;	
	}	
	
	// return the class for a bbcode

	/**
	 * @param $type
	 * @return string
	 */
	function getClass($type='')
	{
				
		$ret = "bbcode-".$type;
		if($this->class)
		{
			$ret .= " bbcode-".$type."-".$this->class;
		}
		return $ret; 
	}


	/**
	 * @return void
	 */
	function clearClass()
	{
		$this->setClass();	
	}
	
	
	
	
	//

	/**
	 * NEW bbcode button rendering function. replacing displayHelp();
	 * @param string (optional) $template eg. news, submitnews, extended, admin, mailout, page, comment, signature
	 * @param string $id
	 * @param array  $options
	 * @return string
	 */
	function renderButtons($template='', $id='', $options=array())
	{
		$template = (string) $template;
		$tp = e107::getParser();

		// Notice Removal
		$BBCODE_TEMPLATE_SUBMITNEWS = '';
		$BBCODE_TEMPLATE_NEWSPOST = '';
		$BBCODE_TEMPLATE_MAILOUT = '';
		$BBCODE_TEMPLATE_CPAGE = '';
		$BBCODE_TEMPLATE_ADMIN = '';
		$BBCODE_TEMPLATE_COMMENT = '';
		$BBCODE_TEMPLATE_SIGNATURE = '';


		require(e107::coreTemplatePath('bbcode')); //correct way to load a core template.

//		$pref = e107::getPref('e_bb_list');
//
//		if (!empty($pref)) // Load the Plugin bbcode AFTER the templates, so they can modify or replace.
//		{
//			foreach($pref as $val)
//			{
//				if(is_readable(e_PLUGIN.$val."/e_bb.php"))
//				{
//					require(e_PLUGIN.$val."/e_bb.php");
//				}
//			}
//		}
	
		$temp = array();
	    $temp['news'] 		= $BBCODE_TEMPLATE_NEWSPOST;
		$temp['submitnews']	= $BBCODE_TEMPLATE_SUBMITNEWS;
		$temp['extended']	= $BBCODE_TEMPLATE_NEWSPOST;
		$temp['admin']		= $BBCODE_TEMPLATE_ADMIN;
		$temp['mailout']	= $BBCODE_TEMPLATE_MAILOUT;
		$temp['page']		= $BBCODE_TEMPLATE_CPAGE;
		$temp['maintenance']= $BBCODE_TEMPLATE_ADMIN;
		$temp['comment'] 	= $BBCODE_TEMPLATE_COMMENT;
		$temp['signature'] 	= $BBCODE_TEMPLATE_SIGNATURE;
		
		if(!isset($temp[$template]))
		{
			// if template not yet defined, assume that $template is the name of a plugin
			// and load the specific bbcode template from the plugin
			// see forum plugin "templates/bbcode_template.php" for an example of the definition
			$tpl = e107::getTemplate($template, 'bbcode', $template);
			if (!empty($tpl))
			{
				// If the plugin has a template defined for bbcode, add it to the list
				$temp[$template] = $tpl;
			}
			unset($tpl);
		}

		if(isset($temp[$template]))
		{
	        $BBCODE_TEMPLATE = $temp[$template];
		}
		elseif(strpos($template,"{")!==false) // custom template provided manually. eg. $template = "<div class='btn-group inline-text'>{BB=link}{BB=b}{BB=i}{BB=u}{BB=img}{BB=format}</div>"
		{
			$BBCODE_TEMPLATE = $template;	
			$template = 'comment';	
		}
		elseif(deftrue('ADMIN_AREA'))
		{
			$BBCODE_TEMPLATE = $BBCODE_TEMPLATE_ADMIN;	
		}
	//	else // Front-end
	//	{
		//	$BBCODE_TEMPLATE = $BBCODE_TEMPLATE;
	//	}


		$pref = e107::getPref('e_bb_list');

		if (!empty($pref)) // Load the Plugin bbcode AFTER the templates, so they can modify or replace.
		{
			foreach($pref as $val)
			{
				if(is_readable(e_PLUGIN.$val."/e_bb.php"))
				{
					require(e_PLUGIN.$val."/e_bb.php");
				}
			}
		}

		$bbcode_shortcodes = e107::getScBatch('bbcode');	
				
		$data = array(
				'tagid'			=> $id,
				'template'		=> $template,
				'trigger'		=> vartrue($options['trigger']), // For BC
		//		'hint_func'		=> $helpfunc, // deprecated and unused
		//		'hint_active'	=> $bbcode_helpactive,  // deprecated and unused
				'size'			=> vartrue($helpsize),
				'eplug_bb'		=> varset($eplug_bb), //?XXX ?
		);
				
		$bbcode_shortcodes->setVars($data);	
		
  		return "<div id='bbcode-panel-".$id."' class='mceToolbar bbcode-panel'>".$tp->parseTemplate($BBCODE_TEMPLATE,TRUE, $bbcode_shortcodes)."</div>";		
	}


	/**
	 * @param $tag
	 * @param $html
	 * @return array|string|string[]
	 */
	function processTag($tag, $html)
    {
        $html = "<html><body>".$html."</body></html>";
        $doc = new DOMDocument();     
        $doc->loadHTML($html);

        $tmp = $doc->getElementsByTagName($tag);

        $var = array();

        $attributes = array('class','style','width','height','src','alt','href');
        
        $params = array(
            'img'   =>  array('style','width','height','alt')
        );
        
        // Generate array for $var ($code_text) & $params ($parm);
        foreach ($tmp as $tg)
        {
            $var = array();
            $parm = array();
            
            foreach($attributes as $att)
            {
                $v = (string) $tg->getAttribute($att);  
                         
                if(trim($v) != '')
                {
                   $var[$att] = $v;
                   if(in_array($att, $params[$tag]))
                    {
                        $parm[$att] = $att."=".str_replace(" ","",$var[$att]); 
                    }
                }                                
            }
     
            $inc = ($parm) ? "  ".implode("&",$parm) : "";  // the parm - eg. [img $parm]whatever[/img]
     
            switch ($tag) 
            {
                case 'img':
                    
                    $e_http = str_replace("/",'\/',e_HTTP);
                    $regex      = "/".$e_http."thumb.php\?src=[^>]*({e_MEDIA_IMAGE}[^&]*)(.*)/i";
                //    echo "REGEX = ".$regex;
                    $code_text = preg_replace($regex,"$1",$var['src']);
                    $code_reg   = str_replace("/","\/",$code_text);
     
                    
                    $search     = '/<img([^>]*)'.$code_reg.'([^>]*)>/i'; // Must match the specific line - not just the tag. 
                    $replace    = "[img{$inc}]".$code_text."[/img]"; // bbcode replacement.  
                break;
                
                default:
                     echo "TAG = ".$tag;
                break;
            }
           
           $html = preg_replace($search,$replace,$html);  
        }
  
        return str_replace(array("<html><body>","</body></html>"),"",$html); 
    }


	/**
	 * Replace all instances of <img> tags with [img] bbcodes - allowing image tags and their 'src' values to remain dynamic.
	 * @param string $html
	 * @param bool $fromDB if html source is directly from the database, set to true to handle '&quot;' etc.
	 * @return string html with <img> tags replaced by [img] bbcodes.
	 */
	function imgToBBcode($html, $fromDB = false)
    {

	    $tp = e107::getParser();

	    if($fromDB === true)
	    {
	    	$html = str_replace('&quot;','"', $html);
	    }

	//    var_dump($this->defaultImageSizes);
	    $cl = $this->getClass();


		$arr = $tp->getTags($html,'img');

		$srch = array("?","&");
		$repl = array("\?","&amp;");

		if(defined('TINYMCE_DEBUG'))
		{
			print_a($arr);
		}

		$arr['img'] = isset($arr['img']) && is_array($arr['img']) ? $arr['img'] : [];
		foreach($arr['img'] as $img)
		{
			if(/*substr($img['src'],0,4) == 'http' ||*/ strpos($img['src'], e_IMAGE_ABS.'emotes/')!==false) // dont resize external images or emoticons.
			{
				continue;
			}

			$regexp = '#(<img[^>]*src="'.str_replace($srch, $repl, $img['src']).'"[^>]*>)#';

			$qr = $tp->thumbUrlDecode($img['src']); // extract width/height and src from thumb URLs.

			if(strpos($qr['src'],'http')!==0 && empty($qr['w']) && empty($qr['aw']))
			{
				$qr['w'] = varset($img['width']);
				$qr['h'] = varset($img['height']);
			}

			$qr['ebase'] = true;


			if(!empty($img['class']))
			{
				$tmp = explode(" ",$img['class']);
				$cls = array();
				foreach($tmp as $v)
				{
					if($v === 'img-rounded' || $v === 'rounded' || (strpos($v,'bbcode') === 0 && $v !== 'bbcode-img-right' && $v !== 'bbcode-img-left' ))
					{
						continue;
					}

					$cls[] = $v;

				}

				if(empty($cls))
				{
					unset($img['class']);
				}
				else
				{
					$img['class'] = implode(" ",$cls);
				}

			}

			if($this->resizeWidth() === (int) varset($img['width']))
			{
				unset($img['width']);
			}


			$code_text = (strpos($img['src'],'http') === 0) ? $img['src'] : str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants('sc'), $qr['src']);

			unset($img['src'],$img['srcset'],$img['@value'], $img['caption'], $img['alt']);
			$parms = !empty($img) ? ' '.str_replace('+', ' ', http_build_query($img)) : "";

			$replacement = '[img'.$parms.']'.$code_text.'[/img]';

			$html = preg_replace($regexp, $replacement, $html);

		}

	    if($fromDB === true)
	    {
	    	$html = str_replace('"', '&quot;', $html);
	    }

		return $html;


    }




    
	/**
	 * Convert HTML to bbcode. 
	 */
	function htmltoBBcode($text)
	{
		$allowedTags = array('html', 'body','div', 'a', 'img', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'b',
			'i', 'pre', 'code', 'strong', 'u', 'em', 'ul', 'ol', 'li',  'h2', 'h3', 'h4', 'h5', 'h6', 'p',
			'blockquote', /*'audio', 'video',*/ 'br', 'small'
		);

		$allowedAttributes = array(
		'default'  => array(),
		'img'      => array('src', 'alt', 'width', 'height'),
		'a'        => array('href', 'target', 'rel'),
		'audio'    => array('src', 'controls', 'autoplay', 'loop', 'muted', 'preload'),
		'video'    => array('autoplay', 'controls', 'height', 'loop', 'muted', 'poster', 'preload', 'src', 'width'),
		'td'       => array('colspan', 'rowspan'),
		'th'       => array('colspan', 'rowspan'),
		'x-bbcode' => array('alt'),
		);


	    $tp = e107::getParser();
	    $tp->setAllowedTags($allowedTags);
	    $tp->setAllowedAttributes($allowedAttributes);
	    $tp->setScriptAttibutes(null);

	    $text = $tp->cleanHtml($text);

	    $tp->init(); // reset to default;

		$text = str_replace("<!-- bbcode-html-start -->","[html]",$text);
		$text = str_replace("<!-- bbcode-html-end -->","[/html]",$text);

	//	$text = str_replace('<!-- pagebreak -->',"[newpage=]",$text);
    
        

		if(strpos($text,'[html]') === 0)
		{
			return $text;
		}
        
       
       
        $text = $this->processTag('img', $text);
        
       
       
		// Youtube conversion (TinyMce)
		
	//	return $text;
	
	//   $text = preg_replace('/<img(?:\s*)?(?:class="([^"]*)")?(?:\s*)?(?:style="([^"]*)")?\s?(?:src="thumb.php\?src=([^"]*)&w=([\d]*)?&h=([\d]*)?")(?:\s*)?(?:\s*)?(?:width="([\d]*)")?\s*(?:height="([\d]*)")?(?:\s*)?(?:alt="([^"]*)")? \/>/i',"[img style=width:$4px;height:$5px; alt=$8]$3[/img]",$text ); 
	
		$text = preg_replace('/<img class="youtube-([\w]*)" style="([^"]*)" src="([^"]*)" alt="([^"]*)" \/>/i',"[youtube=$1]$4[/youtube]",$text);	
		$text = preg_replace('/<!-- Start YouTube-([\w,]*)-([\w]*) -->([^!]*)<!-- End YouTube -->/i','[youtube=$1]$2[/youtube]',$text);	
					
		$text = preg_replace("/<a.*?href=\"(.*?)?request.php\?file=([\d]*)\".*?>(.*?)<\/a>/i","[file=$2]$3[/file]",$text);		
					
		$text = preg_replace("/<a.*?href=\"(.*?)\".*?>(.*?)<\/a>/i","[link=$1]$2[/link]",$text);
		$text = preg_replace('/<div style="text-align: ([\w]*);">([\s\S]*)<\/div>/i',"[$1]$2[/$1]",$text); // verified
		$text = preg_replace('/<div class="bbcode-(?:[\w]*).* style="text-align: ([\w]*);">([\s\S]*)<\/div>/i',"[$1]$2[/$1]",$text); // left / right / center
	//	$text = preg_replace('/<img(?:\s*)?(?:style="([^"]*)")?\s?(?:src="([^"]*)")(?:\s*)?(?:alt="(\S*)")?(?:\s*)?(?:width="([\d]*)")?\s*(?:height="([\d]*)")?(?:\s*)?\/>/i',"[img style=width:$4px;height:$5px;$1]$2[/img]",$text );
	//	$text = preg_replace('/<img class="(?:[^"]*)"(?:\s*)?(?:style="([^"]*)")?\s?(?:src="([^"]*)")(?:\s*)?(?:alt="(\S*)")?(?:\s*)?(?:width="([\d]*)")?\s*(?:height="([\d]*)")?(?:\s*)?\/>/i',"[img style=width:$4px;height:$5px;$1]$2[/img]",$text );
	//	$text = preg_replace('/<span (?:class="bbcode-color" )?style=\"color: ?(.*?);\">(.*?)<\/span>/i',"[color=$1]$2[/color]",$text);
		$text = preg_replace('/<span (?:class="bbcode underline bbcode-u)(?:[^>]*)>(.*?)<\/span>/i',"[u]$1[/u]",$text);
	//	$text = preg_replace('/<table([^"]*)>/i', "[table $1]",$text);
		$text = preg_replace('/<table style="([^"]*)"([\w ="]*)?>/i', "[table style=$1]",$text);
		$text = preg_replace('/<table([\w :\-_;="]*)?>/i', "[table]",$text);
		$text = preg_replace('/<tbody([\w ="]*)?>/i', "[tbody]",$text);
		$text = preg_replace('/<code([\w :\-_;="]*)?>/i', "[code]\n",$text);
		$text = preg_replace('/<strong([\w :\-_;="]*)?>/i', "[b]",$text);
		$text = preg_replace('/<em([\w :\-_;="]*)?>/i', "[i]",$text);
		$text = preg_replace('/<li([\w :\-_;="]*)?>/i', "[*]",$text);
		$text = preg_replace('/<ul([\w :\-_;="]*)?>/i', "[list]",$text);
		$text = preg_replace('/<ol([\w :\-_;="]*)?>/i', "[list=ol]",$text);		
		$text = preg_replace('/<table([\w :\-_;="]*)?>/i', "[table]",$text);
		$text = preg_replace('/<tbody([\w :\-_;="]*)?>/i', "[tbody]",$text);
		$text = preg_replace('/<tr([\w :\-_;="]*)?>/i', "[tr]",$text);
		$text = preg_replace('/<td([\w :\-_;="]*)?>/i', "\t[td]",$text);
		$text = preg_replace('/<blockquote([\w :\-_;="]*)?>/i', "[blockquote]",$text);
		$text = preg_replace('/<p([\w :\-_;="]*)?>/i', "",$text);  // Causes issues : [p] [/p] everywhere. 
		
	//	$ehttp = str_replace("/",'\/',e_HTTP);
	//	$text = preg_replace('/thumb.php\?src='.$ehttp.'([^&]*)([^\[]*)/i', "$1",$text);
	//	$text = preg_replace('/thumb.php\?src=([^&]*)([^\[]*)/i', "$1",$text);
		
			
		// Mostly closing tags. 
		$convert = array(

			array(	"\n",			'<br />'),
		//	array(	"\n",			'<p>'),
			array(	"\n",			"</p>\n"),
			array(	"",			    "<div>\n"),
			array(	"",			    "\t"),
			array(	"",			    "</div>\n"),
			array(	"\n",			"<thead>\n"),
			array(	"\n",			"</thead>\n"),
			array(	"\n",			"</p>"),
			array(	"[/list]",		'</ul>\n'),
			array(	"[/list]",		'</ul>'),
			array(	"[/list]",		'</ol>\n'),
			array(	"[/list]",		'</ol>'),			
			array(	"[h=2]",		'<h2 class="bbcode-center" style="text-align: center;">'), // e107 bbcode markup
			array(	"[h=2]",		'<h2>'),
			array(	"[/h]",			'</h2>'),
			array(	"[h=3]",		'<h3 class="bbcode-center" style="text-align: center;">'), // e107 bbcode markup
			array(	"[h=3]",		'<h3>'),
			array(	"[/h]",			'</h3>'),
			array(	"[h=4]",		'<h4>'),
			array(	"[/h]",		    '</h4>'),
			array(	"[h=5]",		'<h5>'),
			array(	"[/h]",		    '</h5>'),
			array(	"[h=6]",		'<h6>'),
			array(	"[/h]",		    '</h6>'),
			array(	"[/b]",			'</strong>'),
			array(	"[/i]",			'</em>'),
			array(	"[/block]",		'</div>'),
			array(	"[/table]",	    '</table>'),
			array(	"[/tbody]",	    '</tbody>'),
			array(	"[/code]\n",	'</code>'),
			array(	"[/tr]",	    '</tr>'),
			array(	"[/td]",		'</td>'),
			array(	"[td]",		    '<th>'),
			array(	"[/td]",		'</th>'),
			array(	"[/blockquote]",'</blockquote>'),
			array(	"]",			' style=]')
				
		);
		
		foreach($convert as $arr)
		{
			$repl[] = $arr[0];
			$srch[] = $arr[1];	
		}
		
		$paths = array(
			e107::getFolder('images'),
			e107::getFolder('plugins'),
		//	e107::getFolder('media_images'),
			e107::getFolder('media_files'),
			e107::getFolder('media_videos')
		);
		
		$tp = e107::getParser();
		foreach($paths as $k=>$path)
		{
			$srch[] = $path;
			$repl[] = $tp->createConstants($path);
		}
		

		$blank = array('</li>','width:px;height:px;');
		$text = str_replace($blank,"",$text); // Cleanup 
		
		return str_replace($srch,$repl,$text);	
		
	}
	
	
	
	
} // end Class 



/**
 *	Base class for bbcode handlers
 *
 *	Contains core routines for entry, security, logging....
 *
 *	@todo add security
 */
class e_bb_base
{
	/**
	 *	Constructor
	 */
	public function __construct()
	{
	}



	/**
	 *	Called prior to save of user-entered text
	 *
	 *	Allows initial parsing of bbcode, including the possibility of removing or transforming the enclosed text (as is done by the youtube processing)
	 *	Parameters passed by reference to minimise memory use
	 *
	 *	@param string $code_text - text between the bbcode tags
	 *	@param string $parm - any parameters specified for the bbcode
	 *
	 *	@return string for insertion into DB. (If a bbcode is to be inserted, the bbcode 'tags' must be included in the return string.)
	 */
	final public function bbPreSave(&$code_text, &$parm)
	{
		// Could add logging, security in here
		return $this->toDB($code_text, $parm);
	}



	/**
	 *	Process bbcode prior to display
	 *	Functionally this routine does exactly the same as the existing bbcodes
	 *	Parameters passed by reference to minimise memory use
	 *
	 *	@param string $code_text - text between the bbcode tags
	 *	@param string $parm - any parameters specified for the bbcode
	 *
	 *	@return string with $code_text transformed into displayable XHTML as necessary
	 */
	final public function bbPreDisplay(&$code_text, &$parm)
	{
		// Could add logging, security in here
		return $this->toHTML($code_text, $parm);
	}
	
	
	/**
	 *	Process bbcode prior to display in WYSIWYG
	 *	Functionally this routine does exactly the same as the existing bbcodes
	 *	Parameters passed by reference to minimise memory use
	 *
	 *	@param string $code_text - text between the bbcode tags
	 *	@param string $parm - any parameters specified for the bbcode
	 *
	 *	@return string with $code_text transformed into displayable XHTML as necessary
	 */
	final public function bbWYSIWYG(&$code_text, &$parm)
	{
		// Could add logging, security in here
		return $this->toWYSIWYG($code_text, $parm);
	}
}

