<?php

// src/Notifier/CustomLoginLinkNotification
namespace App\Notifier;

use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkNotification;

class CustomLoginLinkNotification extends LoginLinkNotification
{
    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        $emailMessage = parent::asEmailMessage($recipient, $transport);

        /** @var NotificationEmail $email */
        $email = $emailMessage->getMessage();
        $email->htmlTemplate('emails/magic_link_email.html.twig');

        return $emailMessage;
    }
}
