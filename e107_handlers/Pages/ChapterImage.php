<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Pages;

use e107\Shortcode\DeferredValue;

/**
 * A chapter's {CHAPTER_IMAGE}, built where the template reaches it, so a {SETIMAGE} before it or its own w, h and crop size it.
 *
 * @internal
 */
final class ChapterImage implements DeferredValue
{
	/** @var \e_parse */
	private $parser;

	/** @var string */
	private $image;

	/**
	 * @param \e_parse $parser
	 * @param string   $image the chapter's stored image path
	 */
	public function __construct(\e_parse $parser, $image)
	{
		$this->parser = $parser;
		$this->image = (string) $image;
	}

	/**
	 * @param array|string $parm read as {@see \e_parse::toImage()} options when an array
	 * @param string       $mode
	 * @return string|null
	 */
	public function render($parm, $mode)
	{
		return $this->parser->toImage($this->image, is_array($parm) ? $parm : array());
	}
}
