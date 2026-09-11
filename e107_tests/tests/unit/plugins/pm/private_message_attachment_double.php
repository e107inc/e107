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
 * Lets the attachment tests put both attachment trees somewhere disposable,
 * the media one and the directory releases before it wrote to. The production
 * paths are e_MEDIA and e_PLUGIN, which the acceptance suite exercises; what
 * the unit tests are for is the answer the methods give back, which acceptance
 * cannot see.
 *
 * In its own file because the class cannot be declared until the bootstrap has
 * defined e_PLUGIN and pm_class.php has been read, and Codeception parses a
 * *Test.php file before either has happened.
 */
class private_message_attachment_double extends private_message
{
	/** @var string */
	public $root;

	/** @var string the legacy directory, left unset to keep the production one */
	public $legacy;

	public function attachmentRoot()
	{
		return $this->root;
	}

	public function legacyAttachmentDir()
	{
		return isset($this->legacy) ? $this->legacy : parent::legacyAttachmentDir();
	}
}
