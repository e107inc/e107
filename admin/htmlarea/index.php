<?php
require_once("../../class2.php");
?>
<html><head><title>htmlArea</title>
<style type="text/css"><!--
  body{ font-family: arial; font-size: x-small; background-color: #000040; color: #fff}
  td  { font-family: arial; font-size: x-small; }
  a         { color: #9EB3F6; text-decoration: none; }
  a:hover   { color: #FF0000; text-decoration: underline; }
  .headline { font-family: arial black, arial; font-size: 28px; letter-spacing: -1px; }
  .headline2{ font-family: verdana, arial; font-size: 12px; color: #EDF1FD}
  .subhead  { font-family: arial, arial; font-size: 18px; font-weight: bold; font-style: italic; }
  .backtotop     { font-family: arial, arial; font-size: xx-small;  }
  .code     { background-color: #EEEEEE; font-family: Courier New; font-size: x-small;
              margin: 5px 0px 5px 0px; padding: 5px;
              border: black 1px dotted;
            }
	.button{
	border: 1px solid #5E5D63;
	color: #000000;
	font-family: verdana, tahoma, arial, helvetica, sans-serif;
	font-size: 12px;
	text-align:center;
}
  font { font-family: arial black, arial; font-size: 28px; letter-spacing: -1px; }
--></style>

<script language="Javascript1.2"><!-- // load htmlarea
_editor_url = "";                     // URL to htmlarea files
var win_ie_ver = parseFloat(navigator.appVersion.split("MSIE")[1]);
if (navigator.userAgent.indexOf('Mac')        >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Windows CE') >= 0) { win_ie_ver = 0; }
if (navigator.userAgent.indexOf('Opera')      >= 0) { win_ie_ver = 0; }
if (win_ie_ver >= 5.5) {
  document.write('<scr' + 'ipt src="' +_editor_url+ 'editor.js"');
  document.write(' language="Javascript1.2"></scr' + 'ipt>');  
} else { document.write('<scr'+'ipt>function editor_generate() { return false; }</scr'+'ipt>'); }


function updateOpener(){
	opener.document.forms.dataform.data.value = document.forms.mainform.yourFieldNameHere.value;
	window.close()
}

// --></script>
</head>
<body>

<div style="text-align:center">
<form method="POST" action="" name="mainform">
<textarea name="yourFieldNameHere" style="width:650; height:400">
</textarea><br><br />
<input class="button" type="button" value="Finished" onClick="updateOpener()">
<input class="button" type="button" value="Cancel" onClick="window.close()">

<script language="javascript1.2">
editor_generate('yourFieldNameHere');
document.forms.mainform.yourFieldNameHere.value = opener.document.forms.dataform.data.value
</script>
</form>
</div>
<div style="text-align:right">
powered by <a href="http://www.interactivetools.com/products/htmlarea/">htmlArea</a>
</div>
</body>
</html>