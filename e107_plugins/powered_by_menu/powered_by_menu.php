<?php
$text = "
<div style='text-align:center'>
<a href='http://e107.org' rel='external'><img src='".e_IMAGE."button.png' alt='e107' style='border:0' /></a>
<br />
<a href='http://php.net' rel='external'><img src='".e_IMAGE."generic/php-small-trans-light.gif' alt='PHP' style='border:0' /></a>
<br />
<a href='http://mysql.com' rel='external'><img src='".e_IMAGE."generic/poweredbymysql-88.png' alt='mySQL' style='border:0' /></a>
</div>";
$ns -> tablerender(POWEREDBY_L1,  $text);
?>