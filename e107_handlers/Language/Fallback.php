<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Language;

use e107\Cache\SourceDerived;

/**
 * Defines the phrases a language file lacks once it has loaded: in English, or as their own names.
 */
final class Fallback
{
	/** Room for the names a report lists; trigger_error() keeps 1024 bytes of a message. */
	const REPORT_NAMES = 700;

	/** @var callable */
	private $newReader;

	/** @var PhraseFile|null */
	private $reader = null;

	/** @var SourceDerived */
	private $gaps;

	/** @var SourceDerived */
	private $phrases;

	/** @var string[] */
	private $folders;

	/** @var callable|null */
	private $report;

	/** @var \Closure */
	private $besideEnglish;

	/** @var \Closure */
	private $englishAlone;

	/** @var \Closure */
	private $readPhrases;

	/** @var array */
	private $done = array();

	/** @var array name => true, for the phrases files about to load will define */
	private $leftTo = array();

	/**
	 * @param callable      $newReader returns the PhraseFile that reads a file the caches do not answer for
	 * @param SourceDerived $gaps    keeps, per language file, the phrases it lacks
	 * @param SourceDerived $phrases keeps the phrases of files read on their own
	 * @param string[]      $folders endings of the folder names whose subfolders are languages, such as 'languages'
	 * @param callable|null $report  given one message per file whose phrases were defined
	 */
	public function __construct($newReader, SourceDerived $gaps, SourceDerived $phrases, array $folders, $report = null)
	{
		$this->folders = array();

		foreach(array_unique($folders) as $folder)
		{
			$this->folders[] = trim($folder, '/').'/';
		}

		$this->newReader = $newReader;
		$this->gaps = $gaps;
		$this->phrases = $phrases;
		$this->report = $report;

		$this->besideEnglish = function ($source, array $english)
		{
			return $this->gapsBetween($this->reader()->read($source), $this->reader()->read((string) @file_get_contents($english[0])));
		};

		$this->englishAlone = function ($source)
		{
			return $this->gapsBetween(array('how' => array()), $this->reader()->read($source));
		};

		$this->readPhrases = function ($source)
		{
			return $this->reader()->read($source);
		};
	}

	/**
	 * Define the phrases a language file lacks, once it has been included or found missing.
	 *
	 * @param string $path   the file in its own language; English files and _custom files are left alone
	 * @param bool   $byName define each phrase as its own name instead of with the English value
	 * @param bool   $arrays whether the loader defined the phrases of an array the file returned
	 * @return string[] the names defined
	 */
	public function fill($path, $byName, $arrays)
	{
		if(isset($this->done[$path]))
		{
			return array();
		}

		$this->done[$path] = true;
		$english = $this->englishPath($path);

		if($english === null || $english === $path || $this->isOverlay($path))
		{
			return array();
		}

		if(!function_exists('token_get_all'))
		{
			$this->includeEnglish($english, $byName);
			return array();
		}

		$entry = $this->gaps->get($path, $this->besideEnglish, array($english));

		if($entry === null)
		{
			$entry = $this->gaps->get($english, $this->englishAlone);
		}

		if(!is_array($entry))
		{
			return array();
		}

		$filled = array();

		foreach($entry['gaps'] as $name => $value)
		{
			if(($arrays && isset($entry['listed'][$name])) || isset($this->leftTo[$name]) || defined($name))
			{
				continue;
			}

			define($name, $byName ? $name : $this->resolve($value));
			$filled[] = $name;
		}

		if(!empty($filled))
		{
			$this->tell(sprintf('%d phrase(s) missing from %s were defined %s: %s', count($filled), $path,
				$byName ? 'as their own names' : 'in English', $this->listNames($filled)));
		}

		return $filled;
	}

	/**
	 * Leave to the given files, which are about to load, the phrases they define; an empty list ends it.
	 *
	 * @param string[] $paths
	 * @param bool     $arrays whether the loader will define the phrases of an array such a file returns
	 * @return void
	 */
	public function leaveTo(array $paths, $arrays = false)
	{
		$this->leftTo = array();

		foreach($paths as $path)
		{
			$read = $this->phrases->get($path, $this->readPhrases);

			foreach(is_array($read) ? $read['how'] : array() as $name => $how)
			{
				if($how === PhraseFile::DEFINED || $how === PhraseFile::GUARDED || ($arrays && $how === PhraseFile::LISTED))
				{
					$this->leftTo[$name] = true;
				}
			}
		}
	}

	/**
	 * Define each phrase of an array a language file returned that is not defined yet.
	 *
	 * @param array $terms name => value
	 * @return void
	 */
	public function defineTerms(array $terms)
	{
		foreach($terms as $name => $value)
		{
			if(!defined($name))
			{
				define($name, $value);
			}
		}
	}

	/**
	 * The English counterpart of a language file, found from the language its path names.
	 *
	 * @param string $path
	 * @return string|null null when the path names no language
	 */
	public function englishPath($path)
	{
		$found = $this->locate($path);

		if($found === null)
		{
			return null;
		}

		return ($found[1] === 'English') ? $path : $this->swap($path, $found[0], $found[1], 'English');
	}

	/**
	 * Swap the language a language file's path names, in its language folder and its file name alone.
	 *
	 * @param string $path
	 * @param string $from
	 * @param string $to
	 * @return string
	 */
	public function translatePath($path, $from, $to)
	{
		$found = $this->locate($path);

		if($found === null || $found[1] !== $from || $from === $to)
		{
			return $path;
		}

		return $this->swap($path, $found[0], $from, $to);
	}

	/**
	 * Where the language a path names starts, after the last language folder, and what it is.
	 *
	 * @param string $path
	 * @return array|null the offset and the language, or null when the path names none
	 */
	private function locate($path)
	{
		$at = false;

		foreach($this->folders as $folder)
		{
			$found = strrpos($path, $folder);

			if($found !== false && ($at === false || $found + strlen($folder) > $at))
			{
				$at = $found + strlen($folder);
			}
		}

		if($at === false)
		{
			return null;
		}

		$rest = (string) substr($path, $at);
		$slash = strpos($rest, '/');

		if($slash === false && substr($rest, -4) !== '.php')
		{
			return null;
		}

		$language = (string) substr($rest, 0, ($slash === false) ? strcspn($rest, '_.') : $slash);

		if($language === '' || strcspn($language, '_.') !== strlen($language))
		{
			return null;
		}

		return array($at, $language);
	}

	/**
	 * @param string $path
	 * @param int    $at       where the language starts
	 * @param string $language
	 * @param string $to
	 * @return string the path with the language swapped there and at the start of the file name
	 */
	private function swap($path, $at, $language, $to)
	{
		$path = substr($path, 0, $at).$to.substr($path, $at + strlen($language));
		$slash = strrpos($path, '/');
		$file = (string) substr($path, $slash === false ? 0 : $slash + 1);
		$next = (string) substr($file, strlen($language), 1);

		if(strpos($file, $language) === 0 && ($next === '_' || $next === '.'))
		{
			$path = substr($path, 0, strlen($path) - strlen($file)).$to.substr($file, strlen($language));
		}

		return $path;
	}

	/**
	 * @param array $have what the file defines, as {@see PhraseFile::read()} returns it
	 * @param array $from what its English counterpart defines
	 * @return array 'gaps' maps each phrase the file may lack to its English value; 'listed' names those it only returns in an array
	 */
	private function gapsBetween(array $have, array $from)
	{
		$gaps = array();
		$listed = array();

		foreach($from['phrases'] as $name => $value)
		{
			$how = isset($have['how'][$name]) ? $have['how'][$name] : null;

			if($how === PhraseFile::DEFINED || $how === PhraseFile::GUARDED)
			{
				continue;
			}

			$gaps[$name] = $value;

			if($how === PhraseFile::LISTED)
			{
				$listed[$name] = true;
			}
		}

		return array('gaps' => $gaps, 'listed' => $listed);
	}

	/**
	 * @return PhraseFile
	 */
	private function reader()
	{
		if($this->reader === null)
		{
			$this->reader = call_user_func($this->newReader);
		}

		return $this->reader;
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	private function isOverlay($path)
	{
		return substr($path, -strlen('_custom.php')) === '_custom.php';
	}

	/**
	 * @param array|string|bool|int|float|null $value a scalar, or parts naming constants
	 * @return mixed
	 */
	private function resolve($value)
	{
		if(!is_array($value))
		{
			return $value;
		}

		$text = '';

		foreach($value as $part)
		{
			$text .= is_array($part) ? (defined($part[0]) ? constant($part[0]) : $part[0]) : $part;
		}

		return $text;
	}

	/**
	 * Without the tokenizer the English file is run instead, keeping the locale; a define() file's phrases come out in English whatever $byName says.
	 *
	 * @param string $english
	 * @param bool   $byName
	 * @return void
	 */
	private function includeEnglish($english, $byName)
	{
		if(!is_readable($english))
		{
			return;
		}

		$locale = setlocale(LC_ALL, 0);
		$terms = @include $english;
		setlocale(LC_ALL, $locale);

		if(is_array($terms))
		{
			$this->defineTerms($byName ? array_combine(array_keys($terms), array_keys($terms)) : $terms);
		}
	}

	/**
	 * @param string[] $names
	 * @return string
	 */
	private function listNames(array $names)
	{
		$list = implode(', ', $names);

		if(strlen($list) <= self::REPORT_NAMES)
		{
			return $list;
		}

		return substr($list, 0, strrpos(substr($list, 0, self::REPORT_NAMES), ',')).', ...';
	}

	/**
	 * @param string $message
	 * @return void
	 */
	private function tell($message)
	{
		if($this->report !== null)
		{
			call_user_func($this->report, $message);
		}
	}
}
