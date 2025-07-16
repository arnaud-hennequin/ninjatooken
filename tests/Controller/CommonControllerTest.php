<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class CommonControllerTest extends BaseWebTestCase
{
    /**
     * @return array<array<int, string>>
     */
    public static function languageProvider(): array
    {
        return [
            ['fr'],
            ['en'],
        ];
    }

    #[DataProvider('languageProvider')]
    public function testHome(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/', $langue));

        // THEN
        self::assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.flagSocial'));
        $this->assertCount(1, $crawler->filter('.home'));
        $this->assertCount(1, $crawler->filter('.presentation'));
        $this->assertCount(1, $crawler->filter('.create'));
        $this->assertCount(1, $crawler->filter('.diaporama'));
        $this->assertCount(1, $crawler->filter('.account'));
    }

    #[DataProvider('languageProvider')]
    public function testLoggedHome(string $langue): void
    {
        // WITH
        $loggedUser = $this->userDataSetup->addUser();
        $this->client->loginUser($loggedUser);

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/', $langue));

        // THEN
        self::assertResponseIsSuccessful();
        $this->assertCount(0, $crawler->filter('.account')); // accountLogged
    }

    #[DataProvider('languageProvider')]
    public function testPlay(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/jouer', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testGuide(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/guide-du-ninja', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testRules(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/regles-respecter', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testChat(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/chat', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testMainFAQ(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/faq-generale', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testTechnicalFAQ(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/faq-technique', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testTeam(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/team', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testLegals(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/mentions-legales', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testContact(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/nous-contacter', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testSearch(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/search', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }
}
