<?php
 /**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * 
 * YouTube BBcode
 */
 
if (!defined('e107_INIT')) { exit; }

/**
 *	Youtube handling
 *
 * [youtube=tiny|small|medium|large|huge or width,height]ID{&query options}[/youtube]
 * 
 * examples: 
 * [youtube=560,340]N2wivHYCRho?hd=1&color1=&color2=&cc_load_policy=0&autoplay=0[/youtube]
 * [youtube=big|privacy]N2wivHYCRho?hd=1&hl=en[/youtube]
 * 
 * Will also convert Youtube embed code, and youtube 'watch' urls. (http://www.youtube.com/watch?v=)
 * Youtube ID is the only required data!
 * TODO - use swfobject JS - XHTML validation
 */

/**
 *	Class-based bbcode validation
 *
 *	Class name must be 'bb_'.bbname - where bbname is the name of the bbcode. (Note some bbcodes begin '_', and this is retained)
 *
 *	This class must contain exactly two public methods:
 *		toDB($code_text, $parm) - called prior to text being saved
 *		toHTML($code_text, $parm) - called prior to text being displayed
 */

class bb_youtube extends e_bb_base
{

	/**
	 *	Called prior to save
	 *
	 *	If user has posted the complete youtube 'copy and paste' text between the tags, parse it and generate the relevant bbcode
	 */
	function toDB($code_text, $parm)
	{
		$bbpars = array();
		$widthString = '';
		$parm = trim($parm);
		
		// Convert Simple URLs. 
		if(strpos($code_text,"youtube.com/watch?v=")!==FALSE || strpos($code_text,"youtube.com/watch#!v=")!==FALSE )
		{
			$validUrls = array("http://", "https://", "www.","youtube.com/watch?v=","youtube.com/watch#!v=");
			$tmp = str_replace($validUrls,'',$code_text);
			$qrs = explode("&",$tmp);		
			$code_text = $qrs[0];
			unset($qrs);		
		}
			
		if ($parm)
		{
			if (strpos($parm, '|') !== FALSE)
			{
				list($widthString, $parm) = explode('|', $parm);
			}
			elseif (in_array($parm, array('tiny', 'small', 'medium','large', 'big', 'huge')) || (strpos($parm, ',') !== FALSE))
			{	// Assume we're just setting a width
				$widthString = $parm;
				$parm = '';
			}
			if ($parm)
			{
				$bbpars = explode('&', $parm);
			}
		}
		
		/*
		echo '<br />Parm= '.$parm;
		echo "<br />COde= ".htmlspecialchars($code_text);
		echo "<br />Width= ".$widthString;
		*/
		
		$params = array();										// Accumulator for parameters from youtube code
		$ok = 0;
		if (strpos($code_text, '<') === FALSE)
		{	// 'Properly defined' bbcode (we hope)
			$picRef = $code_text;
		}
		else
		{
			//libxml_use_internal_errors(TRUE);
			if (FALSE === ($info = simplexml_load_string($code_text)))
			{
				//print_a($matches);
				//$xmlErrs = libxml_get_errors();
				//print_a($xmlErrs);
				$ok = 1;
			}
			else
			{
				$info1 = (array)$info;
				if (!isset($info1['embed']))
				{
					$ok = 2;
				}
				else
				{
					$info2 = (array)$info1['embed'];
					if (!isset($info2['@attributes']))
					{
						$ok = 3;
					}
				}
			}
			if ($ok != 0)
			{
				print_a($info);
				return '[sanitised]'.$ok.'B'.htmlspecialchars($matches[0]).'B[/sanitised]';
			}
			$target =  (array)$info2['@attributes'];
			unset($info);
			$ws = varset($target['width'], 0);
			$hs = varset($target['height'], 0);
			if (($ws == 0) || ($hs == 0) || !isset($target['src'])) return  '[sanitised]A'.htmlspecialchars($matches[0]).'A[/sanitised]';
			if (!$widthString)
			{
				$widthString = $ws.','.$hs;			// Set size of window
			}
			list($url, $query) = explode('?', $target['src']);
			if (strpos($url, 'youtube-nocookie.com') !== FALSE)
			{
				$bb_params[] = 'privacy';
			}
			
			parse_str($query, $vals);		// Various options set here
												
			if (varset($vals['allowfullscreen'], 'true') != 'true')
			{
				$params[] = 'fs=0';
			}
			if (varset($vals['border'], 0) != 0)
			{
				$params[] = 'border=1';
			}
			if (varset($vals['rel'], 1) != 1)
			{
				$params[] = 'rel='.intval($vals['rel']);
			}
			if (varset($vals['hd'], 1) != 0)
			{
				$params[] = 'hd='.intval($vals['hd']);
			}
			if (varset($vals['hl'], 1) != 0)
			{
				$params[] = 'hl='.$vals['hl'];
			}
			if (varset($vals['color1'], 1) != 0)
			{
				$params[] = 'color1='.$vals['color1'];
			}
			if (varset($vals['color2'], 1) != 0)
			{
				$params[] = 'color2='.$vals['color2'];
			}
			if (varset($vals['cc_load_policy'], 1) != 0)
			{
				$params[] = 'cc_load_policy='.intval($vals['cc_load_policy']);
			}
			if (ADMIN && varset($vals['autoplay'], 1) != 0)
			{
				$params[] = 'autoplay='.intval($vals['autoplay']);
			}

			$picRef = substr($url, strrpos($url, '/') + 1);
		}


		$yID = preg_replace('/[^0-9a-z]/i', '', $picRef);
		if (($yID != $picRef) || (strlen($yID) > 20))
		{	// Possible hack attempt
		}
	//	$params = array_merge($params, $bbpars);			// Any parameters set in bbcode override those in HTML
		// Could check for valid array indices here
		$paramString = implode('&', $params);
		if ($paramString) $picRef .= '?'.$paramString;
		if($widthString)
		{
			$widthString = "=".$widthString;
			if(count($bbpars))
			{
				$widthString .= "|".implode("&",$bbpars);
			}
		}
		$ans = '[youtube'.$widthString.']'.$picRef.'[/youtube]';
		return $ans;
	}



	/**
	 *	Translate youtube bbcode into the appropriate <EMBED> object
	 */
	function toHTML($code_text, $parm)
	{
		if(empty($code_text)) return '';

		list($dimensions,$tmp) = explode('|', $parm, 2);
		
		if($tmp)
		{
			parse_str(varset($tmp, ''), $bbparm);
		}
		
		if(strpos($code_text,"&")!==FALSE && strpos($code_text,"?")===FALSE) // DEPRECATED
		{
			$parms = explode('&', $code_text, 2);	
		}
		else
		{
			$parms = explode('?', $code_text, 2);	// CORRECT SEPARATOR
		}
		

		$code_text = $parms[0];
			
		parse_str(varset($parms[1], ''), $params);
		
	//	print_a($params);

		if(empty($dimensions)) $dimensions = 'medium'; // (default as per YouTube spec)
		// formula: width x (height+25px)

		switch ($dimensions) 
		{
			case 'tiny':
				$params['w'] = 320; // 200;
				$params['h'] = 205; // 180;
			break;
			
			case 'small':
				$params['w'] = 560; // 445;
				$params['h'] = 340; // 364;
			break;
			
			case 'medium':
				$params['w'] = 640; // 500;
				$params['h'] = 385; // 405;
			break;
			
			case 'big':
			case 'large':
				$params['w'] = 853; // 660;
				$params['h'] = 505; // 525;
			break;
			
			case 'huge':
				$params['w'] = 1280; // 980;
				$params['h'] = 745; // 765;
			break;
			
			default: // maximum 1920 x 1080 (+25)
				$dim = explode(',', $dimensions, 2);
				$params['w'] = (integer) varset($dim[0], 445);
				if($params['w'] > 1920 || $params['w'] < 100) $params['w'] = 640;
				
				$params['h'] = (integer) varset($dim[1], 364);
				if($params['h'] > 1105 || $params['h'] < 67) $params['h'] = 385;
			break;
		}
	
	
		$yID = preg_replace('/[^0-9a-z\-_\&\?]/i', '', $code_text);

		$url = isset($bbparm['privacy']) ? 'https://www.youtube-nocookie.com/v/' : 'https://www.youtube.com/v/';
		$url .= $yID.'?';

		if(isset($params['nofull']) || !varset($params['fs'])) 
		{
			$fscr = 'false';
			$url = $url.'fs='.intval($params['fs']);
		} 
		else 
		{
			$fscr = 'true';
			$url = $url.'fs=1';
		}
		

		
		if(isset($params['border'])) $url = $url.'&amp;border='.intval($params['border']);
		if(isset($params['norel'])) // BC non-standard val. 
		{
			$url = $url.'&amp;rel=0';	
		} 
		elseif(isset($params['rel']))
		{
			$url = $url.'&amp;rel='.intval($params['rel']);	
		}
		
		if(isset($params['hd'])) $url = $url.'&amp;hd='.intval($params['hd']);
		
		$hl = 'en_US';
		
		if(isset($params['hl']))
		{
			$params['hl'] = preg_replace('/[^0-9a-z\-_]/i', '', $params['hl']);
			if(strlen($params['hl']) == 2 || strlen($params['hl']) == 5)
			{
				$hl = $params['hl'];
			}
		}
		
		$url = $url.'&amp;hl='.$hl;
		$color = array();
		if(isset($params['color1'])) $color[1] = $params['color1'];
		if(isset($params['color2'])) $color[2] = $params['color2'];
		foreach ($color as $key => $value)
		{
			if (ctype_xdigit($value) && strlen($value) == 6)
			{
				$url = $url.'&amp;color'.$key.'='.$value;
			}
		}
		
		if(isset($params['cc_load_policy']))
		{
			$url .= "&amp;cc_load_policy=".intval($params['cc_load_policy']);
		}
		
		if(isset($params['autoplay']))
		{
			$url .= "&amp;autoplay=".intval($params['autoplay']);
		}
		
		$class = "bbcode ".e107::getBB()->getClass('youtube'); // consistent classes across all themes. 
		
		$ret = "<!-- Start YouTube-".$dimensions."-".$yID." -->\n";	// <-- DO NOT MODIFY - used for detection by bbcode handler. 	
		
		
		if(e107::getConfig()->get('youtube_bbcode_responsive') == 1) // Responsive Mode. 
		{
			$ret .= e107::getParser()->toVideo($yID.".youtube");	
		}
		else // Legacy Mode. 
		{
			$ret .= '<object class="'.$class.'" width="'.$params['w'].'" height="'.$params['h'].'" >
				<param name="movie" value="'.$url.'" />
				<param name="allowFullScreen" value="'.$fscr.'" />
				<param name="allowscriptaccess" value="always" />
				<param name="wmode" value="transparent" />
			';		
				
			// Not XHTML - but needed for compatibility. 
			$ret .= '<embed class="'.$class.'" src="'.$url.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="'.$fscr.'" wmode="transparent" width="'.$params['w'].'" height="'.$params['h'].'" />';
			
			$ret .= '</object>';
		}
		
		$ret .= "<!-- End YouTube -->"; // <-- DO NOT MODIFY. 


		return $ret;
	}

	// Wysiwyg representation of bbcode render. 
	function toWYSIWYG($code_text,$parm)
	{
		//eg. an image of the video thumbnail	
	}
}

?>