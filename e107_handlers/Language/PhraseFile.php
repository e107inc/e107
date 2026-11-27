<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Language;

use e107\Source\LiteralReader;

/**
 * Reads the phrases out of a language file without running it, in the order and with the values PHP would give them.
 */
final class PhraseFile extends LiteralReader
{
	/** Defined whenever the file runs. */
	const DEFINED = 'd';

	/** Defined by an if(!defined()) guard on the same name, so defined once the file has run. */
	const GUARDED = 'g';

	/** Defined only under some other condition. */
	const CONDITIONAL = 'c';

	/** A key of the array the file returns. */
	const LISTED = 'a';

	/** @var array */
	private $phrases;

	/** @var array */
	private $how;

	/** @var array */
	private $unsupported;

	/** @var bool whether a return or exit met so far may already have ended the file */
	private $stopped = false;

	/**
	 * @param string $source
	 * @return array 'phrases' maps each name to a scalar, or to a list of strings and one-element arrays naming a constant;
	 *               'how' maps each name to one of the class constants; 'unsupported' lists the names whose value
	 *               could not be read, each mapped to its own name
	 */
	public function read($source)
	{
		$this->phrases = array();
		$this->how = array();
		$this->unsupported = array();

		if(is_string($source) && $source !== '' && function_exists('token_get_all'))
		{
			$this->tokenize($source);
			$this->walk();
		}

		return array('phrases' => $this->phrases, 'how' => $this->how, 'unsupported' => $this->unsupported);
	}

	/**
	 * Step through the file's top level, entering conditions and stopping where the file would stop.
	 *
	 * @return void
	 */
	private function walk()
	{
		$frames = array();
		$pending = null;
		$this->stopped = false;
		$pos = 0;

		while($pos < $this->count)
		{
			$token = $this->tokens[$pos];
			$id = is_array($token) ? $token[0] : null;
			$text = $this->text($pos);

			if($id !== null && $this->opensOwnScope($id))
			{
				$pos = $this->skipScope($pos);
				continue;
			}

			if($id === T_IF || $id === T_ELSEIF || $id === T_WHILE || $id === T_FOR || $id === T_FOREACH || $id === T_SWITCH || $id === T_CATCH)
			{
				$guard = $this->guardOf($pos, $after);
				$pos = $this->openBlock($frames, $pending, $after, $id === T_ELSEIF, $guard);
				continue;
			}

			if($id === T_ELSE || $id === T_DO || $id === T_TRY || (defined('T_FINALLY') && $id === T_FINALLY))
			{
				$pos = $this->openBlock($frames, $pending, $pos + 1, $id === T_ELSE, false);
				continue;
			}

			if($id === T_ENDIF || $id === T_ENDWHILE || $id === T_ENDFOR || $id === T_ENDFOREACH || $id === T_ENDSWITCH)
			{
				if(!empty($frames) && end($frames) === null)
				{
					array_pop($frames);
				}

				$pos++;
				continue;
			}

			if($id === T_CURLY_OPEN || $id === T_DOLLAR_OPEN_CURLY_BRACES)
			{
				$frames[] = false;
				$pos++;
				continue;
			}

			if($text === '{')
			{
				$frames[] = ($pending === null) ? false : $pending;
				$pending = null;
				$pos++;
				continue;
			}

			if($text === '}')
			{
				array_pop($frames);
				$pos++;
				continue;
			}

			$context = $this->context($frames, $pending);

			if($id === T_RETURN || $id === T_EXIT)
			{
				if(empty($frames) && $pending === null && $this->startsStatement($pos))
				{
					if($id === T_RETURN)
					{
						$this->readReturn($pos + 1);
					}

					return;
				}

				$this->stopped = true;
				$pos++;
				continue;
			}

			if($this->isDefineCall($pos))
			{
				$start = $this->follows($pos, array(T_NS_SEPARATOR)) ? $this->previousSignificant($pos) : $pos;
				$statement = $this->startsStatement($start);
				$pos = $this->readDefine($pos, $statement ? $context : false);

				if($statement)
				{
					$pending = null;
				}

				continue;
			}

			if($text === ';')
			{
				$pending = null;
			}

			$pos++;
		}
	}

	/**
	 * Enter what follows a control keyword: an alternative-syntax block opened by a colon, or the braces or single statement the next tokens hold.
	 *
	 * @param array             $frames
	 * @param string|false|null $pending
	 * @param int               $after  offset after the keyword and its condition
	 * @param bool              $branch whether the keyword is an else or elseif, which ends the alternative-syntax branch before it
	 * @param string|false      $guard
	 * @return int offset to resume from
	 */
	private function openBlock(array &$frames, &$pending, $after, $branch, $guard)
	{
		$next = $this->nextSignificant($after);

		if($next !== null && $this->text($next) === ':')
		{
			if($branch && !empty($frames) && end($frames) === null)
			{
				array_pop($frames);
			}

			$frames[] = null;
			$pending = null;

			return $next + 1;
		}

		$pending = ($pending === null) ? $guard : false;

		return $after;
	}

	/**
	 * Whether the token begins a statement, rather than sitting inside an expression that may never reach it.
	 *
	 * @param int $pos
	 * @return bool
	 */
	private function startsStatement($pos)
	{
		$before = $this->previousSignificant($pos);

		if($before === null || in_array($this->text($before), array(';', '{', '}', ')'), true))
		{
			return true;
		}

		if($this->isId($before, T_ELSE) || $this->isId($before, T_DO) || $this->isId($before, T_CLOSE_TAG))
		{
			return true;
		}

		if($this->text($before) === ':')
		{
			$colon = $this->previousSignificant($before);

			return $colon !== null && ($this->text($colon) === ')' || $this->isId($colon, T_ELSE));
		}

		return false;
	}

	/**
	 * @param array             $frames  one entry per open block: the guarded name, false, or null for an alternative-syntax block
	 * @param string|false|null $pending the condition of a braceless if, while its statement runs
	 * @return string|bool true at the top level, a name inside its own guard, false otherwise
	 */
	private function context(array $frames, $pending)
	{
		if($this->stopped)
		{
			return false;
		}

		if($pending !== null)
		{
			$frames[] = $pending;
		}

		if(empty($frames))
		{
			return true;
		}

		return (count($frames) === 1 && is_string($frames[0])) ? $frames[0] : false;
	}

	/**
	 * The name an `if(!defined('NAME'))` condition guards, or false for any other condition.
	 *
	 * @param int $pos   offset of the keyword
	 * @param int $after set to the offset after the closing parenthesis
	 * @return string|false
	 */
	private function guardOf($pos, &$after)
	{
		$open = $this->nextSignificant($pos + 1);

		if($open === null || $this->text($open) !== '(')
		{
			$after = $pos + 1;
			return false;
		}

		$close = $this->endOfExpression($open + 1, array(')'));
		$after = $close + 1;

		if(!$this->isId($pos, T_IF))
		{
			return false;
		}

		$not = $this->nextSignificant($open + 1, $close);

		if($not === null || $this->text($not) !== '!')
		{
			return false;
		}

		$call = $this->nextSignificant($not + 1, $close);

		if($call === null || strtolower($this->text($call)) !== 'defined')
		{
			return false;
		}

		$argOpen = $this->nextSignificant($call + 1, $close);

		if($argOpen === null || $this->text($argOpen) !== '(')
		{
			return false;
		}

		$argClose = $this->endOfExpression($argOpen + 1, array(')'));

		if($this->nextSignificant($argClose + 1, $close) !== null)
		{
			return false;
		}

		try
		{
			$name = $this->reduce($argOpen + 1, $argClose);
		}
		catch(\RuntimeException $e)
		{
			return false;
		}

		return is_string($name) ? $name : false;
	}

	/**
	 * Whether the token starts a call to the global define().
	 *
	 * @param int $pos
	 * @return bool
	 */
	private function isDefineCall($pos)
	{
		$text = strtolower($this->text($pos));

		if(defined('T_NAME_FULLY_QUALIFIED') && $this->isId($pos, T_NAME_FULLY_QUALIFIED))
		{
			$global = ($text === '\\define');
		}
		elseif($this->isId($pos, T_STRING) && $text === 'define')
		{
			$global = !$this->follows($pos, array(T_OBJECT_OPERATOR, T_DOUBLE_COLON, T_FUNCTION, T_NEW, T_CONST));

			if($global && $this->follows($pos, array(T_NS_SEPARATOR)))
			{
				$before = $this->previousSignificant($this->previousSignificant($pos));
				$global = ($before === null || !$this->isId($before, T_STRING));
			}
		}
		else
		{
			return false;
		}

		$open = $this->nextSignificant($pos + 1);

		return $global && $open !== null && $this->text($open) === '(';
	}

	/**
	 * @param int   $pos
	 * @param array $ids
	 * @return bool whether the significant token before $pos is one of $ids
	 */
	private function follows($pos, array $ids)
	{
		$before = $this->previousSignificant($pos);

		if($before === null)
		{
			return false;
		}

		if(defined('T_NULLSAFE_OBJECT_OPERATOR') && in_array(T_OBJECT_OPERATOR, $ids, true) && $this->isId($before, T_NULLSAFE_OBJECT_OPERATOR))
		{
			return true;
		}

		foreach($ids as $id)
		{
			if($this->isId($before, $id))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int|null $pos
	 * @return int|null
	 */
	private function previousSignificant($pos)
	{
		for($pos = ($pos === null ? -1 : $pos - 1); $pos >= 0; $pos--)
		{
			$token = $this->tokens[$pos];

			if(!is_array($token) || !$this->isNoise($token[0]))
			{
				return $pos;
			}
		}

		return null;
	}

	/**
	 * @param int         $pos     offset of `define`
	 * @param string|bool $context from {@see PhraseFile::context()}
	 * @return int offset to resume from
	 */
	private function readDefine($pos, $context)
	{
		$open = $this->nextSignificant($pos + 1);
		$nameEnd = $this->endOfExpression($open + 1, array(',', ')'));
		$close = $this->endOfExpression($open + 1, array(')'));

		try
		{
			$name = $this->reduce($open + 1, $nameEnd);
		}
		catch(\RuntimeException $e)
		{
			return $close + 1;
		}

		if(!is_string($name) || $name === '' || $this->text($nameEnd) !== ',' || isset($this->how[$name]) && $this->how[$name] !== self::LISTED)
		{
			return $close + 1;
		}

		if($context === true)
		{
			$how = self::DEFINED;
		}
		elseif($context === $name)
		{
			$how = self::GUARDED;
		}
		else
		{
			$how = self::CONDITIONAL;
		}

		$this->keep($name, $nameEnd + 1, $this->endOfExpression($nameEnd + 1, array(',', ')')), $how);

		return $close + 1;
	}

	/**
	 * Read the entries of a returned array literal one at a time.
	 *
	 * @param int $pos first token after `return`
	 * @return void
	 */
	private function readReturn($pos)
	{
		$open = $this->nextSignificant($pos);

		if($this->isId($open, T_ARRAY))
		{
			$open = $this->nextSignificant($open + 1);
			$close = ')';
		}
		else
		{
			$close = ']';
		}

		if($open === null || $this->text($open) !== ($close === ')' ? '(' : '['))
		{
			return;
		}

		$pos = $open + 1;
		$listed = array();

		while($pos < $this->count)
		{
			$entryEnd = $this->endOfExpression($pos, array(',', $close));
			$arrow = $this->endOfExpression($pos, array(T_DOUBLE_ARROW, ',', $close));

			if($arrow < $entryEnd && $this->isId($arrow, T_DOUBLE_ARROW))
			{
				try
				{
					$name = $this->reduce($pos, $arrow);
				}
				catch(\RuntimeException $e)
				{
					$name = null;
				}

				if(is_string($name) && $name !== '' && (!isset($this->how[$name]) || isset($listed[$name])))
				{
					$this->keep($name, $arrow + 1, $entryEnd, $this->stopped ? self::CONDITIONAL : self::LISTED);
					$listed[$name] = true;
				}
			}

			if($entryEnd >= $this->count - 1 || $this->text($entryEnd) === $close)
			{
				return;
			}

			$pos = $entryEnd + 1;
		}
	}

	/**
	 * @param string $name
	 * @param int    $from first token of the value
	 * @param int    $to   exclusive
	 * @param string $how
	 * @return void
	 */
	private function keep($name, $from, $to, $how)
	{
		try
		{
			$value = $this->reduce($from, $to);
			unset($this->unsupported[$name]);
		}
		catch(\RuntimeException $e)
		{
			$value = $name;
			$this->unsupported[$name] = $name;
		}

		$this->phrases[$name] = $value;
		$this->how[$name] = $how;
	}

	/**
	 * @param string $name
	 * @return mixed true, false and null as themselves, any other constant as a part to resolve later
	 */
	protected function constantTerm($name)
	{
		switch(strtolower($name))
		{
			case 'true':
				return true;

			case 'false':
				return false;

			case 'null':
				return null;
		}

		return array(array($name));
	}

	/**
	 * @param mixed $left
	 * @param mixed $right
	 * @return string|array a string while every part is known, otherwise the list of parts
	 */
	protected function concat($left, $right)
	{
		$parts = array();

		foreach(array($left, $right) as $side)
		{
			foreach(is_array($side) ? $side : array((string) $side) as $part)
			{
				$last = count($parts) - 1;

				if(is_string($part) && $last >= 0 && is_string($parts[$last]))
				{
					$parts[$last] .= $part;
				}
				else
				{
					$parts[] = $part;
				}
			}
		}

		return (count($parts) === 1 && is_string($parts[0])) ? $parts[0] : $parts;
	}

	/**
	 * @param int $pos
	 * @return void
	 * @throws \RuntimeException always
	 */
	protected function interpolation($pos)
	{
		throw new \RuntimeException('interpolation');
	}

	/**
	 * @param int $pos
	 * @param int $after
	 * @return array
	 * @throws \RuntimeException always
	 */
	protected function readArray($pos, &$after)
	{
		throw new \RuntimeException('array value');
	}
}
