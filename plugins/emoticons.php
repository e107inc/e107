<?
/*
+---------------------------------------------------------------------------------------+

usage example:
include("emoticons.php");
$msg = "Emoticons are cool (h)";												
$msg = emoticons($msg);														
echo $msg;
full list of how to use @
http://help.microsoft.com/EN_US/HelpWindow_msg.asp?INI=msgr_xpv46.ini&H_VER=1.7&Topic=emoticons.htm&H_APP=Windows%20Messenger&Filter=xp

alphabetised by image name added help link (9.8.02)

+---------------------------------------------------------------------------------------+
*/

function emoticons($msg) {


$emoticons = array(

// 43 smilies August 2002

//"\(a\)" => "angel_smile",
":@" => "owned",
//":\[" => "bat",
//"\(b\)" => "beer_yum",
//"\(&\)" => "bowwow",
//"\(u\)" => "broken_heart",
//"\(\^\)" => "cake",
//"\(p\)" => "camera",
//"\(o\)" => "clock",
//"\(c\)" => "coffee",
":s" => "smirk",
//":'\(" => "cry_smile",
//"\(6\)" => "devil_smile",
//"\(\{\)" => "dude_hug",
":\\$" => "smirk",
//"\(e\)" => "envelope",
//"\(~\)" => "film",
//"\(x\)" => "girl_handsacrossamerica",
//"\(\}\)" => "girl_hug",
//"\(z\)" => "guy_handsacrossamerica",
"\(l\)" => "heart",
//"\(k\)" => "kiss",
//"\(@\)" => "kittykay",
//"\(i\)" => "lightbulb",
//"\(d\)" => "martini_shaken",
//"\(m\)" => "messenger", 
//"\(s\)" => "moon",
//"\(8\)" => "musical_note",
":o" => "omg",
//"\(t\)" => "phone",
//"\(g\)" => "present",
":\)" => "smirk",
//"\(f\)" => "rose",
":\(" => "sad",
"\(h\)" => "dead",
"\(\*\)" => "square",
":D" => "bigsmile",
//"\(n\)" => "thumbs_down",
//"\(y\)" => "thumbs_up",
":p" => "tongue",
":\|" => "confused",
//"\(w\)" => "wilted_rose",
";)" => "wink",
"\:O" => "omg"
);

while (list($short, $name) = each ($emoticons)){
	if(eregi("admin", $_SERVER['PHP_SELF'])){
		$msg = ereg_replace("$short","<img src=\"../themes/shared/emoticons/$name.gif\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
		$msg = ereg_replace(strtoupper($short),"<img src=\"../themes/shared/emoticons/$name.gif\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
	}else{
		$msg = ereg_replace("$short","<img src=\"themes/shared/emoticons/$name.gif\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
		$msg = ereg_replace(strtoupper($short),"<img src=\"themes/shared/emoticons/$name.gif\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
	}
}
return $msg;
}
?>