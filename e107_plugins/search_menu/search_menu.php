<?php
@include(e_PLUGIN."search_menu/languages/".e_LANGUAGE.".php");
$text = "<form method='post' action='".e_BASE."search.php'>
<p>
<input class='tbox' type='text' name='searchquery' size='20' value='' maxlength='50' />
<input class='button' type='submit' name='searchsubmit' value='".LAN_180."' />
</p>
</form>";
if($searchflat){ echo $text; }else{ $ns -> tablerender(LAN_180." ".SITENAME, "<div style='text-align:center'>".$text."</div>"); }
?>