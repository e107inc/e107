<?php

/**
 * {@see e_pref} already carrying a database error, which is what
 * {@see e_pref::save()} tests to decide it cannot write. Nothing in core puts
 * a pref object in that state, so it is armed here rather than produced by a
 * database failing.
 *
 * e_HANDLER.'pref_class.php' must already be loaded when this file is included.
 */
class PrefSqlErrorProbeFixture extends e_pref
{
	public function armSqlError()
	{
		$this->_db_errno = 1;
	}
}
