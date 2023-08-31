<?php

declare(strict_types = 1);

namespace Pagemachine\SvconnectorXls\Tests\Functional\Service;

use Cobweb\Svconnector\Domain\Repository\ConnectorRepository;
use Cobweb\Svconnector\Registry\ConnectorRegistry;
use Cobweb\Svconnector\Service\ConnectorBase;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for \Pagemachine\SvconnectorXls\Service\XlsxConnector
 */
final class XlsxConnectorTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/svconnector',
        'typo3conf/ext/svconnector_xls',
    ];

    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xlsx' => 'fileadmin/plain.xlsx',
    ];

    /**
     * @test
     * @dataProvider validCases
     */
    public function readsXls(array $parameters, array $expected)
    {
        $connector = $this->getConnector();

        $data = $connector->fetchArray($parameters);

        $this->assertEquals($expected, $data);
    }

    public function validCases(): \Generator
    {
        yield 'plain XLSX file' => [
            [
                'filename' => 'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xlsx',
            ],
            [
                [
                    'A' => 'Header 1',
                    'B' => 'Header 2',
                    'C' => 'Header 3',
                ],
                [
                    'A' => 'Value 1.1',
                    'B' => 'Value 1.2',
                    'C' => 'Value 1.3',
                ],
                [
                    'A' => 'Value 2.1',
                    'B' => 'Value 2.2',
                    'C' => 'Value 2.3',
                ],
            ],
        ];

        yield 'XLSX file, respecting headers' => [
            [
                'filename' => 'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xlsx',
                'skip_rows' => 1,
            ],
            [
                [
                    'Header 1' => 'Value 1.1',
                    'Header 2' => 'Value 1.2',
                    'Header 3' => 'Value 1.3',
                ],
                [
                    'Header 1' => 'Value 2.1',
                    'Header 2' => 'Value 2.2',
                    'Header 3' => 'Value 2.3',
                ],
            ],
        ];

        yield 'XLSX file, relative path' => [
            [
                'filename' => 'fileadmin/plain.xlsx',
                'skip_rows' => 1,
            ],
            [
                [
                    'Header 1' => 'Value 1.1',
                    'Header 2' => 'Value 1.2',
                    'Header 3' => 'Value 1.3',
                ],
                [
                    'Header 1' => 'Value 2.1',
                    'Header 2' => 'Value 2.2',
                    'Header 3' => 'Value 2.3',
                ],
            ],
        ];
    }

    private function getConnector(): ConnectorBase
    {
        if (class_exists(ConnectorRepository::class)) {
            $connectorRepository = GeneralUtility::makeInstance(ConnectorRepository::class);

            return $connectorRepository->findServiceByKey('tx_svconnectorxls_xlsx');
        }

        $connectorRegistry = GeneralUtility::makeInstance(ConnectorRegistry::class);

        return $connectorRegistry->getServiceForType('xlsx');
    }
}
