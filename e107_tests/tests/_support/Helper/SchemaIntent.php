<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace Helper;

use e107\Database\Schema\Declared\DeclaredTable;

/**
 * The storage engine and character set {@see \db_verify} would build a declared table with on this server.
 */
trait SchemaIntent
{
	/** @var \db_verify|null */
	private static $schemaIntentVerifier = null;

	/**
	 * @param DeclaredTable $table
	 * @return array ['engine' => string, 'charset' => string]
	 */
	protected function schemaIntentFor(DeclaredTable $table)
	{

		$verifier = $this->schemaIntentVerifier();
		$body = $table->getBody();

		$requirements = $verifier->deriveTableRequirements($verifier->getFields($body), $verifier->getIndex($body));

		$engine = $verifier->getIntendedStorageEngine($table->getDeclaredEngine(), $requirements);

		$requirements['engine'] = $engine;

		return array(
			'engine'  => $engine,
			'charset' => $verifier->getIntendedCharset($table->getDeclaredCharset(), $requirements),
		);
	}

	/**
	 * @return \db_verify uninitialised, with this server's engine list loaded.
	 */
	private function schemaIntentVerifier()
	{

		if(self::$schemaIntentVerifier === null)
		{
			require_once(e_HANDLER.'db_verify_class.php');

			$verifier = new \db_verify(false);
			$verifier->availableStorageEngines = $verifier->getAvailableStorageEngines();

			self::$schemaIntentVerifier = $verifier;
		}

		return self::$schemaIntentVerifier;
	}
}
