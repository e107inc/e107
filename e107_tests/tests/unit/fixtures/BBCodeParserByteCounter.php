<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * The bbcode parser, counting the bytes of text it is handed to parse or to read for [hide] blocks.
 */
class BBCodeParserByteCounter extends e_bbcode
{
	/** @var int */
	public $bytes = 0;

	/**
	 * @param string      $value
	 * @param string      $p_ID
	 * @param string|bool $force_lower
	 * @param string|bool $bbStrip
	 * @return string
	 */
	function parseBBCodes($value, $p_ID = '', $force_lower = 'default', $bbStrip = false)
	{
		$this->bytes += strlen($value);

		return parent::parseBBCodes($value, $p_ID, $force_lower, $bbStrip);
	}

	/**
	 * @param string $text
	 * @return array[]
	 */
	public function hideBlockTree($text)
	{
		$this->bytes += strlen($text);

		return parent::hideBlockTree($text);
	}
}
