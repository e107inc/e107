<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Shortcode;

/**
 * A deferred value that returns one fixed output and records what each render was given.
 */
final class RecordingDeferredValue implements DeferredValue
{
	/** @var array[] each render's parameters and mode, in order */
	public $arguments = array();

	/** @var int[] the parser's thumb width at each render, in order */
	public $thumbWidths = array();

	/** @var string|null */
	private $output;

	/** @var \e_parse */
	private $parser;

	/**
	 * @param string|null $output what every render returns
	 * @param \e_parse    $parser
	 */
	public function __construct($output, \e_parse $parser)
	{
		$this->output = $output;
		$this->parser = $parser;
	}

	/**
	 * @param array|string $parm
	 * @param string       $mode
	 * @return string|null
	 */
	public function render($parm, $mode)
	{
		$this->arguments[] = array($parm, $mode);
		$this->thumbWidths[] = $this->parser->thumbWidth();

		return $this->output;
	}
}
