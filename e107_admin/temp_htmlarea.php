<?

require_once("../class2.php");
require_once(e_HANDLER."htmlarea/htmlarea.inc.php");
$htmlarea_js = htmlarea("data");
require_once("auth.php");




if(IsSet($_POST['submit'])){
$text2 = $_POST['data'];
  $caption = "Result";
  $ns -> tablerender($caption, $text2);

}
   $text = "<div style='text-align:center;width:100%'>
    <form method='post' action='".e_SELF."' name='linkform'>
    <table style='width:100%' class='fborder'>
    <tr>
    <td style='width:100%' class='forumheader3'>
    <textarea id='data' name='data' class='tbox' style='width:100%' cols='80' rows='24' >".$_POST['data']."</textarea>
    </td>
    </tr>

    ";



    $text .="<tr style='vertical-align:top'>
    <td style='text-align:center' class='forumheader'>";
    $text .= "<input class='button' type='submit' name='submit' value='Proceed' />";
    $text .= "</td>
    </tr>
    </table>
    </form>
    </div>";


$caption = "Htmlarea Test Form";
$ns -> tablerender($caption, $text);

require_once(e_ADMIN."footer.php");

function headerjs(){
$headertext = "<script type='text/javascript'>
 _editor_url = '../e107_handlers/htmlarea/';
 _editor_lang = 'en';
  </script>
<script type='text/javascript' src='../e107_handlers/htmlarea/htmlarea.js'></script>
<script type='text/javascript' >
// HTMLArea.loadPlugin('ContextMenu');
// HTMLArea.loadPlugin('TableOperations');
// HTMLArea.loadPlugin('ImageManager');
var config = new HTMLArea.Config(); // create a new configuration object
            config.width = '540px';

            config.height = '320px';

            config.statusBar = false;

       //     config.killWordOnPaste = true;
 config.pageStyle =
            'body { background-color: white; font-size: 12px; border:1px solid black; color: black; font-family: tahoma, verdana, arial, sans-serif; } ' +
            'p { font-width: bold; } '; config.editorURL = '../e107_handlers/htmlarea/';
            config.toolbar = [
            ['fontname','fontsize','space','formatblock','space'],
            ['bold','italic','underline','separator','copy', 'cut', 'paste','separator', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'separator','orderedlist', 'unorderedlist', 'outdent', 'indent', 'separator','forecolor', 'hilitecolor', 'separator',
            'inserthorizontalrule', 'createlink', 'insertimage', 'inserttable', 'separator','htmlmode', 'popupeditor'
            ]];

 </script>

<script type='text/javascript' >
function initEditor() {
var editor_data = new HTMLArea('data', config);
 //  editor_data.registerPlugin('ContextMenu');
 //  editor_data.registerPlugin(TableOperations);
 //  editor_data.registerPlugin(ImageManager);

    //    var check = 'data';
      //  if(document.getElementById(check)){
        editor_data.generate();
     //   return false;
    //    }

 }

 HTMLArea.onload = initEditor;
 window.onload= HTMLArea.init();
       </script>";


$original = " <script type='text/javascript'>
  _editor_url = '../e107_handlers/htmlarea/';
  _editor_lang = 'en';
</script>

<!-- load the main HTMLArea file, this will take care of loading the CSS and
    other required core scripts. -->
<script type='text/javascript' src='../e107_handlers/htmlarea/htmlarea.js'></script>

<!-- load the plugins -->
<script type='text/javascript'>
      // WARNING: using this interface to load plugin
      // will _NOT_ work if plugins do not have the language
      // loaded by HTMLArea.

      // In other words, this function generates SCRIPT tags
      // that load the plugin and the language file, based on the
      // global variable HTMLArea.I18N.lang (defined in the lang file,
      // in our case 'lang/en.js' loaded above).

      // If this lang file is not found the plugin will fail to
      // load correctly and NOTHING WILL WORK.

      HTMLArea.loadPlugin('TableOperations');
      HTMLArea.loadPlugin('SpellChecker');
      HTMLArea.loadPlugin('FullPage');
      // HTMLArea.loadPlugin('HtmlTidy');
      HTMLArea.loadPlugin('ContextMenu');
      HTMLArea.loadPlugin('ListType');
      HTMLArea.loadPlugin('CharacterMap');

</script>


<script type='text/javascript'>
var editor = null;

function initEditor() {

  // create an editor for the 'ta' textbox
  editor = new HTMLArea('data');

  // register the FullPage plugin
  editor.registerPlugin(FullPage);

  // register the SpellChecker plugin
  editor.registerPlugin(TableOperations);
  editor.registerPlugin(ContextMenu);
  // register the SpellChecker plugin
  editor.registerPlugin(SpellChecker);

  // register the HtmlTidy plugin
  //editor.registerPlugin(HtmlTidy);

  // register the ListType plugin
  editor.registerPlugin(ListType);

  editor.registerPlugin(CharacterMap);




  // add a contextual menu

  // load the stylesheet used by our CSS plugin configuration
 // editor.config.pageStyle = '@import url(custom.css);';

  editor.generate();
  return false;
}

HTMLArea.onload = initEditor;

window.onload = HTMLArea.init();
</script>";





 // return $headertext;
}


