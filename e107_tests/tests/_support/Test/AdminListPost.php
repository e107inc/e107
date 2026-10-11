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
 * What an e_admin_ui list posts for a batch and for a confirmed delete, less the e-token each Cest adds itself.
 */
class AdminListPost
{
	/**
	 * A batch as the list's batch menu posts it.
	 *
	 * @param string $trigger etrigger_batch value
	 * @param int $id the one row the selection carries
	 * @param string $selection name of the list's checkbox column
	 * @return array
	 */
	public static function batch($trigger, $id, $selection)
	{
		return array(
			'etrigger_batch'   => $trigger,
			'e__execute_batch' => 'Go',
			$selection         => array($id => $id),
		);
	}

	/**
	 * The request a batch delete's confirm screen finishes with, the ids in delete_confirm_value.
	 *
	 * @param int $id row to delete
	 * @param string $command the batch command, as the confirm screen or a caller spells it
	 * @return array
	 */
	public static function confirmedBatchDelete($id, $command = 'delete')
	{
		return array(
			'etrigger_batch'          => $command,
			'e__execute_batch'        => 'Go',
			'etrigger_delete_confirm' => 'Confirm',
			'delete_confirm_value'    => $id,
		);
	}

	/**
	 * The request a single-row delete's confirm screen finishes with.
	 *
	 * @param int $id row to delete
	 * @return array
	 */
	public static function confirmedRowDelete($id)
	{
		return array(
			'etrigger_delete'         => array($id => $id),
			'etrigger_delete_confirm' => 'Confirm',
			'delete_confirm_value'    => $id,
		);
	}
}
