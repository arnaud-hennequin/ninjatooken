<?php

namespace App\Tests\Controller;

use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends BaseWebTestCase
{
    public function testOldUser(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/fr/utilisateur.php');

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testRegister(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/fr/register');

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testRegistering(): void
    {
        $this->markTestIncomplete();
    }

    public function testLogged(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/fr/login');

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testLogin(): void
    {
        $this->markTestIncomplete();
    }

    public function testLogout(): void
    {
        // WITH

        // WHEN
        $this->client->request('GET', '/logout');

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_MOVED_PERMANENTLY);
    }

    public function testAutologin(): void
    {
        // WITH
        $autologin = 'testAutologin';

        // WHEN
        $this->client->request('GET', sprintf('/fr/auto/%s', $autologin));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUnregister(): void
    {
        // WITH
        $email = 'test@toto.com';

        // WHEN
        $this->client->request('GET', sprintf('/fr/desinscription/%s', $email));

        // THEN
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testFiche(): void
    {
        $this->markTestIncomplete();
    }

    public function testMessagerie(): void
    {
        $this->markTestIncomplete();
    }

    public function testMessagerieEnvoi(): void
    {
        $this->markTestIncomplete();
    }

    public function testUserFind(): void
    {
        $this->markTestIncomplete();
    }

    public function testParametres(): void
    {
        $this->markTestIncomplete();
    }

    public function testParametresUpdate(): void
    {
        $this->markTestIncomplete();
    }

    public function testParametresUpdateAvatar(): void
    {
        $this->markTestIncomplete();
    }

    public function testParametresConfirmMail(): void
    {
        $this->markTestIncomplete();
    }

    public function testParametresUpdatePassword(): void
    {
        $this->markTestIncomplete();
    }

    public function testDeleteAccount(): void
    {
        $this->markTestIncomplete();
    }

    public function testConfirm(): void
    {
        $this->markTestIncomplete();
    }

    public function testConfirmed(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmis(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisDemande(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisBlocked(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisConfirmer(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisBloquer(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisDebloquer(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisSupprimer(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisBlockedSupprimer(): void
    {
        $this->markTestIncomplete();
    }

    public function testAmisDemandeSupprimer(): void
    {
        $this->markTestIncomplete();
    }

    public function testCapture(): void
    {
        $this->markTestIncomplete();
    }

    public function testCaptureSupprimer(): void
    {
        $this->markTestIncomplete();
    }
}
