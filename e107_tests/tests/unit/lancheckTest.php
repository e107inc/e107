<?php
	/**
	 * Created by PhpStorm.
	 * User: Wiz
	 * Date: 1/30/2019
	 * Time: 12:17 PM
	 */


	class lancheckTest extends \Test\Unit
	{

		/** @var lancheck */
		protected $lan;

		/** @var string Scratch language file for the write_lanfile() tests. */
		protected $target;

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

			$this->target = sys_get_temp_dir().'/e107-lancheck-'.uniqid('', true).'.php';
		}

		protected function _after()
		{
			if($this->target && is_file($this->target))
			{
				unlink($this->target);
			}

			unset($_SESSION['lancheck-edit-file'], $_POST['newlang'], $_POST['newdef']);
			unset($_GET['sub'], $_GET['lan'], $_GET['file'], $_GET['type']);
			e107::getMessage()->reset();
		}

		/**
		 * Drive write_lanfile() over the scratch file and hand back what it wrote.
		 *
		 * @param array $newdef  values for the hidden newdef[] field (constant names)
		 * @param array $newlang values for the newlang[] textareas (translations)
		 * @return string the generated language file
		 */
		protected function writeLanFile($newdef, $newlang)
		{
			file_put_contents($this->target, "<?php\n");

			$_SESSION['lancheck-edit-file'] = $this->target;
			$_POST['newdef']                = $newdef;
			$_POST['newlang']               = $newlang;

			ob_start();
			$this->lan->write_lanfile('English');
			ob_end_clean();

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

		public function testFill_phrases_array_const()
		{
			$strings =
				'const LAN1 = "Főadminisztrátor";'."\n".
				'const CORE_LC = "es";'."\n".
				"const LAN2 = 'Hői';";

			$expected = array(
				'orig' => array(
					'LAN1'    => 'Főadminisztrátor',
					'CORE_LC' => 'es',
					'LAN2'    => 'Hői',
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed on const syntax.');
		}

		public function testFill_phrases_array_returnArray()
		{
			$strings =
				'return array('."\n".
				"\t'LAN1' => 'Főadminisztrátor',"."\n".
				"\t'LAN2' => 'Hői',"."\n".
				');';

			$expected = array(
				'orig' => array(
					'LAN1' => 'Főadminisztrátor',
					'LAN2' => 'Hői',
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed on return-array syntax.');
		}

		public function testFill_phrases_array_shortArray()
		{
			$strings =
				'return ['."\n".
				"\t'LAN1' => \"It's escaped\","."\n".
				"\t'LAN2' => 'A \\'quoted\\' value',"."\n".
				'];';

			$expected = array(
				'orig' => array(
					'LAN1' => "It's escaped",
					'LAN2' => "A 'quoted' value",
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() failed on short-array syntax with escapes.');
		}

		public function testFill_phrases_array_ignoresComments()
		{
			// define()s inside comments must NOT be picked up (the old regex needed a
			// pre-pass to strip /* */ blocks; the tokenizer ignores them natively).
			$strings =
				'/* define("LAN_COMMENTED", "ignore me"); */'."\n".
				'// define("LAN_LINE", "ignore me too");'."\n".
				'define("LAN1", "kept");';

			$expected = array(
				'orig' => array(
					'LAN1' => 'kept',
				),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() must ignore commented-out statements.');
		}

		public function testFill_phrases_array_registersEmptyType()
		{
			// A modern file with no recognised phrases must still register the type
			// key (as an empty array) so it is not misreported as "File missing!".
			$strings = '$foo = 1; // nothing to harvest here';

			$expected = array(
				'orig' => array(),
			);

			$actual = $this->lan->fill_phrases_array($strings, 'orig');
			$this->assertEquals($expected, $actual, 'fill_phrases_array() must register the type for phrase-less files.');
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

		/** The guard must not be so tight that the feature stops working. */
		public function testInitStillOpensAnOrdinaryLanguageFile()
		{
			// The edit screen's caption is built from language.php's own language
			// file and from SEP, which the admin theme defines. Neither is present
			// in a unit run, and neither has anything to do with the guard.
			e107::coreLan('language', true);

			if(!defined('SEP'))
			{
				define('SEP', ' &raquo; ');
			}

			$_GET['sub']  = 'edit';
			$_GET['lan']  = 'English';
			$_GET['file'] = 'lan_online.php';

			$result = $this->lan->init();

			$this->assertIsArray($result, 'An ordinary language file should still open.');
			$this->assertSame('edit', $result['mode']);
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
		 * The LC_ALL branch interpolates the value with no quotes at all, so this
		 * one never even needed a quote to break out of.
		 */
		public function testWrite_lanfileQuotesTheSetlocaleValue()
		{
			$payload = 'en_GB.UTF-8); echo "INJECTED_LOCALE"; //';

			$this->writeLanFile(array('LC_ALL'), array($payload));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_UNUSED');

			$this->assertStringNotContainsString('INJECTED_LOCALE', $result['stdout'],
				'The setlocale() argument must be written as a quoted literal, not as source.');
		}

		/**
		 * Saving a language file that carries setlocale() must leave it loadable.
		 * The value reaches write_lanfile() as a bare locale name, and writing it
		 * unquoted produced `setlocale(LC_ALL,en_GB.UTF-8);`, which passes php -l
		 * (it parses as arithmetic on constants) and then fatals on include.
		 */
		public function testWrite_lanfileKeepsSetlocaleLoadable()
		{
			$this->writeLanFile(array('LC_ALL'), array('en_GB.UTF-8'));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$result = $this->includeGenerated('LAN_UNUSED');

			$this->assertSame(0, $result['exit'],
				'Including the generated file must not fatal.');
			$this->assertStringNotContainsString('Error', $result['stdout'],
				'setlocale() must be written with its argument quoted: '.$result['stdout']);
		}

		/**
		 * Whatever the escaping does, an ordinary translation has to come back out
		 * of lancheck's own reader unchanged, or saving twice corrupts the file.
		 */
		public function testWrite_lanfileRoundTripsThroughItsOwnReader()
		{
			$values = array(
				'plain'          => 'Hello world',
				'apostrophe'     => "It's fine",
				'double quote'   => 'say "hi"',
				'backslash'      => 'C:\\path\\to',
				'dollar'         => 'Cost $5 and ${x}',
				'utf8'           => 'Főadminisztrátor',
				'quote and slug' => 'A \'quoted\' value',
			);

			foreach($values as $label => $value)
			{
				$written = $this->writeLanFile(array('LAN_X'), array($value));

				$this->assertSame(0, $this->lintGenerated(), 'Invalid PHP for case: '.$label);

				$result = $this->includeGenerated('LAN_X');
				$this->assertSame('[VALUE]'.$value, $result['stdout'],
					'Runtime value drifted for case: '.$label);

				$back = $this->lan->fill_phrases_array($written, 'tran');
				$this->assertSame($value, $back['tran']['LAN_X'],
					'fill_phrases_array() could not read back the value for case: '.$label);
			}
		}

		/**
		 * var_export() cannot express a NUL inside a single literal; it emits a
		 * concatenation ('a' . "\0" . 'b'), which is valid PHP but which the
		 * tokenizer-based reader stops at, silently truncating the phrase.
		 */
		public function testWrite_lanfileDropsNullBytesFromTheTranslation()
		{
			$written = $this->writeLanFile(array('LAN_X'), array('a'.chr(0).'b'));

			$this->assertSame(0, $this->lintGenerated(), 'Generated language file is not valid PHP.');

			$back = $this->lan->fill_phrases_array($written, 'tran');

			$this->assertArrayHasKey('LAN_X', $back['tran'], 'The phrase went missing entirely.');
			$this->assertSame('ab', $back['tran']['LAN_X'],
				'A NUL must be stripped so the phrase stays readable, not truncated at the NUL.');
		}

		/**
		 * The confirmation screen echoes back what was written; the translation was
		 * interpolated into that HTML raw.
		 */
		public function testWrite_lanfileEscapesTheConfirmationHtml()
		{
			file_put_contents($this->target, "<?php\n");

			$_SESSION['lancheck-edit-file'] = $this->target;
			$_POST['newdef']                = array('LAN_X');
			$_POST['newlang']               = array('<script>alert(1)</script>');

			ob_start();
			$this->lan->write_lanfile('English');
			$rendered = ob_get_clean();

			$this->assertStringNotContainsString('<script>', $rendered,
				'The saved translation must be escaped before it is echoed back.');
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
