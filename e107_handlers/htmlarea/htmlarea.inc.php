<?php

// HTMLAREA handler for e107.
// $Id: htmlarea.inc.php,v 1.13 2004-08-13 23:42:43 e107coders Exp $

// Settings ==========================================================
    $width = "520px";  // htmlarea width
    $height = "300px";  // htmlarea height
    $fullscreen = 0;   // Show Full-Screen Editor button. 0=no 1=yes
    $display_emoticons = 0; // Show Emoticons when enabled in e107 ?
    $tableops = 1;  // Table operations Plugin.
    $spelling = 0;  // Spell Checking Plugin.
    $tidy = 0; // Html Tidy Plugin.
    $context = 1; // Context Menu Plugin
    $imgmanager = 1; // Use Image-Manager in ADMINS areas.
    $charmap = 1; // Load Character Map
 // ========================================================================

function htmlarea($ta_name){
/*  usage:
    $name should be the name of the <textarea> element you wish to replace with Htmlarea.
    You should also add ID="fieldname" to your <textarea> tag.
    eg. <textarea id='post' name='post' >

    And at the beginning of your page (after require_once("class2.php");)
    you would include:

    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
    $htmlarea_js = htmlarea("post");
    or for multiple textareas:
    $htmlarea_js = htmlarea("post,data,otherfield");
*/

  global $charmap,$display_emoticons,$tableops,$spelling,$plgcnt,$height,$width,$context, $tidy,$imagebut, $imgmanager;
    $plgcnt =0; // do not change.
 //   $imagebut = (ADMIN) ? "insertimage" : "space"; // image button for  ADMINS only
    $popupeditor = $fullscreen == 1 ? "popupeditor":"space";
    $charmapon = $charmap ==1 ? "'insertcharacter',":"";

// ==================================================


$areajs = "\n\n<script type='text/javascript'>\n _editor_url = '".e_HANDLER."htmlarea/'; \n _editor_lang = 'en'; \n</script>\n";
$areajs .= "<script type='text/javascript' src='".e_HANDLER."htmlarea/htmlarea.js'></script>\n";
$areajs .= "<script type='text/javascript' >\n";
$areajs .= ($context==1) ? "HTMLArea.loadPlugin('ContextMenu');\n":"";
$areajs .= ($tableops==1) ? "HTMLArea.loadPlugin('TableOperations');\n":"";
$areajs .= ($spelling==1) ? "HTMLArea.loadPlugin('SpellChecker');\n":"";
$areajs .= ($imgmanager==1 && ADMIN) ? "HTMLArea.loadPlugin('ImageManager');\n":"";
$areajs .= ($tidy==1) ? "HTMLArea.loadPlugin('HtmlTidy');\n":"";
$areajs .= ($charmap==1) ? "HTMLArea.loadPlugin('CharacterMap');\n":"";
$areajs .= "</script>\n\n";


$areajs .= "\n<script type='text/javascript' >\n";
$areajs .= "function initEditor() { \n";
$name = explode(",",$ta_name);
    for ($i=0; $i<count($name); $i++) {
        $areajs .= "var editor_".$name[$i]." = new HTMLArea('".$name[$i]."');\n";
        $areajs .= ($context==1) ? " editor_".$name[$i].".registerPlugin('ContextMenu');\n ":"";
        $areajs .=  ($tableops==1) ? " editor_".$name[$i].".registerPlugin(TableOperations);\n ":"";
        $areajs .= ($spelling==1) ? " editor_".$name[$i].".registerPlugin(SpellChecker);\n ":"";
        $areajs .= ($tidy==1) ? " editor_".$name[$i].".registerPlugin(HtmlTidy);\n ":"";
        $areajs .= ($imgmanager==1 && ADMIN) ? " editor_".$name[$i].".registerPlugin(ImageManager);\n ":"";
        $areajs .= ($charmap==1) ? " editor_".$name[$i].".registerPlugin(CharacterMap);\n ":"";

        $areajs .="editor_".$name[$i].".config.toolbar =  [
                [ 'fontname', 'space',
                  'fontsize', 'space',
                  'formatblock', 'space',
                  'bold', 'italic', 'underline', 'separator',
                  'copy', 'cut', 'paste', 'space', 'undo', 'redo', 'space' ],

                [ 'removeformat', 'separator', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'separator',
                  'lefttoright', 'righttoleft', 'separator',
                  'orderedlist', 'unorderedlist', 'outdent', 'indent', 'separator',
                  'forecolor', 'hilitecolor', 'separator',
                  'inserthorizontalrule', 'createlink', 'insertimage', 'inserttable', ".$charmapon." 'separator', 'htmlmode',
                  'popupeditor', 'separator', 'showhelp' ]";

        $areajs .= $display_emoticons ? ",[".htmlarea_emote(2)."]":"";
        $areajs .="]\n\n

        editor_".$name[$i].".config.pageStyle =
                'body { background-color: white; font-size: 12px; border:1px solid black; color: black; font-family: tahoma, verdana, arial, sans-serif; } ' +
                'p { font-width: bold; } ';\n\n";

        $areajs .= "editor_".$name[$i].".config.killWordOnPaste = true;\n";
        $areajs .= ($height)?"editor_".$name[$i].".config.height = '".$height."';\n":"";
        $areajs .= ($width)?"editor_".$name[$i].".config.width = '".$width."';\n":"";


        $areajs .= "
              var check = '".$name[$i]."';
              if(document.getElementById(check)){

              setTimeout(function() {
            editor_".$name[$i].".generate();
             }, 500); \n ";



        $areajs .= "   }";

}
$areajs .="
 }

 HTMLArea.onload = initEditor;
 HTMLArea.init();

</script>\n";

return $areajs;
}

// Build Custom Emoticon Buttons=================

function htmlarea_emote($mode){
global $IMAGES_DIRECTORY, $pref,$display_emoticons;
if($pref['smiley_activate'] && $display_emoticons==1){

        $sql = new db;
        $sql -> db_Select("core", "*", "e107_name='emote'");
        $row = $sql -> db_Fetch(); extract($row);
        $emote = unserialize($e107_value);

        $c=0;
        while(list($code, $name) = @each($emote[$c])){
                if(!$orig[$name]){
                $orig[$name] = TRUE;
        if($mode == "1"){
         //   $str .= "config.registerButton(\"$name\", \"$name\", \"../../".$IMAGES_DIRECTORY."emoticons/".$name."\", false,
            $str .= "config.registerButton(\"$name\", \"$name\", \"".e_IMAGE."emoticons/".$name."\", false,


            // function that gets called when the button is clicked
            function(editor, id) {
            editor.focusEditor();
            editor.insertHTML('<img src=\"".e_IMAGE."emoticons/$name\" style=\"border:0\" alt=\"\" />');
                        } );";
        }

        if($mode == "2"){
            $str .= "'$name' ,";
        }

    }
                $c++;
    }


        return $str;
        }else{
        return "";
        }
}

?>