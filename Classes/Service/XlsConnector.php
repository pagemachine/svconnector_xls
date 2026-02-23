<?php

declare(strict_types=1);

namespace Pagemachine\SvconnectorXls\Service;

use Cobweb\Svconnector\Exception\SourceErrorException;
use Cobweb\Svconnector\Utility\FileUtility;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class XlsConnector extends AbstractConnector
{
    public function getType(): string
    {
        return 'xls';
    }

    public function getName(): string
    {
        return 'XLS Connector';
    }

    public function checkConfiguration(array $parameters = []): array
    {
        $result = parent::checkConfiguration();

        // The "filename" parameter is mandatory
        if (empty($this->parameters['filename'])) {
            $result[ContextualFeedbackSeverity::ERROR->value][] = 'The "filename" parameter is mandatory.';
        }

        if (empty(GeneralUtility::getFileAbsFileName($this->parameters['filename']))) {
            $result[ContextualFeedbackSeverity::ERROR->value][] = 'The "filename" does not exist.';
        }

        return $result;
    }

    /**
     * @return mixed
     */
    protected function query(array $parameters = [])
    {
        $this->logger->info('Call parameters', $this->parameters);
        $this->validateConfiguration();

        $fileUtility = GeneralUtility::makeInstance(FileUtility::class);
        $filename =  $fileUtility->getFileAsTemporaryFile($this->parameters['filename']);

        if ($filename === false) {
            $this->raiseError($fileUtility->getError(), 1605278290, $this->parameters, SourceErrorException::class);
        }

        $reader = IOFactory::createReader('Xls');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filename);
        $worksheet = null;

        try {
            $worksheet = $spreadsheet->getActiveSheet();
        } catch (SpreadsheetException $e) {
            $this->raiseError($e->getMessage(), 1596554370, $this->parameters, SourceErrorException::class);
        }

        $data = [];

        foreach ($worksheet->getRowIterator() as $row) {
            $item = [];

            foreach ($row->getCellIterator() as $key => $cell) {
                $item[$key] = $cell->getValue();
            }

            $data[] = $item;
        }

        // Process the result if any hook is registered
        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processResponse'] ?? [] as $className) {
            $processor = GeneralUtility::makeInstance($className);
            $data = $processor->processResponse($data, $this);
        }

        return $data;
    }
}
