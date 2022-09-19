<?php

namespace Mirko\Newsletter\Tca;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

/**
 * Handle bounced emails. Fetch them, analyse them and take approriate actions.
 */
class EmailTca extends AbstractFormElement
{
    /**
     * Returns an HTML table showing recipient_data content
     *
     */
    public function render(): array
    {
        $data = unserialize($this->data['databaseRow']['recipient_data']);

        if (!$data) {
            $data = [];
        }

        $keys = array_keys($data);

        $html = [];
        $html[] = '<table style="border: 1px grey solid; border-collapse: collapse;">';
        $html[] = '<tr>';
        foreach ($keys as $key) {
            $html[] = '<th style="padding-right: 1em;">' . $key . '</th>';
        }
        $html[] = '</tr>';

        $html[] = '<tr style="border: 1px grey solid; border-collapse: collapse;">';
        foreach ($data as $value) {
            $html[] = '<td style="padding-right: 1em;">' . $value . '</td>';
        }
        $html[] = '</tr>';
        $html[] = '</table>';

        $resultArray['html'] = implode(LF, $html);

        return $resultArray;
    }
}
