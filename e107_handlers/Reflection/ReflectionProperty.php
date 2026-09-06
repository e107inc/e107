<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Reflection;

/**
 * {@see \ReflectionProperty} that may read and write the member it reflects
 * on every PHP e107 runs on.
 *
 * PHP 8.1 stopped requiring {@see \ReflectionProperty::setAccessible()} and
 * 8.5 deprecates calling it; PHP 5.6 to 8.0 still refuse a private or
 * protected member without it. Construct this class instead of
 * \ReflectionProperty and never call setAccessible() yourself.
 */
class ReflectionProperty extends \ReflectionProperty
{
	public function __construct($class, $property)
	{
		parent::__construct($class, $property);

		if (PHP_VERSION_ID < 80100)
		{
			$this->setAccessible(true);
		}
	}
}
