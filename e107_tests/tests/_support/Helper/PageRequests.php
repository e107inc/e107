<?php

namespace Helper;

use Codeception\Module;

/**
 * Records the XMLHttpRequests the current window opens, so a test can tell what a page's scripts asked for and when they were answered.
 */
class PageRequests extends Module
{
	/**
	 * Start recording in the current window: every address opened, and how many of those requests have ended.
	 *
	 * @return void
	 */
	public function startRecordingRequests()
	{
		$this->getModule('WebDriver')->executeJS("window.__opened = []; window.__done = 0; var open = XMLHttpRequest.prototype.open; XMLHttpRequest.prototype.open = function (method, url) { window.__opened.push(String(url)); this.addEventListener('loadend', function () { window.__done++; }); return open.apply(this, arguments); };");
	}

	/**
	 * @return string every address the current window has opened since {@see PageRequests::startRecordingRequests()}, space separated
	 */
	public function grabRequestsOpened()
	{
		return implode(' ', $this->getModule('WebDriver')->executeJS('return window.__opened;'));
	}

	/**
	 * Wait until at least $count of the requests recorded in the current window have ended.
	 *
	 * @param int $count
	 * @return void
	 */
	public function waitForRequestsEnded($count)
	{
		$this->getModule('WebDriver')->waitForJS('return window.__done >= '.(int) $count.';', 10);
	}
}
