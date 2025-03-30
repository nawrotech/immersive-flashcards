<?php

namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Zenstruck\Foundry\Test\Factories;

class RegistrationControllerTest extends WebTestCase
{
    use Factories;
    private $client;
    private $entityManager;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository =  static::getContainer()->get(UserRepository::class);
    }

    private function createUser(bool $isVerified = true): User
    {
        return UserFactory::createOne([
            "email" => "exampleMail@ex.com",
            "isVerified" => $isVerified
        ])->_real()
        ;
    }

    public function testRegistrationPageRendersCorrectly(): void
    {
        $this->client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');
        $this->assertSelectorExists('form[name="registration_form"]');
        $this->assertSelectorExists('#registration_form_email');
        $this->assertSelectorExists('#registration_form_agreeTerms');
    }

    public function testAuthenticatedUserCannotAccessRegistration(): void
    {
        $user = $this->createUser();
        $this->client->loginUser($user);

        $this->client->request('GET', '/register');
        
        $this->assertResponseRedirects('/');
    }

    public function testSuccessfulRegistration(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form();

        $email = 'test@example.com';
        $this->client->submit($form, [
            'registration_form[email]' => $email,
            'registration_form[agreeTerms]' => true,
        ]);

        $this->assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->assertSelectorExists('.flash-success');

        $user = $this->createUser(false);
        $this->assertNotNull($user);
        $this->assertFalse($user->isVerified());
    }

    public function testInvalidRegistrationData(): void
    {
        $crawler = $this->client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form();

        $this->client->submit($form, [
            'registration_form[email]' => 'invalid-email',
            'registration_form[agreeTerms]' => false,
        ]);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testEmailVerificationWithInvalidId(): void
    {
        $this->client->request('GET', '/verify/email');
        $this->assertResponseRedirects('/');

        $this->client->request('GET', '/verify/email?id=999999');
        $this->assertResponseRedirects('/');
    }

    public function testSuccessfulEmailVerification(): void
    {
        $user = $this->createUser(false);
        $this->assertFalse($user->isVerified());

        $emailVerifier = static::getContainer()->get(VerifyEmailHelperInterface::class);

        $signedUrl = $emailVerifier->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            (string) $user->getEmail(),
            ['id' => $user->getId()]
        );

        $this->client->request('GET', $signedUrl->getSignedUrl());

        $userFromDb = $this->userRepository->find($user->getId());
        $this->assertSame($user->getId(), $userFromDb->getId());
        
        $this->assertTrue(
            self::getContainer()->get(Security::class)->isGranted('IS_AUTHENTICATED_FULLY')
        );
        $this->assertTrue($userFromDb->isVerified());
        $this->assertResponseRedirects('/');

        $this->client->followRedirect();
        $this->assertSelectorExists('.flash-success');
    }

}
