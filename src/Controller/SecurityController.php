<?php

namespace App\Controller;

use App\Notifier\CustomLoginLinkNotification;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\LoginLink\LoginLinkHandlerInterface;


class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(
        NotifierInterface $notifier,
        LoginLinkHandlerInterface $loginLinkHandler,
        UserRepository $userRepository,
        Request $request,
        RateLimiterFactory $loginLinkRequestLimiter
    ): Response {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_deck');
        }

        if ($request->isMethod('POST')) {
            $limiter = $loginLinkRequestLimiter->create($request->getClientIp());
            if (false === $limiter->consume(1)->isAccepted()) {
                $this->addFlash('error', 'Too many attempts. Try again later.');
                return $this->redirectToRoute('app_login');
            }

            $email = $request->getPayload()->get('email');
            $user = $userRepository->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash("success", "Check your email for a signin link");
                return $this->redirectToRoute('app_login');
            }

            $loginLinkDetails = $loginLinkHandler->createLoginLink($user);

            $notification = new CustomLoginLinkNotification(
                $loginLinkDetails,
                'Login to Immersive Flashcards'
            );

            $recipient = new Recipient($user->getEmail());
            $notifier->send($notification, $recipient);

            $this->addFlash("success", "Check your email for a signin link");

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/login.html.twig');
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login_check', name: 'login_check')]
    public function check(): never
    {
        throw new \LogicException('This code should never be reached');
    }
}
