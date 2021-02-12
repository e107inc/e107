<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("includes/social_login_config.php");

class social_setup
{
	const RENAMED_PROVIDERS = [
		'AOL' => 'AOLOpenID',
		'Github' => 'GitHub',
		'Live' => 'WindowsLive',
	];

	public function upgrade_required()
	{
		return (
			$this->upgrade_required_provider_name_normalization() ||
			$this->upgrade_required_rename_xup() ||
			$this->upgrade_required_steam_xup_bug()
		);
	}

	private function upgrade_required_provider_name_normalization()
	{
		$coreConfig = e107::getConfig();
		$manager = new social_login_config($coreConfig);
		$providerConfig = $coreConfig->getPref(social_login_config::SOCIAL_LOGIN_PREF);
		if (!is_array($providerConfig)) $providerConfig = [];
		$normalizedProviderNames = array_keys($providerConfig);
		foreach ($normalizedProviderNames as $normalizedProviderName)
		{
			$actualNormalizedProviderName =
				$manager->normalizeProviderName($manager->denormalizeProviderName($normalizedProviderName));
			if ($actualNormalizedProviderName !== $normalizedProviderName) return true;
		}
		return false;
	}

	private function upgrade_required_rename_xup()
	{
		$db = e107::getDb();
		$whereSegment = array_map(function ($oldProviderName)
		{
			return "user_xup LIKE BINARY '{$oldProviderName}\_%'";
		}, array_keys(self::RENAMED_PROVIDERS));
		$count = $db->count('user', '(*)', implode(' OR ', $whereSegment));
		return $count >= 1;
	}

	/**
	 * @see https://github.com/e107inc/e107/pull/4099#issuecomment-590579521
	 */
	private function upgrade_required_steam_xup_bug()
	{
		$db = e107::getDb();
		$count = $db->count('user', '(*)', "user_xup LIKE 'Steam\_https://steamcommunity.com/openid/id/%'");
		return $count >= 1;
	}

	public function upgrade_pre()
	{
		$this->upgrade_pre_provider_name_normalization();
		$this->upgrade_pre_rename_xup();
		$this->upgrade_pre_steam_xup_bug();
	}

	private function upgrade_pre_provider_name_normalization()
	{
		$coreConfig = e107::getConfig();
		$logger = e107::getMessage();
		$manager = new social_login_config($coreConfig);

		$providerConfig = $coreConfig->getPref(social_login_config::SOCIAL_LOGIN_PREF);
		if (!is_array($providerConfig)) $providerConfig = [];

		foreach ($providerConfig as $oldNormalizedProviderName => $oldOptions)
		{
			$denormalizedProviderName = $manager->denormalizeProviderName($oldNormalizedProviderName);
			$denormalizedProviderName = $this->upgradeDenormalizedProviderQuirks($denormalizedProviderName);
			$actualNormalizedProviderName = $manager->normalizeProviderName($denormalizedProviderName);

			$newOptions = $oldOptions;
			/* Commented out because there are no known options to migrate from HybridAuth 2 to Hybridauth 3
			if (isset($newOptions['keys']['key']))
			{
				$newOptions['keys']['id'] = $newOptions['keys']['key'];
				unset($newOptions['keys']['key']);
			}

			if ($newOptions != $oldOptions)
			{
				$manager->setProviderConfig($denormalizedProviderName, $newOptions);
				$logger->addSuccess(
					"Updated configuration format of social login provider $denormalizedProviderName"
				);
			}
			*/

			if ($actualNormalizedProviderName !== $oldNormalizedProviderName)
			{
				$manager->setProviderConfig($denormalizedProviderName, $newOptions);
				$coreConfig->removePref(
					social_login_config::SOCIAL_LOGIN_PREF . '/' . $oldNormalizedProviderName
				);
				$logger->addSuccess(
					"Updated name of social login provider $oldNormalizedProviderName → $actualNormalizedProviderName"
				);
			}
		}

		$manager->saveConfig();
	}

	private function upgradeDenormalizedProviderQuirks($denormalizedProviderName)
	{
		$renamedProviders = self::RENAMED_PROVIDERS;
		if (isset($renamedProviders[$denormalizedProviderName])) return $renamedProviders[$denormalizedProviderName];
		return $denormalizedProviderName;
	}

	private function upgrade_pre_rename_xup()
	{
		$db = e107::getDb();
		foreach (self::RENAMED_PROVIDERS as $oldProviderName => $newProviderName)
		{
			$db->select('user', '*', "user_xup LIKE '{$oldProviderName}\_%'");
			$rows = $db->rows();
			foreach ($rows as $row)
			{
				$old_user_xup = $row['user_xup'];
				$new_user_xup = preg_replace(
					'/^' . preg_quote($oldProviderName) . '_/',
					$newProviderName . '_',
					$old_user_xup
				);
				$this->fixUserXup($db, $row['user_id'], $old_user_xup, $new_user_xup);
			}
		}
	}

	/**
	 * @see https://github.com/e107inc/e107/pull/4099#issuecomment-590579521
	 */
	private function upgrade_pre_steam_xup_bug()
	{
		$db = e107::getDb();
		$db->select('user', '*', "user_xup LIKE 'Steam\_https://steamcommunity.com/openid/id/%'");
		$rows = $db->rows();
		foreach ($rows as $row)
		{
			$old_user_xup = $row['user_xup'];
	        $new_user_xup = str_ireplace(
	        	['http://steamcommunity.com/openid/id/', 'https://steamcommunity.com/openid/id/'],
				'',
				$old_user_xup
			);
			$this->fixUserXup($db, $row['user_id'], $old_user_xup, $new_user_xup);
		}
	}

	/**
	 * @param e_db_mysql $db
	 * @param string $user_id
	 * @param string $old_user_xup
	 * @param string $new_user_xup
	 */
	private function fixUserXup($db, $user_id, $old_user_xup, $new_user_xup)
	{
		$logger = e107::getMessage();
		$status = $db->update(
			'user',
			"user_xup = '" . $db->escape($new_user_xup) . "' WHERE user_id = " . $db->escape($user_id)
		);
		if ($status !== 1)
		{
			$logger->addError(
				"Unexpected error while correcting user_xup of user_id = " . $user_id . " from \"" . $old_user_xup . "\" to \"" . $new_user_xup . "\": " .
				$db->getLastErrorText()
			);
		}
		else
		{
			$logger->addSuccess("Corrected user_xup of user_id = " . $user_id . " from \"" . $old_user_xup . "\" to \"" . $new_user_xup . "\"");
		}
	}
}