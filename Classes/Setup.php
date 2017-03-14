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
	 * @var string Extension key to listen on signals
	 */
	protected $extensionKey = '';

    /**
     * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
     */
    protected $configurationManager = null;

	/**
	 * Initializes the setup class.
	 *
	 * @param string $extensionKey Extension key to listen on signals
	 */
	public function __construct($extensionKey)
	{
		$this->$extensionKey = $extensionKey;
	}

	/**
	 * Executes the setup tasks if extension is installed.
	 *
	 * @param string $extensionKey Installed extension name
	 */
	public function afterInstall($extensionKey)
	{
		if ($extensionKey !== $this->$extensionKey) {
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
		if ($extensionKey !== $this->$extensionKey) {
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
	 * Reads $content to the file $file
	 *
	 * @param string $file Filepath to write to
	 * @param string $content Content to write
	 * @param bool $changePermissions If TRUE, permissions are forced to be set
	 * @return bool TRUE if the file was successfully opened and written to.
	 */
	protected function readFile($file, &$content)
	{
		if (!@is_file($file)) {
			return false;
		}

		if ($fd = fopen($file, 'rb')) {
			$content = fread($fd, filesize($file));
			fclose($fd);
			if ($content === false) {
				return false;
			}
			return true;
		}

		return false;
	}

	/**
	 * Returns the lines from AdditionalConfiguration.php file without own additions
	 */
	protected function getCleanAdditionalConfiguration($extensionKey)
	{
		$newLines = [];

		// Load content and search for the include
		if ($this->readFile($this->getConfigurationManager()->getAdditionalConfigurationFileLocation(), $content) === true)
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

		$newLines[] = '// Run  extension ' . $extensionKey . ' - added on ' . date(DATE_ATOM) . ' by setup';
		$newLines[] = 'if (class_exists(\'Gilbertsoft\ProtectedConfig\Configuration\Modifier\')) {';
		$newLines[] = '	\Gilbertsoft\ProtectedConfig\Configuration\Modifier::processLocalConfiguration(\'' . $extensionKey . '\');';
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
