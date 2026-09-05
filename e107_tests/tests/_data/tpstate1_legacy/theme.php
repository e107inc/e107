<?php
/** Fixture theme: no theme.xml and no Bootstrap declaration, so THEME_LEGACY is true and BOOTSTRAP is undefined. */

if(!defined('e107_INIT')) { exit; }

$themename = "TP State 1 Legacy";
$themeversion = "1.0";
$themeauthor = "e107 Inc";

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$HEADER = "<div id='tpstate1-legacy'><div id='tp-search'>{SEARCH}</div><div id='tp-menu'>{MENU=2}</div>";
$FOOTER = "</div>";
