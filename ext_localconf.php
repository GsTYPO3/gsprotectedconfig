<?php
defined('TYPO3_MODE') || die('Access denied.');

if (TYPO3_MODE === 'BE') {
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
	$signalSlotDispatcher->connect(
		\TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
		'afterExtensionInstall',
		\Gilbertsoft\ProtectedConfig\Setup::class,
		'afterInstall'
	);
	$signalSlotDispatcher->connect(
		\TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
		'afterExtensionUninstall',
		\Gilbertsoft\ProtectedConfig\Setup::class,
		'afterUninstall'
	);
}
