<?php
/**
 * Copyright (C) e107 Inc (e107.org), Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
 * [code] bbcode
 */

if (!defined('e107_INIT')) { exit; }


class bb_code extends e_bb_base
{
	/**
	 * Called prior to save
	 * Re-assemble the bbcode
	 */
	function toDB($code_text, $parm)
	{
		$paramet 	= ($parm == 'inline') ? 'inline' : '';
	//	$code_text 	= htmlspecialchars($code_text, ENT_QUOTES, 'UTF-8');
	//	$code_text = str_replace('<','&ltr;',$code_text);
		$code_text = htmlentities($code_text, ENT_QUOTES, 'utf-8');	

	//	$srch = array('{','}');
	//	$repl = array( '&lbrace;', '&rbrace;'); // avoid code getting parsed as templates or shortcodes. 
			
	//	$code_text = str_replace($srch, $repl, $code_text);

		return $paramet ? '[code='.$paramet.']'.$code_text.'[/code]' : '[code]'.$code_text.'[/code]';
	}



	/**
	 *	Send output to browser. 
	 */
	function toHTML($code_text, $parm)
	{
		global $e107cache;
		
		$class = e107::getBB()->getClass('code');
		$pref 	= e107::getPref();
		$tp 	= e107::getParser();
			
		if($pref['smiley_activate']) 
		{
			$code_text = e107::getEmote()->filterEmotesRev($code_text);
		}
			
		$search = array(E_NL,'&#092;','&#036;', '&lt;');
		$replace = array("\r\n","\\",'$', '<');
		$code_text = str_replace($search, $replace, $code_text);




		if(isset($pref['useGeshi']) && $pref['useGeshi'] && file_exists(e_PLUGIN."geshi/geshi.php")) 
		{
			$code_md5 = md5($code_text);
			if(!$CodeCache = $e107cache->retrieve('GeshiParsed_'.$code_md5)) 
			{
				require_once(e_PLUGIN."geshi/geshi.php");
				if($parm) 
				{
					$geshi = new GeSHi($code_text, $parm, e_PLUGIN."geshi/geshi/");
				} 
				else 
				{
					$geshi = new GeSHi($code_text, ($pref['defaultLanGeshi'] ? $pref['defaultLanGeshi'] : 'php'), e_PLUGIN."geshi/geshi/");
				}
				
				$geshi->line_style1 = "font-family: 'Courier New', Courier, monospace; font-weight: normal; font-style: normal;";
				$geshi->set_encoding('utf-8');
				$geshi->enable_line_numbers(defset('GESHI_NORMAL_LINE_NUMBERS'));
				$geshi->set_header_type(defset('GESHI_HEADER_DIV'));
				$CodeCache = $geshi->parse_code();
				$e107cache->set('GeshiParsed_'.$code_md5, $CodeCache);
			}
				$ret = "<code class='code_highlight code-box {$class}' style='unicode-bidi: embed; direction: ltr'>".str_replace("&amp;", "&", $CodeCache)."</code>";
		}
		else
		{

				$code_text = html_entity_decode($code_text, ENT_QUOTES, 'utf-8');	
				$code_text = trim($code_text);
				$code_text = htmlspecialchars($code_text, ENT_QUOTES, 'utf-8');

				$srch = array('{','}');
				$repl = array('&lbrace;', '&rbrace;'); 

				$code_text = str_replace($srch, $repl, $code_text); // avoid code getting parsed as templates or shortcodes. 
				
				if($parm == 'inline')
				{
					return "<code style='unicode-bidi: embed; direction: ltr'>".$code_text."</code>";	
				}
				
			//	$highlighted_text = highlight_string($code_text, TRUE);
			// highlighted_text = str_replace(array("<code>","</code>"),"",$highlighted_text);
				$divClass = ($parm) ? $parm : 'code_highlight';
				$ret = "<pre class='prettyprint linenums ".e107::getParser()->toAttribute($divClass)." code-box {$class}' style='unicode-bidi: embed; direction: ltr'>".$code_text."</pre>";
		}
			
	
			
		$ret = str_replace("[", "&#091;", $ret);
		
		return $ret;
		
	}


}



?>