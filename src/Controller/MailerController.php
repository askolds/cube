<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{

    const MAIL_FROM = 'no-replay@airsoftgames.eu';
    const MAIL_LINK = 'https://airsoftgames.eu/activate-user-account/';
    const MAIL_SUBJECT = 'Rss reader registration';
    
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/email")
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendEmail(string $to, string $hash): ?string
    {
        $email = (new Email())
            ->from(self::MAIL_FROM)
            ->to($to)
            ->subject(self::MAIL_SUBJECT)
            ->text('Hi, to complete registration click on link or copy link to browser.')
            ->html('<p><a href="' . self::MAIL_LINK . $hash . '">Activate user account</p><p><small>' . self::MAIL_LINK . $hash . '</small></p>');

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {

            return self::MAIL_LINK . $hash;
        }

    }

}
