<?php

namespace Helper;

use Codeception\Module;
use Test\Poll;

/**
 * The visual editor as a browser meets it: one instance per editor area, keyed by the textarea's id.
 */
class Editor extends Module
{
	const ENGINE_SCRIPT = 'script[src*="/e107_plugins/tinymce4/engines/tinymce4/tinymce.min.js"]';

	const INITIALISED = 'var editor = window.tinymce && window.tinymce.get(arguments[0]); return !!(editor && editor.initialized);';

	/**
	 * Poll until the editor on the textarea with id $id reports initialised, and fail the test when it has not after $timeout seconds.
	 *
	 * @param string $id the textarea's id attribute, in which e107 writes a field name's underscores as hyphens
	 * @param int $timeout seconds
	 * @return void
	 */
	public function waitForEditor($id, $timeout = 20)
	{
		$browser = $this->getModule('WebDriver');

		$initialised = Poll::until(function () use ($browser, $id)
		{
			return $browser->executeJS(self::INITIALISED, array($id));
		}, $timeout);

		$this->assertTrue($initialised, 'the editor on #'.$id.' did not initialise within '.$timeout.' seconds');
	}
}
