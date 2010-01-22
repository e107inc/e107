<?php

if (!defined('e107_INIT')) { exit; }

$NEWFORUMPOSTSTYLE_HEADER = "
<!-- newforumposts -->\n<ul>\n";

$NEWFORUMPOSTSTYLE_MAIN = "<li><span class='smalltext'>{THREAD} by {POSTER} [ views: {VIEWS}, replies: {REPLIES}, lastpost: {LASTPOST} ]\n</span>\n</li>\n";

$NEWFORUMPOSTSTYLE_FOOTER = "</ul>\n<br /><br />\n<span class='smalltext'>".NFPM_LAN_6.": <b>{TOTAL_TOPICS}</b> | ".NFPM_LAN_4.": <b>{TOTAL_REPLIES}</b> | ".NFPM_LAN_3.": <b>{TOTAL_VIEWS}</b></span>";


?>