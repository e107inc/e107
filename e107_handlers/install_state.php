<?php
/*
 * e107 website system
 *
 * Copyright (C) 2008-2026 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 * Installer wizard-state transport.
 */

if(!defined('e107_INIT'))
{
	exit;
}

/**
 * Encode installer wizard state for transport in a hidden form field.
 *
 * The multi-stage installer carries its accumulated answers ($previous_steps,
 * a plain array of scalar config) across requests.
 *
 * @param array $state
 * @return string base64-encoded JSON
 */
function install_state_encode(array $state)
{
	return base64_encode(json_encode($state));
}

/**
 * Decode wizard state produced by {@see install_state_encode()}.
 *
 * Always returns an array and never instantiates an object: client input is
 * only ever passed through json_decode(), which yields arrays and scalars but
 * no objects.
 *
 * @param mixed $raw base64-encoded JSON state
 * @return array
 */
function install_state_decode($raw)
{
	$decoded = base64_decode((string) $raw, true);
	if($decoded === false || $decoded === '')
	{
		return array();
	}

	$state = json_decode($decoded, true);

	return is_array($state) ? $state : array();
}
