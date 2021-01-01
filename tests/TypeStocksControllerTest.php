<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeStocksControllerTest extends WebTestCase
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
            ['/type-stocks'],
            ['/type-stocks/new'],
            ['/type-stocks/1'],
            ['/type-stocks/1/edit'],
        ];
    }
}
