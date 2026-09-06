<?php
/**
 * Kills a superglobal process-wide, the way submitnews.php and
 * usersettings.php do. Loaded in the test's own process it takes out every
 * later test that touches $_POST, which is what issue #5908 was.
 */

unset($_POST);
