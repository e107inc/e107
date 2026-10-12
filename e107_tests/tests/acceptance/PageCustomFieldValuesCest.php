<?php

/**
 * A custom page field's value, from whoever stored it, prints as the value it is.
 */
class PageCustomFieldValuesCest
{
	/** An administrator who holds the Pages permission and nothing else. */
	const PAGES_ADMIN = 'cfq7pages';

	const PAGES_ADMIN_PASS = 'Cfq7PagesPass';

	/** Markup a value may carry; the id marks it apart from anything the theme prints. */
	const MARKUP = '"><svg id=cfq7x onload=alert(1)>';

	/** @var int */
	private $chapterId;

	public function _before(AcceptanceTester $I)
	{
		$I->haveMember(self::PAGES_ADMIN, self::PAGES_ADMIN_PASS, array('user_admin' => 1, 'user_perms' => '5.'));

		$fields = array('__tabs__' => array('additional' => 'Extra'));

		foreach(array('myvideo' => 'video', 'myicon' => 'icon', 'mytext' => 'text', 'mybbarea' => 'bbarea', 'mytags' => 'tags',
			'mycheckbox' => 'checkbox', 'myurl' => 'url', 'myemail' => 'email', 'mynumber' => 'number', 'myfile' => 'file',
			'myprogressbar' => 'progressbar', 'myimage' => 'image') as $key => $type)
		{
			$fields[$key] = array('title' => ucfirst($type), 'type' => $type, 'writeParms' => '', 'help' => '');
		}

		$this->chapterId = $I->haveInDatabase('e107_page_chapters', array(
			'chapter_parent'     => 1,
			'chapter_name'       => 'Custom fields cfq7',
			'chapter_sef'        => 'custom-fields-cfq7',
			'chapter_template'   => 'default',
			'chapter_meta_description' => '',
			'chapter_meta_keywords'    => '',
			'chapter_icon'       => '',
			'chapter_fields'     => json_encode($fields),
			'chapter_manager'    => 254,
			'chapter_image'      => '',
			'chapter_order'      => 0,
			'chapter_visibility' => 0,
		));
	}

	public function aPagesAdministratorsValuesAreStoredAsThePageBodyIs(AcceptanceTester $I)
	{
		$page = $this->havePage($I, 'Posted cfq7', array('mytext' => 'Text', 'myicon' => 'fa-check.glyph'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 255), function () use ($I, $page)
		{
			$I->amSignedInAs(self::PAGES_ADMIN);
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(
				'page_fields__mytext'   => 'Text'.self::MARKUP,
				'page_fields__mybbarea' => 'Rich'.self::MARKUP,
				'page_fields__myicon'   => '<i class="fa"></i>'.self::MARKUP,
			), 'etrigger_submit');
		});

		$stored = $I->grabFromDatabase('e107_page', 'page_fields', array('page_id' => $page));

		$I->assertStringContainsString('Text', $stored);
		$I->assertStringNotContainsString('<svg', $stored);
		$I->assertSame(1, json_decode($stored, true)['__trust__']);
	}

	public function anAuthorsHtmlPrintsAndTheirScriptOnlyWithScriptAccess(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap3');
		$page = $this->havePage($I, 'Author cfq7', array('mytext' => 'Text'));
		$scripted = $this->havePage($I, 'Author script cfq7', array('mytext' => 'Text'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 255), function () use ($I, $page)
		{
			$this->saveAsPagesAdmin($I, $page, array(
				'page_fields__mytext'   => 'Plain <b id="cfq7b">bold</b>',
				'page_fields__mybbarea' => 'Rich <script id="cfq7s">var cfq7 = 1;</script><img id="cfq7o" src="x.png" onload="cfq7()">',
			));
		});

		$I->amSignedInAs();
		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->seeInSource('<b id="cfq7b">bold</b>');
		$I->dontSeeInSource('<script id="cfq7s"');
		$I->dontSeeElement('#cfq7o[onload]');

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 254), function () use ($I, $scripted)
		{
			$this->saveAsPagesAdmin($I, $scripted, array('page_fields__mybbarea' => 'Rich <script id="cfq7s">var cfq7 = 1;</script>'));
		});

		$I->amSignedInAs();
		$I->amOnPage('/page.php?id='.$scripted);
		$I->seeInSource('<script id="cfq7s">');
	}

	public function theMainAdministratorsScriptStaysOutOfARichTextValueWithoutScriptAccess(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap3');
		$page = $this->havePage($I, 'Main admin cfq7', array('mytext' => 'Text'));

		$I->haveSitePrefsDuring(array('post_script' => 255), function () use ($I, $page)
		{
			$I->amSignedInAs();
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(
				'page_fields__mybbarea' => 'Rich <script id="cfq7m">var cfq7 = 2;</script><img id="cfq7n" src="x.png" onload="cfq7()">',
			), 'etrigger_submit');
		});

		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->dontSeeInSource('<script id="cfq7m"');
		$I->dontSeeElement('#cfq7n[onload]');
	}

	public function theMainAdministratorSeesStoredValuesAsText(AcceptanceTester $I)
	{
		$page = $this->havePage($I, 'Stored cfq7', array(
			'myvideo'    => 'x'.self::MARKUP.'.youtube',
			'myicon'     => '<i class="fa"></i>'.self::MARKUP,
			'mytext'     => 'Text'.self::MARKUP,
			'mybbarea'   => 'Rich'.self::MARKUP,
			'mytags'     => 'tag'.self::MARKUP,
			'mycheckbox' => 'on'.self::MARKUP,
			'myurl'      => 'https://example.com/'.self::MARKUP,
			'myemail'    => 'a@example.com'.self::MARKUP,
			'mynumber'   => '1'.self::MARKUP,
			'myfile'     => 'x'.self::MARKUP,
			'myprogressbar' => '1/2'.self::MARKUP,
			'myimage'    => 'javascript:alert(1)//.svg',
		));
		$video = $this->havePage($I, 'Stored file cfq7', array('myvideo' => 'x'.self::MARKUP.'.mp4'));

		$I->haveSiteTheme('bootstrap3');
		$I->loginAsAdmin();

		foreach(array($page, $video) as $id)
		{
			$I->amOnPage('/page.php?id='.$id);
			$I->seeInSource('custom-fields-example');
			$I->dontSeeInSource('<svg id=cfq7x');
			$I->dontSeeInSource("href='javascript:");
		}
	}

	public function legitimateValuesStillRender(AcceptanceTester $I)
	{
		$page = $this->havePage($I, 'Plain cfq7', array(
			'myvideo' => 'WcuRPzB4RNc.youtube',
			'myicon'  => 'fa-check.glyph',
			'mytext'  => 'Plain words',
			'myurl'   => 'https://example.com/cfq7',
		));
		$video = $this->havePage($I, 'Plain file cfq7', array('myvideo' => '{e_MEDIA_FILE}2016-04/clip.mp4'));

		$I->haveSiteTheme('bootstrap3');
		$I->loginAsAdmin();

		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('youtube.com/embed/WcuRPzB4RNc');
		$I->seeInSource('fa-check');
		$I->seeInSource('Plain words');
		$I->seeInSource("href='https://example.com/cfq7'");

		$I->amOnPage('/page.php?id='.$video);
		$I->seeInSource('2016-04/clip.mp4" type="video/mp4"');
	}

	/**
	 * Save $values on page $page through its edit form, as the administrator who holds only the Pages permission.
	 *
	 * @param AcceptanceTester $I
	 * @param int $page
	 * @param array $values form field => value
	 */
	private function saveAsPagesAdmin(AcceptanceTester $I, $page, array $values)
	{
		$I->amSignedInAs(self::PAGES_ADMIN);
		$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
		$I->submitForm('#core-page-form', $values, 'etrigger_submit');
	}

	/**
	 * A page in the fixture chapter, laid out by bootstrap3's 'customfields' layout, with $values stored as they
	 * are given.
	 *
	 * @param AcceptanceTester $I
	 * @param string $title
	 * @param array $values custom field key => value
	 * @return int page_id
	 */
	private function havePage(AcceptanceTester $I, $title, array $values)
	{
		return $I->haveInDatabase('e107_page', array(
			'page_title'     => $title,
			'page_sef'       => strtolower(str_replace(' ', '-', $title)),
			'page_chapter'   => $this->chapterId,
			'page_text'      => 'Body',
			'page_author'    => 1,
			'page_datestamp' => time(),
			'page_class'     => 0,
			'page_password'  => '',
			'page_template'  => 'customfields',
			'page_order'     => 0,
			'page_fields'    => json_encode($values),
		));
	}
}
