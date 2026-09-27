<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

/**
 * e107_admin/theme.php cannot be included from a test, so its $adminMenu literal is read from the source.
 */
class themeAdminPageTest extends \Test\Unit
{
	/** @var array */
	private $tokens;

	protected function _before()
	{
		$page = e_ADMIN . 'theme.php';
		$this->assertFileExists($page);
		$this->tokens = $this->codeTokens(file_get_contents($page));
	}

	public function testTheAdminMenuDeclaresNoPermMarker()
	{
		$menu = $this->adminMenu();

		$this->assertNotSame(array(), $menu, 'theme.php must still declare an $adminMenu property.');

		$marked = array();
		foreach($menu as $route => $options)
		{
			if(array_key_exists('perm', $options))
			{
				$marked[] = $route . ' (perm ' . var_export($options['perm'], true) . ')';
			}
		}

		$this->assertSame(array(), $marked,
			'Nothing reads an $adminMenu perm to decide who may reach a route. e_admin_dispatcher gates '
			. 'dispatch on $access, $perm and the perm or userclass on the mode\'s entry in $modes, none '
			. 'of which this page sets, so a marker here only decides whether the entry is drawn and '
			. 'hides a route that answers anyway when its URL is typed. That is what issue 6274 was: '
			. 'theme upload was hidden from an administrator who could reach it. Gate a route where the '
			. 'dispatcher reads it, and leave the menu describing what the page does: '
			. implode(', ', $marked));
	}

	/**
	 * The tokens of a source file with whitespace and comments dropped, so the next token is the next code.
	 */
	private function codeTokens($source)
	{
		$code = array();
		foreach(token_get_all($source) as $token)
		{
			if(is_array($token) && in_array($token[0], array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT), true))
			{
				continue;
			}
			$code[] = $token;
		}

		return $code;
	}

	/**
	 * The $adminMenu literal as route => its perm markers, at any nesting, a marker it cannot read being null.
	 */
	private function adminMenu()
	{
		$start = null;
		foreach($this->tokens as $i => $token)
		{
			if(is_array($token) && $token[0] === T_VARIABLE && $token[1] === '$adminMenu'
				&& $this->isPropertyDeclaration($i))
			{
				$start = $i;
				break;
			}
		}
		if($start === null)
		{
			return array();
		}

		$entries = array();
		$route = null;
		$depth = 0;

		for($i = $start, $n = count($this->tokens); $i < $n; $i++)
		{
			$token = $this->tokens[$i];

			if($token === '(' || $token === '[')
			{
				$depth++;
				continue;
			}
			if($token === ')' || $token === ']')
			{
				$depth--;
				if($depth <= 0)
				{
					break;
				}
				continue;
			}

			$key = $this->stringAt($i);
			$arrow = isset($this->tokens[$i + 1]) ? $this->tokens[$i + 1] : null;
			if($key === null || !is_array($arrow) || $arrow[0] !== T_DOUBLE_ARROW)
			{
				continue;
			}

			if($depth === 1)
			{
				$route = $key;
				$entries[$route] = array();
			}
			elseif($key === 'perm' && $route !== null)
			{
				$entries[$route][$key] = $this->stringAt($i + 2);
			}
		}

		return $entries;
	}

	/**
	 * Whether the variable at an offset is declaring a property rather than using one of the same name.
	 */
	private function isPropertyDeclaration($offset)
	{
		for($i = $offset - 1; $i >= 0; $i--)
		{
			$token = $this->tokens[$i];
			if(is_array($token))
			{
				if(in_array($token[0], array(T_PUBLIC, T_PROTECTED, T_PRIVATE, T_VAR), true))
				{
					return true;
				}
				continue;
			}
			if($token !== '?' && $token !== '|')
			{
				return false;
			}
		}

		return false;
	}

	/**
	 * Contents of the quoted string at an offset, or null where that token is anything else.
	 */
	private function stringAt($offset)
	{
		if(!isset($this->tokens[$offset]) || !is_array($this->tokens[$offset])
			|| $this->tokens[$offset][0] !== T_CONSTANT_ENCAPSED_STRING)
		{
			return null;
		}

		return (string) substr($this->tokens[$offset][1], 1, -1);
	}
}
