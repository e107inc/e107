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
|     $Source: /cvs_backup/e107_0.7/e107_plugins/fader_menu/fader_menu.php,v $
|     $Revision: 1.2 $
|     $Date: 2004-12-13 13:20:43 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
$fader = "

<script type='text/javascript'>

/*
Fading Scroller- By DynamicDrive.com
For full source code, and usage terms, visit http://www.dynamicdrive.com
This notice MUST stay intact for use
*/

var delay=".$menu_pref['fader_delay']." //set delay between message change (in miliseconds)
var fcontent=new Array()
begintag='' //set opening tag, such as font declarations

";

$aj = new textparse;
for($a=1; $a<=10; $a++){
        $var = "fader_message_$a";
        if($menu_pref[$var]){
                $var2 = str_replace("\"", "'", $aj -> tpa($menu_pref[$var]));
                $var2 = str_replace("\r\n", "", $var2);
                $fader .= "fcontent[".($a-1)."] = \"".$var2."\";\n";
        }
}

$fader .= "

closetag=''
var fwidth='100%' //set scroller width
var fheight='".$menu_pref['fader_height']."'
var fadescheme=".$menu_pref['fader_colour']."

";


$fader .= <<< TEXT
var fadelinks=1  //should links inside scroller content also fade like text? 0 for no, 1 for yes.

///No need to edit below this line/////////////////

var hex=(fadescheme==0)? 255 : 0
var startcolor=(fadescheme==0)? "rgb(255,255,255)" : "rgb(0,0,0)"
var endcolor=(fadescheme==0)? "rgb(0,0,0)" : "rgb(255,255,255)"

var ie4=document.all&&!document.getElementById
var ns4=document.layers
var DOM2=document.getElementById
var faderdelay=0
var index=0

if (DOM2)
faderdelay=2000

//function to change content
function changecontent(){
if (index>=fcontent.length)
index=0
if (DOM2){
document.getElementById("fscroller").style.color=startcolor
document.getElementById("fscroller").innerHTML=begintag+fcontent[index]+closetag
linksobj=document.getElementById("fscroller").getElementsByTagName("A")
if (fadelinks)
linkcolorchange(linksobj)
colorfade()
}
else if (ie4)
document.all.fscroller.innerHTML=begintag+fcontent[index]+closetag
else if (ns4){
document.fscrollerns.document.fscrollerns_sub.document.write(begintag+fcontent[index]+closetag)
document.fscrollerns.document.fscrollerns_sub.document.close()
}

index++
setTimeout("changecontent()",delay+faderdelay)
}

// colorfade() partially by Marcio Galli for Netscape Communications.  ////////////
// Modified by Dynamicdrive.com

frame=20;

function linkcolorchange(obj){
if (obj.length>0){
for (i=0;i<obj.length;i++)
obj[i].style.color="rgb("+hex+","+hex+","+hex+")"
}
}

function colorfade() {
// 20 frames fading process
if(frame>0) {
hex=(fadescheme==0)? hex-12 : hex+12 // increase or decrease color value depd on fadescheme
document.getElementById("fscroller").style.color="rgb("+hex+","+hex+","+hex+")"; // Set color value.
if (fadelinks)
linkcolorchange(linksobj)
frame--;
setTimeout("colorfade()",20);
}

else{
document.getElementById("fscroller").style.color=endcolor;
frame=20;
hex=(fadescheme==0)? 255 : 0
}
}

if (ie4||DOM2)
document.write('<div id="fscroller" style="width:'+fwidth+';height:'+fheight+';padding:2px"></div>')

</script>


<ilayer id="fscrollerns" width=&{fwidth}; height=&{fheight};><layer id="fscrollerns_sub" width=&{fwidth}; height=&{fheight}; left=0 top=0></layer></ilayer>

TEXT;

$ns -> tablerender($menu_pref['fader_caption'], $fader, 'fader');
