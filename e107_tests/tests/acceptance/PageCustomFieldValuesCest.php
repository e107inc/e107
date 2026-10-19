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
		$I->assertSame(1, json_decode($stored, true)['__trust__']['mytext']);
	}

	public function aLowerAdministratorsSaveKeepsTheValuesTheyLeftAlone(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap3');
		$page = $this->havePage($I, 'Kept cfq7', array('mytext' => 'Text'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 250), function () use ($I, $page)
		{
			$I->amSignedInAs();
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(
				'page_fields__mytext'  => 'Plain <b id="cfq7k" onclick="cfq7()">kept</b>',
				'page_fields__myemail' => 'a@example.com <b id="cfq7e" onclick="cfq7()">e</b>',
			), 'etrigger_submit');

			$this->saveAsPagesAdmin($I, $page, array('page_fields__myemail' => 'b@example.com <b id="cfq7c" onclick="cfq7()">c</b>'));
		});

		$I->amSignedInAs();
		$I->amOnPage('/page.php?id='.$page);
		$I->seeElement('#cfq7k[onclick]');
		$I->seeElement('#cfq7c');
		$I->dontSeeElement('#cfq7c[onclick]');

		$stored = json_decode($I->grabFromDatabase('e107_page', 'page_fields', array('page_id' => $page)), true);
		$I->assertSame(array(2, 1), array($stored['__trust__']['mytext'], $stored['__trust__']['myemail']));
	}

	public function aHigherAdministratorsSaveDoesNotPromoteTextTheyLeftAlone(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap3');
		$page = $this->havePage($I, 'Typed cfq7', array('mytext' => 'Text'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 250), function () use ($I, $page)
		{
			$this->saveAsPagesAdmin($I, $page, array('page_fields__mytext' => '&lt;b id="cfq7p" onclick="cfq7()"&gt;typed&lt;/b&gt;'));
		});

		$I->haveSitePrefsDuring(array('post_html' => 250, 'post_script' => 250), function () use ($I, $page)
		{
			$this->saveAsPagesAdmin($I, $page, array('page_fields__myemail' => '&#60;b id="cfq7q" onclick="cfq7()"&#62;typed&#60;/b&#62;'));

			$I->amSignedInAs();
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(), 'etrigger_submit');
		});

		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->dontSeeElement('#cfq7p');
		$I->dontSeeElement('#cfq7q');
	}

	/**
	 * Text holding the '+' and '%3C' the page form's {@see e_parse::toForm()} would rewrite stays text after the main
	 * administrator saves the page untouched, whichever state the editor is in.
	 */
	public function aHigherAdministratorsSaveDoesNotPromoteTextTheFormWouldRewrite(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap3');
		$page = $this->havePage($I, 'Rewritten cfq7', array('mytext' => 'Text'));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 250), function () use ($I, $page)
		{
			$this->saveAsPagesAdmin($I, $page, array('page_fields__mytext' => '&lt;b id="cfq7z" onclick="cfq7()"&gt;1+1&lt;/b&gt;%3Cb id="cfq7y" onclick="cfq7()"%3Ey'));
		});

		$I->haveSitePrefsDuring(array('post_html' => 250, 'post_script' => 250), function () use ($I, $page)
		{
			$I->amSignedInAs();
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(), 'etrigger_submit');
		});

		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->dontSeeElement('#cfq7z');
		$I->dontSeeElement('#cfq7y');
	}

	/**
	 * An icon starting with '[html]', whose entities the page form shows as they are written, keeps the level its
	 * author stored it at after the main administrator saves the page untouched, so its quotes go on printing encoded.
	 */
	public function aHigherAdministratorsSaveDoesNotPromoteAnHtmlValueTheFormShowsEncoded(AcceptanceTester $I)
	{
		$I->haveSiteTheme('bootstrap3');
		$value = '[html]x&#039; id=&#039;cfq7w&#039; onerror=&#039;cfq7()&#039; y&amp;.png';
		$page = $this->havePage($I, 'Icon cfq7', array('myicon' => $value, '__trust__' => array('myicon' => 1)));

		$I->haveSitePrefsDuring(array('post_html' => 250, 'post_script' => 250), function () use ($I, $page)
		{
			$I->amSignedInAs();
			$I->amOnPage('/e107_admin/cpage.php?mode=page&action=edit&id='.$page);
			$I->submitForm('#core-page-form', array(), 'etrigger_submit');
		});

		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->dontSeeElement('#cfq7w[onerror]');

		$stored = json_decode($I->grabFromDatabase('e107_page', 'page_fields', array('page_id' => $page)), true);
		$I->assertSame(array($value, 1), array($stored['myicon'], $stored['__trust__']['myicon']));
	}

	/**
	 * A value holding a script, saved untouched by an administrator without script access, is kept with no warning that
	 * it was refused.
	 */
	public function aLowerAdministratorsUntouchedSaveKeepsAScriptWithoutWarningOfIt(AcceptanceTester $I)
	{
		$value = 'Kept<script id=&quot;cfq7w&quot;>var cfq7 = 3;</script>';
		$page = $this->havePage($I, 'Script cfq7', array('mytext' => $value, '__trust__' => array('mytext' => 2)));

		$I->haveSitePrefsDuring(array('post_html' => 254, 'post_script' => 250), function () use ($I, $page)
		{
			$this->saveAsPagesAdmin($I, $page, array());
			$I->dontSee('permission to use');
		});

		$stored = json_decode($I->grabFromDatabase('e107_page', 'page_fields', array('page_id' => $page)), true);
		$I->assertSame(array($value, 2), array($stored['mytext'], $stored['__trust__']['mytext']));
	}

	public function anUntouchedSaveStoresEachValueAgainByteForByte(AcceptanceTester $I)
	{
		$values = array('mytext' => 'java[p][/p]script:cfq7() [p][/p]<b>bold</b>', 'myurl' => 'https://example.com/cfq7[h][/h]', 'myemail' => 'a@example.com [p][/p]');
		$page = $this->havePage($I, 'Bytes cfq7', $values + array('__trust__' => array('mytext' => 2, 'myurl' => 1)));

		$this->saveAsPagesAdmin($I, $page, array());

		$stored = json_decode($I->grabFromDatabase('e107_page', 'page_fields', array('page_id' => $page)), true);
		$I->assertSame($values, array_intersect_key($stored, $values));
		$I->assertSame(array('mytext' => 2, 'myurl' => 1), array_intersect_key($stored['__trust__'], $values));
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

	public function storedMarkupNeverRunsForTheMainAdministrator(AcceptanceTester $I)
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
			$I->dontSeeElement('svg#cfq7x');
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

	public function aValueWithNoLevelPrintsItsFormattingButNotItsScript(AcceptanceTester $I)
	{
		$page = $this->havePage($I, 'Legacy cfq418', array(
			'mybbarea' => '[html]<p><b id="cfq418b">Sale</b> <img id="cfq418i" src="x.png" onerror="cfq418()"></p>'
				.'<script id="cfq418s">var cfq418 = 1;</script>[/html]',
			'mytext'   => '<b id="cfq418t" onclick="cfq418()">Text</b><script>var cfq418 = 1;</script>',
			'myurl'    => '<b id="cfq418u">Link</b>',
			'myemail'  => '<b id="cfq418e">Mail</b>',
			'mynumber' => '<b id="cfq418n">1</b>',
		));

		$I->haveSiteTheme('bootstrap3');
		$I->amSignedInAs();
		$I->amOnPage('/page.php?id='.$page);
		$I->seeInSource('custom-fields-example');
		$I->seeElement('b#cfq418b');
		$I->seeElement('img#cfq418i');
		$I->dontSeeElement('img#cfq418i[onerror]');
		$I->dontSeeInSource('var cfq418');

		foreach(array('t', 'u', 'e', 'n') as $field)
		{
			$I->seeElement('b#cfq418'.$field);
		}

		$I->dontSeeElement('b#cfq418t[onclick]');
		$I->dontSee('permission to use');
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
