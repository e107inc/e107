<?php
/**
 * Fails the suite on a WebDriver test that signs in through a form without being about signing in.
 */
class webdriverSignsInThroughTheProbeTest extends \Test\Unit
{
	const SUITE = 'tests/webdriver';

	/** The group that marks a test, or a whole Cest, as being about the sign-in form. */
	const GROUP = 'sign-in';

	/** @var array the actor methods that sign in through a form, in lower case as PHP compares method names */
	private static $formSignIns = array('loginasadmin', 'logintoforum');

	/** @var array what may stand between a declaration and its docblock */
	private static $modifiers = array(T_ABSTRACT, T_FINAL, T_PRIVATE, T_PROTECTED, T_PUBLIC, T_STATIC, T_WHITESPACE, T_COMMENT);

	public function testOnlyTestsAboutSigningInUseTheForm()
	{
		$findings = array();

		foreach (\Test\Tree::phpFiles(self::SUITE) as $file)
		{
			foreach ($this->formSignInsIn(file_get_contents($file)) as $finding)
			{
				$findings[] = \Test\Tree::relativePath($file) . ':' . $finding;
			}
		}

		$this->assertSame(array(), $findings,
			"These WebDriver tests sign in through a form, which costs a page load and a password check, and an admin\n"
			. "sign-in lands on the dashboard and its feeds first. Sign in with \$I->amSignedInAs(\$user) instead, or, if\n"
			. "the test is about signing in, tag it or its Cest with @group " . self::GROUP . ".");
	}

	/**
	 * @dataProvider sources
	 * @param string $source
	 * @param array $expected
	 */
	public function testTheDetectorReadsTokensNotText($source, array $expected)
	{
		$this->assertSame($expected, $this->formSignInsIn("<?php\n" . $source));
	}

	public function sources()
	{
		$test = "class SomeCest\n{\n%s\n\tpublic function t(\$I)\n\t{\n\t\t%s\n\t}\n}";

		return array(
			'an admin signing in through the form' => array(sprintf($test, '', '$I->loginAsAdmin();'), array('7 loginAsAdmin()')),
			'a member signing in through the form' => array(sprintf($test, '', "\$I->loginToForum('m');"), array('7 loginToForum()')),
			'a test tagged sign-in' => array(sprintf($test, "\t/** @group sign-in */", '$I->loginAsAdmin();'), array()),
			'a tag among others in a longer docblock' => array(sprintf($test, "\t/**\n\t * Signs in.\n\t *\n\t * @group tls\n\t * @group sign-in\n\t */", '$I->loginAsAdmin();'), array()),
			'a Cest tagged sign-in' => array("/** @group sign-in */\n" . sprintf($test, '', '$I->loginAsAdmin();'), array()),
			'a test tagged with another group' => array(sprintf($test, "\t/** @group tls */", '$I->loginAsAdmin();'), array('7 loginAsAdmin()')),
			'a group whose name starts the same way' => array(sprintf($test, "\t/** @group sign-in-later */", '$I->loginAsAdmin();'), array('7 loginAsAdmin()')),
			'the tag on the test before' => array("class SomeCest\n{\n\t/** @group sign-in */\n\tpublic function a(\$I) {}\n\n\tpublic function b(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array('9 loginAsAdmin()')),
			'a hook' => array("class SomeCest\n{\n\tpublic function _before(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array('6 loginAsAdmin()')),
			'a hook tagged sign-in, which Codeception reads no groups from' => array("class SomeCest\n{\n\t/** @group sign-in */\n\tpublic function _before(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array('7 loginAsAdmin()')),
			'a private helper tagged sign-in' => array("class SomeCest\n{\n\t/** @group sign-in */\n\tprivate function signIn(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array('7 loginAsAdmin()')),
			'a hook in a Cest tagged sign-in' => array("/** @group sign-in */\nclass SomeCest\n{\n\tpublic function _before(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array()),
			'the name in another case' => array(sprintf($test, '', '$I->LoginAsAdmin();'), array('7 LoginAsAdmin()')),
			'a helper of the Cest\'s own with the same name' => array(sprintf($test, '', '$this->loginToForum($I);'), array()),
			'an anonymous class with a tagged method before the sign-in' => array(sprintf($test, '', '$o = new class { /** @group sign-in */ public function x() {} }; $I->loginAsAdmin();'), array('7 loginAsAdmin()')),
			'a closure inside a tagged test' => array(sprintf($test, "\t/** @group sign-in */", '$f = function () use ($I) { $I->loginAsAdmin(); };'), array()),
			'modifiers between the tag and the test' => array("class SomeCest\n{\n\t/** @group sign-in */\n\tfinal public static function t(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array()),
			'the probe sign-in' => array(sprintf($test, '', "\$I->amSignedInAs('m');"), array()),
			'a sign-in in a comment' => array(sprintf($test, '', '// $I->loginAsAdmin();'), array()),
			'a sign-in in a string' => array(sprintf($test, '', "\$s = '\$I->loginAsAdmin();';"), array()),
			'a method declared with the name' => array("class SomeCest\n{\n\tpublic function loginAsAdmin() {}\n}", array()),
			'a tagged test returning by reference' => array("class SomeCest\n{\n\t/** @group sign-in */\n\tpublic function &t(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array()),
			'a tagged test named like a keyword' => array("class SomeCest\n{\n\t/** @group sign-in */\n\tpublic function list(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array()),
			'a tagged abstract Cest, which Codeception does not run' => array("/** @group sign-in */\nabstract class BaseCest\n{\n\tpublic function _before(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array('7 loginAsAdmin()')),
			'a tagged class whose name does not end in Cest' => array("/** @group sign-in */\nclass AdminSteps\n{\n\tpublic static function signIn(\$I)\n\t{\n\t\t\$I->loginAsAdmin();\n\t}\n}", array('7 loginAsAdmin()')),
			'a tagged function outside a class' => array("/** @group sign-in */\nfunction signIn(\$I)\n{\n\t\$I->loginAsAdmin();\n}", array('5 loginAsAdmin()')),
			'a tagged function inside an untagged test' => array(sprintf($test, '', '/** @group sign-in */ function signIn($I) { $I->loginAsAdmin(); }'), array('7 loginAsAdmin()')),
		);
	}

	/**
	 * @param string $source
	 * @return array one "<line> <name>()" per form sign-in in neither a test nor a Cest tagged as being about signing in
	 */
	private function formSignInsIn($source)
	{
		$found = array();
		$tokens = token_get_all($source);
		$enclosing = array();
		$declared = null;

		foreach ($tokens as $i => $token)
		{
			if (\Test\Tokens::opensBrace($token))
			{
				$enclosing[] = $declared;
				$declared = null;
			}
			elseif ($token === '}')
			{
				array_pop($enclosing);
			}
			elseif ($token === ';')
			{
				$declared = null;
			}
			elseif (is_array($token) && in_array($token[0], array(T_CLASS, T_FUNCTION), true) && \Test\Tokens::declaredName($tokens, $i) !== null)
			{
				$declared = $this->declaration($tokens, $i, end($enclosing));
			}
			elseif (is_array($token) && $token[0] === T_STRING && in_array(strtolower($token[1]), self::$formSignIns, true)
				&& \Test\Tokens::isCalledOnAnActor($tokens, $i) && !$this->isInATaggedDeclaration($enclosing))
			{
				$found[] = $token[2] . ' ' . $token[1] . '()';
			}
		}

		return $found;
	}

	/**
	 * @param array $tokens
	 * @param int $i index of a class or function keyword that declares a name
	 * @param array|false|null $parent the declaration the braces around it belong to, null for other braces, false for none
	 * @return array array(T_CLASS or T_FUNCTION, whether Codeception runs it as a Cest or a test, whether it also carries @group {@see GROUP})
	 */
	private function declaration(array $tokens, $i, $parent)
	{
		$name = \Test\Tokens::declaredName($tokens, $i);
		$modifiers = array();

		for ($j = $i - 1; isset($tokens[$j]) && is_array($tokens[$j]) && in_array($tokens[$j][0], self::$modifiers, true); $j--)
		{
			$modifiers[] = $tokens[$j][0];
		}

		if ($tokens[$i][0] === T_CLASS)
		{
			$runs = substr($name, -4) === 'Cest' && !in_array(T_ABSTRACT, $modifiers, true);
		}
		else
		{
			$runs = is_array($parent) && $parent[0] === T_CLASS && $parent[1] && strpos($name, '_') !== 0
				&& !array_intersect(array(T_PRIVATE, T_PROTECTED), $modifiers);
		}

		$docblock = isset($tokens[$j]) && is_array($tokens[$j]) && $tokens[$j][0] === T_DOC_COMMENT ? $tokens[$j][1] : '';

		return array($tokens[$i][0], $runs, $runs && $this->carriesTheGroup($docblock));
	}

	/**
	 * @param array $enclosing one {@see declaration()} per brace the code sits in, or null for a brace that is not one
	 * @return bool whether the code is in a Cest or a test that carries @group {@see GROUP}
	 */
	private function isInATaggedDeclaration(array $enclosing)
	{
		foreach ($enclosing as $declaration)
		{
			if ($declaration !== null && $declaration[2])
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $docblock
	 * @return bool
	 */
	private function carriesTheGroup($docblock)
	{
		$words = preg_split('#[\s*/]+#', $docblock, -1, PREG_SPLIT_NO_EMPTY);

		foreach ($words as $k => $word)
		{
			if ($word === '@group' && isset($words[$k + 1]) && $words[$k + 1] === self::GROUP)
			{
				return true;
			}
		}

		return false;
	}
}
