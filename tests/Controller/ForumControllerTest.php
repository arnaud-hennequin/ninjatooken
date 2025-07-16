<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class ForumControllerTest extends BaseWebTestCase
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
    public function testOldMessage(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/message.php', $langue));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('languageProvider')]
    public function testOldForum(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/forum.php', $langue));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    #[DataProvider('languageProvider')]
    public function testEvent(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/event/1', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    #[DataProvider('languageProvider')]
    public function testAddEvent(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/event/ajouter/', $langue));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    #[DataProvider('languageProvider')]
    public function testForumList(string $langue): void
    {
        // WITH

        // WHEN
        $crawler = $this->client->request('GET', sprintf('/%s/forum', $langue));

        // THEN
        self::assertResponseIsSuccessful();
    }

    public function testForum(): void
    {
        $this->markTestIncomplete();
    }

    public function testAddForum(): void
    {
        $this->markTestIncomplete();
    }

    public function testThread(): void
    {
        $this->markTestIncomplete();
    }

    public function testEditThread(): void
    {
        $this->markTestIncomplete();
    }

    public function testLockThread(): void
    {
        $this->markTestIncomplete();
    }

    public function testPostitThread(): void
    {
        $this->markTestIncomplete();
    }

    public function testDeleteThread(): void
    {
        $this->markTestIncomplete();
    }

    public function testAddComment(): void
    {
        $this->markTestIncomplete();
    }

    public function testEditComment(): void
    {
        $this->markTestIncomplete();
    }

    public function testDeleteComment(): void
    {
        $this->markTestIncomplete();
    }
}
