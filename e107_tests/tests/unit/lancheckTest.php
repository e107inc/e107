<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 1/30/2019
	 * Time: 12:17 PM
	 */


	class lancheckTest extends \Codeception\Test\Unit
	{

		/** @var lancheck */
		protected $lan;

		/** @var string Scratch language file for the write_lanfile() tests. */
		protected $target;

		/** @var string What the confirmation screen rendered on the last write. */
		protected $rendered;

		/** @var string Scratch plugin directory for the language-pack tests. */
		protected $scratchPlugin;

		protected function _before()
		{
			require_once(e_ADMIN."lancheck.php");

			try
			{
				$this->lan = $this->make('lancheck');
			}
			catch (Exception $e)
			{
				$this->fail("Couldn't load lancheck object");
			}

			// write_lanfile() ends in `global $ns; $ns->tablerender(...)`, and the
			// unit bootstrap loads class2.php inside a method, so $ns never reaches
			// the global scope.
			global $ns;
			$ns = e107::getRender();

			$this->target = e_LANGUAGEDIR.'e107_tests_lancheck_target.php';
		}

		protected function _after()
		{
			unset($_SESSION['lancheck']);
			unset($_SESSION['lancheck-edit-file'], $_POST['newlang'], $_POST['newdef']);
			unset($_GET['sub'], $_GET['lan'], $_GET['file'], $_GET['type']);
			e107::getMessage()->reset();

			if($this->target && is_file($this->target))
			{
				unlink($this->target);
			}

			if($this->scratchPlugin && is_dir($this->scratchPlugin))
			{
				foreach(glob($this->scratchPlugin.'languages/*') as $file)
				{
					unlink($file);
				}

				rmdir($this->scratchPlugin.'languages');
				rmdir($this->scratchPlugin);
				$this->scratchPlugin = null;
			}
		}

		/**
		 * Drive write_lanfile() over the scratch file and hand back what it wrote.
		 *
		 * @param array $newdef  values for the hidden newdef[] field (constant names)
		 * @param array $newlang values for the newlang[] textareas (translations)
		 * @param bool  $fresh   start from an empty file rather than reusing the last one
		 * @return string the generated language file
		 */
		protected function writeLanFile($newdef, $newlang, $fresh = true)
		{
			if($fresh)
			{
				file_put_contents($this->target, "<?php\n");
			}

			$_SESSION['lancheck-edit-file'] = $this->target;
			$_POST['newdef']                = $newdef;
			$_POST['newlang']               = $newlang;

			ob_start();
			$this->lan->write_lanfile('English');
			$this->rendered = ob_get_clean();

			return file_get_contents($this->target);
		}

		/**
		 * Include the generated file in a clean subprocess and report what it did.
		 *
		 * A syntax check alone proves nothing here: an injected payload is valid
		 * PHP. This runs the file and reports the constant's value plus anything
		 * the file echoed on its own account.
		 *
		 * @param string $constant the constant the file is expected to define
		 * @return array {stdout: string, exit: int}
		 */
		protected function includeGenerated($constant)
		{
			$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

			if(in_array('exec', $disabled, true))
			{
				$this->markTestSkipped('exec() is disabled here, so the generated file cannot be executed.');
			}

			$php = 'error_reporting(0);'
				.'include '.var_export($this->target, true).';'
				.'echo "[VALUE]".(defined('.var_export($constant, true).') ? constant('.var_export($constant, true).') : "[UNDEFINED]");';

			$output = array();
			$status = 0;
			exec('php -r '.escapeshellarg($php).' 2>&1', $output, $status);

			return array('stdout' => implode("\n", $output), 'exit' => $status);
		}

		/** @return int 0 when the generated file is syntactically valid PHP */
		protected function lintGenerated()
		{
			$output = array();
			$status = 0;
			exec('php -l '.escapeshellarg($this->target).' 2>&1', $output, $status);

			return $status;
		}


/*
		public function testCheck_lan_errors()
		{

		}

		public function testCheckLog()
		{

		}
*/
		public function testFill_phrases_array()
		{

			$strings =
				'define("LAN1", "Főadminisztrátor");'."\n".
				'define("LAN2", "Hői");'."\n".
				'define("LAN3", "Rendszerinformáció");'."\n".
				'define("LAN4", "Felhasználó");'."\n".
				'define("LAN5", "Regisztrált felhasználó");';

			$expected = array (
				'orig' =>
					array (
						'LAN1' => 'Főadminisztrátor',
						'LAN2' => 'Hői',
						'LAN3' => 'Rendszerinformáció',
						'LAN4' => 'Felhasználó',
						'LAN5' => 'Regisztrált felhasználó',
					),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed.');

		}

/*
		public function testThirdPartyPlugins()
		{

		}

		public function testInit()
		{

		}

		public function testCheck_lanfiles()
		{

		}

		public function testGetFilePaths()
		{

		}

		public function testGetOnlineLanguagePacks()
		{

		}

		public function testGet_comp_lan_phrases()
		{

		}
*/
		public function testIs_utf8()
		{
			$strings = array(
				"Főadminisztrátor",
				"Hői",
				"Rendszerinformáció",
				"Felhasználó",
				"Regisztrált felhasználó");

			foreach($strings as $expected)
			{
				$actual = $this->lan->is_utf8($expected);
				$this->assertEquals(true, $actual, 'is_utf8() failed on '.$expected.'.');
			}


		}

		/**
		 * The file to edit comes off the query string and is never checked, while
		 * toDB() leaves ../ completely intact (it encodes for HTML, not for the
		 * filesystem). The path decides where a directory gets created, where a
		 * stub .php file is written, and where the save lands.
		 */
		public function testInitRefusesATraversingFile()
		{
			$traversals = array(
				'../../../../tmp/e107-escape.php',
				'..\\..\\tmp\\e107-escape.php',
				'/etc/e107-escape.php',
				'admin/../../../../tmp/e107-escape.php',
				'lan_online.php'."\0".'.txt',
			);

			foreach($traversals as $file)
			{
				$_GET['sub']  = 'edit';
				$_GET['lan']  = 'English';
				$_GET['file'] = $file;

				$this->assertFalse($this->lan->init(),
					'The editor opened a file outside the language roots: '.$file);
				$this->assertArrayNotHasKey('lancheck-edit-file', $_SESSION,
					'A traversing path was handed to the save step: '.$file);
			}
		}

		/**
		 * The edit screen's caption comes from language.php's own language file and from SEP,
		 * which the admin theme defines; a unit run has neither and neither bears on the guard.
		 */
		protected function loadEditScreenLans()
		{
			e107::coreLan('language', true);

			if(!defined('SEP'))
			{
				define('SEP', ' &raquo; ');
			}
		}

		/** The guard must not be so tight that the feature stops working. */
		public function testInitStillOpensAnOrdinaryLanguageFile()
		{
			$this->loadEditScreenLans();

			$_GET['sub']  = 'edit';
			$_GET['lan']  = 'English';
			$_GET['file'] = 'lan_online.php';

			$result = $this->lan->init();

			$this->assertIsArray($result, 'An ordinary language file should still open.');
			$this->assertSame('edit', $result['mode']);
		}

		/**
		 * GHSA-pf37-7c5m-mpg3: the P and T types prefix the plugin and theme roots, and
		 * containment stopped at the root itself, so any writable .php file under a plugin
		 * opened in the editor and was replaced whole by the save that followed.
		 */
		public function testInitRefusesAPluginFileOutsideALanguagesDirectory()
		{
			$this->loadEditScreenLans();

			$_GET['sub']  = 'edit';
			$_GET['lan']  = 'English';
			$_GET['type'] = 'P';
			$_GET['file'] = 'download/handlers/SecureLinkDecorator.php';

			$this->assertEmpty($this->lan->init(),
				'The editor opened a plugin file that is not a language file.');
			$this->assertArrayNotHasKey('lancheck-edit-file', $_SESSION,
				'A plugin file outside a languages directory reached the save step.');
		}

		/** The plugin the editor is actually for still opens. */
		public function testInitStillOpensAPluginLanguageFile()
		{
			$this->loadEditScreenLans();

			$plugin = $this->writeScratchPlugin(array('English.php' => 'define("LAN_TEST_X", "Hello");'));

			$_GET['sub']  = 'edit';
			$_GET['lan']  = 'English';
			$_GET['type'] = 'P';
			$_GET['file'] = $plugin.'/languages/English.php';

			$result = $this->lan->init();

			$this->assertIsArray($result, 'A plugin language file should still open.');
			$this->assertSame('edit', $result['mode']);
		}

		/**
		 * GHSA-pf37-7c5m-mpg3: the header block the generator writes carried the
		 * display name of whoever pressed save, and a display name holding the
		 * sequence that ends a comment ended the header early, leaving the rest
		 * of the name to run as PHP on every page that loads the file.
		 */
		public function testWrite_lanfileKeepsUserInputOutOfTheGeneratedHeader()
		{
			$written = $this->writeLanFile(array('LAN_X'), array('harmless'));

			$this->assertStringNotContainsString(USERNAME, $written,
				'The generated file must carry no value the saving user controls.');

			$result = $this->includeGenerated('LAN_X');

			$this->assertSame(0, $result['exit'], 'Including the generated file must not fatal.');
			$this->assertSame('[VALUE]harmless', $result['stdout'],
				'Nothing but the translation may come out of the generated file.');
		}

		/**
		 * GHSA-pf37-7c5m-mpg3: the translation lands inside a double-quoted PHP
		 * string literal, so a value carrying a quote used to close define() early
		 * and run whatever followed, for every visitor to a page loading the file.
		 */
		public function testWrite_lanfileEscapesTheTranslation()
		{
			$payload = 'x"); echo "INJECTED_VALUE"; //';

			$this->writeLanFile(array('LAN_X'), array($payload));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_X');

			$this->assertSame('[VALUE]'.$payload, $result['stdout'],
				'The translation must survive verbatim as data and nothing else may run.');
		}

		/**
		 * The constant name arrives in the hidden newdef[] field, which is just as
		 * client-controlled as the textarea, and it is written into define("...")
		 * as well as into the if(!defined("...")) guard.
		 */
		public function testWrite_lanfileRejectsAnInjectedConstantName()
		{
			$payload = 'LAN_X", "x"); echo "INJECTED_NAME"; //';

			$this->writeLanFile(array($payload), array('harmless'));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_X');

			$this->assertStringNotContainsString('INJECTED_NAME', $result['stdout'],
				'A constant name that is not an identifier must never reach the generated file.');
		}

		/** The same name is also interpolated into the ndef++ "if (!defined(...))" guard. */
		public function testWrite_lanfileRejectsAnInjectedNdefGuard()
		{
			$payload = 'ndef++LAN_X")) { echo "INJECTED_NDEF"; } if (true) { //';

			$this->writeLanFile(array($payload), array('harmless'));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_X');

			$this->assertStringNotContainsString('INJECTED_NDEF', $result['stdout'],
				'The ndef++ guard must not be a second injection point for the constant name.');
		}

		/**
		 * PCRE's $ matches before a final newline as well as at the end of the subject, so a
		 * name ending in one passed the test and went into the file as a constant no template
		 * can ever reference by name.
		 */
		public function testWrite_lanfileRejectsAConstantNameEndingInANewline()
		{
			$written = $this->writeLanFile(array("FOO\n"), array('harmless'));

			$this->assertStringNotContainsString('FOO', $written,
				'A name that is not a usable identifier must not be written into the file.');
		}

		/**
		 * The LC_ALL branch interpolates the value with no quotes at all, so this
		 * one never even needed a quote to break out of.
		 */
		public function testWrite_lanfileQuotesTheSetlocaleValue()
		{
			$payload = "'en_GB.UTF-8'); echo \"INJECTED_LOCALE\"; //";

			$this->writeLanFile(array('LC_ALL'), array($payload));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_UNUSED');

			$this->assertSame(0, $result['exit'],
				'Including the generated file must not fatal: '.$result['stdout']);
			$this->assertStringNotContainsString('INJECTED_LOCALE', $result['stdout'],
				'The setlocale() arguments must be written as quoted literals, not as source.');
		}

		/**
		 * The reader hands the whole argument list back as one blob, so every
		 * locale in it has to survive the round trip and the file has to stay
		 * loadable.
		 */
		public function testWrite_lanfileKeepsEverySetlocaleArgument()
		{
			$written = $this->writeLanFile(array('LC_ALL'),
				array("'en_GB.UTF-8', 'en_GB.utf8', 'eng_eng.utf8', 'en'"));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_UNUSED');
			$this->assertSame(0, $result['exit'], 'Including the generated file must not fatal.');

			foreach(array('en_GB.UTF-8', 'en_GB.utf8', 'eng_eng.utf8', 'en') as $locale)
			{
				$this->assertStringContainsString('"'.$locale.'"', $written,
					'Locale dropped from the rewritten setlocale() call: '.$locale);
			}
		}

		/**
		 * Whatever the escaping does, an ordinary translation has to come back out
		 * of lancheck's own reader unchanged, or saving twice corrupts the file.
		 *
		 * On this branch fill_phrases_array() returns the body of the literal
		 * rather than its value, so the property to hold is that feeding that body
		 * straight back in leaves the phrase itself untouched.
		 */
		public function testWrite_lanfileRoundTripsThroughItsOwnReader()
		{
			// The field holds the body of the literal on this branch, not the phrase,
			// so an escape sequence typed into it means what it means in PHP source.
			// Each case is (what the editor shows, what the phrase must come out as).
			$values = array(
				'plain'         => array('Hello world', 'Hello world'),
				'apostrophe'    => array("It's fine", "It's fine"),
				'escaped quote' => array('say \"hi\"', 'say "hi"'),
				'escaped slash' => array('C:\\\\path', 'C:\\path'),
				'dollar'        => array('Cost $5', 'Cost $5'),
				'utf8'          => array('Főadminisztrátor', 'Főadminisztrátor'),
			);

			foreach($values as $label => $pair)
			{
				list($typed, $value) = $pair;

				$first = $this->writeLanFile(array('LAN_X'), array($typed));

				$this->assertSame(0, $this->lintGenerated(), 'Invalid PHP for case: '.$label);

				$result = $this->includeGenerated('LAN_X');
				$this->assertSame('[VALUE]'.$value, $result['stdout'],
					'Runtime value drifted for case: '.$label);

				$back = $this->lan->fill_phrases_array($first, 'tran');
				$this->assertArrayHasKey('LAN_X', $back['tran'], 'Phrase went missing for case: '.$label);

				// Save again with exactly what the editor would have shown. Escapes
				// that were re-escaped rather than carried across would multiply here.
				$this->writeLanFile(array('LAN_X'), array($back['tran']['LAN_X']), false);

				$again = $this->includeGenerated('LAN_X');
				$this->assertSame('[VALUE]'.$value, $again['stdout'],
					'Saving an untouched phrase a second time changed it for case: '.$label);
			}
		}

		/** A NUL cannot be written into the literal, so it is dropped rather than left to truncate the phrase. */
		public function testWrite_lanfileDropsNullBytesFromTheTranslation()
		{
			$this->writeLanFile(array('LAN_X'), array('a'.chr(0).'b'));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_X');

			$this->assertSame('[VALUE]ab', $result['stdout'], 'The NUL must be stripped, not kept or truncated at.');
		}

		/**
		 * The confirmation screen echoes back what was written; the translation was
		 * interpolated into that HTML raw.
		 */
		public function testWrite_lanfileEscapesTheConfirmationHtml()
		{
			$this->writeLanFile(array('LAN_X'), array('<script>alert(1)</script>'));

			$this->assertStringNotContainsString('<script>', $this->rendered,
				'The saved translation must be escaped before it is echoed back.');
		}

		/**
		 * The confirmation screen is the only account of what went to disk, and a phrase that
		 * is not valid UTF-8 is an ordinary condition of a language pack rather than an odd
		 * one, so the line has to survive one instead of disappearing.
		 */
		public function testWrite_lanfileShowsAPhraseThatIsNotValidUtf8()
		{
			$this->writeLanFile(array('LAN_X'), array("caf\xe9"));

			$this->assertStringContainsString('LAN_X', $this->rendered,
				'The confirmation screen dropped the whole line for a phrase that is not valid UTF-8.');
		}

		/**
		 * Write a scratch plugin language pack, which _after() then removes.
		 *
		 * The directory name is fixed so that WorkspaceCleanup's sweep can carry
		 * it and heal a run that dies before _after().
		 *
		 * @param array $files file name => pack body, without the opening tag
		 * @return string the plugin's directory name
		 */
		protected function writeScratchPlugin($files)
		{
			$plugin = 'temptest6109';
			$this->scratchPlugin = e_PLUGIN.$plugin.'/';

			mkdir($this->scratchPlugin.'languages', 0755, true);

			foreach($files as $name => $body)
			{
				file_put_contents($this->scratchPlugin.'languages/'.$name, "<?php
".$body);
			}

			return $plugin;
		}

		/**
		 * A plugin that keeps its packs flat in languages/ leaves nothing before
		 * the first slash of the relative path.
		 */
		public function testGet_comp_lan_phrasesReadsAFlatPluginLayout()
		{
			$plugin = $this->writeScratchPlugin(array('English.php' => "define('LAN_TEST_FLAT', 'flat');
"));

			$phrases = $this->lan->get_comp_lan_phrases(e_PLUGIN.$plugin.'/languages/', 'English', 1);

			$this->assertSame(array('English.php' => array('LAN_TEST_FLAT' => 'flat')), $phrases);
		}

		/**
		 * The whitelist reads the component out of the path, and only once
		 * thirdPartyPlugins() has asked for it.
		 * The last assertion is the shape check_lanfiles() passes, where the
		 * component directory is already the root.
		 */
		public function testGet_comp_lan_phrasesFiltersOnTheComponentDirectory()
		{
			$plugin = $this->writeScratchPlugin(array('English.php' => "define('LAN_TEST_FLAT', 'flat');
"));
			$expected = array($plugin.'/languages/English.php' => array('LAN_TEST_FLAT' => 'flat'));

			$this->lan->thirdPartyPlugins(false);

			$this->lan->core_plugins = array($plugin);
			$this->assertSame($expected, $this->lan->get_comp_lan_phrases(e_PLUGIN, 'English', 2));

			$this->lan->core_plugins = array('not_a_plugin_of_ours');
			$this->assertSame(array(), $this->lan->get_comp_lan_phrases(e_PLUGIN, 'English', 2));

			$this->lan->core_plugins = array($plugin);
			$this->assertSame(array(), $this->lan->get_comp_lan_phrases(e_PLUGIN.$plugin.'/languages/', 'English', 1));
		}

		/**
		 * Put the checker into the state check_all() establishes before it
		 * verifies a pack.
		 *
		 * @param string $language the language being verified
		 */
		protected function startVerifying($language)
		{
			$transLanguage = new ReflectionProperty('lancheck', 'transLanguage');
			$transLanguage->setAccessible(true);
			$transLanguage->setValue($this->lan, $language);

			$_SESSION['lancheck'][$language] = array('file' => 0, 'def' => 0, 'bom' => 0, 'utf' => 0, 'total' => 0);
		}

		/** A pack with no BOM and no trailing content never gets a 'bom' entry. */
		public function testCheck_lanfilesAcceptsAPackWithoutABomEntry()
		{
			$plugin = $this->writeScratchPlugin(array(
				'English.php' => "define('LAN_TEST_FLAT', 'flat');
",
				'Dutch.php'   => "define('LAN_TEST_FLAT', 'plat');
",
			));

			$this->startVerifying('Dutch');

			$text = $this->lan->check_lanfiles('P', $plugin, 'English', 'Dutch');

			$this->assertStringContainsString($plugin, $text);
		}

/*
		public function testCountFiles()
		{

		}

		public function test__construct()
		{

		}

		public function testCleanFile()
		{

		}

		public function testGetLocalLanguagePacks()
		{

		}

		public function testCheck_core_lanfiles()
		{

		}

		public function testRemoveLanguagePack()
		{

		}

		public function testErrorsOnly()
		{

		}

		public function testCheck_all()
		{

		}

		public function testZipLang()
		{

		}

		public function testGet_lan_file_phrases()
		{

		}

		public function testNewFile()
		{

		}

		public function testEdit_lanfiles()
		{

		}

*/


	}
