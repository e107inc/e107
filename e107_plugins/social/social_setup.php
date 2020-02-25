<?php
/**
 * e107 website system
 *
 * Copyright (C) 2008-2020 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

require_once("SocialLoginConfigManager.php");

class social_setup
{
	public function upgrade_required()
	{
		return (
			$this->upgrade_required_provider_name_normalization() ||
			$this->upgrade_required_steam_xup_bug()
		);
	}

	private function upgrade_required_provider_name_normalization()
	{
		$coreConfig = e107::getConfig();
		$manager = new SocialLoginConfigManager($coreConfig);
		$providerConfig = $coreConfig->getPref(SocialLoginConfigManager::SOCIAL_LOGIN_PREF);
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

	/**
	 * @see https://github.com/e107inc/e107/pull/4099#issuecomment-590579521
	 */
	private function upgrade_required_steam_xup_bug()
	{
		$db = e107::getDb();
		$count = $db->count('user', '(*)', "user_xup LIKE 'Steam_https://steamcommunity.com/openid/id/%'");
		return $count >= 1;
	}

	public function upgrade_pre()
	{
		$this->upgrade_pre_provider_name_normalization();
		$this->upgrade_pre_steam_xup_bug();
	}

	private function upgrade_pre_provider_name_normalization()
	{
		$coreConfig = e107::getConfig();
		$logger = e107::getMessage();
		$manager = new SocialLoginConfigManager($coreConfig);

		$providerConfig = $coreConfig->getPref(SocialLoginConfigManager::SOCIAL_LOGIN_PREF);
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
					SocialLoginConfigManager::SOCIAL_LOGIN_PREF . '/' . $oldNormalizedProviderName
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
		switch ($denormalizedProviderName)
		{
			case 'AOL':
				$denormalizedProviderName = 'AOLOpenID';
				break;
			case 'Live':
				$denormalizedProviderName = 'WindowsLive';
				break;
		}
		return $denormalizedProviderName;
	}

	/**
	 * @see https://github.com/e107inc/e107/pull/4099#issuecomment-590579521
	 */
	private function upgrade_pre_steam_xup_bug()
	{
		$logger = e107::getMessage();
		$db = e107::getDb();
		$db->select('user', '*', "user_xup LIKE 'Steam_https://steamcommunity.com/openid/id/%'");
		$rows = $db->rows();
		foreach ($rows as $row)
		{
			$old_user_xup = $row['user_xup'];
	        $new_user_xup = str_ireplace(
	        	['http://steamcommunity.com/openid/id/', 'https://steamcommunity.com/openid/id/'],
				'',
				$old_user_xup
			);
	        $status = $db->update(
	        	'user',
				"user_xup = '".$db->escape($new_user_xup)."' WHERE user_id = ".$db->escape($row['user_id'])
			);
	        if ($status !== 1)
			{
				$logger->addError(
					"Unexpected error while correcting user_xup of user_id = ".$row['user_id']." from \"".$old_user_xup."\" to \"".$new_user_xup."\": ".
					$db->getLastErrorText()
				);
			}
	        else
			{
				$logger->addSuccess("Corrected user_xup of user_id = ".$row['user_id']." from \"".$old_user_xup."\" to \"".$new_user_xup."\"");
			}
		}
	}
}