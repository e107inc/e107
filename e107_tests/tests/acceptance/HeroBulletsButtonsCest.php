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

		$I->amSignedInAs(self::HERO_ADMIN);
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
			$I->amSignedInAs(self::HERO_ADMIN);
			$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
			$I->submitForm('#plugin-hero-form', array(
				'hero_bullets[0][text]' => 'Bullet <b id="q7chb">bold</b><img id="q7chi" src="x.png" onload="q7c()">',
				'hero_button1[label]'   => 'Call <b id="q7chl">us</b>',
				'hero_button1[url]'     => 'tel:+15555550100',
			), 'etrigger_submit');
		});

		$I->assertSame(1, json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true)['__trust__']);

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
			$I->amSignedInAs(self::HERO_ADMIN);
			$I->amOnPage('/e107_plugins/hero/admin_config.php?mode=main&action=edit&id='.$id);
			$I->submitForm('#plugin-hero-form', array(
				'hero_bullets[0][text]' => 'Bullet <b id="q7cho">bold</b>',
				'hero_button1[label]'   => 'Call <b id="q7cho2">us</b>',
			), 'etrigger_submit');
		});

		$I->assertSame(0, json_decode($I->grabFromDatabase('e107_hero', 'hero_button1', array('hero_id' => $id)), true)['__trust__']);

		$I->amOnPage('/');
		$I->seeInSource('Q7C outside');
		$I->dontSeeInSource('<b id="q7cho">');
		$I->dontSeeInSource('<b id="q7cho2">');
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

		$I->amOnPage('/');
		$I->see('Plain bullet q7c');
		$I->see('Plain button q7c');
		$I->seeInSource('href="https://example.com/q7c" class="btn btn-success');
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
