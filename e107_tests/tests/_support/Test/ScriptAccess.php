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
 * The class that may post script on the shared parser, set so that no one holds it, and put back afterwards.
 */
trait ScriptAccess
{
	/** @var mixed the class that could post script as it was found */
	private $savedScriptAccess;

	/**
	 * Keeps the class that may post script and lets no one post script.
	 *
	 * @return void
	 */
	protected function holdScriptAccess()
	{
		$this->savedScriptAccess = e107::getParser()->getScriptAccess();
		e107::getParser()->setScriptAccess(e_UC_NOBODY);
	}

	/**
	 * Puts the class that may post script back as {@see ScriptAccess::holdScriptAccess()} found it.
	 *
	 * @return void
	 */
	protected function restoreScriptAccess()
	{
		e107::getParser()->setScriptAccess($this->savedScriptAccess);
	}
}
