<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Test double for MagpieRSSTest; lives outside tests/unit because a test
 * file is parsed before the handler it would extend is loaded.
 */

class MagpieRSSTestDouble extends MagpieRSS
{
	/** @var string[] every run feed_cdata() received */
	public $runs = array();

	/** @var array[] every append() call, as array(prefix, key, text) */
	public $appended = array();

	/** @var string[] every value concat() was asked to append */
	public $concatenated = array();

	public function feed_cdata($p, $text)
	{
		$this->runs[] = $text;
		parent::feed_cdata($p, $text);
	}

	public function append($el, $text)
	{
		$this->appended[] = array($this->current_namespace, $el, $text);
		parent::append($el, $text);
	}

	public function concat(&$str1, $str2 = "")
	{
		$this->concatenated[] = $str2;
		parent::concat($str1, $str2);
	}
}
