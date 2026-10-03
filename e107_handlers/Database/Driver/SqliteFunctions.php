<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Database\Driver;

use e107\Reflection\ReflectionMethod;
use e107\Shims\PdoSqlite;
use PDO;
use PDOException;

if(!class_exists(PdoSqlite::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/Shims/PdoSqlite.php');
}

if(!class_exists(ReflectionMethod::class, false))
{
	require_once(dirname(dirname(__DIR__)).'/Reflection/ReflectionMethod.php');
}

/**
 * Functions written in PHP for every SQLite connection: the core pack {@see \e107\Database\Platform\SqlitePlatform}
 * writes, and the MySQL compatibility pack for third-party raw SQL. A function that cannot answer as MySQL would
 * throws a PDOException, which fails the statement.
 */
final class SqliteFunctions
{
	/** @var int the PCRE backtracking budget of one regexp() call */
	const BACKTRACK_LIMIT = 100000;

	/** @var int bytes above which a padded string is NULL, as MySQL's is above max_allowed_packet */
	const MAX_ALLOWED_PACKET = 16777216;

	/** @var int times get_lock() opens the lock file anew after finding the one it locked already let go */
	const LOCK_REOPENS = 100;

	/** @var string the path every advisory lock file name starts with */
	private $lockPrefix;

	/** @var array[] lock name => array('handle' => the open lock file, 'count' => times taken and not yet released) */
	private $locks = array();

	/**
	 * @param string $lockPrefix
	 */
	private function __construct($lockPrefix)
	{
		$this->lockPrefix = $lockPrefix;
	}

	/**
	 * Register the packs on a connection.
	 *
	 * @param PDO $pdo a handle from {@see PdoSqlite::connect()}
	 * @param string $version the SQLite library version, so a built-in that already answers as MySQL is kept
	 * @param bool $mysqlCompat whether to register the MySQL compatibility pack too
	 * @return void
	 */
	public static function register($pdo, $version, $mysqlCompat)
	{
		$database = '';

		foreach($pdo->query('PRAGMA database_list')->fetchAll(PDO::FETCH_ASSOC) as $attached)
		{
			if($attached['name'] === 'main')
			{
				$database = (string) $attached['file'];
			}
		}

		$functions = new self(($database === '') ? rtrim(sys_get_temp_dir(), '/\\').DIRECTORY_SEPARATOR.'e107' : $database);
		$packs = $functions->corePack();

		if($mysqlCompat)
		{
			$packs += $functions->mysqlPack($version);
		}

		foreach($packs as $name => $definition)
		{
			PdoSqlite::createFunction($pdo, $name, $functions->closure($definition[0]), $definition[1]);
		}

		PdoSqlite::createAggregate($pdo, 'e107_group_concat_distinct', $functions->closure('groupConcatDistinctStep'), $functions->closure('groupConcatDistinctFinal'), 2);
	}

	/**
	 * Gives back the locks still held when the connection goes.
	 */
	public function __destruct()
	{
		foreach(array_keys($this->locks) as $name)
		{
			$this->locks[$name]['count'] = 1;
			$this->releaseLock($name);
		}
	}

	/**
	 * @return array name => array(method, argument count or -1)
	 */
	private function corePack()
	{
		return array(
			'regexp'                  => array('regexp', 2),
			'e107_match'              => array('match', -1),
			'e107_json_contains'      => array('jsonContains', 2),
			'e107_json_contains_path' => array('jsonContainsPath', 2),
			'e107_json_length'        => array('jsonLength', 1),
			'find_in_set'             => array('findInSet', 2),
			'get_lock'                => array('getLock', 2),
			'release_lock'            => array('releaseLock', 1),
		);
	}

	/**
	 * @param string $version
	 * @return array name => array(method, argument count or -1)
	 */
	private function mysqlPack($version)
	{
		$pack = array(
			'concat'           => array('concat', -1),
			'substring_index'  => array('substringIndex', 3),
			'greatest'         => array('greatest', -1),
			'least'            => array('least', -1),
			'left'             => array('left', 2),
			'right'            => array('right', 2),
			'lpad'             => array('lpad', 3),
			'rpad'             => array('rpad', 3),
			'locate'           => array('locate', -1),
			'char_length'      => array('charLength', 1),
			'from_unixtime'    => array('fromUnixtime', -1),
			'unix_timestamp'   => array('unixTimestamp', -1),
			'now'              => array('now', 0),
			'curdate'          => array('curdate', 0),
			'rand'             => array('rand', -1),
			'md5'              => array('md5', 1),
			'sha1'             => array('sha1', 1),
		);

		if(version_compare($version, '3.44.0', '<'))
		{
			$pack['concat_ws'] = array('concatWs', -1);
		}

		if(version_compare($version, '3.48.0', '<'))
		{
			$pack['if'] = array('ifFunction', 3);
		}

		return $pack;
	}

	/**
	 * @param string $method
	 * @return \Closure the private method, callable by SQLite
	 */
	private function closure($method)
	{
		$reflection = new ReflectionMethod($this, $method);

		return $reflection->getClosure($this);
	}

	/**
	 * subject REGEXP pattern, case-insensitive as under e107's _ci collations.
	 *
	 * @param string|null $pattern
	 * @param string|null $subject
	 * @return int|null
	 * @throws PDOException when PCRE cannot answer, as MySQL raises an error
	 */
	private function regexp($pattern, $subject)
	{
		if($pattern === null || $subject === null)
		{
			return null;
		}

		$previous = ini_set('pcre.backtrack_limit', (string) self::BACKTRACK_LIMIT);
		$delimited = "\x01".$pattern."\x01i";
		$result = @preg_match($delimited.'u', (string) $subject);

		if($result === false && preg_last_error() === PREG_BAD_UTF8_ERROR)
		{
			$result = @preg_match($delimited, (string) $subject);
		}

		$error = preg_last_error();

		if($previous !== false)
		{
			ini_set('pcre.backtrack_limit', $previous);
		}

		if($result === false)
		{
			throw new PDOException('REGEXP could not match the pattern (PCRE error '.$error.').');
		}

		return $result;
	}

	/**
	 * Full-text relevance in the manner of MySQL's boolean-mode MATCH ... AGAINST: '+word' must appear, '-word'
	 * must not, 'word*' matches a prefix, '"a phrase"' matches the words together, and other words add relevance.
	 *
	 * @return float relevance, 0 when the text does not match
	 */
	private function match()
	{
		$args = func_get_args();
		$terms = $this->searchTerms((string) array_shift($args));

		$text = '';
		foreach($args as $arg)
		{
			if($arg !== null)
			{
				$text .= ' '.$arg;
			}
		}

		$text = mb_strtolower(strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8')), 'UTF-8');
		$score = 0;

		foreach($terms as $term)
		{
			$hits = preg_match_all($term['pattern'], $text);

			if($term['operator'] === '-')
			{
				if($hits)
				{
					return 0;
				}
				continue;
			}

			if($term['operator'] === '+' && !$hits)
			{
				return 0;
			}

			$score += $hits;
		}

		return ($score > 0) ? (float) $score : 0;
	}

	/**
	 * @param string $query
	 * @return array[] terms, each array('operator' => '+'|'-'|'', 'pattern' => regex)
	 */
	private function searchTerms($query)
	{
		preg_match_all('/(?<!\S)([+\-~<>]?)(?:"([^"]*)"|([^\s"]+))/u', mb_strtolower($query, 'UTF-8'), $matches, PREG_SET_ORDER);
		$terms = array();

		foreach($matches as $match)
		{
			$operator = in_array($match[1], array('+', '-'), true) ? $match[1] : '';

			if(isset($match[2]) && $match[2] !== '')
			{
				$words = preg_split('/\s+/u', trim($match[2]));
				$pattern = '/(?<![\pL\pN])'.implode('[^\pL\pN]+', array_map(function($w) { return preg_quote($w, '/'); }, $words)).'(?![\pL\pN])/u';
			}
			else
			{
				$word = isset($match[3]) ? trim($match[3], '()') : '';
				$prefix = (substr($word, -1) === '*');
				$word = rtrim($word, '*');

				if($word === '')
				{
					continue;
				}

				$pattern = '/(?<![\pL\pN])'.preg_quote($word, '/').($prefix ? '' : '(?![\pL\pN])').'/u';
			}

			$terms[] = array('operator' => $operator, 'pattern' => $pattern);
		}

		return $terms;
	}

	/**
	 * JSON_CONTAINS(target, candidate).
	 *
	 * @param string|null $target
	 * @param string|null $candidate
	 * @return int|null
	 * @throws PDOException on a document that is not JSON
	 */
	private function jsonContains($target, $candidate)
	{
		if($target === null || $candidate === null)
		{
			return null;
		}

		return $this->contains($this->decodeJson($target), $this->decodeJson($candidate)) ? 1 : 0;
	}

	/**
	 * JSON_CONTAINS_PATH(document, 'one', path) for a path of member and array-index legs: '$.a."b c"[0]'.
	 *
	 * @param string|null $document
	 * @param string|null $path
	 * @return int|null
	 * @throws PDOException on a document that is not JSON or a path with wildcards, ranges or 'last'
	 */
	private function jsonContainsPath($document, $path)
	{
		if($document === null || $path === null)
		{
			return null;
		}

		$node = $this->decodeJson($document);
		$leg = '\.([\pL_$][\pL\pN_$]*)|\.("(?:[^"\\\\]|\\\\.)*")|\[(\d+)\]';

		if(!preg_match('/^\$(?:'.$leg.')*$/uD', (string) $path))
		{
			throw new PDOException('JSON path "'.$path.'" is not one SQLite can evaluate.');
		}

		preg_match_all('/'.$leg.'/u', (string) $path, $legs, PREG_SET_ORDER);

		foreach($legs as $step)
		{
			if(isset($step[3]) && $step[3] !== '')
			{
				if(!is_array($node))
				{
					if($step[3] !== '0')
					{
						return 0;
					}
					continue;
				}

				if(!array_key_exists((int) $step[3], $node))
				{
					return 0;
				}

				$node = $node[(int) $step[3]];
				continue;
			}

			$key = ($step[1] !== '') ? $step[1] : json_decode($step[2]);

			if($key === null)
			{
				throw new PDOException('JSON path "'.$path.'" has a member name that is not a JSON string.');
			}

			$members = is_object($node) ? get_object_vars($node) : array();

			if(!array_key_exists($key, $members))
			{
				return 0;
			}

			$node = $members[$key];
		}

		return 1;
	}

	/**
	 * JSON_LENGTH(document): elements of an array, members of an object, 1 for a scalar.
	 *
	 * @param string|null $document
	 * @return int|null
	 * @throws PDOException on a document that is not JSON
	 */
	private function jsonLength($document)
	{
		if($document === null)
		{
			return null;
		}

		$value = $this->decodeJson($document);

		if(is_object($value))
		{
			return count(get_object_vars($value));
		}

		return is_array($value) ? count($value) : 1;
	}

	/**
	 * @param string $document
	 * @return mixed objects as stdClass, so {} and [] stay apart
	 * @throws PDOException
	 */
	private function decodeJson($document)
	{
		$document = (string) $document;
		$value = json_decode($document);

		if(trim($document) === '' || ($value === null && json_last_error() !== JSON_ERROR_NONE))
		{
			throw new PDOException('Invalid JSON text: '.(trim($document) === '' ? 'the document is empty' : json_last_error_msg()).'.');
		}

		return $value;
	}

	/**
	 * MySQL's containment: a member of an object in the same member, an element in some element of an array, a
	 * scalar in an equal scalar.
	 *
	 * @param mixed $target
	 * @param mixed $candidate
	 * @return bool
	 */
	private function contains($target, $candidate)
	{
		if(is_object($target))
		{
			if(!is_object($candidate))
			{
				return false;
			}

			$members = get_object_vars($target);

			foreach(get_object_vars($candidate) as $key => $value)
			{
				if(!array_key_exists($key, $members) || !$this->contains($members[$key], $value))
				{
					return false;
				}
			}

			return true;
		}

		if(is_array($target))
		{
			foreach(is_array($candidate) ? $candidate : array($candidate) as $wanted)
			{
				if(!$this->containedInAnElement($target, $wanted))
				{
					return false;
				}
			}

			return true;
		}

		if(is_array($candidate) || is_object($candidate))
		{
			return false;
		}

		$numeric = (is_int($target) || is_float($target)) && (is_int($candidate) || is_float($candidate));

		return $numeric ? $target == $candidate : $target === $candidate;
	}

	/**
	 * @param array $elements
	 * @param mixed $wanted
	 * @return bool whether an element of the same kind contains it
	 */
	private function containedInAnElement(array $elements, $wanted)
	{
		foreach($elements as $element)
		{
			if(is_array($element) === is_array($wanted) && is_object($element) === is_object($wanted) && $this->contains($element, $wanted))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * FIND_IN_SET(needle, set): the 1-based position of the needle among the comma-separated items, 0 when absent or
	 * when the needle holds a comma.
	 *
	 * @param string|null $needle
	 * @param string|null $set
	 * @return int|null
	 */
	private function findInSet($needle, $set)
	{
		if($needle === null || $set === null)
		{
			return null;
		}

		$needle = mb_strtolower((string) $needle, 'UTF-8');

		if(strpos($needle, ',') !== false || (string) $set === '')
		{
			return 0;
		}

		foreach(explode(',', (string) $set) as $i => $item)
		{
			if(mb_strtolower($item, 'UTF-8') === $needle)
			{
				return $i + 1;
			}
		}

		return 0;
	}

	/**
	 * GET_LOCK(name, timeout): a lock file beside the database, held until RELEASE_LOCK or until the connection
	 * goes; taken again by the connection that holds it, it needs one RELEASE_LOCK more, as on MariaDB.
	 *
	 * @param string|null $name
	 * @param int|null $timeout seconds
	 * @return int|null 1 when taken or already held here, 0 when another connection held it throughout, null when
	 *                  no lock file can be held
	 */
	private function getLock($name, $timeout)
	{
		if($name === null)
		{
			return null;
		}

		$name = (string) $name;

		if(isset($this->locks[$name]))
		{
			$this->locks[$name]['count']++;

			return 1;
		}

		$path = $this->lockPath($name);
		$deadline = microtime(true) + max(0, (int) $timeout);
		$released = 0;

		do
		{
			$handle = @fopen($path, 'c');

			if($handle === false)
			{
				return null;
			}

			if(flock($handle, LOCK_EX | LOCK_NB))
			{
				clearstatcache(true, $path);
				$file = @stat($path);

				if($file !== false && $file['ino'] === fstat($handle)['ino'])
				{
					$this->locks[$name] = array('handle' => $handle, 'count' => 1);

					return 1;
				}

				flock($handle, LOCK_UN);
				fclose($handle);

				if(++$released < self::LOCK_REOPENS)
				{
					continue;
				}

				return null;
			}

			fclose($handle);

			if(microtime(true) >= $deadline)
			{
				return 0;
			}

			usleep(50000);
		}
		while(true);
	}

	/**
	 * RELEASE_LOCK(name).
	 *
	 * @param string|null $name
	 * @return int|null 1 when released, 0 when this connection does not hold it
	 */
	private function releaseLock($name)
	{
		if($name === null)
		{
			return null;
		}

		$name = (string) $name;

		if(!isset($this->locks[$name]))
		{
			return 0;
		}

		if(--$this->locks[$name]['count'] > 0)
		{
			return 1;
		}

		$handle = $this->locks[$name]['handle'];
		unset($this->locks[$name]);
		@unlink($this->lockPath($name));
		flock($handle, LOCK_UN);
		fclose($handle);

		return 1;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private function lockPath($name)
	{
		return $this->lockPrefix.'-lock-'.md5($name);
	}

	/**
	 * CONCAT(...): NULL as soon as any argument is NULL, as in MySQL and unlike SQLite's own from 3.44.
	 *
	 * @return string|null
	 */
	private function concat()
	{
		$args = func_get_args();

		return in_array(null, $args, true) ? null : implode('', $args);
	}

	/**
	 * @param array|null $context aggregate state
	 * @param int $row
	 * @param string|null $value
	 * @param string $separator
	 * @return array
	 */
	private function groupConcatDistinctStep($context, $row, $value, $separator)
	{
		if(!is_array($context))
		{
			$context = array('values' => array(), 'separator' => (string) $separator);
		}

		if($value !== null && !in_array((string) $value, $context['values'], true))
		{
			$context['values'][] = (string) $value;
		}

		return $context;
	}

	/**
	 * @param array|null $context
	 * @param int $rows
	 * @return string|null
	 */
	private function groupConcatDistinctFinal($context, $rows)
	{
		if(!is_array($context) || empty($context['values']))
		{
			return null;
		}

		return implode($context['separator'], $context['values']);
	}

	/**
	 * SUBSTRING_INDEX(string, delimiter, count).
	 *
	 * @param string|null $string
	 * @param string|null $delimiter
	 * @param int|null $count
	 * @return string|null
	 */
	private function substringIndex($string, $delimiter, $count)
	{
		if($string === null || $delimiter === null || $count === null)
		{
			return null;
		}

		$count = (int) $count;

		if($count === 0 || (string) $delimiter === '')
		{
			return '';
		}

		$parts = explode((string) $delimiter, (string) $string);

		return implode((string) $delimiter, ($count > 0) ? array_slice($parts, 0, $count) : array_slice($parts, $count));
	}

	/**
	 * @return mixed the largest argument, null when any is NULL
	 */
	private function greatest()
	{
		return $this->extreme(func_get_args(), 1);
	}

	/**
	 * @return mixed the smallest argument, null when any is NULL
	 */
	private function least()
	{
		return $this->extreme(func_get_args(), -1);
	}

	/**
	 * MySQL's comparison for GREATEST and LEAST: integers as integers, anything with a REAL as numbers, and
	 * otherwise as strings, ignoring case.
	 *
	 * @param array $args
	 * @param int $sign 1 for the largest, -1 for the smallest
	 * @return mixed
	 */
	private function extreme(array $args, $sign)
	{
		if(empty($args) || in_array(null, $args, true))
		{
			return null;
		}

		$integers = true;
		$reals = false;

		foreach($args as $arg)
		{
			$integers = $integers && is_int($arg);
			$reals = $reals || is_float($arg);
		}

		$best = array_shift($args);

		foreach($args as $arg)
		{
			if($integers || $reals)
			{
				$order = ((float) $arg > (float) $best) - ((float) $arg < (float) $best);
			}
			else
			{
				$order = strcmp(mb_strtolower((string) $arg, 'UTF-8'), mb_strtolower((string) $best, 'UTF-8'));
			}

			if($order * $sign > 0)
			{
				$best = $arg;
			}
		}

		return $best;
	}

	/**
	 * @param string|null $string
	 * @param int|null $length
	 * @return string|null
	 */
	private function left($string, $length)
	{
		return ($string === null || $length === null) ? null : mb_substr((string) $string, 0, max(0, (int) $length), 'UTF-8');
	}

	/**
	 * @param string|null $string
	 * @param int|null $length
	 * @return string|null
	 */
	private function right($string, $length)
	{
		if($string === null || $length === null)
		{
			return null;
		}

		return ((int) $length <= 0) ? '' : mb_substr((string) $string, -(int) $length, null, 'UTF-8');
	}

	/**
	 * @param string|null $string
	 * @param int|null $length
	 * @param string|null $pad
	 * @return string|null
	 */
	private function lpad($string, $length, $pad)
	{
		return $this->pad($string, $length, $pad, STR_PAD_LEFT);
	}

	/**
	 * @param string|null $string
	 * @param int|null $length
	 * @param string|null $pad
	 * @return string|null
	 */
	private function rpad($string, $length, $pad)
	{
		return $this->pad($string, $length, $pad, STR_PAD_RIGHT);
	}

	/**
	 * LOCATE(substring, string[, start]): 1-based and case-insensitive; 0 when absent or when start is outside the
	 * string.
	 *
	 * @return int|null
	 */
	private function locate()
	{
		$args = func_get_args();

		if(count($args) < 2 || $args[0] === null || $args[1] === null)
		{
			return null;
		}

		$needle = (string) $args[0];
		$haystack = (string) $args[1];
		$start = isset($args[2]) ? (int) $args[2] : 1;

		if($start < 1 || $start > mb_strlen($haystack, 'UTF-8') + 1)
		{
			return 0;
		}

		if($needle === '')
		{
			return $start;
		}

		$found = mb_stripos($haystack, $needle, $start - 1, 'UTF-8');

		return ($found === false) ? 0 : $found + 1;
	}

	/**
	 * @param string|null $string
	 * @return int|null characters, not bytes
	 */
	private function charLength($string)
	{
		return ($string === null) ? null : mb_strlen((string) $string, 'UTF-8');
	}

	/**
	 * FROM_UNIXTIME(timestamp[, format]) in PHP's time zone.
	 *
	 * @return string|null
	 */
	private function fromUnixtime()
	{
		$args = func_get_args();

		if(!isset($args[0]) || !is_numeric($args[0]))
		{
			return null;
		}

		return isset($args[1]) ? $this->dateFormat((int) $args[0], (string) $args[1]) : date('Y-m-d H:i:s', (int) $args[0]);
	}

	/**
	 * UNIX_TIMESTAMP([date]).
	 *
	 * @return int|null
	 */
	private function unixTimestamp()
	{
		$args = func_get_args();

		if(empty($args))
		{
			return time();
		}

		if($args[0] === null)
		{
			return null;
		}

		$time = is_numeric($args[0]) ? (int) $args[0] : strtotime((string) $args[0]);

		return ($time === false) ? 0 : $time;
	}

	/**
	 * @return string
	 */
	private function now()
	{
		return date('Y-m-d H:i:s');
	}

	/**
	 * @return string
	 */
	private function curdate()
	{
		return date('Y-m-d');
	}

	/**
	 * RAND([seed]): a float in [0, 1). A seed is accepted but does not make the sequence repeatable.
	 *
	 * @return float
	 */
	private function rand()
	{
		return mt_rand() / (mt_getrandmax() + 1);
	}

	/**
	 * @param string|null $value
	 * @return string|null
	 */
	private function md5($value)
	{
		return ($value === null) ? null : md5((string) $value);
	}

	/**
	 * @param string|null $value
	 * @return string|null
	 */
	private function sha1($value)
	{
		return ($value === null) ? null : sha1((string) $value);
	}

	/**
	 * CONCAT_WS(separator, ...): skips NULL arguments; NULL only for a NULL separator.
	 *
	 * @return string|null
	 */
	private function concatWs()
	{
		$args = func_get_args();
		$separator = array_shift($args);

		if($separator === null)
		{
			return null;
		}

		return implode((string) $separator, array_filter($args, function($arg) { return $arg !== null; }));
	}

	/**
	 * IF(condition, then, else).
	 *
	 * @param mixed $condition
	 * @param mixed $then
	 * @param mixed $else
	 * @return mixed
	 */
	private function ifFunction($condition, $then, $else)
	{
		return ($condition !== null && (float) $condition != 0) ? $then : $else;
	}

	/**
	 * @param string|null $string
	 * @param int|null $length
	 * @param string|null $pad
	 * @param int $side STR_PAD_LEFT or STR_PAD_RIGHT
	 * @return string|null
	 */
	private function pad($string, $length, $pad, $side)
	{
		if($string === null || $length === null || $pad === null || (int) $length < 0)
		{
			return null;
		}

		$length = (int) $length;
		$string = (string) $string;
		$pad = (string) $pad;
		$missing = $length - mb_strlen($string, 'UTF-8');

		if($missing <= 0)
		{
			return mb_substr($string, 0, $length, 'UTF-8');
		}

		if($pad === '')
		{
			return null;
		}

		$repeats = (int) ceil($missing / mb_strlen($pad, 'UTF-8'));

		if(strlen($string) + $repeats * strlen($pad) > self::MAX_ALLOWED_PACKET)
		{
			return null;
		}

		$fill = mb_substr(str_repeat($pad, $repeats), 0, $missing, 'UTF-8');

		return ($side === STR_PAD_LEFT) ? $fill.$string : $string.$fill;
	}

	/**
	 * DATE_FORMAT's specifiers, but for the week numbers.
	 *
	 * @param int $time
	 * @param string $format MySQL format string
	 * @return string
	 * @throws PDOException on a week-number specifier (%U, %u, %V, %v, %X, %x)
	 */
	private function dateFormat($time, $format)
	{
		$map = array('a' => 'D', 'b' => 'M', 'c' => 'n', 'D' => 'jS', 'd' => 'd', 'e' => 'j', 'f' => 'u', 'H' => 'H',
			'h' => 'h', 'I' => 'h', 'i' => 'i', 'k' => 'G', 'l' => 'g', 'M' => 'F', 'm' => 'm', 'p' => 'A',
			'r' => 'h:i:s A', 'S' => 's', 's' => 's', 'T' => 'H:i:s', 'W' => 'l', 'w' => 'w', 'Y' => 'Y', 'y' => 'y');

		return preg_replace_callback('/%(.)/s', function($m) use ($map, $time)
		{
			if(isset($map[$m[1]]))
			{
				return date($map[$m[1]], $time);
			}

			if($m[1] === 'j')
			{
				return sprintf('%03d', date('z', $time) + 1);
			}

			if(strpos('UuVvXx', $m[1]) !== false)
			{
				throw new PDOException('FROM_UNIXTIME has no week numbers on SQLite (%'.$m[1].').');
			}

			return $m[1];
		}, $format);
	}
}
