<?php

$EM_CONF[$_EXTKEY] = [
    'title' => '[NITSAN] TYPO3 + Zoho CRM',
    'description' => 'Easily install and configure your powermail form with zoho CRM, read more at documentation. Live-Demo: https://demo.t3terminal.com/t3t-extensions/ You can download PRO version for more-features & free-support at https://t3terminal.com/zoho-crm-typo3-extension-free',
    'category' => 'plugin',
    'author' => 'T3: Milan Rathod, QA: Siddharth Sheth',
    'author_email' => 'sanjay@nitsan.in',
    'author_company' => 'NITSAN Technologies Pvt Ltd',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-10.9.99',
            'powermail' => '2.1.0-9.5.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

