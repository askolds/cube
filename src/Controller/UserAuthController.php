<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Controller\MailerController;

class UserAuthController extends AbstractController
{

    private $entityManager;
    private $passwordEncoder;
    private $mailer;
    private $session;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, MailerController $mailer, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->mailer = $mailer;
        $this->session = $session;
    }

    /**
     * @Route("/", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('show_rss');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('login/homepage.html.twig', ['error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/singup", name="app_singup")
     */
    public function singUp(): Response
    {

        $request = Request::createFromGlobals();
        if (!$request->request->get('_csrf_token')) {
            return $this->render('login/signup.html.twig', ['error' => null]);
        }

        $error = null;
        $params = [
            'name',
            'surname',
            'email',
            'password',
            'passwordRepeated',
        ];
        $name = null;
        $surname = null;
        $email = null;
        $password = null;
        $passwordRepeated = null;

        $data = $request->request;

        foreach ($params as $param) {
            $formValue = $data->get($param);
            if (empty($formValue)) {
                $error = 'Field ' . $param . ' can\'t be empty!';

                return $this->render('login/signup.html.twig', ['error' => $error, 'data' => $data]);
            }
            $$param = $formValue;
        }

        if (!$this->isUniqueEmail($email)) {
            return $this->render('login/signup.html.twig', ['error' => 'Email already registered!', 'data' => $data]);
        }

        if (!self::isPasswordCorrect($password, $passwordRepeated)) {
            return $this->render('login/signup.html.twig', ['error' => 'Passwords mismatch', 'data' => $data]);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password . $user->getSalt()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $hash = $user->getId() . md5($user->getEmail());
        $user->setHash($hash);
        $this->entityManager->flush();

        //fire email render success page
        $link = $this->mailer->sendEmail($email, $hash);

        return $this->render('login/success.html.twig', ['email' => $email, 'link' => $link]);

    }

    private function isPasswordCorrect(string $password, string $passwordRepeated): bool
    {
        return $password === $passwordRepeated;
    }

    private function isUniqueEmail(string $email): bool
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        return !($user instanceof User);
    }

    /**
     * @Route("/check-email/{email}", methods="GET")
     */
    public function checkEmailAjax(string $email): Response
    {
        return $this->json(['unique' => !self::isUniqueEmail($email)]);
    }

    /**
     * @Route("/activate-user-account/{hash}", methods="GET")
     */
    public function activateUserAccount(string $hash): Response
    {
        $request = Request::createFromGlobals();
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['hash' => $hash, 'status' => User::STATUS_INACTIVE]);
        if ($user instanceof User) {
            $user->setUserActive();
            $this->entityManager->flush();
            $this->session->set('user', $user);

            return $this->render('login/homepage.html.twig', ['email' => $user->getEmail()]);

            //log in to site
            return $this->redirectToRoute('show_rss');
        }

        return $this->render('login/fail.html.twig');
    }
}