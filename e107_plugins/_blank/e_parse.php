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



if (!defined('e107_INIT')) { exit; }


class _blank_parse
{


	/* constructor */
	function __construct()
	{


	}


	/**
	 * Process a string before it is sent to the browser as html.
	 * @param string $text html/text to be processed.
	 * @param string $context Current context ie.  OLDDEFAULT | BODY | TITLE | SUMMARY | DESCRIPTION | WYSIWYG etc.
	 * @return string
	 */
	function toHTML($text, $context='')
	{
		$text = str_replace('****', '<hr>', $text);
		return $text;
	}




	/**
	 * Process a string before it is saved to the database.
	 * @param string $text html/text to be processed.
	 * @param array $param nostrip, noencode etc.
	 * @return string
	 */
	function toDB($text, $param=array())
	{
	//	e107::getDebug()->log($text);
		$text = str_replace('<hr>', '****', $text);
		return $text;
	}




}




?>