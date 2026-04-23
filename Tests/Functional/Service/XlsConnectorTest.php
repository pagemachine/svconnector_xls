<?php

declare(strict_types=1);

namespace Pagemachine\SvconnectorXls\Tests\Functional\Service;

use Cobweb\Svconnector\Domain\Model\Dto\CallContext;
use Cobweb\Svconnector\Domain\Model\Dto\ConnectionInformation;
use Pagemachine\SvconnectorXls\Service\XlsConnector;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\NullLogger;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Testcase for \Pagemachine\SvconnectorXls\Service\XlsConnector
 */
final class XlsConnectorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/svconnector',
        'typo3conf/ext/svconnector_xls',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/svconnector_xls/Tests/Functional/Service/Fixtures/plain.xls' => 'fileadmin/plain.xls',
    ];

    #[DataProvider('validCases')]
    #[Test]
    public function readsXls(array $parameters, array $expected)
    {
        $connector = new XlsConnector(
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
}
