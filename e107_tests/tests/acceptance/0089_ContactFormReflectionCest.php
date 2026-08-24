<?php

/**
 * The contact form gives back what was posted to it, and did so twice over
 * without ever escaping the message field.
 *
 * contact_shortcodes.php built the textarea as
 *
 *     ...>".stripslashes($_POST['body'])."</textarea>
 *
 * so a posted body carrying </textarea> closed the element and everything
 * after it became markup in a page served from the site's own origin. The
 * three sibling fields in the same batch had always escaped their value, so
 * this was an omission rather than a policy.
 *
 * Escaping the field is only half of it. renderContactForm() parses the form
 * template in full, and contact.php then dropped the finished markup into
 * $LAYOUT and parsed the result a second time, so every value the form gave
 * back was handed to the shortcode parser. Escaping answers < and >; it does
 * not answer {, and a posted {CONTACT_EMAIL} put a whole input element on the
 * page with no angle bracket in the request at all. The layout is parsed
 * before the rendered blocks go into it now, which is the only order in which
 * a block that has already been rendered is not rendered again. On a default
 * install the second pass had nothing else to do: the core layout template is
 * the two placeholders and nothing more.
 *
 * Nothing about either reflection needs a session. contact.php renders the
 * form whenever the visitor's class may see it, and it renders it after a
 * failed submission as well as before one, so a post that carries no token at
 * all still reaches the shortcode. The form is opened to guests here because
 * that is the configuration where a stranger can reach the reflection; a
 * default install offers the form to members only, which hides the fault
 * rather than fixing it.
 *
 * The submissions deliberately omit send-contactus. Without it contact.php
 * skips processFormSubmit() entirely, which keeps the captcha and the rest of
 * the validation out of the measurement: what is under test is the redisplay,
 * not the send.
 *
 * Three of the cases below pass before the fix as well as after, and they are
 * what stops the refusal being satisfied by dropping the field: an ordinary
 * message must still come back, it must come back with its backslashes
 * intact, because the stripslashes() that guarded the old line ran
 * unconditionally on a PHP that has had no magic quotes since 5.4, and both
 * assembled blocks must still reach the page.
 */
class ContactFormReflectionCest
{
	/** Guests may see the contact form on this site while the file runs. */
	const VISIBLE_TO_EVERYONE = 0;

	/** What a default install ships: the form is offered to members. */
	const VISIBLE_TO_MEMBERS = 253;

	public function _before(AcceptanceTester $I)
	{
		$I->haveSitePref('contact_visibility', self::VISIBLE_TO_EVERYONE);
	}

	public function _after(AcceptanceTester $I)
	{
		$I->haveSitePref('contact_visibility', self::VISIBLE_TO_MEMBERS);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param array $fields what to put in the form
	 */
	private function post(AcceptanceTester $I, array $fields)
	{
		$I->amOnPage('/contact.php');
		$I->seeElement('#contactBody');
		$I->submitForm('#contactForm', $fields);
	}

	public function aPostedMessageCannotCloseTheTextarea(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a posted message that closes the message field');

		$this->post($I, array('body' => '</textarea><script>alert(document.domain)</script>'));

		$I->dontSeeInSource('</textarea><script>');
		$I->seeInSource('&lt;/textarea&gt;&lt;script&gt;alert(document.domain)&lt;/script&gt;');
	}

	public function aPostedMessageCannotOpenATagOfItsOwn(AcceptanceTester $I)
	{
		$I->wantTo('Refuse a posted message that opens a tag inside the message field');

		$this->post($I, array('body' => '<img src=x onerror=alert(document.domain)>'));

		$I->dontSeeInSource('<img src=x onerror=');
		$I->seeInSource('&lt;img src=x onerror=alert(document.domain)&gt;');
	}

	public function aPostedMessageIsNotParsedAsATemplate(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to expand a shortcode written into the message field');

		$this->post($I, array('body' => 'START{SITENAME}END'));

		$I->seeInSource('START{SITENAME}END');
	}

	public function aPostedMessageCannotRenderAFormFieldOfItsOwn(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to expand a shortcode that renders markup of its own');

		$this->post($I, array('body' => 'START{CONTACT_EMAIL}END'));

		$I->seeInSource('START{CONTACT_EMAIL}END');
		$I->dontSeeInSource("START<input type='email'");
	}

	public function aPostedSubjectIsNotParsedAsATemplate(AcceptanceTester $I)
	{
		$I->wantTo('Refuse to expand a shortcode written into the subject field');

		$this->post($I, array('subject' => 'START{SITENAME}END'));

		$I->seeInSource('START{SITENAME}END');
	}

	public function anOrdinaryMessageIsStillGivenBack(AcceptanceTester $I)
	{
		$I->wantTo('Give an ordinary message back to whoever typed it');

		$this->post($I, array('body' => 'Order 5150 arrived damaged. Please advise.'));

		$I->seeInSource('Order 5150 arrived damaged. Please advise.');
	}

	public function aMessageKeepsItsBackslashes(AcceptanceTester $I)
	{
		$I->wantTo('Give a message back with its backslashes intact');

		$this->post($I, array('body' => 'The installer stops at C:\\inetpub\\wwwroot and says "no".'));

		$I->seeInSource('C:\\inetpub\\wwwroot');
	}

	public function bothAssembledBlocksStillReachThePage(AcceptanceTester $I)
	{
		$I->wantTo('Put the contact information and the contact form on the page');

		$I->amOnPage('/contact.php');

		$I->seeElement('#contactForm');
		$I->seeElement('#contactBody');
		$I->dontSeeInSource('{---CONTACT-FORM---}');
		$I->dontSeeInSource('{---CONTACT-INFO---}');
	}
}
