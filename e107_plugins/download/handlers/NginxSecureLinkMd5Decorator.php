<?php
require_once('SecureLinkDecorator.php');

class NginxSecureLinkMd5Decorator implements SecureLinkDecorator
{
	protected $url = null;
	protected $prefs = array();

	public static $SUPPORTED_VARIABLES = array(
		'$secure_link_expires',
		'$uri',
		'$remote_addr',
		'$host',
	);

	static function supported_variables() {
		return self::$SUPPORTED_VARIABLES;
	}

	function __construct($url, $preferences)
	{
		$this->url = $url;
		$this->prefs = $preferences;
	}

	public function decorate()
	{
		$prefs = $this->prefs;
		$url = $this->url;
		$expiry = intval($prefs['download_security_link_expiry']);
		if ($expiry <= 0)
			$expiry = PHP_INT_MAX;
		else
			$expiry = time() + $expiry;
		$url_parts = parse_url($url);
		$evaluation = str_replace(
			self::supported_variables(),
			array(
				$expiry,
				$url_parts['path'],
				$_SERVER['REMOTE_ADDR'],
				$url_parts['host'],
			),
			$prefs['download_security_expression']
		);
		$query_string = $url_parts['query'];
		parse_str($query_string, $query_args);
		$query_args['md5'] = str_replace(array('+', '/', '='), array('-', '_', ''), base64_encode(md5($evaluation, true)));
		if (strpos($prefs['download_security_expression'], '$secure_link_expires') !== false)
			$query_args['expires'] = $expiry;
		require_once(__DIR__ . '/../vendor/shim_http_build_url.php');
		return http_build_url($url_parts, array('query' => http_build_query($query_args)));
	}
}