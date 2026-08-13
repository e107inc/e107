<?php
/**
 * Swept immediately after unsets_post.php. It stays clean only if the two
 * ran in different processes.
 */

if(!isset($_POST))
{
	trigger_error('$_POST did not survive the previous script', E_USER_WARNING);
}
