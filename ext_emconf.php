<?php

$EM_CONF['ns_zoho_crm'] = [
    'title' => 'TYPO3 + Zoho CRM',
    'description' => 'Easily install and configure your powermail form with zoho CRM, read more at documentation.',
    'category' => 'plugin',
    'author' => 'T3Planet // NITSAN',
    'author_email' => 'sanjay@nitsan.in',
    'author_company' => 'T3Planet // NITSAN',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '3.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '6.2.0-11.5.99',
            'powermail' => '2.1.0-10.9.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];

