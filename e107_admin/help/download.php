<?php
$text = "Please upload your files into the ".e_FILE."downloads folder, your images into the ".e_FILE."downloadimages folder and thumbnail images into the ".e_FILE."downloadthumbs folder.
<br /><br />
To submit a download, first create a parent, then create a category under that parent, you will then be able to make the download available.";
$ns -> tablerender("Download Help", $text);
?>