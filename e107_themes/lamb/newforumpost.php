<?php

if (!defined('e107_INIT')) { exit; }

$NEWFORUMPOSTSTYLE_HEADER = "
<!-- newforumposts -->\n<ul>\n";

$NEWFORUMPOSTSTYLE_MAIN = "<li><span class='smalltext'>{THREAD} ".NFPM_LAN_7." {POSTER} [ ".NFPM_LAN_3.": {VIEWS}, ".NFPM_LAN_4.": {REPLIES}, ".NFPM_LAN_5.": {LASTPOST} ]\n</span>\n</li>\n";

$NEWFORUMPOSTSTYLE_FOOTER = "</ul>\n<br /><br />\n<span class='smalltext'>".NFPM_LAN_6.": <b>{TOTAL_TOPICS}</b> | ".NFPM_LAN_4.": <b>{TOTAL_REPLIES}</b> | ".NFPM_LAN_3.": <b>{TOTAL_VIEWS}</b></span>";


?>