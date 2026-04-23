<?php

declare(strict_types=1);

namespace Pagemachine\SvconnectorXls\Tests\Functional\Service;

use Cobweb\Svconnector\Domain\Model\Dto\CallContext;
use Cobweb\Svconnector\Domain\Model\Dto\ConnectionInformation;
use Pagemachine\SvconnectorXls\Service\XlsxConnector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for \Pagemachine\SvconnectorXls\Service\XlsxConnector
 */
final class XlsxConnectorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/svconnector',
        'typo3conf/ext/svconnector_xls',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xlsx' => 'fileadmin/plain.xlsx',
    ];

    #[DataProvider('validCases')]
    #[Test]
    public function readsXls(array $parameters, array $expected)
    {
        $connector = new XlsxConnector(
            $this->get(EventDispatcherInterface::class),
            new CallContext(),
            new ConnectionInformation(),
        );
        $connector->setLogger(new NullLogger());
        $connector->setParameters($parameters);

        $data = $connector->fetchArray();

        self::assertEquals($expected, $data);
    }

    public static function validCases(): \Generator
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
}
