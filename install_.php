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
|     $Source: /cvs_backup/e107_0.7/install_.php,v $
|     $Revision: 1.8 $
|     $Date: 2005-01-23 14:31:12 $
|     $Author: sweetas $
+----------------------------------------------------------------------------+
*/
@include("e107_handlers/errorhandler_class.php");
set_error_handler("error_handler");
error_reporting(E_ERROR | E_WARNING | E_PARSE);

if(isset($_POST['frontpage'])){ header("location: index.php"); exit;}
if(isset($_POST['adminpage'])){ header("location: admin/admin.php"); exit;}
if(!$_POST['mysql_server']){ $_POST['mysql_server'] = "localhost"; }
if(!$_POST['mysql_prefix'] && !$_POST['stage_2']){ $_POST['mysql_prefix'] = "e107_"; }
if(!$_POST['admin_email']){ $_POST['admin_email'] = "you@yoursite.com"; }

if(!$_POST['installlanguage']){ $_POST['installlanguage'] = "English"; }
@include("e107_languages/".$_POST['installlanguage']."/lan_install.php");


echo "<?xml version='1.0' encoding='iso-8859-1' ?>\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>[ e107 installing ]</title>
<link rel="stylesheet" href="e107_install/style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="content-style-type" content="text/css" />
<?php
echo "\n
<script type='text/javascript'>
<!--
function preloadimages(nbrpic,pic){
     myimages=new Image();
     myimages.src=pic;
}
var listpics = new Array();
";
$handle=opendir("e107_install/images");
$nbrpic=0;
while ($file = readdir($handle)){
        if(strstr($file,".") && $file != "." && $file != ".."){
                $imagelist[] = $file;
                echo "listpics[".$nbrpic."]='e107_install/images/".$file."';";
                $nbrpic++;
        }
}

closedir($handle);
echo "\nfor(i=0;i<(".$nbrpic."-1);i++){ preloadimages(i,listpics[i]); }
// -->
</script>
</head>
<body>";


if(!$_POST['stage']){
        stage1();
}else if($_POST['stage'] == 2){
        stage2();
}else if($_POST['stage'] == 3){
        stage3();
}else if($_POST['stage'] == 4){
        stage4();
}else if($_POST['stage'] == 5){
        stage5();
}else if($_POST['stage'] == 6){
        stage6();
}else if($_POST['stage'] == 7){
        stage7();
}


function stage1(){
        $text = ren_header("<div class='installcaplarge'>Installation Stage 1&nbsp;&nbsp;</div>\n<div class='installcapsmall'>Choose language&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\nPlease choose language to use during installation procedure ...<br /><br />\n<select name='installlanguage' class='tbox'>\n";
        $counter = 0;
        $sellan = "English";
        $lanlist = get_lan();
        while(isset($lanlist[$counter])){
                if($lanlist[$counter] == $sellan){
                        $text .= "<option selected>".$lanlist[$counter]."</option>\n";
                }else{
                        $text .= "<option>".$lanlist[$counter]."</option>\n";
                }
                $counter++;
        }
        $text .= "</select> \n<input class='button' type='submit' name='setlanguage' value='Set Language' />\n</td>\n</tr>\n</table>\n<input type='hidden' name='stage' value='2'>\n</form>\n<br /><br />
        ";

        tablestyle(INSLAN14." ...", $text);

        echo "\n</td></tr></table></div>";
}


function stage2(){
        $text = ren_header("<div class='installcaplarge'>".INSLAN1." 2&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN2."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
        <table class='fborder' style='width:80%'>\n<tr>
        <td style='width:33%' class='installbox1'><b>".INSLAN3."</b></td>
        <td style='width:33%' class='installboxgeneric'>".phpversion()."</td>";
        $verreq = str_replace(".","", "4.0.6");
        $server = str_replace(".","", phpversion());
                if(strlen($server) < 3) {$server = $server."0";}
        if($server <= $verreq){
                $error[0] = TRUE;
                $text .= "<td style='width:33%' class='installboxfail'>* ".INSLAN4." *</td>
                </tr>
                <tr>
                <td colspan='3' class='installboxfail'><br />";

                $text .= "<b>".INSLAN5."<br />(".INSLAN65.").</b><br /><br />".INSLAN66."<br />".INSLAN67." <a href='http://php.net'>php.net</a> ".INSLAN68."<br />".INSLAN69."<br />".INSLAN70."<br />".INSLAN71;
                $text .= "<br /><br />".INSLAN6."<br /><br /></td></tr></table></td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
                exit;
        }else{
                $text .= "<td style='width:33%' class='installboxpass'>* ".INSLAN7." *</td>
                </tr>";
        }

        $text .= "<tr><td style='width:33%' class='installbox1'><b>".INSLAN8."</b></td>
        <td style='width:33%' class='installboxgeneric'>".(@mysql_get_server_info() ? @mysql_get_server_info() : "&nbsp;")."</td>";

        if(!mysql_get_server_info()){
                $error[1] = TRUE;
                $text .= "<td style='width:33%' class='installboxfail'>* Fail *</td>
                </tr>
                <tr>
                <td colspan='3' class='installboxgeneric'><br />";

                $text .= "<b>".INSLAN9."</b><br /><br /> ".INSLAN72."<br />".INSLAN73."<br />".INSLAN74."<br />".INSLAN75;
                $text .= "<br /><br /></td></tr>";
        }else{
                $text .= "<td style='width:33%; text-align:center' class='installboxpass'>* ".INSLAN7." *</td>
                </tr>";
        }

        $text .= "<tr><td style='width:33%' class='installbox1'><b>".INSLAN10."</b></td>
        <td style='width:33%' class='installbox1'>&nbsp;</td>";

        if(!is_writable("e107_config.php")){
                $error[2] = TRUE;
                $errorstr .= "<b>e107_config.php</b> ".INSLAN11.".</b> ";
        }

        if(!is_writable("e107_files/cache/")){
                $error[3] = TRUE;
                $errorstr .= "<b>e107_files/cache/</b> ".INSLAN11.".</b> ";
        }

        if(!is_writable("e107_files/backend/news.txt") || !is_writable("e107_files/backend/news.xml")){
                $error[4] = TRUE;
                $errorstr .= "<b>e107_files/backend/news.txt</b> ".INSLAN62." <b>e107_files/backend/news.xml</b> ".INSLAN11.".</b> ";
        }

        if(!is_writable("e107_files/public/") || !is_writable("e107_files/public/avatars/")){
                $error[5] = TRUE;
                $errorstr .= "<b>e107_files/public/</b> ".INSLAN61." ".INSLAN62." <b>e107_files/public/avatars/</b> ".INSLAN12.".</b> ";
        }

        if(!is_writable("e107_plugins/custom/") || !is_writable("e107_plugins/custompages/")){
                $error[6] = TRUE;
                $errorstr .= "<br /><b>e107_plugins/custom/</b> ".INSLAN61." ".INSLAN62." <b>e107_plugins/custompages/</b> ".INSLAN61." ".INSLAN12.".</b> ";
        }

        if($error[2] || $error[3] || $error[4] || $error[5] || $error[6]){
                $text .= "<td style='width:33%' class='installboxfail'>* ".INSLAN4." *</td>
                </tr>
                <tr>
                <td colspan='3' class='installboxgeneric'>
                <br /><b>".INSLAN63.":</b> $errorstr<br /><br /> ".INSLAN13."<br /><br /></td></tr>";
        }else{
                $text .= "<td style='width:33%; text-align:center' class='installboxpass'>* ".INSLAN7." *</td>
                </tr>";
        }

        $text .= "</td></tr></table>
        <br /><br />";

        if($error[0]){
                $text .= "<div class='installfail'>Script Halted.</div></td></tr></table></div>";
                tablestyle(INSLAN14." ... ".INSLAN15, $text);
                echo "\n\n</body>\n</html>";
                exit;

        }else if($error[2] || $error[3] || $error[4] || $error[5] || $error[6]){
                $text .= "
                <input class='button' type='submit' name='retest' value='".INSLAN18."' />
                <input type='hidden' name='stage' value='2'><input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'><br /><br />";
        }else if($error[1]){
                $text .= "<div class='installfail'>".INSLAN16."</div><br /><br /><input class='button' type='submit' name='submit' value='".INSLAN17."' />
                <input type='hidden' name='stage' value='3'><input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'><br /><br />";
        }else{
                $text .= INSLAN19.".<br /><br />
                <input class='button' type='submit' name='submit' value='".INSLAN17."' />
                <input type='hidden' name='stage' value='3'><input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'><br /><br />";
        }

        $text .= "</td></tr></table></div>";
        tablestyle(INSLAN14." ...", $text);

}


function stage3(){
        $text = ren_header("<div class='installcaplarge'>".INSLAN1." 3&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN20."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
        ".INSLAN21."<br /><br />
        <table class='fborder' style='width:80%'>\n<tr>

        <td style='width:40%' class='installbox1'><b>".INSLAN22.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='text' name='mysql_server' size='60' value='".$_POST['mysql_server']."' maxlength='100' /></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN23.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='text' name='mysql_name' size='60' value='".$_POST['mysql_name']."' maxlength='100' /></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN24.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='password' name='mysql_password' size='60' value='".$_POST['mysql_password']."' maxlength='100' /></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN25.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='text' name='mysql_db' size='42' value='".$_POST['mysql_db']."' maxlength='100' />
        <input type='checkbox' name='createdbconfirm' value='1'><span class='defaulttext'>".INSLAN60."</span></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN26.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='text' name='mysql_prefix' size='60' value='".$_POST['mysql_prefix']."'  maxlength='100' /></td></tr></table>

        <br />

        <input class='button' type='submit' name='submit' value='".INSLAN17."' /><br /><br />
        <input type='hidden' name='stage' value='4'><input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'>";

        $text .= "</td></tr></table></div>";
        tablestyle(INSLAN14." ...", $text);
}


function stage4(){
        if($_POST['mysql_server'] == "" || $_POST['mysql_name'] == "" || $_POST['mysql_db'] == ""){
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 3 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".INSLAN29.".<br /><br />
                ".stagehd(3)."
                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
        }else{
                if(!@mysql_connect($_POST['mysql_server'], $_POST['mysql_name'], $_POST['mysql_password'])){
                        $text = ren_header("<div class='installcaplarge'>".INSLAN1." 3 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                        ".INSLAN30."<br /><br />
                        ".stagehd(3)."
                        </td></tr></table></div>";
                        tablestyle(INSLAN14." ...", $text);
                }else{
                        $text = ren_header("<div class='installcaplarge'>".INSLAN1." 3&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN31."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                        ".INSLAN32."<br />";
                        if($_POST['createdbconfirm']){
                                $text .= "".INSLAN33." <b>".$_POST['mysql_db']."</b> ... ";
                                if(!mysql_query("CREATE DATABASE ".$_POST['mysql_db'])){
                                        $text .= INSLAN34."<br /><br />
                                        ".stagehd(3)."
                                        </td></tr></table></div>";
                                        tablestyle(INSLAN14." ...", $text);
                                        exit;
                                }else{
                                        $text .= INSLAN35."<br />";
                                }
                        }
                        $text .= INSLAN36."<br /><br />
                        <input class='button' type='submit' name='submit' value='".INSLAN17."' /><br /><br />
                        <input type='hidden' name='stage' value='5'>
                        <input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'>
                        <input type='hidden' name='mysql_server' value='".$_POST['mysql_server'] ."'>
                        <input type='hidden' name='mysql_name' value='".$_POST['mysql_name'] ."'>
                        <input type='hidden' name='mysql_db' value='".$_POST['mysql_db'] ."'>
                        <input type='hidden' name='mysql_password' value='".$_POST['mysql_password'] ."'>
                        <input type='hidden' name='mysql_prefix' value='".$_POST['mysql_prefix'] ."'>

                        </td></tr></table></div>";
                        tablestyle(INSLAN14." ...", $text);
                }
        }
}

function stagehd($stage){
        return "<input class='button' type='submit' name='submit' value='".INSLAN37."' />
        <input type='hidden' name='stage' value='$stage'>
        <input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'>
        <input type='hidden' name='mysql_server' value='".$_POST['mysql_server'] ."'>
        <input type='hidden' name='mysql_name' value='".$_POST['mysql_name'] ."'>
        <input type='hidden' name='mysql_db' value='".$_POST['mysql_db'] ."'>
        <input type='hidden' name='createdbconfirm' value='".$_POST['createdbconfirm'] ."'>
        <input type='hidden' name='mysql_password' value='".$_POST['mysql_password'] ."'>
        <input type='hidden' name='mysql_prefix' value='".$_POST['mysql_prefix'] ."'>
        <input type='hidden' name='admin_name' value='".$_POST['admin_name'] ."'>
        <input type='hidden' name='admin_password1' value='".$_POST['admin_password1'] ."'>
        <input type='hidden' name='admin_email' value='".$_POST['admin_email'] ."'>
        ";
}

function stage5(){

        $text = ren_header("<div class='installcaplarge'>".INSLAN1." 4&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN38."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
        ".INSLAN39."<br /><br />
        <table class='fborder' style='width:80%'>\n<tr>

        <td style='width:40%' class='installbox1'><b>".INSLAN40.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='text' name='admin_name' size='60' value='".$_POST['admin_name']."' maxlength='100' /></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN41.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='password' name='admin_password1' size='60' value='' maxlength='100' /></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN42.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='password' name='admin_password2' size='60' value='' maxlength='100' /></td></tr>

        <tr><td style='width:40%' class='installbox1'><b>".INSLAN43.":</b></td>
        <td style='width:60%' class='installbox1'><input class='tbox' type='text' name='admin_email' size='60' value='".$_POST['admin_email']."' maxlength='100' />
        </td></tr></table>

        <br />

        <input class='button' type='submit' name='submit' value='".INSLAN17."' /><br /><br />
        <input type='hidden' name='stage' value='6'>
        <input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'>
        <input type='hidden' name='mysql_server' value='".$_POST['mysql_server'] ."'>
        <input type='hidden' name='mysql_name' value='".$_POST['mysql_name'] ."'>
        <input type='hidden' name='mysql_db' value='".$_POST['mysql_db'] ."'>
        <input type='hidden' name='createdbconfirm' value='".$_POST['createdbconfirm'] ."'>
        <input type='hidden' name='mysql_password' value='".$_POST['mysql_password'] ."'>
        <input type='hidden' name='mysql_prefix' value='".$_POST['mysql_prefix'] ."'>

        </td></tr></table></div>";
        tablestyle(INSLAN14." ...", $text);
}


function stage6(){
        if($_POST['admin_name'] == "" || $_POST['admin_password1'] == "" || $_POST['admin_password2'] == "" || $_POST['admin_email'] == ""){
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 4 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".INSLAN44."<br /><br />
                ".stagehd(5)."
                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
        }else if($_POST['admin_password1'] != $_POST['admin_password2']){
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 4 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".INSLAN45."<br /><br />
                ".stagehd(5)."
                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
        }else if(!preg_match('/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`{|}~]+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $_POST['admin_email'])){
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 4 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".$_POST['admin_email']." ".INSLAN46."<br /><br />
                ".stagehd(5)."
                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
        }else{
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 5&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN47."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".INSLAN48."<br /><br />
                <input class='button' type='submit' name='submit' value='".INSLAN17."' /><br /><br />
                <input type='hidden' name='stage' value='7'>
                <input type='hidden' name='installlanguage' value='".$_POST['installlanguage']."'>
                <input type='hidden' name='mysql_server' value='".$_POST['mysql_server'] ."'>
                <input type='hidden' name='mysql_name' value='".$_POST['mysql_name'] ."'>
                <input type='hidden' name='mysql_db' value='".$_POST['mysql_db'] ."'>
                <input type='hidden' name='createdbconfirm' value='".$_POST['createdbconfirm'] ."'>
                <input type='hidden' name='mysql_password' value='".$_POST['mysql_password'] ."'>
                <input type='hidden' name='mysql_prefix' value='".$_POST['mysql_prefix'] ."'>
                <input type='hidden' name='admin_name' value='".$_POST['admin_name'] ."'>
                <input type='hidden' name='admin_password1' value='".$_POST['admin_password1'] ."'>
                <input type='hidden' name='admin_email' value='".$_POST['admin_email'] ."'>

                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
        }
}

function stage7(){

        $fpath = str_replace(strrchr($_SERVER['PHP_SELF'], "/"), "", $_SERVER['PHP_SELF'])."/";
        $data = chr(60)."?php\n".
chr(47)."*\n+---------------------------------------------------------------+\n|        e107 website system\n|        /e107_config.php\n|\n|        ©Steve Dunstan 2001-2002\n|        http://e107.org\n|        jalist@e107.org\n|\n|        Released under the terms and conditions of the\n|        GNU General Public License (http://gnu.org).\n+---------------------------------------------------------------+\n\n".INSLAN64."\n\n*".
chr(47)."\n\n".
chr(36)."mySQLserver = ".chr(34).$_POST['mysql_server'].chr(34).";\n".
chr(36)."mySQLuser = ".chr(34).$_POST['mysql_name'].chr(34).";\n".
chr(36)."mySQLpassword = ".chr(34).$_POST['mysql_password'].chr(34).";\n".
chr(36)."mySQLdefaultdb = ".chr(34).$_POST['mysql_db'].chr(34).";\n".
chr(36)."mySQLprefix = ".chr(34).$_POST['mysql_prefix'].chr(34).";\n\n".

chr(36)."ADMIN_DIRECTORY = ".chr(34)."e107_admin/".chr(34).";\n".
chr(36)."FILES_DIRECTORY = ".chr(34)."e107_files/".chr(34).";\n".
chr(36)."IMAGES_DIRECTORY = ".chr(34)."e107_images/".chr(34).";\n".
chr(36)."THEMES_DIRECTORY = ".chr(34)."e107_themes/".chr(34).";\n".
chr(36)."PLUGINS_DIRECTORY = ".chr(34)."e107_plugins/".chr(34).";\n".
chr(36)."HANDLERS_DIRECTORY = ".chr(34)."e107_handlers/".chr(34).";\n".
chr(36)."LANGUAGES_DIRECTORY = ".chr(34)."e107_languages/".chr(34).";\n".
chr(36)."HELP_DIRECTORY = ".chr(34)."e107_docs/help/".chr(34).";\n".
chr(36)."DOWNLOADS_DIRECTORY =  ".chr(34)."e107_files/downloads/".chr(34).";\n".
"// ".chr(36)."DOWNLOADS_DIRECTORY =  ".chr(34)."<fullpath>/downloads/".chr(34).";\n".
"// eg. ".chr(36)."DOWNLOADS_DIRECTORY =  ".chr(34)."/home/downloads/".chr(34).";\n\n".
"define(".chr(34)."e_HTTP".chr(34).", ".chr(34).$fpath.chr(34).");\n\n?".

chr(62);

        $fp = @fopen("e107_config.php","w");
        if(!@fwrite($fp, $data)){
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 5 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".INSLAN49." (chmod 777).<br /><br />
                ".stagehd(7)."
                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
                echo "\n\n</form></body>\n</html>";
                exit;
        }

        $error = create_tables();
        if($error){
                $text = ren_header("<div class='installcaplarge'>".INSLAN1." 5 : ".INSLAN27."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN28."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                $error<br /><br />
                ".stagehd(7)."
                </td></tr></table></div>";
                tablestyle(INSLAN14." ...", $text);
                echo "\n\n</form></body>\n</html>";
                exit;
        }else{
                $text = ren_header("<div class='installcaplarge'>".INSLAN50."&nbsp;&nbsp;</div>\n<div class='installcapsmall'>".INSLAN51."&nbsp;&nbsp;&nbsp;&nbsp;</div>")."\n<tr>\n<td colspan='2' style='text-align:center'>\n<br /><br />\n
                ".INSLAN52.".<br /><br />
                <input class='button' type='submit' name='frontpage' value='".INSLAN53."' />
                </td></tr></table></div>";
                tablestyle("e107 installing ...", $text);

        }

}


function create_tables(){

        @mysql_connect($_POST['mysql_server'], $_POST['mysql_name'], $_POST['mysql_password']);
        @mysql_select_db($_POST['mysql_db']);
        $mySQLprefix = $_POST['mysql_prefix'];


        $filename = "e107_admin/sql/core_sql.php";
        @$fd = fopen ($filename, "r");
        $sql_data = @fread($fd, filesize($filename));
        @fclose ($fd);

        if(!$sql_data){
                return INSLAN54."<br /><br />";
        }

        preg_match_all( "/create(.*?)myisam;/si", $sql_data, $result );

        foreach ($result[0] as $sql_table){
                preg_match("/CREATE TABLE\s(.*?)\s\(/si", $sql_table, $match);
                $tablename = $match[1];
                preg_match_all( "/create(.*?)myisam;/si", $sql_data, $result );
                $sql_table = preg_replace("/create table\s/si", "CREATE TABLE ".$mySQLprefix, $sql_table);
                if(!mysql_query($sql_table)){        return INSLAN55; }
        }

        $welcome_message = "<b>".INSLAN56."</b><br /><br />";
        $welcome_message .= INSLAN57. "<br />".INSLAN76." <a href='e107_admin/admin.php'>".INSLAN77."</a>, ".INSLAN78;
        $welcome_message .= "\n\n[b]Support[/b]\ne107 Homepage: http://e107.org, ".INSLAN58."\nForums: http://e107.org/forum.php\n\n[b]Downloads[/b]\nPlugins: http://e107coders.org\nThemes: http://e107themes.org\n<br /><br />".INSLAN59."";

        $search = array("'", "'");
        $replace = array("&quot;", "&#39;");
        $welcome_message = str_replace($search, $replace, $welcome_message);
        $datestamp = time();

        mysql_query("INSERT INTO ".$mySQLprefix."content VALUES (0, '$article_heading', '$article_subheading', '$article', '$datestamp', 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."news VALUES (0, 'Welcome to e107', '$welcome_message', '', '$datestamp', '0', '1', 1, 0, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."news_category VALUES (0, 'Misc', 'icon5.png') ");
        mysql_query("INSERT INTO ".$mySQLprefix."poll VALUES (0, '$datestamp', 0, 1, 'So what do you think of e107?', 'I&#39;m not impressed', 'It&#39;s not bad but I&#39;ve seen better', 'It&#39;s good', 'I love it!', 'Grah I hate polls', 'What&#39;s e107 anyway?', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, '', 1) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Home', 'index.php', '', 'main_16.png', 1, 1, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Forum', 'forum.php', '', 'forums_16.png', 1, 2, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Downloads', 'download.php', '', 'downloads_16.png', 1, 3, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Members', 'user.php', '', 'users_16.png', 1, 4, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Submit News', 'submitnews.php', '', 'submitnews_16.png', 1, 5, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Submit Article', 'subcontent.php?article', '', 'articles_16.png', 1, 6, 0, 0, 255) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Submit Review', 'subcontent.php?review', '', 'reviews_16.png', 1, 7, 0, 0, 255) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Stats', 'stats.php', '', 'stats_16.png', 1, 8, 0, 0, 0) ");
        mysql_query("INSERT INTO ".$mySQLprefix."links VALUES (0, 'Site Map', 'sitemap.php', '', 'sitemap_16.png', 1, 9, 0, 0, 0) ");


        $e107['e107_author'] = "Steve Dunstan (jalist)";
        $e107['e107_url'] = "http://e107.org";
        $e107['e107_version'] = "v0.617";
        $e107['e107_build'] = "";
        $e107['e107_datestamp'] = time();
        $tmp = serialize($e107);
        mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('e107', '$tmp') ");

        $udirs = "admin/|plugins/|temp";
        $e_SELF = "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
        $e_HTTP = eregi_replace($udirs, "", substr($e_SELF, 0, strrpos($e_SELF, "/"))."/");
        require_once("e107_files/def_e107_prefs.php");

        $tmp = serialize($pref);
        mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('pref', '$tmp') ");

        $emote = 'a:60:{i:0;a:1:{s:2:"&|";s:7:"cry.png";}i:1;a:1:{s:3:"&-|";s:7:"cry.png";}i:2;a:1:{s:3:"&o|";s:7:"cry.png";}i:3;a:1:{s:3:":((";s:7:"cry.png";}i:4;a:1:{s:3:"~:(";s:7:"mad.png";}i:5;a:1:{s:4:"~:o(";s:7:"mad.png";}i:6;a:1:{s:4:"~:-(";s:7:"mad.png";}i:7;a:1:{s:2:":)";s:9:"smile.png";}i:8;a:1:{s:3:":o)";s:9:"smile.png";}i:9;a:1:{s:3:":-)";s:9:"smile.png";}i:10;a:1:{s:2:":(";s:9:"frown.png";}i:11;a:1:{s:3:":o(";s:9:"frown.png";}i:12;a:1:{s:3:":-(";s:9:"frown.png";}i:13;a:1:{s:2:":D";s:8:"grin.png";}i:14;a:1:{s:3:":oD";s:8:"grin.png";}i:15;a:1:{s:3:":-D";s:8:"grin.png";}i:16;a:1:{s:2:":?";s:12:"confused.png";}i:17;a:1:{s:3:":o?";s:12:"confused.png";}i:18;a:1:{s:3:":-?";s:12:"confused.png";}i:19;a:1:{s:3:"%-6";s:11:"special.png";}i:20;a:1:{s:2:"x)";s:8:"dead.png";}i:21;a:1:{s:3:"xo)";s:8:"dead.png";}i:22;a:1:{s:3:"x-)";s:8:"dead.png";}i:23;a:1:{s:2:"x(";s:8:"dead.png";}i:24;a:1:{s:3:"xo(";s:8:"dead.png";}i:25;a:1:{s:3:"x-(";s:8:"dead.png";}i:26;a:1:{s:2:":@";s:7:"gah.png";}i:27;a:1:{s:3:":o@";s:7:"gah.png";}i:28;a:1:{s:3:":-@";s:7:"gah.png";}i:29;a:1:{s:2:":!";s:8:"idea.png";}i:30;a:1:{s:3:":o!";s:8:"idea.png";}i:31;a:1:{s:3:":-!";s:8:"idea.png";}i:32;a:1:{s:2:":|";s:11:"neutral.png";}i:33;a:1:{s:3:":o|";s:11:"neutral.png";}i:34;a:1:{s:3:":-|";s:11:"neutral.png";}i:35;a:1:{s:2:"?!";s:12:"question.png";}i:36;a:1:{s:2:"B)";s:12:"rolleyes.png";}i:37;a:1:{s:3:"Bo)";s:12:"rolleyes.png";}i:38;a:1:{s:3:"B-)";s:12:"rolleyes.png";}i:39;a:1:{s:2:"8)";s:10:"shades.png";}i:40;a:1:{s:3:"8o)";s:10:"shades.png";}i:41;a:1:{s:3:"8-)";s:10:"shades.png";}i:42;a:1:{s:2:":O";s:12:"suprised.png";}i:43;a:1:{s:3:":oO";s:12:"suprised.png";}i:44;a:1:{s:3:":-O";s:12:"suprised.png";}i:45;a:1:{s:2:":p";s:10:"tongue.png";}i:46;a:1:{s:3:":op";s:10:"tongue.png";}i:47;a:1:{s:3:":-p";s:10:"tongue.png";}i:48;a:1:{s:2:":P";s:10:"tongue.png";}i:49;a:1:{s:3:":oP";s:10:"tongue.png";}i:50;a:1:{s:3:":-P";s:10:"tongue.png";}i:51;a:1:{s:2:";)";s:8:"wink.png";}i:52;a:1:{s:3:";o)";s:8:"wink.png";}i:53;a:1:{s:3:";-)";s:8:"wink.png";}i:54;a:1:{s:4:"!ill";s:7:"ill.png";}i:55;a:1:{s:7:"!amazed";s:10:"amazed.png";}i:56;a:1:{s:4:"!cry";s:7:"cry.png";}i:57;a:1:{s:6:"!dodge";s:9:"dodge.png";}i:58;a:1:{s:6:"!alien";s:9:"alien.png";}i:59;a:1:{s:6:"!heart";s:9:"heart.png";}}';
        mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('emote', '$emote') ");

        $menu_conf = 'a:23:{s:15:"comment_caption";s:15:"Latest Comments";s:15:"comment_display";s:2:"10";s:18:"comment_characters";s:2:"50";s:15:"comment_postfix";s:12:"[ more ... ]";s:13:"comment_title";i:0;s:15:"article_caption";s:8:"Articles";s:16:"articles_display";s:2:"10";s:17:"articles_mainlink";s:23:"Articles Front Page ...";s:21:"newforumposts_caption";s:18:"Latest Forum Posts";s:21:"newforumposts_display";s:2:"10";s:19:"forum_no_characters";s:2:"20";s:13:"forum_postfix";s:10:"[more ...]";s:11:"update_menu";s:20:"Update menu Settings";s:17:"forum_show_topics";s:1:"1";s:24:"newforumposts_characters";s:2:"50";s:21:"newforumposts_postfix";s:10:"[more ...]";s:19:"newforumposts_title";i:0;s:13:"clock_caption";s:11:"Date / Time";s:15:"reviews_caption";s:7:"Reviews";s:15:"reviews_display";s:2:"10";s:15:"reviews_parents";s:1:"1";s:16:"reviews_mainlink";s:21:"Review Front Page ...";s:16:"articles_parents";s:1:"1";}';
        mysql_query("INSERT INTO ".$mySQLprefix."core VALUES ('menu_pref', '$menu_conf') ");

        mysql_query("INSERT INTO ".$mySQLprefix."banner VALUES (0, 'e107', 'e107login', 'e107password', 'e107.jpg', 'http://e107.org', 0, 0, 0, 0, 0, 0, '', 'campaign_one') ");
        mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('1', 'This text (if activated) will appear at the top of your front page all the time.', '0')");
        mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('2', 'Member message ----- This text (if activated) will appear at the top of your front page all the time - only logged in members will see this.', '0')");
        mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES ('3', 'Administrator message ----- This text (if activated) will appear at the top of your front page all the time - only logged in administrators will see this.', '0')");
                mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES (4, 'This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on.', '0')");
                mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES (5, 'Member rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in members will see this.', '0')");
                mysql_query("INSERT INTO ".$mySQLprefix."wmessage VALUES (6, 'Administrator rules ----- This text (if activated) will appear on a page when \"Forum Rules\" link is clicked on - only logged in administrators will see this.', '0')");

        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'login_menu', 1, 1, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'search_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'chatbox_menu', 1, 3, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'sitebutton_menu', 1, 4, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'online_menu', 1, 5, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'compliance_menu', 1, 6, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'clock_menu', 2, 1, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'articles_menu', 2, 2, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'poll_menu', 2, 4, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'headlines_menu', 2, 5, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'counter_menu', 2, 6, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'powered_by_menu', 2, 7, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'backend_menu', 2, 8, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'admin_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'banner_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'comment_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'newforumposts_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'review_menu', 2, 3, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'tree_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'userlanguage_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'usertheme_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'blogcalendar_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'online_extended_menu', 0, 0, 0, '')");
        mysql_query("INSERT INTO ".$mySQLprefix."menus VALUES (0, 'other_news_menu', 0, 0, 0, '')");

        mysql_query("INSERT INTO ".$mySQLprefix."userclass_classes VALUES (1, 'PRIVATEMENU', 'Grants access to private menu items')");
        mysql_query("INSERT INTO ".$mySQLprefix."userclass_classes VALUES (2, 'PRIVATEFORUM1', 'Example private forum class')");
                mysql_query("INSERT INTO ".$mySQLprefix."parser VALUES (0,'e107core','/{(PROFILE)=([0-9]+)}/') ");
                mysql_query("INSERT INTO ".$mySQLprefix."parser VALUES (0,'e107core','/{(EMAILTO)=(.+?)}/') ");
                mysql_query("INSERT INTO ".$mySQLprefix."parser VALUES (0,'e107core','/{(AVATAR)(=(.+?))*}/') ");
                mysql_query("INSERT INTO ".$mySQLprefix."parser VALUES (0,'e107core','/{(PICTURE)(=(.+?))*}/') ");
                mysql_query("INSERT INTO ".$mySQLprefix."parser VALUES (0,'e107core','/{(USERNAME)}/') ");
                mysql_query("INSERT INTO ".$mySQLprefix."plugin VALUES (0, 'Integrity Check', '0.03', 'integrity_check', 1) ");


        $userp = "1, '".$_POST['admin_name']."', '', '".md5($_POST['admin_password1'])."', '', '".$_POST['admin_email']."', '', '', '', '', '', '', '', '', '', 0, ".time().", 0, 0, 0, 0, 0, 0, '$ip', 0, '', '', '', 0, 1, '', '', '0', '', ".time();
        mysql_query("INSERT INTO ".$mySQLprefix."user VALUES ($userp)" );
        mysql_close();

        return FALSE;

}

function tablestyle($caption, $text){
        global $style;
//        echo "Mode: ".$style;

        echo "<div class='spacer'>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captiontopleft'><img src='e107_install/images/blank.gif' width='21' height='4' alt='' style='display: block;' /></td>
<td class='captiontopmiddle'><img src='e107_install/images/blank.gif' width='1' height='4' alt='' style='display: block;' /></td>
<td class='captiontopright'><img src='e107_install/images/blank.gif' width='8' height='4' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='captionleft'><img src='e107_install/images/blank.gif' width='21' height='18' alt='' style='display: block;' /></td>
<td class='captionbar' style='white-space:nowrap'>".$caption."</td>
<td class='captionend'><img src='e107_install/images/blank.gif' width='12' height='18' alt='' style='display: block;' /></td>
<td class='captionmain'><img src='e107_install/images/blank.gif' width='1' height='18' alt='' style='display: block;' /></td>
<td class='captionright'><img src='e107_install/images/blank.gif' width='11' height='18' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bodyleft'><img src='e107_install/images/blank.gif' width='3' height='1' alt='' style='display: block;' /></td>
<td class='bodymain'>".$text."</td>
<td class='bodyright'><img src='e107_install/images/blank.gif' width='3' height='1' alt='' style='display: block;' /></td>
</tr>
</table>
<table cellpadding='0' cellspacing='0'>
<tr>
<td class='bottomleft'><img src='e107_install/images/blank.gif' width='12' height='8' alt='' style='display: block;' /></td>
<td class='bottommain'><img src='e107_install/images/blank.gif' width='1' height='8' alt='' style='display: block;' /></td>
<td class='bottomright'><img src='e107_install/images/blank.gif' width='12' height='8' alt='' style='display: block;' /></td>
</tr>
</table>
</div>";
}

function get_lan(){
        $handle=opendir("e107_languages/");
        while ($file = readdir($handle)){
                if($file != "." && $file != ".." && $file != "/"){
                        $lanlist[] = $file;
                }
        }
        closedir($handle);
        return $lanlist;
}

function ren_header($text){
        echo "<div style='text-align:center'>\n<table cellspacing='0' cellpadding='0' style='width:732px'><tr><td>";

        $str = "<form method='post' action='".$_SERVER['PHP_SELF']."'>
<table cellspacing='0' cellpadding='0' style='width:726px'>\n<tr>\n<td height='120px' style='width: 186px; background-image: url(e107_images/install/install1.png);'></td>\n<td height='120px' style='width: 540px; background-image: url(e107_images/install/install2.png); vertical-align:top; text-align:right'>\n<br />\n$text\n<br />\n</td>\n</tr></table><table style='width:99%'>\n";
        return $str;
}



echo "\n\n</form></body>\n</html>";
?>