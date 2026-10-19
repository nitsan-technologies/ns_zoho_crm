<?php

$EM_CONF['ns_zoho_crm'] = [
    'title' => 'Zoho CRM Integration for TYPO3',
    'description' => 'Integrate Zoho CRM with TYPO3 using Powermail. Automatically sync form submissions to your CRM without coding.',
    'category' => 'plugin',
    'author' => 'Team T3Planet',
    'author_email' => 'info@t3planet.de',
    'author_company' => 'T3Planet',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '3.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-11.5.99',
            'ns_license' => '14.5.4-14.9.99',
            'powermail' => '2.1.0-10.9.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

