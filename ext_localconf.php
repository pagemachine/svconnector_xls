<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'svconnector_xls',
    'connector',
    'tx_svconnectorxls_xls',
    [
        'title' => 'XLS connector',
        'description' => 'Connector service for reading an XLS file',
        'subtype' => 'xls',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => \Pagemachine\SvconnectorXls\Service\XlsConnector::class,
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'svconnector_xls',
    'connector',
    'tx_svconnectorxls_xlsx',
    [
        'title' => 'XLSX connector',
        'description' => 'Connector service for reading an XLSX file',
        'subtype' => 'xlsx',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => \Pagemachine\SvconnectorXls\Service\XlsxConnector::class,
    ]
);
