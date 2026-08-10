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
 * A remote host that answers with whatever the test says, without a network.
 *
 * The harness has no outbound access, and e_file::isUrlSafe() refuses the
 * container's own address, so the seam the importer downloads through is what a
 * unit test stands in for. Everything the package is about happens after the
 * bytes arrive.
 *
 * In its own file because the class cannot be declared until the bootstrap has
 * defined e_PLUGIN and the provider has been read, and Codeception parses a
 * *Test.php file before either has happened.
 */
class import_remote_image_double extends rss_import
{
	/** @var string bytes the remote host answers with */
	public $bytes = '';

	/** @var boolean whether the remote host answers at all */
	public $reachable = true;

	/** @var array every URL the importer asked for */
	public $requested = array();

	public function fetchRemoteFile($url, $localName)
	{
		$this->requested[] = $url;

		if(!$this->reachable)
		{
			return false;
		}

		return file_put_contents(e_TEMP.$localName, $this->bytes) !== false;
	}

	public function storeRemoteImage($url, $dir, $prefix = '')
	{
		return $this->importRemoteImage($url, $dir, $prefix);
	}
}
