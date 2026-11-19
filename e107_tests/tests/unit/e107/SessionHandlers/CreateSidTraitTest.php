<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\SessionHandlers;

/**
 * Core's save handlers carry every method PHP 8.6 expects of a {@see \SessionHandlerInterface}.
 */
class CreateSidTraitTest extends \Test\Unit
{
	/**
	 * PHP 8.6 warns as a class implementing the interface without {@see \SessionIdInterface::create_sid()} or {@see \SessionUpdateTimestampHandlerInterface::validateId()} is declared, and deprecates registering one.
	 */
	public function testTheCoreHandlersAreDeclaredAndRegisteredWithoutADiagnostic()
	{
		$php = "error_reporting(E_ALL); ini_set('display_errors', 'stderr'); \$handlers = ".var_export(APP_PATH.'/e107_handlers/', true)."; ";
		$php .= <<<'PHP'
spl_autoload_register(function($class) use ($handlers) {
	$file = $handlers.str_replace('\\', '/', substr($class, strlen('e107\\'))).'.php';
	if(is_file($file)) { require $file; }
});
foreach(array('e107\SessionHandlers\DatabaseSessionHandler', 'e107\SessionHandlers\NonblockingFilesSessionHandler') as $class) {
	$reflection = new ReflectionClass($class);
	session_set_save_handler($reflection->newInstanceWithoutConstructor(), false);
}
echo '@@registered@@';
PHP;

		list($output, $status) = $this->runInCli($php);

		$this->assertSame(array('@@registered@@'), $output);
		$this->assertSame(0, $status);
	}

	/**
	 * Every PHP e107 runs on asks a save handler that has the method for each new id, PHP 5.6 and 7.0 included.
	 */
	public function testARegeneratedSessionTakesTheIdTheHandlerMade()
	{
		$php = 'class CreateSidSpy extends \\e107\\SessionHandlers\\NonblockingFilesSessionHandler { ';
		$php .= 'public function create_sid() { $id = parent::create_sid(); fwrite(STDERR, "@@".$id."@@"); return $id; } } ';
		$php .= "session_write_close(); ini_set('session.use_strict_mode', '0'); ";
		$php .= "session_set_save_handler(new CreateSidSpy(".var_export(sys_get_temp_dir(), true)."), true); session_start(); ";
		$php .= "session_regenerate_id(true); fwrite(STDERR, '@@'.session_id().'@@'); session_destroy();";

		list($made, $regenerated) = $this->marksFromBootedCli($php, 2);

		$this->assertTrue(SessionId::isWellFormed($made), $made);
		$this->assertSame($made, $regenerated);
	}
}
