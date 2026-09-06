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
class resize_handlerTest extends \Test\Unit
{

	/** @var string */
	private $workDir;

	/** @var string */
	private $source;

	/**
	 * Marker that _before() actually reached the pref block; guards the
	 * teardown so a run that bailed out earlier doesn't clobber globals it
	 * never touched (and that downstream tests in the same shuffled run
	 * rely on).
	 *
	 * @var bool
	 */
	private $prefMutated = false;

	/** @var array|null Saved $pref snapshot. */
	private $savedPref;

	protected function _before()
	{
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
		// PHPUnit/Codeception runs tearDown even when setUp short-circuited,
		// so we'd otherwise unset $GLOBALS['pref'] (savedPref still at its
		// default null) and break every later test in the same shuffled suite
		// run that does `global $pref; $pref[...]`.
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
		$this->requireImageMagick();

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
		$this->requireImageMagick();

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

	// -----------------------------------------------------------------
	// im_path: the third interpolation on the same two lines
	// -----------------------------------------------------------------

	/**
	 * Payloads that smuggle a command into the 'im_path' preference.
	 *
	 * resize_handler.php:161 and :165 build the command line as
	 *
	 *   $pref['im_path']."convert -quality ".intval(...)." ... ".escapeshellarg(...)
	 *
	 * Every argument on those lines is intval()'d or escapeshellarg()'d. The
	 * prefix is not, and it is the part a preference supplies. The fix for
	 * GHSA-3j33-c9v4-4p42 / CVE-2026-48997 escaped the source and the
	 * destination and left this one where it was.
	 *
	 * `%s` is the absolute path of a marker file the payload creates.
	 *
	 * @return string[][]
	 */
	public function imPathPayloads()
	{
		return [
			'command separator' => ['touch %s; '],
			'subshell'          => ['$(touch %s) '],
			'backticks'         => ['`touch %s` '],
			'trailing pipeline' => ['touch %s || '],
		];
	}

	/**
     * @dataProvider imPathPayloads
     * @param string $payloadTemplate
     */
    public function testResizeImageMustNotExecuteShellMetacharactersInImPath($payloadTemplate)
	{
		$this->requireWorkingShell();

		$marker = $this->workDir.'/marker_'.bin2hex(random_bytes(6));

		$GLOBALS['pref']['im_path'] = sprintf($payloadTemplate, $marker);

		$destination = $this->workDir.'/out.jpg';

		ob_start();
		$this->runWithWarningsSuppressed(function () use ($destination)
		{
			resize_image($this->source, $destination, 400);
		});
		ob_end_clean();

		$this->assertFalse(
			is_file($marker),
			'The im_path preference "'.$payloadTemplate.'" reached /bin/sh: resize_image() ran '
				.'`touch '.$marker.'` as the web account. im_path is the one interpolation on '
				.'resize_handler.php:161 and :165 that is neither intval()\'d nor escapeshellarg()\'d. '
				.'No unauthenticated route reaches it since e107_images/thumb.php became a shim over '
				.'e_thumbnail, so the remaining callers are the authenticated upload paths.'
		);
	}

	// -----------------------------------------------------------------
	// controls: a fix must not break ImageMagick for real sites
	// -----------------------------------------------------------------

	/**
	 * im_path is a directory prefix, not an argument. A site that keeps
	 * ImageMagick outside $PATH sets it to the directory holding `convert`,
	 * with a trailing separator, and the shipped default in
	 * e107_core/xml/default_install.xml is exactly that shape.
	 *
	 * escapeshellarg() on the prefix would produce `'/opt/bin/'convert`, which
	 * no shell resolves. This case is what stops a fix that escapes everything
	 * and silently takes ImageMagick away from every site that ever set the
	 * preference.
	 */
	public function testImageMagickBranchStillWorksWithADirectoryPrefixImPath()
	{
		$this->requireImageMagick();

		$binary = trim((string) shell_exec('command -v convert 2>/dev/null'));

		$shimDir = $this->workDir.'/imagemagick/';
		if (!mkdir($shimDir, 0700, true) || !symlink($binary, $shimDir.'convert'))
		{
			$this->fail("Could not build an ImageMagick directory at {$shimDir}");
		}

		$this->assertSame(
			1,
			preg_match('~^/.*/$~', $this->shippedDefaultImPath()),
			'This case stands in for the shipped default im_path, so the two have to be the same shape.'
		);

		$destination = $this->workDir.'/prefixed.jpg';

		$GLOBALS['pref']['im_path'] = $shimDir;

		ob_start();
		$result = $this->runWithWarningsSuppressed(function () use ($destination)
		{
			return resize_image($this->source, $destination, 400);
		});
		$output = ob_get_clean();

		$this->assertTrue(
			$result,
			'resize_image() refused a legitimate directory-prefix im_path ('.$shimDir.'). Output: '.$output
		);
		$this->assertFileExists($destination, 'The ImageMagick branch produced no image.');

		$stats = getimagesize($destination);
		$this->assertNotFalse($stats, 'The ImageMagick branch wrote something that is not an image.');
		$this->assertSame(IMAGETYPE_JPEG, $stats[2], 'The ImageMagick branch wrote a non-JPEG.');
		$this->assertSame(400, $stats[0], 'The ImageMagick branch did not resize to the requested width.');
	}

	/**
	 * The other legitimate value, and by far the commonest: empty, meaning
	 * `convert` is on $PATH. A fix that insists on an absolute path would break
	 * every site that never touched the preference at all.
	 */
	public function testImageMagickBranchStillWorksWithAnEmptyImPath()
	{
		$this->requireImageMagick();

		$destination = $this->workDir.'/onpath.jpg';

		$GLOBALS['pref']['im_path'] = '';

		ob_start();
		$result = $this->runWithWarningsSuppressed(function () use ($destination)
		{
			return resize_image($this->source, $destination, 400);
		});
		$output = ob_get_clean();

		$this->assertTrue($result, 'resize_image() refused an empty im_path. Output: '.$output);
		$this->assertFileExists($destination, 'The ImageMagick branch produced no image.');
	}

	/**
	 * Values resize_image() has no backend for.
	 *
	 * resize_method is in media_admin_ui::$prefs, so an administrator writes it
	 * from the media preferences page and it arrives at resize_image() as
	 * whatever the request said. The switch has to keep answering "no" to
	 * everything it does not recognise: near misses in case and whitespace, a
	 * value carrying its own shell payload, and a backend that does not exist.
	 *
	 * @return string[][]
	 */
	public function outOfRangeResizeMethods()
	{
		return [
			'wrong case'         => ['imagemagick'],
			'trailing space'     => ['ImageMagick '],
			'command appended'   => ['ImageMagick; touch'],
			'no such backend'    => ['gd3'],
			'numeric'            => ['1'],
			'array-ish string'   => ['Array'],
		];
	}

	/**
     * @dataProvider outOfRangeResizeMethods
     * @param string $method
     */
    public function testOutOfRangeResizeMethodReachesNoBackend($method)
	{
		$this->requireWorkingShell();

		$marker = $this->workDir.'/marker_'.bin2hex(random_bytes(6));
		$destination = $this->workDir.'/out_'.bin2hex(random_bytes(4)).'.jpg';

		// If an unrecognised mode were to fall through to the ImageMagick
		// branch, this im_path would say so out loud.
		$GLOBALS['pref']['im_path'] = 'touch '.$marker.'; ';
		$GLOBALS['pref']['resize_method'] = $method;

		ob_start();
		$result = $this->runWithWarningsSuppressed(function () use ($destination)
		{
			return resize_image($this->source, $destination, 400);
		});
		ob_end_clean();

		$this->assertFalse($result, 'resize_image() should refuse the unknown resize_method "'.$method.'".');
		$this->assertFalse(is_file($marker), 'resize_method "'.$method.'" reached the ImageMagick branch.');
		$this->assertFalse(is_file($destination), 'resize_method "'.$method.'" produced an output file.');
	}

	/**
	 * The im_path default e107 installs with.
	 *
	 * @return string
	 */
	private function shippedDefaultImPath()
	{
		$xml = file_get_contents(APP_PATH.'/e107_core/xml/default_install.xml');

		if (!preg_match('~<core name="im_path">([^<]*)</core>~', $xml, $matches))
		{
			$this->fail('default_install.xml declares no im_path default.');
		}

		return $matches[1];
	}

	/**
	 * Refuse to draw a conclusion from an absent marker file on a host where
	 * the shell was never reachable in the first place.
	 *
	 * The im_path cases do not need `convert`: the payload fires before the
	 * shell ever looks the binary up. They do need exec() to work, and a
	 * hardened php.ini or a read-only workdir would make every one of them
	 * pass while proving nothing at all.
	 */
	private function requireWorkingShell()
	{
		$disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));

		if (in_array('exec', $disabled, true) || in_array('passthru', $disabled, true))
		{
			$this->markTestSkipped('exec()/passthru() are disabled here, so an absent marker would prove nothing.');
		}

		$canary = $this->workDir.'/shell_canary_'.bin2hex(random_bytes(4));
		$out = [];
		$rc = 0;
		exec('touch '.escapeshellarg($canary), $out, $rc);

		$this->assertSame(0, $rc, 'exec() could not run `touch` here.');
		$this->assertFileExists($canary, 'exec() ran `touch` and no file appeared; the marker assertions would be vacuous.');

		unlink($canary);
	}

	/**
	 * Skip the calling case when the `convert` binary is missing.
	 *
	 * Only the destination-path cases need it: they drive a real resize and
	 * then inspect what `convert` did or did not write, which proves nothing
	 * when the binary was never there to run. Cases that attack the command
	 * line itself, such as an injected 'im_path' preference, must not call
	 * this: passthru() hands the string to /bin/sh, so the payload fires
	 * before `convert` is ever looked up and the regression is observable on
	 * a host without ImageMagick.
	 */
	private function requireImageMagick()
	{
		if (!self::imageMagickAvailable())
		{
			$this->markTestSkipped('ImageMagick (convert) is not installed; this case needs the real binary.');
		}
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
