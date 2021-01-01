<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ManagementControllerTest extends WebTestCase
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
            ['/management'],
            ['/management/runs/new'],
            ['/management/runs/1/treasury/new'],
            ['/management/runs'],
            ['/management/runs/1/treasury/edit'],
            ['/management/runs/1'],
        ];
    }
}
