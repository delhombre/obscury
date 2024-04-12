<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Account;
use App\Form\ResettingType;
use App\Form\RegistrationType;
use App\Repository\UserRepository;
use App\Service\Mailer\MailerService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     *
     * @Route("/generate", name="eee")
     */
    public function generatePassword(UserPasswordEncoderInterface $encoder)
    {
        $user = new User();
        $hash = $encoder->encodePassword($user, 'obscuryvgtanamon');

        dd($hash);
    }

    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, UserPasswordEncoderInterface $encoder, TokenGeneratorInterface $tokenGenerator, MailerService $mailer)
    {
        $user = new User();
        $account = new Account();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash)
                ->setCreatedAt(new \DateTime())
                ->setConfirmationToken($tokenGenerator->generateToken())
                ->setAccount($account);
            $this->manager->persist($user);
            $this->manager->flush();

            // on utilise le service Mailer
            $bodyMail = $mailer->createBodyMail('security/confirmationMail.html.twig', [
                'user' => $user
            ]);
            $mailer->sendMessage('support@obscury.com', $user->getEmail(), 'confirmation de compte', $bodyMail);
            $this->addFlash('sendMail_success', "Un mail va vous être envoyé afin que vous puissiez activer votre compte. Le lien que vous recevrez sera valide 24h.");

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register/confirm/{token}/{id}", name="security_confirmAccount")
     */
    public function confirmAccount(User $user, $token)
    {
        //$user = $repo->findOneBy(['username' => $username]);
        if ($user->getConfirmationToken() !== null || $token === $user->getConfirmationToken() || $this->isRequestInTime($user->getCreatedAt())) {
            $user->setConfirmationToken(null)
                ->setIsActive(true);
            $this->manager->persist($user);
            $this->manager->flush();

            return $this->redirectToRoute('security_login');
        } else {
            return $this->render('security/tokenExpiration.html.twig');
        }
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authUtils)
    {
        //Retourne une login error si il y en a une
        $error = $authUtils->getLastAuthenticationError();
        //Dernier username entré par le user
        $lastUsername = $authUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
    }

    /**
     * @Route("/reset-password", name="security_resetPassword")
     */
    public function resetPassword(Request $request, UserRepository $repo, MailerService $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        // création d'un formulaire "à la volée", afin que l'internaute puisse renseigner son mail
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class, [
                'constraints' => [
                    new Email(),
                    new NotBlank()
                ]
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // voir l'épisode 2 de cette série pour retrouver la méthode loadUserByUsername:
            $user = $repo->loadUserByUsername($form->getData()['email']);

            // aucun email associé à ce compte.
            if (!$user) {
                $this->addFlash('mailNotExist_warning', "Cet email n'existe pas.");
                return $this->redirectToRoute("security_resetPassword");
            }

            // création du token
            $user->setToken($tokenGenerator->generateToken());
            // enregistrement de la date de création du token
            $user->setPasswordRequestedAt(new \Datetime());
            $this->manager->persist($user);
            $this->manager->flush();

            // on utilise le service Mailer créé précédemment
            $bodyMail = $mailer->createBodyMail('security/mail.html.twig', [
                'user' => $user
            ]);
            $mailer->sendMessage('support@obscury.com', $user->getEmail(), 'renouvellement du mot de passe', $bodyMail);
            $this->addFlash('sendMailResetPassword_success', "Un mail va vous être envoyé afin que vous puissiez renouveller votre mot de passe. Le lien que vous recevrez sera valide 24h.");

            return $this->redirectToRoute("security_login");
        }

        return $this->render('security/resetMail.html.twig', [
            'form' => $form->createView()
        ]);
    }

    // si supérieur à 10min, retourne false
    // sinon retourne true
    private function isRequestInTime(\Datetime $passwordRequestedAt = null)
    {
        if ($passwordRequestedAt === null) {
            return false;
        }

        $now = new \DateTime();
        $interval = $now->getTimestamp() - $passwordRequestedAt->getTimestamp();

        $daySeconds = 60 * 10;
        $response = $interval > $daySeconds ? false : $response = true;
        return $response;
    }

    /**
     * @Route("/{token}/{id}/new-password", name="security_resetting")
     */
    public function resetting($token, User $user, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // interdit l'accès à la page si:
        // le token associé au membre est null
        // le token enregistré en base et le token présent dans l'url ne sont pas égaux
        // le token date de plus de 10 minutes
        if ($user->getToken() === null || $token !== $user->getToken() || !$this->isRequestInTime($user->getPasswordRequestedAt())) {
            throw new AccessDeniedHttpException();
        }

        $form = $this->createForm(ResettingType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            // réinitialisation du token à null pour qu'il ne soit plus réutilisable
            $user->setToken(null);
            $user->setPasswordRequestedAt(null);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('resetPassword_success', "Votre mot de passe a été renouvelé.");

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/change-password", name="security_passwordChange")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(ResettingType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('resetPassword_success', "Votre mot de passe a été modifié.");

            return $this->redirectToRoute('security_login');
        }

        return $this->render('security/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
