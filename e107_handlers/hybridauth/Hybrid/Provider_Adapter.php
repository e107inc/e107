<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */

/**
 * Hybrid_Provider_Adapter is the basic class which Hybrid_Auth will use
 * to connect users to a given provider.
 *
 * Basically Hybrid_Provider_Adapter will create a bridge from your php
 * application to the provider api.
 *
 * Hybrid_Auth will automatically load Hybrid_Provider_Adapter and create
 * an instance of it for each authenticated provider.
 */
class Hybrid_Provider_Adapter {

	/**
	 * Provider ID (or unique name)
	 * @var mixed
	 */
	public $id = null;

	/**
	 * Provider adapter specific config
	 * @var array
	 */
	public $config = null;

	/**
	 * Provider adapter extra parameters
	 * @var array
	 */
	public $params = array();

	/**
	 * Provider adapter wrapper path
	 * @var string
	 */
	public $wrapper = null;

	/**
	 * Provider adapter instance
	 * @var Hybrid_Provider_Model
	 */
	public $adapter = null;

	/**
	 * Create a new adapter switch IDp name or ID
	 *
	 * @param string $id     The id or name of the IDp
	 * @param array  $params (optional) required parameters by the adapter
	 * @return Hybrid_Provider_Adapter
	 * @throws Exception
	 */
	function factory($id, $params = array()) {
		Hybrid_Logger::info("Enter Hybrid_Provider_Adapter::factory( $id )");

		# init the adapter config and params
		$this->id = $id;
		$this->params = $params;
		$this->id = $this->getProviderCiId($this->id);
		$this->config = $this->getConfigById($this->id);

		# check the IDp id
		if (!$this->id) {
			throw new Exception("No provider ID specified.", 2);
		}

		# check the IDp config
		if (!$this->config) {
			throw new Exception("Unknown Provider ID, check your configuration file.", 3);
		}

		# check the IDp adapter is enabled
		if (!$this->config["enabled"]) {
			throw new Exception("The provider '{$this->id}' is not enabled.", 3);
		}

		# include the adapter wrapper
		if (isset($this->config["wrapper"]) && is_array($this->config["wrapper"])) {
			if (isset($this->config["wrapper"]["path"])) {
				require_once $this->config["wrapper"]["path"];
			}

			if (!class_exists($this->config["wrapper"]["class"])) {
				throw new Exception("Unable to load the adapter class.", 3);
			}

			$this->wrapper = $this->config["wrapper"]["class"];
		} else {
			require_once Hybrid_Auth::$config["path_providers"] . $this->id . ".php";

			$this->wrapper = "Hybrid_Providers_" . $this->id;
		}

		# create the adapter instance, and pass the current params and config
		$this->adapter = new $this->wrapper($this->id, $this->config, $this->params);

		return $this;
	}

	/**
	 * Hybrid_Provider_Adapter::login(), prepare the user session and the authentication request
	 * for index.php
	 * @return void
	 * @throw Exception
	 */
	function login() {
		Hybrid_Logger::info("Enter Hybrid_Provider_Adapter::login( {$this->id} ) ");

		if (!$this->adapter) {
			throw new Exception("Hybrid_Provider_Adapter::login() should not directly used.");
		}

		// clear all unneeded params
		foreach (Hybrid_Auth::$config["providers"] as $idpid => $params) {
			Hybrid_Auth::storage()->delete("hauth_session.{$idpid}.hauth_return_to");
			Hybrid_Auth::storage()->delete("hauth_session.{$idpid}.hauth_endpoint");
			Hybrid_Auth::storage()->delete("hauth_session.{$idpid}.id_provider_params");
		}

		// make a fresh start
		$this->logout();

		# get hybridauth base url
		if (empty(Hybrid_Auth::$config["base_url"])) {
			// the base url wasn't provide, so we must use the current
			// url (which makes sense actually)
			$url = empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off' ? 'http' : 'https';
			$url .= '://' . $_SERVER['HTTP_HOST'];
			$url .= $_SERVER['REQUEST_URI'];
			$HYBRID_AUTH_URL_BASE = $url;
		} else {
			$HYBRID_AUTH_URL_BASE = Hybrid_Auth::$config["base_url"];
		}

		// make sure params is array
		if (!is_array($this->params)) {
			$this->params = array();
		}

		# we make use of session_id() as storage hash to identify the current user
		# using session_regenerate_id() will be a problem, but ..
		$this->params["hauth_token"] = session_id();

		# set request timestamp
		$this->params["hauth_time"] = time();

		# for default HybridAuth endpoint url hauth_login_start_url
		# 	auth.start  required  the IDp ID
		# 	auth.time   optional  login request timestamp
		if (!isset($this->params["login_start"]) ) {
			$this->params["login_start"] = $HYBRID_AUTH_URL_BASE . ( strpos($HYBRID_AUTH_URL_BASE, '?') ? '&' : '?' ) . "hauth.start={$this->id}&hauth.time={$this->params["hauth_time"]}";
		}

		# for default HybridAuth endpoint url hauth_login_done_url
		# 	auth.done   required  the IDp ID
		if (!isset($this->params["login_done"]) ) {
			$this->params["login_done"] = $HYBRID_AUTH_URL_BASE . ( strpos($HYBRID_AUTH_URL_BASE, '?') ? '&' : '?' ) . "hauth.done={$this->id}";
		}

		# workaround to solve windows live authentication since microsoft disallowed redirect urls to contain any parameters
		# http://mywebsite.com/path_to_hybridauth/?hauth.done=Live will not work
		if ($this->id=="Live") { 
			$this->params["login_done"] = $HYBRID_AUTH_URL_BASE."live.php"; 
		}

		# Workaround to fix broken callback urls for the Facebook OAuth client
		if ($this->adapter->useSafeUrls) {
				$this->params['login_done'] = str_replace('hauth.done', 'hauth_done', $this->params['login_done']);
		}

		if (isset($this->params["hauth_return_to"])) {
			Hybrid_Auth::storage()->set("hauth_session.{$this->id}.hauth_return_to", $this->params["hauth_return_to"]);
		}
		if (isset($this->params["login_done"])) {
			Hybrid_Auth::storage()->set("hauth_session.{$this->id}.hauth_endpoint", $this->params["login_done"]);
		}
		Hybrid_Auth::storage()->set("hauth_session.{$this->id}.id_provider_params", $this->params);

		// store config to be used by the end point
		Hybrid_Auth::storage()->config("CONFIG", Hybrid_Auth::$config);

		// move on
		Hybrid_Logger::debug("Hybrid_Provider_Adapter::login( {$this->id} ), redirect the user to login_start URL.");

		// redirect
		if (empty($this->params["redirect_mode"])) {
			Hybrid_Auth::redirect($this->params["login_start"]);	
		} else {
			Hybrid_Auth::redirect($this->params["login_start"],$this->params["redirect_mode"]);
		}
	}

	/**
	 * Let hybridauth forget all about the user for the current provider
	 * @return bool
	 */
	function logout() {
		$this->adapter->logout();
	}

	// --------------------------------------------------------------------

	/**
	 * Return true if the user is connected to the current provider
	 * @return bool
	 */
	public function isUserConnected() {
		return $this->adapter->isUserConnected();
	}

	// --------------------------------------------------------------------

	/**
	 * Call adapter methods defined in the adapter model:
	 *   getUserProfile()
	 *   getUserContacts()
	 *   getUserActivity()
	 *   setUserStatus()
	 *
	 * @param string $name      Method name
	 * @param array  $arguments Call arguments
	 * @return mixed
	 * @throws Exception
	 */
	public function __call($name, $arguments) {
		Hybrid_Logger::info("Enter Hybrid_Provider_Adapter::$name(), Provider: {$this->id}");

		if (!$this->isUserConnected()) {
			throw new Exception("User not connected to the provider {$this->id}.", 7);
		}

		if (!method_exists($this->adapter, $name)) {
			throw new Exception("Call to undefined function Hybrid_Providers_{$this->id}::$name().");
		}

    return call_user_func_array(array($this->adapter, $name), $arguments);
	}

	/**
	 * If the user is connected, then return the access_token and access_token_secret
	 * if the provider api use oauth
	 *
	 * <code>
	 * array(
	 *   'access_token' => '',
	 *   'access_token_secret' => '',
	 *   'refresh_token' => '',
	 *   'expires_in' => '',
	 *   'expires_at' => '',
	 * )
	 * </code>
	 * @return array
	 */
	public function getAccessToken() {
		if (!$this->adapter->isUserConnected()) {
			Hybrid_Logger::error("User not connected to the provider.");
			throw new Exception("User not connected to the provider.", 7);
		}

		return array(
			"access_token" => $this->adapter->token("access_token"), // OAuth access token
			"access_token_secret" => $this->adapter->token("access_token_secret"), // OAuth access token secret
			"refresh_token" => $this->adapter->token("refresh_token"), // OAuth refresh token
			"expires_in" => $this->adapter->token("expires_in"), // OPTIONAL. The duration in seconds of the access token lifetime
			"expires_at" => $this->adapter->token("expires_at"), // OPTIONAL. Timestamp when the access_token expire. if not provided by the social api, then it should be calculated: expires_at = now + expires_in
		);
	}

	/**
	 * Naive getter of the current connected IDp API client
	 * @return stdClass
	 * @throws Exception
	 */
	function api() {
		if (!$this->adapter->isUserConnected()) {
			Hybrid_Logger::error("User not connected to the provider.");

			throw new Exception("User not connected to the provider.", 7);
		}
		return $this->adapter->api;
	}

	/**
	 * Redirect the user to hauth_return_to (the callback url)
	 * @return void
	 */
	function returnToCallbackUrl() {
		// get the stored callback url
		$callback_url = Hybrid_Auth::storage()->get("hauth_session.{$this->id}.hauth_return_to");

		// if the user presses the back button in the browser and we already deleted the hauth_return_to from
		// the session in the previous request, we will redirect to '/' instead of displaying a blank page.
		if (!$callback_url) {
			$callback_url = '/';
		}

		// remove some unneeded stored data
		Hybrid_Auth::storage()->delete("hauth_session.{$this->id}.hauth_return_to");
		Hybrid_Auth::storage()->delete("hauth_session.{$this->id}.hauth_endpoint");
		Hybrid_Auth::storage()->delete("hauth_session.{$this->id}.id_provider_params");

		// back to home
		Hybrid_Auth::redirect($callback_url);
	}

	/**
	 * Return the provider config by id
	 *
	 * @param string $id Config key
	 * @return mixed
	 */
	function getConfigById($id) {
		if (isset(Hybrid_Auth::$config["providers"][$id])) {
			return Hybrid_Auth::$config["providers"][$id];
		}
		return null;
	}

	/**
	 * Return the provider config by id; case insensitive
	 *
	 * @param string $id Provider id
	 * @return mixed
	 */
	function getProviderCiId($id) {
		foreach (Hybrid_Auth::$config["providers"] as $idpid => $params) {
			if (strtolower($idpid) == strtolower($id)) {
				return $idpid;
			}
		}
		return null;
	}

}
