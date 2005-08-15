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
|     $Source: /cvs_backup/e107_0.7/e107_handlers/bbcode_handler.php,v $
|     $Revision: 1.32 $
|     $Date: 2005-08-15 18:44:51 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/

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
		'*br', '*ba', 'color', 'size', 'code',
		'html', 'flash', 'link', 'email',
		'url', 'quote', 'left', 'right',
		'b', 'justify', 'file', 'stream',
		'textarea', 'list', 'php', 'time'
		);
		foreach($core_bb as $c)
		{
			$this->bbLocation[$c] = 'core';
		}
		if(isset($pref['plug_bb']) && $pref['plug_bb'] != '')
		{
			$tmp = explode(',',$pref['plug_bb']);
			foreach($tmp as $val)
			{
				list($code, $location) = explode(':',$val);
				$this->bbLocation[$code] = $location;
			}
		}
		$this->bbLocation = array_diff($this->bbLocation, array(''));
		krsort($this->bbLocation);
		$this->List = array_keys($this->bbLocation);
		while($this->List[count($this->List)-1]{0} == "*")
		{
			array_unshift($this->List, array_pop($this->List));
		}
	}

	function parseBBCodes($text, $p_ID)
	{
		global $code;
		global $postID;
		global $single_bb;
		$postID = $p_ID;
		$done = false;
		$single_bb = false;
		$i=0;
		foreach($this->List as $code)
		{
			if("*" == $code{0})
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
				$old_text = $text;
				$text = preg_replace_callback($pattern, array($this, 'doCode'), $text);
				if($old_text == $text)
				{
					$text = substr($old_text, 0, $pos)."&#091;".substr($old_text, $pos+1);
				}
			}
		}
		return $text;
	}

	function doCode($matches)
	{
		global $tp, $postID, $full_text, $code_text, $parm;

		$full_text = $tp->replaceConstants($matches[0]);
		$code = $matches[1];
		$parm = substr($matches[3], 1);
		$code_text = $tp->replaceConstants($matches[4]);
		if($this->single_bb == true)
		{
			$code = '*'.$code;
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
				$bbFile = e_FILE.'bbcode/'.strtolower(str_replace('*', '', $code)).'.bb';
			}
			else
			{
				// Add code to check for plugin bbcode addition
				$bbFile = e_PLUGIN.$this->bbLocation[$code].'/'.strtolower(str_replace('*', '', $code)).'.bb';
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