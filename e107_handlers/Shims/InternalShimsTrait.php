<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Shims for PHP internal functions
 */

namespace e107\Shims;

trait InternalShimsTrait
{
	use Internal\GetParentClassTrait;
	use Internal\ReadfileTrait;
	use Internal\StrftimeTrait;
	use Internal\StrptimeTrait;
}