<?php

/**
 * @license GPLv3, http://www.gnu.org/copyleft/gpl.html
 * @copyright Aimeos (aimeos.org), 2015
 * @package TYPO3_Aimeos
 */


namespace Gilbertsoft\ProtectedConfig;

use \TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Backend\Utility\BackendUtility;


/**
 * Aimeos distribution setup class.
 *
 * @package TYPO3_Aimeos
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
			$additionalConfigurationLines[] = "require_once('ext/gsprotectedconfig/Configuration/AdditionalConfiguration.php');";

			$configurationManager->writeAdditionalConfiguration($additionalConfigurationLines);
		}
	}
}
