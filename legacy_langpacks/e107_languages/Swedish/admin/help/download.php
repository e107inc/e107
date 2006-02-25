<?php
/*
+ ----------------------------------------------------------------------------+
|     e107 website system - Language File.
|
|     $Source: /cvs_backup/e107_langpacks/legacy_langpacks/e107_languages/Swedish/admin/help/download.php,v $
|     $Revision: 1.2 $
|     $Date: 2006-02-25 13:24:52 $
|     $Author: mrpiercer $
+----------------------------------------------------------------------------+
*/

if (!defined('e107_INIT')) { exit; }

$text = "Var v&auml;nlig att ladda upp dina filer till ".e_FILE."downloads foldern, dina bilder till ".e_FILE."downloadimages foldern och tumnagelbilder till ".e_FILE."downloadthumbs foldern.
&lt;br /&gt;&lt;br /&gt;
F&ouml;r att skapa en nerladdning, skapa f&ouml;rst en v&auml;rd, sedan en underkategori under den v&auml;rden. Efter det kommer du att kunna g&ouml;ra nerladdningen tillg&auml;nglig.";
$ns -&gt; tablerender("Nerladdningshj&auml;lp", $text);

?>
