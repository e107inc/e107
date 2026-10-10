<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Theme;

/**
 * A page for the theme to frame; its body is rendered only once the theme header has run.
 */
final class Page
{
	/** @var string */
	private $title;

	/** @var callable */
	private $render;

	/**
	 * @param string $title
	 * @param callable $render returns the body's HTML
	 */
	public function __construct($title, callable $render)
	{
		$this->title = (string) $title;
		$this->render = $render;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function render()
	{
		return (string) call_user_func($this->render);
	}
}
