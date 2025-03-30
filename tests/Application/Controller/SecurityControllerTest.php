<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends WebTestCase
{
    use Factories;
    use MailerAssertionsTrait;
    use ResetDatabase;

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
        return UserFactory::createOne([
            'email' => self::TEST_USER_EMAIL
        ])->_real();
    }

    public function testLoginSendsEmailForExistingUser(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        
        $user = $this->createUser();
        
        $form = $crawler->selectButton('Send Login Link')->form();
        $client->submit($form, [
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
        $crawler = $client->request('GET', '/login');
        
        $form = $crawler->selectButton('Send Login Link')->form();
        $client->submit($form, [
            'email' => 'nonexistent@example.com',
        ]);

        $this->assertEmailCount(0);
        $this->assertResponseRedirects('/login');
    }

    public function testSameFlashMessageRegardlessOfEmailExistence(): void
    {
        $client = static::createClient();
        
        // First with non-existent email
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Send Login Link')->form();
        $client->submit($form, [
            'email' => 'nonexistent@example.com',
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', 'Check your email for a signin link');

        // Then with a real user
        $this->createUser();
        
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Send Login Link')->form();
        $client->submit($form, [
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
        
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Send Login Link')->form();
        $client->submit($form, [
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
