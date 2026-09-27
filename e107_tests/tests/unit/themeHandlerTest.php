<?php
	/**
	 * Created by PhpStorm.
	 * Date: 2/7/2019
	 * Time: 5:03 PM
	 */


	class themeHandlerTest extends \Codeception\Test\Unit
	{

		/** @var themeHandler */
		protected $th;

		/** @var bool */
		private static $themeConfigStubDeclared = false;

		protected function _before()
		{

			try
			{
				$this->th = $this->make('themeHandler');
			}
			catch(Exception $e)
			{
				$this->fail("Couldn't load themeHandler object");
			}

		}

		protected function _after()
		{
			if(class_exists('theme_config', false))
			{
				theme_config::$fields = null;
			}
		}

		public function testSetThemeConfig()
		{
			$config        = e107::getThemeConfig(e107::getPref('sitetheme'));
			$siteThemePref = e107::getConfig()->get('sitetheme_pref');
			$posted        = $_POST;

			$this->th->id             = e107::getPref('sitetheme');
			$this->th->themeConfigObj = $this->themeConfigStub();

			$_POST = array('themeHandlerTest_text' => 'posted');

			try
			{
				e107::getConfig()->set('sitetheme_pref', array('themeHandlerTest_text' => 'legacy'))->save(false, true, false);

				$this->th->setThemeConfig();
				$stored = $config->getPref();
				$legacy = e107::getConfig()->get('sitetheme_pref');
			}
			finally
			{
				$_POST = $posted;
				self::undoThemeConfigSave(array($config), $siteThemePref);
			}

			$this->assertEmpty($legacy, 'a successful save of the site theme has to retire the legacy preference');
			$this->assertSame('posted', $stored['themeHandlerTest_text']);
			$this->assertSame('', $stored['themeHandlerTest_checkbox']);
			$this->assertSame(array(e_LANGUAGE => ''), $stored['themeHandlerTest_multilan']);
			$this->assertSame(array(), $stored['themeHandlerTest_checkboxes']);
			$this->assertSame(array(), $stored['themeHandlerTest_optarray']);
			$this->assertSame(array(), $stored['themeHandlerTest_json']);
			$this->assertSame(array(), $stored['themeHandlerTest_lanlist']);
			$this->assertSame(array(), $stored['themeHandlerTest_layouts']);
			$this->assertSame('', $stored['themeHandlerTest_dropdown']);

			$markup = array_filter(array_keys(self::themeConfigFields()), 'is_numeric');

			$this->assertNotSame(array(), array_diff($markup, array(0)), 'e_pref::setData() drops the key 0 on its own, so the declarations must carry a markup row past the first');
			$this->assertSame(array(), array_filter(array_keys($stored), 'is_numeric'), 'a numeric-keyed row is raw markup, not a field declaration');
		}

		public function testSetThemeConfigMultilanFieldHoldingAString()
		{
			$config        = e107::getThemeConfig(e107::getPref('sitetheme'));
			$siteThemePref = e107::getConfig()->get('sitetheme_pref');
			$posted        = $_POST;
			$stored        = array();

			$this->th->id             = e107::getPref('sitetheme');
			$this->th->themeConfigObj = $this->themeConfigStub();

			$_POST = array('themeHandlerTest_multilan' => array(e_LANGUAGE => 'posted'));

			try
			{
				foreach(array('legacy', '') as $before)
				{
					$config->setPref('themeHandlerTest_multilan', $before)->save(false, true, false);

					$this->th->setThemeConfig();

					$stored[$before] = $config->getPref('themeHandlerTest_multilan');
				}
			}
			finally
			{
				$_POST = $posted;
				self::undoThemeConfigSave(array($config), $siteThemePref);
			}

			$this->assertSame(array('legacy' => array(e_LANGUAGE => 'posted'), '' => array(e_LANGUAGE => 'posted')), $stored);
		}

		public function testSetThemeConfigReportsAFailedSave()
		{
			$theme  = e107::getPref('sitetheme');
			$posted = $_POST;

			$this->th->id             = $theme;
			$this->th->themeConfigObj = $this->themeConfigStub();

			$_POST = array();

			try
			{
				e107::getThemeConfig($theme)->addValidationError('themeHandlerTest');

				$result = $this->th->setThemeConfig();
			}
			finally
			{
				$_POST = $posted;
				self::forgetThemeConfig($theme);
			}

			$this->assertFalse($result);
		}

		public function testSetThemeConfigWritesNoPreferenceRowForAThemeDeclaringNoFields()
		{
			$theme  = 'themeHandlerTestNoFields';
			$posted = $_POST;

			$this->th->id             = $theme;
			$this->th->themeConfigObj = $this->themeConfigStub(array());

			$_POST = array();

			try
			{
				$result = $this->th->setThemeConfig();
				$rows   = e107::getDb()->count('core', '(*)', "WHERE e107_name = 'theme_" . $theme . "'");
			}
			finally
			{
				$_POST = $posted;
				e107::getDb()->delete('core', "e107_name = 'theme_" . $theme . "'");
				self::forgetThemeConfig($theme);
			}

			$this->assertSame(0, $rows, 'a theme declaring no fields has nothing to store, so nothing may be written for it');
			$this->assertSame(0, $result);
		}

		public function testSetThemeConfigRetiresTheLegacyPreferenceWithNothingToStore()
		{
			$theme         = e107::getPref('sitetheme');
			$config        = e107::getThemeConfig($theme);
			$siteThemePref = e107::getConfig()->get('sitetheme_pref');
			$posted        = $_POST;

			$this->th->id             = $theme;
			$this->th->themeConfigObj = $this->themeConfigStub(array());

			$_POST = array();

			try
			{
				$config->setPref('themeHandlerTest_text', 'stored')->save(false, true, false);
				e107::getConfig()->set('sitetheme_pref', array('themeHandlerTest_text' => 'legacy'))->save(false, true, false);

				$result = $this->th->setThemeConfig();
				$legacy = e107::getConfig()->get('sitetheme_pref');
			}
			finally
			{
				$_POST = $posted;
				self::undoThemeConfigSave(array($config), $siteThemePref);
			}

			$this->assertSame(0, $result);
			$this->assertEmpty($legacy, 'the legacy preference is retired whenever the theme preferences stand as they should, not only when a write happened');
		}

		public function testSetThemeConfigKeepsTheLegacyPreferenceWhileTheThemeStoresNothing()
		{
			$theme         = e107::getPref('sitetheme');
			$config        = e107::getThemeConfig($theme);
			$siteThemePref = e107::getConfig()->get('sitetheme_pref');
			$original      = $config->getPref();
			$posted        = $_POST;

			$this->th->id             = $theme;
			$this->th->themeConfigObj = $this->themeConfigStub(array());

			$_POST = array();

			try
			{
				foreach(array_keys($original) as $field)
				{
					$config->removePref($field);
				}

				$config->save(false, true, false);
				e107::getConfig()->set('sitetheme_pref', array('themeHandlerTest_text' => 'legacy'))->save(false, true, false);

				$result   = $this->th->setThemeConfig();
				$legacy   = e107::getConfig()->get('sitetheme_pref');
				$stored   = $config->getPref();
				$fallback = e107::getThemePref('themeHandlerTest_text');
			}
			finally
			{
				$_POST = $posted;
				$config->setPref($original)->save(false, true, false);
				self::undoThemeConfigSave(array($config), $siteThemePref);
			}

			$this->assertSame(0, $result);
			$this->assertSame(array(), $stored, 'the theme has to end the save storing nothing, or this proves nothing');
			$this->assertSame(array('themeHandlerTest_text' => 'legacy'), $legacy, 'nothing replaced the legacy preference, so retiring it would drop the values e107::getThemePref() falls back to');
			$this->assertSame('legacy', $fallback, 'e107::getThemePref() is the reader that decides this, on the same test of the theme\'s own preferences');
		}

		public function testSetStyleReportsAFailedSaveOnceAndInTheAdminsWords()
		{
			e107::includeLan(e_LANGUAGEDIR . e_LANGUAGE . '/admin/lan_theme.php');

			$theme         = e107::getPref('sitetheme');
			$siteThemePref = e107::getConfig()->get('sitetheme_pref');
			$posted        = $_POST;
			$mes           = e107::getMessage();

			$this->th->id             = $theme;
			$this->th->themeConfigObj = $this->themeConfigStub();

			$_POST = array();
			$mes->reset(false, false, true);

			try
			{
				e107::getThemeConfig($theme)->addValidationError('themeHandlerTest');

				$this->th->setStyle();

				$errors  = $mes->get(E_MESSAGE_ERROR, 'default', true, false);
				$success = $mes->hasMessage(E_MESSAGE_SUCCESS, 'default', true);
			}
			finally
			{
				$_POST = $posted;
				e107::getConfig()->removePostedData();
				self::forgetThemeConfig($theme);
				self::undoThemeConfigSave(array(e107::getThemeConfig($theme)), $siteThemePref);
				$mes->reset(false, false, true);
			}

			$this->assertFalse($success, 'a write that failed must not be reported as saved');
			$this->assertSame(array(LAN_THEME_OPTIONS_NOT_SAVED), $errors, 'the admin should get one message, not the wording e_pref was asked to keep off the screen');
		}

		public function testSetStyleStaysSilentWhenAPreV214ThemeReportsFalse()
		{
			require_once(__DIR__ . '/fixtures/ThemeHandlerLegacyThemeConfig.php');

			$posted = $_POST;
			$mes    = e107::getMessage();

			$this->th->id             = e107::getPref('sitetheme');
			$this->th->themeConfigObj = new ThemeHandlerLegacyThemeConfig();

			$_POST = array();
			$mes->reset(false, false, true);

			try
			{
				$this->th->setStyle();

				$reported = $mes->hasMessage(false, 'default', true);
			}
			finally
			{
				$_POST = $posted;
				e107::getConfig()->removePostedData();
				$mes->reset(false, false, true);
			}

			$this->assertFalse($reported, 'a pre-v2.1.4 process() returning false means nothing changed, not that the write failed');
		}

		public function testSetThemeConfigWritesToTheThemeBeingConfigured()
		{
			$otherTheme    = 'themeHandlerTestTheme';
			$otherConfig   = e107::getThemeConfig($otherTheme);
			$siteConfig    = e107::getThemeConfig(e107::getPref('sitetheme'));
			$siteThemePref = e107::getConfig()->get('sitetheme_pref');
			$posted        = $_POST;

			$this->th->id             = $otherTheme;
			$this->th->themeConfigObj = $this->themeConfigStub();

			$_POST = array('themeHandlerTest_text' => 'posted');

			try
			{
				e107::getConfig()->set('sitetheme_pref', array('themeHandlerTest_text' => 'legacy'))->save(false, true, false);

				$this->th->setThemeConfig();

				$stored     = $otherConfig->getPref();
				$siteStored = $siteConfig->getPref();
				$legacy     = e107::getConfig()->get('sitetheme_pref');
			}
			finally
			{
				$_POST = $posted;
				self::undoThemeConfigSave(array($otherConfig, $siteConfig), $siteThemePref);
				e107::getDb()->delete('core', "e107_name='theme_" . $otherTheme . "'");
				$otherConfig->clearPrefCache();
			}

			$this->assertArrayHasKey('themeHandlerTest_text', $stored, 'the theme being configured has to be the one that received the posted field');
			$this->assertSame('posted', $stored['themeHandlerTest_text']);
			$this->assertArrayNotHasKey('themeHandlerTest_text', $siteStored);
			$this->assertSame(array('themeHandlerTest_text' => 'legacy'), $legacy);
		}

		public function testThemeConfigEmptyValueMirrorsRenderElement()
		{
			$frm    = e107::getForm();
			$helper = new ReflectionMethod('themeHandler', 'themeConfigEmptyValue');
			$helper->setAccessible(true);

			foreach(self::renderedThemeConfigFields() as $field => $data)
			{
				$html = $frm->renderElement($field, '', $data);

				$this->assertSame(1, preg_match("/name='([^']+)'/", $html, $match), $field);
				$this->assertSame(strpos($match[1], $field . '[') === 0, is_array($helper->invoke(null, $data)), $field);
			}
		}

		public function testEveryNumericKeyIsAMarkupRow()
		{
			$predicate = new ReflectionMethod('themeHandler', 'isThemeConfigMarkupRow');
			$predicate->setAccessible(true);

			foreach(array(0, 1, '0', '2', '0.5', '-1') as $markup)
			{
				$this->assertTrue($predicate->invoke(null, $markup), var_export($markup, true));
			}

			foreach(array('themeHandlerTest_text', 'caption', '') as $declaration)
			{
				$this->assertFalse($predicate->invoke(null, $declaration), var_export($declaration, true));
			}
		}

		public function testRenderThemeConfigMarkupRowOmittingAKey()
		{
			$this->th->id             = e107::getPref('sitetheme');
			$this->th->themeConfigObj = $this->themeConfigStub(array(
				array('caption' => 'Markup', 'html' => "<input type='text' name='themeHandlerTest_raw' />"),
				array(),
				"<input type='text' name='themeHandlerTest_scalar' />",
			));

			$empty    = "<tr><td><b></b>:</td><td colspan='2'><div class='field-help'></div></td></tr>";
			$expected = "<tr><td><b>Markup</b>:</td><td colspan='2'><input type='text' name='themeHandlerTest_raw' /><div class='field-help'></div></td></tr>"
				. $empty . $empty;

			$this->assertSame($expected, $this->th->renderThemeConfig(), 'a markup row leaving out caption, html or help renders as though it set them empty, the way _blank ships its second row, and a row that is not an array at all renders empty rather than bringing the page down');
		}

		/**
		 * Field declarations whose rendered input name decides which empty {@see themeHandler::setThemeConfig()} stores when the field is absent from the POST.
		 *
		 * @return array
		 */
		public static function renderedThemeConfigFields()
		{
			return array(
				'text_plain'              => array('title' => 'T', 'type' => 'text'),
				'checkbox_plain'          => array('title' => 'T', 'type' => 'checkbox'),
				'checkboxes_plain'        => array('title' => 'T', 'type' => 'checkboxes', 'writeParms' => array('one' => 'One', 'two' => 'Two')),
				'comma_plain'             => array('title' => 'T', 'type' => 'comma', 'writeParms' => array('one' => 'One')),
				'userclasses_plain'       => array('title' => 'T', 'type' => 'userclasses'),
				'userclass_plain'         => array('title' => 'T', 'type' => 'userclass'),
				'userclass_multiple'      => array('title' => 'T', 'type' => 'userclass', 'writeParms' => array('multiple' => true)),
				'country_plain'           => array('title' => 'T', 'type' => 'country'),
				'country_multiple'        => array('title' => 'T', 'type' => 'country', 'writeParms' => array('multiple' => true)),
				'country_json'            => array('title' => 'T', 'type' => 'country', 'writeParms' => '{"multiple":1}'),
				'dropdown_plain'          => array('title' => 'T', 'type' => 'dropdown', 'writeParms' => array('one' => 'One')),
				'dropdown_optarray'       => array('title' => 'T', 'type' => 'dropdown', 'writeParms' => array('optArray' => array('one' => 'One'), 'multiple' => true)),
				'dropdown_json'           => array('title' => 'T', 'type' => 'dropdown', 'writeParms' => '{"optArray":{"one":"One"},"multiple":1}'),
				'dropdown_options_string' => array('title' => 'T', 'type' => 'dropdown', 'writeParms' => array('one' => 'One', '__options' => 'multiple=1')),
				'dropdown_parms_string'   => array('title' => 'T', 'type' => 'dropdown', 'writeParms' => 'multiple=1'),
				'language_options_string' => array('title' => 'T', 'type' => 'language', 'writeParms' => '__options[multiple]=1'),
				'language_options_array'  => array('title' => 'T', 'type' => 'language', 'writeParms' => array('__options' => array('multiple' => true))),
				'language_optarray'       => array('title' => 'T', 'type' => 'language', 'writeParms' => array('optArray' => array('one' => 'One'), 'multiple' => true)),
			);
		}

		/**
		 * Stands in for a theme's own theme_config class: {@see themeHandler::setThemeConfig()} is why it must carry that name, and {@see themeHandler::loadThemeConfig()} asking class_exists() on that bare name, process-wide, is why it is required on first use rather than declared at file scope.
		 *
		 * @param array|null $fields the declarations {@see theme_config::config()} answers with, or null for {@see themeHandlerTest::themeConfigFields()}
		 * @return object exposing config()
		 */
		protected function themeConfigStub($fields = null)
		{
			if(!self::$themeConfigStubDeclared)
			{
				if(class_exists('theme_config', false))
				{
					$this->markTestSkipped("a theme's own theme_config class already holds the name in this process");
				}

				require_once(codecept_data_dir('themeHandlerTestThemeConfig.php'));
				self::$themeConfigStubDeclared = true;
			}

			theme_config::$fields = $fields;

			return new theme_config();
		}

		/**
		 * The theme configuration rows both save-path tests drive {@see themeHandler::setThemeConfig()} with: the field declarations, and the numeric-keyed raw markup rows {@see themeHandler::renderThemeConfig()} writes out as they stand, the second of them carrying no help, as _blank's own second row does not.
		 *
		 * @return array
		 */
		public static function themeConfigFields()
		{
			return array(
				'themeHandlerTest_text'     => array('title' => 'Text', 'type' => 'text'),
				'themeHandlerTest_checkbox' => array('title' => 'Checkbox', 'type' => 'checkbox'),
				'themeHandlerTest_multilan' => array('title' => 'Multilan', 'type' => 'text', 'multilan' => true),
				'themeHandlerTest_checkboxes' => array('title' => 'Checkboxes', 'type' => 'checkboxes', 'writeParms' => array('one' => 'One', 'two' => 'Two')),
				'themeHandlerTest_optarray' => array('title' => 'Optarray', 'type' => 'dropdown', 'writeParms' => array('optArray' => array('one' => 'One'), 'multiple' => true)),
				'themeHandlerTest_json' => array('title' => 'Json', 'type' => 'dropdown', 'writeParms' => '{"optArray":{"one":"One"},"multiple":1}'),
				'themeHandlerTest_lanlist' => array('title' => 'Lanlist', 'type' => 'lanlist', 'writeParms' => '__options[multiple]=1'),
				'themeHandlerTest_layouts' => array('title' => 'Layouts', 'type' => 'layouts', 'writeParms' => array('multiple' => true)),
				'themeHandlerTest_dropdown' => array('title' => 'Dropdown', 'type' => 'dropdown', 'writeParms' => array('one' => 'One')),
				array('caption' => 'Markup', 'html' => "<input type='text' name='themeHandlerTest_raw' />", 'help' => ''),
				array('caption' => 'More markup', 'html' => "<input type='text' name='themeHandlerTest_raw2' />"),
			);
		}

		/**
		 * Drops the preference object {@see e107::getThemeConfig()} keeps for a theme, so a test that spoiled one hands the next a clean one.
		 *
		 * @param string $theme
		 * @return void
		 */
		private static function forgetThemeConfig($theme)
		{
			$registry = new ReflectionProperty('e107', '_theme_config_arr');
			$registry->setAccessible(true);

			$configs = $registry->getValue();
			unset($configs[$theme]);
			$registry->setValue(null, $configs);
		}

		/**
		 * Drops the preferences the tests write and puts back the core preference {@see themeHandler::setThemeConfig()} clears.
		 *
		 * @param e_theme_pref[] $configs
		 * @param mixed $siteThemePref
		 * @return void
		 */
		private static function undoThemeConfigSave(array $configs, $siteThemePref)
		{
			foreach($configs as $config)
			{
				foreach(array_keys(self::themeConfigFields()) as $field)
				{
					$config->removePref($field);
				}

				$config->save(false, true, false);
			}

			e107::getConfig()->set('sitetheme_pref', $siteThemePref)->save(false, true, false);
		}
		/**
		 * A theme declaring no layouts renders no custom-pages field, so nothing named custompages reaches {@see themeHandler::postObserver()}.
		 * @see https://github.com/e107inc/e107/issues/6064
		 */
		public function testPostObserverWithoutCustomPagesInThePost()
		{
			$run = $this->postStyleSubmission(e107::getPref('sitetheme'), array());

			$this->assertSame(array(), $run['warnings'], implode("\n", $run['warnings']));
			$this->assertSame($run['before']['sitetheme_custompages'], $run['stored']['sitetheme_custompages']);
		}

		/**
		 * The four preferences {@see themeHandler::postObserver()} writes under submit_style describe the site theme, so a post naming another theme has to be refused.
		 * @see https://github.com/e107inc/e107/issues/6177
		 */
		public function testPostObserverRefusesAStyleSubmissionNamingAnotherTheme()
		{
			$siteTheme = e107::getPref('sitetheme');
			$run = $this->postStyleSubmission($siteTheme === 'bootstrap3' ? 'voux' : 'bootstrap3');

			$this->assertSame($run['before'], $run['stored'], 'a post naming another theme must leave every site-scoped theme preference as it was');
			$this->assertSame(array(TPVLAN_REFUSED_NOT_SITE_THEME), $run['errors'], 'the administrator has to be told why the options were not saved');
		}

		/**
		 * The positive control for {@see themeHandlerTest::testPostObserverRefusesAStyleSubmissionNamingAnotherTheme()}: a refusal that refused everything would pass it too.
		 */
		public function testPostObserverSavesAStyleSubmissionNamingTheSiteTheme()
		{
			$run = $this->postStyleSubmission(e107::getPref('sitetheme'));

			$this->assertSame('themeHandlerTest.css', $run['stored']['themecss'], 'the site theme\'s own submission still writes the stylesheet');
			$this->assertSame('themeHandlerTest_layout', $run['stored']['sitetheme_deflayout'], 'and the default layout with it');
			$this->assertArrayHasKey('themeHandlerTest_layout', $run['stored']['sitetheme_custompages'], 'and the custom pages the form posted');
			$this->assertSame(array(), $run['errors'], 'nothing was refused, so nothing should be reported');
		}

		/**
		 * Two theme directories can differ only in characters the file filter folds onto a hyphen, so the comparison has to be on the names themselves.
		 */
		public function testPostObserverRefusesAThemeWhoseNameOnlyFiltersOntoTheSiteThemes()
		{
			$config    = e107::getConfig();
			$siteTheme = $config->get('sitetheme');

			try
			{
				$config->set('sitetheme', 'themeHandlerTest-theme')->save(false, true, false);
				$run = $this->postStyleSubmission('themeHandlerTest theme');
			}
			finally
			{
				$config->set('sitetheme', $siteTheme)->save(false, true, false);
			}

			$this->assertSame($run['before'], $run['stored'], 'two directories that filter onto one name are still two themes');
			$this->assertSame(array(TPVLAN_REFUSED_NOT_SITE_THEME), $run['errors'], 'and the administrator is told so');
		}

		/**
		 * Drives {@see themeHandler::postObserver()} through submit_style for one posted theme, restoring every preference it wrote.
		 *
		 * @param string $curTheme
		 * @param array|null $values the style fields to post beside curTheme; null posts the full set
		 * @return array before, stored, errors and warnings
		 */
		private function postStyleSubmission($curTheme, $values = null)
		{
			e107::coreLan('theme', true);

			if(!is_array($values))
			{
				$values = array(
					'themecss'       => 'themeHandlerTest.css',
					'layout_default' => 'themeHandlerTest_layout',
					'custompages'    => array('themeHandlerTest_layout' => "/themeHandlerTest\n"),
				);
			}

			$config        = e107::getConfig();
			$themeConfig   = e107::getThemeConfig(e107::getPref('sitetheme'));
			$siteThemePref = $config->get('sitetheme_pref');
			$original      = $config->getPref();
			$posted        = $_POST;
			$mes           = e107::getMessage();
			$warnings      = array();

			$this->th->themeConfigObj = $this->themeConfigStub();

			$before = $this->siteThemePreferences();
			$_POST  = array('curTheme' => $curTheme, 'submit_style' => 1) + $values;

			$mes->reset(false, false, true);
			set_error_handler(function ($no, $str) use (&$warnings) {
				$warnings[] = $str;

				return true;
			});

			try
			{
				$this->th->postObserver();
				$errors = $mes->get(E_MESSAGE_ERROR, 'default', true, false);

				return array(
					'before'   => $before,
					'stored'   => $this->siteThemePreferences(),
					'errors'   => empty($errors) ? array() : (array) $errors,
					'warnings' => $warnings,
				);
			}
			finally
			{
				restore_error_handler();
				$_POST = $posted;
				$config->removePostedData();
				$config->setPref($original)->save(false, true, false);
				self::undoThemeConfigSave(array($themeConfig), $siteThemePref);
				$mes->reset(false, false, true);
			}
		}

		/**
		 * @return array the site-scoped preferences the submit_style branch writes, keyed by preference name
		 */
		private function siteThemePreferences()
		{
			$config = e107::getConfig();
			$stored = array();

			foreach(array('themecss', 'sitetheme_deflayout', 'sitetheme_layouts', 'sitetheme_custompages') as $field)
			{
				$stored[$field] = $config->get($field);
			}

			return $stored;
		}

		public function testFindDefault()
		{
			$result = $this->th->findDefault('bootstrap3');
			$this->assertSame('jumbotron_sidebar_right', $result);

		}

		public function testFindDefaultCSS()
		{
			$result = $this->th->findDefaultCSS('voux');
			$this->assertSame('style.css', $result);

			$result = $this->th->findDefaultCSS('bootstrap5');
			$this->assertSame('https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.2.3/quartz/bootstrap.min.css', $result);

		}

	}
