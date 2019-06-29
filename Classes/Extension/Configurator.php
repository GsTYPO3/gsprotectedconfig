<?php
namespace Gilbertsoft\ProtectedConfig\Extension;

/*
 * This file is part of the "GS Protected Config" Extension for TYPO3 CMS.
 *
 * Copyright (C) 2017-2019 by Gilbertsoft (gilbertsoft.org)
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

/**
 * Use declarations
 */
use Gilbertsoft\Lib\Extension\AbstractConfigurator;
use Gilbertsoft\ProtectedConfig\Service\InstallService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * The Configuration Modifier class.
 *
 * USE:
 * The class is intended to be used without creating an instance of it.
 * So: Do not instantiate - call functions with "\Gilbertsoft\ProtectedConfig\Configuration\Modifier::" prefixed the function name.
 * So use \Gilbertsoft\ProtectedConfig\Configuration\Modifier::[method-name] to refer to the functions, eg. '\Gilbertsoft\ProtectedConfig\Configuration\Modifier::processLocalConfiguration($extensionKey)'
 */
class Configurator extends AbstractConfigurator
{
    /**
     * @param string $string String to be converted to lowercase underscore
     * @return string lowercase_and_underscored_string
     */
    protected static function sanitizeValue(array &$conf, $value, $default)
    {
        if (!isset($conf[$value])) {
            $conf[$value] = $default;
        }
    }

    /**
     * @param string $extensionKey Extension key to load config from
     * @return array Sanitized extension configuration array
     */
    protected static function getSanitizedExtConf($extensionKey)
    {
        if (class_exists('TYPO3\CMS\Core\Configuration\ExtensionConfiguration')) {
            try {
                $conf = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)->get($extensionKey);
            } catch (\TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException $e) {
                $conf = [];
            }
        } else {
            $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extensionKey]);
            $conf = $conf ?? [];
        }

        self::sanitizeValue($conf, 'localConfigEnable', false);
        self::sanitizeValue($conf, 'localIncludeEnable', false);
        self::sanitizeValue($conf, 'localIncludeFileName', '');

        self::sanitizeValue($conf, 'contextConfigEnable', false);
        self::sanitizeValue($conf, 'contextIncludeEnable', false);
        self::sanitizeValue($conf, 'contextIncludePath', '');
        self::sanitizeValue($conf, 'contextExtendSiteName', '');
        self::sanitizeValue($conf, 'contextExtendDatabase', '');

        self::sanitizeValue($conf, 'feConfigEnable', false);
        self::sanitizeValue($conf, 'feIncludeEnable', false);
        self::sanitizeValue($conf, 'feIncludeFileName', '');

        self::sanitizeValue($conf, 'beConfigEnable', false);
        self::sanitizeValue($conf, 'beIncludeEnable', false);
        self::sanitizeValue($conf, 'beIncludeFileName', '');

        self::sanitizeValue($conf, 'cliConfigEnable', false);
        self::sanitizeValue($conf, 'cliIncludeEnable', false);
        self::sanitizeValue($conf, 'cliIncludeFileName', '');

        // override deprecated value
        $conf['cliResetCaching'] = false;

        self::sanitizeValue($conf, 'ajaxConfigEnable', false);
        self::sanitizeValue($conf, 'ajaxIncludeEnable', false);
        self::sanitizeValue($conf, 'ajaxIncludeFileName', '');

        self::sanitizeValue($conf, 'installConfigEnable', false);
        self::sanitizeValue($conf, 'installIncludeEnable', false);
        self::sanitizeValue($conf, 'installIncludeFileName', '');

        return $conf;
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function includeConfiguration($enable, $fileName)
    {
        // Include configuration file
        if ($enable == 1) {
            if (class_exists('TYPO3\CMS\Core\Core\Environment')) {
                $includeFile = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/' . $fileName;
            } else {
                $includeFile = PATH_site . $fileName;
            }

            if (@is_file($includeFile)) {
                require $includeFile;
            }
        }
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleLocalConfiguration($extConf)
    {
        // Include configuration file
        self::includeConfiguration($extConf['localIncludeEnable'], $extConf['localIncludeFileName']);
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleContextConfiguration($extConf)
    {
        // Application context configuration
        $context = GeneralUtility::getApplicationContext();

        // Update site name
        if ((($extConf['contextExtendSiteName'] & 1) && $context->isDevelopment()) ||
            (($extConf['contextExtendSiteName'] & 2) && $context->isTesting()) ||
            (($extConf['contextExtendSiteName'] & 4) && $context->isProduction())) {
            // Update site name based on context
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'] .= ' (' . (string)$context . ')';
        }

        // Update database
        if ((($extConf['contextExtendDatabase'] & 1) && $context->isDevelopment()) ||
            (($extConf['contextExtendDatabase'] & 2) && $context->isTesting()) ||
            (($extConf['contextExtendDatabase'] & 4) && $context->isProduction())) {
            // Update database based on context
            $GLOBALS['TYPO3_CONF_VARS']['DB']['database'] .= '_' . GeneralUtility::camelCaseToLowerCaseUnderscored((string)$context);
        }

        // Include configuration file
        self::includeConfiguration($extConf['contextIncludeEnable'], $extConf['contextIncludePath'] . GeneralUtility::camelCaseToLowerCaseUnderscored((string)$context) . '.php');
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleFeConfiguration($extConf)
    {
        self::includeConfiguration($extConf['feIncludeEnable'], $extConf['feIncludeFileName']);
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleBeConfiguration($extConf)
    {
        self::includeConfiguration($extConf['beIncludeEnable'], $extConf['beIncludeFileName']);
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleCliConfiguration($extConf)
    {
        self::includeConfiguration($extConf['cliIncludeEnable'], $extConf['cliIncludeFileName']);
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleAjaxConfiguration($extConf)
    {
        self::includeConfiguration($extConf['ajaxIncludeEnable'], $extConf['ajaxIncludeFileName']);
    }

    /**
     * @param array $extConf Extension configuration array
     * @return void
     */
    protected static function handleInstallConfiguration($extConf)
    {
        self::includeConfiguration($extConf['installIncludeEnable'], $extConf['installIncludeFileName']);
    }

    /**
     * Called from ext_localconf.php.
     *
     * @param string $extensionKey Extension key
     * @return void
     */
    public static function localconf($extensionKey)
    {
        InstallService::registerService($extensionKey);
    }

    /**
     * Called from ext_tables.php.
     *
     * @param string $extensionKey Extension key
     * @return void
     */
    public static function tables($extensionKey)
    {
    }

    /**
     * Called from additionalConfiguration.php.
     *
     * @param string $extensionKey Extension key
     * @return void
     */
    public static function additionalConfiguration($extensionKey)
    {
        // Get the configuration
        $extConf = self::getSanitizedExtConf($extensionKey);

        if ($extConf['localConfigEnable'] == 1) {
            self::handleLocalConfiguration($extConf);
        }

        if ($extConf['contextConfigEnable'] == 1) {
            self::handleContextConfiguration($extConf);
        }

        if (($extConf['feConfigEnable'] == 1) && self::isFrontend()) {
            self::handleFeConfiguration($extConf);
        }

        if (($extConf['beConfigEnable'] == 1) && self::isBackend()) {
            self::handleBeConfiguration($extConf);
        }

        if (($extConf['cliConfigEnable'] == 1) && self::isCli()) {
            self::handleCliConfiguration($extConf);
        }

        if (($extConf['ajaxConfigEnable'] == 1) && self::isAjax()) {
            self::handleAjaxConfiguration($extConf);
        }

        if (($extConf['installConfigEnable'] == 1) && self::isInstall()) {
            self::handleInstallConfiguration($extConf);
        }
    }
}
