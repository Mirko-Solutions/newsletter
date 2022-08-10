<?php

namespace Mirko\Newsletter\Tests\Functional\Repository;

use Mirko\Newsletter\Domain\Repository\BounceAccountRepository;

require_once __DIR__ . '/../AbstractFunctionalTestCase.php';

/**
 * Functional test for the \Mirko\Newsletter\Domain\Repository\BounceAccountRepository
 */
class BounceAccountRepositoryTest extends \Mirko\Newsletter\Tests\Functional\AbstractFunctionalTestCase
{
    /** @var BounceAccountRepository */
    private $bounceAccountRepository;

    public function setUp()
    {
        parent::setUp();
        $this->bounceAccountRepository = $this->objectManager->get(BounceAccountRepository::class);
    }

    public function testFindFirst()
    {
        $bounceAccount = $this->bounceAccountRepository->findFirst();
        $this->assertNotNull($bounceAccount);
        $this->assertSame(666, $bounceAccount->getUid());
    }
}
