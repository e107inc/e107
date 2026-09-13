<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e107_admin/users.php is an entry point that cannot be included from a test, so the user_class
 * controls are held to their structural obligations against the source: the option lists are put
 * through the builder the controls are rendered from, and the two routes that write the column
 * are read for the rule they apply.
 *
 * A list that does not name a built-in class renders no control for it, and the save that follows
 * writes the column without it.
 */
class usersAdminUserClassTest extends \Test\Unit
{
	/** @var string */
	private $page;

	/** @var array */
	private $tokens;

	/** @var user_class */
	private $userclass;

	protected function _before()
	{
		$this->page = e_ADMIN . 'users.php';
		$this->assertFileExists($this->page);

		$this->tokens = token_get_all(file_get_contents($this->page));
		$this->userclass = e107::getUserClass();
	}

	public function testTheEditFormOffersTheBuiltInMemberClass()
	{
		$parms = $this->stringPairs($this->arguments($this->fieldDefinition('user_class')));

		foreach(array('writeParms', 'readParms') as $parm)
		{
			$this->assertArrayHasKey($parm, $parms,
				'The user_class field must declare ' . $parm . ' as a parameter string. Moving it to '
				. 'the array form admin_ui also accepts means teaching stringPairs() to read that.');

			parse_str($parms[$parm], $parsed);

			$this->assertArrayHasKey('classlist', $parsed,
				$parm . ' must name the classes the control offers.');
			$this->assertArrayHasKey(e_UC_MEMBER, $this->offered($parsed['classlist']),
				$parm . ' must offer the built-in Members class, or editing a user drops it.');
		}
	}

	public function testEveryUserClassTreeOffersTheBuiltInMemberClass()
	{
		$optlists = $this->vettedTreeOptlists();

		$this->assertNotEmpty($optlists,
			'Set user class and Create User both render a user class tree in ' . $this->page . '.');

		foreach($optlists as $optlist)
		{
			$this->assertArrayHasKey(e_UC_MEMBER, $this->offered($optlist),
				'A user class tree that omits the built-in Members class cannot put it back.');
		}
	}

	public function testSetUserClassJudgesTheChangeRatherThanEachPostedClass()
	{
		$calls = $this->namesIn($this->page, 'manageUserclass');

		$this->assertContains('refusesClassChange', $calls,
			'Set user class must weigh the difference it is asked to make, as the edit and batch routes do.');
		$this->assertNotContains('checkAllowed', $calls,
			'A refusal per posted class abandons the whole submission over a box the caller never moved.');
	}

	public function testSetUserClassKeepsTheClassesItsFormNeverOffered()
	{
		$this->assertContains('uc_required_class_list', $this->namesIn($this->page, 'manageUserclass'),
			'Set user class must read the list it rendered from, or the save discards every class it did not show.');
	}

	public function testCreateUserStoresTheClassesItsFormPosted()
	{
		$this->assertContains('checkAllowed', $this->namesIn($this->page, 'AddSubmitTrigger'),
			'Quick Add must store the classes the form posted, and put Members back only behind the '
			. 'rule, for a caller who may not take it away.');
	}

	public function testCreateUserOpensWithTheBuiltInMemberClassTicked()
	{
		$this->assertContains('e_UC_MEMBER', $this->namesIn($this->page, 'AddPage'),
			'The Create User form must open with Members ticked, or the default account stops holding it.');
	}

	/**
	 * The classes an option list yields, keyed by class id.
	 */
	private function offered($optlist)
	{
		return $this->userclass->uc_required_class_list($optlist);
	}

	/**
	 * The option list argument of every vetted_tree() call on the page.
	 */
	private function vettedTreeOptlists()
	{
		$optlists = array();

		foreach($this->tokens as $i => $token)
		{
			if(!is_array($token) || $token[0] !== T_STRING || $token[1] !== 'vetted_tree')
			{
				continue;
			}

			$arguments = $this->arguments($this->nextCode($i));

			$this->assertArrayHasKey(3, $arguments,
				'A vetted_tree() call in ' . $this->page . ' passes no option list.');

			$optlist = $this->literal($arguments[3]);

			$this->assertNotNull($optlist,
				'A vetted_tree() option list in ' . $this->page . ' is no longer a plain string.');

			$optlists[] = $optlist;
		}

		return $optlists;
	}

	/**
	 * The offset of the opening parenthesis of the array a field name is defined as.
	 */
	private function fieldDefinition($field)
	{
		$found = null;

		foreach($this->tokens as $i => $token)
		{
			if(!is_array($token) || $token[0] !== T_CONSTANT_ENCAPSED_STRING || $this->value($token) !== $field)
			{
				continue;
			}

			$arrow = $this->nextCode($i);

			if($arrow === null || !is_array($this->tokens[$arrow]) || $this->tokens[$arrow][0] !== T_DOUBLE_ARROW)
			{
				continue;
			}

			$array = $this->nextCode($arrow);

			if($array !== null && is_array($this->tokens[$array]) && $this->tokens[$array][0] === T_ARRAY)
			{
				$found = $this->nextCode($array);
				break;
			}
		}

		$this->assertNotNull($found,
			"'" . $field . "' is no longer defined as a field array in " . $this->page . '.');

		return $found;
	}

	/**
	 * One slice of code tokens per comma-separated entry of the list opening at an offset.
	 */
	private function arguments($open)
	{
		$this->assertSame('(', $this->tokens[$open], 'An argument list is read from its opening parenthesis.');

		$arguments = array();
		$current = array();
		$depth = 0;

		for($i = $open, $n = count($this->tokens); $i < $n; $i++)
		{
			$token = $this->tokens[$i];

			if(!$this->isCode($token))
			{
				continue;
			}

			if($token === '(' || $token === '[')
			{
				$depth++;

				if($depth === 1)
				{
					continue;
				}
			}
			elseif($token === ')' || $token === ']')
			{
				$depth--;

				if($depth === 0)
				{
					break;
				}
			}
			elseif($token === ',' && $depth === 1)
			{
				$arguments[] = $current;
				$current = array();
				continue;
			}

			$current[] = $token;
		}

		if($current)
		{
			$arguments[] = $current;
		}

		return $arguments;
	}

	/**
	 * The entries of a sliced list whose key and value are both plain strings, as an array.
	 */
	private function stringPairs(array $slices)
	{
		$pairs = array();

		foreach($slices as $slice)
		{
			if(count($slice) !== 3 || !is_array($slice[1]) || $slice[1][0] !== T_DOUBLE_ARROW)
			{
				continue;
			}

			$key = $this->literal(array($slice[0]));
			$value = $this->literal(array($slice[2]));

			if($key !== null && $value !== null)
			{
				$pairs[$key] = $value;
			}
		}

		return $pairs;
	}

	/**
	 * The string a slice holds, directly or through a class constant of the page, or null.
	 */
	private function literal(array $slice)
	{
		if(count($slice) === 1 && is_array($slice[0]) && $slice[0][0] === T_CONSTANT_ENCAPSED_STRING)
		{
			return $this->value($slice[0]);
		}

		if(count($slice) === 3 && is_array($slice[1]) && $slice[1][0] === T_DOUBLE_COLON
			&& is_array($slice[2]) && $slice[2][0] === T_STRING)
		{
			$constants = $this->constants();

			return isset($constants[$slice[2][1]]) ? $constants[$slice[2][1]] : null;
		}

		return null;
	}

	/**
	 * The string class constants the page declares, keyed by name.
	 */
	private function constants()
	{
		$constants = array();

		foreach($this->tokens as $i => $token)
		{
			if(!is_array($token) || $token[0] !== T_CONST)
			{
				continue;
			}

			$name = $this->nextCode($i);
			$assign = $name === null ? null : $this->nextCode($name);
			$value = $assign === null ? null : $this->nextCode($assign);

			if($value === null || $this->tokens[$assign] !== '='
				|| !is_array($this->tokens[$value]) || $this->tokens[$value][0] !== T_CONSTANT_ENCAPSED_STRING)
			{
				continue;
			}

			$constants[$this->tokens[$name][1]] = $this->value($this->tokens[$value]);
		}

		return $constants;
	}

	/**
	 * The text between the quotes of a string token, which none of the lists read here escapes.
	 */
	private function value(array $token)
	{
		return (string) substr($token[1], 1, -1);
	}

	private function isCode($token)
	{
		return !is_array($token)
			|| ($token[0] !== T_WHITESPACE && $token[0] !== T_COMMENT && $token[0] !== T_DOC_COMMENT);
	}

	private function nextCode($offset)
	{
		for($i = $offset + 1, $n = count($this->tokens); $i < $n; $i++)
		{
			if($this->isCode($this->tokens[$i]))
			{
				return $i;
			}
		}

		return null;
	}
}
