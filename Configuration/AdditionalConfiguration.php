<?php

// Get the configuration
$gsEXTCONF = \Gilbertsoft\ProtectedConfig\Utility::getSanitizedExtConf();

// Local configuration
if ($gsEXTCONF['localConfigEnable'] == 1)
{
	// Include configuration file
	if ($gsEXTCONF['localIncludeEnable'] == 1)
	{
		$settingsFile = PATH_site . $gsEXTCONF['localIncludeFileName'];

		if (file_exists($settingsFile)) {
			require_once($settingsFile);
		}

		unset($settingsFile);
	}
}

// Application context configuration
if ($gsEXTCONF['contextConfigEnable'] == 1)
{
	$currentApplicationContext = \TYPO3\CMS\Core\Utility\GeneralUtility::getApplicationContext();

	// Update site name
	if (($gsEXTCONF['contextExtendSiteName'] == 1) || (
		($gsEXTCONF['contextExtendSiteName'] == 0) && (!$currentApplicationContext->isProduction())
	   ))
	{
		// Update site name based on context
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] .= ' (' . (string)$currentApplicationContext . ')';
	}

	// Update database
	if (($gsEXTCONF['contextExtendDatabase'] == 1) || (
		($gsEXTCONF['contextExtendDatabase'] == 0) && (!$currentApplicationContext->isProduction())
	   ))
	{
		// Update database based on context
		$GLOBALS['TYPO3_CONF_VARS']['DB']['database'] .= '_' . \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored((string)$currentApplicationContext);
	}

	// Include configuration file
	if ($gsEXTCONF['contextIncludeEnable'] == 1)
	{
		$contextConfigFile = PATH_site . $gsEXTCONF['contextIncludePath'] . \TYPO3\CMS\Core\Utility\GeneralUtility::camelCaseToLowerCaseUnderscored((string)$currentApplicationContext) . '.php';

		if (file_exists($contextConfigFile)) {
			require_once($contextConfigFile);
		}

		unset($contextConfigFile);
	}
}

// CLI mode configuration
if ((TYPO3_cliMode === true) && ($gsEXTCONF['cliConfigEnable'] == 1))
{
	// Reset caching
	if ($gsEXTCONF['cliResetCaching'] == 1) {
		// Change cache config to database backend
  		$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['extbase_object']['backend'] = \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class;
	}

	// Include configuration file
	if ($gsEXTCONF['cliIncludeEnable'] == 1)
	{
		$settingsFile = PATH_site . $gsEXTCONF['cliIncludeFileName'];

		if (file_exists($settingsFile)) {
			require_once($settingsFile);
		}

		unset($settingsFile);
	}
}

// Unset EXTCONF variable
unset($gsEXTCONF);
