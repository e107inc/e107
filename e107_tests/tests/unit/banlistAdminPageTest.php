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
 * so its two structural obligations are checked against the source: the custom
 * batch option it declares has a handler, and it calls nothing that only exists
 * inside the sibling banlist_export.php entry point.
 */
class banlistAdminPageTest extends \Test\Unit
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

	public function testFailedLoginDeleteAllBatchOptionHasAHandler()
	{
		$methods = $this->methodsByClass($this->page);

		$this->assertArrayHasKey('failed_ui', $methods, 'banlist.php must still declare failed_ui.');
		$this->assertContains('handleListDeleteAllBatch', $methods['failed_ui'],
			"The 'delete-all' batch option needs a handleListDeleteAllBatch() handler, "
			. 'or e_admin_controller_ui::_handleListBatch() treats "delete-all" as a column name.');
	}

	public function testEveryGlobalFunctionCallOnTheBanlistPageResolves()
	{
		require_once(e_HANDLER . 'upload_handler.php');

		$declared = $this->declaredFunctions($this->page);
		$missing = array();

		foreach($this->globalFunctionCalls($this->page) as $call)
		{
			if(!function_exists($call) && !in_array($call, $declared, true))
			{
				$missing[] = $call;
			}
		}

		$this->assertSame(array(), $missing,
			'banlist.php calls global functions that are defined nowhere, '
			. 'so reaching them is a fatal: ' . implode(', ', $missing));
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
			if(\Test\Tokens::opensBrace($token))
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
			if($token[0] === T_CLASS && \Test\Tokens::id(\Test\Tokens::neighbour($tokens, $i, -1)) !== T_DOUBLE_COLON)
			{
				$name = \Test\Tokens::declaredName($tokens, $i);
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
				$name = \Test\Tokens::declaredName($tokens, $i);
				if($name !== null)
				{
					call_user_func($visit, 'function', $name, $class);
				}
				continue;
			}
			if($token[0] === T_STRING && \Test\Tokens::neighbour($tokens, $i, 1) === '('
				&& !in_array(\Test\Tokens::id(\Test\Tokens::neighbour($tokens, $i, -1)),
					array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_NEW, T_FUNCTION), true))
			{
				call_user_func($visit, 'call', $token[1], $class);
			}
		}
	}

	private function methodsByClass($file)
	{
		$found = array();
		$this->walk($file, function ($kind, $name, $class) use (&$found)
		{
			if($kind === 'class')
			{
				$found[$name] = isset($found[$name]) ? $found[$name] : array();
			}
			elseif($kind === 'function' && $class !== null)
			{
				$found[$class][] = $name;
			}
		});

		return $found;
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
