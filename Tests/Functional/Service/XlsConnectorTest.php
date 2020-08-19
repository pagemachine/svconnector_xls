<?php
declare(strict_types = 1);

namespace Pagemachine\SvconnectorXls\Tests\Functional\Service;

use Cobweb\Svconnector\Domain\Repository\ConnectorRepository;
use Cobweb\Svconnector\Service\ConnectorBase;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Testcase for \Pagemachine\SvconnectorXls\Service\XlsConnector
 */
final class XlsConnectorTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/svconnector',
        'typo3conf/ext/svconnector_xls',
    ];

    protected $pathsToLinkInTestInstance = [
        'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xls' => 'fileadmin/plain.xls',
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
        yield 'plain XLS file' => [
            [
                'filename' => 'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xls',
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

        yield 'XLS file, respecting headers' => [
            [
                'filename' => 'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xls',
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

        yield 'XLS file, relative path' => [
            [
                'filename' => 'fileadmin/plain.xls',
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
        $connectorRepository = GeneralUtility::makeInstance(ConnectorRepository::class);

        return $connectorRepository->findServiceByKey('tx_svconnectorxls_service');
    }
}
