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

require_once(__DIR__.'/MysqlLiteral.php');
require_once(__DIR__.'/TableDefinition.php');

if(!class_exists(SqlLexer::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/SqlLexer.php');
}

/**
 * Reads e107's schema DSL, MySQL's CREATE TABLE text whole or in fragments, into the engine-neutral
 * {@see TableDefinition}; a construct the model cannot hold is refused with {@see UnsupportedException}.
 *
 * <code>
 * $parser = new MysqlDdlParser();
 * $table = $parser->parseCreateTable('CREATE TABLE news (news_id int(10) unsigned NOT NULL auto_increment, PRIMARY KEY (news_id)) ENGINE=InnoDB;');
 * $column = $parser->parseColumn('user_twitter', "VARCHAR(255) NOT NULL DEFAULT ''");
 * </code>
 */
final class MysqlDdlParser
{
	/** @var SqlLexer */
	private $lexer;

	/** @var MysqlLiteral */
	private $literal;

	/** @var string[] MySQL's type synonyms => the type each stands for */
	private static $synonyms = array(
		'int1' => 'tinyint', 'int2' => 'smallint', 'int3' => 'mediumint', 'middleint' => 'mediumint', 'int4' => 'int',
		'int8' => 'bigint', 'float4' => 'float', 'float8' => 'double', 'nvarchar' => 'varchar', 'varcharacter' => 'varchar',
	);

	/** @var string[] text types => the binary type a binary character set makes of each */
	private static $binaryTypes = array(
		'char' => 'binary', 'varchar' => 'varbinary', 'tinytext' => 'tinyblob', 'text' => 'blob', 'mediumtext' => 'mediumblob',
		'longtext' => 'longblob',
	);

	/** @var array[] significant tokens of the text being parsed */
	private $tokens = array();

	/** @var int position in $tokens */
	private $pos = 0;

	/** @var string the text being parsed, for error messages */
	private $source = '';

	public function __construct()
	{
		$this->lexer = SqlLexer::mysql();
		$this->literal = new MysqlLiteral();
	}

	/**
	 * Parse one CREATE TABLE statement.
	 *
	 * @param string $sql
	 * @return TableDefinition
	 * @throws InvalidArgumentException on text that is not a CREATE TABLE statement
	 * @throws UnsupportedException on a construct the model cannot hold
	 */
	public function parseCreateTable($sql)
	{
		$this->start($sql);

		$this->expectWord('CREATE');
		$this->acceptWord('TEMPORARY');
		$this->expectWord('TABLE');

		if($this->acceptWord('IF'))
		{
			$this->expectWord('NOT');
			$this->expectWord('EXISTS');
		}

		$name = $this->readName();

		if($this->acceptSymbol('.'))
		{
			$name = $this->readName();
		}

		$this->expectSymbol('(');
		list($columns, $indexes) = $this->readCreateDefinitions();
		$this->expectSymbol(')');

		$options = $this->readTableOptions();
		$this->acceptSymbol(';');
		$this->expectEnd();

		return new TableDefinition($name, $this->withTableDefaults($columns, $options), $indexes, $options);
	}

	/**
	 * Parse the text between a CREATE TABLE statement's parentheses.
	 *
	 * @param string $name table name to give the definition
	 * @param string $body column and index definitions, comma-separated
	 * @param array $options table options, lowercased name => value, as {@see MysqlDdlParser::parseTableOptions()} reads them
	 * @return TableDefinition
	 * @throws InvalidArgumentException|UnsupportedException
	 */
	public function parseTableBody($name, $body, array $options = array())
	{
		$this->start($body);
		list($columns, $indexes) = $this->readCreateDefinitions();
		$this->expectEnd();

		return new TableDefinition($name, $this->withTableDefaults($columns, $options), $indexes, $options);
	}

	/**
	 * Parse a table options tail such as "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4".
	 *
	 * @param string $sql
	 * @return array lowercased option name => value
	 */
	public function parseTableOptions($sql)
	{
		$this->start($sql);
		$options = $this->readTableOptions();
		$this->acceptSymbol(';');
		$this->expectEnd();

		return $options;
	}

	/**
	 * Parse a column definition written without its name, e.g. "INT(10) UNSIGNED NOT NULL DEFAULT '0'"; a key on it is refused.
	 *
	 * @param string $name the column's name
	 * @param string $definition
	 * @return ColumnDefinition
	 * @throws InvalidArgumentException|UnsupportedException
	 */
	public function parseColumn($name, $definition)
	{
		$this->start($definition);
		$inline = array();
		$column = $this->readColumnBody($name, $inline);
		$this->expectEnd();

		if(!empty($inline))
		{
			throw new UnsupportedException('A column definition on its own cannot declare a key ("'.$definition.'").');
		}

		return $column;
	}

	/**
	 * Parse a column definition that starts with its name, e.g. "`user_twitter` VARCHAR(255) NOT NULL".
	 *
	 * @param string $definition
	 * @return ColumnDefinition
	 * @throws InvalidArgumentException|UnsupportedException
	 */
	public function parseNamedColumn($definition)
	{
		$this->start($definition);
		$name = $this->readName();
		$inline = array();
		$column = $this->readColumnBody($name, $inline);
		$this->expectEnd();

		if(!empty($inline))
		{
			throw new UnsupportedException('A column definition on its own cannot declare a key ("'.$definition.'").');
		}

		return $column;
	}

	/**
	 * Parse one index definition, e.g. "UNIQUE KEY `u` (`a`, `b`(20))" or "PRIMARY KEY (id)".
	 *
	 * @param string $definition
	 * @return IndexDefinition
	 * @throws InvalidArgumentException|UnsupportedException
	 */
	public function parseIndex($definition)
	{
		$this->start($definition);
		$index = $this->readIndex();

		if($index === null)
		{
			throw new InvalidArgumentException('Not an index definition: '.$definition);
		}

		$this->expectEnd();

		$names = array();

		return $this->named($index, $names);
	}

	/**
	 * @param string $sql
	 * @return void
	 */
	private function start($sql)
	{
		$this->source = (string) $sql;
		$this->tokens = $this->lexer->significantTokens($this->source);
		$this->pos = 0;
	}

	/**
	 * Column and index definitions up to the closing parenthesis or the end of the text.
	 *
	 * @return array array(ColumnDefinition[], IndexDefinition[])
	 */
	private function readCreateDefinitions()
	{
		$columns = array();
		$pending = array();

		do
		{
			if($this->atEnd() || $this->isSymbol(')'))
			{
				break;
			}

			$index = $this->readIndex();

			if($index !== null)
			{
				$pending[] = $index;
				continue;
			}

			$name = $this->readName();
			$inline = array();
			$columns[] = $this->readColumnBody($name, $inline);

			if(!empty($inline))
			{
				$kind = in_array(IndexDefinition::KIND_PRIMARY, $inline, true) ? IndexDefinition::KIND_PRIMARY : IndexDefinition::KIND_UNIQUE;
				$pending[] = array('name' => ($kind === IndexDefinition::KIND_PRIMARY) ? null : $name, 'kind' => $kind, 'parts' => array(array('column' => $name)));
			}
		}
		while($this->acceptSymbol(','));

		return array($columns, $this->namedIndexes($pending, $columns));
	}

	/**
	 * @param array[] $pending indexes from {@see MysqlDdlParser::readIndex()}, named or not
	 * @param ColumnDefinition[] $columns the table's columns, whose declared names the unnamed indexes take
	 * @return IndexDefinition[] each named, the unnamed after every named one has claimed its name, as MySQL does
	 */
	private function namedIndexes(array $pending, array $columns)
	{
		$declared = array();
		$taken = array();

		foreach($columns as $column)
		{
			$declared[strtolower($column->getName())] = $column->getName();
		}

		foreach($pending as $index)
		{
			if($index['name'] !== null)
			{
				$taken[strtolower($index['name'])] = true;
			}
		}

		$indexes = array();

		foreach($pending as $index)
		{
			$first = strtolower($index['parts'][0]['column']);

			if($index['name'] === null && isset($declared[$first]))
			{
				$index['parts'][0]['column'] = $declared[$first];
			}

			$indexes[] = $this->named($index, $taken);
		}

		return $indexes;
	}

	/**
	 * An index definition at the current position, or null (position unchanged) when a column definition starts here.
	 *
	 * @return array|null array('name' => string|null, 'kind' => IndexDefinition::KIND_*, 'parts' => array)
	 */
	private function readIndex()
	{
		$save = $this->pos;
		$symbol = null;

		if($this->acceptWord('CONSTRAINT'))
		{
			if(!$this->isWord('PRIMARY') && !$this->isWord('UNIQUE') && !$this->isWord('FOREIGN') && !$this->isWord('CHECK'))
			{
				$symbol = $this->readName();
			}
		}

		if($this->isWord('FOREIGN') || $this->isWord('CHECK'))
		{
			throw new UnsupportedException('e107 schemas cannot declare '.strtoupper($this->peek('text')).' constraints: '.$this->source);
		}

		$kind = null;
		$name = $symbol;

		if($this->acceptWord('PRIMARY'))
		{
			$this->expectWord('KEY');
			$kind = IndexDefinition::KIND_PRIMARY;
		}
		elseif($this->acceptWord('UNIQUE'))
		{
			$this->acceptWord('KEY') || $this->acceptWord('INDEX');
			$kind = IndexDefinition::KIND_UNIQUE;
		}
		elseif($this->acceptWord('FULLTEXT') || $this->acceptWord('SPATIAL'))
		{
			$kind = (strtoupper($this->tokens[$this->pos - 1]['text']) === 'FULLTEXT') ? IndexDefinition::KIND_FULLTEXT : IndexDefinition::KIND_SPATIAL;
			$this->acceptWord('KEY') || $this->acceptWord('INDEX');
		}
		elseif($this->acceptWord('KEY') || $this->acceptWord('INDEX'))
		{
			$kind = IndexDefinition::KIND_INDEX;
		}
		else
		{
			$this->pos = $save;
			return null;
		}

		if(!$this->isSymbol('(') && !$this->isWord('USING'))
		{
			$name = $this->readName();
		}

		$this->skipIndexType();
		$parts = $this->readKeyParts();
		$this->skipIndexOptions();

		return array('name' => $name, 'kind' => $kind, 'parts' => $parts);
	}

	/**
	 * @return void
	 */
	private function skipIndexType()
	{
		if($this->acceptWord('USING'))
		{
			$this->readWord();
		}
	}

	/**
	 * @return void
	 */
	private function skipIndexOptions()
	{
		while(true)
		{
			if($this->acceptWord('USING'))
			{
				$this->readWord();
			}
			elseif($this->acceptWord('COMMENT'))
			{
				$this->readString();
			}
			elseif($this->acceptWord('KEY_BLOCK_SIZE'))
			{
				$this->acceptSymbol('=');
				$this->readValue();
			}
			elseif($this->acceptWord('WITH'))
			{
				$this->expectWord('PARSER');
				$this->readName();
			}
			elseif($this->acceptWord('VISIBLE') || $this->acceptWord('INVISIBLE'))
			{
				continue;
			}
			else
			{
				return;
			}
		}
	}

	/**
	 * A parenthesised key part list: name [(length)] [ASC|DESC], ...
	 *
	 * @return array[]
	 */
	private function readKeyParts()
	{
		$this->expectSymbol('(');
		$parts = array();

		do
		{
			if($this->isSymbol('('))
			{
				throw new UnsupportedException('e107 schemas cannot declare functional key parts: '.$this->source);
			}

			$part = array('column' => $this->readName(), 'length' => null, 'direction' => 'ASC');

			if($this->acceptSymbol('('))
			{
				$part['length'] = (int) $this->readNumber();
				$this->expectSymbol(')');
			}

			if($this->acceptWord('DESC'))
			{
				$part['direction'] = 'DESC';
			}
			else
			{
				$this->acceptWord('ASC');
			}

			$parts[] = $part;
		}
		while($this->acceptSymbol(','));

		$this->expectSymbol(')');

		return $parts;
	}

	/**
	 * Give a parsed index its name, taking MySQL's name for an unnamed key.
	 *
	 * @param array $index from {@see MysqlDdlParser::readIndex()}
	 * @param array $taken lowercased names already used => true; updated
	 * @return IndexDefinition
	 */
	private function named(array $index, array &$taken)
	{
		$name = $index['name'];

		if($index['kind'] === IndexDefinition::KIND_PRIMARY)
		{
			$name = 'PRIMARY';
		}
		elseif($name === null)
		{
			$base = $index['parts'][0]['column'];
			$name = $base;

			for($n = 2; isset($taken[strtolower($name)]) || strtolower($name) === 'primary'; $n++)
			{
				$name = $base.'_'.$n;
			}
		}

		$taken[strtolower($name)] = true;

		return new IndexDefinition($name, $index['kind'], $index['parts']);
	}

	/**
	 * The type and attributes of a column whose name has been read.
	 *
	 * @param string $name
	 * @param string[] $inline receives the kinds of inline keys (PRIMARY KEY, UNIQUE) the column declares, which make
	 *                         one key between them
	 * @return ColumnDefinition
	 */
	private function readColumnBody($name, array &$inline)
	{
		$attributes = array();
		$type = $this->readType($attributes);

		if($type === 'serial')
		{
			$type = 'bigint';
			$attributes += array('unsigned' => true, 'nullable' => false, 'autoIncrement' => true);
			$inline[] = IndexDefinition::KIND_UNIQUE;
		}

		if($this->acceptSymbol('('))
		{
			if($type === 'enum' || $type === 'set')
			{
				$members = array();
				do
				{
					$members[] = $this->readString();
				}
				while($this->acceptSymbol(','));
				$attributes['members'] = $members;
			}
			else
			{
				$length = $this->readNumber();
				if($this->acceptSymbol(','))
				{
					$length .= ','.$this->readNumber();
				}
				$attributes['length'] = $length;
			}

			$this->expectSymbol(')');
		}

		while(!$this->atEnd() && !$this->isSymbol(',') && !$this->isSymbol(')'))
		{
			$word = strtoupper($this->readWord());

			switch($word)
			{
				case 'UNSIGNED':
					$attributes['unsigned'] = true;
					break;

				case 'SIGNED':
					break;

				case 'ZEROFILL':
					$attributes['zerofill'] = true;
					break;

				case 'NOT':
					$this->expectWord('NULL');
					$attributes['nullable'] = false;
					break;

				case 'NULL':
					$attributes['nullable'] = true;
					break;

				case 'DEFAULT':
					list($attributes['defaultKind'], $attributes['default']) = $this->readDefault(new ColumnDefinition($name, $type));
					break;

				case 'AUTO_INCREMENT':
					$attributes['autoIncrement'] = true;
					break;

				case 'CHARACTER':
					$this->expectWord('SET');
					$attributes['charset'] = $this->readWordOrString();
					break;

				case 'CHARSET':
					$attributes['charset'] = $this->readWordOrString();
					break;

				case 'ASCII':
					$attributes['charset'] = 'latin1';
					break;

				case 'UNICODE':
					$attributes['charset'] = 'ucs2';
					break;

				case 'BYTE':
					$attributes['charset'] = 'binary';
					break;

				case 'SERIAL':
					$this->expectWord('DEFAULT');
					$this->expectWord('VALUE');
					$attributes['nullable'] = false;
					$attributes['autoIncrement'] = true;
					$inline[] = IndexDefinition::KIND_UNIQUE;
					break;

				case 'COLLATE':
					$attributes['collation'] = $this->readWordOrString();
					break;

				case 'COMMENT':
					$attributes['comment'] = $this->readString();
					break;

				case 'ON':
					$this->expectWord('UPDATE');
					$attributes['onUpdate'] = $this->readExpressionDefault();
					break;

				case 'PRIMARY':
					$this->expectWord('KEY');
					$inline[] = IndexDefinition::KIND_PRIMARY;
					break;

				case 'KEY':
					$inline[] = IndexDefinition::KIND_PRIMARY;
					break;

				case 'UNIQUE':
					$this->acceptWord('KEY');
					$inline[] = IndexDefinition::KIND_UNIQUE;
					break;

				case 'BINARY':
					$attributes['collation'] = 'binary';
					break;

				case 'COLUMN_FORMAT':
				case 'STORAGE':
					$this->readWord();
					break;

				case 'VISIBLE':
				case 'INVISIBLE':
					break;

				case 'GENERATED':
				case 'AS':
				case 'REFERENCES':
				case 'CHECK':
					throw new UnsupportedException('e107 schemas cannot declare '.$word.' on a column: '.$this->source);

				default:
					throw new InvalidArgumentException('Unexpected "'.$word.'" in the definition of column "'.$name.'": '.$this->source);
			}
		}

		return $this->column($name, $type, $attributes);
	}

	/**
	 * @param array $attributes receives the character set a NATIONAL type implies
	 * @return string the column's base type, a synonym read as the type it stands for
	 */
	private function readType(array &$attributes)
	{
		$type = strtolower($this->readWord());

		if($type === 'national' || $type === 'nchar' || $type === 'nvarchar')
		{
			$attributes['charset'] = 'utf8';
		}

		if($type === 'national')
		{
			$type = strtolower($this->readWord());
		}

		if($type === 'double')
		{
			$this->acceptWord('PRECISION');
		}

		if($type === 'long')
		{
			if($this->acceptWord('VARBINARY'))
			{
				return 'mediumblob';
			}

			$this->acceptWord('VARCHAR');

			return 'mediumtext';
		}

		if($type === 'char' || $type === 'character' || $type === 'nchar')
		{
			return ($this->acceptWord('VARYING') || $this->acceptWord('VARCHAR')) ? 'varchar' : 'char';
		}

		return isset(self::$synonyms[$type]) ? self::$synonyms[$type] : $type;
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param array $attributes as {@see ColumnDefinition::__construct()} takes them
	 * @return ColumnDefinition the column, a text type in the binary character set made the binary type MySQL makes it
	 */
	private function column($name, $type, array $attributes)
	{
		if(isset($attributes['charset']) && strtolower($attributes['charset']) === 'binary')
		{
			if(isset(self::$binaryTypes[$type]))
			{
				$type = self::$binaryTypes[$type];
				unset($attributes['charset'], $attributes['collation']);
			}
			else
			{
				$attributes['collation'] = 'binary';
			}
		}

		return new ColumnDefinition($name, $type, $attributes);
	}

	/**
	 * @param ColumnDefinition[] $columns
	 * @param array $options table options, lowercased name => value
	 * @return ColumnDefinition[] the columns, each text column that names no character set or collation of its own
	 *                            given the table's, as MySQL gives it
	 */
	private function withTableDefaults(array $columns, array $options)
	{
		$binary = (isset($options['charset']) && strtolower($options['charset']) === 'binary') || (isset($options['collate']) && strtolower($options['collate']) === 'binary');

		if(!$binary && !isset($options['collate']))
		{
			return $columns;
		}

		$inherited = array();

		foreach($columns as $column)
		{
			$attributes = $column->toArray();
			unset($attributes['name'], $attributes['type']);

			if($column->isText() && $column->getType() !== 'json' && $column->getCharset() === null && $column->getCollation() === null)
			{
				if($binary)
				{
					$attributes['charset'] = 'binary';
				}
				else
				{
					$attributes['collation'] = $options['collate'];
				}
			}

			$inherited[] = $this->column($column->getName(), $column->getType(), $attributes);
		}

		return $inherited;
	}

	/**
	 * The value after DEFAULT.
	 *
	 * @param ColumnDefinition $column the column so far, whose type decides what a hexadecimal or bit literal means
	 * @return array array(ColumnDefinition::DEFAULT_* kind, value or expression)
	 */
	private function readDefault(ColumnDefinition $column)
	{
		$literal = $this->literal->read($this->tokens, $this->pos, $column);

		if($literal !== null)
		{
			return $literal;
		}

		if($this->atEnd())
		{
			throw new InvalidArgumentException('DEFAULT without a value: '.$this->source);
		}

		return array(ColumnDefinition::DEFAULT_EXPRESSION, $this->readExpressionDefault());
	}

	/**
	 * An expression default: CURRENT_TIMESTAMP and its synonyms, a bare word, or a parenthesised expression.
	 *
	 * @return string the expression, with the CURRENT_TIMESTAMP synonyms normalised to CURRENT_TIMESTAMP
	 */
	private function readExpressionDefault()
	{
		if($this->isSymbol('('))
		{
			return $this->readParenthesised();
		}

		$word = strtoupper($this->readWord());

		if($this->acceptSymbol('('))
		{
			$args = array();
			while(!$this->isSymbol(')'))
			{
				$args[] = $this->next('text');
			}
			$this->expectSymbol(')');
			$word .= '('.implode('', $args).')';
		}

		if(preg_match('/^(CURRENT_TIMESTAMP|NOW|LOCALTIME|LOCALTIMESTAMP)(\(\d*\))?$/', $word))
		{
			return 'CURRENT_TIMESTAMP';
		}

		return $word;
	}

	/**
	 * A balanced parenthesised expression, as its raw text.
	 *
	 * @return string
	 */
	private function readParenthesised()
	{
		$this->expectSymbol('(');
		$depth = 1;
		$text = '(';

		while(!$this->atEnd())
		{
			$token = $this->next();
			$text .= ($text === '(' || $token['text'] === ')' || $token['text'] === ',') ? $token['text'] : ' '.$token['text'];

			if($token['text'] === '(')
			{
				$depth++;
			}
			elseif($token['text'] === ')' && --$depth === 0)
			{
				return $text;
			}
		}

		throw new InvalidArgumentException('Unbalanced parentheses: '.$this->source);
	}

	/**
	 * Table options until the end of the text or a semicolon.
	 *
	 * @return array lowercased option name => value
	 */
	private function readTableOptions()
	{
		$options = array();

		while(!$this->atEnd() && !$this->isSymbol(';'))
		{
			if($this->acceptSymbol(','))
			{
				continue;
			}

			$word = strtoupper($this->readWord());

			if($word === 'DEFAULT')
			{
				$word = strtoupper($this->readWord());
			}

			if($word === 'CHARACTER')
			{
				$this->expectWord('SET');
				$word = 'CHARSET';
			}

			if(($word === 'DATA' || $word === 'INDEX') && $this->acceptWord('DIRECTORY'))
			{
				$word .= ' DIRECTORY';
			}

			$this->acceptSymbol('=');
			$value = $this->readValue();

			switch($word)
			{
				case 'TYPE':
				case 'ENGINE':
					$options['engine'] = $value;
					break;

				case 'CHARSET':
					$options['charset'] = $value;
					break;

				case 'COLLATE':
					$options['collate'] = $value;
					break;

				default:
					$options[strtolower($word)] = $value;
			}
		}

		return $options;
	}

	/**
	 * A word, number or string value.
	 *
	 * @return string
	 */
	private function readValue()
	{
		$token = $this->next();

		if($token === null)
		{
			throw new InvalidArgumentException('A value is missing: '.$this->source);
		}

		return $token['value'];
	}

	/**
	 * @return string an identifier, quoted or not
	 */
	private function readName()
	{
		$token = $this->next();

		if($token === null || ($token['type'] !== SqlLexer::T_WORD && $token['type'] !== SqlLexer::T_QUOTED_IDENTIFIER))
		{
			throw new InvalidArgumentException('A name was expected'.($token ? ' at "'.$token['text'].'"' : ' at the end').': '.$this->source);
		}

		return $token['value'];
	}

	/**
	 * @return string an unquoted word
	 */
	private function readWord()
	{
		$token = $this->next();

		if($token === null || $token['type'] !== SqlLexer::T_WORD)
		{
			throw new InvalidArgumentException('A keyword was expected'.($token ? ' at "'.$token['text'].'"' : ' at the end').': '.$this->source);
		}

		return $token['text'];
	}

	/**
	 * @return string a word, a quoted identifier or a string's value
	 */
	private function readWordOrString()
	{
		$token = $this->next();

		if($token === null || !in_array($token['type'], array(SqlLexer::T_WORD, SqlLexer::T_STRING, SqlLexer::T_QUOTED_IDENTIFIER), true))
		{
			throw new InvalidArgumentException('A name was expected: '.$this->source);
		}

		return $token['value'];
	}

	/**
	 * @return string a string literal's value
	 */
	private function readString()
	{
		$token = $this->next();

		if($token === null || $token['type'] !== SqlLexer::T_STRING)
		{
			throw new InvalidArgumentException('A quoted string was expected: '.$this->source);
		}

		return $token['value'];
	}

	/**
	 * @return string a number's text
	 */
	private function readNumber()
	{
		$token = $this->next();

		if($token === null || $token['type'] !== SqlLexer::T_NUMBER)
		{
			throw new InvalidArgumentException('A number was expected: '.$this->source);
		}

		return $token['text'];
	}

	/**
	 * @param string|null $field 'text' or 'value' to return that field only
	 * @return array|string|null the next token (or its field), consumed; null at the end
	 */
	private function next($field = null)
	{
		if(!isset($this->tokens[$this->pos]))
		{
			return null;
		}

		$token = $this->tokens[$this->pos++];

		return ($field === null) ? $token : $token[$field];
	}

	/**
	 * @param string|null $field
	 * @return array|string|null the next token (or its field), not consumed
	 */
	private function peek($field = null)
	{
		if(!isset($this->tokens[$this->pos]))
		{
			return null;
		}

		return ($field === null) ? $this->tokens[$this->pos] : $this->tokens[$this->pos][$field];
	}

	/**
	 * @return bool
	 */
	private function atEnd()
	{
		return !isset($this->tokens[$this->pos]);
	}

	/**
	 * @param string $word
	 * @return bool
	 */
	private function isWord($word)
	{
		$token = $this->peek();

		return $token !== null && $token['type'] === SqlLexer::T_WORD && strtoupper($token['text']) === $word;
	}

	/**
	 * @param string $symbol
	 * @return bool
	 */
	private function isSymbol($symbol)
	{
		$token = $this->peek();

		return $token !== null && $token['type'] === SqlLexer::T_SYMBOL && $token['text'] === $symbol;
	}

	/**
	 * @param string $word
	 * @return bool whether it was there (and consumed)
	 */
	private function acceptWord($word)
	{
		if($this->isWord($word))
		{
			$this->pos++;
			return true;
		}

		return false;
	}

	/**
	 * @param string $symbol
	 * @return bool whether it was there (and consumed)
	 */
	private function acceptSymbol($symbol)
	{
		if($this->isSymbol($symbol))
		{
			$this->pos++;
			return true;
		}

		return false;
	}

	/**
	 * @param string $word
	 * @return void
	 */
	private function expectWord($word)
	{
		if(!$this->acceptWord($word))
		{
			throw new InvalidArgumentException('"'.$word.'" was expected'.($this->peek() ? ' at "'.$this->peek('text').'"' : ' at the end').': '.$this->source);
		}
	}

	/**
	 * @param string $symbol
	 * @return void
	 */
	private function expectSymbol($symbol)
	{
		if(!$this->acceptSymbol($symbol))
		{
			throw new InvalidArgumentException('"'.$symbol.'" was expected'.($this->peek() ? ' at "'.$this->peek('text').'"' : ' at the end').': '.$this->source);
		}
	}

	/**
	 * @return void
	 */
	private function expectEnd()
	{
		if(!$this->atEnd())
		{
			throw new InvalidArgumentException('Unexpected "'.$this->peek('text').'": '.$this->source);
		}
	}
}
