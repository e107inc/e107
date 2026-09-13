<?php
/**
 * An include, the shape a template or a menu has: a direct request gets the
 * guard's exit, so the sweep has no business loading it.
 */

if(!defined('e107_INIT')) { exit; }

echo "include fixture loaded\n";
