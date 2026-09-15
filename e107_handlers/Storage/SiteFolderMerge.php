<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Storage;

/**
 * Moves one site folder pair's files into another, file by file, never over a file already there.
 *
 * Applying is idempotent: each run plans from what is on disk, so a second run moves nothing
 * and a run after the operator has cleared a collision by hand finishes the job.
 */
final class SiteFolderMerge
{
	/** @var SiteFolder */
	private $from;

	/** @var SiteFolder */
	private $to;

	/**
	 * @param SiteFolder $from
	 * @param SiteFolder $to
	 * @throws \InvalidArgumentException when $to is the known-bad pair or the same pair as $from
	 */
	public function __construct(SiteFolder $from, SiteFolder $to)
	{
		if($to->isKnownBad())
		{
			throw new \InvalidArgumentException('The folder named by the hash of two empty values is never a merge target');
		}

		if($from->hash() === $to->hash())
		{
			throw new \InvalidArgumentException('A site folder cannot be merged into itself');
		}

		$this->from = $from;
		$this->to = $to;
	}

	/**
	 * @return array 'moves' and 'collisions', each a list of root-prefixed relative paths
	 */
	public function plan()
	{
		$plan = array('moves' => array(), 'collisions' => array());

		foreach($this->from->files() as $relative)
		{
			$plan[self::occupied($this->to->path($relative)) ? 'collisions' : 'moves'][] = $relative;
		}

		return $plan;
	}

	/**
	 * Carries out plan(); when every move went through, the source then deletes what it regenerates and the directories left empty.
	 *
	 * @return array 'moved' and 'collisions' as lists of relative paths, 'failed' as relative path => reason
	 */
	public function apply()
	{
		$plan = $this->plan();
		$result = array('moved' => array(), 'collisions' => $plan['collisions'], 'failed' => array());

		foreach($plan['moves'] as $relative)
		{
			$target = $this->to->path($relative);
			$reason = self::move($this->from->path($relative), $target);

			if($reason === '')
			{
				$result['moved'][] = $relative;
			}
			elseif(self::occupied($target))
			{
				$result['collisions'][] = $relative;
			}
			else
			{
				$result['failed'][$relative] = $reason;
			}
		}

		if(empty($result['failed']))
		{
			$this->from->tidy();
		}

		return $result;
	}

	/**
	 * @param string $path
	 * @return bool whether anything, a dangling symbolic link included, already sits at $path
	 */
	private static function occupied($path)
	{
		return file_exists($path) || is_link($path);
	}

	/**
	 * @param string $source
	 * @param string $target
	 * @return string '' when moved, otherwise why not
	 */
	private static function move($source, $target)
	{
		$dir = dirname($target);

		if(!is_dir($dir))
		{
			$reason = self::attempt(function() use ($dir)
			{
				return mkdir($dir, 0755, true);
			});

			if($reason !== '')
			{
				return $reason;
			}
		}

		if(self::occupied($target))
		{
			return 'the target already exists';
		}

		return self::attempt(function() use ($source, $target)
		{
			return rename($source, $target);
		});
	}

	/**
	 * @param callable $operation returns true on success
	 * @return string '' on success, otherwise the diagnostic PHP raised or a placeholder when it raised none
	 */
	private static function attempt($operation)
	{
		$message = '';

		set_error_handler(function($severity, $text) use (&$message)
		{
			$message = $text;

			return true;
		});

		$done = $operation();

		restore_error_handler();

		if($done)
		{
			return '';
		}

		return $message === '' ? 'refused without a diagnostic' : $message;
	}
}
