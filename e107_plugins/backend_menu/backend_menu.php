<?php
$text = "<div style='text-align:center' class='smalltext'>
".BACKEND_MENU_L1."<br />
<a href='".e_FILE."backend/news.xml'>news.xml</a> - <a href='".e_FILE."backend/news.txt'>news.txt</a>
</div>";
$ns -> tablerender(BACKEND_MENU_L2, $text);
?>