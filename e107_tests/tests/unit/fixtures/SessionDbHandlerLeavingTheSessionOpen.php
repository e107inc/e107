<?php

/**
 * {@see e_session_db} that leaves the process's own session open: the handler's destructor closes whatever session is active.
 *
 * e_HANDLER.'session_handler.php' must already be loaded when this file is included.
 */
class SessionDbHandlerLeavingTheSessionOpen extends e_session_db
{
	public function __destruct()
	{
	}
}
