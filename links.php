<?php
require_once('class2.php');
header('Location:'.SITEURL.$PLUGINS_DIRECTORY.'links_page/links.php'.(e_QUERY ? '?'.e_QUERY : ''));
exit;
?>