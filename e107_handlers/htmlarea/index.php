<html>
<head>
<title>HTMLArea 3.0 for e107</title>
  <script type="text/javascript">
   //   _editor_url = window.opener._editor_url;
  //    _editor_lang = window.opener._editor_lang;
 //     var BASE = window.opener.document.baseURI || window.opener.document.URL;
  //    var head = document.getElementsByTagName("head")[0];
 //     var base = document.createElement("base");
 //     base.href = BASE;
 //     head.appendChild(base);
    </script>

<script type="text/javascript" src="htmlarea.js"></script>


<script type="text/javascript" src="htmlarea-lang-en.js"></script>
<script type="text/javascript" src="dialog.js"></script>
<script type="text/javascript" src="editor.js"></script>



<style type="text/css">
@import url(htmlarea.css);

html, body {
  font-family: Verdana,sans-serif;
  background-color: #ddd;
  color: #000;
}
a:link, a:visited { color: #00f; }
a:hover { color: #048; }
a:active { color: #f00; }

textarea { background-color: #fff; border: 1px solid 00f; }
</style>

<script type="text/javascript">
var editor = null;
function initEditor() {
  editor = new HTMLArea("ta");
  editor.generate();

}
function insertHTML() {
  var html = prompt("Enter some HTML code here");
  if (html) {
    editor.insertHTML(html);
  }
}
function highlight() {
  editor.surroundHTML('<span style="background:yellow">', '</span>');
}

function updateOpener(){
        htmresult = editor.getHTML();
      opener.document.forms.dataform.data.value = htmresult;
   //      parent_object.setHTML(editor.getInnerHTML());
        window.close()
}

</script>

</head>

<!-- use <body onload="initEditor()"> if you don't care about
     customizing the editor.  It's the easiest way! :)
         <body onload="HTMLArea.replaceAll()"> -->
<body onload="initEditor()">

HTMLArea &copy; <a href="http://interactivetools.com">InteractiveTools.com</a>, 2003.</p>

<form method="POST" action="" name="mainform">

<textarea id="ta" name='ta' style="width:60%" rows="20"></textarea>

<p>

<input class="button" type="button" value="Finished" onClick="updateOpener()">
<input class="button" type="button" value="Cancel" onClick="window.close()">

<input type="button" name="ins" value="  insert html  " onclick="return insertHTML();" />
<input type="button" name="hil" value="  highlight text  " onclick="return highlight();" />

<script type="text/javascript">
document.forms.mainform.ta.value = opener.document.forms.dataform.data.value
</script>

</form>

</body>
</html>