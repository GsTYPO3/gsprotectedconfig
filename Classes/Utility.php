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


use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;
use TYPO3\CMS\Core\Imaging\GraphicalFunctions;
use TYPO3\CMS\Core\Service\OpcodeCacheService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Page\PageRepository;


/**
 * The legendary "t3lib_div" class - Miscellaneous functions for general purpose.
 * Most of the functions do not relate specifically to TYPO3
 * However a section of functions requires certain TYPO3 features available
 * See comments in the source.
 * You are encouraged to use this library in your own scripts!
 *
 * USE:
 * The class is intended to be used without creating an instance of it.
 * So: Don't instantiate - call functions with "\TYPO3\CMS\Core\Utility\GeneralUtility::" prefixed the function name.
 * So use \TYPO3\CMS\Core\Utility\GeneralUtility::[method-name] to refer to the functions, eg. '\TYPO3\CMS\Core\Utility\GeneralUtility::milliseconds()'
 */
class Utility
{
	/**
	 * Returns a given CamelCasedString as an lowercase string with underscores.
	 * Example: Converts BlogExample to blog_example, and minimalValue to minimal_value
	 *
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
	 * Returns a given CamelCasedString as an lowercase string with underscores.
	 * Example: Converts BlogExample to blog_example, and minimalValue to minimal_value
	 *
	 * @param string $string String to be converted to lowercase underscore
	 * @return string lowercase_and_underscored_string
	 */
	public static function getSanitizedExtConf()
	{
		$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['gsprotectedconfig']);

		self::sanitizeValue($conf, 'localConfigEnable', false);
		self::sanitizeValue($conf, 'localIncludeEnable', false);
		self::sanitizeValue($conf, 'localIncludeFileName', '');
		self::sanitizeValue($conf, 'contextConfigEnable', false);
		self::sanitizeValue($conf, 'contextIncludeEnable', false);
		self::sanitizeValue($conf, 'contextIncludePath', '');
		self::sanitizeValue($conf, 'contextExtendSiteName', '');
		self::sanitizeValue($conf, 'contextExtendDatabase', '');
		self::sanitizeValue($conf, 'cliConfigEnable', false);
		self::sanitizeValue($conf, 'cliIncludeEnable', false);
		self::sanitizeValue($conf, 'cliIncludeFileName', '');
		self::sanitizeValue($conf, 'cliResetCaching', false);

		return $conf;
	}

	/**
	 * Reads $content to the file $file
	 *
	 * @param string $file Filepath to write to
	 * @param string $content Content to write
	 * @param bool $changePermissions If TRUE, permissions are forced to be set
	 * @return bool TRUE if the file was successfully opened and written to.
	 */
	public static function readFile($file, &$content)
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
}
