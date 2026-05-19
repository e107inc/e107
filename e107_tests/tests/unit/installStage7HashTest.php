<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Regression test for issue #5631: stage_7() in install.php must resolve
 * the literal "[hash]" placeholder in every e107_dirs entry, not just
 * SYSTEM_DIRECTORY and CACHE_DIRECTORY. updatePaths() (stage_5) does the
 * full sweep, so the bug is silent in the normal install flow; but when
 * stage_7() or import_configuration() is reached without updatePaths()
 * running first (e.g. a custom migration script), every derived directory
 * key (CACHE_DB_*, CACHE_CONTENT_*, MEDIA_FILES_*, AVATARS_*, ...) is
 * left with a literal "[hash]" substring.
 *
 * The fix in stage_7() now loops over the entire e107_dirs array and
 * resolves any remaining placeholders. This test exercises that loop in
 * isolation via the resolveSitePathPlaceholders() helper.
 *
 * Because install.php runs the full install entrypoint at top level
 * (instantiates e_install, calls renderPage()), we cannot simply require
 * the file from a unit test. Instead, we extract the e_install class
 * definition from the source via the PHP tokenizer and eval() it under
 * a renamed class so the helper can be invoked through reflection
 * against a synthetic e107 object holding only the two properties the
 * helper touches (e107_dirs and site_path).
 */


class installStage7HashTest extends \Codeception\Test\Unit
{
	/** @var string */
	private static $renamedClass = 'e_install_for_5631_test';

	public static function setUpBeforeClass(): void
	{
		if (class_exists(self::$renamedClass, false))
		{
			return;
		}

		$installPhp = realpath(APP_PATH . '/install.php');
		self::assertNotFalse(
			$installPhp,
			'install.php must exist at the expected location'
		);

		$source = file_get_contents($installPhp);
		$classBody = self::extractClassBody($source, 'e_install');
		self::assertNotNull(
			$classBody,
			'Unable to locate the e_install class definition in install.php'
		);

		// Rename the class so it does not collide with anything the test
		// runner may already have loaded, then evaluate just the class
		// definition (without install.php's top-level install bootstrap).
		$renamed = preg_replace(
			'/\bclass\s+e_install\b/',
			'class ' . self::$renamedClass,
			$classBody,
			1
		);

		eval($renamed);
	}

	public function testResolveSitePathPlaceholdersClearsAllHashTokens()
	{
		// Simulate the state of $e107->e107_dirs *before* updatePaths()
		// has run: every derived directory still contains the literal
		// "[hash]" placeholder. This is exactly the state that stage_7()
		// would observe if a custom migration script reused install
		// internals without first calling stage_5/updatePaths().
		$sitePath = 'abcdef1234567890';

		$dirs = [
			// Root entries that updatePaths()/defaultDirs() would normally
			// resolve before stage_7 runs.
			'ADMIN_DIRECTORY'           => 'e107_admin/',
			'IMAGES_DIRECTORY'          => 'e107_images/',
			'THEMES_DIRECTORY'          => 'e107_themes/',
			'PLUGINS_DIRECTORY'         => 'e107_plugins/',
			'FILES_DIRECTORY'           => 'e107_files/',
			'HANDLERS_DIRECTORY'        => 'e107_handlers/',
			'LANGUAGES_DIRECTORY'       => 'e107_languages/',
			'DOCS_DIRECTORY'            => 'e107_docs/',
			'CORE_DIRECTORY'            => 'e107_core/',
			'WEB_DIRECTORY'             => 'e107_web/',
			'MEDIA_BASE_DIRECTORY'      => 'e107_media/',
			'SYSTEM_BASE_DIRECTORY'     => 'e107_system/',

			// Multisite-aware roots: defaultDirs() appends "[hash]/" to
			// these. The old stage_7 hand-resolved exactly these two.
			'MEDIA_DIRECTORY'           => 'e107_media/[hash]/',
			'SYSTEM_DIRECTORY'          => 'e107_system/[hash]/',

			// Derived keys: defaultDirs() builds these from
			// MEDIA_DIRECTORY/SYSTEM_DIRECTORY, so all of them carry the
			// "[hash]" substring transitively. The old stage_7 left
			// every one of these untouched - that is bug #5631.
			'CACHE_DIRECTORY'           => 'e107_system/[hash]/cache/',
			'CACHE_CONTENT_DIRECTORY'   => 'e107_system/[hash]/cache/content/',
			'CACHE_IMAGE_DIRECTORY'     => 'e107_system/[hash]/cache/images/',
			'CACHE_DB_DIRECTORY'        => 'e107_system/[hash]/cache/db/',
			'CACHE_URL_DIRECTORY'       => 'e107_system/[hash]/cache/url/',
			'LOGS_DIRECTORY'            => 'e107_system/[hash]/logs/',
			'BACKUP_DIRECTORY'          => 'e107_system/[hash]/backup/',
			'TEMP_DIRECTORY'            => 'e107_system/[hash]/temp/',
			'IMPORT_DIRECTORY'          => 'e107_system/[hash]/import/',
			'MEDIA_IMAGES_DIRECTORY'    => 'e107_media/[hash]/images/',
			'MEDIA_ICONS_DIRECTORY'     => 'e107_media/[hash]/icons/',
			'MEDIA_VIDEOS_DIRECTORY'    => 'e107_media/[hash]/videos/',
			'MEDIA_FILES_DIRECTORY'     => 'e107_media/[hash]/files/',
			'MEDIA_UPLOAD_DIRECTORY'    => 'e107_system/[hash]/temp/',
			'AVATARS_DIRECTORY'         => 'e107_media/[hash]/avatars/',
			'DOWNLOADS_DIRECTORY'       => 'e107_media/[hash]/files/',
			'UPLOADS_DIRECTORY'         => 'e107_system/[hash]/temp/',
		];

		$result = $this->invokeResolver($dirs, $sitePath);

		// 1. No e107_dirs value may contain a literal "[hash]" after
		//    the resolver has run. This is the headline assertion for
		//    issue #5631.
		foreach ($result as $key => $path)
		{
			$this->assertStringNotContainsString(
				'[hash]',
				$path,
				"e107_dirs[$key] still contains a literal [hash] after "
				. "resolveSitePathPlaceholders() ran; this is the bug "
				. "reported in #5631."
			);
		}

		// 2. The placeholder must have been replaced with the actual
		//    site hash, not merely stripped.
		$this->assertSame(
			'e107_media/' . $sitePath . '/avatars/',
			$result['AVATARS_DIRECTORY'],
			'AVATARS_DIRECTORY must have [hash] replaced with the site_path '
			. '(not stripped) so that derived multisite paths resolve '
			. 'to the correct on-disk location.'
		);
		$this->assertSame(
			'e107_system/' . $sitePath . '/cache/db/',
			$result['CACHE_DB_DIRECTORY'],
			'CACHE_DB_DIRECTORY must have [hash] replaced with the site_path; '
			. 'before #5631 this was left untouched by stage_7.'
		);

		// 3. The historical SYSTEM_DIRECTORY/MEDIA_DIRECTORY behaviour
		//    must be preserved: the "/{site_path}" segment is stripped
		//    (these two are the multisite-aware roots; the duplicated
		//    hash segment was a defaultDirs() artefact). Trace:
		//      "e107_system/[hash]/"  -- replace --> "e107_system/{sp}/"
		//      "e107_system/{sp}/"    -- strip   --> "e107_system/"
		$this->assertSame(
			'e107_system/',
			$result['SYSTEM_DIRECTORY'],
			'SYSTEM_DIRECTORY must have the "/{site_path}" segment stripped, '
			. 'leaving the bare "e107_system/" multisite root.'
		);
		$this->assertSame(
			'e107_media/',
			$result['MEDIA_DIRECTORY'],
			'MEDIA_DIRECTORY must have the "/{site_path}" segment stripped, '
			. 'leaving the bare "e107_media/" multisite root.'
		);

		// 4. Non-multisite entries must pass through unchanged.
		$this->assertSame('e107_admin/',  $result['ADMIN_DIRECTORY']);
		$this->assertSame('e107_images/', $result['IMAGES_DIRECTORY']);
		$this->assertSame('e107_core/',   $result['CORE_DIRECTORY']);
	}

	public function testResolverIsIdempotent()
	{
		// In the normal install flow, updatePaths() has already cleared
		// every "[hash]" before stage_7 runs. Calling the helper a
		// second time on its own output must therefore be a no-op
		// (other than the unconditional SYSTEM/MEDIA strip, which is
		// already idempotent because str_replace finds nothing to
		// replace once the path has been stripped).
		$sitePath = 'deadbeefcafef00d';

		$dirs = [
			'MEDIA_DIRECTORY'           => 'e107_media/[hash]/',
			'SYSTEM_DIRECTORY'          => 'e107_system/[hash]/',
			'CACHE_DB_DIRECTORY'        => 'e107_system/[hash]/cache/db/',
			'AVATARS_DIRECTORY'         => 'e107_media/[hash]/avatars/',
		];

		$first  = $this->invokeResolver($dirs, $sitePath);
		$second = $this->invokeResolver($first, $sitePath);

		$this->assertSame(
			$first,
			$second,
			'resolveSitePathPlaceholders() must be idempotent: calling it '
			. 'twice on already-resolved e107_dirs must not mutate the result.'
		);
	}

	/**
	 * Invoke the extracted resolveSitePathPlaceholders() helper against a
	 * synthetic e107 object holding only the two properties the helper
	 * touches.
	 *
	 * @param array  $dirs
	 * @param string $sitePath
	 * @return array  the modified e107_dirs array
	 */
	private function invokeResolver(array $dirs, $sitePath)
	{
		$reflectionClass = new \ReflectionClass(self::$renamedClass);
		$instance = $reflectionClass->newInstanceWithoutConstructor();

		$fakeE107 = new \stdClass();
		$fakeE107->e107_dirs = $dirs;
		$fakeE107->site_path = $sitePath;

		// Private members are reachable by ReflectionProperty/Method
		// directly since PHP 8.1; setAccessible() is a no-op and emits
		// a deprecation notice on 8.5+. Codeception (5.x) already
		// requires PHP 8.2+, so it is safe to elide the calls here.
		$e107Property = $reflectionClass->getProperty('e107');
		$e107Property->setValue($instance, $fakeE107);

		$method = $reflectionClass->getMethod('resolveSitePathPlaceholders');
		$method->invoke($instance);

		return $fakeE107->e107_dirs;
	}

	/**
	 * Walk install.php with the PHP tokenizer and return the source text
	 * of the named class definition (from "class" keyword to its closing
	 * brace, inclusive). Returns null if the class isn't found.
	 *
	 * @param string $source
	 * @param string $className
	 * @return string|null
	 */
	private static function extractClassBody($source, $className)
	{
		$tokens = token_get_all($source);
		$count = count($tokens);

		for ($i = 0; $i < $count; $i++)
		{
			$token = $tokens[$i];
			if (!is_array($token) || $token[0] !== T_CLASS)
			{
				continue;
			}

			// Skip whitespace between "class" and the class name.
			$j = $i + 1;
			while ($j < $count && is_array($tokens[$j])
				&& in_array($tokens[$j][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true))
			{
				$j++;
			}

			if ($j >= $count || !is_array($tokens[$j])
				|| $tokens[$j][0] !== T_STRING
				|| $tokens[$j][1] !== $className)
			{
				continue;
			}

			// Find the opening brace of the class body, then walk to its
			// matching closing brace by tracking nesting depth.
			$start = $i;
			$k = $j + 1;
			while ($k < $count
				&& !(is_string($tokens[$k]) && $tokens[$k] === '{'))
			{
				$k++;
			}
			if ($k >= $count)
			{
				return null;
			}

			$depth = 0;
			$end = null;
			for ($m = $k; $m < $count; $m++)
			{
				$cur = $tokens[$m];
				if (is_string($cur))
				{
					if ($cur === '{')
					{
						$depth++;
					}
					elseif ($cur === '}')
					{
						$depth--;
						if ($depth === 0)
						{
							$end = $m;
							break;
						}
					}
				}
				elseif (is_array($cur) && $cur[0] === T_CURLY_OPEN)
				{
					// "${...}" / "{$...}" interpolation opener — counts
					// as an open brace; its matching close is a plain '}'.
					$depth++;
				}
			}

			if ($end === null)
			{
				return null;
			}

			// Reassemble the class definition source text from tokens.
			$body = '';
			for ($n = $start; $n <= $end; $n++)
			{
				$body .= is_array($tokens[$n]) ? $tokens[$n][1] : $tokens[$n];
			}
			return $body;
		}

		return null;
	}
}
