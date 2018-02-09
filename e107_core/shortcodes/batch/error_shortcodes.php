<?php

/**
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * @file
 * Error shortcodes.
 */

if(!defined('e107_INIT'))
{
	exit;
}


/**
 * Class error_shortcodes.
 */
class error_shortcodes extends e_shortcode
{

	/**
	 * @return mixed
	 */
	public function sc_error_title()
	{
		return varset($this->var['title'], '');
	}

	/**
	 * @return mixed
	 */
	public function sc_error_subtitle()
	{
		return varset($this->var['subtitle'], '');
	}

	/**
	 * @return mixed
	 */
	public function sc_error_caption()
	{
		return varset($this->var['caption'], '');
	}

	/**
	 * @return mixed
	 */
	public function sc_error_content()
	{
		return varset($this->var['content'], '');
	}

	/**
	 * @return string
	 */
	public function sc_error_link_home()
	{
		$icon = e107::getParser()->toGlyph('fa-home');
		$url = SITEURL;

		return '<a href="' . $url . '" class="btn btn-primary">' . $icon . ' ' . LAN_ERROR_20 . '</a>';
	}

	/**
	 * @return string
	 */
	public function sc_error_link_search()
	{
		$icon = e107::getParser()->toGlyph('fa-search');
		$url = e107::getUrl()->create('search');

		return '<a href="' . $url . '" class="btn btn-default btn-secondary">' . $icon . ' ' . LAN_ERROR_22 . '</a>';
	}

}

