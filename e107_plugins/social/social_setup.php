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
		$coreConfig = e107::getConfig();
		$manager = new SocialLoginConfigManager($coreConfig);
		$providerConfig = $coreConfig->getPref(SocialLoginConfigManager::SOCIAL_LOGIN_PREF);
		$normalizedProviderNames = array_keys($providerConfig);
		foreach ($normalizedProviderNames as $normalizedProviderName)
		{
			$actualNormalizedProviderName =
				$manager->normalizeProviderName($manager->denormalizeProviderName($normalizedProviderName));
			if ($actualNormalizedProviderName !== $normalizedProviderName) return true;
		}
		return false;
	}

	public function upgrade_pre()
	{
		$coreConfig = e107::getConfig();
		$logger = e107::getMessage();
		$manager = new SocialLoginConfigManager($coreConfig);

		$providerConfig = $coreConfig->getPref(SocialLoginConfigManager::SOCIAL_LOGIN_PREF);

		foreach ($providerConfig as $oldNormalizedProviderName => $oldOptions)
		{
			$denormalizedProviderName = $manager->denormalizeProviderName($oldNormalizedProviderName);
			$denormalizedProviderName = $this->upgradeDenormalizedProviderQuirks($denormalizedProviderName);
			$actualNormalizedProviderName = $manager->normalizeProviderName($denormalizedProviderName);

			$newOptions = $oldOptions;
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
}