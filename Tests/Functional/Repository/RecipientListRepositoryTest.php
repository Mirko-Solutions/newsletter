<?php

namespace Mirko\Newsletter\Tests\Functional\Repository;

use Mirko\Newsletter\Domain\Repository\RecipientListRepository;

require_once __DIR__ . '/../AbstractFunctionalTestCase.php';

/**
 * Functional test for the \Mirko\Newsletter\Domain\Repository\RecipientListRepository
 */
class RecipientListRepositoryTest extends \Mirko\Newsletter\Tests\Functional\AbstractFunctionalTestCase
{
    /** @var RecipientListRepository */
    private $recipientListRepository;

    public function setUp()
    {
        parent::setUp();
        $this->recipientListRepository = $this->objectManager->get(RecipientListRepository::class);
    }

    public function testFindByUidInitialized()
    {
        $recipientList = $this->recipientListRepository->findByUidInitialized(1000);
        $this->assertNotNull($recipientList);
        $this->assertSame(2, $recipientList->getCount(), 'should not have to call init() to get count');
    }
}
