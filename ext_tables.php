<?php
defined('TYPO3_MODE') || die('Access denied.');
$_EXTKEY = 'ns_zoho_crm';
call_user_func(
    function($extKey)
    {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($extKey, 'Configuration/TypoScript', 'Zoho Api Integration');
    },
    $_EXTKEY
);
