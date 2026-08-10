<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

/**
 * A Drupal site that answers with whatever the test says, without a network.
 *
 * In its own file because drupal_import cannot be declared until the bootstrap
 * has defined e_PLUGIN and loaded the plugin's language file, and Codeception
 * parses a *Test.php file before either has happened.
 */
class drupal_avatar_double extends drupal_import
{
	/** @var string bytes the source site answers with */
	public $bytes = '';

	/** @var array every URL the importer asked for */
	public $requested = array();

	public function fetchRemoteFile($url, $localName)
	{
		$this->requested[] = $url;

		return file_put_contents(e_TEMP.$localName, $this->bytes) !== false;
	}
}
