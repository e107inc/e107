<?php

function retrieve_cache($query) {
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
	global $e107cache;
	if (!is_object($e107cache)) {
		return FALSE;
	}
	return $e107cache->clear($query);
}

?>