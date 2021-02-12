<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once(e_HANDLER . "user_handler.php");

class social_login_config
{
	const SOCIAL_LOGIN_PREF = "social_login";

	const SOCIAL_LOGIN_FLAGS = "social_login_active";
	const ENABLE_BIT_GLOBAL = 0;
	const ENABLE_BIT_TEST_PAGE = 1;

	/**
	 * @var e_pref
	 */
	private $config;
	private $supportedProvidersCache = null;

	/**
	 * SocialLoginHandler constructor.
	 * @param $config e_pref
	 */
	public function __construct($config)
	{
		$this->config = $config;
	}

	/**
	 * Check a social login boolean (toggle) setting
	 *
	 * For backwards compatibility, if the global bit (0) is off, no other bits can be on.
	 *
	 * @param int $bit Which setting to check
	 * @return boolean TRUE if the setting is enabled, FALSE otherwise
	 */
	public function isFlagActive($bit = self::ENABLE_BIT_GLOBAL)
	{
		$flags = $this->config->get(self::SOCIAL_LOGIN_FLAGS);
		if (!($flags & 1 << self::ENABLE_BIT_GLOBAL)) return false;
		return (bool)($flags & 1 << $bit);
	}

	/**
	 * Set a social login boolean (toggle) setting
	 *
	 * For backwards compatibility, if the global bit (0) is off, no other bits can be on.
	 *
	 * @param int $bit Which setting to change
	 * @param boolean $active TRUE to enable the setting, FALSE to disable the setting
	 */
	public function setFlag($bit, $active)
	{
		$flags = $this->config->get(self::SOCIAL_LOGIN_FLAGS);
		if (!is_numeric($flags)) $flags = 0x0;

		$flags = $flags & ~(1 << $bit) | ($active << $bit);

		if (!($flags & 1 << self::ENABLE_BIT_GLOBAL)) $this->config->set(self::SOCIAL_LOGIN_FLAGS, 0x0);
		else $this->config->set(self::SOCIAL_LOGIN_FLAGS, $flags);
		$this->saveConfig();
	}

	/**
	 * Checks whether the specified social login provider is enabled
	 * @param $providerName string The un-normalized name of the provider to check
	 * @return bool Whether the specified provider is enabled
	 */
	public function isProviderEnabled($providerName)
	{
		$result = $this->getProviderConfig($providerName, "/enabled");
		return (bool)$result;
	}

	/**
	 * Disable and remove the specified social login provider
	 * @param $providerName string The un-normalized name of the provider to forget
	 */
	public function forgetProvider($providerName)
	{
		$this->config->removePref(self::SOCIAL_LOGIN_PREF . '/' . $this->normalizeProviderName($providerName));
	}

	/**
	 * Overwrite the entire social login provider configuration with the specified options
	 *
	 * Does not commit to database.
	 *
	 * @param $providerName string The un-normalized name of the social login provider
	 * @param $options array Associative array of options
	 *        $options['enabled'] bool Whether the social login provider is enabled
	 *        $options['keys'] array Authentication app keys
	 *        $options['keys']['id'] string The OAuth1 client key or OAuth2 client ID
	 *        $options['keys']['secret'] string The OAuth1 or OAuth2 client secret
	 *        $options['scope'] string OAuth2 scopes, space-delimited
	 * @see social_login_config::saveConfig() to commit to database.
	 *
	 */
	public function setProviderConfig($providerName, $options)
	{
		$config = $this->config->get(self::SOCIAL_LOGIN_PREF);
		if (!is_array($config)) $this->config->set(self::SOCIAL_LOGIN_PREF, []);

		self::array_unset_empty_recursive($options);

		if (empty($options)) $this->forgetProvider($providerName);
		else $this->config->setPref(
			self::SOCIAL_LOGIN_PREF . '/' . $this->normalizeProviderName($providerName),
			$options
		);
	}

	private static function array_unset_empty_recursive(&$array)
	{
		foreach ($array as $key => &$value)
		{
			if (is_array($value))
			{
				$arraySize = self::array_unset_empty_recursive($value);
				if (!$arraySize)
				{
					unset($array[$key]);
				}
			}
			else if (empty($array[$key]))
			{
				unset($array[$key]);
			}
		}
		return count($array);
	}

	public function saveConfig()
	{
		$this->config->save(true, false, false);
	}

	/**
	 * Get the social login provider configuration currently stored in the database
	 * @param $providerName string The un-normalized name of the social login provider
	 * @param $path string Nested array keys, slash-delimited ("/")
	 * @return array|mixed The configuration of the specified provider, or the value of the $path
	 */
	public function getProviderConfig($providerName, $path = "")
	{
		if (empty($path)) $path = "";
		elseif (substr($path, 0, 1) !== "/") $path = "/$path";

		$pref = $this->config->getPref(
			self::SOCIAL_LOGIN_PREF . '/' . $this->normalizeProviderName($providerName) . $path
		);

		return $pref;
	}

	/**
	 * Get configs of providers that are supported and configured
	 * @return array Associative array where the key is the denormalized provider name and the value is its config
	 */
	public function getValidConfiguredProviderConfigs()
	{
		$supported_providers = $this->getSupportedProviders();
		$configured_providers = $this->getConfiguredProviders();
		$unsupported_providers = array_diff($configured_providers, $supported_providers);
		$configured_providers = array_diff($configured_providers, $unsupported_providers);

		$provider_configs = [];
		foreach ($configured_providers as $configured_provider)
		{
			$provider_configs[$configured_provider] =
				$this->getProviderConfig($configured_provider);
		}

		return $provider_configs;
	}

	/**
	 * Get the social login providers for which we have adapters
	 * @return array String list of supported providers. Empty if Hybridauth is broken.
	 */
	public function getSupportedProviders()
	{
		if ($this->supportedProvidersCache === null)
			$this->supportedProvidersCache = e_user_provider::getSupportedProviders();
		return $this->supportedProvidersCache;
	}

	/**
	 * Get the type of provider from a provider name
	 * @param $providerName string Name of the supported social login provider
	 * @return string|bool "OAuth1", "OAuth2", or "OpenID". If false, the provider name is invalid.
	 *                     Other values are technically possible but not supported.
	 */
	public function getTypeOfProvider($providerName)
	{
		return e_user_provider::getTypeOf($providerName);
	}

	/**
	 * Get standard and supplementary fields of the specified provider
	 * @param $providerName string Name of the supported social login provider
	 * @return array Multidimensional associative array where the keys are the known field names and the values are a
	 *               description of what their key is for.  Keys can be nested in parent keys.  Parent keys will not
	 *               have a description of the key.  All fields take a string value.  Return will be empty if the
	 *               specified provider does not have any known fields.
	 */
	public function getFieldsOf($providerName)
	{
		return e_user_provider::getFieldsOf($providerName);
	}

	/**
	 * Get the providers that are currently configured in the core preferences
	 * @return array String list of configured provider names
	 */
	public function getConfiguredProviders()
	{
		$output = [];
		$social_login_config = $this->getSocialLoginConfig();
		$configured_providers = array_keys($social_login_config);
		foreach ($configured_providers as $configured_provider)
		{
			$output[] = $this->denormalizeProviderName($configured_provider);
		}
		sort($output);
		return $output;
	}

	protected function getSocialLoginConfig()
	{
		$config = $this->config->get(self::SOCIAL_LOGIN_PREF);
		if (!is_array($config)) $config = [];

		return $config;
	}

	/**
	 * Turn a provider name into one fit for storage in the database (core preferences)
	 * @return string Normalized social login provider name
	 */
	public function normalizeProviderName($providerName)
	{
		$normalizedProviderName = $providerName;
		foreach ($this->getSupportedProviders() as $providerProperCaps)
		{
			if (mb_strtolower($providerName) == mb_strtolower($providerProperCaps))
			{
				$normalizedProviderName = $providerProperCaps;
				break;
			}
		}
		$providerType = $this->getTypeOfProvider($normalizedProviderName);
		$normalizedProviderName = preg_replace('/(OpenID|OAuth1|OAuth2)$/i', '', $normalizedProviderName);
		if (empty($normalizedProviderName) && !empty($providerType) || $providerName == $providerType)
			return $providerType;
		elseif ($providerType)
			return "{$normalizedProviderName}-{$providerType}";
		return $providerName;
	}

	/**
	 * Turn a normalized provider name into a Hybridauth-compatible adapter name
	 * @param $normalizedProviderName string Provider name stored in the database
	 * @return string Hybridauth-compatible adapter name. May not necessarily exist in Hybridauth.
	 */
	public function denormalizeProviderName($normalizedProviderName)
	{
		list($provider_name, $provider_type) = array_pad(explode("-", $normalizedProviderName), 2, "");
		if ($provider_type != $this->getTypeOfProvider($provider_name)) $provider_name .= $provider_type;
		return $provider_name;
	}
}