<?php

/**
 * A private message attachment is served to whoever asks for it.
 *
 * pm.php's `get` action hands the message id straight to
 * private_message::send_file(), which reads the row through pm_get() and never
 * asks who is calling. Its one test compares the user id encoded in the stored
 * filename against the row's pm_from, which asks whether the file belongs to
 * the message, not whether the caller does. For a genuine attachment the two
 * are equal by construction, so the comparison passes for every member alike.
 * show_pm() has asked the ownership question at pm.php:392 since it was
 * written; send_file() never has.
 *
 * Reported as GHSA-46vx-phhg-m6h9.
 *
 * A guard that reads the row is worth no more than the writer of that row, so
 * the writer is covered here too: post_pm() forwarded $_POST into add(), whose
 * bulk-send branch copied every pm_* request key into the insert, and a member
 * could author a row naming somebody else as its sender and carrying somebody
 * else's attachment.
 *
 * Attachments are fetched with redirects unfollowed, so the assertions are
 * about what the server answered rather than about where a client chose to go
 * next. The refusal is asserted on the bytes and on an empty body, never on a
 * rendered page: send_file() writes the file straight out and exits, so a page
 * is not among the things it can answer with.
 *
 * Members and front-end sign-in come from Helper\ForumFixture. Neither is
 * forum specific, and duplicating either here would let the two drift.
 */
class PmAttachmentCest
{
	const PROBE_FILE = 'e107_tests_pm_fixture_probe.php';
	const PLUGIN = 'pm';

	/** Written into the attachment and looked for byte for byte. */
	const SECRET = 'PM-ATTACHMENT-SECRET-BYTES';

	/** The message's own text, for the show_pm() half. */
	const BODY = 'PM-BODY-SECRET-WORDS';

	/** Its subject, which show_pm() also hands to the pm shortcode batch. */
	const SUBJECT = 'PM-SUBJECT-SECRET-WORDS';

	/** @var int */
	private $alice;

	/** @var int */
	private $bob;

	/** @var int */
	private $carol;

	/** @var int id of the message alice sent bob */
	private $pmId;

	/** @var string stored name of its attachment */
	private $attachment;

	public function _before(AcceptanceTester $I)
	{
		// Before anything is asked of the plugin's front end: pm.php opens with
		// e107::isInstalled('pm') and redirects while that is false, which would
		// turn every refusal below into a refusal about the wrong thing.
		$I->havePluginInstalled(self::PLUGIN);

		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$this->probe($I, 'act=reset');

		$this->alice = $I->haveForumMember('pmalice');
		$this->bob = $I->haveForumMember('pmbob');
		$this->carol = $I->haveForumMember('pmcarol');

		$body = $this->probe($I, 'act=pm&from='.$this->alice.'&to='.$this->bob
			.'&body='.urlencode(self::SECRET));

		$this->pmId = (int) $this->grab('/PM_ID=(\d+)/', $body);
		$this->attachment = $this->grab('/FNAME=(\S+)/', $body);

		$I->logoutFromForum();
	}

	public function _after(AcceptanceTester $I)
	{
		$I->startFollowingRedirects();
		$I->deleteAppFile(self::PROBE_FILE);
		$I->dropPluginInstall(self::PLUGIN);
		$I->dropPluginProbe();
	}

	/**
	 * The endpoint has to serve the file to somebody, or every refusal below is
	 * satisfied by an attachment route that never worked at all.
	 *
	 * The header is asserted with it because it is the one part of send_file()
	 * that reads the stored name apart from the guards: the download name is
	 * the fourth field of explode("_", $fname, 4).
	 */
	public function theSenderIsServedTheAttachment(AcceptanceTester $I)
	{
		$I->loginToForum('pmalice');
		$I->stopFollowingRedirects();

		$I->amOnPage($this->attachmentUrl($this->pmId));

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::SECRET);
		$I->seeHttpHeader('Content-Disposition', 'attachment; filename=secret.txt');
	}

	/**
	 * The other party to the message is the whole point of an attachment.
	 */
	public function theRecipientIsServedTheAttachment(AcceptanceTester $I)
	{
		$I->loginToForum('pmbob');
		$I->stopFollowingRedirects();

		$I->amOnPage($this->attachmentUrl($this->pmId));

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::SECRET);
		$I->seeHttpHeader('Content-Disposition', 'attachment; filename=secret.txt');
	}

	/**
	 * The defect. Carol is neither party to the message and holds nothing but
	 * an ordinary member session and a message id she can count up to.
	 */
	public function aMemberWhoIsNeitherPartyIsRefusedTheAttachment(AcceptanceTester $I)
	{
		$I->loginToForum('pmcarol');
		$I->stopFollowingRedirects();

		$I->amOnPage($this->attachmentUrl($this->pmId));

		$I->dontSeeInSource(self::SECRET);
		$I->assertSame('', $I->grabPageSource(), 'Refused attachment must serve no body');
		$I->seeResponseCodeIs(200);
	}

	/**
	 * Knowing the stored name is not a way in either. Carol owns this message,
	 * so ownership alone lets it through and only the filename comparison
	 * stands between her and alice's file. That comparison stays for exactly
	 * this reason, and this is what would notice it going away.
	 */
	public function aMemberCannotNameAnotherMembersAttachmentInTheirOwnMessage(AcceptanceTester $I)
	{
		$body = $this->probe($I, 'act=pm&from='.$this->carol.'&to='.$this->carol
			.'&fname='.urlencode($this->attachment));
		$forged = (int) $this->grab('/PM_ID=(\d+)/', $body);

		$I->loginToForum('pmcarol');
		$I->stopFollowingRedirects();

		$I->amOnPage($this->attachmentUrl($forged));

		$I->dontSeeInSource(self::SECRET);
		$I->assertSame('', $I->grabPageSource(), 'Refused attachment must serve no body');
		$I->seeResponseCodeIs(200);
	}

	/**
	 * The other half of that forgery, and the half the filename comparison
	 * cannot see. Naming alice as the sender of a message carol wrote makes
	 * that comparison agree with itself, so all that is left is who gets to say
	 * who sent a message. post_pm() sends what the browser posted, so carol
	 * posts pm_from and pm_attachments alongside the ordinary fields.
	 *
	 * The message really is sent, which is what the id assertion is for: a
	 * refusal earlier in post_pm() would leave nothing to fetch and the empty
	 * body would then prove nothing at all.
	 *
	 * The body goes in as pm_text rather than as the form's pm_message for the
	 * same reason an attacker would put it there: pm_message is no column of
	 * private_msg, and the branch this exercises copied it into the insert as
	 * one, which failed the whole statement. Every key posted here is a column,
	 * so nothing stands between the request and the row but the fix.
	 */
	public function aMemberCannotForgeTheSenderOfTheMessageTheySend(AcceptanceTester $I)
	{
		$I->loginToForum('pmcarol');

		$I->sendPostRequest('/e107_plugins/pm/pm.php', array(
			'postpm'         => '1',
			'numsent'        => '0',
			'pm_to'          => 'pmcarol',
			'pm_subject'     => 'forged',
			'pm_text'        => 'forged',
			'pm_from'        => (string) $this->alice,
			'pm_attachments' => $this->attachment,
			'e-token'        => $this->formToken($I),
		));

		$body = $this->probe($I, 'act=lastpm');
		$forged = (int) $this->grab('/PM_ID=(\d+)/', $body);

		$I->assertGreaterThan($this->pmId, $forged, 'Carol must have sent a message');

		$I->stopFollowingRedirects();
		$I->amOnPage($this->attachmentUrl($forged));

		$I->dontSeeInSource(self::SECRET);
		$I->assertSame('', $I->grabPageSource(), 'Refused attachment must serve no body');
		$I->seeResponseCodeIs(200);

		$I->assertSame((string) $this->carol, $this->grab('/PM_FROM=(\d+)/', $body),
			'The sender of record must be the member who sent it');
	}

	/**
	 * pm_to is a varchar, and add() writes a user class name into the outbox
	 * copy of a class send, so a row whose pm_to reads "12 Newsletter" is an
	 * ordinary row. isParticipant() compares strings for that reason: a loose
	 * comparison reads that pm_to as the number 12 on PHP below 8 and hands the
	 * sender's attachment to member 12.
	 *
	 * This case cannot fail on the harness, whose PHP 8 compares that pair as
	 * strings already. It is here for release/v2.3.x, whose CI runs 5.6, and
	 * for the next reader who assumes == is safe. Alice is the positive
	 * control: the sender of a class send matches her own outbox copy on
	 * pm_from, and comparing strings must not take that away.
	 */
	public function aMemberIsRefusedAnOutboxCopyAddressedToAClassNamedAfterTheirId(AcceptanceTester $I)
	{
		$body = $this->probe($I, 'act=pm&from='.$this->alice
			.'&to_name='.urlencode($this->carol.' Newsletter')
			.'&fname='.urlencode($this->attachment));
		$classRow = (int) $this->grab('/PM_ID=(\d+)/', $body);

		$I->loginToForum('pmcarol');
		$I->stopFollowingRedirects();

		$I->amOnPage($this->attachmentUrl($classRow));

		$I->dontSeeInSource(self::SECRET);
		$I->assertSame('', $I->grabPageSource(), 'Refused attachment must serve no body');
		$I->seeResponseCodeIs(200);

		$I->amOnPage('/e107_plugins/pm/pm.php?show.'.$classRow);

		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource(self::BODY);
		$I->dontSeeInSource(self::SUBJECT);

		$I->startFollowingRedirects();
		$I->logoutFromForum();
		$I->loginToForum('pmalice');
		$I->stopFollowingRedirects();

		$I->amOnPage($this->attachmentUrl($classRow));

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::SECRET);
	}

	/**
	 * send_file() now asks the question show_pm() has always asked, through the
	 * one routine. These two pin what that routine has to keep answering, so
	 * moving the check out of show_pm() cannot quietly change what show_pm()
	 * does. The message itself is the only thing show_pm() produces, so its
	 * text is the side effect to read back.
	 */
	public function theMessageIsShownToItsRecipient(AcceptanceTester $I)
	{
		$I->loginToForum('pmbob');
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107_plugins/pm/pm.php?show.'.$this->pmId);

		$I->seeResponseCodeIs(200);
		$I->seeInSource(self::BODY);
	}

	/**
	 * The subject is read back as well as the body, because show_pm() used to
	 * hand the whole row to the pm shortcode batch before deciding and that
	 * batch is a registry singleton the rest of the request shares.
	 */
	public function theMessageIsWithheldFromAMemberWhoIsNeitherParty(AcceptanceTester $I)
	{
		$I->loginToForum('pmcarol');
		$I->stopFollowingRedirects();

		$I->amOnPage('/e107_plugins/pm/pm.php?show.'.$this->pmId);

		$I->seeResponseCodeIs(200);
		$I->dontSeeInSource(self::BODY);
		$I->dontSeeInSource(self::SUBJECT);
	}

	/**
	 * @param int $pmId
	 * @param int $index attachment number within the message
	 * @return string
	 */
	private function attachmentUrl($pmId, $index = 0)
	{
		return '/e107_plugins/pm/pm.php?get.'.$pmId.'.'.$index;
	}

	/**
	 * The CSRF token the PM send form carries, read off that form.
	 *
	 * class2.php answers an authenticated POST without one with 403 and the
	 * words "Unauthorized access!", which would make the forged send fail for a
	 * reason that has nothing to do with who a message says it came from.
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
			throw new \RuntimeException('PM fixture probe failed for "'.$query.'": '.trim(strip_tags($body)));
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
			throw new \RuntimeException('PM fixture probe reported no '.$pattern.': '.trim(strip_tags($body)));
		}

		return $matches[1];
	}

	/**
	 * The message and its attachment are built through the application, because
	 * where send_file() looks for the file is e107's own answer and not a path
	 * this test should be guessing at. e_CURRENT_PLUGIN is what getUserDir()
	 * reads to find it, and only a request to a plugin's own page defines it,
	 * so a probe sitting in the docroot has to say which plugin it is standing
	 * in for.
	 *
	 * @return string
	 */
	private function probeSource()
	{
		$php = <<<'PHP'
<?php
// Fixture for PmAttachmentCest. Written per test, removed in _after().
$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
{{E107_TEST_PROBE_GUARD}}
header('Content-Type: text/plain');

if(!defined('e_CURRENT_PLUGIN'))
{
	define('e_CURRENT_PLUGIN', 'pm');
}

$act = isset($_GET['act']) ? $_GET['act'] : '';
$db = e107::getDb();

switch($act)
{
	case 'reset':
		// Messages this fixture wrote are not tracked by the Db module, so a
		// run that died part way through would leave them for the next one.
		$db->delete('private_msg');
		echo "PROBE_OK reset\n";
		break;

	case 'pm':
		$from = (int) $_GET['from'];
		// to_name writes pm_to verbatim, which is the shape add() gives the
		// outbox copy of a class or multi-recipient send.
		$to = isset($_GET['to_name']) ? $_GET['to_name'] : (string) (int) $_GET['to'];
		$fname = isset($_GET['fname']) ? basename($_GET['fname']) : '';

		if($fname === '')
		{
			// The shape upload_handler.php gives an attachment:
			// time()_USERID_<random>_<original name>
			$fname = time().'_'.$from.'_'.e_random::hex(16).'_secret.txt';
			$dir = e107::getFile()->getUserDir($from, true, 'attachments');

			if(file_put_contents($dir.$fname, $_GET['body']) === false)
			{
				echo 'could not write '.$dir.$fname."\n";
				exit;
			}
		}

		$db->insert('private_msg', array(
			'pm_from'        => $from,
			'pm_to'          => $to,
			'pm_sent'        => time(),
			'pm_read'        => 0,
			'pm_subject'     => '{{SUBJECT}}',
			'pm_text'        => '{{BODY}}',
			'pm_sent_del'    => 0,
			'pm_read_del'    => 0,
			'pm_attachments' => $fname,
			'pm_option'      => '',
			'pm_size'        => 0,
		));

		$id = (int) $db->lastInsertId();

		if(!$id)
		{
			echo "insert failed\n";
			exit;
		}

		echo "PROBE_OK PM_ID=".$id." FNAME=".$fname."\n";
		break;

	case 'lastpm':
		// Reads back what the application itself wrote, so a test can ask who a
		// message says it came from without trusting the page that sent it.
		$row = array();

		if($db->select('private_msg', 'pm_id, pm_from, pm_attachments', 'ORDER BY pm_id DESC LIMIT 1', true))
		{
			$row = $db->fetch();
		}

		if(empty($row))
		{
			echo "no messages\n";
			exit;
		}

		echo "PROBE_OK PM_ID=".$row['pm_id']." PM_FROM=".$row['pm_from']
			." PM_ATTACH=".$row['pm_attachments']."\n";
		break;

	default:
		echo "unknown action\n";
}
PHP;

		return strtr($php, array('{{SUBJECT}}' => self::SUBJECT, '{{BODY}}' => self::BODY));
	}
}
