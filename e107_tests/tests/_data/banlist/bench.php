<?php
/**
 * Time the ban check of one request, old engine against new, over a ban
 * list of N random ranges.
 *
 * Usage: bench.php <handlersDir> <scratchDir> <rows> <lookups> <seed>
 *
 * Loads only the Ip classes: no class2.php, no database. Writes the
 * prefix-token file the old eIPHandler read and the compiled range table
 * the new one reads into <scratchDir>, then times <lookups> lookups of
 * random addresses, half of them drawn from inside a listed range, against
 * each. The old check is the file() read, split and prefix scan of
 * eIPHandler::getWhiteBlackList() and checkIP() as they stood before the
 * range table, reproduced here because both were private. Prints one line
 * on stdout:
 *
 *   rows=N segments=N old_bytes=N new_bytes=N old_us=F new_us=F old_hits=N new_hits=N
 *
 * where old_us and new_us are the mean microseconds of one request's ban
 * check, file read included, and the hit counts show both engines agreed
 * on the addresses the old prefix format can express.
 */

use e107\Ip\Address;
use e107\Ip\Range;
use e107\Ip\RangeFile;
use e107\Ip\RangeSet;

if(!isset($argv) || count($argv) < 6)
{
	fwrite(STDERR, "bench: usage: bench.php <handlersDir> <scratchDir> <rows> <lookups> <seed>\n");
	exit(2);
}

list(, $benchHandlers, $benchDir, $benchRows, $benchLookups, $benchSeed) = $argv;

$benchHandlers = rtrim($benchHandlers, '/\\').'/';
$benchDir = rtrim($benchDir, '/\\').'/';
$benchRows = (int) $benchRows;
$benchLookups = (int) $benchLookups;

require $benchHandlers.'Ip/Address.php';
require $benchHandlers.'Ip/Range.php';
require $benchHandlers.'Ip/RangeLookup.php';
require $benchHandlers.'Ip/RangeSet.php';
require $benchHandlers.'Ip/RangeFile.php';

if(!is_dir($benchDir))
{
	mkdir($benchDir, 0777, true);
}

mt_srand((int) $benchSeed);

/**
 * A random dotted IPv4 block of the given prefix length, as the admin would type it.
 *
 * @param int $prefix 8, 16, 24 or 32
 * @return string
 */
function benchBlock($prefix)
{
	$octets = array(mt_rand(1, 223), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));

	for($i = $prefix / 8; $i < 4; $i++)
	{
		$octets[$i] = '*';
	}

	return implode('.', $octets);
}

$benchSet = new RangeSet();
$benchOldLines = '';
$benchInside = array();

for($i = 1; $i <= $benchRows; $i++)
{
	$prefix = mt_rand(1, 100) <= 90 ? 32 : (mt_rand(1, 100) <= 80 ? 24 : 16);
	$text = benchBlock($prefix);
	$range = Range::fromString($text);
	$type = mt_rand(1, 100) <= 2 ? 100 : -1;
	$benchSet->add($range, $i, $type, 0, $text);

	$encoded = Address::toEncoded($range->start());
	$token = (string) substr($encoded, 0, strlen($encoded) - ((32 - $prefix) / 4) - (int) ((32 - $prefix) / 16));
	$benchOldLines .= $token.' '.$type." 0\n";

	if(count($benchInside) < $benchLookups)
	{
		$benchInside[] = Address::toEncoded($range->start());
	}
}

$benchSet->compile();

$benchOldFile = $benchDir.'banlist.php';
$benchNewFile = $benchDir.'banranges.php';
file_put_contents($benchOldFile, "<?php\n; die();\n".$benchOldLines);
file_put_contents($benchNewFile, RangeFile::render($benchSet));

$benchQueries = array();

for($i = 0; $i < $benchLookups; $i++)
{
	if($i % 2 === 0 && isset($benchInside[$i >> 1]))
	{
		$benchQueries[] = $benchInside[$i >> 1];
		continue;
	}

	$benchQueries[] = Address::toEncoded(Address::toHex(implode('.', array(mt_rand(1, 223), mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)))));
}

/**
 * One request's ban check as eIPHandler did it before the range table.
 *
 * @param string $file
 * @param string $addr encoded visitor address
 * @return int the matched type, or 0
 */
function benchOldCheck($file, $addr)
{
	$ret = array();
	$vals = file($file);
	unset($vals[0]);

	foreach($vals as $line)
	{
		if(strpos($line, ';') === 0) continue;
		if(trim($line))
		{
			$tmp = explode(' ', $line);
			if(count($tmp) >= 2)
			{
				$ret[] = array('ip' => $tmp[0], 'action' => $tmp[1], 'time_limit' => (int) $tmp[2]);
			}
		}
	}

	foreach($ret as $val)
	{
		if(strpos($addr, $val['ip']) === 0)
		{
			return (int) $val['action'];
		}
	}

	return 0;
}

/**
 * One request's ban check against the compiled table.
 *
 * @param string $file
 * @param string $addr encoded visitor address
 * @return int the matched type, or 0
 */
function benchNewCheck($file, $addr)
{
	$set = RangeFile::open($file);
	$segment = $set->find(Address::toHex($addr));

	if($segment < 0)
	{
		return 0;
	}

	$hits = $set->hits($segment);
	$entry = $set->entry($hits[0]);

	return (int) $entry['type'];
}

$benchOldHits = 0;
$benchStart = microtime(true);

foreach($benchQueries as $addr)
{
	if(benchOldCheck($benchOldFile, $addr) !== 0)
	{
		$benchOldHits++;
	}
}

$benchOldUs = (microtime(true) - $benchStart) * 1000000 / $benchLookups;

$benchNewHits = 0;
$benchStart = microtime(true);

foreach($benchQueries as $addr)
{
	if(benchNewCheck($benchNewFile, $addr) !== 0)
	{
		$benchNewHits++;
	}
}

$benchNewUs = (microtime(true) - $benchStart) * 1000000 / $benchLookups;

printf("rows=%d segments=%d old_bytes=%d new_bytes=%d old_us=%.1f new_us=%.1f old_hits=%d new_hits=%d\n",
	$benchRows, $benchSet->segmentCount(), filesize($benchOldFile), filesize($benchNewFile), $benchOldUs, $benchNewUs, $benchOldHits, $benchNewHits);
