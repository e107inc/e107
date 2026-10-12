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
 * The globals the [hide] bbcode reads, set so that $e_hide_allowed alone decides what a reader sees, and put back afterwards.
 */
trait HideGlobals
{
	/** @var array $e_hide_allowed, $e_hide_query and $sql as they were found */
	private $savedHideGlobals = array();

	/**
	 * Keeps the [hide] globals, points the hide query at a row that always exists, and lets the reader see hidden content.
	 *
	 * @return void
	 */
	protected function holdHideGlobals()
	{
		global $e_hide_allowed, $e_hide_query, $sql;

		$this->savedHideGlobals = array($e_hide_allowed, $e_hide_query, $sql);
		$e_hide_allowed = true;
		$e_hide_query = 'SELECT 1';
		$sql = e107::getDb();
	}

	/**
	 * @param bool $allowed whether the reader may see what a [hide] block holds
	 * @return void
	 */
	protected function allowHiddenContent($allowed)
	{
		global $e_hide_allowed;

		$e_hide_allowed = (bool) $allowed;
	}

	/**
	 * Puts the [hide] globals back as {@see HideGlobals::holdHideGlobals()} found them.
	 *
	 * @return void
	 */
	protected function restoreHideGlobals()
	{
		global $e_hide_allowed, $e_hide_query, $sql;

		list($e_hide_allowed, $e_hide_query, $sql) = $this->savedHideGlobals;
	}
}
