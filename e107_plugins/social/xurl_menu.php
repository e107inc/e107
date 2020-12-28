<?php

require_once(e_PLUGIN."social/e_shortcode.php");
$sc = new social_shortcodes;
$body = $sc->sc_xurl_icons();
$title = empty($parm['xurlCaption'][e_LANGUAGE]) ? LAN_PLUGIN_SOCIAL_NAME : $parm['xurlCaption'][e_LANGUAGE];

e107::getRender()->tablerender($title, $body, 'social-xurl-menu');

