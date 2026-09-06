<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Schema\Introspect;

use InvalidArgumentException;

/**
 * One index of a table: its name, its kind, and its ordered parts. Immutable.
 *
 * Only an index materialised by {@see \e107\Database\Schema\Declared\Materialiser}
 * carries DDL; one read from a live table leaves it null, and it takes no part in
 * {@see IndexSchema::equals()}.
 *
 * <code>
 * new IndexSchema('PRIMARY', IndexSchema::KIND_PRIMARY, array(new IndexPart('user_id', null, 'ASC')));
 * </code>
 */
final class IndexSchema
{
	const KIND_PRIMARY = 'PRIMARY';

	const KIND_UNIQUE = 'UNIQUE';

	const KIND_INDEX = 'INDEX';

	const KIND_FULLTEXT = 'FULLTEXT';

	const KIND_SPATIAL = 'SPATIAL';

	/** @var string INDEX_NAME */
	private $name;

	/** @var string one of the KIND_* constants */
	private $kind;

	/** @var IndexPart[] ordered by SEQ_IN_INDEX, keys 0..n-1 */
	private $parts;

	/** @var string|null the server's own definition line; never part of {@see IndexSchema::equals()} */
	private $ddl;

	/**
	 * @param string $name INDEX_NAME.
	 * @param string $kind One of the KIND_* constants, case-insensitive on the way in and stored uppercase.
	 * @param IndexPart[] $parts Ordered by SEQ_IN_INDEX; re-keyed to 0..n-1.
	 * @param string|null $ddl This index's line from SHOW CREATE TABLE, without the trailing comma; null on the live side.
	 * @throws InvalidArgumentException on an empty name, an unrecognised kind, an empty part list, or a part that is not an IndexPart.
	 */
	public function __construct($name, $kind, array $parts, $ddl = null)
	{
		$name = (string) $name;

		if($name === '')
		{
			throw new InvalidArgumentException('An index must have a name.');
		}

		if(count($parts) === 0)
		{
			throw new InvalidArgumentException('Index "'.$name.'" must have at least one part.');
		}

		$ordered = array();

		foreach($parts as $part)
		{
			if(!$part instanceof IndexPart)
			{
				throw new InvalidArgumentException('Index "'.$name.'" was given a part that is not an IndexPart.');
			}

			$ordered[] = $part;
		}

		$this->name = $name;
		$this->kind = self::_validateKind($kind, $name);
		$this->parts = $ordered;
		$this->ddl = ($ddl === null) ? null : (string) $ddl;
	}

	/**
	 * This index with the server's own definition line attached.
	 *
	 * @param string|null $ddl
	 * @return IndexSchema a copy; this object is immutable.
	 */
	public function withDdl($ddl)
	{
		return new self($this->name, $this->kind, $this->parts, $ddl);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @return string one of the KIND_* constants.
	 */
	public function getKind()
	{
		return $this->kind;
	}

	/**
	 * @return IndexPart[] in SEQ_IN_INDEX order.
	 */
	public function getParts()
	{
		return $this->parts;
	}

	/**
	 * The indexed column names in index order, never sorted, with null in the slot
	 * of any part that indexes an expression rather than a column.
	 *
	 * @return array<string|null>
	 */
	public function getColumnNames()
	{
		$names = array();

		foreach($this->parts as $part)
		{
			$names[] = $part->getColumnName();
		}

		return $names;
	}

	/**
	 * This index's own line of the server's SHOW CREATE TABLE output, without
	 * the trailing comma, ready to be spliced into an ADD.
	 *
	 * @return string|null null when the index was read from a live table rather than materialised.
	 */
	public function getDdl()
	{
		return $this->ddl;
	}

	/**
	 * Value equality over the name, the kind, and the ordered parts. The DDL takes
	 * no part.
	 *
	 * @param mixed $other
	 * @return bool
	 */
	public function equals($other)
	{
		if(!$other instanceof self)
		{
			return false;
		}

		if($this->name !== $other->name || $this->kind !== $other->kind)
		{
			return false;
		}

		if(count($this->parts) !== count($other->parts))
		{
			return false;
		}

		foreach($this->parts as $i => $part)
		{
			if(!$part->equals($other->parts[$i]))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Every identifying field, with the parts as a list in order and the DDL omitted.
	 *
	 * @return array
	 */
	public function toArray()
	{
		$parts = array();

		foreach($this->parts as $part)
		{
			$parts[] = $part->toArray();
		}

		return array(
			'name'  => $this->name,
			'kind'  => $this->kind,
			'parts' => $parts,
		);
	}

	/**
	 * @param string $kind
	 * @param string $name for the error message.
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private static function _validateKind($kind, $name)
	{
		$kind = strtoupper(trim((string) $kind));

		$known = array(
			self::KIND_PRIMARY,
			self::KIND_UNIQUE,
			self::KIND_INDEX,
			self::KIND_FULLTEXT,
			self::KIND_SPATIAL,
		);

		if(!in_array($kind, $known, true))
		{
			throw new InvalidArgumentException('Invalid index kind "'.$kind.'" for index "'.$name.'": expected one of '.implode(', ', $known).'.');
		}

		return $kind;
	}
}
