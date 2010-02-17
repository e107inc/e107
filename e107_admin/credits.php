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
|     $Source: /cvs_backup/e107_0.7/e107_admin/credits.php,v $
|     $Revision$
|     $Date$
|     $Author$
+----------------------------------------------------------------------------+
*/

require_once("../class2.php");
$e_sub_cat = 'credits';
include(e_ADMIN.'ver.php');


$creditsArray = array(
	array(	"name" => "MagpieRSS",
				"url" => "http://magpierss.sourceforge.net/",
				"description" => CRELAN_10,
				"version" => "0.71.1",
				"licence" => "GPL, ".CRELAN_8
			),
	array(	"name" => "PCLZip",
				"url" => "http://www.phpconcept.net/pclzip/",
				"description" => CRELAN_11,
				"version" => "2.3",
				"licence" => "GPL"
			),
	array(	"name" => "PCLTar",
				"url" => "http://www.phpconcept.net/pcltar/",
				"description" => CRELAN_12,
				"version" => "1.3",
				"licence" => "GPL"
			),
	array(	"name" => "TinyMCE",
				"url" => "http://tinymce.moxiecode.com/",
				"description" => CRELAN_13,
				"version" => "2.08",
				"licence" => "GPL"
			),
	array(	"name" => "Nuvolo Icons",
				"url" => "http://www.icon-king.com",
				"description" => CRELAN_14,
				"version" => "1.0",
				"licence" => "GPL"
			),
	array(	"name" => "PHPMailer",
				"url" => "http://phpmailer.sourceforge.net/",
				"description" => CRELAN_15,
				"version" => "1.72",
				"licence" => "GPL"
			),
	array(	"name" => "Brainjar DHTML Menu",
				"url" => "http://www.brainjar.com/dhtml/menubar/",
				"description" => CRELAN_16,
				"version" => "0.1",
				"licence" => "GPL, ".CRELAN_8
			),
	array(	"name" => "DHTML / JavaScript Calendar",
				"url" => "http://www.dynarch.com/projects/",
				"description" => CRELAN_17,
				"version" => "1.0",
				"licence" => "GPL"
			),
	array(	"name" => "FPDF",
				"url" => "http://www.fpdf.org/",
				"description" => CRELAN_18,
				"version" => "1.53",
				"licence" => "Freeware"
			),
	array(	"name" => "UFPDF",
				"url" => "http://www.acko.net/node/56",
				"description" => CRELAN_19,
				"version" => "0.1",
				"licence" => "GPL"
			),
	);

$contentA = array(
	"<h3>".CRELAN_6."<\/h3>",
	"<h1>Carl Cedergren<\/h1>[ asperon ]<br /><br /><br />".CRELAN_20,
	"<h1>Cameron Hanly<\/h1>[ CaMer0n ]<br /><br /><br />".CRELAN_21,
	"<h1>Steve Dunstan<\/h1>[ jalist ]<br /><br /><br />".CRELAN_22,
	"<h1>Eric Vanderfeesten<\/h1> [ lisa ]<br /><br /><br />".CRELAN_23,
	"<h1>Thom Michelbrink<\/h1>[ McFly ]<br /><br /><br />".CRELAN_24,
	"<h1>William Moffett<\/h1>[ que ]<br /><br /><br />".CRELAN_25,
	"<h1>Martin Nicholls<\/h1>[ streaky ]<br /><br /><br />".CRELAN_26,
	"<h1>James Currie<\/h1>[ SweetAs ]<br /><br /><br />".CRELAN_27,
	"<h1>Pete Holzmann<\/h1>[ MrPete ]<br /><br /><br />".CRELAN_28
);

echo "<?xml version='1.0' encoding='".CHARSET."' ?><!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>".PAGE_NAME."</title>
<meta http-equiv='content-type' content='text/html; charset=".CHARSET."' />
<meta http-equiv='content-style-type' content='text/css' />
<link rel='stylesheet' href='".THEME."style.css' type='text/css' />
</head>
<body style='padding: 0; margin: 0; background-color: #e8e8e8; color: #8E8E8E'>

<div><img src='".e_IMAGE."generic/cred.png' alt='' />
<div class='smalltext' style='position: absolute; top: 120px; left: 118px;'><b>".CRELAN_7." ".$e107info['e107_version'].", build ".($e107info['e107_build'] ? $e107info['e107_build'] : "zero")."</b><br />&copy; 2002-2008, ".CRELAN_3."</div>

";


$fadejs = "
<script type='text/javascript'>
<!--

var delay = 2000;
var maxsteps=30;
var stepdelay=40;
var startcolor= new Array(255,255,255);
var endcolor=new Array(0,0,0);
var fcontent=new Array();
";

if(e_QUERY && e_QUERY == "stps")
{
	$count=1;
	$fadejs .= "fcontent[0] = '<br /><br />".CRELAN_2."';";
	foreach($creditsArray as $credits)
	{
		extract($credits);
		$fadejs .= "fcontent[$count] = '<br /><br /><h1>$name<\/h1>".CRELAN_7." $version<br /><br />$description<br /><br />[ <a href=\"$url\" rel=\"external\">$url<\/a> ]<br /><br />".CRELAN_9." - $licence';
		";
		$count++;
	}
}
else
{
	$count=0;
	foreach($contentA as $content)
	{
		$fadejs .= "fcontent[$count] = '<br /><br />$content';
		";
		$count++;
	}
}

$fadejs .= <<<TEXT
begintag='';
closetag='';


var fadelinks=1;

var fwidth='95%';
var fheight='220px;'

///No need to edit below this line/////////////////

var ie4=document.all&&!document.getElementById;
var DOM2=document.getElementById;
var faderdelay=0;
var index=0;

/*Rafael Raposo edited function*/
//function to change content
function changecontent(){
  if (index>=fcontent.length)
    index=0
  if (DOM2){
    document.getElementById("fscroller").style.color="rgb("+startcolor[0]+", "+startcolor[1]+", "+startcolor[2]+")"
    document.getElementById("fscroller").innerHTML=begintag+fcontent[index]+closetag
    if (fadelinks)
      linkcolorchange(1);
    colorfade(1, 15);
  }
  else if (ie4)
    document.all.fscroller.innerHTML=begintag+fcontent[index]+closetag;
  index++
}

// colorfade() partially by Marcio Galli for Netscape Communications.  ////////////
// Modified by Dynamicdrive.com

function linkcolorchange(step){
  var obj=document.getElementById("fscroller").getElementsByTagName("A");
  if (obj.length>0){
    for (i=0;i<obj.length;i++)
      obj[i].style.color=getstepcolor(step);
  }
}

/*Rafael Raposo edited function*/
var fadecounter;
function colorfade(step) {
  if(step<=maxsteps) {
    document.getElementById("fscroller").style.color=getstepcolor(step);
    if (fadelinks)
      linkcolorchange(step);
    step++;
    fadecounter=setTimeout("colorfade("+step+")",stepdelay);
  }else{
    clearTimeout(fadecounter);
    document.getElementById("fscroller").style.color="rgb("+endcolor[0]+", "+endcolor[1]+", "+endcolor[2]+")";
    setTimeout("changecontent()", delay);

  }
}

/*Rafael Raposo's new function*/
function getstepcolor(step) {
  var diff
  var newcolor=new Array(3);
  for(var i=0;i<3;i++) {
    diff = (startcolor[i]-endcolor[i]);
    if(diff > 0) {
      newcolor[i] = startcolor[i]-(Math.round((diff/maxsteps))*step);
    } else {
      newcolor[i] = startcolor[i]+(Math.round((Math.abs(diff)/maxsteps))*step);
    }
  }
  return ("rgb(" + newcolor[0] + ", " + newcolor[1] + ", " + newcolor[2] + ")");
}

if (ie4||DOM2)
  document.write('<div id="fscroller" style="text-align: center; width:'+fwidth+';height:'+fheight+'"><\/div>');

if (window.addEventListener)
window.addEventListener("load", changecontent, false)
else if (window.attachEvent)
window.attachEvent("onload", changecontent)
else if (document.getElementById)
window.onload=changecontent;
//-->
</script>

TEXT;

echo $fadejs;

echo "
<div style='text-align: center; margin-left: auto; margin-right: auto;'>
<form method='get' action=''><div>".
(e_QUERY && e_QUERY == "stps" ? "<input class='tbox' type='button' onclick=\"self.parent.location='".e_ADMIN."credits.php'\" value='".CRELAN_4."' />" : "<input class='tbox' type='button' onclick=\"self.parent.location='".e_ADMIN."credits.php?stps'\" value='".CRELAN_5."' />")."</div>
</form>
</div>

</div>
</body>
</html>
";

?>
