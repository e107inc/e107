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
|     $Source: /cvs_backup/e107_0.8/e107_handlers/bbcode_handler.php,v $
|     $Revision: 1.3 $
|     $Date: 2007-01-02 20:20:37 $
|     $Author: e107steved $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class e_bbcode
{

	var $bbList;
	var $bbLocation;
	var $single_bb;
	var $List;

	function e_bbcode()
	{
		global $pref;
		$core_bb = array(
		'blockquote', 'img', 'i', 'u', 'center',
		'_br', 'color', 'size', 'code',
		'html', 'flash', 'link', 'email',
		'url', 'quote', 'left', 'right',
		'b', 'justify', 'file', 'stream',
		'textarea', 'list', 'php', 'time',
		'spoiler', 'hide'
		);

		foreach($core_bb as $c)
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


		$this->bbLocation = array_diff($this->bbLocation, array(''));
		krsort($this->bbLocation);
		$this->List = array_keys($this->bbLocation);
		while($this->List[count($this->List)-1]{0} == "_")
		{
			array_unshift($this->List, array_pop($this->List));
		}
		if(($_c = array_search('code', $this->List)) !== FALSE)
		{
			unset($this->List[$c]);
			array_unshift($this->List, "code");
		}

	}

	function parseBBCodes($text, $p_ID)
	{
		global $code;
		global $postID;
		global $single_bb;
		global $_matches;
		$postID = $p_ID;
		$done = false;
		$single_bb = false;
		$i=0;

		$tmplist = array();
		foreach($this->List as $code)
		{
			if("_" == $code[0])
			{
				if(strpos($text, "[".substr($code, 1)) !== FALSE)
				{
					$tmplist[] = $code;
				}
			}
			else
			{
				if(strpos($text, "[{$code}") !== FALSE && strpos($text, "[/{$code}") !== FALSE)
				{
					$tmplist[] = $code;
				}
			}
		}

		foreach($tmplist as $code)
		{
			if("_" == $code{0})
			{
				$code = substr($code, 1);
				$this->single_bb = true;
				$pattern = "#\[({$code})(\d*)(.*?)\]#s";
			}
			else
			{
				$this->single_bb = false;
				$pattern = "#\[({$code})(\d*)(.*?)\](.*?)\[\/{$code}\\2\]#s";
			}
			$i=0;
			while($code && ($pos = strpos($text, "[{$code}")) !== false)
			{
				$text = preg_replace_callback($pattern, array($this, 'doCode'), $text);
				$leftover_code = $_matches[1].$_matches[2];
				$text = str_replace("[{$leftover_code}", "&#091;{$leftover_code}", $text);
				if ($i == strpos($text, "[{$code}")) { break; }

				if ($pos == ($i = strpos($text,"[{$code}")))
    			{
        			$pattern2 = "#\[({$code})(\d*)(.*?)\]#s";
        			$text = preg_replace_callback($pattern2, array($this, 'doCode'), $text);
        			$leftover_code = $_matches[1].$_matches[2];
        			$text = str_replace("[{$leftover_code}", "&#091;{$leftover_code}", $text);
    			}
			}
		}
		return $text;
	}

	function doCode($matches)
	{
		global $tp, $postID, $full_text, $code_text, $parm, $_matches;
		$_matches = $matches;
		$full_text = $tp->replaceConstants($matches[0]);
		$code = $matches[1];
		$parm = substr($matches[3], 1);
		$code_text = '';
		if (isset($matches[4])) $code_text = $tp->replaceConstants($matches[4]);
		if($this->single_bb == true)
		{
			$code = '_'.$code;
		}
		if (E107_DEBUG_LEVEL)
		{
			global $db_debug;
			$db_debug->logCode(1, $code, $parm, $postID);
		}

		if (is_array($this->bbList) && array_key_exists($code, $this->bbList))
		{
			$bbcode = $this->bbList[$code];
		}
		else
		{
			if ($this->bbLocation[$code] == 'core')
			{
				$bbFile = e_FILE.'bbcode/'.strtolower(str_replace('_', '', $code)).'.bb';
			}
			else
			{
				// Add code to check for plugin bbcode addition
				$bbFile = e_PLUGIN.$this->bbLocation[$code].'/'.strtolower($code).'.bb';
			}
			if (file_exists($bbFile))
			{
				$bbcode = file_get_contents($bbFile);
				$this->bbList[$code] = $bbcode;
			}
			else
			{
				$this->bbList[$code] = '';
				return false;
			}
		}
		global $e107_debug;
		if(E107_DBG_BBSC)
		{
			trigger_error("starting bbcode [$code]", E_USER_ERROR);
		}
		ob_start();
		$bbcode_return = eval($bbcode);
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
}
?>
