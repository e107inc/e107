<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * The core route table, composed from routes.php for everything that consults it.
 *
 */

if (!defined('e107_INIT')) { exit; }

return new \e107\Routing\RouteTable(require(e_CORE.'routes.php'));
