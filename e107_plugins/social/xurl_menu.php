<?php

require_once(e_PLUGIN."social/e_shortcode.php");
$sc = new social_shortcodes;

$body = $sc->sc_xurl_icons($parm);

$title = isset($parm['caption'][e_LANGUAGE]) ? (string) $parm['caption'][e_LANGUAGE] : LAN_PLUGIN_SOCIAL_NAME ;

e107::getRender()->tablerender($title, $body, 'social-xurl-menu');

