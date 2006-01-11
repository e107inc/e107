<?php

if (!defined('e107_INIT')) { exit; }

$text = "Activeer uw site statistiek logging op dit scherm. Als u weinig schijfruimte op uw server hebt, kruis dan Alleen domain aan als Refer logtype, hiermee wordt alleen het domein gelogd, en niet de hele url-tekst, bijv. 'e107.org' in plaats van 'http://e107.org/links.php' ";
$ns -> tablerender("Logging Hulp", $text);
?>