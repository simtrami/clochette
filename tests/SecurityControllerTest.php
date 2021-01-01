<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrls
     * @param $url
     */
    public function testPageIsSuccessful($url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function provideUrls(): array
    {
        return [
            ['/login'],
        ];
    }

    public function testLogoutRedirectToLogin(): void
    {
        $client = self::createClient();
        $client->request('GET', '/logout');

        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testPostLogin(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Connexion')->form();

        $form['email'] = 'intro-user@example.com';
        $form['password'] = 'secret';

        $client->submit($form);
        self::assertSame($client->followRedirect()->getUri(), 'http://localhost/purchase');
    }
}
