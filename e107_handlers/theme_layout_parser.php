<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

if(!defined('e107_INIT')) { exit; }

/**
 * Reads the layout variables out of a pre-v2.2.2 theme.php without running it.
 *
 * Themes older than v2.2.2 declare their layouts as $HEADER, $FOOTER,
 * $CUSTOMHEADER, $CUSTOMFOOTER and $LAYOUT inside theme.php. The menu manager
 * used to recover them by rewriting the file with a chain of regular
 * expressions and handing the result to eval(); this tokenises the source and
 * folds the assignments instead, so no theme code is ever executed.
 *
 * The grammar covered is what real themes use: string literals, heredocs and
 * nowdocs, array literals, element assignment ($LAYOUT['home'] = '...') and
 * concatenation with constants. An assignment that does not fold, such as one
 * built by a function call, keeps its raw source as the value: the caller only
 * counts {MENU=n} placeholders, and those survive intact.
 */
class e_theme_layout_parser extends \e107\Source\LiteralReader
{
	/**
	 * Variables worth collecting, in $name form.
	 * @var array
	 */
	protected $wanted = array('$HEADER', '$FOOTER', '$CUSTOMHEADER', '$CUSTOMFOOTER', '$LAYOUT');

	/**
	 * Constant values that depend on the theme being read rather than the live one.
	 * @var array
	 */
	protected $constants = array();

	/**
	 * @param string      $source contents of a theme.php
	 * @param string|null $theme  theme folder name, used to resolve THEME and THEME_ABS
	 * @return array keys HEADER, FOOTER, CUSTOMHEADER, CUSTOMFOOTER and LAYOUT, each
	 *               a string, an array, or null when the theme never assigns it
	 */
	public function parse($source, $theme = null)
	{
		$result = array(
			'HEADER'       => null,
			'FOOTER'       => null,
			'CUSTOMHEADER' => null,
			'CUSTOMFOOTER' => null,
			'LAYOUT'       => null,
		);

		if(!function_exists('token_get_all') || !is_string($source) || $source === '')
		{
			return $result;
		}

		$this->constants = $this->themeConstants($theme);
		$this->tokenize($source);

		$pos = 0;

		while($pos < $this->count)
		{
			$token = $this->tokens[$pos];

			if(is_array($token) && $this->opensOwnScope($token[0]))
			{
				$pos = $this->skipScope($pos);
				continue;
			}

			if(is_array($token) && $token[0] === T_VARIABLE && in_array($token[1], $this->wanted, true))
			{
				$pos = $this->readAssignment($pos, $result);
				continue;
			}

			$pos++;
		}

		return $result;
	}

	/**
	 * Fold one `$VAR = ...;` or `$VAR['key'] = ...;` statement into $result.
	 *
	 * @param int   $pos    offset of the T_VARIABLE token
	 * @param array $result collected values, modified in place
	 * @return int offset to resume scanning from
	 */
	protected function readAssignment($pos, &$result)
	{
		$name = (string) substr($this->tokens[$pos][1], 1);
		$next = $this->nextSignificant($pos + 1);
		$key = null;

		if($next !== null && $this->text($next) === '[')
		{
			$keyAt = $this->nextSignificant($next + 1);

			try
			{
				$key = $this->readScalar($keyAt, $after);
			}
			catch(Exception $e)
			{
				return $pos + 1;
			}

			$after = $this->nextSignificant($after);

			if($after === null || $this->text($after) !== ']')
			{
				return $pos + 1;
			}

			$next = $this->nextSignificant($after + 1);
		}

		if($next === null)
		{
			return $pos + 1;
		}

		$append = $this->isId($next, T_CONCAT_EQUAL);

		if(!$append && $this->text($next) !== '=')
		{
			return $pos + 1; // the variable is being read, not written
		}

		$from = $next + 1;
		$to = $this->endOfStatement($from);

		try
		{
			$value = $this->reduce($from, $to);
		}
		catch(Exception $e)
		{
			$value = $this->rawSource($from, $to);
		}

		$this->store($result, $name, $key, $value, $append);

		return $to + 1;
	}

	/**
	 * @param array      $result
	 * @param string     $name
	 * @param string|int $key    null for a whole-variable assignment
	 * @param mixed      $value
	 * @param bool       $append true for `.=`
	 * @return void
	 */
	protected function store(&$result, $name, $key, $value, $append)
	{
		if(!array_key_exists($name, $result))
		{
			return;
		}

		if($key === null)
		{
			if($append && is_string($result[$name]) && is_string($value))
			{
				$result[$name] .= $value;
				return;
			}

			$result[$name] = $value;
			return;
		}

		if(!is_array($result[$name]))
		{
			$result[$name] = array();
		}

		if($append && isset($result[$name][$key]) && is_string($result[$name][$key]) && is_string($value))
		{
			$result[$name][$key] .= $value;
			return;
		}

		$result[$name][$key] = $value;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	protected function constantTerm($name)
	{
		if(isset($this->constants[$name]))
		{
			return $this->constants[$name];
		}

		if(defined($name))
		{
			$value = constant($name);

			return is_scalar($value) ? (string) $value : '';
		}

		return '';
	}

	/**
	 * THEME and THEME_ABS point at the live theme, not the one being read.
	 *
	 * @param string|null $theme
	 * @return array
	 */
	protected function themeConstants($theme)
	{
		if(empty($theme))
		{
			return array();
		}

		$abs = defined('e_THEME_ABS') ? e_THEME_ABS : '';

		return array(
			'THEME'     => e_THEME.$theme.'/',
			'THEME_ABS' => $abs.$theme.'/',
		);
	}
}
