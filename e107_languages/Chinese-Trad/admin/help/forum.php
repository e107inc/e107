<?php
$caption = "Forum Help";
$text = "<b>General</b><br />
Use this screen to create or 編輯 your forums<br />
<br />
<b>Parents/Forums</b><br />
A parent is a heading that other forums are displayed under, this makes layout simpler and makes navigating around your forums much simpler for 訪客.
<br /><br />
<b>Accessability</b>
<br />
You can set your forums to only be accessable to certain 訪客. Once you have set the 'class' of the 訪客 you can tick the 
class to only allow those 訪客 access to the forum. You can set parents or individual forums up in this way.
<br /><br />
<b>Moderators</b>
<br />
Tick the names of the listed 管理員s to give them moderator status on the forum. The 管理員 must have forum moderation 權限 to be listed here.
<br /><br />
<b>Ranks</b>
<br />
Set your 會員 ranks from here. If the image fields are filled in, images will be used, to use rank names enter the names and make sure the corrosponding rank image field is blank.<br />The threshold is the number of points the 會員 needs to gain before his level changes.";
$ns -> tablerender($caption, $text);
unset($text);
?>