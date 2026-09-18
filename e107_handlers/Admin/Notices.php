<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

namespace e107\Admin;

/**
 * What the administrator can be told about and told to stop telling them.
 *
 * One id names a notice everywhere it appears: the record it is drawn from,
 * the suppression that hides it, the bell entry and the link that dismisses
 * it. They are gathered here so that a notice cannot be hidden under one
 * spelling and shown under another.
 */
class Notices
{
	/** Requests cron.php turned away. */
	const CRON_REFUSED = 'cron-refused';

	/** Attempts to reset the main administrator's password. */
	const FPW_ADMIN_RESET = 'fpw-admin-reset';

	/** The banner offered to a site installed before e107 v2. */
	const UPGRADE_ALERT = 'upgrade-alert';
}
