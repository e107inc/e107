<?php

$_E107['minimal'] = true;
require_once("../../../class2.php");
ob_start();
ob_implicit_flush(0);
header("last-modified: " . gmdate("D, d M Y H:i:s",mktime(0,0,0,15,2,2004)) . " GMT");
header('Content-type: text/javascript');

$tp = e107::getParser();
$json = $tp->toJSON(e107::getJs()->getSettings());

$js = '$(document).ready(function() {';
$js .= "var e107 = e107 || {'settings': {}, 'behaviors': {}};\n";
$js .= "jQuery.extend(e107.settings, " . $json . ");\n";
$js .= '});';

header ('ETag: "' . md5($js).'"' );
echo $js;
echo_gzipped_page();