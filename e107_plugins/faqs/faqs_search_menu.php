<?php


if (!defined('e107_INIT')) { exit; }

$sc =	e107::getScBatch('faqs', true);

$tmpl = e107::getTemplate('faqs');

$text = $tp->parseTemplate("{FAQ_SEARCH}", true, $sc);

$sc->setVars(array());

$ns->tablerender($sc->caption($tmpl, 'search', LAN_PLUGIN_FAQS_SEARCH), $text, 'faqs-search-menu');


