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
"&\|" => "cry", 
"&-\|" => "cry", 
"&o\|" => "cry", 
":\(\(" => "cry", 

"~:\(" => "mad", 
"~:o\(" => "mad",
"~:-\(" => "mad",

":\)" => "smile", 
":o\)" => "smile",
":-\)" => "smile",

":\(" => "frown", 
":o\(" => "frown", 
":-\(" => "frown", 

":D" => "grin", 
":oD" => "grin", 
":-D" => "grin", 

":\?" => "confused", 
":o\?" => "confused", 
":-\?" => "confused", 

"\%-6" => "special", 

"x\)" => "dead", 
"xo\)" => "dead", 
"x-\)" => "dead", 
"x\(" => "dead", 
"xo\(" => "dead", 
"x-\(" => "dead", 

":@" => "gah", 
":o@" => "gah", 
":-@" => "gah", 

":!" => "idea", 
":o!" => "idea", 
":-!" => "idea", 

":\|" => "neutral", 
":o\|" => "neutral", 
":-\|" => "neutral", 

"\?!" => "question", 

"B\)" => "rolleyes", 
"Bo\)" => "rolleyes", 
"B-\)" => "rolleyes", 

"8\)" => "shades", 
"8o\)" => "shades", 
"8-\)" => "shades", 

":O" => "suprised", 
":oO" => "suprised", 
":-O" => "suprised", 

":p" => "tongue", 
":op" => "tongue", 
":-p" => "tongue", 
":P" => "tongue", 
":oP" => "tongue", 
":-P" => "tongue", 

";\)" => "wink", 
";o\)" => "wink", 
";-\)" => "wink"

);

while (list($short, $name) = each ($emoticons)){
//	echo $short."<br />";
	if(eregi("admin", $_SERVER['PHP_SELF'])){
		$msg = ereg_replace(strtoupper($short),"<img src=\"../themes/shared/emoticons/$name.png\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
//		$msg = ereg_replace(strtoupper($short),"<img src=\"../themes/shared/emoticons/$name.png\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
	}else{
		$msg = ereg_replace(strtoupper($short),"<img src=\"themes/shared/emoticons/$name.png\" alt=\"\" style=\"vertical-align:absmiddle\" />", $msg);
//		$msg = ereg_replace(strtoupper($short),"<img src=\"themes/shared/emoticons/$name.png\" alt=\"\" style=\"vertical-align:absmiddle\" />",$msg);
	}
}
return $msg;
}
?>