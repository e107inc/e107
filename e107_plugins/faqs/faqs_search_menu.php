<?php


if (!defined('e107_INIT')) { exit; }

$sc =	e107::getScBatch('faqs', true);

$text = $tp->parseTemplate("{FAQ_SEARCH}", true, $sc);

$ns->tablerender(LAN_PLUGIN_FAQS_SEARCH, $text, 'faqs-search-menu');


