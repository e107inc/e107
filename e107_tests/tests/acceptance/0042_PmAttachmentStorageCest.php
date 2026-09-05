<?php

/**
 * A private message attachment is fetched straight off the web server.
 *
 * The plugin stores attachments inside the document root, at
 * e107_media/<site_path>/plugins/pm/attachments/user_NNNNNN/<name>, and e107
 * ships a deny rule for e107_system but none for e107_media. Nothing but the
 * stored name stands between the file and anybody at all: no session, no
 * membership, no message id. The name is disclosed in full to both parties to
 * the message, so either of them can hand the file to the world by handing over
 * one URL, and neither can take it back.
 *
 * A site upgraded from a release that stored attachments beside the plugin has
 * the same files at e107_plugins/pm/attachments/<name>. pm_class.php still
 * reads and deletes from that directory, so it is a live route and not merely
 * an old one.
 *
 * This is the other half of GHSA-46vx-phhg-m6h9. Package P5 shut the pm.php
 * route to another member's attachment; the bytes were reachable without
 * pm.php the whole time.
 *
 * The fixture sends a real PM with a real attachment through pm.php's own form,
 * because what is under test is what the plugin does when it stores a file. A
 * probe that wrote the attachment itself would prove only that a directory can
 * be protected, not that the plugin protects it.
 *
 * Storing a file is not the route that matters on a site that already has one,
 * where every attachment predates the rules and no member need ever send
 * another. installingThePluginProtectsAttachmentsAlreadyOnDisk drives the route
 * such a site does take, the plugin manager's own install and upgrade.
 *
 * Declared gap: a deny rule stops Apache, not e107's own PHP file servers.
 * thumb.php re-serves any readable image under e_MEDIA (e_thumbnail::checkSrc
 * has no root containment on this base), so an image-typed attachment still
 * comes back out through it. Package P2 contains thumb.php and names this
 * subtree; it is not an ancestor of this branch, so the assertion belongs to
 * the integration merge rather than here.
 *
 * Every path here is asked of the application through the probe and never
 * derived from the database name and prefix: the acceptance suite installs
 * twice and the second install puts the site under the literal path
 * 000000test, so a computed site path addresses a directory the application
 * never reads.
 *
 * Members and front-end sign-in come from Helper\ForumFixture, as in 0033.
 */
class PmAttachmentStorageCest
{
	const PROBE_FILE = 'e107_tests_pm_storage_probe.php';
	const PLUGIN = 'pm';

	/** Bytes of the file the fixture really uploads, looked for byte for byte. */
	const SECRET = 'PM-STORED-ATTACHMENT-SECRET-BYTES';

	/** Bytes of the attachment an upgraded site left in the plugin directory. */
	const LEGACY_SECRET = 'PM-LEGACY-ATTACHMENT-SECRET-BYTES';

	/** Bytes of a forum post attachment, which is public by design. */
	const FORUM_BYTES = 'FORUM-ATTACHMENT-PUBLIC-BYTES';

	const SUBJECT = 'attachment storage';

	/** @var int */
	private $alice;

	/** @var int */
	private $bob;

	/** @var int id of the message alice sent bob */
	private $pmId;

	/** @var string url path of the stored attachment, relative to the docroot */
	private $attachmentUrl;

	/** @var string url path of the file left in the legacy plugin directory */
	private $legacyUrl;

	/** @var string url path of a forum post attachment */
	private $forumUrl;

	/** @var string the file the fixture posts, on the runner's own disk */
	private $upload;

	public function _before(AcceptanceTester $I)
	{
		// pm.php opens with e107::isInstalled('pm') and redirects while that is
		// false, which would turn a refusal about storage into a refusal about
		// the plugin being absent.
		$I->havePluginInstalled(self::PLUGIN);

		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$this->probe($I, 'act=reset');

		$this->alice = $I->haveForumMember('pmstorealice');
		$this->bob = $I->haveForumMember('pmstorebob');

		$this->legacyUrl = $this->grab('/LEGACY_FILE=(\S+)/',
			$this->probe($I, 'act=legacy&create=1'));

		// A member who holds an attachment directory and is not the sender, so
		// that what the send reads can be told from what it does not.
		$this->probe($I, 'act=dirs&plugin=pm&user='.$this->bob.'&create=1');

		$this->upload = sys_get_temp_dir().'/e107_pm_storage_'.getmypid().'.pdf';
		file_put_contents($this->upload, "%PDF-1.4\n".self::SECRET."\n%%EOF\n");

		$I->loginToForum('pmstorealice');
		$this->sendWithAttachment($I);
		$I->logoutFromForum();

		$row = $this->probe($I, 'act=lastpm');
		$this->pmId = (int) $this->grab('/PM_ID=(\d+)/', $row);
		$attachment = $this->grab('/PM_ATTACH=(\S+)/', $row);

		$this->attachmentUrl = $this->grab('/USER_DIR=(\S+)/',
			$this->probe($I, 'act=dirs&plugin=pm&user='.$this->alice)).$attachment;

		$this->forumUrl = $this->grab('/USER_DIR=(\S+)/',
			$this->probe($I, 'act=dirs&plugin=forum&user='.$this->alice.'&create=1')).'note.txt';
		$I->writeAppFile($this->forumUrl, self::FORUM_BYTES);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();

		if ($this->upload !== null && file_exists($this->upload))
		{
			unlink($this->upload);
		}

		$I->deleteAppFile(self::PROBE_FILE);
		$I->dropPluginInstall(self::PLUGIN);
		$I->dropPluginProbe();
	}

	/**
	 * The route P5 fixed has to keep working, or every refusal below is
	 * satisfied by an attachment feature that serves nobody.
	 *
	 * The recipient is the interesting party: the file sits in the sender's own
	 * upload directory, so this is also what notices a deny rule that stops
	 * e107 reading the file back as well as stopping Apache serving it.
	 */
	public function theRecipientCanStillDownloadTheAttachmentThroughThePlugin(AcceptanceTester $I)
	{
		$I->loginToForum('pmstorebob');
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107_plugins/pm/pm.php?get.'.$this->pmId.'.0');

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::SECRET);
	}

	/**
	 * The sender too, because send_file() resolves the directory from the id
	 * encoded in the stored name rather than from the session.
	 */
	public function theSenderCanStillDownloadTheAttachmentThroughThePlugin(AcceptanceTester $I)
	{
		$I->loginToForum('pmstorealice');
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107_plugins/pm/pm.php?get.'.$this->pmId.'.0');

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::SECRET);
	}

	/**
	 * The regression the whole shape of the fix is arranged around.
	 *
	 * A public forum's post attachments are meant to be fetched directly: the
	 * forum links them from the post. They are stored by the same helpers the PM
	 * plugin uses, so a deny rule written inside e_file::getUserDir() or
	 * e_file::getUploaded() would take every one of them off every site.
	 *
	 * This says only that a forum attachment stays fetchable. It does not say
	 * that being fetchable is right for a userclass-restricted forum, whose post
	 * attachments carry no random component at all (upload_handler.php:256, from
	 * forum_post.php:1703) and have the same exposure the PM plugin had. That
	 * wants a serving route rather than a deny rule and is its own item.
	 */
	public function aForumPostAttachmentIsStillServedOverHttp(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->stopFollowingRedirects();

		$I->amOnPage('/'.$this->forumUrl);

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::FORUM_BYTES);
	}

	/**
	 * The same regression read off the disk rather than off the wire, so a
	 * failure names the file that should not be there instead of leaving a
	 * status code to be interpreted.
	 */
	public function theForumAttachmentDirectoryCarriesNoDenyRule(AcceptanceTester $I)
	{
		$dirs = $this->probe($I, 'act=dirs&plugin=forum&user='.$this->alice);

		$I->assertSame('0', $this->grab('/USER_HT=(\d)/', $dirs),
			'A forum attachment directory must carry no deny rule');
	}

	/**
	 * The other half of the same regression, one layer down.
	 *
	 * The two tests above go through e_file::getUserDir(), which only decides
	 * where a file goes. e_file::getUploaded() is what puts it there, and it is
	 * the other obvious wrong home for the rule: a guard written inside it would
	 * leave both of those green while every forum attachment on every site
	 * answered 403.
	 */
	public function theForumUploadHelperWritesNoDenyRule(AcceptanceTester $I)
	{
		$upload = $this->probe($I, 'act=upload&plugin=forum');

		$I->assertSame('0', $this->grab('/USER_HT=(\d)/', $upload),
			'e_file::getUploaded() must not protect the directory it stores into');
		$I->assertSame('0', $this->grab('/USER_IDX=(\d)/', $upload),
			'e_file::getUploaded() must not write an index.html either');
	}

	/**
	 * The guard files have to survive the plugin's own maintenance sweep.
	 *
	 * pm_conf.php builds a fresh e_file, calls get_files() on the attachment
	 * directory and unlinks everything no message names. Nothing but the default
	 * fileFilter in file_class.php keeps the rules this package writes off that
	 * list, and an administrator running the sweep once would otherwise
	 * unprotect the directory with no test noticing.
	 */
	public function theMaintenanceSweepDoesNotSeeTheGuardFiles(AcceptanceTester $I)
	{
		$this->probe($I, 'act=setup');

		$I->assertSame('1', $this->grab('/LEGACY_HT=(\d)/', $this->probe($I, 'act=legacy')),
			'The directory the sweep reads carries no deny rule, so this proves nothing');

		$orphans = $this->grab('/ORPHANS=(\S*)/', $this->probe($I, 'act=orphans'));
		$names = explode(',', $orphans);

		$I->assertNotContains('.htaccess', $names, 'The sweep must not see the deny rule');
		$I->assertNotContains('index.html', $names, 'The sweep must not see the index.html');
	}

	/**
	 * The defect. Nobody at all: no session, no cookie, nothing but the URL,
	 * which is what a party to the message can pass on and what a search engine
	 * can find in a referrer.
	 */
	public function theStoredAttachmentIsNotServedByTheWebServer(AcceptanceTester $I)
	{
		$this->seeFileIsReallyThere($I, $this->attachmentUrl);

		$I->resetAllCookies();
		$I->stopFollowingRedirects();

		$I->amOnPage('/'.$this->attachmentUrl);

		$I->dontSeeInSource(self::SECRET);
		$I->seeResponseCodeIs(403);
	}

	/**
	 * The same bytes on a site upgraded from a release that stored attachments
	 * beside the plugin. send_file() and del() both still reach into that
	 * directory, so covering only the media path would leave every upgraded
	 * site exactly where it started.
	 *
	 * What covers it is the plugin's install and upgrade hook, driven here as
	 * the plugin manager drives it. A send covers the sender's own directory
	 * and the root above it, and the legacy directory is under neither.
	 */
	public function theLegacyPluginAttachmentDirectoryIsNotServed(AcceptanceTester $I)
	{
		$this->seeFileIsReallyThere($I, $this->legacyUrl);

		$this->probe($I, 'act=setup');

		$I->resetAllCookies();
		$I->stopFollowingRedirects();

		$I->amOnPage('/'.$this->legacyUrl);

		$I->dontSeeInSource(self::LEGACY_SECRET);
		$I->seeResponseCodeIs(403);
	}

	/**
	 * The route an existing site really takes.
	 *
	 * Nobody sends anything here. The directories are put back in the state an
	 * upgrade leaves them in, attachments and all, and then the plugin is
	 * installed exactly as the plugin manager installs it. That alone has to
	 * cover what the site already holds, because covering a directory when the
	 * plugin next writes into it asks a member to send another attachment before
	 * the one they already sent is protected, and the sites holding exposed
	 * files are the ones whose members are not sending any.
	 *
	 * The send route the other tests here drive reaches the same rules, but only
	 * once somebody sends something. This one needs nobody.
	 */
	public function installingThePluginProtectsAttachmentsAlreadyOnDisk(AcceptanceTester $I)
	{
		$this->probe($I, 'act=reset');

		$legacy = $this->grab('/LEGACY_FILE=(\S+)/', $this->probe($I, 'act=legacy&create=1'));
		$stored = $this->grab('/USER_DIR=(\S+)/',
			$this->probe($I, 'act=dirs&plugin=pm&user='.$this->alice.'&create=1')).'stale.pdf';
		$I->writeAppFile($stored, "%PDF-1.4\n".self::SECRET."\n%%EOF\n");

		$bare = $this->probe($I, 'act=dirs&plugin=pm&user='.$this->alice);
		$I->assertSame('0', $this->grab('/ROOT_HT=(\d)/', $bare),
			'The reset must leave the attachment directories bare, or this proves nothing');

		$I->dropPluginInstall(self::PLUGIN);
		$I->havePluginInstalled(self::PLUGIN);

		$pm = $this->probe($I, 'act=dirs&plugin=pm&user='.$this->alice);

		$I->assertSame('1', $this->grab('/ROOT_HT=(\d)/', $pm), 'attachment root deny rule');
		$I->assertSame('1', $this->grab('/USER_HT=(\d)/', $pm), 'member directory deny rule');
		$I->assertSame('1', $this->grab('/LEGACY_HT=(\d)/', $this->probe($I, 'act=legacy')),
			'legacy directory deny rule');

		$this->seeFileIsReallyThere($I, $stored);
		$this->seeFileIsReallyThere($I, $legacy);

		$I->resetAllCookies();
		$I->stopFollowingRedirects();

		$I->amOnPage('/'.$stored);
		$I->dontSeeInSource(self::SECRET);
		$I->seeResponseCodeIs(403);

		$I->amOnPage('/'.$legacy);
		$I->dontSeeInSource(self::LEGACY_SECRET);
		$I->seeResponseCodeIs(403);
	}

	/**
	 * The guard files themselves, read off the disk.
	 *
	 * Not on its own worth anything: a .htaccess proves nothing about what a
	 * server does with it, which is what the fetches above are for. What this
	 * adds is where they appeared and where they did not. The probe's reset
	 * removed the directories before the fixture sent anything, so what is here
	 * was written by the send, and the send writes over the sender's own
	 * directory and the root above it alone. Bob's directory was there before
	 * it and is still bare after it: a walk of every member directory on the
	 * site is a glob and three stat calls per member on every send (#6160), and
	 * the deny rule at the root is what covers the members not sending.
	 */
	public function theGuardFilesAreWrittenWhenAnAttachmentIsStored(AcceptanceTester $I)
	{
		$pm = $this->probe($I, 'act=dirs&plugin=pm&user='.$this->alice);

		$I->assertSame('1', $this->grab('/ROOT_HT=(\d)/', $pm), 'attachment root deny rule');
		$I->assertSame('1', $this->grab('/ROOT_IDX=(\d)/', $pm), 'attachment root index.html');
		$I->assertSame('1', $this->grab('/USER_HT=(\d)/', $pm), 'member directory deny rule');
		$I->assertSame('1', $this->grab('/USER_IDX=(\d)/', $pm), 'member directory index.html');

		$bystander = $this->probe($I, 'act=dirs&plugin=pm&user='.$this->bob);

		$I->assertSame('1', $this->grab('/USER_EXISTS=(\d)/', $bystander),
			'The other member has no directory, so finding nothing in it proves nothing');
		$I->assertSame('0', $this->grab('/USER_HT=(\d)/', $bystander),
			'The send wrote into a member directory it was not storing anything in');
	}

	/**
	 * A deny rule on a parent answers 403 for a path holding nothing at all, and
	 * Apache runs the authorisation phase before it decides a file is missing. A
	 * refusal is therefore worth nothing until the bytes are known to be at the
	 * URL being refused.
	 *
	 * @param AcceptanceTester $I
	 * @param string $path docroot-relative path
	 * @return void
	 */
	private function seeFileIsReallyThere(AcceptanceTester $I, $path)
	{
		$I->assertSame('1',
			$this->grab('/STAT_EXISTS=(\d)/', $this->probe($I, 'act=stat&path='.urlencode($path))),
			'Nothing is at '.$path.', so refusing it proves nothing');
	}

	/**
	 * Send a PM carrying a real upload, through the real form.
	 *
	 * What the guard rules must not depend on is a message being sent at all,
	 * which is what installingThePluginProtectsAttachmentsAlreadyOnDisk is for.
	 *
	 * A PDF rather than a text file because e107 vets uploads against
	 * filetypes.xml, and the list the installer writes for members is
	 * zip,gz,jpg,jpeg,png,gif,webp,xml,pdf. An XML file would be refused for
	 * carrying "<?".
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function sendWithAttachment(AcceptanceTester $I)
	{
		$I->sendPostRequest('/e107_plugins/pm/pm.php', array(
			'postpm'     => '1',
			'numsent'    => '0',
			'pm_to'      => (string) $this->bob,
			'pm_subject' => self::SUBJECT,
			'pm_message' => 'see attached',
			'e-token'    => $this->formToken($I),
		), array(
			'file_userfile' => array(
				array(
					'name'     => 'secret.pdf',
					'type'     => 'application/pdf',
					'tmp_name' => $this->upload,
					'error'    => 0,
					'size'     => filesize($this->upload),
				),
			),
		));
	}

	/**
	 * The CSRF token the PM send form carries, read off that form.
	 *
	 * @param AcceptanceTester $I
	 * @return string
	 */
	private function formToken(AcceptanceTester $I)
	{
		$I->amOnPage('/e107_plugins/pm/pm.php?send');

		return $this->grab('/name=[\'"]e-token[\'"][^>]*value=[\'"]([^\'"]+)[\'"]/',
			$I->grabPageSource());
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
			throw new \RuntimeException('PM storage probe failed for "'.$query.'": '.trim(strip_tags($body)));
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
			throw new \RuntimeException('PM storage probe reported no '.$pattern.': '.trim(strip_tags($body)));
		}

		return $matches[1];
	}

	/**
	 * Where e107 keeps these files is e107's own answer, not one this test
	 * should be computing. e_CURRENT_PLUGIN is what getUserDir() reads to place
	 * a directory, and only a request to a plugin's own page defines it, so the
	 * probe is told which plugin it is standing in for.
	 *
	 * @return string
	 */
	private function probeSource()
	{
		$php = <<<'PHP'
<?php
// Fixture for PmAttachmentStorageCest. Written per test, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

$plugin = isset($_GET['plugin']) ? preg_replace('/[^\w-]/', '', $_GET['plugin']) : 'pm';

if(!defined('e_CURRENT_PLUGIN'))
{
	define('e_CURRENT_PLUGIN', $plugin);
}

/**
 * The path a browser would ask for, given a path the application works with.
 */
function e107_test_url($path)
{
	$path = str_replace('\\', '/', $path);
	$root = str_replace('\\', '/', e_ROOT);

	if($root !== '' && strpos($path, $root) === 0)
	{
		$path = substr($path, strlen($root));
	}

	return ltrim($path, './');
}

function e107_test_guards($label, $dir)
{
	$dir = rtrim($dir, '/').'/';

	return $label.'_DIR='.e107_test_url($dir)
		.' '.$label.'_EXISTS='.(is_dir($dir) ? 1 : 0)
		.' '.$label.'_HT='.(file_exists($dir.'.htaccess') ? 1 : 0)
		.' '.$label.'_IDX='.(file_exists($dir.'index.html') ? 1 : 0);
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

		if(is_dir($path))
		{
			e107_test_rmtree($path);
		}
		else
		{
			unlink($path);
		}
	}

	rmdir($dir);
}

$act = isset($_GET['act']) ? $_GET['act'] : '';
$db = e107::getDb();
$legacyDir = e_PLUGIN.'pm/attachments/';

switch($act)
{
	case 'reset':
		// Messages this fixture sends are not tracked by the Db module, so a
		// run that died part way through would leave them for the next one.
		$db->delete('private_msg');

		// Attachments are for admins on a stock install, and the member under
		// test is deliberately not one.
		e107::getPlugConfig('pm')->set('attach_class', e_UC_MEMBER)->save(false, true, false);

		// The list of what may be uploaded, where the application looks for it.
		// A site that has ever visited admin > File Types has one; a freshly
		// installed one does not, because install.php strips the site path from
		// SYSTEM_DIRECTORY (install.php:1891) before saveFileTypes() writes the
		// file, and set_max_size() then makes the maximum upload size zero and
		// every upload "exceeds allowable limits". The bytes are the installer's
		// own, verbatim.
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

		// Both directories go, guard files and all, so that whatever is found
		// in them later was put there by the plugin during this test.
		e107_test_rmtree(rtrim(e_MEDIA.'plugins/pm/attachments/', '/'));
		e107_test_rmtree(rtrim(e_MEDIA.'plugins/forum/attachments/', '/'));
		e107_test_rmtree(rtrim($legacyDir, '/'));

		echo "PROBE_OK reset\n";
		break;

	case 'legacy':
		// The shape an upgraded site is in: attachments beside the plugin,
		// where releases before the media directory put them.
		if(!empty($_GET['create']))
		{
			if(!is_dir($legacyDir) && !mkdir($legacyDir, 0755, true))
			{
				echo 'could not create '.$legacyDir."\n";
				exit;
			}

			$name = time().'_1_'.e_random::hex(16).'_legacy.txt';

			if(file_put_contents($legacyDir.$name, '{{LEGACY_SECRET}}') === false)
			{
				echo 'could not write '.$legacyDir.$name."\n";
				exit;
			}

			echo 'PROBE_OK LEGACY_FILE='.e107_test_url($legacyDir.$name).' ';
		}
		else
		{
			echo 'PROBE_OK ';
		}

		echo e107_test_guards('LEGACY', $legacyDir)."\n";
		break;

	case 'dirs':
		$user = (int) $_GET['user'];
		$create = !empty($_GET['create']);
		$userDir = e107::getFile()->getUserDir($user, $create, 'attachments');

		echo 'PROBE_OK '.e107_test_guards('ROOT', dirname($userDir))
			.' '.e107_test_guards('USER', $userDir)."\n";
		break;

	case 'setup':
		// The plugin manager's own install and upgrade route, which is what
		// covers a directory the plugin is not writing into.
		require_once(e_PLUGIN.'pm/pm_setup.php');

		$setup = new pm_setup();
		$setup->install_post();

		echo "PROBE_OK setup\n";
		break;

	case 'stat':
		$path = isset($_GET['path']) ? str_replace('..', '', $_GET['path']) : '';

		echo 'PROBE_OK STAT_EXISTS='.(($path !== '' && is_file(e_ROOT.ltrim($path, '/'))) ? 1 : 0)."\n";
		break;

	case 'orphans':
		// What the plugin's own maintenance sweep sees. pm_conf.php builds a
		// fresh e_file and unlinks every file get_files() returns that no message
		// names, so anything of ours on this list is deleted the first time an
		// administrator runs it.
		require_once(e_HANDLER.'file_class.php');

		$sweep = new e_file();
		$names = array();

		foreach($sweep->get_files($legacyDir) as $file)
		{
			$names[] = $file['fname'];
		}

		echo 'PROBE_OK ORPHANS='.implode(',', $names)."\n";
		break;

	case 'upload':
		// The helper that really stores an attachment, rather than the one that
		// decides where it goes. Nothing is uploaded: what is under test is the
		// directory getUploaded() leaves behind.
		$_FILES['file_userfile'] = array();
		e107::getFile()->getUploaded('attachments', false, array());

		echo 'PROBE_OK '.e107_test_guards('USER',
			e107::getFile()->getUserDir(USERID, false, 'attachments'))."\n";
		break;

	case 'lastpm':
		$row = $db->createQueryBuilder()
			->select('pm_id', 'pm_from', 'pm_attachments')->from('private_msg')
			->orderBy('pm_id', 'DESC')->setMaxResults(1)->fetchRow();

		if(empty($row))
		{
			echo "no messages were sent\n";
			exit;
		}

		if($row['pm_attachments'] === '')
		{
			echo "the message carries no attachment\n";
			exit;
		}

		echo "PROBE_OK PM_ID=".$row['pm_id']." PM_FROM=".$row['pm_from']
			." PM_ATTACH=".$row['pm_attachments']."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;

		return strtr($php, array('{{LEGACY_SECRET}}' => self::LEGACY_SECRET));
	}
}
