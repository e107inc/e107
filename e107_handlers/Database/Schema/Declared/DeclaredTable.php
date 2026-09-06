<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Declared;

use InvalidArgumentException;

/**
 * One `CREATE TABLE` declaration as it stands in a `*_sql.php` schema file. Immutable.
 *
 * <code>
 * $table = new DeclaredTable('core', 'news', "news_id int(10) unsigned NOT NULL auto_increment, PRIMARY KEY (news_id)", 'MyISAM', 'utf8');
 * $table->getName();           // 'news'   (the e107_ prefix is already stripped)
 * $table->getDeclaredEngine(); // 'MyISAM' (null when the file does not say)
 * </code>
 */
final class DeclaredTable
{
	/** @var string 'core', or the plugin folder that declared the table */
	private $sqlFile;

	/** @var string unprefixed table name */
	private $name;

	/** @var string verbatim text between the outer parentheses of CREATE TABLE */
	private $body;

	/** @var string|null ENGINE= or TYPE= as declared, null when absent */
	private $declaredEngine;

	/** @var string|null DEFAULT CHARSET / CHARACTER SET as declared, null when absent */
	private $declaredCharset;

	/**
	 * @param string $sqlFile 'core' or a plugin folder. Non-empty.
	 * @param string $name Unprefixed table name. Non-empty.
	 * @param string $body Verbatim column/key block, without the enclosing parentheses or a trailing semicolon.
	 * @param string|null $declaredEngine Engine as declared; an empty string becomes null.
	 * @param string|null $declaredCharset Character set as declared; an empty string becomes null.
	 * @throws InvalidArgumentException when $sqlFile or $name is empty.
	 */
	public function __construct($sqlFile, $name, $body, $declaredEngine = null, $declaredCharset = null)
	{
		$this->sqlFile = $this->_requireNonEmpty($sqlFile, 'sqlFile');
		$this->name = $this->_requireNonEmpty($name, 'name');
		$this->body = (string) $body;
		$this->declaredEngine = $this->_normaliseOptional($declaredEngine);
		$this->declaredCharset = $this->_normaliseOptional($declaredCharset);
	}

	/**
	 * @return string 'core' for `core_sql.php`, otherwise the plugin folder.
	 */
	public function getSqlFile()
	{
		return $this->sqlFile;
	}

	/**
	 * @return string Unprefixed table name, e.g. 'news'.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string Verbatim column/key block, exactly as the schema file spells it.
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Not the engine the table ends up with: {@see EngineCharsetResolverInterface::resolve()} settles that.
	 *
	 * @return string|null Engine as declared, null when the file declares none.
	 */
	public function getDeclaredEngine()
	{
		return $this->declaredEngine;
	}

	/**
	 * @return string|null Character set as declared, null when the file declares none.
	 */
	public function getDeclaredCharset()
	{
		return $this->declaredCharset;
	}

	/**
	 * @param mixed $other
	 * @return bool True when $other is a DeclaredTable with every field equal.
	 */
	public function equals($other)
	{
		if(!$other instanceof self)
		{
			return false;
		}

		return $this->toArray() === $other->toArray();
	}

	/**
	 * @return array ['sqlFile'=>string, 'name'=>string, 'body'=>string, 'engine'=>string|null, 'charset'=>string|null]
	 */
	public function toArray()
	{
		return array(
			'sqlFile' => $this->sqlFile,
			'name'    => $this->name,
			'body'    => $this->body,
			'engine'  => $this->declaredEngine,
			'charset' => $this->declaredCharset,
		);
	}

	/**
	 * @param mixed $value
	 * @param string $what
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function _requireNonEmpty($value, $what)
	{
		$value = trim((string) $value);

		if($value === '')
		{
			throw new InvalidArgumentException('DeclaredTable requires a non-empty '.$what.'.');
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return string|null
	 */
	private function _normaliseOptional($value)
	{
		if($value === null)
		{
			return null;
		}

		$value = trim((string) $value);

		return ($value === '') ? null : $value;
	}
}
