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
 * Provider names in the social plugin's login configuration: which names the save the login forms share keeps, and how the admin pages and the update print the names a site holds, including names no provider has.
 */
class socialProviderNameAdminTest extends \Codeception\Test\Unit
{
	use \Test\BootedCli;

	/** A name holding a quote and a markup tag, as a hand-made post could store before the save refused names no provider has. */
	const POSTED_NAME = "Old'Provider<b>";

	/** What the add form posts for Facebook. */
	const FACEBOOK_POST = array('enabled' => '1', 'keys' => array('id' => '1234567890123456', 'secret' => 'a-secret'));

	protected function _after()
	{
		putenv('QUERY_STRING');
	}

	public function testTheUnsupportedPagePrintsAStoredNameInsideTheAttributesOfItsRow()
	{
		$row = $this->onlyRow($this->page('action=unsupported', array(self::POSTED_NAME => array('enabled' => 1))));
		$id = strtolower(self::POSTED_NAME);

		self::assertSame(array('id' => 'social-login-row-'.$id), $this->attributesOf($row),
			"The name changed the attributes of its row:\n".$this->markupOf($row));
		self::assertSame(array('for' => 'social-login-'.$id.'-enabled'), $this->attributesOf($this->labelOf($row)),
			"The name changed the attributes of its label:\n".$this->markupOf($row));
	}

	public function testTheUnsupportedPagePrintsAStoredNameAsTheTextOfItsLabel()
	{
		$row = $this->onlyRow($this->page('action=unsupported', array(self::POSTED_NAME => array('enabled' => 1))));
		$label = $this->labelOf($row);

		self::assertSame(0, $label->getElementsByTagName('*')->length,
			"The name added elements to its label:\n".$this->markupOf($row));
		self::assertSame(self::POSTED_NAME, $label->textContent,
			"The label does not read as the name:\n".$this->markupOf($row));
	}

	/**
	 * A name kept from a provider that Hybridauth no longer has, the case the Unsupported page is for.
	 */
	public function testTheUnsupportedPagePrintsALegacyNameAsBefore()
	{
		$page = $this->page('action=unsupported', array('AOL' => array('enabled' => 1)));

		self::assertStringContainsString("<tr id='social-login-row-aol'>", $page);
		self::assertStringContainsString("<label for='social-login-aol-enabled'>AOL</label>", $page);
	}

	/**
	 * A name written like a path constant, which the page must print as typed rather than expand.
	 */
	public function testTheUnsupportedPagePrintsABracedNameAsTyped()
	{
		$row = $this->onlyRow($this->page('action=unsupported', array('{e_PLUGIN}' => array('enabled' => 1))));

		self::assertSame('{e_PLUGIN}', $this->labelOf($row)->textContent,
			"The label does not read as the name:\n".$this->markupOf($row));
	}

	public function testTheUpdatePrintsARenamedStoredNameAsText()
	{
		$message = $this->onlySuccessOf($this->update(array(self::POSTED_NAME.'-x' => array('enabled' => 1))));

		self::assertSame(0, $message->getElementsByTagName('*')->length,
			"The name added elements to the update's message:\n".$this->markupOf($message));
		self::assertSame('Updated name of social login provider '.self::POSTED_NAME.'-x → '.self::POSTED_NAME.'x', $message->textContent,
			"The update's message does not read as the names:\n".$this->markupOf($message));
	}

	/**
	 * A name written like a path constant, which the update's message must print as typed rather than expand.
	 */
	public function testTheUpdatePrintsABracedNameAsTyped()
	{
		$message = $this->onlySuccessOf($this->update(array('{e_PLUGIN}-x' => array('enabled' => 1))));

		self::assertSame('Updated name of social login provider {e_PLUGIN}-x → {e_PLUGIN}x', $message->textContent,
			"The update's message does not read as the names:\n".$this->markupOf($message));
	}

	/**
	 * A name kept from Hybridauth 2, the case the update renames names for.
	 */
	public function testTheUpdatePrintsALegacyRenameAsBefore()
	{
		$messages = $this->update(array('Github' => array('enabled' => 1)));

		self::assertStringContainsString("<div class='s-message-item'>Updated name of social login provider Github → GitHub-OAuth2</div>", $messages);
	}

	/**
	 * The Configure page lists the supported providers alone, through the row the Unsupported page uses.
	 */
	public function testTheConfigurePagePrintsASupportedProviderAsBefore()
	{
		$page = $this->page('action=configure', array('Facebook-OAuth2' => array('keys' => array('id' => '1234567890123456'))));

		self::assertStringContainsString("<tr id='social-login-row-facebook'>", $page);
		self::assertStringContainsString($this->facebookLabel($page), $page);
	}

	public function testTheAddFormPrintsASupportedProviderAsBefore()
	{
		$page = $this->page('action=modify&id=Facebook', array());

		self::assertStringContainsString($this->facebookLabel($page), $page);
	}

	public function testTheSaveKeepsNoNameThatNoProviderHas()
	{
		$stored = $this->save(array(self::POSTED_NAME => array('enabled' => '1'), 'Facebook' => self::FACEBOOK_POST), array());

		self::assertSame(array('Facebook-OAuth2' => self::FACEBOOK_POST), $stored, 'The save kept a name no provider has.');
	}

	public function testTheSaveStoresASupportedProviderAsBefore()
	{
		self::assertSame(array('Facebook-OAuth2' => self::FACEBOOK_POST), $this->save(array('Facebook' => self::FACEBOOK_POST), array()));
	}

	/**
	 * The add form posts the name its address names, which a hand-typed address can give in another case.
	 */
	public function testTheSaveStoresAProviderPostedInAnotherCaseAsBefore()
	{
		self::assertSame(array('Facebook-OAuth2' => self::FACEBOOK_POST), $this->save(array('facebook' => self::FACEBOOK_POST), array()));
	}

	public function testTheSaveLeavesAStoredNameThatNoProviderHasAsItIs()
	{
		$stored = $this->save(array('Facebook' => self::FACEBOOK_POST), array('AOL' => array('enabled' => '1')));

		self::assertSame(array('AOL' => array('enabled' => '1'), 'Facebook-OAuth2' => self::FACEBOOK_POST), $stored);
	}

	/**
	 * @param string $page
	 * @return string the label a page prints for Facebook, linked to the documentation Hybridauth names for it
	 */
	private function facebookLabel($page)
	{
		self::assertSame(1, preg_match('/^FACTS:(.*)$/m', $page, $match), "The child did not report what the label is built from:\n".$page);
		$facts = json_decode($match[1], true);

		return "<label for='social-login-facebook-enabled'><a class='e-tip' rel='external' title=' ".$facts['title']."' href='".$facts['href']."'>Facebook</a></label>";
	}

	/**
	 * Renders a page of the social plugin's admin area as the main admin, with the social login configuration held in memory.
	 *
	 * @param string $query the page's query string less its mode
	 * @param array  $login what the social_login preference holds, by stored provider name
	 * @param string $php   more for the child to run before the page
	 * @return string everything the page wrote
	 */
	private function page($query, array $login, $php = '')
	{
		putenv('QUERY_STRING=mode=main&'.$query);

		$seed = "e107::getUser()->setData(e107::getDb()->retrieve('user', '*', 'user_id = 1')); ";
		$seed .= "e107::getConfig()->set('social_login', ".var_export($login, true)."); ";
		$seed .= "register_shutdown_function(function() { \$docs = (new ReflectionClass('Hybridauth\\\\Provider\\\\Facebook'))->getDefaultProperties(); ";
		$seed .= "echo \"\\nFACTS:\".json_encode(array('title' => LAN_SOCIAL_ADMIN_10, 'href' => \$docs['apiDocumentation'])).\"\\n\"; }); ";

		$page = $this->renderInBootedCli('e107_plugins/social/admin_config.php', $seed.$php);

		self::assertStringNotContainsString('Fatal error', $page, "The page died:\n".$page);

		return $page;
	}

	/**
	 * Posts to the save the login forms share, as the add form does and as the main admin, with the social login configuration held in memory and the save itself refused, so nothing is written.
	 *
	 * @param array $posted what the form posts as social_login, by provider name
	 * @param array $login  what the social_login preference holds before the save, by stored provider name
	 * @return array what the social_login preference holds once the save has run
	 */
	private function save(array $posted, array $login)
	{
		$php = "\$_POST = ".var_export(array('save_social_logins' => '1', 'social_login' => $posted), true)."; ";
		$php .= "e107::getConfig()->addValidationError('forced by socialProviderNameAdminTest'); ";
		$php .= "register_shutdown_function(function() { echo \"\\nSTORED:\".json_encode(e107::getConfig()->get('social_login')).\"\\n\"; }); ";

		$page = $this->page('action=add', $login, $php);

		self::assertSame(1, preg_match('/^STORED:(.*)$/m', $page, $match), "The save did not report what it kept:\n".$page);

		return json_decode($match[1], true);
	}

	/**
	 * Runs the social plugin's own step of the update the main admin starts from the update page, with the social login configuration held in memory and the save it ends with refused.
	 *
	 * @param array $login what the social_login preference holds, by stored provider name
	 * @return string the messages the update left for the page, as the page renders them
	 */
	private function update(array $login)
	{
		$php = "e107::getConfig()->set('social_login', ".var_export($login, true)."); ";
		$php .= "e107::getConfig()->addValidationError('forced by socialProviderNameAdminTest'); ";
		$php .= "require_once(e_PLUGIN.'social/social_setup.php'); ";
		$php .= "(new social_setup())->upgrade_pre(); ";
		$php .= "echo \"\\nMESSAGES:\".e107::getMessage()->render().\"\\nEND\\n\"; ";

		list($output) = $this->runInBootedCli($php);
		$out = implode("\n", $output);

		self::assertSame(1, preg_match('/^MESSAGES:(.*)^END$/ms', $out, $match), "The update did not finish:\n".$out);

		return $match[1];
	}

	/**
	 * @param string $page
	 * @return DOMElement the one provider row the page printed
	 */
	private function onlyRow($page)
	{
		$rows = $this->query($page, '//tr[starts-with(@id, "social-login-row-")]');

		self::assertSame(1, $rows->length, "The page did not print one provider row:\n".$page);

		return $rows->item(0);
	}

	/**
	 * @param string $messages
	 * @return DOMElement the one success message among $messages
	 */
	private function onlySuccessOf($messages)
	{
		$items = $this->query($messages, '//div[contains(@class, "alert-success")]//div[@class="s-message-item"]');

		self::assertSame(1, $items->length, "The update did not leave one success message:\n".$messages);

		return $items->item(0);
	}

	/**
	 * @param string $html
	 * @param string $xpath
	 * @return DOMNodeList what $xpath finds in $html, parsed as a browser would
	 */
	private function query($html, $xpath)
	{
		$dom = new DOMDocument();
		$errors = libxml_use_internal_errors(true);
		$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
		libxml_clear_errors();
		libxml_use_internal_errors($errors);

		return (new DOMXPath($dom))->query($xpath);
	}

	/**
	 * @param DOMElement $row
	 * @return DOMElement the label in the row's first cell
	 */
	private function labelOf(DOMElement $row)
	{
		$labels = (new DOMXPath($row->ownerDocument))->query('./td[1]/label', $row);

		self::assertSame(1, $labels->length, "The row's first cell holds no single label:\n".$this->markupOf($row));

		return $labels->item(0);
	}

	/**
	 * @param DOMElement $element
	 * @return array the element's attributes, name => value
	 */
	private function attributesOf(DOMElement $element)
	{
		$attributes = array();

		foreach($element->attributes as $attribute)
		{
			$attributes[$attribute->nodeName] = $attribute->nodeValue;
		}

		return $attributes;
	}

	/**
	 * @param DOMElement $element
	 * @return string
	 */
	private function markupOf(DOMElement $element)
	{
		return $element->ownerDocument->saveHTML($element);
	}
}
