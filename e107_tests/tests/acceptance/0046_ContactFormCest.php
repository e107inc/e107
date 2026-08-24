<?php

/**
 * P6 item 3. contact.php has four independent defects, and each of them is
 * asserted on its own here, because a fix for one of them would otherwise look
 * like a fix for all four.
 *
 * (a) contact.php:196-198 resolves the recipient with
 *     where('user_id', intval($_POST['contact_person'])) and nothing else. The
 *     selector that built the list applied the sitecontacts userclass and
 *     user_ban = 0 (contact_shortcodes.php:44-60), so any user id at all can be
 *     named as the recipient of a message the site sends on the caller's behalf.
 * (b) processFormSubmit() verifies no CSRF token of its own. The core check in
 *     class2.php exempts a request that carries no cookie, by design, so a
 *     cookieless POST reaches the mailer with nothing standing in front of it.
 * (c) The CAPTCHA gate at contact.php:135 reads
 *     isset($_POST['rand_num']) && $sec_img->invalidCode(...), so leaving
 *     rand_num out of the POST skips the check rather than failing it.
 * (d) processFormSubmit() is called at contact.php:53, above the
 *     check_class($active) gate at :63. A visitor outside the permitted class
 *     is shown no form and sends the mail anyway.
 *
 * Outbound mail is captured with e107's own dry run transport: the
 * mail_log_options preference is set to "1,1" for the duration of the Cest, so
 * every send is written to e107_system/<site path>/logs/mailoutlog.log with its
 * recipient and body, and nothing leaves the container.
 */
class ContactFormCest
{
	const PROBE_FILE = 'e107_tests_p6_contact_probe.php';

	/** CONTACT PEOPLE, one of the classes the installer ships. */
	const CONTACT_CLASS = 2;

	/** @var array user_id => email, of the two users the selector offers */
	private $listed = array();

	/** @var int a user who is in no class at all */
	private $unlistedId;

	/** @var string */
	private $unlistedEmail;

	/** @var int a user in the contact class whom the selector excludes for being banned */
	private $bannedId;

	/** @var string */
	private $bannedEmail;

	public function _before(AcceptanceTester $I)
	{
		$suffix = uniqid('', false);

		// Codeception keeps one Cest instance for the whole class, and the Db
		// module deletes the previous test's rows, so this has to start empty
		// or it accumulates ids that no longer exist.
		$this->listed = array();

		foreach(array('one', 'two') as $n)
		{
			$email = 'p6contact-'.$n.'-'.$suffix.'@example.com';
			$id = $this->seedUser($I, 'p6c'.$n.$suffix, $email, (string) self::CONTACT_CLASS, 0);
			$this->listed[$id] = $email;
		}

		$this->unlistedEmail = 'p6contact-outsider-'.$suffix.'@example.com';
		$this->unlistedId = $this->seedUser($I, 'p6cout'.$suffix, $this->unlistedEmail, '', 0);

		$this->bannedEmail = 'p6contact-banned-'.$suffix.'@example.com';
		$this->bannedId = $this->seedUser($I, 'p6cban'.$suffix, $this->bannedEmail,
			(string) self::CONTACT_CLASS, 1);

		$I->writeAppFile(self::PROBE_FILE, $this->probeSource());
		$I->amOnPage('/'.self::PROBE_FILE.'?act=setup');
		$I->seeInSource('PROBE_OK');
	}

	public function _after(AcceptanceTester $I)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?act=teardown');
		$I->deleteAppFile(self::PROBE_FILE);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $name
	 * @param string $email
	 * @param string $class
	 * @param int $ban
	 * @return int
	 */
	private function seedUser(AcceptanceTester $I, $name, $email, $class, $ban)
	{
		return $I->haveInDatabase('e107_user', array(
			'user_name'      => $name,
			'user_loginname' => $name,
			'user_email'     => $email,
			'user_password'  => '',
			'user_join'      => 1600000000,
			'user_ban'       => $ban,
			'user_class'     => $class,
			'user_signature' => '',
			'user_prefs'     => '',
			'user_perms'     => '',
			'user_realm'     => '',
		));
	}

	/**
	 * @param AcceptanceTester $I
	 * @param string $query
	 * @return string
	 */
	private function probe(AcceptanceTester $I, $query)
	{
		$I->amOnPage('/'.self::PROBE_FILE.'?'.$query);

		return $I->grabPageSource();
	}

	/**
	 * A CAPTCHA answer that the application will accept.
	 *
	 * The answer is sealed inside the token, so only the site can read it back;
	 * the probe runs in the site and mints a matching pair rather than reading
	 * an image. Without this there is no way to send a request that is
	 * legitimate in every respect except the one under test, and no way to tell
	 * a fix from a form that no longer works.
	 *
	 * @param AcceptanceTester $I
	 * @return array rand_num and code_verify
	 */
	private function captcha(AcceptanceTester $I)
	{
		$out = $this->probe($I, 'act=captcha');

		preg_match('/RAND=(\S+)/', $out, $rand);
		preg_match('/CODE=(\S+)/', $out, $code);

		$I->assertNotEmpty($rand, 'the fixture must be able to mint a CAPTCHA token');
		$I->assertNotEmpty($code, 'the fixture must be able to mint a CAPTCHA solution');

		return array('rand_num' => $rand[1], 'code_verify' => $code[1]);
	}

	/**
	 * A submission that is valid in every respect. Individual tests take pieces
	 * away from it.
	 *
	 * @return array
	 */
	private function submission()
	{
		return array(
			'author_name'     => 'P6 Contact Tester',
			'email_send'      => 'p6sender@example.com',
			'subject'         => 'P6 contact subject',
			'body'            => 'This body is comfortably longer than the fifteen characters the form insists on.',
			'send-contactus'  => 'Send',
		);
	}

	/**
	 * Open the contact page the way a browser does, and come back with the
	 * session cookie and the CSRF token the document was given.
	 *
	 * @param AcceptanceTester $I
	 * @return string token value
	 */
	private function openFormAndGrabToken(AcceptanceTester $I)
	{
		$I->amOnPage('/contact.php');

		$matched = preg_match('/name="e-token" value="([^"]+)"/', $I->grabPageSource(), $m);
		$I->assertSame(1, $matched, 'the contact page must publish a CSRF token to its forms');

		return $m[1];
	}

	/**
	 * The CAPTCHA pair a rendered document actually carries.
	 *
	 * A test has to be able to answer the CAPTCHA the visitor was given rather
	 * than mint a fresh one, because that is the only way to measure the fields
	 * the template produced. The answer is not in the document any more, so it
	 * is read back through the probe, which runs inside the site and is the only
	 * party holding the key. That the probe is needed at all is the point: this
	 * used to be a base64 decode any visitor could do.
	 *
	 * @param AcceptanceTester $I
	 * @param string $source rendered markup
	 * @return array rand_num and code_verify
	 */
	private function captchaFromSource(AcceptanceTester $I, $source)
	{
		$matched = preg_match('/name=[\'"]rand_num[\'"] value=[\'"]([^\'"]+)[\'"]/', $source, $m);
		$I->assertSame(1, $matched, 'the rendered form must carry a rand_num field');

		$I->assertCount(5, explode('.', $m[1]), 'rand_num must be a compact JWE');

		$out = $this->probe($I, 'act=solve&t='.urlencode($m[1]));
		preg_match('/CODE=(\S+)/', $out, $code);
		$I->assertNotEmpty($code, 'the site must be able to open the challenge it just issued');

		$I->assertStringNotContainsString($code[1], $source,
			'the answer must not be anywhere in the document the visitor was served');

		return array('rand_num' => $m[1], 'code_verify' => $code[1]);
	}

	/**
	 * @param AcceptanceTester $I
	 * @return string mail log contents
	 */
	private function mailLog(AcceptanceTester $I)
	{
		return $this->probe($I, 'act=maillog');
	}

	/**
	 * The recorded message bodies alone, without the headers the rest of the log carries.
	 *
	 * @param AcceptanceTester $I
	 * @return array
	 */
	private function mailBodies(AcceptanceTester $I)
	{
		preg_match_all("/^Body: (.*?)^-{20,}/ms", $this->mailLog($I), $m);

		return $m[1];
	}

	/**
	 * (a) The recipient is whoever the caller names.
	 */
	public function theRecipientMustBeOneTheSelectorWouldHaveOffered(AcceptanceTester $I)
	{
		$I->wantTo('refuse a contact message addressed to a user the selector never offered');

		$this->probe($I, 'act=clearmaillog');

		foreach(array($this->unlistedId => $this->unlistedEmail, $this->bannedId => $this->bannedEmail) as $id => $email)
		{
			$token = $this->openFormAndGrabToken($I);
			$post = array_merge($this->submission(), $this->captcha($I), array(
				'e-token'        => $token,
				'contact_person' => $id,
			));
			$I->sendPostRequest('/contact.php', $post);
		}

		$log = $this->mailLog($I);

		$I->assertStringNotContainsString($this->unlistedEmail, $log,
			'a user outside the sitecontacts class must not be reachable through contact_person');
		$I->assertStringNotContainsString($this->bannedEmail, $log,
			'a banned user must not be reachable through contact_person');
	}

	/**
	 * Positive control for (a). A refusal that simply stopped delivering to
	 * anybody would satisfy the test above.
	 */
	public function aMessageToAListedContactIsStillDelivered(AcceptanceTester $I)
	{
		$I->wantTo('keep delivering a contact message to a listed contact');

		$this->probe($I, 'act=clearmaillog');

		$ids = array_keys($this->listed);
		$id = $ids[0];

		$token = $this->openFormAndGrabToken($I);
		$post = array_merge($this->submission(), $this->captcha($I), array(
			'e-token'        => $token,
			'contact_person' => $id,
		));
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringContainsString($this->listed[$id], $this->mailLog($I),
			'a message to a listed contact must still be delivered');
	}

	/**
	 * Second control for (a), on the client half: the list the visitor is shown
	 * is the one the predicates produce.
	 */
	public function theSelectorOffersOnlyListedContacts(AcceptanceTester $I)
	{
		$I->wantTo('keep the contact selector offering exactly the listed contacts');

		$I->amOnPage('/contact.php');

		foreach(array_keys($this->listed) as $id)
		{
			$I->seeInSource("<option value='".$id."'>");
		}

		$I->dontSeeInSource("<option value='".$this->unlistedId."'>");
		$I->dontSeeInSource("<option value='".$this->bannedId."'>");
	}

	/**
	 * (b) No token of its own, and the core check exempts a cookieless request.
	 */
	public function aCookielessTokenlessPostDoesNotSendMail(AcceptanceTester $I)
	{
		$I->wantTo('refuse a contact submission that presents no CSRF token at all');

		$captcha = $this->captcha($I);
		$this->probe($I, 'act=clearmaillog');

		$I->resetAllCookies();
		$I->sendPostRequest('/contact.php', array_merge($this->submission(), $captcha));

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a cookieless, tokenless POST must not reach the mailer');
	}

	/**
	 * (b) again, in the modes the test above cannot reach.
	 *
	 * csrf_enforce decides what proof a write has to show, and the harness is
	 * served over plain HTTP, where a mode that reads no token degrades to one
	 * that does. So the request says it arrived over TLS, the way a site behind
	 * a terminating proxy does, and mode 4 - the mode a site with no stored
	 * preference runs - is then exercised as itself.
	 */
	public function aCookielessTokenlessPostDoesNotSendMailInAnyCsrfMode(AcceptanceTester $I)
	{
		$I->wantTo('refuse a cookieless contact submission whatever CSRF mode the site is in');

		// 0 off, 2 token enforced, 4 same-site, which is CSRF_CHECK_RECOMMENDED.
		foreach(array(0, 2, 4) as $mode)
		{
			$this->probe($I, 'act=csrf&m='.$mode);

			$captcha = $this->captcha($I);
			$this->probe($I, 'act=clearmaillog');

			$I->resetAllCookies();
			$I->haveHttpHeader('X-Forwarded-Proto', 'https');
			$I->sendPostRequest('/contact.php', array_merge($this->submission(), $captcha));
			$I->deleteHeader('X-Forwarded-Proto');

			$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
				'a cookieless, tokenless POST must not reach the mailer in csrf_enforce mode '.$mode);
		}

		$this->probe($I, 'act=csrf&m=default');
	}

	/**
	 * Positive control for the mode-independent half: in a browser-only mode
	 * there is no token to present, so a browser that vouches for the request
	 * with Sec-Fetch-Site must still be able to send a message.
	 */
	public function aVouchedSubmissionIsStillDeliveredInABrowserOnlyMode(AcceptanceTester $I)
	{
		$I->wantTo('keep a real browser sending contact mail in a browser-only CSRF mode');

		$this->probe($I, 'act=csrf&m=4');
		$this->probe($I, 'act=clearmaillog');

		$I->haveHttpHeader('X-Forwarded-Proto', 'https');
		$I->amOnPage('/contact.php');

		$post = array_merge($this->submission(), $this->captchaFromSource($I, $I->grabPageSource()));

		$I->haveHttpHeader('Sec-Fetch-Site', 'same-origin');
		$I->sendPostRequest('/contact.php', $post);
		$I->deleteHeader('Sec-Fetch-Site');
		$I->deleteHeader('X-Forwarded-Proto');

		$log = $this->mailLog($I);
		$this->probe($I, 'act=csrf&m=default');

		$I->assertStringContainsString('Mail-ID=', $log,
			'a submission the browser vouched for must still be delivered');
	}

	/**
	 * The CAPTCHA is required of every submission, so it cannot be left to the
	 * template: the bundled contact menu renders a template that has none, and
	 * so does every theme that ships its own contact_template.php.
	 */
	public function aFormFromATemplateWithNoCaptchaStillCarriesOne(AcceptanceTester $I)
	{
		$I->wantTo('put the CAPTCHA into a contact form whose template leaves it out');

		$menu = $this->probe($I, 'act=menu');

		$I->assertNotFalse(strpos($menu, "name='rand_num'"),
			'the contact menu must carry the CAPTCHA token its handler requires');
		$I->assertNotFalse(strpos($menu, "name='code_verify'"),
			'the contact menu must carry the CAPTCHA answer field its handler requires');
		$I->assertLessThan(strripos($menu, '</form>'), strpos($menu, "name='code_verify'"),
			'the CAPTCHA fields must be inside the form, or they are never submitted');
	}

	/**
	 * A plugin may answer the render seams with markup of its own, and core
	 * cannot tell what that markup is. Asking whether the form already carries
	 * a CAPTCHA by looking for core's own field name therefore always said no,
	 * and put a second copy of the plugin's widget on the page.
	 */
	public function anOverriddenCaptchaIsNotRenderedTwice(AcceptanceTester $I)
	{
		$I->wantTo('leave a form alone when a plugin has already put its CAPTCHA on it');

		$out = $this->probe($I, 'act=overridden');

		preg_match('/WIDGETS=(\d+)/', $out, $m);

		$I->assertSame('2', isset($m[1]) ? $m[1] : $out,
			'the template asks for the image and the input, and nothing may append a third and a fourth');
	}

	/**
	 * The contact menu ships a template with no CAPTCHA in it, so it depends on
	 * the block being appended. A page that renders the form first must not
	 * spend that on the form and leave the menu unsubmittable.
	 */
	public function theMenuStillCarriesACaptchaAfterTheFormHasRendered(AcceptanceTester $I)
	{
		$I->wantTo('give every form on the page its own CAPTCHA');

		$menu = $this->probe($I, 'act=formthenmenu');

		$I->assertNotFalse(strpos($menu, "name='code_verify'"),
			'the contact menu must carry the CAPTCHA answer field its handler requires');
		$I->assertNotFalse(strpos($menu, "name='rand_num'"),
			'the contact menu must carry the CAPTCHA token its handler requires');
	}

	/**
	 * The block appended to one form must not count as the next form's CAPTCHA.
	 * Two contact menus on one page are two forms, and each has to be
	 * submittable on its own.
	 */
	public function eachAppendedCaptchaIsCountedOnlyForItsOwnForm(AcceptanceTester $I)
	{
		$I->wantTo('append a CAPTCHA to every form on the page that needs one');

		$out = $this->probe($I, 'act=menutwice');

		preg_match('/TOKENS=(\d+)/', $out, $m);

		$I->assertSame('2', isset($m[1]) ? $m[1] : $out,
			'two contact menus are two forms and both need their own CAPTCHA');
	}

	/**
	 * The document a visitor is served has to be enough on its own: every other
	 * test here mints its CAPTCHA out of the application, so none of them would
	 * notice a form that stopped rendering one.
	 */
	public function aSubmissionAnsweringTheRenderedCaptchaIsDelivered(AcceptanceTester $I)
	{
		$I->wantTo('keep the contact form a visitor is served submittable as rendered');

		$this->probe($I, 'act=clearmaillog');

		$I->amOnPage('/contact.php');
		$source = $I->grabPageSource();

		$matched = preg_match('/name="e-token" value="([^"]+)"/', $source, $m);
		$I->assertSame(1, $matched, 'the contact page must publish a CSRF token to its forms');

		$post = array_merge($this->submission(), $this->captchaFromSource($I, $source),
			array('e-token' => $m[1]));
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringContainsString('Mail-ID=', $this->mailLog($I),
			'a submission that answers the form as rendered must be delivered');
	}

	/**
	 * (c) The gate is skipped by leaving the field out.
	 */
	public function omittingTheCaptchaFieldDoesNotSkipTheCaptcha(AcceptanceTester $I)
	{
		$I->wantTo('refuse a contact submission that simply omits the CAPTCHA field');

		$this->probe($I, 'act=clearmaillog');

		$token = $this->openFormAndGrabToken($I);
		$I->sendPostRequest('/contact.php',
			array_merge($this->submission(), array('e-token' => $token)));

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a submission with no rand_num must not reach the mailer');
	}

	/**
	 * Control for (c): the gate itself has always worked when the field is
	 * present, so if this ever stops holding the test above is measuring
	 * nothing.
	 */
	public function aWrongCaptchaAnswerIsStillRefused(AcceptanceTester $I)
	{
		$I->wantTo('keep refusing a wrong CAPTCHA answer');

		$captcha = $this->captcha($I);
		$captcha['code_verify'] = 'definitely-not-the-code';

		$this->probe($I, 'act=clearmaillog');

		$token = $this->openFormAndGrabToken($I);
		$I->sendPostRequest('/contact.php',
			array_merge($this->submission(), $captcha, array('e-token' => $token)));

		$I->assertStringNotContainsString('Mail-ID=', $this->mailLog($I),
			'a wrong CAPTCHA answer must not reach the mailer');
	}

	/**
	 * (d) The handler runs above the gate that decides who may use the form.
	 */
	public function aVisitorOutsideThePermittedClassCannotSendMail(AcceptanceTester $I)
	{
		$I->wantTo('refuse a contact submission from a visitor who is not shown the form');

		$this->probe($I, 'act=visibility&v=253'); // members only
		$this->probe($I, 'act=clearmaillog');

		// Everything a member's submission would carry, from a guest.
		$token = $this->openFormAndGrabToken($I);
		$post = array_merge($this->submission(), $this->captcha($I), array('e-token' => $token));
		$I->sendPostRequest('/contact.php', $post);

		$log = $this->mailLog($I);
		$this->probe($I, 'act=visibility&v=0');

		$I->assertStringNotContainsString('Mail-ID=', $log,
			'a guest must not send mail through a members-only contact form');
	}

	/**
	 * Positive control for (b), (c) and (d) together: a guest submission that is
	 * correct in every respect must still be delivered.
	 */
	public function aCompleteGuestSubmissionIsStillDelivered(AcceptanceTester $I)
	{
		$I->wantTo('keep a complete guest submission working on a public contact form');

		$this->probe($I, 'act=clearmaillog');

		$token = $this->openFormAndGrabToken($I);
		$post = array_merge($this->submission(), $this->captcha($I), array('e-token' => $token));
		$I->sendPostRequest('/contact.php', $post);

		$I->assertStringContainsString('Mail-ID=', $this->mailLog($I),
			'a complete guest submission must still be delivered');
	}

	/**
	 * The sender's address travels as a Reply-To header and nothing else.
	 */
	public function theDeliveredMessageCarriesTheSendersAddress(AcceptanceTester $I)
	{
		$I->wantTo('find the sender address in the contact message as delivered');

		$this->probe($I, 'act=clearmaillog');

		$token = $this->openFormAndGrabToken($I);
		$submission = $this->submission();
		$post = array_merge($submission, $this->captcha($I), array('e-token' => $token));
		$I->sendPostRequest('/contact.php', $post);

		$bodies = $this->mailBodies($I);

		$I->assertCount(1, $bodies, 'the dry run transport must record the body it was handed');
		$I->assertStringContainsString($submission['email_send'], $bodies[0],
			'the message the recipient reads must carry the address that sent it');
		$I->assertStringNotContainsString('e-email', $bodies[0],
			'the address must reach the recipient as itself, not as obfuscated markup');
	}

	/**
	 * A visitor's words reach the site as words, whatever they look like.
	 *
	 * The message and any custom field the template adds are assembled into an
	 * HTML mail, so anything that parses as markup on the way in is markup by
	 * the time an administrator opens it. strip_tags() on the message was not
	 * enough: it answers a literal tag and says nothing about BBCode, which the
	 * parser turns into one, and it never covered the custom fields at all,
	 * whose names went into the table unescaped.
	 *
	 * The markers are what stop this being satisfied by an empty mail: every
	 * piece the visitor sent must still be in the message the site receives.
	 */
	public function aMessageReachesTheSiteAsWordsRatherThanMarkup(AcceptanceTester $I)
	{
		$I->wantTo('read a contact message as what was typed rather than as markup');

		$this->probe($I, 'act=clearmaillog');

		$token = $this->openFormAndGrabToken($I);
		$post = array_merge($this->submission(), $this->captcha($I), array(
			'e-token'             => $token,
			'body'                => 'Please look at [img]http://example.com/BODYMARKER.png[/img] '
				.'and at <marquee>BODYTAG</marquee> and at {SITENAME}, which is comfortably '
				.'longer than the fifteen characters the form insists on.',
			'noteFIELDKEY<marquee>' => '<marquee>FIELDTAG</marquee>',
		));
		$I->sendPostRequest('/contact.php', $post);

		$log = $this->mailLog($I);

		$I->assertStringContainsString('BODYMARKER', $log,
			'what the visitor wrote must still reach the recipient');
		$I->assertStringContainsString('BODYTAG', $log,
			'text inside a tag the visitor wrote must still reach the recipient');
		$I->assertStringContainsString('noteFIELDKEY', $log,
			'a custom field must still be named in the message');
		$I->assertStringContainsString('FIELDTAG', $log,
			'a custom field value must still reach the recipient');

		$I->assertStringNotContainsString('<marquee', $log,
			'a tag the visitor wrote must not be a tag in the message');
		$I->assertStringNotContainsString("src='http://example.com/BODYMARKER.png'", $log,
			'BBCode the visitor wrote must not be rendered into the message');
		$I->assertStringContainsString('{SITENAME}', $log,
			'a shortcode the visitor wrote must not be expanded into the message');
	}

	/**
	 * @return string
	 */
	private function probeSource()
	{
		$contactClass = self::CONTACT_CLASS;

		return <<<PHP
<?php
// Fixture for 0035_ContactFormCest. Removed again in the Cest's _after().
\$_E107['allow_guest'] = true;
require_once(__DIR__.'/class2.php');
header('Content-Type: text/plain');

// Every request in the container arrives from the bridge address, so a Cest
// that makes more than a handful of them bans itself part way through.
e107::getDb()->delete('online');
e107::getDb()->delete('banlist', 'banlist_bantype IN (2, -2)');

class e107TestsP6Captcha
{
	public static function render(\$form = null)
	{
		return '<div class="p6-widget"></div>';
	}
}

class e107TestsP6Probe
{
	/**
	 * The contact form, rendered the way contact.php renders it.
	 *
	 * @return string
	 */
	public static function contactForm()
	{
		\$sc = e107::getScBatch('contact');
		\$sc->wrapper('contact/form');

		return \$sc->withImagecode(
			e107::getParser()->parseTemplate(e107::getCoreTemplate('contact', 'form'), true, \$sc));
	}
}

\$act = isset(\$_GET['act']) ? \$_GET['act'] : '';
\$config = e107::getConfig('core');
\$logFile = e_LOG.'mailoutlog.log';

switch(\$act)
{
	case 'setup':
		\$config->set('e107_tests_p6_mail_backup', \$config->get('mail_log_options', ''));
		\$config->set('e107_tests_p6_contacts_backup', \$config->get('sitecontacts', ''));
		\$config->set('e107_tests_p6_visibility_backup', \$config->get('contact_visibility', ''));
		\$config->set('e107_tests_p6_csrf_backup', \$config->get('csrf_enforce', 'default'));
		// 1 is dry run: log it, do not send it, and let the caller believe it
		// went. 2 would attempt a real send, and there is no sendmail here.
		\$config->set('mail_log_options', '1,1');
		\$config->set('sitecontacts', '$contactClass');
		\$config->set('contact_visibility', '0');
		\$config->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'teardown':
		\$config->set('mail_log_options', \$config->get('e107_tests_p6_mail_backup', ''));
		\$config->set('sitecontacts', \$config->get('e107_tests_p6_contacts_backup', ''));
		\$config->set('contact_visibility', \$config->get('e107_tests_p6_visibility_backup', ''));
		\$csrf = \$config->get('e107_tests_p6_csrf_backup', 'default');
		if(\$csrf === 'default') { \$config->remove('csrf_enforce'); }
		else { \$config->set('csrf_enforce', \$csrf); }
		\$config->remove('e107_tests_p6_mail_backup');
		\$config->remove('e107_tests_p6_contacts_backup');
		\$config->remove('e107_tests_p6_visibility_backup');
		\$config->remove('e107_tests_p6_csrf_backup');
		\$config->save(false, true, false);
		@unlink(\$logFile);
		echo "PROBE_OK\n";
		break;

	case 'csrf':
		if(\$_GET['m'] === 'default') { \$config->remove('csrf_enforce'); }
		else { \$config->set('csrf_enforce', (int) \$_GET['m']); }
		\$config->save(false, true, false);
		echo "PROBE_OK csrf\n";
		break;

	case 'menu':
		// The bundled contact menu, rendered the way the menu system renders it.
		ob_start();
		require(e_PLUGIN.'contact/contact_menu.php');
		\$menu = ob_get_clean();
		echo "PROBE_OK\n".\$menu;
		break;

	case 'overridden':
		// What a CAPTCHA plugin does: both render seams answered by its own
		// widget, and no code_verify field anywhere in what it emits.
		e107::getOverride()->replace('secure_image::r_image', 'e107TestsP6Captcha::render');
		e107::getOverride()->replace('secure_image::renderInput', 'e107TestsP6Captcha::render');
		echo "PROBE_OK\n";
		echo "WIDGETS=".substr_count(e107TestsP6Probe::contactForm(), 'p6-widget')."\n";
		break;

	case 'menutwice':
		ob_start();
		require(e_PLUGIN.'contact/contact_menu.php');
		require(e_PLUGIN.'contact/contact_menu.php');
		\$menus = ob_get_clean();
		echo "PROBE_OK\n";
		echo "TOKENS=".substr_count(\$menus, "name='rand_num'")."\n";
		break;

	case 'formthenmenu':
		e107TestsP6Probe::contactForm();
		ob_start();
		require(e_PLUGIN.'contact/contact_menu.php');
		\$menu = ob_get_clean();
		echo "PROBE_OK\n".\$menu;
		break;

	case 'visibility':
		\$config->set('contact_visibility', (string) intval(\$_GET['v']));
		\$config->save(false, true, false);
		echo "PROBE_OK\n";
		break;

	case 'captcha':
		// The answer is sealed inside the token, so a valid pair can be minted
		// here, inside the site, instead of read off an image. The challenge is
		// deliberately not named for a form, which is what a theme rendering its
		// own CAPTCHA markup does and what contact.php must therefore accept.
		\$img = e107::getSecureImg();
		echo "PROBE_OK\n";
		echo "RAND=".\$img->getToken()."\n";
		echo "CODE=".\$img->getSecret()."\n";
		break;

	case 'solve':
		// Read the answer out of a challenge some other document was served.
		// Only the site can do this now.
		\$claims = e107::getSealedToken(secure_image::TOKEN_PURPOSE)->open(\$_GET['t']);
		echo "PROBE_OK\n";
		echo "CODE=".(isset(\$claims['solution']) ? \$claims['solution'] : '')."\n";
		break;

	case 'clearmaillog':
		@unlink(\$logFile);
		echo "PROBE_OK\n";
		break;

	case 'maillog':
		clearstatcache();
		echo "PROBE_OK\n";
		echo is_readable(\$logFile) ? file_get_contents(\$logFile) : '';
		break;

	default:
		echo "unknown action\n";
}
PHP;
	}
}
