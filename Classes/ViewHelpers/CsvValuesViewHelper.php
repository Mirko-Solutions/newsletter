<?php

namespace Mirko\Newsletter\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Format array of values to CSV format
 */
class CsvValuesViewHelper extends AbstractViewHelper
{
    const DELIM = ',';
    const QUOTE = '"';

    public function initializeArguments()
    {
        parent::initializeArguments();

        $this->registerArgument('values', 'array', 'Format array of values to CSV format');
    }

    /**
     * Format array of values to CSV format
     *
     * @return string
     */
    public function render()
    {
        $row = $this->arguments['values'];

        $out = [];

        foreach ($row as $value) {
            $out[] = str_replace(self::DELIM, self::QUOTE . self::QUOTE, $value);
        }
        return self::QUOTE . implode(self::QUOTE . self::DELIM . self::QUOTE, $out) . self::QUOTE;
    }
}
