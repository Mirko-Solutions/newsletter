<?php

namespace Mirko\Newsletter\Domain\Model\RecipientList;

use Mirko\Newsletter\Tools;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Recipient List using CSV file
 */
class CsvFile extends AbstractArray
{
    /**
     * csvSeparator
     *
     * @var string
     */
    protected $csvSeparator = ',';

    /**
     * csvFields
     *
     * @var string
     */
    protected $csvFields = '';

    /**
     * csvFilename
     *
     * @var string
     */
    protected $csvFilename = '';

    /**
     * Setter for csvSeparator
     *
     * @param string $csvSeparator csvSeparator
     */
    public function setCsvSeparator($csvSeparator)
    {
        $this->csvSeparator = $csvSeparator;
    }

    /**
     * Getter for csvSeparator
     *
     * @return string csvSeparator
     */
    public function getCsvSeparator()
    {
        return $this->csvSeparator;
    }

    /**
     * Setter for csvFields
     *
     * @param string $csvFields csvFields
     */
    public function setCsvFields($csvFields)
    {
        $this->csvFields = $csvFields;
    }

    /**
     * Getter for csvFields
     *
     * @return string csvFields
     */
    public function getCsvFields()
    {
        return $this->csvFields;
    }

    /**
     * Setter for csvFilename
     *
     * @param string $csvFilename csvFilename
     */
    public function setCsvFilename($csvFilename)
    {
        $this->csvFilename = $csvFilename;
    }

    /**
     * Getter for csvFilename
     *
     * @return string csvFilename
     */
    public function getCsvFileName(): string
    {
        $file = $this->getFileReferences($this->getUid());
        if (!is_array($file)) {
            return '';
        }

        return $file['name'];
    }

    /**
     * Return the path where CSV file are contained
     *
     * @return string
     */
    protected function getAbsoluteFilePath(): string
    {
        $file = $this->getFileReferences($this->getUid());
        if (!is_array($file)) {
            return '';
        }

        return   Environment::getPublicPath(). "/fileadmin{$file['identifier']}";
    }

    protected function getFileReferences($uid) {
        $fileRepository = GeneralUtility::makeInstance(FileRepository::class);
        $fileObjects = $fileRepository->findByRelation('tx_newsletter_domain_model_recipientlist', 'csv_filename', $uid);

        if (empty($fileObjects)) {
            return null;
        }

        return $fileObjects[0]->getOriginalFile()->getProperties();
    }

    public function init()
    {
        $this->loadCsvFromFile($this->getAbsoluteFilePath());
    }

    /**
     * Load data from a CSV file.
     *
     * @param string $filename path to the CSV file may be on disk or remote URL
     */
    protected function loadCsvFromFile($filename)
    {
        $csvdata = null;
        if ($filename) {
            $csvdata = Tools::getURL($filename);
        }

        $this->loadCsvFromData($csvdata);
    }

    /**
     * Load data from a CSV data.
     *
     * @param string $csvdata CSV data
     */
    protected function loadCsvFromData($csvdata)
    {
        $this->data = [];

        $sepchar = $this->getCsvSeparator() ? $this->getCsvSeparator() : ',';
        $keys = array_unique(array_map('trim', explode($sepchar, $this->getCsvFields())));
        if ($csvdata && $sepchar && count($keys)) {
            $lines = explode("\n", $csvdata);
            foreach ($lines as $line) {
                if (!trim($line)) {
                    continue;
                }

                $values = str_getcsv($line, $sepchar);
                if (count($values) != count($keys)) {
                    $this->error = sprintf('Field names count (%1$d) is not equal to values count (%2$d)', count($keys), count($values));
                }
                $row = array_combine($keys, $values);

                if ($row) {
                    $this->data[] = $row;
                }
            }
        }
    }

    public function getError()
    {
        if (isset($this->error)) {
            return $this->error;
        }

        return parent::getError();
    }
}
