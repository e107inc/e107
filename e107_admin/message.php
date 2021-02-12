<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2009 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

// FILE IS DEPRECATED - UP FOR REMOVAL IN THE FUTURE

require_once(__DIR__.'/../class2.php');

e107::coreLan('message', true);

$e_sub_cat = 'message';
require_once("auth.php");
$gen = new convert;
$mes = e107::getMessage();

// DO NOT TRANSLATE - warning for deprecated file. 
e107::getMessage()->addWarning("This area is no longer in use and will be removed in the future. For reported broken downloads, see the Downloads Admin Area.");

$ns->tablerender("Received Messages", $mes->render());


require_once("footer.php");