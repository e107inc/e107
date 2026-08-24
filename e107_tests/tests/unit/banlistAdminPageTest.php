<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e107_admin/banlist.php is an entry point that cannot be included from a test,
 * so it is checked against the source: it must call nothing that exists only
 * inside the sibling banlist_export.php entry point.
 */
class banlistAdminPageTest extends \Codeception\Test\Unit
{
	/** @var string */
	private $page;

	/** @var string */
	private $exportPage;

	protected function _before()
	{
		$this->page = e_ADMIN . 'banlist.php';
		$this->exportPage = e_ADMIN . 'banlist_export.php';
	}

	public function testBanlistPageCallsNoHelperOwnedByTheExportPage()
	{
		$leaked = array_values(array_intersect(
			$this->globalFunctionCalls($this->page),
			$this->declaredFunctions($this->exportPage)
		));

		$this->assertSame(array(), $leaked,
			'banlist.php never includes banlist_export.php, so a call into it is a fatal: '
			. implode(', ', $leaked));
	}

	// ---- source helpers ----

	private function tokens($file)
	{
		$this->assertFileExists($file);

		return token_get_all(file_get_contents($file));
	}

	/**
	 * Name declared after a T_CLASS / T_FUNCTION token, or null for a closure.
	 */
	private function declaredName(array $tokens, $offset)
	{
		for($i = $offset + 1, $n = count($tokens); $i < $n; $i++)
		{
			if(is_array($tokens[$i]) && $tokens[$i][0] === T_STRING)
			{
				return $tokens[$i][1];
			}
			if(is_array($tokens[$i]) && in_array($tokens[$i][0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
			{
				continue;
			}
			if($tokens[$i] === '&')
			{
				continue;
			}

			return null;
		}

		return null;
	}

	/**
	 * Walk a file, reporting class name and brace depth to a callback.
	 */
	private function walk($file, $visit)
	{
		$tokens = $this->tokens($file);
		$depth = 0;
		$class = null;
		$classDepth = null;

		foreach($tokens as $i => $token)
		{
			if($token === '{')
			{
				$depth++;
				continue;
			}
			if($token === '}')
			{
				$depth--;
				if($classDepth !== null && $depth < $classDepth)
				{
					$class = null;
					$classDepth = null;
				}
				continue;
			}
			if(!is_array($token))
			{
				continue;
			}
			if(in_array($token[0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true))
			{
				$depth++;
				continue;
			}
			if($token[0] === T_CLASS && $this->previousCode($tokens, $i) !== T_DOUBLE_COLON)
			{
				$name = $this->declaredName($tokens, $i);
				if($name !== null)
				{
					$class = $name;
					$classDepth = $depth + 1;
					call_user_func($visit, 'class', $name, null);
				}
				continue;
			}
			if($token[0] === T_FUNCTION)
			{
				$name = $this->declaredName($tokens, $i);
				if($name !== null)
				{
					call_user_func($visit, 'function', $name, $class);
				}
				continue;
			}
			if($token[0] === T_STRING && $this->nextCode($tokens, $i) === '('
				&& !in_array($this->previousCode($tokens, $i),
					array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NEW, T_FUNCTION), true))
			{
				call_user_func($visit, 'call', $token[1], $class);
			}
		}
	}

	private function previousCode(array $tokens, $offset)
	{
		for($i = $offset - 1; $i >= 0; $i--)
		{
			if(is_array($tokens[$i]) && in_array($tokens[$i][0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
			{
				continue;
			}

			return is_array($tokens[$i]) ? $tokens[$i][0] : $tokens[$i];
		}

		return null;
	}

	private function nextCode(array $tokens, $offset)
	{
		for($i = $offset + 1, $n = count($tokens); $i < $n; $i++)
		{
			if(is_array($tokens[$i]) && in_array($tokens[$i][0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
			{
				continue;
			}

			return is_array($tokens[$i]) ? $tokens[$i][0] : $tokens[$i];
		}

		return null;
	}

	private function declaredFunctions($file)
	{
		$found = array();
		$this->walk($file, function ($kind, $name, $class) use (&$found)
		{
			if($kind === 'function' && $class === null)
			{
				$found[] = $name;
			}
		});

		return $found;
	}

	private function globalFunctionCalls($file)
	{
		$found = array();
		$this->walk($file, function ($kind, $name, $class) use (&$found)
		{
			if($kind === 'call')
			{
				$found[$name] = $name;
			}
		});

		return array_values($found);
	}
}
