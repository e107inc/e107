<?php

// Commented out for beta testing reasons, should be
// unccommented on release or use on production sites.

function retrieve_cache($query) {
	if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
		global $db_debug;
		$db_debug->aDeprecatiated();
	}
	global $e107cache, $e107_debug;
	if (!is_object($e107cache)) {
		return FALSE;
	}
	$ret = $e107cache->retrieve($query);
	if ($e107_debug && $ret) {
		echo "cache used for: $query <br />";
	}
	return $ret;
}

function set_cache($query, $text) {
	if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
		global $db_debug;
		$db_debug->aDeprecatiated();
	}
	global $e107cache;
	if (!is_object($e107cache)) {
		return FALSE;
	}
	if ($e107_debug) {
		echo "cache set for: $query <br />";
	}
	$e107cache->set($query, $text);
}

function clear_cache($query) {
	if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
		global $db_debug;
		$db_debug->aDeprecatiated();
	}
	global $e107cache;
	if (!is_object($e107cache)) {
		return FALSE;
	}
	return $e107cache->clear($query);
}


//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//
class textparse {
	function editparse($text, $mode = "off") {
		if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
			global $db_debug;
			$db_debug->aDeprecatiated();
		}
		global $tp;
		return $tp->toForm($text);
	}

	function tpa($text, $mode = '', $referrer = '', $highlight_search = FALSE, $poster_id = '') {
		if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
			global $db_debug;
			$db_debug->aDeprecatiated();
		}
		global $tp;
		return $tp->toHTML($text, TRUE, $mode, $poster_id);
	}

	function tpj($text) {
		if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
			global $db_debug;
			$db_debug->aDeprecatiated();
		}
		return $text;
	}

	function formtpa($text, $mode = '') {
		if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
			global $db_debug;
			$db_debug->aDeprecatiated();
		}
		global $tp;
		$no_encode = ($mode == 'admin') ? TRUE :
		FALSE;
		return $tp->toDB($text, $no_encode);
	}

	function formtparev($text) {
		if (defined('E107_DEBUG_LEVEL') && E107_DEBUG_LEVEL >= 32766) {
			global $db_debug;
			$db_debug->aDeprecatiated();
		}
		global $tp;
		return $tp->toFORM($text);
	}

}
//------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------//

?>