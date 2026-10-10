<?php

interface Preparer
{
	/**
	 * Path the app under test runs from: the original tree, or an isolated
	 * copy the preparer set up. Defined before APP_PATH, during bootstrap.
	 *
	 * @return string
	 */
	public function getAppPath();
}