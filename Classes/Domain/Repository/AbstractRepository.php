<?php

namespace Mirko\Newsletter\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Abstract repository to workaround difficulties (or misunderstanding?) with extbase.
 */
abstract class AbstractRepository extends Repository
{
    /**
     * Override parent method to set default settings to ignore storagePid because we did
     * not understand how to use it. And we usually don't want to be tied to a
     * specific pid anyway, so we prefer to do it manually when necessary.
     * TODO this method should be destroyed once we understand how to properly work with storagePid
     */
    public function createQuery()
    {
        $query = parent::createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        return $query;
    }

    public function persistAll()
    {
        $this->persistenceManager->persistAll();
    }
}
