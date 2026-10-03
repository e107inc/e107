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

use InvalidArgumentException;

/**
 * One engine-neutral key or index of a {@see TableDefinition}, always named the way MySQL names it ('PRIMARY' for
 * the primary key).
 */
final class IndexDefinition
{
	const KIND_PRIMARY = 'PRIMARY';
	const KIND_UNIQUE = 'UNIQUE';
	const KIND_INDEX = 'INDEX';
	const KIND_FULLTEXT = 'FULLTEXT';
	const KIND_SPATIAL = 'SPATIAL';

	/** @var string */
	private $name;

	/** @var string one of the KIND_* constants */
	private $kind;

	/** @var array[] parts, each array('column' => name, 'length' => int|null, 'direction' => 'ASC'|'DESC') */
	private $parts;

	/**
	 * @param string $name index name; 'PRIMARY' for the primary key
	 * @param string $kind one of the KIND_* constants
	 * @param array $parts column names, or arrays with 'column' and optionally 'length' and 'direction'
	 * @throws InvalidArgumentException on an unknown kind or an empty part list
	 */
	public function __construct($name, $kind, array $parts)
	{
		if(!in_array($kind, array(self::KIND_PRIMARY, self::KIND_UNIQUE, self::KIND_INDEX, self::KIND_FULLTEXT, self::KIND_SPATIAL), true))
		{
			throw new InvalidArgumentException('Unknown index kind "'.$kind.'".');
		}

		if(empty($parts))
		{
			throw new InvalidArgumentException('An index needs at least one column.');
		}

		$this->name = ($kind === self::KIND_PRIMARY) ? 'PRIMARY' : (string) $name;
		$this->kind = $kind;
		$this->parts = array();

		foreach($parts as $part)
		{
			if(!is_array($part))
			{
				$part = array('column' => $part);
			}

			$this->parts[] = array(
				'column'    => (string) $part['column'],
				'length'    => isset($part['length']) ? (int) $part['length'] : null,
				'direction' => (isset($part['direction']) && strtoupper($part['direction']) === 'DESC') ? 'DESC' : 'ASC',
			);
		}
	}

	/** @return string */
	public function getName() { return $this->name; }

	/** @return string one of the KIND_* constants */
	public function getKind() { return $this->kind; }

	/** @return array[] see the constructor */
	public function getParts() { return $this->parts; }

	/** @return bool */
	public function isPrimary() { return $this->kind === self::KIND_PRIMARY; }

	/** @return bool whether the index refuses duplicate keys (primary or unique) */
	public function isUnique() { return $this->kind === self::KIND_PRIMARY || $this->kind === self::KIND_UNIQUE; }

	/**
	 * @return string[] the indexed column names, in order
	 */
	public function getColumnNames()
	{
		$names = array();

		foreach($this->parts as $part)
		{
			$names[] = $part['column'];
		}

		return $names;
	}

	/**
	 * @return array every attribute, for comparison and debugging
	 */
	public function toArray()
	{
		return array('name' => $this->name, 'kind' => $this->kind, 'parts' => $this->parts);
	}
}
