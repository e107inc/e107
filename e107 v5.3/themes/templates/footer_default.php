<?php
/*
+---------------------------------------------------------------+
|	e107 website system
|	/themes/templates/templateh.php
|
|	©Steve Dunstan 2001-2002
|	http://jalist.com
|	stevedunstan@jalist.com
|
|	Released under the terms and conditions of the
|	GNU General Public License (http://gnu.org).
+---------------------------------------------------------------+
*/
parseheader($FOOTER);
?>
</body>
</html>
<?php
$sql -> db_Close();
?>
<script type="text/javascript">
var ref=""+escape(top.document.referrer);
var colord = window.screen.colorDepth; 
var res = window.screen.width + "x" + window.screen.height;
var self = document.location;
document.write("<img src='plugins/log2.php?referer=" + ref + "&amp;color=" + colord + "&amp;self=" + self + "&amp;res=" + res + "' style='float:left; border:0' alt='' />");
</script>