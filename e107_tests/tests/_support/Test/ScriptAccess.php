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
 * The class that may post script on the shared parser, set so that no one holds it, and put back afterwards; keep this in PHP 5.6 syntax.
 */
trait ScriptAccess
{
	/** @var array the class that could post script and the phpunit flag, as they were found */
	private $savedScriptAccess = array();

	/**
	 * Keeps the class that may post script and lets no one post script, with the phpunit flag that stops check_class() granting every class on the command line.
	 *
	 * @return void
	 */
	protected function holdScriptAccess()
	{
		$this->savedScriptAccess = array(
			'class'   => e107::getParser()->getScriptAccess(),
			'phpunit' => isset($GLOBALS['_E107']['phpunit']) ? $GLOBALS['_E107']['phpunit'] : null,
		);

		$GLOBALS['_E107']['phpunit'] = true;
		e107::getParser()->setScriptAccess(e_UC_NOBODY);
	}

	/**
	 * Puts the class that may post script and the phpunit flag back as {@see ScriptAccess::holdScriptAccess()} found them.
	 *
	 * @return void
	 */
	protected function restoreScriptAccess()
	{
		e107::getParser()->setScriptAccess($this->savedScriptAccess['class']);

		if($this->savedScriptAccess['phpunit'] === null)
		{
			unset($GLOBALS['_E107']['phpunit']);
		}
		else
		{
			$GLOBALS['_E107']['phpunit'] = $this->savedScriptAccess['phpunit'];
		}
	}
}
