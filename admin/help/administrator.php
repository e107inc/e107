<?php
$caption = "Site Admin Help";
$text = "Use this page to enter new, or delete site administrators.
<br /><br>
<b>Permissions</b>
<br>
Level 1: Can access everything<br />
Level 2: Cannot change site prefs or add new administrators<br />
Level 3: Can only post news items.<br />
Level 4: Can only moderate forums.";
$ns -> tablerender($caption, $text);
?>