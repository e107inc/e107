<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

use e107;

/**
 * A links-table row as the navigation renderers receive it, and the legacy renderer run over it.
 */
trait SitelinkRows
{
	/**
	 * @param array $columns the columns that differ from a plain top-level link to index.php
	 * @return array
	 */
	protected static function linkRow($columns = array())
	{
		return array_merge(array(
			'link_id'          => 1,
			'link_name'        => 'Home',
			'link_url'         => 'index.php',
			'link_owner'       => '',
			'link_sefurl'      => '',
			'link_description' => '',
			'link_button'      => '',
			'link_parent'      => 0,
			'link_open'        => 0,
			'link_class'       => 0,
			'link_order'       => 1,
		), $columns);
	}

	/**
	 * Renders a row with {@see \sitelinks::makeLink()}, setting for the call the $tp global it reads, which class2.php defines on a real request.
	 *
	 * @param array $row
	 * @return string
	 */
	protected static function makeLink($row)
	{
		$hadParser = array_key_exists('tp', $GLOBALS);
		$parser = $hadParser ? $GLOBALS['tp'] : null;
		$GLOBALS['tp'] = e107::getParser();

		try
		{
			return e107::getSitelinks()->makeLink($row, false, array('linkstart' => '', 'linkclass' => '', 'linkend' => ''), false);
		}
		finally
		{
			if($hadParser)
			{
				$GLOBALS['tp'] = $parser;
			}
			else
			{
				unset($GLOBALS['tp']);
			}
		}
	}
}
