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

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;


/**
 * GS Proteced Config setup class.
 */
class Setup
{
	/**
	 * Executes the setup tasks if extension is installed.
	 *
	 * @param string|null $extname Installed extension name
	 */
	public function extensionInstalled($extensionKey = null)
	{
		if ($extensionKey !== 'gsprotectedconfig') {
			return;
		}

		$this->updateAdditionalConfiguration();
	}


	/**
	 * Creates the AdditionalConfiguration.php file with necessary includes
	 */
	protected function updateAdditionalConfiguration()
	{
		$configurationManager = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ConfigurationManager::class);

		if (\Gilbertsoft\ProtectedConfig\Utility::readFile($configurationManager->getAdditionalConfigurationFileLocation(), $content) === true) {
			$includeFound = (strpos($content, 'gsprotectedconfig') !== false);
			$additionalConfigurationLines = explode(LF, $content);
			array_shift($additionalConfigurationLines);
		} else {
			$includeFound = false;
			$additionalConfigurationLines = [];
		}

		if (!$includeFound) {
			$additionalConfigurationLines[] = '// Include AdditionalConfiguration.php from extension gsprotectedconfig';
			$additionalConfigurationLines[] = "if (@is_file('ext/gsprotectedconfig/Configuration/AdditionalConfiguration.php')) {";
			$additionalConfigurationLines[] = "	require('ext/gsprotectedconfig/Configuration/AdditionalConfiguration.php');";
			$additionalConfigurationLines[] = "}";

			$configurationManager->writeAdditionalConfiguration($additionalConfigurationLines);
		}
	}
}
