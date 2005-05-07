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
|     $Revision: 1.1 $
|     $Date: 2005-05-07 23:37:18 $
|     $Author: streaky $
+----------------------------------------------------------------------------+
*/

// Slieght fix for sites that need it..

$folder = dirname($_SERVER['PHP_SELF']).'/';
$slashed_foler = str_replace(array("/", "."), array("\\/", "\\."), $folder);

?>
if (navigator.platform == "Win32" && navigator.appName == "Microsoft Internet Explorer" && window.attachEvent) {
	document.writeln('<style type="text/css">img, input.image { visibility:hidden; } </style>');
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
		img.style.visibility = "visible";
	}

	var nl = document.getElementsByTagName("INPUT");
	for (var i = nl.length - 1, e = null; (e = nl[i]); i--) {
		if (e.className && e.className.match(/\bimage\b/i) != null) {
			if (e.src.match(/\.png$/i) != null) {
				fnFixPng(e);
				e.attachEvent("onpropertychange", fnPropertyChanged);
			}
			e.style.visibility = "visible";
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

function dbg(o) {
	var s = "";
	var i = 0;
	for (var p in o) {
		s += p + ": " + o[p] + "\n";
		if (++i % 10 == 0) {
			alert(s);
			s = "";
		}
	}
	alert(s);
}

function fnFixPng(img) {
	var src = img.src;
	img.style.width = img.width + "px";
	img.style.height = img.height + "px";
	img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + src + "', sizingMethod='scale')"
	img.src = "<?php echo $folder; ?>sleight_img.gif";
}