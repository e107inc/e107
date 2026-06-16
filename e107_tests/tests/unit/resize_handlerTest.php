<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Regression coverage for GHSA-3j33-c9v4-4p42.
 *
 * resize_image() builds an ImageMagick `convert` command line by string
 * concatenation. Historically the destination path was wrapped in raw
 * double quotes and not escaped — POSIX shells evaluate $(...) and
 * `...` inside double quotes, so any caller that passed an attacker-
 * influenced destination filename (notably submitnews.php) could
 * execute arbitrary commands as the web server account.
 *
 * The fix is to pass the destination path through escapeshellarg(), so
 * any shell metacharacters are taken literally. These tests assert that
 * behaviour by invoking the real ImageMagick branch with crafted
 * destinations and checking that no command substitution leaked into
 * the filesystem.
 */
class resize_handlerTest extends \Codeception\Test\Unit
{
	use \Helper\PhpUnitCompat;
	/** @var string */
	private $workDir;

	/** @var string */
	private $source;

	/**
	 * Marker that _before() actually ran past markTestSkipped(); guards the
	 * teardown so a skipped run doesn't clobber globals it never touched
	 * (and that downstream tests in the same shuffled run rely on).
	 *
	 * @var bool
	 */
	private $prefMutated = false;

	/** @var array|null Saved $pref snapshot. */
	private $savedPref;

	protected function _before()
	{
		if (!self::imageMagickAvailable())
		{
			$this->markTestSkipped('ImageMagick (convert) is not installed; skipping shell-injection regression.');
		}

		require_once(e_HANDLER.'resize_handler.php');

		$this->workDir = sys_get_temp_dir().'/e107-resize-ghsa-3j33-'.bin2hex(random_bytes(6));
		if (!mkdir($this->workDir, 0700, true))
		{
			$this->fail("Could not create workdir {$this->workDir}");
		}

		$this->source = $this->workDir.'/source.jpg';
		$this->createSourceImage($this->source, 1200, 900);

		// Switch the resize backend to ImageMagick for the duration of the test,
		// and clear any path/quality prefs so we get deterministic command lines.
		$this->savedPref = isset($GLOBALS['pref']) ? $GLOBALS['pref'] : null;
		if (!isset($GLOBALS['pref']) || !is_array($GLOBALS['pref']))
		{
			$GLOBALS['pref'] = [];
		}
		$GLOBALS['pref']['resize_method'] = 'ImageMagick';
		$GLOBALS['pref']['im_path']       = '';
		$GLOBALS['pref']['im_quality']    = 99;
		$GLOBALS['pref']['image_owner']   = '';
		unset($GLOBALS['pref']['im_width'], $GLOBALS['pref']['im_height']);
		$this->prefMutated = true;
	}

	protected function _after()
	{
		// PHPUnit/Codeception runs tearDown even when setUp short-circuited
		// via markTestSkipped(), so we'd otherwise unset $GLOBALS['pref']
		// (savedPref still at its default null) and break every later test
		// in the same shuffled suite run that does `global $pref; $pref[...]`.
		if (!$this->prefMutated)
		{
			return;
		}

		if ($this->savedPref === null)
		{
			unset($GLOBALS['pref']);
		}
		else
		{
			$GLOBALS['pref'] = $this->savedPref;
		}

		if ($this->workDir && is_dir($this->workDir))
		{
			$this->rmrf($this->workDir);
		}
	}

	/**
	 * Payloads that smuggle command substitution into a destination filename.
	 *
	 * Each payload contains a `%s` placeholder where the test will splice in
	 * the absolute path of a marker file. The payloads, when fed through a
	 * POSIX shell, run `touch <marker>` — on a vulnerable build the marker
	 * file appears on disk; on a patched build it does not.
	 *
	 * Three forms are exercised:
	 *   - $(...) command substitution wholly inside the destination filename
	 *   - `...` backtick command substitution
	 *   - "..." inline that closes and reopens the wrapping double quotes,
	 *     which is the worst case: argv splits into several convert args and
	 *     the resulting output filename can escape into the CWD.
	 *
	 * @return string[][]
	 */
	public function destinationPayloads()
	{
		return [
			'dollar parens' => ['out_$(touch %s).jpg'],
			'backticks'     => ['out_`touch %s`.jpg'],
			'closing quote' => ['out".$(touch %s)."after.jpg'],
		];
	}

	/**
     * @dataProvider destinationPayloads
     * @param string $payloadTemplate
     */
    public function testResizeImageMustNotExecuteShellMetacharactersInDestination($payloadTemplate)
	{
		// Marker file lives next to the source so cleanup is automatic in _after().
		// Name uses only hex so it can't itself influence shell parsing.
		$marker = $this->workDir.'/marker_'.bin2hex(random_bytes(6));
		$payload = sprintf($payloadTemplate, $marker);
		$destination = $this->workDir.'/'.$payload;

		// resize_image() emits non-fatal warnings (e.g. getimagesize() on the
		// missing destination) when it can't write what we asked for. We're
		// interested in whether the shell ran our command, not in those side
		// effects — suppress them so they don't masquerade as the regression.
		ob_start();
		$this->runWithWarningsSuppressed(function () use ($destination)
		{
			resize_image($this->source, $destination, 400);
		});
		ob_end_clean();

		$this->assertFileDoesNotExist(
			$marker,
			'Destination payload "'.$payloadTemplate.'" caused the shell to execute '
				.'`touch '.$marker.'`. resize_image() must escapeshellarg() the '
				.'destination path before passing it to exec/passthru.'
		);

		// Belt-and-braces: also assert no file with id-style output appeared
		// in the workdir or in the process CWD (the "closing quote" payload
		// can shunt output into CWD on a vulnerable build).
		$this->assertSame(
			[],
			$this->findInjectedFiles($this->workDir),
			'Shell substitution leaked id output into a filename in the workdir.'
		);
		$this->assertSame(
			[],
			$this->findInjectedFiles(getcwd()),
			'Shell substitution leaked id output into a filename in the CWD.'
		);
	}

	public function testResizeImageWritesLiteralDestinationFilename()
	{
		// A destination name that *contains* shell metacharacters but is otherwise
		// a valid POSIX filename. After the fix the file should be written
		// verbatim; before the fix the shell ate the `$(id)` substring.
		$payload = 'out_$(id).jpg';
		$destination = $this->workDir.'/'.$payload;

		ob_start();
		$result = $this->runWithWarningsSuppressed(function () use ($destination)
		{
			return resize_image($this->source, $destination, 400);
		});
		ob_end_clean();

		$this->assertTrue(
			file_exists($destination),
			'resize_image() should write the destination at the literal path it was given, '
				.'not whatever the shell evaluates it to. Expected file: '.$destination
		);
		$this->assertTrue($result, 'resize_image() should report success when writing the destination.');
	}

	/**
	 * Run $fn with E_WARNING/E_NOTICE silenced. Codeception's ErrorHandler
	 * promotes those to fatal test errors by default, which would mask the
	 * actual assertion we care about.
	 */
	private function runWithWarningsSuppressed(callable $fn)
	{
		set_error_handler(static function ($errno) {
			return ($errno & (E_WARNING | E_NOTICE | E_DEPRECATED | E_USER_WARNING | E_USER_NOTICE)) !== 0;
		});
		try
		{
			return $fn();
		}
		finally
		{
			restore_error_handler();
		}
	}

	/**
     * @param string $path
     * @param int $width
     * @param int $height
     */
    private function createSourceImage($path, $width, $height)
	{
		$img = imagecreatetruecolor($width, $height);
		imagefill($img, 0, 0, imagecolorallocate($img, 135, 206, 235));
		imagejpeg($img, $path, 80);
		imagedestroy($img);
	}

	/**
     * Filenames in $dir that contain output from the `id` command,
     * which only appears if command substitution actually fired.
     *
     * @return string[]
     * @param string $dir
     */
    private function findInjectedFiles($dir)
	{
		$leaked = [];
		foreach (scandir($dir) as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}
			// `id` always prints `uid=` and `gid=`; either appearing in a filename
			// is conclusive evidence the shell evaluated the payload.
			if (strpos($entry, 'uid=') !== false || strpos($entry, 'gid=') !== false)
			{
				$leaked[] = $entry;
			}
		}
		return $leaked;
	}

	/**
     * @param string $dir
     */
    private function rmrf($dir)
	{
		foreach (scandir($dir) as $entry)
		{
			if ($entry === '.' || $entry === '..')
			{
				continue;
			}
			$path = $dir.'/'.$entry;
			is_dir($path) ? $this->rmrf($path) : @unlink($path);
		}
		@rmdir($dir);
	}

	/**
     * @return bool
     */
    private static function imageMagickAvailable()
	{
		$which = trim((string) shell_exec('command -v convert 2>/dev/null'));
		return $which !== '';
	}
}
