<?php
/** Fixture theme: the tpstate1_legacy shape plus a v1-shaped online_menu_template.php at the theme root. */

if(!defined('e107_INIT')) { exit; }

$themename = "TP State 1 Online Template";
$themeversion = "1.0";
$themeauthor = "e107 Inc";

function tablestyle($caption, $text, $mode = '')
{
	echo "<div class='tp-block'><h2 class='tp-caption'>".$caption."</h2><div class='tp-body'>".$text."</div></div>";
}

$HEADER = "<div id='tpstate1-onlinetpl'><div id='tp-menu'>{MENU=2}</div>";
$FOOTER = "</div>";
