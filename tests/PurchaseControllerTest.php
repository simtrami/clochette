<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PurchaseControllerTest extends WebTestCase
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

    public function testIntroGetIndexIsSuccessful(): void
    {
        self::createClient([], [
            'PHP_AUTH_USER' => 'intro-user@example.com',
            'PHP_AUTH_PW' => 'secret',
        ])->request('GET', '/purchase');
        $this->assertResponseIsSuccessful();
    }

    public function testBureauGetOpenDrawerRedirects(): void
    {
        self::createClient([], [
            'PHP_AUTH_USER' => 'bureau-user@example.com',
            'PHP_AUTH_PW' => 'secret',
        ])->request('GET', '/purchase/open-drawer');
        $this->assertResponseRedirects('/purchase', 302);
    }

    public function provideUrls(): array
    {
        return [
            ['/purchase'],
            ['/purchase/submit'],
            ['/purchase/open-drawer'],
        ];
    }
}
