<?php

/**
 * A hero slide's bullets and buttons, from whoever stored them, print as the values they are on the home page and in
 * the hero plugin's admin area.
 */
class HeroBulletsButtonsCest
{
	/** An administrator who holds the hero plugin's permission and nothing else. */
	const HERO_ADMIN = 'q7chero';

	const HERO_ADMIN_PASS = 'Q7cHeroPass';

	/** Markup a part may carry; the id marks it apart from anything the theme prints. */
	const MARKUP = '"><svg id=q7ch onload=alert(1)>';

	/** A class that leaves its attribute. */
	const CLASS_BREAK = "primary' autofocus onfocus='q7ch()";

	public function _before(AcceptanceTester $I)
	{
		$I->havePluginInstalled('hero');
		$plugin = $I->grabFromDatabase('e107_plugin', 'plugin_id', array('plugin_path' => 'hero'));
		$I->haveMember(self::HERO_ADMIN, self::HERO_ADMIN_PASS, array('user_admin' => 1, 'user_perms' => 'P'.$plugin.'.'));
	}

	public function _after(AcceptanceTester $I)
	{
		$I->dropPluginProbe();
	}

	public function aHeroAdministratorsBulletsAndButtonsAreStoredAsTheTitleIs(AcceptanceTester $I)
	{
		$id = $this->haveSlide($I, 'Q7C posted', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->loginAsAdmin(self::HERO_ADMIN, self::HERO_ADMIN_PASS);
		$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
		$I->submitForm('#plugin-hero-form', array(
			'hero_bullets[0][icon]' => '<i class="fa"></i>'.self::MARKUP,
			'hero_bullets[0][text]' => 'Bullet'.self::MARKUP,
			'hero_button1[icon]'    => '<i class="fa"></i>'.self::MARKUP,
			'hero_button1[label]'   => 'Go'.self::MARKUP,
			'hero_button1[url]'     => 'https://example.com/q7c?a=1&b=2',
		), 'etrigger_submit');

		$bullets = $I->grabFromDatabase('e107_hero', 'hero_bullets', array('hero_id' => $id));
		$button = $I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id));

		$I->assertStringContainsString('Bullet', $bullets);
		$I->assertStringNotContainsString('<svg', $bullets.$button);
		$I->assertSame('https://example.com/q7c?a=1&b=2', json_decode($button, true)['url']);
	}

	public function anAuthorsHtmlAndPhoneLinkPrint(AcceptanceTester $I)
	{
		$id = $this->haveSlide($I, 'Q7C author', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 255), function () use ($I, $id)
		{
			$I->resetAllCookies();
			$I->loginAsAdmin(self::HERO_ADMIN, self::HERO_ADMIN_PASS);
			$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
			$I->submitForm('#plugin-hero-form', array(
				'hero_bullets[0][text]' => 'Bullet <b id="q7chb">bold</b><img id="q7chi" src="x.png" onload="q7c()">',
				'hero_button1[label]'   => 'Call <b id="q7chl">us</b>',
				'hero_button1[url]'     => 'tel:+15555550100',
			), 'etrigger_submit');
		});

		$I->assertSame(1, json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true)['__trust__']['label']);

		$I->haveSiteTheme('bootstrap5');
		$I->amOnPage('/');
		$I->seeInSource('<b id="q7chb">bold</b>');
		$I->dontSeeElement('#q7chi[onload]');
		$I->seeInSource('<b id="q7chl">us</b>');
		$I->seeInSource('href="tel:+15555550100"');
	}

	public function anAuthorOutsideTheHtmlClassGetsText(AcceptanceTester $I)
	{
		$id = $this->haveSlide($I, 'Q7C outside', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->haveSitePrefsDuring(array('post_html' => 250, 'post_script' => 255), function () use ($I, $id)
		{
			$I->resetAllCookies();
			$I->loginAsAdmin(self::HERO_ADMIN, self::HERO_ADMIN_PASS);
			$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
			$I->submitForm('#plugin-hero-form', array(
				'hero_bullets[0][text]' => 'Bullet <b id="q7cho">bold</b>',
				'hero_button1[label]'   => 'Call <b id="q7cho2">us</b>',
			), 'etrigger_submit');
		});

		$I->assertSame(0, json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true)['__trust__']['label']);

		$I->haveSiteTheme('bootstrap5');
		$I->amOnPage('/');
		$I->seeInSource('Q7C outside');
		$I->dontSeeInSource('<b id="q7cho">');
		$I->dontSeeInSource('<b id="q7cho2">');
	}

	public function aLowerAdministratorsSaveKeepsThePartsTheyLeftAlone(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap5');
		$id = $this->haveSlide($I, 'Q7C kept', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $id)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array(
				'hero_bullets[0][text]' => 'Bullet <b id="q7chk" onclick="q7c()">kept</b>',
				'hero_button1[label]'   => 'Call <b id="q7chl">us</b>',
				'hero_button1[url]'     => 'tel:+15555550100',
			));
		});

		$I->haveSitePrefsDuring(array('post_html' => 250, 'post_script' => 255), function () use ($I, $id)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array('hero_bullets[1][text]' => 'Second <b id="q7chc">changed</b>'));
		});

		$I->amOnPage('/');
		$I->seeElement('#q7chk[onclick]');
		$I->seeInSource('<b id="q7chl">us</b>');
		$I->seeInSource('href="tel:+15555550100"');
		$I->see('Second');
		$I->dontSeeElement('#q7chc');
	}

	public function aHigherAdministratorsSaveDoesNotPromotePartsTheyLeftAlone(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap5');
		$id = $this->haveSlide($I, 'Q7C typed', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 255), function () use ($I, $id)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array('hero_bullets[0][text]' => '&lt;b id="q7chp" onclick="q7c()"&gt;typed&lt;/b&gt;'));
		});

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $id)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array());
		});

		$I->amOnPage('/');
		$I->seeInSource('Q7C typed');
		$I->dontSeeElement('#q7chp');
	}

	/**
	 * A bullet text with a reference such as &#128; in it stays text after an untouched save with script access.
	 */
	public function aHigherAdministratorsSaveDoesNotPromoteATextTheBrowserWouldDecodeItself(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap5');
		$id = $this->haveSlide($I, 'Q7C decoded', array('icon' => 'fa-sun-o.glyph', 'text' => '&#128;<b id="q7chg" onclick="q7c()">legacy</b>'),
			array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $id)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array());
		});

		$I->amOnPage('/');
		$I->seeInSource('Q7C decoded');
		$I->dontSeeElement('#q7chg');
	}

	/**
	 * A lower author's label and address holding line breaks stay as stored after an untouched save with script access.
	 */
	public function aHigherAdministratorsSaveDoesNotPromoteWhatTheBrowserPostsBackWithoutItsLineBreaks(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap5');
		$label = "Go\r&lt;b id=&quot;q7chn&quot; onclick=&quot;q7c()&quot;&gt;x&lt;/b&gt;";
		$id = $this->haveSlide($I, 'Q7C line breaks', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'),
			array('label' => $label, 'url' => "tel:+1555\r\n5550100", 'class' => 'primary', '__trust__' => array('label' => 0, 'url' => 0)));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $id, $label)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array('hero_button1[label]' => str_replace("\r", '', html_entity_decode($label, ENT_QUOTES)),
				'hero_button1[url]' => 'tel:+15555550100'));
		});

		$I->amOnPage('/');
		$I->seeInSource('Q7C line breaks');
		$I->dontSeeElement('#q7chn');
		$I->dontSeeInSource('href="tel:+15555550100"');
	}

	/**
	 * A script author's label and address holding line breaks stay as stored after an untouched save by a text-only author.
	 */
	public function aLowerAdministratorsSaveKeepsWhatTheBrowserPostsBackAltered(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap5');
		$id = $this->haveSlide($I, 'Q7C lower altered', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));
		$label = "Call\n<b id=\"q7chm\" onclick=\"q7c()\">us</b>";

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $id, $label)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array('hero_button1[label]' => $label, 'hero_button1[url]' => "tel:+1555\n5550100"));
		});

		$stored = json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true);

		$I->haveSitePrefsDuring(array('post_html' => 250, 'post_script' => 255), function () use ($I, $id, $label)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array('hero_button1[label]' => str_replace("\n", '', $label), 'hero_button1[url]' => 'tel:+15555550100'));
		});

		$kept = json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true);
		ksort($stored);
		ksort($kept);
		$I->assertSame($stored, $kept);
		$I->assertSame(array(2, 2), array($kept['__trust__']['label'], $kept['__trust__']['url']));

		$I->amOnPage('/');
		$I->seeElement('#q7chm[onclick]');
	}

	/**
	 * A bullet text starting with line breaks stays as stored after an untouched save with script access.
	 */
	public function aHigherAdministratorsSaveKeepsATextStartingWithLineBreaks(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap5');
		$text = "\r\n\n<b id=\"q7chy\" onclick=\"q7c()\">legacy</b>";
		$id = $this->haveSlide($I, 'Q7C breaks', array('icon' => 'fa-sun-o.glyph', 'text' => $text), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $id, $text)
		{
			$this->saveSlideAsHeroAdmin($I, $id, array('hero_bullets[0][text]' => "\r\n".substr($text, 3)));
		});

		$bullets = json_decode($I->grabFromDatabase('e107_hero', 'hero_bullets', array('hero_id' => $id)), true);
		$I->assertSame(array($text, array()), array($bullets[0]['text'], $bullets[0]['__trust__']));
		$I->amOnPage('/');
		$I->seeInSource('Q7C breaks');
		$I->dontSeeElement('#q7chy');
	}

	/**
	 * An inline edit of a slide's title leaves its bullets as they were stored.
	 */
	public function anInlineEditLeavesTheBulletsAlone(AcceptanceTester $I)
	{
		$list = '/e107_plugins/hero/admin_config.php?mode=main&action=list';
		$id = $this->haveSlide($I, 'Q7C inline', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet q7ci', '__trust__' => array('text' => 1)),
			array('label' => 'Go', 'url' => '', 'class' => 'primary'));
		$stored = $I->grabFromDatabase('e107_hero', 'hero_bullets', array('hero_id' => $id));

		$I->loginAsAdmin();
		$I->amOnPage($list);
		$I->sendPostRequest('/e107_plugins/hero/admin_config.php?mode=main&action=inline&id='.$id.'&ajax_used=1', array(
			'name'    => 'hero_title',
			'value'   => 'Q7C inline renamed',
			'pk'      => $id,
			'token'   => $I->grabAttributeFrom('[data-name="hero_title"][data-pk="'.$id.'"]', 'data-token'),
			'e-token' => $I->grabFreshAdminToken($list),
		));

		$I->seeInDatabase('e107_hero', array('hero_id' => $id, 'hero_title' => 'Q7C inline renamed'));
		$I->assertSame($stored, $I->grabFromDatabase('e107_hero', 'hero_bullets', array('hero_id' => $id)));
	}

	public function anUntouchedSaveStoresEachValueAgainByteForByte(AcceptanceTester $I)
	{
		$bullet = array('icon' => 'fa-sun-o.glyph', 'icon_style' => 'info', 'text' => 'java[p][/p]script:q7c() [h][/h]<b>kept</b>', 'animation' => '',
			'animation_delay' => '0', '__trust__' => array('icon_style' => 1, 'text' => 2));
		$button = array('icon' => '', 'label' => 'Go [p][/p]<b>now</b>', 'url' => 'https://example.com/q7c', 'class' => 'primary', '__trust__' => array('label' => 1, 'url' => 1));
		$id = $this->haveSlide($I, 'Q7C bytes', $bullet, $button);

		$this->saveSlideAsHeroAdmin($I, $id, array());

		$bullets = json_decode($I->grabFromDatabase('e107_hero', 'hero_bullets', array('hero_id' => $id)), true);
		$kept = array('bullet' => $bullets[0], 'button' => json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true));

		foreach(array('bullet' => $bullet, 'button' => $button) as $part => $seeded)
		{
			ksort($seeded);
			ksort($kept[$part]);
			$I->assertSame($seeded, $kept[$part], $part);
		}
	}

	public function theMainAdministratorSeesStoredBulletsAndButtonsAsText(AcceptanceTester $I)
	{
		$id = $this->haveSlide($I, 'Q7C stored', array(
			'icon'            => '<i class="fa"></i>'.self::MARKUP,
			'icon_style'      => 'warning'.self::MARKUP,
			'text'            => 'Bullet'.self::MARKUP,
			'animation'       => 'fadeIn'.self::MARKUP,
			'animation_delay' => '15'.self::MARKUP,
		), array('icon' => '<i class="fa"></i>'.self::MARKUP, 'label' => 'Go'.self::MARKUP, 'url' => 'javascript:q7ch()', 'class' => self::CLASS_BREAK));

		$I->haveSiteTheme('bootstrap5');
		$I->loginAsAdmin();

		foreach(array('/', '/e107_plugins/hero/admin_config.php?mode=main&action=list', '/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id) as $page)
		{
			$I->amOnPage($page);
			$I->seeInSource('Q7C stored');
			$I->dontSeeInSource('<svg id=q7ch');
			$I->dontSeeInSource("primary' autofocus");
			$I->dontSeeInSource('href="javascript:q7ch');
			$I->dontSeeInSource("href='javascript:q7ch");
		}
	}

	public function aSavedLabelReopensAsTyped(AcceptanceTester $I)
	{
		$label = 'Q&A "quoted" Don\'t $5';
		$id = $this->haveSlide($I, 'Q7C label', array('icon' => 'fa-sun-o.glyph', 'text' => 'Bullet'), array('label' => 'Go', 'url' => '', 'class' => 'primary'));

		$I->loginAsAdmin();
		$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
		$I->submitForm('#plugin-hero-form', array('hero_button1[label]' => $label), 'etrigger_submit');
		$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);

		$I->seeInField('hero_button1[label]', $label);
	}

	public function plainBulletsAndButtonsStillRender(AcceptanceTester $I)
	{
		$this->haveSlide($I, 'Q7C plain', array('icon' => 'fa-sun-o.glyph', 'text' => 'Plain bullet q7c'), array('label' => 'Plain button q7c', 'url' => 'https://example.com/q7c', 'class' => 'success'));

		$I->haveSiteTheme('bootstrap5');
		$I->amOnPage('/');
		$I->see('Plain bullet q7c');
		$I->see('Plain button q7c');
		$I->seeInSource('href="https://example.com/q7c" class="btn btn-success');
	}

	/**
	 * Save slide $id through its edit form as the hero-only administrator, the icon pickers posting the icon they show.
	 *
	 * @param AcceptanceTester $I
	 * @param int $id
	 * @param array $values form field => value
	 */
	private function saveSlideAsHeroAdmin(AcceptanceTester $I, $id, array $values)
	{
		$I->resetAllCookies();
		$I->loginAsAdmin(self::HERO_ADMIN, self::HERO_ADMIN_PASS);
		$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
		$I->submitForm('#plugin-hero-form', $values + array('hero_bullets[0][icon]' => 'fa-sun-o', 'hero_button1[icon]' => ''), 'etrigger_submit');
	}

	/**
	 * A slide whose first bullet and first button hold the parts given, stored as they are given.
	 *
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param array $bullet
	 * @param array $button
	 * @return int hero_id
	 */
	private function haveSlide(AcceptanceTester $I, $title, array $bullet, array $button)
	{
		return $I->haveInDatabase('e107_hero', array(
			'hero_title'       => $title,
			'hero_description' => 'Q7C description',
			'hero_bg'          => '',
			'hero_media'       => '',
			'hero_bullets'     => json_encode(array(array_merge(array('icon' => '', 'icon_style' => '', 'text' => '', 'animation' => '', 'animation_delay' => '0'), $bullet))),
			'hero_button1'     => json_encode(array_merge(array('icon' => '', 'label' => '', 'url' => '', 'class' => ''), $button)),
			'hero_button2'     => json_encode(array('icon' => '', 'label' => '', 'url' => '', 'class' => '')),
			'hero_order'       => 0,
			'hero_class'       => 0,
		));
	}
}
