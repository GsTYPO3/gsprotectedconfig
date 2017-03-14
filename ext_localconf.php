<?php
defined('TYPO3_MODE') || die('Access denied.');

if (TYPO3_MODE === 'BE') {
	$signalSlot = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\Gilbertsoft\ProtectedConfig\Service\InstallService::class, $_EXTKEY);
	$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
	$signalSlotDispatcher->connect(
		\TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
		'afterExtensionInstall',
		$signalSlot,
		'afterInstall'
	);
	$signalSlotDispatcher->connect(
		\TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
		'afterExtensionUninstall',
		$signalSlot,
		'afterUninstall'
	);
}
