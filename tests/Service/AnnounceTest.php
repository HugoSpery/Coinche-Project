<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\CardRepository;
use App\Service\Round\RoundSearchProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AnnounceTest extends WebTestCase
{

    public function testSomething(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $cardRepository = $this->createMock(CardRepository::class);
        $announceChecker = $container->get(RoundSearchProvider::class);


        $this->assertEquals();
    }
}
