<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Connector service - XLS(X)',
    'description' => 'Connector service for reading a XLS(X) file',
    'category' => 'misc',
    'author' => 'Mathias Brodala',
    'author_email' => 'mbrodala@pagemachine.de',
    'author_company' => 'Pagemachine AG',
    'state' => 'stable',
    'version' => '3.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
        ],
    ],
];
