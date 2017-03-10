<?php
defined('TYPO3_MODE') || die('Access denied.');

if (TYPO3_MODE === 'BE') {
    call_user_func(
        function ($extKey) {
			/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
			$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
			$signalSlotDispatcher->connect(
				\TYPO3\CMS\Extensionmanager\Service\ExtensionManagementService::class,
				'hasInstalledExtensions',
				\Gilbertsoft\ProtectedConfig\Setup::class,
				'extensionInstalled'
			);
        },
        $_EXTKEY
    );
}
