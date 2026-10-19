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
	private $body;

	/** @var string|null */
	private $caption;

	/** @var string */
	private $mode = 'default';

	/**
	 * @param string $title
	 * @param callable $body returns the body's HTML
	 */
	public function __construct($title, callable $body)
	{
		$this->title = (string) $title;
		$this->body = $body;
	}

	/**
	 * A copy whose body the theme draws in one box, as {@see e_render::tablerender()} does.
	 *
	 * @param string $caption
	 * @param string $mode the table style mode
	 * @return Page
	 */
	public function withCaption($caption, $mode = 'default')
	{
		$page = clone $this;
		$page->caption = (string) $caption;
		$page->mode = (string) $mode;

		return $page;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return string|null null when the body is drawn as it is, without a box
	 */
	public function getCaption()
	{
		return $this->caption;
	}

	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}

	/**
	 * @return string the body's HTML, without the box
	 */
	public function render()
	{
		return (string) call_user_func($this->body);
	}
}
