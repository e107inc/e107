<?php

/*
+ ----------------------------------------------------------------------------+
|     e107 website system
|
|     ©Steve Dunstan 2001-2002
|     http://e107.org
|     jalist@e107.org
|
|     Released under the terms and conditions of the
|     GNU General Public License (http://gnu.org).
|
|     $Source: /cvs_backup/e107_0.7/e107_files/sleight_js.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-06-29 21:10:21 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/

// Credit to youngpup.net for sorting the annoyince that is ie for us =)
// modified to ignore ie 7+ and figure out where it is on the server.

// Slieght fix for sites that need it..

$folder = dirname($_SERVER['PHP_SELF']).'/';
$slashed_folder = str_replace(array("/", "."), array("\\/", "\\."), $folder);

header("Content-type: application/x-javascript");

?>
if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent) {
	window.attachEvent("onload", fnLoadPngs);
}

function fnLoadPngs() {
	var rslt = navigator.appVersion.match(/MSIE (\d+\.\d+)/, '');
	var itsAllGood = (rslt != null && Number(rslt[1]) >= 5.5 && Number(rslt[1]) < 7);

	for (var i = document.images.length - 1, img = null; (img = document.images[i]); i--) {
		if (itsAllGood && img.src.match(/\.png$/i) != null) {
			fnFixPng(img);
			img.attachEvent("onpropertychange", fnPropertyChanged);
		}
	}

	var nl = document.getElementsByTagName("input");
	for (var i = nl.length - 1, e = null; (e = nl[i]); i--) {
		if (e.type == 'image') {
			if (e.src.match(/\.png$/i) != null) {
				fnFixPng(e);
				e.attachEvent("onpropertychange", fnPropertyChanged);
			}
		}
	}
}

function fnPropertyChanged() {
	if (window.event.propertyName == "src") {
		var el = window.event.srcElement;
		if (!el.src.match(/<?php echo $slashed_folder; ?>sleight_img\.gif$/i)) {
			el.filters.item(0).src = el.src;
			el.src = "<?php echo $folder; ?>sleight_img.gif";
		}
	}
}

function fnFixPng(img) {
	var src = img.src;
	img.style.width = img.width + "px";
	img.style.height = img.height + "px";
	img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')"
	img.src = "<?php echo $folder; ?>sleight_img.gif";
}