<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The sqlite lane's populator (see lib/config.php): builds the SQLite database from the MySQL dump.
 *
 *   php lib/sqlite-fixture.php <MySQL dump> sqlite:<database file>
 *
 * Relative paths are relative to e107_tests/, as Codeception reads them. Codeception shows what this prints only when
 * it fails, so errors go to standard output.
 */

require_once(__DIR__.'/SqliteFixture.php');

if($argc !== 3 || strpos($argv[2], 'sqlite:') !== 0)
{
	echo "usage: php sqlite-fixture.php <MySQL dump> sqlite:<database file>\n";
	exit(2);
}

$root = dirname(__DIR__).'/';
$dump = (strpos($argv[1], '/') === 0) ? $argv[1] : $root.$argv[1];
$database = (string) substr($argv[2], strlen('sqlite:'));
$database = (strpos($database, '/') === 0) ? $database : $root.$database;

try
{
	SqliteFixture::build($dump, $database);
}
catch(Exception $e)
{
	echo get_class($e).': '.$e->getMessage()."\n";
	exit(1);
}
