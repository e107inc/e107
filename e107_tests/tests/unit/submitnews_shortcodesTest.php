<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * Coverage for issue #6286: submitnews.php built its form in PHP, so a theme
 * could reach the page with CSS and nothing else. The markup is now
 * e107_core/templates/submitnews_template.php and the fields behind it are
 * this batch, which is what a theme overrides.
 *
 * The rows the shipped template produces are asserted element by element,
 * because a site that overrides nothing has to keep the page it had.
 */
class submitnews_shortcodesTest extends \Test\Unit
{
	/** @var submitnews_shortcodes */
	protected $sc;

	/** @var array */
	protected $categories = array(
		array('category_id' => '3', 'category_name' => 'Announcements'),
		array('category_id' => '7', 'category_name' => 'Site & <b>news</b>'),
	);

	/** @var array */
	protected $prefsBefore = array();

	/** @var int */
	protected $userBefore = 0;

	protected function _before()
	{
		$this->sc = e107::getScBatch('submitnews');
		$this->sc->setVars(array('categories' => $this->categories, 'min_width' => 0, 'min_height' => 0));
		$this->sc->wrapper('submitnews/form');

		foreach(array('news_subheader', 'subnews_attach', 'upload_enabled', 'upload_class') as $key)
		{
			$this->prefsBefore[$key] = e107::pref('core', $key);
		}

		$this->userBefore = (int) e107::getUser()->getId();

		unset($_POST['submitnews_name'], $_POST['submitnews_email'], $_POST['submitnews_title'],
			$_POST['submitnews_item'], $_POST['submitnews_media'], $_POST['cat_id']);
	}

	protected function _after()
	{
		foreach($this->prefsBefore as $key => $value)
		{
			e107::getConfig('core')->setPref($key, $value);
		}

		e107::getUser()->setId($this->userBefore);
	}

	public function testTheTemplateAndItsBatchExist()
	{
		self::assertInstanceOf('e_shortcode', e107::getScBatch('submitnews'));
		self::assertNotEmpty($this->formTemplate());
	}

	public function testThePageItselfHoldsNoMarkup()
	{
		$source = file_get_contents(e_BASE.'submitnews.php');

		self::assertStringContainsString("e107::getCoreTemplate('submitnews'", $source);
		self::assertStringContainsString("e107::getScBatch('submitnews')", $source);

		foreach(array('forumheader3', 'fborder', '<table', '<tr', '<select', '<input') as $markup)
		{
			self::assertStringNotContainsString($markup, $source, 'submitnews.php builds its own markup again');
		}
	}

	public function testTemplateDrivesEveryFieldThroughAShortcode()
	{
		$template = $this->formTemplate();

		foreach(array('SUBHEADER', 'NAME', 'EMAIL', 'CATEGORY', 'TITLE', 'BODY', 'KEYWORDS',
			         'SUMMARY', 'DESCRIPTION', 'MEDIA', 'ATTACH', 'SUBMIT') as $field)
		{
			self::assertStringContainsString('{SUBMITNEWS_'.$field.'}', $template);
			self::assertTrue(method_exists($this->sc, 'sc_submitnews_'.strtolower($field)),
				'{SUBMITNEWS_'.$field.'} has no shortcode behind it');
		}
	}

	public function testOnlyTheConditionalRowsNeedAWrapper()
	{
		$template = $this->formTemplate();

		foreach(array('CATEGORY', 'TITLE', '135', 'SUBNEWSLAN_9', 'SUMMARY', 'META_DESCRIPTION', 'SUBNEWSLAN_13') as $label)
		{
			self::assertStringContainsString('{LAN='.$label.'}', $template,
				'a theme overriding only the template body has to keep this label');
		}

		self::assertSame(array('SUBMITNEWS_NAME', 'SUBMITNEWS_EMAIL', 'SUBMITNEWS_ATTACH'),
			array_keys(e107::templateWrapper('submitnews/form')));
	}

	public function testFormKeepsItsLegacyMarkup()
	{
		$rendered = $this->render();

		self::assertStringContainsString("<form id='dataform' method='post' action='".e_SELF."' enctype='multipart/form-data' onsubmit='return frmVerify()'>", $rendered);
		self::assertStringContainsString("<table class='table fborder'>", $rendered);
		self::assertStringContainsString("<td colspan='2' style='text-align:center' class='forumheader'>", $rendered);
		self::assertStringContainsString("<input class='btn btn-success button' type='submit' name='submitnews_submit' value='".LAN_136."' />", $rendered);
		self::assertStringNotContainsString('e-token', $rendered, 'the token comes from e_token_injector, not from the template');
	}

	public function testEveryRowCarriesItsLabelAndCellClasses()
	{
		$rendered = $this->render();

		$labels = array(LAN_NAME, LAN_EMAIL, LAN_CATEGORY, LAN_TITLE, LAN_135,
			SUBNEWSLAN_9, LAN_SUMMARY, LAN_META_DESCRIPTION, SUBNEWSLAN_13);

		foreach($labels as $label)
		{
			self::assertStringContainsString("<td style='width:20%' class='forumheader3'>".$label."</td>", $rendered);
		}

		self::assertStringContainsString("<td style='width:80%' class='forumheader3'>", $rendered);
	}

	public function testGuestFieldsRenderForAGuest()
	{
		e107::getUser()->setId(0);

		self::assertStringContainsString("name='submitnews_name'", $this->sc->sc_submitnews_name());
		self::assertStringContainsString("name='submitnews_email'", $this->sc->sc_submitnews_email());
		self::assertStringContainsString("<td style='width:20%' class='forumheader3'>".LAN_NAME."</td>", $this->render());
	}

	public function testGuestRowsVanishForAMember()
	{
		e107::getUser()->setId(1);

		self::assertSame('', $this->sc->sc_submitnews_name());
		self::assertSame('', $this->sc->sc_submitnews_email());

		$rendered = $this->render();
		self::assertStringNotContainsString("name='submitnews_name'", $rendered);
		self::assertStringNotContainsString("<td style='width:20%' class='forumheader3'>".LAN_NAME."</td>", $rendered);
	}

	public function testCategoriesRenderAsTheLegacySelect()
	{
		$_POST['cat_id'] = '7';

		$select = $this->sc->sc_submitnews_category();

		$tp = e107::getParser();

		self::assertStringStartsWith("<select name='cat_id' class='tbox form-control'>", $select);
		self::assertStringContainsString("<option value='3' >".$tp->toHTML('Announcements', false, 'defs')."</option>", $select);
		self::assertStringContainsString("<option value='7' selected='selected'>".$tp->toHTML('Site & <b>news</b>', false, 'defs')."</option>", $select);
		self::assertStringEndsWith('</select>', $select);
	}

	public function testNoCategoriesLeavesTheNotice()
	{
		$this->sc->setVars(array('categories' => array()));

		self::assertSame(NWSLAN_10, $this->sc->sc_submitnews_category());
	}

	public function testAttachmentRowIsAbsentWhenTheSiteTakesNoAttachments()
	{
		e107::getConfig('core')->setPref('subnews_attach', 0);

		self::assertSame('', $this->sc->sc_submitnews_attach());
		self::assertStringNotContainsString("name='file_userfile[]'", $this->render());
	}

	public function testAttachmentRowCarriesTheMinimumDimensions()
	{
		e107::getConfig('core')->setPref('subnews_attach', 1);
		e107::getConfig('core')->setPref('upload_enabled', 1);
		e107::getConfig('core')->setPref('upload_class', e_UC_PUBLIC);
		$this->sc->setVars(array('min_width' => 640, 'min_height' => 480));

		if(!deftrue('FILE_UPLOADS'))
		{
			self::markTestSkipped('file uploads are off in this PHP');
		}

		$attach = $this->sc->sc_submitnews_attach();

		self::assertStringContainsString("<input class='tbox' type='file' name='file_userfile[]' multiple='multiple' />", $attach);
		self::assertStringContainsString('640px × 480px', $attach);
	}

	public function testSubheaderComesFromThePreference()
	{
		e107::getConfig('core')->setPref('news_subheader', 'Send us [b]your[/b] news');

		self::assertStringContainsString('>your</strong>', $this->sc->sc_submitnews_subheader());

		e107::getConfig('core')->setPref('news_subheader', '');

		self::assertSame('', $this->sc->sc_submitnews_subheader());
	}

	public function testMediaFieldsStayEightAndAreReachableFromTheForm()
	{
		$media = $this->sc->sc_submitnews_media();

		self::assertSame(8, substr_count($media, "<div class='form-group'>"));
		self::assertStringContainsString("name='submitnews_media[0]'", $media);
		self::assertStringContainsString("name='submitnews_media[7]'", $media);
	}

	protected function formTemplate()
	{
		$template = e107::getCoreTemplate('submitnews', null, true, true);

		return varset($template['form'], '');
	}

	/**
	 * @return string the shipped template as the page renders it
	 */
	protected function render()
	{
		return e107::getParser()->parseTemplate($this->formTemplate(), true, $this->sc);
	}
}
