<?php
namespace Test;

/**
 * The banlist_ip key in the state a test needs, put back afterwards because the files of a batch share one database.
 */
final class BanlistAddressKey
{
	/**
	 * @return bool whether banlist_ip is the unique key over (banlist_ip, banlist_bantype)
	 */
	public static function isUnique()
	{
		return self::read() === array('UNIQUE', 'banlist_ip', 'banlist_bantype');
	}

	/**
	 * @param bool|null $unique null for no banlist_ip key at all
	 * @return callable puts the key back as it was found; call it in a finally block or _after()
	 */
	public static function set($unique)
	{
		$found = self::read();

		if($unique === null)
		{
			self::change($found, array());
		}
		else
		{
			self::change($found, $unique ? array('UNIQUE', 'banlist_ip', 'banlist_bantype') : array('KEY', 'banlist_ip'));
		}

		return static function () use ($found)
		{
			self::change(self::read(), $found);
		};
	}

	/**
	 * @param string[] $from the key as {@see BanlistAddressKey::read()} gives it
	 * @param string[] $to the same shape, empty for none
	 * @return void
	 */
	private static function change(array $from, array $to)
	{
		if($from === $to)
		{
			return;
		}

		$clauses = ($from === array()) ? array() : array('DROP INDEX `banlist_ip`');

		if($to !== array())
		{
			$kind = array_shift($to);
			$clauses[] = ($kind === 'UNIQUE' ? 'ADD UNIQUE KEY' : 'ADD KEY').' `banlist_ip` (`'.implode('`,`', $to).'`)';
		}

		if(\e107::getDb()->gen('ALTER TABLE `#banlist` '.implode(', ', $clauses)) === false)
		{
			throw new \RuntimeException('The banlist_ip key could not be changed with '.implode(', ', $clauses).'.');
		}
	}

	/**
	 * @return string[] UNIQUE or KEY followed by the key's columns in order, empty where there is no banlist_ip key
	 */
	private static function read()
	{
		$sql = \e107::getDb();
		$sql->gen("SHOW INDEX FROM `#banlist` WHERE Key_name = 'banlist_ip'");

		$columns = array();
		$kind = 'KEY';

		while($row = $sql->fetch())
		{
			$columns[(int) $row['Seq_in_index']] = $row['Column_name'];
			$kind = ((int) $row['Non_unique'] === 0) ? 'UNIQUE' : 'KEY';
		}

		if($columns === array())
		{
			return array();
		}

		ksort($columns);

		return array_merge(array($kind), array_values($columns));
	}
}
