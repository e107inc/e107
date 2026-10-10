<?php
/** Fixture template: its caption names the page and the route it was loaded on, so it fails to load where e_PAGE is not yet defined. */

if(!defined('e107_INIT')) { exit; }

$LOGOUT_TEMPLATE['confirm']['caption'] = 'Loaded on '.e_PAGE.' for '.e107::route();

$LOGOUT_TEMPLATE['confirm']['body'] = "<a href='{LOGOUT_CANCEL_URL}'>{LAN=LAN_CANCEL}</a>";
