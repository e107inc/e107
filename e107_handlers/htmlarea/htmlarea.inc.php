<?php
// Settings ==========================================================
    $width = "540px";  // htmlarea width
    $height = "320px";  // htmlarea height
    $fullscreen = 1;   // Show Full-Screen Editor button. 0=no 1=yes
    $display_emoticons = 0; // Show Emoticons when enabled in e107 ?
    $tableops = 1;  // Table operations.
    $spelling = 0;  // Spell Checking.
 // ========================================================================
    $plgcnt =0; // do not change.
    $imagebut = (ADMIN) ? "insertimage" : "space"; // image button for  ADMINS only
    $popupeditor = $fullscreen == 1 ? "popupeditor":"space";

// ==========================
echo "<script type=\"text/javascript\"> _editor_url = '".e_HANDLER."htmlarea/'; </script>\n";
echo "<script type=\"text/javascript\" src=\"".e_HANDLER."htmlarea/htmlarea.js\"></script>\n";
echo "<script type=\"text/javascript\">\n";
echo ($tableops==1) ? "HTMLArea.loadPlugin('TableOperations');\n":"";
echo ($spelling==1) ? "HTMLArea.loadPlugin('SpellChecker');\n":"";
echo "var config = new HTMLArea.Config(); // create a new configuration object\n";
echo htmlarea_emote(1);
echo "config.width = '".$width."';\n
            config.height = '".$height."';\n
            config.statusBar = false;\n
            config.pageStyle =
            'body { background-color: white; font-size: 12px; border:1px solid black; color: black; font-family: tahoma, verdana, arial, sans-serif; } ' +
            'p { font-width: bold; } ';
            config.editorURL = '".e_HANDLER."htmlarea/';
            config.toolbar = [
            ['fontname','fontsize','space','formatblock','space'],
            ['bold','italic','underline','separator','copy', 'cut', 'paste','separator', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'separator',";

// echo "'insertorderedlist', 'insertunorderedlist', 'outdent', 'indent', 'separator',";
echo "'orderedlist', 'unorderedlist', 'outdent', 'indent', 'separator',";
echo "'forecolor', 'hilitecolor', 'separator',
            'inserthorizontalrule', 'createlink', '".$imagebut."', 'inserttable', 'separator','htmlmode', '".$popupeditor."'
            ]";
echo $display_emoticons ? ",[".htmlarea_emote(2)."]":"";
echo"];";


echo "</script>\n";

// ==================================================
function htmlarea($name){
/*  usage:
    $name should be the name of the <textarea> element you wish to replace with Htmlarea.
    You should also add ID="fieldname" to your <textarea> tag.

    eg. <textarea id='post' name='post' >

    And at the beginning of your page you would include:

    require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
    htmlarea("post");
*/
  global $tableops,$spelling,$plgcnt;


echo "<script type=\"text/javascript\" defer=\"1\">
     var editor_$name = new HTMLArea('$name', config);";
echo  ($tableops==1 && $plgcnt<1) ? " editor_$name.registerPlugin(TableOperations);\n ":"";
echo ($spelling==1 && $plgcnt<1) ? " editor_$name.registerPlugin(SpellChecker);\n ":"";
        $plgcnt++;
echo "  setTimeout(function() {
        var check = '$name';
        if(document.getElementById(check)){
        editor_$name.generate();
        }
       }, 10);
       </script>\n";
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