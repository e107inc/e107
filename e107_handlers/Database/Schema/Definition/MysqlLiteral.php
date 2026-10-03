<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Definition;

use e107\Database\Exception\UnsupportedException;
use e107\Database\SqlLexer;
use InvalidArgumentException;

require_once(__DIR__.'/ColumnDefinition.php');

if(!class_exists(SqlLexer::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/SqlLexer.php');
}

/**
 * Reads one MySQL literal as the value a column stores it as; a hexadecimal or bit literal is bytes in a string
 * column and a number in a numeric one, so it is read only for a known column, and X'..' not for a numeric one but BIT.
 *
 * <code>
 * $i = 0;
 * list($kind, $value) = (new MysqlLiteral())->read(SqlLexer::mysql()->significantTokens("0x41"), $i, $column);
 * </code>
 */
final class MysqlLiteral
{
	/** a hexadecimal or bit literal spells bytes */
	const STRING = 'string';

	/** a hexadecimal or bit literal spells a number */
	const NUMBER = 'number';

	/** a hexadecimal or bit literal spells a number, X'..' included */
	const BIT = 'bit';

	/**
	 * Whether text is a decimal number as MySQL and SQLite both write one: an optional sign, digits with an optional
	 * point, an optional exponent.
	 *
	 * @param string $text
	 * @return bool
	 */
	public static function isDecimal($text)
	{
		return (bool) preg_match('/^[+-]?(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?$/D', (string) $text);
	}

	/**
	 * @param array[] $tokens significant tokens from {@see SqlLexer::mysql()}
	 * @param int $i position of the literal; moved past it when there is one
	 * @param ColumnDefinition|null $column the column the value is stored in, or null when it is not known
	 * @return array|null array(ColumnDefinition::DEFAULT_LITERAL, string) or array(ColumnDefinition::DEFAULT_NULL, null);
	 *                    null, with $i unmoved, when no literal starts at $i
	 * @throws InvalidArgumentException on a hexadecimal literal with an odd number of digits
	 * @throws UnsupportedException on a hexadecimal or bit literal for no known column, or a number PHP cannot hold
	 */
	public function read(array $tokens, &$i, $column = null)
	{
		if(!isset($tokens[$i]))
		{
			return null;
		}

		$token = $tokens[$i];
		$next = isset($tokens[$i + 1]) ? $tokens[$i + 1] : null;

		if($token['type'] === SqlLexer::T_STRING)
		{
			$i++;

			return array(ColumnDefinition::DEFAULT_LITERAL, $this->concatenated($tokens, $i, $token['value']));
		}

		if($token['type'] === SqlLexer::T_NUMBER)
		{
			$i++;

			return array(ColumnDefinition::DEFAULT_LITERAL, $this->number($token['text'], $this->context($column)));
		}

		if($token['type'] === SqlLexer::T_SYMBOL && ($token['text'] === '-' || $token['text'] === '+') && $next !== null && $next['type'] === SqlLexer::T_NUMBER)
		{
			$i += 2;
			$number = $this->number($next['text'], (self::isDecimal($next['text']) && $this->context($column) === self::STRING) ? self::STRING : self::NUMBER);

			return array(ColumnDefinition::DEFAULT_LITERAL, ($token['text'] === '-' && trim($number, '0.') !== '') ? '-'.$number : $number);
		}

		if($token['type'] !== SqlLexer::T_WORD)
		{
			return null;
		}

		$word = strtoupper($token['text']);

		if($word === 'NULL')
		{
			$i++;

			return array(ColumnDefinition::DEFAULT_NULL, null);
		}

		if($word === 'TRUE' || $word === 'FALSE')
		{
			$i++;

			return array(ColumnDefinition::DEFAULT_LITERAL, ($word === 'TRUE') ? '1' : '0');
		}

		if(($word[0] === '_' || $word === 'N') && $next !== null && $next['type'] === SqlLexer::T_STRING)
		{
			$i += 2;

			return array(ColumnDefinition::DEFAULT_LITERAL, $this->concatenated($tokens, $i, $next['value']));
		}

		if($word[0] === '_' && $next !== null && $next['type'] === SqlLexer::T_NUMBER)
		{
			$i += 2;

			return array(ColumnDefinition::DEFAULT_LITERAL, $this->number($next['text'], self::STRING));
		}

		return null;
	}

	/**
	 * @param array[] $tokens
	 * @param int $i position after a string; moved past the strings that follow it
	 * @param string $value the string
	 * @return string the string with every string that follows it appended, as MySQL joins adjacent strings
	 */
	private function concatenated(array $tokens, &$i, $value)
	{
		while(isset($tokens[$i]) && $tokens[$i]['type'] === SqlLexer::T_STRING)
		{
			$value .= $tokens[$i++]['value'];
		}

		return $value;
	}

	/**
	 * @param ColumnDefinition|null $column
	 * @return string|null the MysqlLiteral context a value for the column is read in; null when the column is not known
	 */
	private function context($column)
	{
		if(!$column instanceof ColumnDefinition)
		{
			return null;
		}

		if($column->getType() === 'bit')
		{
			return self::BIT;
		}

		return $column->isNumeric() ? self::NUMBER : self::STRING;
	}

	/**
	 * @param string $text a numeric literal: decimal, 0x.., X'..', 0b.. or b'..'
	 * @param string|null $context one of the context constants, or null when the column is not known
	 * @return string
	 */
	private function number($text, $context)
	{
		if(preg_match('/^0x([0-9A-Fa-f]+)$/D', $text, $match) || preg_match("/^[xX]'([0-9A-Fa-f]*)'$/D", $text, $match))
		{
			$hex = $match[1];
			$quoted = ($text[0] !== '0');

			if($quoted && strlen($hex) % 2)
			{
				throw new InvalidArgumentException('A hexadecimal literal needs an even number of digits: '.$text);
			}

			if($quoted && $context === self::NUMBER)
			{
				throw new UnsupportedException('MySQL reads '.$text.' as a string, which a numeric column does not take.');
			}

			return ($this->requireContext($context, $text) === self::STRING) ? (string) pack('H*', (strlen($hex) % 2) ? '0'.$hex : $hex) : $this->integer(hexdec('0'.$hex), $text);
		}

		if(preg_match('/^0b([01]+)$/D', $text, $match) || preg_match("/^[bB]'([01]*)'$/D", $text, $match))
		{
			return ($this->requireContext($context, $text) === self::STRING) ? $this->bytes($match[1]) : $this->integer(bindec('0'.$match[1]), $text);
		}

		return ($context === self::STRING) ? $this->decimalText($text) : $text;
	}

	/**
	 * @param string $text an unsigned decimal literal
	 * @return string the text MySQL stores for it in a string column: no leading zeros, the fraction as written
	 * @throws UnsupportedException on an exponent, which MySQL stores as it prints a double
	 */
	private function decimalText($text)
	{
		if(!preg_match('/^(\d*)(?:\.(\d*))?$/D', $text, $match))
		{
			throw new UnsupportedException('MySQL stores '.$text.' in a string column as it prints a double; write the default as a string.');
		}

		$whole = ltrim($match[1], '0');

		return (($whole === '') ? '0' : $whole).((isset($match[2]) && $match[2] !== '') ? '.'.$match[2] : '');
	}

	/**
	 * @param string|null $context
	 * @param string $text the literal, for the message
	 * @return string the context
	 * @throws UnsupportedException when the column, and so the meaning of the literal, is not known
	 */
	private function requireContext($context, $text)
	{
		if($context === null)
		{
			throw new UnsupportedException('The column decides what '.$text.' stores, and it is not known here.');
		}

		return $context;
	}

	/**
	 * @param int|float $number hexdec() or bindec() of a literal
	 * @param string $text the literal, for the message
	 * @return string
	 * @throws UnsupportedException when the value is past PHP_INT_MAX
	 */
	private function integer($number, $text)
	{
		if(!is_int($number))
		{
			throw new UnsupportedException('PHP cannot hold the value of '.$text.' exactly.');
		}

		return (string) $number;
	}

	/**
	 * @param string $bits
	 * @return string the bytes the bits spell, the first padded on the left with zeros
	 */
	private function bytes($bits)
	{
		$bytes = '';

		if($bits === '')
		{
			return $bytes;
		}

		foreach(str_split(str_pad($bits, (int) ceil(strlen($bits) / 8) * 8, '0', STR_PAD_LEFT), 8) as $byte)
		{
			$bytes .= chr(bindec($byte));
		}

		return $bytes;
	}
}
