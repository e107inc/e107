<?php
/** Fixture template: the online menus a theme supplies at its own root, marked so a test can see which file won. */

$sc_style['ONLINE_GUESTS']['pre'] = "<li class='tp-online-guests'>";
$sc_style['ONLINE_GUESTS']['post'] = "</li>";

$ONLINE_TEMPLATE['enabled'] = "<ul class='online-menu'><li>TPSTATE1_ONLINETPL_MARKER</li>{ONLINE_GUESTS}</ul>";

$ONLINE_MENU_TEMPLATE['lastseen']['start'] = "<ul class='lastseen-menu'><li>TPSTATE1_ONLINETPL_LASTSEEN</li>";
$ONLINE_MENU_TEMPLATE['lastseen']['item'] = "<li>{LASTSEEN_USERLINK}</li>";
$ONLINE_MENU_TEMPLATE['lastseen']['end'] = "</ul>";
