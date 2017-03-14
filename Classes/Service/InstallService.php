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

namespace Gilbertsoft\ProtectedConfig\Service;


use Gilbertsoft\ProtectedConfig\Utility;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;


/**
 * GS Proteced Config Install Service class.
 */
class InstallService
{
	/**
	 * @var string Extension key to listen on signals
	 */
	protected $extensionKey = 'gsprotectedconfig';

    /**
     * @var \TYPO3\CMS\Core\Configuration\ConfigurationManager
     */
    protected $configurationManager = null;

    /**
     * @var string
     */
    protected $messageQueueByIdentifier = '';

	/**
	 * Initializes the install service
	 *
	 * @param string $extensionKey Extension key to listen on signals
	 */
	public function __construct($extensionKey)
	{
		$this->extensionKey = $extensionKey;
        $this->messageQueueByIdentifier = 'extbase.flashmessages.tx_extensionmanager_tools_extensionmanagerextensionmanager';
	}

	/**
	 * Executes the setup tasks if extension is installed.
	 *
	 * @param string $extensionKey Installed extension name
	 */
	public function afterInstall($extensionKey)
	{
        if ($extensionKey == $this->extensionKey)
        {
			$this->updateAdditionalConfiguration($extensionKey);
		}
	}

	/**
	 * Executes the setup tasks if extension is uninstalled.
	 *
	 * @param string $extensionKey Uninstalled extension name
	 */
	public function afterUninstall($extensionKey)
	{
        if ($extensionKey == $this->extensionKey)
        {
			$this->removeAdditionalConfiguration($extensionKey);
		}
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
		if (($content = GeneralUtility::getUrl($this->getConfigurationManager()->getAdditionalConfigurationFileLocation())) !== false)
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
        /**
         * Add Flashmessage that the configuration has to be checked
         */
        $flashMessage = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
            'To enable the features you have to edit the extensions configuration in the Extension Manager. After the new configuration is saved please relogin to the backend to ensure the changes!',
            'Check your configuration',
            FlashMessage::NOTICE,
            true
        );
        $this->addFlashMessage($flashMessage);

        /**
         * Update configuration
         */
		$newLines = $this->getCleanAdditionalConfiguration($extensionKey);

		if (!empty(end($newLines))) {
			$newLines[] = '';
		}

		$newLines[] = '// Run configuration modifier for extension ' . $extensionKey . ' - added on ' . date(DATE_ATOM) . ' by ' . __CLASS__;
		$newLines[] = 'if (class_exists(\'Gilbertsoft\ProtectedConfig\Configuration\Modifier\')) {';
		$newLines[] = '	\Gilbertsoft\ProtectedConfig\Configuration\Modifier::processLocalConfiguration(\'' . $extensionKey . '\');';
		$newLines[] = '}';

		$this->getConfigurationManager()->writeAdditionalConfiguration($newLines);

        /**
         * Add Flashmessage that the configuration has written
         */
        $flashMessage = GeneralUtility::makeInstance(
            'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
            'Configuration successfully added to ' .
            $this->getConfigurationManager()->getAdditionalConfigurationFileLocation() .
            '.',
            'Configuration added',
            FlashMessage::OK,
            true
        );
        $this->addFlashMessage($flashMessage);
	}

	/**
	 * Removes the added lines in the AdditionalConfiguration.php file
	 */
	protected function removeAdditionalConfiguration($extensionKey)
	{
		$newLines = $this->getCleanAdditionalConfiguration($extensionKey);

		if (empty($newLines) !== true)
		{
			$this->getConfigurationManager()->writeAdditionalConfiguration($newLines);

			/**
			 * Add Flashmessage that the configuration has written
			 */
			$flashMessage = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
				'Configuration successfully removed from ' .
				$this->getConfigurationManager()->getAdditionalConfigurationFileLocation() .
				'.',
				'Configuration removed',
				FlashMessage::OK,
				true
			);
			$this->addFlashMessage($flashMessage);
		}
		else
		{
			unlink($this->getConfigurationManager()->getAdditionalConfigurationFileLocation());

			/**
			 * Add Flashmessage that the configuration has deleted
			 */
			$flashMessage = GeneralUtility::makeInstance(
				'TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
				'File ' .
				$this->getConfigurationManager()->getAdditionalConfigurationFileLocation() .
				' successfully deleted.',
				'Configuration delete',
				FlashMessage::OK,
				true
			);
			$this->addFlashMessage($flashMessage);
		}
	}

    /**
     * Adds a Flash Message to the Flash Message Queue
     *
     * @param FlashMessage $flashMessage
     */
    protected function addFlashMessage(FlashMessage $flashMessage)
    {
        if ($flashMessage) {
            /** @var $flashMessageService \TYPO3\CMS\Core\Messaging\FlashMessageService */
            $flashMessageService = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessageService');
            /** @var $flashMessageQueue \TYPO3\CMS\Core\Messaging\FlashMessageQueue */
            $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier($this->messageQueueByIdentifier);
            $flashMessageQueue->enqueue($flashMessage);
        }
    }
}
