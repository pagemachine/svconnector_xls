<?php

declare(strict_types = 1);

namespace Pagemachine\SvconnectorXls\Service;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class XlsxConnector extends AbstractConnector
{
    /**
     * @param array $parameters
     */
    public function checkConfiguration($parameters): array
    {
        $result = parent::checkConfiguration($parameters);

        // The "filename" parameter is mandatory
        if (empty($parameters['filename'])) {
            $result[AbstractMessage::ERROR][] = 'The "filename" parameter is mandatory.';
        }

        if (empty(GeneralUtility::getFileAbsFileName($parameters['filename']))) {
            $result[AbstractMessage::ERROR][] = 'The "filename" does not exist.';
        }

        return $result;
    }

    /**
     * @param array $parameters
     * @return mixed
     */
    protected function query($parameters)
    {
        $this->logger->info('Call parameters', $parameters);
        $this->validateConfiguration($parameters);

        $filename = GeneralUtility::getFileAbsFileName($parameters['filename']);
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($filename);

        $data = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $item = [];

                foreach ($row->getCells() as $key => $cell) {
                    $item[] = $cell->getValue();
                }

                $data[] = $item;
            }
        }

        // Process the result if any hook is registered
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processResponse'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processResponse'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
                $data = $processor->processResponse($data, $this);
            }
        }

        return $data;
    }
}
