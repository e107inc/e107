<?php
$text = "
<b>&raquo;</b> <u>Plugin Title:</u>
Sets title displayed in the menu caption.  Set it to PMLAN_PM to use the language file.
<br /><br />
<b>&raquo;</b> <u>Restrict PM to:</u>
Allows you to restrict the use of the PM system.
<br />As userclasses are added to the system, this list will auto-populate.  The 'Everyone' and 
'Members only' options have the same functionality.
<br /><br />
<b>&raquo;</b> <u>Send email notifications:</u>
If set to yes, it will email the recipient of a PM that a PM has been delivered.
";
$ns -> tablerender("PM Help", $text);
?>