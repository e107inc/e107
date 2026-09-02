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
 * Which post_attachments key the SMF provider files an attachment under: 'img' for an image, 'file' for the rest.
 */
class smfAttachmentsTest extends \Codeception\Test\Unit
{
	/** @var smf_import */
	private $smf;

	protected function _before()
	{
		require_once(e_PLUGIN.'import/providers/smf_import_class.php');

		$this->smf = new smf_import();
	}

	/**
	 * @param string|null $filename null for a message without an attachment
	 * @param string|null $fileext  as SMF stores it
	 * @return array|null the decoded post_attachments the provider produced
	 */
	private function attachmentsOf($filename, $fileext)
	{
		$target = array();
		$source = array(
			'id_msg'         => 7,
			'body'           => 'hello',
			'id_topic'       => 3,
			'id_board'       => 2,
			'approved'       => 1,
			'poster_time'    => 1490000000,
			'id_member'      => 5,
			'modified_time'  => 0,
			'post_edit_user' => 0,
			'poster_ip'      => '127.0.0.1',
			'poster_name'    => 'someone',
			'filename'       => $filename,
			'fileext'        => $fileext,
			'size'           => '1234',
		);

		$this->smf->copyForumPostData($target, $source);

		return $target['post_attachments'] === null ? null : e107::unserialize($target['post_attachments']);
	}

	public function testEveryImageExtensionIsFiledUnderImg()
	{
		foreach (array('png', 'jpg', 'jpeg', 'gif', 'webp') as $ext)
		{
			$attachments = $this->attachmentsOf('picture.'.$ext, $ext);

			self::assertSame(array('img'), array_keys($attachments), $ext.' is an image');
			self::assertSame('picture.'.$ext, $attachments['img'][0]['file']);
			self::assertSame('1234', $attachments['img'][0]['size']);
		}
	}

	public function testAnythingElseIsFiledUnderFile()
	{
		$attachments = $this->attachmentsOf('notes.pdf', 'pdf');

		self::assertSame(array('file'), array_keys($attachments));
		self::assertSame('notes.pdf', $attachments['file'][0]['file']);
	}

	public function testAMessageWithoutAnAttachmentStoresNothing()
	{
		self::assertNull($this->attachmentsOf(null, null));
	}
}
