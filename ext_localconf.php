<?php
defined('TYPO3_MODE') || die('Access denied.');

if (class_exists('Gilbertsoft\ProtectedConfig\Extension\Configurator')) {
    \Gilbertsoft\ProtectedConfig\Extension\Configurator::localconf($_EXTKEY);
}
