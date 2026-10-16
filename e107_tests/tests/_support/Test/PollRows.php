<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 */

namespace Test;

/**
 * A row of the polls table as poll::render_poll() takes it, for the tests that render one.
 */
trait PollRows
{
	/**
	 * @param array $values the columns that differ from the default row
	 * @return array
	 */
	private function pollRow(array $values = array())
	{
		return array_merge(array(
			'poll_id'              => 0,
			'poll_datestamp'       => time(),
			'poll_start_datestamp' => 0,
			'poll_end_datestamp'   => 0,
			'poll_admin_id'        => 1,
			'poll_title'           => 'Fixture poll',
			'poll_options'         => "Yes\x01No\x01",
			'poll_votes'           => "1\x011",
			'poll_ip'              => '',
			'poll_type'            => 1,
			'poll_comment'         => 1,
			'poll_allow_multiple'  => 0,
			'poll_result_type'     => 0,
			'poll_vote_userclass'  => 0,
			'poll_storage_method'  => 0,
		), $values);
	}
}
