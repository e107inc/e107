<?php

/**
 * Mailout acts on a GET, and e107's CSRF guard does not police a GET.
 *
 * e_session::isStateChangingRequest() returns true only for POST, so attest()
 * returns early on every GET that carries no e-token at all. What stands between
 * an attacker's <img> tag and a state-changing GET is therefore whatever the
 * endpoint does for itself, and e107_admin/mailout.php did nothing for three of
 * them: the AJAX progress endpoint sends the next batch of queued mail,
 * action=sendnow releases a held mailshot to its whole list, action=send
 * throws the recipient list away and builds it again, and action=test opens an
 * outbound connection to the configured SMTP server and authenticates to it
 * with the stored credentials.
 *
 * The progress endpoint is reachable from a plain <img> tag because
 * e107_class.php falls back to isset($_REQUEST['ajax_used']) and $_REQUEST
 * carries the query string, so ?ajax_used=1 satisfies e_AJAX_REQUEST without a
 * header. Its response body is the percentage sendProgress() returns, and
 * sendProgress() calls e107MailManager::doEmailTask() before it computes that
 * number, so seeing the number is proof the batch ran.
 *
 * The e-token in a query string is e107's established marker for a
 * state-changing GET, which e107_admin/plugin.php, theme.php and language.php
 * already use: the endpoint tests that it is present and attest() decides
 * whether it is the right one. These cases assert both halves of that division
 * of labour, and the last four are the controls that ordinary admin navigation
 * still works.
 *
 * @see e107_handlers/session_handler.php  e_session::isStateChangingRequest()
 * @see e107_admin/mailout.php             mailout_tokenMissing()
 */
class MailoutCsrfCest
{
	const MENU = '/e107_admin/mailout.php';

	/** The mailshot the send and sendnow cases act on. */
	const ACTING_ID = 99001;

	/** A quiescent mailshot, so the progress endpoint reports a fixed percentage. */
	const PROGRESS_ID = 99002;

	/** (mail_sent_count + mail_fail_count) / mail_total_count of PROGRESS_ID, as sendProgress() renders it. */
	const PROGRESS_PERCENT = '37';

	const PROBE_EMAIL = 'csrf-probe@example.invalid';

	/** MAIL_STATUS_PENDING: what sendnowPage() writes over the whole list. */
	const STATUS_PENDING = 10;

	/** MAIL_STATUS_SAVED: where the fixture starts, and where it must stay. */
	const STATUS_SAVED = 20;

	/** A distinctive fragment of LAN_MAILOUT_REFUSED_TOKEN_MISSING. */
	const REFUSED = 'no security token';

	/** testPage()'s own report of the connection it opened, whatever the server answered. */
	const SMTP_ATTEMPTED = 'Connect failed using';

	/** A bulk mailer that is not SMTP, which the Mailout menu entry must survive. */
	const NON_SMTP_MAILER = 'php';

	/** An administrator holding 'W' and nothing else: what mailout.php's own gate asks for. */
	const DELEGATED_ADMIN = 'm84wadmin';

	/** What that administrator holds. */
	const DELEGATED_PERMS = 'W';

	/** Seeded onto the delegated administrator, as 0037_AdminRoutePermsCest seeds its own. */
	const PWCHANGE = 1262304000;

	/**
	 * A GET that the framework does police: attest() refuses any e-token it
	 * cannot validate, whatever the request method, and answers with this.
	 */
	const UNAUTHORIZED = 'Unauthorized access!';

	public function _before(AcceptanceTester $I)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin();

		$I->haveInDatabase('e107_mail_content', array(
			'mail_source_id'      => self::ACTING_ID,
			'mail_content_status' => self::STATUS_SAVED,
			'mail_total_count'    => 1,
			'mail_togo_count'     => 1,
			'mail_create_date'    => 1,
			'mail_creator'        => 1,
			'mail_create_app'     => 'core',
			'mail_title'          => 'CSRF probe mailshot',
			'mail_subject'        => 'CSRF probe mailshot',
			'mail_body'           => 'CSRF probe body',
			'mail_body_templated' => '',
			'mail_other'          => '',
		));

		$I->haveInDatabase('e107_mail_recipients', array(
			'mail_recipient_id'    => 1,
			'mail_recipient_email' => self::PROBE_EMAIL,
			'mail_recipient_name'  => 'CSRF probe',
			'mail_status'          => self::STATUS_SAVED,
			'mail_detail_id'       => self::ACTING_ID,
			'mail_send_date'       => 2147483647,
			'mail_target_info'     => '',
		));

		$I->haveInDatabase('e107_mail_content', array(
			'mail_source_id'      => self::PROGRESS_ID,
			'mail_content_status' => self::STATUS_SAVED,
			'mail_total_count'    => 100,
			'mail_togo_count'     => 63,
			'mail_sent_count'     => 37,
			'mail_create_date'    => 1,
			'mail_creator'        => 1,
			'mail_create_app'     => 'core',
			'mail_title'          => 'CSRF probe queue',
			'mail_subject'        => 'CSRF probe queue',
			'mail_body'           => 'CSRF probe body',
			'mail_body_templated' => '',
			'mail_other'          => '',
		));
	}

	/**
	 * sendProgress() hands e107MailManager::doEmailTask() the batch size from the
	 * mail_pause preference and lets it deliver, then writes
	 * e_LOG/send-mail-progress.txt. All of that ran off a bare query string.
	 */
	public function aTokenlessGetDoesNotRunTheMailQueue(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?ajax_used=1&mode=' . self::PROGRESS_ID);

		$I->dontSeeInSource(self::PROGRESS_PERCENT);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * sendnowPage() sets the mailshot and every one of its mail_recipients rows
	 * to MAIL_STATUS_PENDING, which is what releases a held mailshot to its whole
	 * list.
	 */
	public function aTokenlessGetDoesNotReleaseTheMailshotToItsList(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=main&action=sendnow&id=' . self::ACTING_ID);

		$I->seeInDatabase('e107_mail_content', array(
			'mail_source_id'      => self::ACTING_ID,
			'mail_content_status' => self::STATUS_SAVED,
		));
		$I->seeInDatabase('e107_mail_recipients', array(
			'mail_detail_id' => self::ACTING_ID,
			'mail_status'    => self::STATUS_SAVED,
		));
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * sendPage() deletes every mail_recipients row for the mailshot and rebuilds
	 * the list from the mail handlers, so a forged hit discards whoever was on it.
	 */
	public function aTokenlessGetDoesNotRegenerateTheRecipientList(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=main&action=send&id=' . self::ACTING_ID);

		$I->seeInDatabase('e107_mail_recipients', array(
			'mail_detail_id'       => self::ACTING_ID,
			'mail_recipient_email' => self::PROBE_EMAIL,
		));
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * Presence is all the endpoint tests; whether the value is the right one is
	 * attest()'s half. Both halves are needed, so assert the second one too.
	 */
	public function aGetCarryingATokenThatDoesNotValidateIsRefused(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=main&action=send&id=' . self::ACTING_ID . '&e-token=not-even-close');

		$I->seeInSource(self::UNAUTHORIZED);
		$I->seeInDatabase('e107_mail_recipients', array(
			'mail_detail_id'       => self::ACTING_ID,
			'mail_recipient_email' => self::PROBE_EMAIL,
		));
	}

	/**
	 * The control that matters most. A guard on a link the administrator reaches
	 * by ordinary navigation is worse than the hole it closes if the navigation
	 * stops working, so this follows whatever the mail list publishes for itself
	 * rather than a URL of the test's own.
	 */
	public function theMailListsOwnSendLinkStillRegeneratesTheRecipientList(AcceptanceTester $I)
	{
		$I->amOnPage($this->publishedLink($I, self::MENU . '?mode=main', 'send', self::ACTING_ID));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->dontSeeInDatabase('e107_mail_recipients', array(
			'mail_detail_id'       => self::ACTING_ID,
			'mail_recipient_email' => self::PROBE_EMAIL,
		));
	}

	/**
	 * The Send Now button lives on the confirmation page that action=send renders,
	 * so this walks the whole admin path rather than jumping into the middle of it.
	 */
	public function theConfirmationPagesOwnSendNowLinkStillReleasesTheMailshot(AcceptanceTester $I)
	{
		$I->amOnPage($this->sendNowLink($I));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->seeInDatabase('e107_mail_content', array(
			'mail_source_id'      => self::ACTING_ID,
			'mail_content_status' => self::STATUS_PENDING,
		));
	}

	/**
	 * The progress bar polls whatever e107::getForm()->progressBar() was given,
	 * with the mailshot id appended, so the queue keeps running for the
	 * administrator who started it.
	 */
	public function theProgressBarUrlTheAdminPageMintsStillRunsTheQueue(AcceptanceTester $I)
	{
		$I->amOnPage($this->sendNowLink($I));

		$source = $I->grabPageSource();
		$matches = array();

		if(!preg_match('#data-progress="([^"]*)"#', $source, $matches))
		{
			throw new \RuntimeException('The Send Now page published no progress-bar URL');
		}

		$poll = $this->toPath(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));

		$I->amOnPage($poll . (strpos($poll, '?') === false ? '?' : '&')
			. 'mode=' . self::PROGRESS_ID . '&ajax_used=1');

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->seeInSource(self::PROGRESS_PERCENT);
	}

	/**
	 * testPage() opens a TCP connection to the configured smtp_server and offers
	 * the stored smtp_username and smtp_password to it, so a forged hit makes the
	 * site talk to whatever host its preferences name. It carries no getperms('0')
	 * of its own either, unlike prefsPage() and maintPage() beside it.
	 */
	public function aTokenlessGetDoesNotOpenAnSmtpConnection(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=prefs&action=test');

		$I->dontSeeInSource(self::SMTP_ATTEMPTED);
		$I->seeInSource(self::REFUSED);
	}

	/**
	 * The only link in core that reaches testPage() is the Mailout menu entry, so
	 * this reads it off the rendered menu and follows it. It runs with the bulk
	 * mailer set to something other than SMTP, because testPage() reads the stored
	 * SMTP preferences whatever the site sends its bulk mail with: a menu entry
	 * that appeared only for one of the three mailers would leave the guarded page
	 * with no entry point at all on the other two. The preference goes back before
	 * anything is asserted, so a failure here leaves the site as it was.
	 */
	public function theMailoutMenusOwnTestLinkStillOpensAnSmtpConnection(AcceptanceTester $I)
	{
		$was = $this->haveBulkMailer($I, self::NON_SMTP_MAILER);

		$I->amOnPage(self::MENU . '?mode=prefs&action=prefs');

		$matches = array();
		preg_match('#href="([^"]*mailout\\.php\\?mode=prefs&(?:amp;)?action=test[^"]*)"#',
			$I->grabPageSource(), $matches);

		$this->haveBulkMailer($I, $was);

		$I->assertNotEmpty($matches, 'The Mailout menu published no Test SMTP Connection link');

		$I->amOnPage($this->toPath(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8')));

		$I->dontSeeInSource(self::REFUSED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
		$I->seeInSource(self::SMTP_ATTEMPTED);
	}

	/**
	 * mailout.php's only gate is getperms('W') at the top of the file, class
	 * mailout_admin declares neither $perm nor $access, and
	 * e_admin_dispatcher::checkAccess() consults nothing else. The '0' on the menu
	 * entry is read by renderMenu(), which decides whether the entry is drawn and
	 * lets the route through either way, so every administrator who could open
	 * Mailout at all could make the site connect and authenticate to the configured
	 * SMTP server. prefsPage(), maintPage() and saveMailPrefs() beside it have each
	 * asked getperms('0') for years.
	 */
	public function aDelegatedAdministratorIsRefusedTheSmtpConnectionTest(AcceptanceTester $I)
	{
		$this->loginAsDelegatedAdmin($I);

		$I->amOnPage(self::MENU . '?mode=prefs&action=test&e-token=' . $I->grabFreshAdminToken(self::MENU));

		$I->dontSeeInSource(self::SMTP_ATTEMPTED);
		$I->dontSeeInSource(self::UNAUTHORIZED);
	}

	/**
	 * The other half of the same gate: it asks for the permission its neighbours ask
	 * for, and refuses nobody they would let through.
	 */
	public function aMainAdministratorStillGetsTheSmtpConnectionTest(AcceptanceTester $I)
	{
		$I->amOnPage(self::MENU . '?mode=prefs&action=test&e-token=' . $I->grabFreshAdminToken(self::MENU));

		$I->seeInSource(self::SMTP_ATTEMPTED);
	}

	/**
	 * A page that only renders is not a CSRF surface, and gating one would refuse
	 * a bookmark and the browser's back button. They stay reachable bare.
	 */
	public function theMailoutPagesThatOnlyRenderStillOpenWithoutAToken(AcceptanceTester $I)
	{
		foreach(array('main', 'saved', 'pending', 'held', 'sent', 'recipients', 'prefs', 'maint') as $mode)
		{
			$I->amOnPage(self::MENU . '?mode=' . $mode);

			$I->dontSeeInSource(self::REFUSED);
			$I->dontSeeInSource(self::UNAUTHORIZED);
		}
	}

	/**
	 * Seed an administrator holding one delegated permission and sign in as them.
	 *
	 * Seeded on every call rather than memoised: Codeception shares one Cest
	 * instance across its test methods and removes every haveInDatabase() row after
	 * each of them, so a cached user id outlives the user it names.
	 *
	 * @param AcceptanceTester $I
	 * @return void
	 */
	private function loginAsDelegatedAdmin(AcceptanceTester $I)
	{
		$I->haveInDatabase('e107_user', array(
			'user_name'      => self::DELEGATED_ADMIN,
			'user_loginname' => self::DELEGATED_ADMIN,
			'user_email'     => self::DELEGATED_ADMIN . '@example.com',
			'user_password'  => md5(self::DELEGATED_ADMIN),
			'user_join'      => 1262304000,
			'user_class'     => '',
			'user_admin'     => 1,
			'user_perms'     => self::DELEGATED_PERMS,
			'user_pwchange'  => self::PWCHANGE,
			'user_xup'       => '',
			'user_prefs'     => '',
			'user_signature' => '',
			'user_realm'     => '',
		));

		$I->resetAllCookies();
		$I->loginAsAdmin(self::DELEGATED_ADMIN, self::DELEGATED_ADMIN);
		$I->dontSeeElement('input[name=authpass]');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $mailer value to leave the bulkmailer preference at
	 * @return string the value it held before
	 */
	private function haveBulkMailer(AcceptanceTester $I, $mailer)
	{
		$I->amOnPage(self::MENU . '?mode=prefs&action=prefs');

		$was = $I->grabValueFrom('#mailsettingsform [name=bulkmailer]');

		if($was !== $mailer)
		{
			$I->submitForm('#mailsettingsform', array('bulkmailer' => $mailer), 'updateprefs');
		}

		return $was;
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string path to the Send Now link, tokenised exactly as the confirmation page published it
	 */
	private function sendNowLink(AcceptanceTester $I)
	{
		$confirm = $this->publishedLink($I, self::MENU . '?mode=main', 'send', self::ACTING_ID);

		return $this->publishedLink($I, $confirm, 'sendnow', self::ACTING_ID);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $page page to read the link off
	 * @param string $action value of the action parameter to look for
	 * @param int $id value of the id parameter to look for
	 * @return string path to follow, tokenised exactly as the page published it
	 */
	private function publishedLink(AcceptanceTester $I, $page, $action, $id)
	{
		$I->amOnPage($page);

		$pattern = "#href='([^']*mailout\\.php\\?mode=main&(?:amp;)?action=" . preg_quote($action, '#')
			. "&(?:amp;)?id=" . (int) $id . "[^']*)'#";
		$matches = array();

		if(!preg_match($pattern, $I->grabPageSource(), $matches))
		{
			throw new \RuntimeException('Mailout published no ' . $action . ' link for mailshot ' . $id);
		}

		return $this->toPath(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
	}

	/**
	 * @param string $url absolute or relative
	 * @return string path and query only, so the browser module keeps its own base URL
	 */
	private function toPath($url)
	{
		$path = parse_url($url, PHP_URL_PATH);
		$query = parse_url($url, PHP_URL_QUERY);

		return $path . ($query === null ? '' : '?' . $query);
	}
}
