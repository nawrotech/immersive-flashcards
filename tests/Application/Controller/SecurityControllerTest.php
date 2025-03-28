<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    const TEST_USER_EMAIL = 'test-user@example.com';

    public function testLoginPageRendersCorrectly(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    private function createUser(): User
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $user = new User();
        $user->setEmail(self::TEST_USER_EMAIL);
        $user->setPassword('password');
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function testLoginSendsEmailForExistingUser(): void
    {
        $client = static::createClient();

        $user = $this->createUser();

        $client->request('POST', '/login', [
            'email' => $user->getEmail(),
        ]);

        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();
        $this->assertEmailHeaderSame($email, 'To', $user->getEmail());
        $this->assertEmailHeaderSame($email, 'Subject', 'Login to Immersive Flashcards');
    }


    public function testLoginWithNonExistentEmailDoesNotSendEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/login', [
            'email' => 'nonexistent@example.com',
        ]);

        $this->assertEmailCount(0);
        $this->assertResponseRedirects('/login');
    }

    public function testSameFlashMessageRegardlessOfEmailExistence(): void
    {
        $client = static::createClient();

        // First with non-existent email
        $client->request('POST', '/login', [
            'email' => 'nonexistent@example.com',
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Check your email for a signin link');

        // Then with a real user
        $this->createUser();

        $client->request('POST', '/login', [
            'email' => self::TEST_USER_EMAIL,
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Check your email for a signin link');
    }

    public function testLoginEmailContentIsCorrect(): void
    {
        $client = static::createClient();

        $this->createUser();

        $client->request('POST', '/login', [
            'email' => self::TEST_USER_EMAIL,
        ]);

        $this->assertEmailCount(1);
        $email = $this->getMailerMessage();

        $this->assertEmailHeaderSame($email, 'To', self::TEST_USER_EMAIL);
        $this->assertEmailHeaderSame($email, 'Subject', 'Login to Immersive Flashcards');

        $this->assertEmailHtmlBodyContains($email, '<a');
        $this->assertEmailHtmlBodyContains($email, 'login_check');
        $this->assertEmailHtmlBodyContains($email, 'expires');
        $this->assertEmailHtmlBodyContains($email, 'hash');
    }
}
