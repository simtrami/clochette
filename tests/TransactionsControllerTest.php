<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransactionsControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrls
     * @param $url
     */
    public function testPageIsRedirection($url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function provideUrls(): array
    {
        return [
            ['/transactions'],
            ['/transactions/unregistered'],
        ];
    }
}
