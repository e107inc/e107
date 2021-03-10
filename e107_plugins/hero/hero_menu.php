<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2016 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Hero menu file.
 *
 */


if (!defined('e107_INIT')) { exit; }

// e107::lan('hero','menu', true);

$text = e107::getParser()->parseTemplate("{HERO: template=menu}");
e107::getRender()->tablerender(null, $text, 'hero-menu');

