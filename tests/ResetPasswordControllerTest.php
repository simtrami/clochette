<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    /**
     * @dataProvider publicUrls
     * @param $url
     */
    public function testPageIsSuccessful($url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    /**
     * @dataProvider requireParametersUrls
     * @param $url
     */
    public function testPageIsRedirection($url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function publicUrls(): array
    {
        return [
            ['/reset-password'],
        ];
    }

    public function requireParametersUrls(): array
    {
        return [
            ['/reset-password/check-email'],
            ['/reset-password/reset/token'],
        ];
    }
}
