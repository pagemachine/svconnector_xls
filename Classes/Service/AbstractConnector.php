<?php

declare(strict_types=1);

namespace Pagemachine\SvconnectorXls\Service;

use Cobweb\Svconnector\Exception\SourceErrorException;
use Cobweb\Svconnector\Service\ConnectorBase;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractConnector extends ConnectorBase
{
    protected string $extensionKey = 'svconnector_xls';

    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @return mixed
     */
    public function fetchRaw(array $parameters = [])
    {
        $result = $this->query($parameters);

        // Implement post-processing hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processRaw'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processRaw'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
                $result = $processor->processRaw($result, $this);
            }
        }

        return $result;
    }

    public function fetchArray(array $parameters = []): array
    {
        $headers = [];
        $data = [];
        // Get the data from the file
        $result = $this->query($parameters);
        $numResults = count($result);
        // If there are some results, process them
        if ($numResults > 0) {
            // Handle header rows, if any
            if (!empty($parameters['skip_rows'])) {
                for ($i = 0; $i < $parameters['skip_rows']; $i++) {
                    $headers = array_shift($result);
                }
            }
            foreach ($result as $row) {
                $rowData = [];
                foreach ($row as $index => $value) {
                    $key = $headers[$index] ?? $index;
                    $rowData[$key] = $value;
                }
                $data[] = $rowData;
            }
        }

        $this->logger->info('Structured data', $data);

        // Implement post-processing hook
        if (is_array(($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processArray'] ?? ''))) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processArray'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
                $data = $processor->processArray($data, $this);
            }
        }

        return $data;
    }

    public function fetchXML(array $parameters = []): string
    {
        // Get the data as an array
        $result = $this->fetchArray($parameters);
        // Transform result to XML
        $xml = GeneralUtility::array2xml($result);
        // Check if the current (BE) charset is the same as the file encoding
        $encoding = $parameters['encoding'] ?? 'UTF-8';
        $xml = '<?xml version="1.0" encoding="' . htmlspecialchars((string)$encoding) . '" standalone="yes" ?>' . "\n" . $xml;

        // Implement post-processing hook
        if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processXML'])) {
            foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][$this->extensionKey]['processXML'] as $className) {
                $processor = GeneralUtility::makeInstance($className);
                $xml = $processor->processXML($xml, $this);
            }
        }

        return $xml;
    }

    protected function validateConfiguration(array $parameters): void
    {
        // Check the configuration
        $problems = $this->checkConfiguration($parameters);
        // Log all issues and raise error if any
        $this->logConfigurationCheck($problems);

        if (count($problems[AbstractMessage::ERROR]) > 0) {
            $message = '';

            foreach ($problems[AbstractMessage::ERROR] as $problem) {
                if ($message !== '') {
                    $message .= "\n";
                }
                $message .= $problem;
            }

            $this->raiseError(
                $message,
                1596554384,
                [],
                SourceErrorException::class
            );
        }
    }
}
