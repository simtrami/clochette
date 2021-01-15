<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ModeControllerTest extends WebTestCase
{
    public function testGetIndexIsRedirection(): void
    {
        $client = self::createClient();
        $client->request('GET', '/settings/modes');

        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function testPostToggleIsRedirection(): void
    {
        $client = self::createClient();
        $client->request('POST', '/settings/modes/toggle');

        $this->assertTrue($client->getResponse()->isRedirection());
    }
}
