<?php

/*
 * This file is part of the "GS Protected Config" Extension for TYPO3 CMS.
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

$EM_CONF[$_EXTKEY] = [
    'title' => 'GS Protected Config',
    'description' => 'GS Protected Config allows you to import configurations from outside the web directory or override it dependent from the application context or TYPO3 mode (FE, BE, CLI, AJAX and INSTALL). You can use this extension to move the website from dev to testing to production without changing something inside the web root if you place your settings outside. Templates can be found in Resources/Private/Templates. Configuration can simply be adapted by the Extension Manager and must be activated first!',
    'version' => '3.0.2',
    'category' => 'misc',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-0.0.0',
            'typo3' => '6.2.0-8.9.99',
            'gslib' => '0.0.5-0.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Simon Gilli',
    'author_email' => 'typo3@gilbertsoft.org',
    'author_company' => 'Gilbertsoft',
    'autoload' => [
        'psr-4' => [
            'Gilbertsoft\\ProtectedConfig\\' => 'Classes'
        ]
    ],
    'autoload-dev' => [
        'psr-4' => [
            'Gilbertsoft\\ProtectedConfig\\Tests\\' => 'Tests'
        ]
    ]
];
