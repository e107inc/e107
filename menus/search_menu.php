<?php
$text =  "<div style=\"text-align:center\">
<form method=\"post\" action=\"search.php\">
<input class=\"tbox\" type=\"text\" name=\"searchquery\" size=\"20\" value=\"\" maxlength=\"50\" />
<input class=\"button\" type=\"submit\" name=\"searchsubmit\" value=\"".LAN_180."\" />
</form>
</div>";

$ns -> tablerender(LAN_180." ".SITENAME, $text);
?>