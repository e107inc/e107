<?php
	/**
	 * Created by PhpStorm.
	 * Date: 2/7/2019
	 * Time: 5:03 PM
	 */


	class themeHandlerTest extends \Test\Unit
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
				self::replaceThemeConfig($theme);
			}

			$this->assertFalse($result);
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
				self::replaceThemeConfig($theme, $this->losingThemePref($theme));

				$this->th->setStyle();

				$errors  = $mes->get(E_MESSAGE_ERROR, 'default', true, false);
				$success = $mes->hasMessage(E_MESSAGE_SUCCESS, 'default', true);
			}
			finally
			{
				$_POST = $posted;
				e107::getConfig()->removePostedData();
				self::replaceThemeConfig($theme);
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
				e107::getDb()->createQueryBuilder()->delete('core')->where('e107_name', 'theme_' . $otherTheme)->execute();
				$otherConfig->clearPrefCache();
			}

			$this->assertArrayHasKey('themeHandlerTest_text', $stored, 'the theme being configured has to be the one that received the posted field');
			$this->assertSame('posted', $stored['themeHandlerTest_text']);
			$this->assertArrayNotHasKey('themeHandlerTest_text', $siteStored);
			$this->assertSame(array('themeHandlerTest_text' => 'legacy'), $legacy);
		}

		public function testRenderThemeConfigMarkupRowWithoutHelpText()
		{
			require_once(__DIR__ . '/fixtures/ThemeHandlerMarkupThemeConfig.php');

			$this->th->id             = e107::getPref('sitetheme');
			$this->th->themeConfigObj = new ThemeHandlerMarkupThemeConfig();

			$text = $this->th->renderThemeConfig();

			$this->assertSame(2, substr_count($text, "<div class='field-help'>"), 'both markup rows have to render a help block');
			$this->assertStringContainsString("<div class='field-help'></div>", $text, "a markup row without help renders an empty help block, the way a field declaration without help already does");
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

		/**
		 * Field declarations whose rendered input name decides which empty {@see themeHandler::setThemeConfig()} stores when the field is absent from the POST; lanlist and layouts are left to the helper's own assertions because a first render of either in a unit context raises on the unrelated core faults #6085 and #6086.
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
		 * @return object exposing config()
		 */
		protected function themeConfigStub()
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

			return new theme_config();
		}

		/**
		 * The theme configuration field declarations both save-path tests drive {@see themeHandler::setThemeConfig()} with.
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
			);
		}

		/**
		 * Stands an object in for the preferences {@see e107::getThemeConfig()} keeps for a theme, or with no object drops what is there, so a test that spoiled one hands the next a clean one.
		 *
		 * @param string $theme
		 * @param e_theme_pref|null $config
		 * @return void
		 */
		private static function replaceThemeConfig($theme, $config = null)
		{
			$registry = new ReflectionProperty('e107', '_theme_config_arr');
			$registry->setAccessible(true);

			$configs = $registry->getValue();

			if($config === null)
			{
				unset($configs[$theme]);
			}
			else
			{
				$configs[$theme] = $config;
			}

			$registry->setValue(null, $configs);
		}

		/**
		 * A theme's preferences that lose every compare-and-swap {@see e_pref::persist()} runs.
		 *
		 * @param string $theme
		 * @return e_theme_pref
		 */
		private function losingThemePref($theme)
		{
			require_once(__DIR__ . '/fixtures/ThemeHandlerLosingThemePref.php');

			return new ThemeHandlerLosingThemePref($theme);
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
/*
		public function testTheme_adminlog()
		{

		}

		public function testPostObserver()
		{

		}

		public function testInstallContent()
		{

		}

		public function testRenderTheme()
		{

		}

		public function testSetAdminStyle()
		{

		}

		public function testRenderThemeInfo()
		{

		}

		public function testRenderUploadForm()
		{

		}
*/
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
/*
		public function testGetThemes()
		{

		}

		public function testRenderOnline()
		{

		}

		public function testShowThemes()
		{

		}

		public function testSetLayouts()
		{

		}

		public function testRenderThemeConfig()
		{

		}

		public function testGetThemeCategory()
		{

		}

		public function testShowPreview()
		{

		}

		public function testLoadThemeConfig()
		{

		}

		public function testParse_theme_php()
		{

		}

		public function testRenderThemeHelp()
		{

		}

		public function testSetAdminTheme()
		{

		}

		public function testRefreshPage()
		{

		}
*/

/*
		public function testThemeUpload()
		{

		}

		public function testInstallContentCheck()
		{

		}

		public function testSetStyle()
		{

		}

		public function testGetMarketplace()
		{

		}

		public function testRenderPresets()
		{

		}

		public function testRenderPlugins()
		{

		}

		public function testThemePreview()
		{

		}

		public function testSetCustomPages()
		{

		}

		public function testGetThemeInfo()
		{

		}

		public function testSetTheme()
		{

		}

	*/


	}
