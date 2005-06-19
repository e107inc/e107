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
|     $Revision: 1.6 $
|     $Date: 2005-06-19 09:04:28 $
|     $Author: stevedunstan $
+----------------------------------------------------------------------------+
*/
require_once("../class2.php");
$e_sub_cat = 'credits';


$creditsArray = array(
	array(	"name" => "MagpieRSS", 
				"url" => "http://magpierss.sourceforge.net/", 
				"description" => "MagpieRSS provides an XML-based (expat) RSS parser in PHP.", 
				"version" => "0.71.1", 
				"licence" => "GPL, permission granted"
			),
	array(	"name" => "PCLZip", 
				"url" => "http://www.phpconcept.net/pclzip/", 
				"description" => "PclZip library offers compression and extraction functions for Zip formatted archives (WinZip, PKZIP).", 
				"version" => "2.3", 
				"licence" => "GPL"
			),
	array(	"name" => "PCLTar", 
				"url" => "http://www.phpconcept.net/pcltar/", 
				"description" => "PclTar offer the ability to archive a list of files or directories with or without compression. The archives created by PclTar are readeable by most of gzip/tar applications and by the Windows WinZip application.", 
				"version" => "1.3", 
				"licence" => "GPL"
			),
	array(	"name" => "TinyMCE", 
				"url" => "http://tinymce.moxiecode.com/", 
				"description" => "TinyMCE is a platform independent web based Javascript HTML WYSIWYG editor control released as Open Source under LGPL by Moxiecode Systems AB. It has the ability to convert HTML TEXTAREA fields or other HTML elements to editor instances.", 
				"version" => "1.42", 
				"licence" => "GPL"
			),
	array(	"name" => "Nuvolo Icons", 
				"url" => "http://www.icon-king.com", 
				"description" => "Icons used in e107", 
				"version" => "1.0", 
				"licence" => "GPL"
			),
	array(	"name" => "PHPMailer", 
				"url" => "http://phpmailer.sourceforge.net/", 
				"description" => "Full featured email transfer class for PHP", 
				"version" => "1.72", 
				"licence" => "GPL"
			),
	array(	"name" => "Brainjar DHTML Menu", 
				"url" => "http://www.brainjar.com/dhtml/menubar/", 
				"description" => "Menu system used in Jayya theme", 
				"version" => "0.1", 
				"licence" => "GPL, permission granted"
			),
	array(	"name" => "DHTML / JavaScript Calendar", 
				"url" => "http://www.dynarch.com/projects/", 
				"description" => "Popup calendar widget", 
				"version" => "1.0", 
				"licence" => "GPL"
			),
	);

$contentA = array(
	"<h3>e107 v0.7 was brought to you by ...</h3>", 
	"<h1>Carl Cedergren</h1>[ asperon ]<br /><br /><br />\"message goes here\"",
	"<h1>Cameron Hanly</h1>[ cameron ]<br /><br /><br />\"message goes here\"", 
	"<h1>Steve Dunstan</h1>[ jalist ]<br /><br /><br />\"message goes here\"", 
	"<h1>Eric Vanderfeesten</h1> [ lisa ]<br /><br /><br />\"message goes here\"", 
	"<h1>Thom Michelbrink</h1>[ McFly ]<br /><br /><br />\"message goes here\"", 
	"<h1>William Moffett</h1>[ que ]<br /><br /><br />\"message goes here\"", 
	"<h1>Martin Nicholls</h1>[ streaky ]<br /><br /><br />\"message goes here\"", 
	"<h1>SweetAs</h1>[ SweetAs ]<br /><br /><br />\"message goes here\""
);


$sql -> db_Select("core", "*", "e107_name='e107' ");
$foo = $sql -> db_Fetch();
$e107v = unserialize($foo['e107_value']);


echo "<?xml version='1.0' encoding='utf-8' ?><!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<title>e107 Credits</title>
<meta http-equiv='content-type' content='text/html; charset=utf-8' />
<meta http-equiv='content-style-type' content='text/css' />
<link rel='stylesheet' href='".e_THEME."lamb/style.css' type='text/css' />
</head>
<body style='padding: 0; margin: 0; background-color: #e8e8e8; color: #8E8E8E'>



<img src='".e_IMAGE."generic/cred.png' alt='' />
<div class='smalltext' style='position: absolute; top: 110px; left: 130px;'><b>version ".$e107v['e107_version'].", build ".($e107v['e107_build'] ? $e107v['e107_build'] : "zero")."</b><br />&copy; 2002-2005, all rights reserved</div>

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
		$fadejs .= "fcontent[$count] = '<br /><br /><h1>$name</h1>version $version<br /><br />$description<br /><br />[ <a href=\"$url\" rel=\"external\">$url</a> ]';
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
  document.write('<div id="fscroller" style="text-align: center; width:'+fwidth+';height:'+fheight+'"></div>');

if (window.addEventListener)
window.addEventListener("load", changecontent, false)
else if (window.attachEvent)
window.attachEvent("onload", changecontent)
else if (document.getElementById)
window.onload=changecontent
</script>
<ilayer id="fscrollerns" width=&{fwidth}; height=&{fheight};><layer id="fscrollerns_sub" width=&{fwidth}; height=&{fheight}; left=0 top=0></layer></ilayer>

TEXT;



echo $fadejs;


echo "
<div style='text-align: center; margin-left: auto; margin-right: auto;'>
<form>".
(e_QUERY && e_QUERY == "stps" ? "<input class='tbox' type='button' onclick=\"self.parent.location='".e_ADMIN."credits.php'\" value='Show e107 Dev Team'>" : "<input class='tbox' type='button' onclick=\"self.parent.location='".e_ADMIN."credits.php?stps'\" value='Show third party scripts'>")."
</form>
</div>
";

?>



