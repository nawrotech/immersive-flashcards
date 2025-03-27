<?php

namespace App\Tests\Unit\Notifier;

use App\Notifier\CustomLoginLinkNotification;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Recipient\Recipient;

class CustomLoginLinkNotificationTest extends TestCase
{
    public function testUsesCustomEmailTemplate(): void
    {

        $loginLinkDetails = $this->createMock(LoginLinkDetails::class);

        assert($loginLinkDetails instanceof LoginLinkDetails);
        $notification = new CustomLoginLinkNotification($loginLinkDetails, 'Login Link');

        $emailMessage = $notification->asEmailMessage(new Recipient('test@example.com'));
        /** @var NotificationEmail $email */
        $email = $emailMessage->getMessage();

        $email->htmlTemplate('emails/custom_login_link_email.html.twig');

        $this->assertEquals('emails/custom_login_link_email.html.twig', $email->getHtmlTemplate());
    }
}
