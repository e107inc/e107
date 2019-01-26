<?php
/*
+ ----------------------------------------------------------------------------+
||     e107 website system
|
|     Copyright (C) 2008-2009 e107 Inc 
|     http://e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.8/e107_handlers/cli_class.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

class eCLI
{

	/**
	 *  Provided a list of command line arguments, parse them in a unix manner.
	 *  If no args are provided, will default to $_SERVER['argv']
	 *
	 * @param string $argv
	 * @return array arg values
	 */

	function parse_args($argv='')
	{
		if($argv == '')
		{
			if(isset($_SERVER['argv']))
			{
				$argv = $_SERVER['argv'];
			}
			else
			{
				return array();
			}
		}
		if(!is_array($argv))
		{
			return array();
		}
		$_ARG = array();
		foreach ($argv as $arg)
		{
			if (preg_match('#^-{1,2}([a-zA-Z0-9]*)=?(.*)$#', $arg, $matches))
			{
				$key = $matches[1];
				switch ($matches[2])
				{
					case '':
					case 'true':
					$arg = true;
					break;
					case 'false':
					$arg = false;
					break;
					default:
					$arg = $matches[2];
				}

				/* make unix like -afd == -a -f -d */
				if(preg_match("/^-([a-zA-Z0-9]+)/", $matches[0], $match))
				{
					$string = $match[1];
					for($i=0; strlen($string) > $i; $i++)
					{
						$_ARG[$string[$i]] = true;
					}
				}
				else
				{
					/* --arg=val will assign $_ARG['arg'] to 'val' */
					/* --arg (or --arg=true) will assign $_ARG['arg'] to true */
					/* --arg=false will assign $_ARG['arg'] to false */
					$_ARG[$key] = $arg;
				}
			}
			else
			{
				/* assume command input parm, add each to input array */
				$_ARG['input'][] = $arg;
			}
		}
		return $_ARG;
	}

}
