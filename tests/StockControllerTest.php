<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StockControllerTest extends WebTestCase
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
            ['/stock'],
            ['/stock/new'],
            ['/stock/1/edit'],
            ['/stock/1/delete'],
        ];
    }
}
