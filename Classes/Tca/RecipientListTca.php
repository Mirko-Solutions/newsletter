<?php

namespace Mirko\Newsletter\Tca;

use Mirko\Newsletter\Domain\Repository\RecipientListRepository;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Render extract of recipient list
 */
class RecipientListTca extends AbstractFormElement
{
    /**
     * Returns an HTML table showing recipient_data content
     *
     * @return array
     */
    public function render()
    {
        $result = [];
        $uid = (int)$this->data['databaseRow']['uid'];
        if ($uid !== 0) {
            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
            $recipientListRepository = $objectManager->get(RecipientListRepository::class);
            $recipientList = $recipientListRepository->findByUidInitialized($uid);

            $result = $recipientList->getExtract();
        }
        $resultArray['html'] = implode(LF, $result);
        return $resultArray;
    }
}
