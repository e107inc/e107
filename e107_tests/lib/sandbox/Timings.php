<?php

namespace Sandbox;

/**
 * How long each test file took last time, kept between runs so the queue can hand out the long ones first.
 */
class Timings
{
	/** @var string */
	private $file;

	/** @var array<string,float> */
	private $seconds = array();

	/** @param string $file */
	public function __construct($file)
	{
		$this->file = $file;
		$known = is_file($file) ? json_decode(file_get_contents($file), true) : null;
		if (is_array($known))
		{
			$this->seconds = $known;
		}
	}

	/**
	 * Measured seconds where known; otherwise the file's size, scaled by what a byte has cost the files that were measured.
	 *
	 * @param string $root the directory the paths are relative to
	 * @param string[] $files
	 * @return array<string,float>
	 */
	public function estimate($root, array $files)
	{
		$ratios = array();
		foreach ($files as $file)
		{
			if (isset($this->seconds[$file]))
			{
				$ratios[] = $this->seconds[$file] / max(1, filesize("$root/$file"));
			}
		}
		sort($ratios);
		$ratio = $ratios ? $ratios[(int) (count($ratios) / 2)] : 1.0;

		$weights = array();
		foreach ($files as $file)
		{
			$weights[$file] = isset($this->seconds[$file]) ? $this->seconds[$file] : filesize("$root/$file") * $ratio;
		}

		return $weights;
	}

	/** @param array<string,float> $seconds */
	public function record(array $seconds)
	{
		$this->seconds = array_merge($this->seconds, $seconds);
	}

	public function save()
	{
		@mkdir(dirname($this->file), 0777, true);
		file_put_contents($this->file.'.new', json_encode($this->seconds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		rename($this->file.'.new', $this->file);
	}
}
