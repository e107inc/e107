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
 * Lets private_messageAttachmentPathsTest put the attachment tree somewhere
 * disposable. The production path is e_MEDIA, which the acceptance suite
 * exercises; what the unit tests are for is the answer the methods give back,
 * which acceptance cannot see.
 *
 * In its own file because the class cannot be declared until the bootstrap has
 * defined e_PLUGIN and pm_class.php has been read, and Codeception parses a
 * *Test.php file before either has happened.
 */
class private_message_attachment_double extends private_message
{
	/** @var string */
	public $root;

	public function attachmentRoot()
	{
		return $this->root;
	}
}
