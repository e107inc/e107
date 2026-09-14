<?php
namespace Test;

/**
 * The class attributes a PHP source writes, for the conventions tests that hold core's markup to one class list.
 */
final class Markup
{
	/** Class lists already read, keyed by path, so a second rule over the same tree does not tokenise it again. */
	private static $read = array();

	/**
	 * Every class list the given files write, keyed by where it was found, for a rule that holds each one to a convention.
	 *
	 * @param array $paths absolute
	 * @return array 'path relative to the installation root #ordinal within the file' => string[] the classes on that element
	 */
	public static function classListsIn(array $paths)
	{
		$lists = array();

		foreach ($paths as $path)
		{
			$found = 0;

			foreach (self::classLists($path) as $classes)
			{
				$found++;
				$lists[substr($path, strlen(e_ROOT)) . ' #' . $found] = $classes;
			}
		}

		return $lists;
	}

	/**
	 * The classes of every class attribute one file writes, an attribute a concatenation cuts short counted for the classes it names before the cut.
	 *
	 * @param string $path absolute
	 * @return array[] one array of class names per attribute, in source order
	 */
	public static function classLists($path)
	{
		if (isset(self::$read[$path]))
		{
			return self::$read[$path];
		}

		$markup = array(T_INLINE_HTML, T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE);
		$lists = array();

		foreach (token_get_all(file_get_contents($path)) as $token)
		{
			if (!is_array($token) || !in_array($token[0], $markup, true))
			{
				continue;
			}

			$text = $token[0] === T_CONSTANT_ENCAPSED_STRING ? self::literal($token[1]) : $token[1];

			if (!preg_match_all('/\bclass\s*=\s*(["\'])([^"\']*)(?:\1|$)/', $text, $matches))
			{
				continue;
			}

			foreach ($matches[2] as $list)
			{
				$lists[] = preg_split('/\s+/', trim($list), -1, PREG_SPLIT_NO_EMPTY);
			}
		}

		self::$read[$path] = $lists;

		return $lists;
	}

	/**
	 * @param string $token the source text of one quoted string, its quotes included
	 * @return string what PHP would hold in memory for it
	 */
	private static function literal($token)
	{
		$quote = substr($token, 0, 1);
		$escaped = $quote === '"' ? array('\\"', '\\\\') : array("\\'", '\\\\');

		return str_replace($escaped, array($quote, '\\'), (string) substr($token, 1, -1));
	}
}
