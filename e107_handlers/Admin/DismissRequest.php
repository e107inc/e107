<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * A ?dismiss=<id> request, and the link that makes one.
 *
 * The e-token in the query string is only tested for presence here, because
 * e_session::attest() has already refused the request if its value was wrong.
 */
class DismissRequest
{
	const NONE = 'none';
	const REFUSED = 'refused';
	const UNKNOWN = 'unknown';
	const DONE = 'done';

	/** @var string */
	private $id;

	/** @var bool */
	private $authorised;

	/**
	 * @param array $query
	 *   Normally $_GET.
	 * @param bool $tokenMode
	 *   Whether the session issues tokens, which is whether e_TOKEN is defined.
	 */
	public function __construct(array $query, $tokenMode)
	{
		$this->id = (isset($query['dismiss']) && is_string($query['dismiss'])) ? $query['dismiss'] : '';
		$this->authorised = !$tokenMode || !empty($query['e-token']);
	}

	/**
	 * @param array $dismissible
	 *   Notice id => callable that records the suppression, called with the id.
	 * @return string
	 *   NONE when nothing was asked for, REFUSED when the request carried no
	 *   token, UNKNOWN when the id is not on offer, DONE when the callable ran.
	 */
	public function act(array $dismissible)
	{
		if($this->id === '')
		{
			return self::NONE;
		}

		if(!$this->authorised)
		{
			return self::REFUSED;
		}

		if(!isset($dismissible[$this->id]) || !is_callable($dismissible[$this->id]))
		{
			return self::UNKNOWN;
		}

		call_user_func($dismissible[$this->id], $this->id);

		return self::DONE;
	}

	/**
	 * @param string $id
	 * @param string $href
	 *   The page that will act on the request, with any query it already needs.
	 * @param string $token
	 *   The session's e_TOKEN.
	 * @param string $label
	 * @return string
	 */
	public static function link($id, $href, $token, $label)
	{
		$href .= (strpos($href, '?') === false) ? '?' : '&amp;';
		$href .= 'dismiss='.rawurlencode($id).'&amp;e-token='.$token;

		return "<a class='btn btn-xs btn-primary' href='".$href."'>".$label."</a>";
	}
}
