<?php
// Diagnostic only: do PHP's vectorised string functions agree with plain implementations on this CPU?
function ref_b64($s)
{
	$t = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
	$o = '';
	$n = strlen($s);
	for ($i = 0; $i + 2 < $n; $i += 3)
	{
		$v = (ord($s[$i]) << 16) | (ord($s[$i + 1]) << 8) | ord($s[$i + 2]);
		$o .= $t[($v >> 18) & 63].$t[($v >> 12) & 63].$t[($v >> 6) & 63].$t[$v & 63];
	}
	$r = $n - $i;
	if ($r === 1)
	{
		$v = ord($s[$i]) << 16;
		$o .= $t[($v >> 18) & 63].$t[($v >> 12) & 63].'==';
	}
	elseif ($r === 2)
	{
		$v = (ord($s[$i]) << 16) | (ord($s[$i + 1]) << 8);
		$o .= $t[($v >> 18) & 63].$t[($v >> 12) & 63].$t[($v >> 6) & 63].'=';
	}
	return $o;
}
mt_srand(isset($argv[1]) ? (int) $argv[1] : 1);
$bad = array('enc' => 0, 'dec' => 0, 'lower' => 0, 'md5' => 0, 'session' => 0);
$examples = array();
$t0 = microtime(true);
for ($i = 0; $i < 20000; $i++)
{
	$len = mt_rand(1, 1500);
	$s = ($i % 2) ? random_bytes($len) : substr(str_repeat('e107sess|a:2:{s:12:"__form_token";s:64:"'.bin2hex(random_bytes(32)).'";}Test_cookie|s:34:"'.mt_rand(1, 99).'.'.md5(random_bytes(8)).'";', 20), 0, $len);
	$e = base64_encode($s);
	if ($e !== ref_b64($s)) { $bad['enc']++; $examples[] = 'enc len='.$len; }
	if (base64_decode($e) !== $s) { $bad['dec']++; $examples[] = 'dec len='.$len; }
	if (base64_decode($e, true) !== $s) { $bad['dec']++; $examples[] = 'strict dec len='.$len; }
	$a = strtr($s, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
	if (strtolower($s) !== $a) { $bad['lower']++; $examples[] = 'lower len='.$len; }
}
echo json_encode(array('php' => PHP_VERSION, 'bad' => $bad, 'examples' => array_slice($examples, 0, 10), 'seconds' => round(microtime(true) - $t0, 2))), "\n";
