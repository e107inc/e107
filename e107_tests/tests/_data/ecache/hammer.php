<?php
/**
 * Race {@see ecache::set()}, {@see ecache::retrieve()} and
 * {@see ecache::delete()} on one entry until a deadline. ecacheTest runs
 * several of these against one cache directory at once.
 *
 * Usage: hammer.php <handlersDir> <cacheDir> <seconds> <tag> <payloadBytes> <seed>
 *
 * Loads only the cache handler: no class2.php, no database. Prints one line
 * on stdout, "iterations=N torn=N misses=N", where torn counts every
 * retrieve() that returned neither false nor a whole payload. Writes nothing
 * to stderr itself, so anything there is a PHP diagnostic.
 */

if(!isset($argv) || count($argv) < 7)
{
	fwrite(STDERR, "hammer: usage: hammer.php <handlersDir> <cacheDir> <seconds> <tag> <payloadBytes> <seed>\n");
	exit(2);
}

list(, $e107HammerHandlers, $e107HammerDir, $e107HammerSeconds, $e107HammerTag, $e107HammerBytes, $e107HammerSeed) = $argv;

$e107HammerHandlers = rtrim($e107HammerHandlers, '/\\') . '/';
$e107HammerDir = rtrim($e107HammerDir, '/\\') . '/';

define('e107_INIT', true);
require $e107HammerHandlers . 'core_functions.php';
require $e107HammerHandlers . 'e107_class.php';
define('e_CACHE_CONTENT', $e107HammerDir);
require $e107HammerHandlers . 'cache_handler.php';

mt_srand((int) $e107HammerSeed);

$e107HammerPayload  = str_repeat(chr(65 + ((int) $e107HammerSeed % 26)), (int) $e107HammerBytes);
$e107HammerCache    = new ecache();
$e107HammerDeadline = microtime(true) + (float) $e107HammerSeconds;
$e107HammerCounts   = array('iterations' => 0, 'torn' => 0, 'misses' => 0);
$e107HammerPattern  = 'C_' . preg_replace('#\W#', '_', $e107HammerTag) . '*.cache.php';

/**
 * Every writer's payload is one letter repeated $length times, so a whole
 * file from any writer is one run of one letter of that length.
 *
 * @param string $read
 * @param int    $length
 * @return bool
 */
function e107HammerIsWholePayload($read, $length)
{
	return strlen($read) === $length && strspn($read, $read[0]) === $length;
}

while(microtime(true) < $e107HammerDeadline)
{
	$e107HammerCounts['iterations']++;

	switch(mt_rand(0, 9))
	{
		case 0:
			$e107HammerCache->delete(e_CACHE_CONTENT, $e107HammerPattern);
			break;

		case 1:
		case 2:
		case 3:
			$e107HammerCache->set($e107HammerTag, $e107HammerPayload, true);
			break;

		default:
			$e107HammerRead = $e107HammerCache->retrieve($e107HammerTag, false, true);

			if($e107HammerRead === false)
			{
				$e107HammerCounts['misses']++;
			}
			elseif(!e107HammerIsWholePayload($e107HammerRead, strlen($e107HammerPayload)))
			{
				$e107HammerCounts['torn']++;
			}
			break;
	}
}

echo 'iterations=' . $e107HammerCounts['iterations'] . ' torn=' . $e107HammerCounts['torn'] . ' misses=' . $e107HammerCounts['misses'] . "\n";
