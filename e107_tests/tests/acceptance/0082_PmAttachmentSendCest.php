<?php

/**
 * The PM compose form can send an attachment.
 *
 * post_pm() gated the whole attachment path on $_POST['uploaded'] and assigned
 * that key four lines inside the block requiring it non-empty, so the block
 * could never run. Nothing in the tree posts the key: the shipped form emits
 * file_userfile[]. Attachments have been dropped without a word since v2.1.4.
 *
 * 0041 and 0042 seed their attachments through an in-app probe rather than the
 * compose form, which is why neither noticed. This one posts what the form
 * posts and reads back what the application stored.
 *
 * Members and front-end sign-in come from Helper\ForumFixture; neither is forum
 * specific.
 */
class PmAttachmentSendCest
{
	const PROBE_FILE = 'e107_tests_pm_send_probe.php';
	const PLUGIN = 'pm';

	/** The names the browser puts in $_FILES, and the tails of the stored names. */
	const UPLOAD_NAME = 'sent.pdf';
	const SMALL_NAME = 'fits.pdf';

	/** Written into the attachment so the stored bytes can be identified. */
	const SECRET = 'PM-SENT-ATTACHMENT-SECRET-BYTES';

	const SUBJECT = 'attachment send';

	/** e_UC_MEMBER and e_UC_NOBODY, which the test process does not define. */
	const CLASS_MEMBER = 253;
	const CLASS_NOBODY = 255;

	/** attach_size in kilobytes: comfortably over the upload, and under it. */
	const LIMIT_KB = 500;
	const LIMIT_KB_TOO_SMALL = 1;

	/** @var int */
	private $alice;

	/** @var int */
	private $bob;

	/** @var array the files the fixture posts, on the runner's own disk, by name */
	private $uploads = array();

	public function _before(AcceptanceTester $I)
	{
		// pm.php opens with e107::isInstalled('pm') and redirects while that is
		// false, which would turn a dropped attachment into a missing plugin.
		$I->havePluginInstalled(self::PLUGIN);

		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$this->probe($I, 'act=reset&class='.self::CLASS_MEMBER.'&size='.self::LIMIT_KB);

		$this->alice = $I->haveForumMember('pmsendalice');
		$this->bob = $I->haveForumMember('pmsendbob');

		// The larger one is over LIMIT_KB_TOO_SMALL and the smaller one is under it,
		// so one post can carry both verdicts at once.
		$this->uploads = array(
			self::UPLOAD_NAME => "%PDF-1.4\n".self::SECRET."\n".str_repeat("%pad\n", 400)."%%EOF\n",
			self::SMALL_NAME => "%PDF-1.4\n".self::SECRET."\n%%EOF\n",
		);

		foreach($this->uploads as $name => $bytes)
		{
			$path = sys_get_temp_dir().'/e107_pm_send_'.getmypid().'_'.$name;
			file_put_contents($path, $bytes);
			$this->uploads[$name] = $path;
		}
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();

		foreach($this->uploads as $path)
		{
			if (file_exists($path))
			{
				unlink($path);
			}
		}

		$I->deleteAppFile(self::PROBE_FILE);
		$I->dropPluginInstall(self::PLUGIN);
		$I->dropPluginProbe();
	}

	/**
	 * The defect. Alice holds attach_class and posts a file the way the shipped
	 * form posts one, with no `uploaded` field anywhere in the request.
	 *
	 * Both halves are asserted because either alone can be satisfied by a
	 * half-working path: a row naming a file nobody stored, or a stored file no
	 * message points at.
	 */
	public function anAttachmentPostedByTheShippedFormIsSentWithTheMessage(AcceptanceTester $I)
	{
		$I->loginToForum('pmsendalice');

		$this->sendWithAttachment($I);

		$row = $this->probe($I, 'act=lastpm');

		$I->assertSame((string) $this->alice, $this->grab('/PM_FROM=(\d+)/', $row),
			'Alice must have sent a message');
		$I->assertStringEndsWith(self::UPLOAD_NAME, $this->grab('/PM_ATTACH=(\S*)/', $row),
			'The message must name the attachment that was posted');
		$I->assertSame($this->grab('/PM_ATTACH=(\S*)/', $row), $this->grab('/PM_STORED=(\S*)/', $row),
			'The sender\'s attachment directory must hold exactly what the message names');
	}

	/**
	 * The other side of the same guard. LAN_PM_23 sits in the else arm, so it
	 * has been as unreachable as the attachment path itself and a member who
	 * may not attach files was told nothing at all.
	 */
	public function aMemberWhoMayNotAttachFilesIsToldSo(AcceptanceTester $I)
	{
		$this->probe($I, 'act=reset&class='.self::CLASS_NOBODY.'&size='.self::LIMIT_KB);

		$I->loginToForum('pmsendalice');

		$this->sendWithAttachment($I);

		$I->seeResponseCodeIs(200);
		$I->see('You are not allowed to send attachments');

		$I->assertSame('', $this->grab('/PM_STORED=(\S*)/', $this->probe($I, 'act=lastpm')),
			'Nothing may be stored for a member who may not attach files');
	}

	/**
	 * A forward guard rather than evidence about this change: at the base nothing
	 * is stored for any reason at all, so all three assertions hold there too.
	 * What it holds is the size verdict itself, which is new work here. Take the
	 * limit out of the loop and the file is stored, PM_STORED fills, and the
	 * message the sender is shown goes away with it.
	 */
	public function anAttachmentOverTheSizeLimitIsNotAttached(AcceptanceTester $I)
	{
		$this->probe($I, 'act=reset&class='.self::CLASS_MEMBER.'&size='.self::LIMIT_KB_TOO_SMALL);

		$I->loginToForum('pmsendalice');

		$this->sendWithAttachment($I);

		$I->see('exceeds size limit');

		$row = $this->probe($I, 'act=lastpm');

		$I->assertSame('', $this->grab('/PM_ATTACH=(\S*)/', $row),
			'The message must carry no attachment it was told exceeded the limit');
		$I->assertSame('', $this->grab('/PM_STORED=(\S*)/', $row),
			'Nothing must reach the disk for an attachment reported as not attached');
	}

	/**
	 * One post, both verdicts. This is the only case that reaches the pruning of
	 * $_FILES, because with a single file the guard either refuses the send
	 * outright or has nothing to prune, and getUploaded() reads the superglobal
	 * rather than anything post_pm() filtered. Drop the prune and the oversize
	 * file is stored and named while the sender is told it was not attached.
	 */
	public function onlyTheAttachmentsUnderTheLimitAreSentWithTheMessage(AcceptanceTester $I)
	{
		$this->probe($I, 'act=reset&class='.self::CLASS_MEMBER.'&size='.self::LIMIT_KB_TOO_SMALL);

		$I->loginToForum('pmsendalice');

		$this->sendWithAttachment($I, array(self::SMALL_NAME, self::UPLOAD_NAME));

		$I->see('exceeds size limit');

		$row = $this->probe($I, 'act=lastpm');
		$stored = $this->grab('/PM_STORED=(\S*)/', $row);

		$I->assertStringNotContainsString(',', $stored,
			'Exactly one of the two posted files may be stored');
		$I->assertStringEndsWith(self::SMALL_NAME, $stored,
			'The stored file must be the one under the limit');
		$I->assertSame($this->grab('/PM_ATTACH=(\S*)/', $row), $stored,
			'The message must name exactly what is on disk');
	}

	/**
	 * A PDF because e107 vets uploads against filetypes.xml, whose member list
	 * the installer writes as zip,gz,jpg,jpeg,png,gif,webp,xml,pdf.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function sendWithAttachment(AcceptanceTester $I, array $names = array(self::UPLOAD_NAME))
	{
		$files = array();

		foreach($names as $name)
		{
			$files[] = array(
				'name'     => $name,
				'type'     => 'application/pdf',
				'tmp_name' => $this->uploads[$name],
				'error'    => 0,
				'size'     => filesize($this->uploads[$name]),
			);
		}

		$I->sendPostRequestWithFiles('/e107_plugins/pm/pm.php', array(
			'postpm'     => '1',
			'numsent'    => '0',
			'pm_to'      => (string) $this->bob,
			'pm_subject' => self::SUBJECT,
			'pm_message' => 'see attached',
			'e-token'    => $I->grabForumToken('/e107_plugins/pm/pm.php?send'),
		), array('file_userfile' => $files));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string probe output
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);
		$body = $I->grabPageSource();

		if (strpos($body, 'PROBE_OK') === false)
		{
			throw new \RuntimeException('PM send probe failed for "'.$query.'": '.trim(strip_tags($body)));
		}

		return $body;
	}

	/**
	 * @param string $pattern
	 * @param string $body
	 * @return string
	 */
	private function grab($pattern, $body)
	{
		$matches = array();

		if (!preg_match($pattern, $body, $matches))
		{
			throw new \RuntimeException('PM send probe reported no '.$pattern.': '.trim(strip_tags($body)));
		}

		return $matches[1];
	}

	/**
	 * Where e107 keeps an attachment is e107's own answer and not one this test
	 * should be computing. e_CURRENT_PLUGIN is what getUserDir() reads to place
	 * the directory, and only a request to a plugin's own page defines it, so a
	 * probe in the docroot says which plugin it stands in for.
	 *
	 * @return string
	 */
	private function probeSource()
	{
		$php = <<<'PHP'
<?php
// Fixture for PmAttachmentSendCest. Written per test, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(!defined('e_CURRENT_PLUGIN'))
{
	define('e_CURRENT_PLUGIN', 'pm');
}

function e107_test_rmtree($dir)
{
	if(!is_dir($dir))
	{
		return;
	}

	foreach(scandir($dir) as $entry)
	{
		if($entry === '.' || $entry === '..')
		{
			continue;
		}

		$path = $dir.'/'.$entry;

		is_dir($path) ? e107_test_rmtree($path) : unlink($path);
	}

	rmdir($dir);
}

$act = isset($_GET['act']) ? $_GET['act'] : '';
$db = e107::getDb();

switch($act)
{
	case 'reset':
		// Messages this fixture sends are not tracked by the Db module, so a
		// run that died part way through would leave them for the next one.
		$db->delete('private_msg');

		// Attachments are for admins on a stock install and the member under test
		// is deliberately not one, so the class is named per case, and the size
		// limit with it because one case is about the limit.
		e107::getPlugConfig('pm')
			->set('attach_class', (int) $_GET['class'])
			->set('attach_size', (int) $_GET['size'])
			->save(false, true, false);

		// The list of what may be uploaded, where the application looks for it.
		// A site that has ever visited admin > File Types has one; a freshly
		// installed one does not, because install.php strips the site path from
		// SYSTEM_DIRECTORY before saveFileTypes() writes the file, and
		// set_max_size() then makes every upload "exceed allowable limits".
		if(!is_readable(e_SYSTEM.'filetypes.xml'))
		{
			file_put_contents(e_SYSTEM.'filetypes.xml', '<?xml version="1.0" encoding="utf-8"?>
<e107Filetypes>
	<class name="member" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="2M" />
	<class name="admin" type="zip,gz,jpg,jpeg,png,gif,webp,xml,pdf" maxupload="10M" />
	<class name="main" type="zip,gz,rar,jpg,jpeg,png,gif,webp,xml,pdf,ppt,pptx,mov,mp4,mp3,doc,docx,xls,xlsm,mp3,mp4,wav,ogg,webm,mid,midi,torrent,txt,dmg,msi" maxupload="50M" />
</e107Filetypes>');
		}

		foreach(glob(e_CACHE_CONTENT.'S_Config_*.cache.php') ?: array() as $file)
		{
			@unlink($file);
		}

		// Whatever is found on disk later was put there during this case.
		e107_test_rmtree(rtrim(e_MEDIA.'plugins/pm/attachments/', '/'));

		echo "PROBE_OK reset\n";
		break;

	case 'lastpm':
		// Read back through the application's own placement rule, so the test
		// asserts on where e107 put the file rather than on a path it guessed.
		$row = $db->createQueryBuilder()
			->select('pm_id', 'pm_from', 'pm_attachments')->from('private_msg')
			->orderBy('pm_id', 'DESC')->setMaxResults(1)->fetchRow();

		if(empty($row))
		{
			echo "no messages were sent\n";
			exit;
		}

		$names = ($row['pm_attachments'] === '') ? array() : explode(chr(0), $row['pm_attachments']);
		$dir = rtrim(e107::getFile()->getUserDir((int) $row['pm_from'], false, 'attachments'), '/').'/';
		$stored = array();

		foreach(glob($dir.'*') ?: array() as $file)
		{
			if(is_file($file) && !in_array(basename($file), array('.htaccess', 'index.html'), true))
			{
				$stored[] = basename($file);
			}
		}

		echo "PROBE_OK PM_ID=".$row['pm_id']." PM_FROM=".$row['pm_from']
			." PM_STORED=".implode(',', $stored)." PM_ATTACH=".implode(',', $names)."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;

		return $php;
	}
}
