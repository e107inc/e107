<?php

if (!defined('e107_INIT')) { exit; }
include_once(e_HANDLER.'shortcode_handler.php');
$news_shortcodes = $tp->e_sc->parse_scbatch(__FILE__);
/*
NEWS_POSTS
return $news_posts;
*/