<?php
defined('TYPO3_MODE') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'svconnector_xls',
    'connector',
    'tx_svconnectorxls_service',
    [
        'title' => 'XLS(X) connector',
        'description' => 'Connector service for reading a XLS(X) file',
        'subtype' => 'xls',
        'available' => true,
        'priority' => 50,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => \Pagemachine\SvconnectorXls\Service\XlsConnector::class,
    ]
);
