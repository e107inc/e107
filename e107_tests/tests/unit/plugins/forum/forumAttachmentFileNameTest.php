<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * @group plugins
 *
 * The file a stored attachment entry names, from either shape an entry is stored in.
 */
class forumAttachmentFileNameTest extends \Codeception\Test\Unit
{
	const FILE = '1690000000_0_notes.txt';

	protected function _before()
	{
		require_once(e_PLUGIN . 'forum/forum_attachments.php');
	}

	public function testAnEntryStoredAsAnArrayNamesItsFile()
	{
		self::assertSame(self::FILE, forum_attachments::fileName(array('file' => self::FILE, 'name' => 'notes.txt', 'size' => 5)));
	}

	public function testAnEntryStoredAsABareNameIsItsFile()
	{
		self::assertSame(self::FILE, forum_attachments::fileName(self::FILE));
	}

	public function testAnArrayEntryWithoutAFileNamesNone()
	{
		self::assertSame('', forum_attachments::fileName(array('name' => 'notes.txt')));
	}
}
