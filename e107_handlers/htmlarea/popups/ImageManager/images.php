<?
/***********************************************************************
** Title.........:        Image Manager, draws the thumbnails and directies
** Version.......:        1.01
** Author........:        Xiang Wei ZHUO <wei@zhuo.org>
** Filename......:        images.php
** Last changed..:        8 Mar 2003
** Notes.........:        Configuration in config.inc.php

                    Functions
                     - create a new folder,
                     - delete folder,
                     - upload new image
                     - use cached thumbnail views

***********************************************************************/
require_once("../../../../class2.php"); 
require_once( 'config.inc.php');

if(isset($_GET['dir'])) {
        $dirParam = $_GET['dir'];

        if(strlen($dirParam) > 0)
        {
                if(substr($dirParam,0,1)=='/')
                        $IMG_ROOT .= $dirParam;
                else
                        $IMG_ROOT = $dirParam;
        }
}

$refresh_dirs = false;
$clearUploads = false;

if(strrpos($IMG_ROOT, '/')!= strlen($IMG_ROOT)-1)
        $IMG_ROOT .= '/';


if(isset($_GET['create']) && isset($_GET['dir']) && $SAFE_MODE == false)
{
        create_folder();
}

if(isset($_GET['delFile']) && isset($_GET['dir']))
{
        delete_file($_GET['delFile']);
}

if(isset($_GET['delFolder']) && isset($_GET['dir']))
{
        delete_folder($_GET['delFolder']);
}

if(isset($_FILES['upload']) && is_array($_FILES['upload']) && isset($_POST['dirPath']))
{

        $dirPathPost = $_POST['dirPath'];

        if(strlen($dirPathPost) > 0)
        {
                if(substr($dirPathPost,0,1)=='/')
                        $IMG_ROOT .= $dirPathPost;
                else
                        $IMG_ROOT = $dirPathPost;
        }

        if(strrpos($IMG_ROOT, '/')!= strlen($IMG_ROOT)-1)
                $IMG_ROOT .= '/';

        do_upload($_FILES['upload'], $BASE_DIR.$BASE_ROOT.$dirPathPost.'/');
}

function do_upload($file, $dest_dir)
{
        global $clearUploads;

        if(is_file($file['tmp_name']))
        {
                //var_dump($file); echo "DIR:$dest_dir";
                move_uploaded_file($file['tmp_name'], $dest_dir.$file['name']);
                chmod($dest_dir.$file['name'], 0666);
        }



        $clearUploads = true;
}

function delete_folder($folder)
{
        global $BASE_DIR, $refresh_dirs;
        //var_dump($BASE_DIR);
        $del_folder = dir_name($BASE_DIR).$folder;
        //echo $del_folder;
        if(is_dir($del_folder) && num_files($del_folder) <= 0) {
                //echo $del_folder.'<br>';
                rm_all_dir($del_folder);
                $refresh_dirs = true;
        }
}

function rm_all_dir($dir)
{
        //$dir = dir_name($dir);
        //echo "OPEN:".$dir.'<Br>';
        if(is_dir($dir))
        {
                $d = @dir($dir);

                while (false !== ($entry = $d->read()))
                {
                        //echo "#".$entry.'<br>';
                        if($entry != '.' && $entry != '..')
                        {
                                $node = $dir.'/'.$entry;
                                //echo "NODE:".$node;
                                if(is_file($node)) {
                                        //echo " - is file<br>";
                                        unlink($node);
                                }
                                else if(is_dir($node)) {
                                        //echo " -        is Dir<br>";
                                        rm_all_dir($node);
                                }
                        }
                }
                $d->close();

                rmdir($dir);
        }
        //echo "RM: $dir <br>";
}

function delete_file($file)
{
        global $BASE_DIR;

        $del_image = dir_name($BASE_DIR).$file;

        $del_thumb = dir_name($del_image).'.'.basename($del_image);

        if(is_file($del_image)) {
                unlink($del_image);
        }

        if(is_file($del_thumb)) {
                unlink($del_thumb);
        }
}

function create_folder()
{
        global $BASE_DIR, $IMG_ROOT, $refresh_dirs;

        $folder_name = $_GET['foldername'];

        if(strlen($folder_name) >0)
        {
                $folder = $BASE_DIR.$IMG_ROOT.$folder_name;

                if(!is_dir($folder) && !is_file($folder))
                {
                        mkdir($folder,0777);
                        chmod($folder,0777);
                        $refresh_dirs = true;
                }
        }
}

function num_files($dir)
{
        $total = 0;

        if(is_dir($dir))
        {
                $d = @dir($dir);

                while (false !== ($entry = $d->read()))
                {
                        //echo $entry."<br>";
                        if(substr($entry,0,1) != '.') {
                                $total++;
                        }
                }
                $d->close();
        }
        return $total;
}

function dirs($dir,$abs_path)
{
        $d = dir($dir);
                //echo "Handle: ".$d->handle."<br>\n";
                //echo "Path: ".$d->path."<br>\n";
                $dirs = array();
                while (false !== ($entry = $d->read())) {
                        if(is_dir($dir.'/'.$entry) && substr($entry,0,1) != '.')
                        {
                                //dirs($dir.'/'.$entry, $prefix.$prefix);
                                //echo $prefix.$entry."<br>\n";
                                $path['path'] = $dir.'/'.$entry;
                                $path['name'] = $entry;
                                $dirs[$entry] = $path;
                        }
                }
                $d->close();

                ksort($dirs);
                for($i=0; $i<count($dirs); $i++)
                {
                        $name = key($dirs);
                        $current_dir = $abs_path.'/'.$dirs[$name]['name'];
                        echo ", \"$current_dir\"\n";
                        dirs($dirs[$name]['path'],$current_dir);
                        next($dirs);
                }
}

function parse_size($size)
{
        if($size < 1024)
                return $size.' btyes';
        else if($size >= 1024 && $size < 1024*1024)
        {
                return sprintf('%01.2f',$size/1024.0).' Kb';
        }
        else
        {
                return sprintf('%01.2f',$size/(1024.0*1024)).' Mb';
        }
}

function show_image($img, $file, $info, $size)
{
        global $BASE_DIR, $BASE_URL, $newPath;

        $img_path = dir_name($img);
        $img_file = basename($img);

        $thumb_image = 'thumbs.php?img='.urlencode($img);

        $img_url = $BASE_URL.$img_path.'/'.$img_file;

        $filesize = parse_size($size);

?>
<td>
<table width="102" border="0" cellpadding="0" cellspacing="2">
  <tr>
    <td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
        <a href="javascript:;" onClick="javascript:imageSelected('<? echo $img_url; ?>', <? echo $info[0];?>, <? echo $info[1]; ?>,'<? echo $file; ?>');"><img src="<? echo $thumb_image; ?>" alt="<? echo $file; ?> - <? echo $filesize; ?>" border="0"></a></td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="0" cellpadding="2">
        <tr>
          <td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
                        <a href="javascript:;" onClick="javascript:preview('<? echo $img_url; ?>', '<? echo $file; ?>', ' <? echo $filesize; ?>',<? echo $info[0].','.$info[1]; ?>);"><img src="edit_pencil.gif" width="15" height="15" border="0"></a></td>
          <td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
                        <a href="images.php?delFile=<? echo $img_url; ?>&dir=<? echo $newPath; ?>" onClick="return deleteImage('<? echo $file; ?>');"><img src="edit_trash.gif" width="15" height="15" border="0"></a></td>
          <td width="98%" class="imgCaption"><? echo $info[0].'x'.$info[1]; ?></td>
        </tr>
      </table></td>
  </tr>
</table>
</td>
<?
}

function show_dir($path, $dir)
{
        global $newPath, $BASE_DIR, $BASE_URL;

        $num_files = num_files($BASE_DIR.$path);
?>
<td>
<table width="102" border="0" cellpadding="0" cellspacing="2">
  <tr>
    <td align="center" class="imgBorder" onMouseOver="pviiClassNew(this,'imgBorderHover')" onMouseOut="pviiClassNew(this,'imgBorder')">
          <a href="images.php?dir=<? echo $path; ?>" onClick="changeLoadingStatus('load')">
                <img src="folder.gif" width="80" height="80" border=0 alt="<? echo $dir; ?>">
          </a>
        </td>
  </tr>
  <tr>
    <td><table width="100%" border="0" cellspacing="1" cellpadding="2">
        <tr>
          <td width="1%" class="buttonOut" onMouseOver="pviiClassNew(this,'buttonHover')" onMouseOut="pviiClassNew(this,'buttonOut')">
                        <a href="images.php?delFolder=<? echo $BASE_URL.$path; ?>&dir=<? echo $newPath; ?>" onClick="return deleteFolder('<? echo $dir; ?>', <? echo $num_files; ?>);"><img src="edit_trash.gif" width="15" height="15" border="0"></a></td>
          <td width="99%" class="imgCaption"><? echo $dir; ?></td>
        </tr>
      </table></td>
  </tr>
</table>
</td>
<?
}

function draw_no_results()
{
?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><div align="center" style="font-size:large;font-weight:bold;color:#CCCCCC;font-family: Helvetica, sans-serif;">No Images Found</div></td>
  </tr>
</table>
<?
}

function draw_no_dir()
{
        global $BASE_DIR, $BASE_ROOT;
?>
<table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td><div align="center" style="font-size:small;font-weight:bold;color:#CC0000;font-family: Helvetica, sans-serif;">Configuration Problem: &quot;<? echo $BASE_DIR.$BASE_ROOT; ?>&quot; does not exist.</div></td>
  </tr>
</table>
<?
}


function draw_table_header()
{
        echo '<table border="0" cellpadding="0" cellspacing="2">';
        echo '<tr>';
}

function draw_table_footer()
{
        echo '</tr>';
        echo '</table>';
}

?>
<html>
<head>
<title>Image Browser</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<style type="text/css">
<!--
.imgBorder {
        height: 96px;
        border: 1px solid threedface;
        vertical-align: middle;
}
.imgBorderHover {
        height: 96px;
        border: 1px solid threedface;
        vertical-align: middle;
        background: #FFFFCC;
        cursor: hand;
}

.buttonHover {
        border: 1px solid;
        border-color: ButtonHighlight ButtonShadow ButtonShadow ButtonHighlight;
        cursor: hand;
        background: #FFFFCC;
}
.buttonOut
{
 border: 1px solid;
 border-color: white;
}

.imgCaption {
        font-size: 9pt;
        font-family: "MS Shell Dlg", Helvetica, sans-serif;
        text-align: center;
}
.dirField {
        font-size: 9pt;
        font-family: "MS Shell Dlg", Helvetica, sans-serif;
        width:110px;
}

-->
</style>
<?
        $dirPath = eregi_replace($BASE_ROOT,'',$IMG_ROOT);

        $paths = explode('/', $dirPath);
        $upDirPath = '/';
        for($i=0; $i<count($paths)-2; $i++)
        {
                $path = $paths[$i];
                if(strlen($path) > 0)
                {
                        $upDirPath .= $path.'/';
                }
        }

        $slashIndex = strlen($dirPath);
        $newPath = $dirPath;
        if($slashIndex > 1 && substr($dirPath, $slashIndex-1, $slashIndex) == '/')
        {
                $newPath = substr($dirPath, 0,$slashIndex-1);
        }
?>
<script type="text/javascript" src="../popup.js"></script>
<script type="text/javascript" src="../../dialog.js"></script>
<script language="JavaScript" type="text/JavaScript">
<!--
function pviiClassNew(obj, new_style) { //v2.6 by PVII
  obj.className=new_style;
}

function goUp()
{
        location.href = "ImageManager/images.php?dir=<? echo $upDirPath; ?>";
}

function changeDir(newDir)
{
        location.href = "ImageManager/images.php?dir="+newDir;
}

function newFolder(oldDir, newFolder)
{
        location.href = "ImageManager/images.php?dir="+oldDir+'&create=folder&foldername='+newFolder;
}

function updateDir()
{
        var newPath = "<? echo $newPath; ?>";
        if(window.top.document.forms[0] != null) {

        var allPaths = window.top.document.forms[0].dirPath.options;
        //alert("new:"+newPath);
        for(i=0; i<allPaths.length; i++)
        {
                //alert(allPaths.item(i).value);
                allPaths.item(i).selected = false;
                if((allPaths.item(i).value)==newPath)
                {
                        allPaths.item(i).selected = true;
                }
        }

<?
        if($clearUploads) {
?>
        var topDoc = window.top.document.forms[0];
        topDoc.upload.value = null;
        //topDoc.upload.disabled = true;
<?
        }
?>

        }

}

<? if ($refresh_dirs) { ?>
function refreshDirs()
{
        var allPaths = window.top.document.forms[0].dirPath.options;
        var fields = ["/" <? dirs($BASE_DIR.$BASE_ROOT,'');?>];

        var newPath = "<? echo $newPath; ?>";

        while(allPaths.length > 0)
        {
                for(i=0; i<allPaths.length; i++)
                {
                        allPaths.remove(i);
                }
        }

        for(i=0; i<fields.length; i++)
        {
                var newElem =        document.createElement("OPTION");
                var newValue = fields[i];
                newElem.text = newValue;
                newElem.value = newValue;

                if(newValue == newPath)
                        newElem.selected = true;
                else
                        newElem.selected = false;

                allPaths.add(newElem);
        }
}
refreshDirs();
<? } ?>

function imageSelected(filename, width, height, alt)
{
        var topDoc = window.top.document.forms[0];
        topDoc.f_url.value = filename;
        topDoc.f_width.value= width;
        topDoc.f_height.value = height;
        topDoc.f_alt.value = alt;
        topDoc.orginal_width.value = width;
        topDoc.orginal_height.value = height;

}

function preview(file, image, size, width, height)
{
        /*
        var predoc = '<img src="'+file+'" alt="'+image+' ('+width+'x'+height+', '+size+')">';
        var w = 450;
        var h = 400;
        var LeftPosition=(screen.width)?(screen.width-w)/2:100;
        var TopPosition=(screen.height)?(screen.height-h)/2:100;

         var win = window.open('','image_preview','toolbar=no,location=no,menubar=no,status=yes,scrollbars=yes,resizable=yes,width='+w+',height='+h+',top='+TopPosition+',left='+LeftPosition);
     var doc=win.document.open();

     doc.writeln('<html>\n<head>\n<title>Image Preview - '+image+' ('+width+'x'+height+', '+size+')</title>');
     doc.writeln('</head>\n<body>');
     doc.writeln(predoc);
     doc.writeln('</body>\n</html>\n');
     doc=win.document.close();
     win.focus();*/
        //alert(file);
         Dialog("../ImageEditor/ImageEditor.php?img="+escape(file), function(param) {
                if (!param) {        // user must have pressed Cancel
                        return false;
                }
        }, null);
     return;
}

function deleteImage(file)
{
        if(confirm("Delete image \""+file+"\"?"))
                return true;

        return false;
}

function deleteFolder(folder, numFiles)
{
        if(numFiles > 0) {
                alert("There are "+numFiles+" files/folders in \""+folder+"\".\n\nPlease delete all files/folder in \""+folder+"\" first.");
                return false;
        }

        if(confirm("Delete folder \""+folder+"\"?"))
                return true;

        return false;
}

function MM_findObj(n, d) { //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function MM_showHideLayers() { //v6.0
  var i,p,v,obj,args=MM_showHideLayers.arguments;
  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i],window.top.document))!=null) { v=args[i+2];
    if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
    obj.visibility=v; }
}

function changeLoadingStatus(state)
{
        var statusText = null;
        if(state == 'load') {
                statusText = 'Loading Images';
        }
        else if(state == 'upload') {
                statusText = 'Uploading Files';
        }
        if(statusText != null) {
                var obj = MM_findObj('loadingStatus', window.top.document);
                //alert(obj.innerHTML);
                if (obj != null && obj.innerHTML != null)
                        obj.innerHTML = statusText;
                MM_showHideLayers('loading','','show')
        }
}

//-->
</script>
</head>
<body onLoad="updateDir();" bgcolor="#FFFFFF">

<?
//var_dump($_GET);
//echo $dirParam.':'.$upDirPath;
//echo '<br>';
$d = @dir($BASE_DIR.$IMG_ROOT);

if($d)
{
        //var_dump($d);
        $images = array();
        $folders = array();
        while (false !== ($entry = $d->read()))
        {
                $img_file = $IMG_ROOT.$entry;

                if(is_file($BASE_DIR.$img_file) && substr($entry,0,1) != '.')
                {
                        $image_info = @getimagesize($BASE_DIR.$img_file);
                        if(is_array($image_info))
                        {
                                $file_details['file'] = $img_file;
                                $file_details['img_info'] = $image_info;
                                $file_details['size'] = filesize($BASE_DIR.$img_file);
                                $images[$entry] = $file_details;
                                //show_image($img_file, $entry, $image_info);
                        }
                }
                else if(is_dir($BASE_DIR.$img_file) && substr($entry,0,1) != '.')
                {
                        $folders[$entry] = $img_file;
                        //show_dir($img_file, $entry);
                }
        }
        $d->close();

        if(count($images) > 0 || count($folders) > 0)
        {
                //now sort the folders and images by name.
                ksort($images);
                ksort($folders);

                draw_table_header();

                for($i=0; $i<count($folders); $i++)
                {
                        $folder_name = key($folders);
                        show_dir($folders[$folder_name], $folder_name);
                        next($folders);
                }
                for($i=0; $i<count($images); $i++)
                {
                        $image_name = key($images);
                        show_image($images[$image_name]['file'], $image_name, $images[$image_name]['img_info'], $images[$image_name]['size']);
                        next($images);
                }
                draw_table_footer();
        }
        else
        {
                draw_no_results();
        }
}
else
{
        draw_no_dir();
}

?>
<script language="JavaScript" type="text/JavaScript">
MM_showHideLayers('loading','','hide')
</script>
</body>
</html>