<?php

/*
 * This file is part of the GS Proteced Config TYPO3 Extension.
 *
 * Copyright (C) 2017 by Gilbertsoft (gilbertsoft.org)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * For the full license information, please read the LICENSE file that
 * was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace Gilbertsoft\ProtectedConfig;


use Gilbertsoft\ProtectedConfig\Utility;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * GS Proteced Config setup class.
 */
class Setup
{
	/**
	 * Executes the setup tasks if extension is installed.
	 *
	 * @param string $extensionKey Installed extension name
	 */
	public function afterInstall($extensionKey)
	{
		if ($extensionKey !== 'gsprotectedconfig') {
			return;
		}

		$this->updateAdditionalConfiguration($extensionKey);
	}

	/**
	 * Executes the setup tasks if extension is uninstalled.
	 *
	 * @param string $extensionKey Uninstalled extension name
	 */
	public function afterUninstall($extensionKey)
	{
		if ($extensionKey !== 'gsprotectedconfig') {
			return;
		}

		$this->removeAdditionalConfiguration($extensionKey);
	}

	/**
	 * Get the ConfigurationManager
     *
     * @return \TYPO3\CMS\Core\Configuration\ConfigurationManager
	 */
	protected function getConfigurationManager()
	{
		if (!isset($this->configurationManager)) {
			$this->configurationManager = GeneralUtility::makeInstance(ObjectManager::class)
				->get(ConfigurationManager::class);
		}
		return $this->configurationManager;
	}

	/**
	 * Returns the lines from AdditionalConfiguration.php file without own additions
	 */
	protected function getCleanAdditionalConfiguration($extensionKey)
	{
		$newLines = [];

		// Load content and search for the include
		if (Utility::readFile($this->getConfigurationManager()->getAdditionalConfigurationFileLocation(), $content) === true)
		{
			$currentLines = explode(LF, $content);

			// Delete the php marker line
			array_shift($currentLines);

			//
			$startFound = false;
			$endFound = false;

			foreach ($currentLines as $line)
			{
				if (($startFound) && (!$endFound)) {
					$endFound = (strpos($line, '}') !== false);
				} elseif ((!$startFound) && (strpos($line, $extensionKey) !== false)) {
					$startFound = true;
					$endFound = false;
				} else {
					$newLines[] = $line;
				}
			}
		}

		// Remove blank lines at the end
		$revLines = array_reverse($newLines);
		$newLines = [];
		$lineFound = false;

		foreach ($revLines as $line)
		{
			if ($lineFound || !empty(trim($line))) {
				$newLines[] = $line;
				$lineFound = true;
			}
		}

		return array_reverse($newLines);
	}

	/**
	 * Creates the AdditionalConfiguration.php file with necessary includes
	 */
	protected function updateAdditionalConfiguration($extensionKey)
	{
		$newLines = $this->getCleanAdditionalConfiguration($extensionKey);

		$newLines[] = '// Include AdditionalConfiguration.php from extension ' . $extensionKey;
		$newLines[] = '$_EXTKEY = \'' . $extensionKey . '\';';
		$newLines[] = 'if (@is_file(PATH_typo3conf . \'ext/\' . $_EXTKEY . \'/Configuration/AdditionalConfiguration.php\')) {';
		$newLines[] = '	require PATH_typo3conf . \'ext/\' . $_EXTKEY . \'/Configuration/AdditionalConfiguration.php\';';
		$newLines[] = '}';

		$this->getConfigurationManager()->writeAdditionalConfiguration($newLines);
	}

	/**
	 * Removes the added lines in the AdditionalConfiguration.php file
	 */
	protected function removeAdditionalConfiguration($extensionKey)
	{
		$newLines = $this->getCleanAdditionalConfiguration($extensionKey);

		$this->getConfigurationManager()->writeAdditionalConfiguration($newLines);
	}
}
