<?php
/** Fixture theme: no theme.xml but a Bootstrap define, so THEME_LEGACY is true while BOOTSTRAP is set. */

if(!defined('e107_INIT')) { exit; }

$themename = "TP State 4 Legacy Bootstrap";
$themeversion = "1.0";
$themeauthor = "e107 Inc";

define('BOOTSTRAP', 3);

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$HEADER = "<div id='tpstate4-legacybs'>";
$FOOTER = "</div>";
