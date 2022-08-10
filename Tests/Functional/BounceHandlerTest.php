<?php

namespace Mirko\Newsletter\Tests\Functional;

use Mirko\Newsletter\BounceHandler;
use Mirko\Newsletter\Domain\Repository\EmailRepository;

require_once __DIR__ . '/AbstractFunctionalTestCase.php';

/**
 * Functional test for the \Mirko\Newsletter\BounceHandler
 */
class BounceHandlerTest extends \Mirko\Newsletter\Tests\Functional\AbstractFunctionalTestCase
{
    public function testDispatch()
    {
        $filename = dirname(__DIR__) . '/Unit/Fixtures/bounce/2-87c4e9b09085befbb7f20faa7482213a-Undelivered Mail Returned to Sender.eml';
        $content = file_get_contents($filename);

        $bounceHandler = new BounceHandler($content);
        $bounceHandler->dispatch();

        $emailRepository = $this->objectManager->get(EmailRepository::class);
        $email = $emailRepository->findByUid(302);
        $this->assertTrue($email->isBounced());
        $this->assertRecipientListCallbackWasCalled('bounced recipient2@example.com, 2, 2, 3, 4');
    }
}
