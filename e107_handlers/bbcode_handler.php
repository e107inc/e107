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

class e_bbcode
{
	var $bbList;			// Caches the file contents for each bbcode processed
	var $bbLocation;		// Location for each file - 'core' or a plugin name
	var $preProcess = FALSE;	// Set when processing bbcodes prior to saving
	var $core_bb = array();
	var $class = FALSE;
	private $resizePrefs = array();

	function __construct()
	{
		$pref = e107::getPref();

		$this->resizePrefs = $pref['resize_dimensions'];

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
		$code_stack = array();								// Stack for unprocessed bbcodes and text
		$unmatch_stack = array();							// Stack for unmatched bbcodes
		$result = '';										// Accumulates fully processed text
		$stacktext = '';									// Accumulates text which might be subject to one or more bbcodes
		$nopro = FALSE;										// Blocks processing within [code]...[/code] tags
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
		

		
		$pattern = '#^\[(/?)([A-Za-z_]+)(\d*)([=:]?)(.*?)]$#i';	// Pattern to split up bbcodes
		// $matches[0] - same as the input text
		// $matches[1] - '/' for a closing tag. Otherwise empty string
		// $matches[2] - the bbcode word
		// $matches[3] - any digits immediately following the bbcode word
		// $matches[4] - '=' or ':' according to the separator used
		// $matches[5] - any parameter

		$content = preg_split('#(\[(?:\w|/\w).*?\])#mis', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );

		foreach ($content as $cont)
		{  // Each chunk is either a bbcode or a piece of text
			$is_proc = FALSE;
			while (!$is_proc)
			{
				$oddtext = '';
				if ($cont[0] == '[')
				{  // We've got a bbcode - split it up and process it
					$match_count = preg_match($pattern,$cont,$matches);
					$bbparam = (isset($matches[5])) ? $matches[5] : '';
					$bbword = (isset($matches[2])) ? $matches[2] : '';
					if($cont[1] != '/')
					{
						$bbsep = varset($matches[4]);
					}
					if ($force_lower) $bbword = strtolower($bbword);
					if ($nopro && ($bbword == 'code') && ($matches[1] == '/')) $nopro = FALSE;		// End of code block
					if (($bbword) && ($bbword == trim($bbword)) && !$nopro)
					{  // Got a code to process here
						if (($bbStrip === TRUE) || in_array($bbword,$strip_array))
						{
							$is_proc = TRUE;		// Just discard this bbcode
						}
						else
						{

							if ($matches[1] == '/')
							{  // Closing code to process
								$found = FALSE;
								$i = 0;
								while ($i < count($code_stack))
								{     // See if code is anywhere on the stack.
									if (($code_stack[$i]['type'] == 'bbcode') && ($code_stack[$i]['code'] == $bbword) && ($code_stack[0]['numbers'] == $matches[3]))
									{
										$found = TRUE;
										break;
									}
									$i++;
								}

								if ($found)
								{
									$found = FALSE;   // Use as 'done' variable now
									// Code is on stack - $i has index number. Process text, discard unmatched open codes, process 'our' code
									while ($i > 0) { $unmatch_stack[] = array_shift($code_stack); $i--; }    // Get required code to top of stack

									// Pull it off using array_shift - this keeps it as a zero-based array, newest first.
									while (!$found && (count($code_stack) != 0))
									{
										switch ($code_stack[0]['type'])
										{
											case 'text' :
												$stacktext = $code_stack[0]['code'].$stacktext;   // Need to insert text at front
												array_shift($code_stack);
												break;
											case 'bbcode' :
												if (($code_stack[0]['code'] == $bbword) && ($code_stack[0]['numbers'] == $matches[3]))
												{
													$stacktext = $this->proc_bbcode($bbword, $code_stack[0]['param'], $stacktext, $bbparam, $code_stack[0]['bbsep'], $code_stack[0]['block'].$stacktext.$cont);
													array_shift($code_stack);
													// Intentionally don't terminate here - may be some text we can clean up
													$bbword='';    // Necessary to make sure we don't double process if several instances on stack
													while (count($unmatch_stack) != 0) { array_unshift($code_stack,array_pop($unmatch_stack));  }
												}
												else
												{
													$found = TRUE;  // Terminate on unmatched bbcode
												}
												break;
										}
										if (count($code_stack) == 0)
										{
											$result .= $stacktext;
											$stacktext = '';
											$found = TRUE;
										}
									}
									$is_proc = TRUE;
								}
							}
							else
							{  // Opening code to process
								// If its a single code, we can process it now. Otherwise just stack the value
								if (array_key_exists('_'.$bbword,$this->bbLocation))
								{  // Single code to process
									if (count($code_stack) == 0)
									{
										$result .= $this->proc_bbcode('_'.$bbword,$bbparam,'','','',$cont);
									}
									else
									{
										$stacktext .= $this->proc_bbcode('_'.$bbword,$bbparam,'','','',$cont);
									}
									$is_proc = TRUE;
								}
								elseif (array_key_exists($bbword,$this->bbLocation))
								{
									if ($stacktext != '')
									{ // Stack the text we've accumulated so far
										array_unshift($code_stack,array('type' => 'text','code' => $stacktext));
										$stacktext = '';
									}
									array_unshift($code_stack,array('type' => 'bbcode','code' => $bbword, 'numbers'=> $matches[3], 'param'=>$bbparam, 'bbsep' => $bbsep, 'block' => $cont));
									if ($bbword == 'code') $nopro = TRUE;
									$is_proc = TRUE;
								}
							}
						}
					}
					// Next lines could be deleted - but gives better rejection of 'stray' opening brackets
					if ((!$is_proc) && (($temp = strrpos($cont,"[")) !== 0))
					{
						$oddtext = substr($cont,0,$temp);
						$cont = substr($cont,$temp);
					}
				}

				if (!$is_proc)
				{  // We've got some text between bbcodes (or possibly text in front of a bbcode)
					if ($oddtext == '') { $oddtext = $cont; $is_proc = TRUE; }
					if (count($code_stack) == 0)
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

		// Basically done - just tidy up now
		// If there's still anything on the stack, we need to process it
		while (count($code_stack) != 0)
		{
			switch ($code_stack[0]['type'])
			{
				case 'text' :
					$stacktext = $code_stack[0]['code'].$stacktext;   // Need to insert text at front
					array_shift($code_stack);
					break;
				case 'bbcode' :
					$stacktext = '['.$code_stack[0]['code'].']'.$stacktext;   // To discard unmatched codes, delete this line
					array_shift($code_stack);  		// Just discard any unmatched bbcodes
					break;
			}
		}
		$result .= $stacktext;
		return $result;
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
		
		global $e107_debug;

		if (is_object($this->bbList[$code]))
		{
			if ($this->preProcess == 'toDB')
			{
				//echo "Preprocess: ".htmlspecialchars($code_text).", params: {$param1}<br />";
				return $this->bbList[$code]->bbPreSave($code_text, $param1);
			}
			if($this->preProcess == 'toWYSIWYG')//XXX FixMe NOT working - messes with default toHTML behavior. 
			{
			// 	return $this->bbList[$code]->bbWYSIWYG($code_text, $param1);					
			}
			return $this->bbList[$code]->bbPreDisplay($code_text, $param1);
		}
		if ($this->preProcess == 'toDB') return $full_text;		// No change

		/**
		 *	@todo - capturing output deprecated
		 */
		ob_start();
		$bbcode_return = eval($bbcode); //FIXME notice removal
		$bbcode_output = ob_get_contents();
		ob_end_clean();

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
	 * @return array
	 */
	function getContent($type,$text,$path='')
	{
		if(!in_array($type,$this->core_bb))
		{
			return;
		}

		if(substr(ltrim($text),0,6) == '[html]' && $type == 'img') // support for html img tags inside [html] bbcode.
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
			preg_match_all("/\[".$type."(?:[^\]]*)?]([^\[]*)(?:\[\/".$type."])/im",$text,$mtch);
		}

		

		$ret = array();
		
		if(!empty($mtch) && is_array($mtch[1]))
		{
			$tp = e107::getParser();
			foreach($mtch[1] as $i)
			{

				if(substr($i,0,4)=='http')
				{
					$ret[] = $i;
				}
				elseif(substr($i,0,3)=="{e_")
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
	function setClass($mode=false)
	{
		$this->class = $mode;	
	}
	
	// return the Mode used by the class.  eg. news | page | user | {plugin-folder}
	function getMode()
	{
		return $this->class; 	
	}
	
	
	function resizeWidth()
	{
		if($this->class && !empty($this->resizePrefs[$this->class.'-bbcode']['w']))
		{
			return (int) $this->resizePrefs[$this->class.'-bbcode']['w'];
		}

		return false;	
	}
	
	function resizeHeight()
	{
		if($this->class && !empty($this->resizePrefs[$this->class.'-bbcode']['h']))
		{
			return (int) $this->resizePrefs[$this->class.'-bbcode']['h'];
		}

		return false;	
	}	
	
	// return the class for a bbcode
	function getClass($type='')
	{
				
		$ret = "bbcode-".$type;
		if($this->class)
		{
			$ret .= " bbcode-".$type."-".$this->class;
		}
		return $ret; 
	}	
	
	
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
				$qr['w'] = $img['width'];
				$qr['h'] = $img['height'];
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

			if($this->resizeWidth() === (int) $img['width'])
			{
				unset($img['width']);
			}


			$code_text = (strpos($img['src'],'http') === 0) ? $img['src'] : str_replace($tp->getUrlConstants('raw'), $tp->getUrlConstants('sc'), $qr['src']);

			unset($img['src'],$img['srcset'],$img['@value'], $img['caption'], $img['alt']);
			$parms = !empty($img) ? ' '.str_replace('+', ' ', http_build_query($img,null, '&')) : "";

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
	    
       
		$text = str_replace("<!-- bbcode-html-start -->","[html]",$text);
		$text = str_replace("<!-- bbcode-html-end -->","[/html]",$text);
	//	$text = str_replace('<!-- pagebreak -->',"[newpage=]",$text);
    
        

		if(substr($text,0,6)=='[html]')
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
			array(	"[/b]",			'</strong>'),
			array(	"[/i]",			'</em>'),
			array(	"[/block]",		'</div>'),
			array(	"[/table]",	'</table>'),
			array(	"[/tbody]",	'</tbody>'),
			array(	"[/code]\n",	'</code>'),
			array(	"[/tr]",	'</tr>'),
			array(	"[/td]",		'</td>'),	
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

?>