<?php

namespace Test;

/**
 * Reads the debug lines a test causes on the default message stack; keep this in PHP 5.6 syntax.
 */
trait DebugMessages
{
	/**
	 * Empties the default debug message stack, so {@see DebugMessages::debugMessages()} reads only what follows.
	 *
	 * @return void
	 */
	protected function resetDebugMessages()
	{
		\e107::getMessage()->reset(E_MESSAGE_DEBUG, 'default', false);
	}

	/**
	 * @return string the default stack's debug lines since {@see DebugMessages::resetDebugMessages()}, one per line; reading empties the stack
	 */
	protected function debugMessages()
	{
		return implode("\n", (array) \e107::getMessage()->get(E_MESSAGE_DEBUG, 'default', true, true));
	}
}
