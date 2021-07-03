<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Album;
use App\Entity\Musique;
use App\Form\AlbumType;
use App\Form\MusiqueType;
use App\Form\ProfileType;
use App\Form\UsernameType;
use App\Repository\SubscriptionRepository;
use App\Service\Account\AccountService;
use App\Service\Subscription\SubscriptionService;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

class UserController extends AbstractController
{
    /**
     * @Route("/profile", name="user_account")
     */
    public function index(Request $request, ObjectManager $manager, SubscriptionRepository $repo, SubscriptionService $subscriptionService, AccountService $accountService)
    {
        $user = $this->getUser();
        $account = $user->getAccount();
        $musique = new Musique();
        $album = new Album();

        $subscriptionService->isActiveSubscription($user, $manager);

        if ($user->getSubscriptionBeginAt()) {
            $date = $user->getSubscriptionBeginAt()->diff($user->getSubscriptionEndAt(), \true)->days;
        } else {
            $date = \null;
        }

        $subscriptionService->subscriptionChecker($user, $user->getAmateurIsActive(), $date, 3, $manager);

        $subscriptionService->subscriptionChecker($user, $user->getGoldenIsActive(), $date, 7, $manager);

        $subscriptionService->subscriptionChecker($user, $user->getPremiumIsActive(), $date, 15, $manager);

        //Form Profile
        $formProfile = $this->createForm(ProfileType::class, $account);
        $formProfile->handleRequest($request);
        if ($formProfile->isSubmitted() && $formProfile->isValid()) {
            $manager->persist($account);
            $manager->flush();

            return $this->redirectToRoute('user_account');
        }


        //Form Username
        $formUsername = $this->createForm(UsernameType::class, $user);
        $formUsername->handleRequest($request);
        if ($formUsername->isSubmitted() && $formUsername->isValid()) {
            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('user_account');
        }

        //Form Musique
        $formMusique = $this->createForm(MusiqueType::class, $musique);
        $formMusique->handleRequest($request);
        if ($formMusique->isSubmitted() && $formMusique->isValid()) {
            $musique->setCreatedAt(new \DateTime());

            $musique->setUser($user);
            $manager->persist($musique);

            $manager->flush();

            return $this->redirectToRoute('user_musics_show');
        }

        //Form Album
        $formAlbum = $this->createForm(AlbumType::class, $album);
        $formAlbum->handleRequest($request);
        if ($formAlbum->isSubmitted() && $formAlbum->isValid()) {

            $album->setCreatedAt(new \DateTime());
            $album->setUser($user);

            $manager->persist($album);
            $manager->flush();

            $this->addFlash('success', 'Votre album a bien été crée.');
            return $this->redirectToRoute('user_account');
        }

        return $this->render('user/index.html.twig', [
            'account' => $account,
            'albums' => $user->getAlbums(),
            'formProfile' => $formProfile->createView(),
            'formUsername' => $formUsername->createView(),
            'formMusique' => $formMusique->createView(),
            'formAlbum' => $formAlbum->createView(),
            'amateur' => $repo->findOneByTitle('Amateur'),
            'golden' => $repo->findOneByTitle('Golden'),
            'premium' => $repo->findOneByTitle('Premium'),
            'current_menu' => 'profil'
        ]);
    }

    /**
     * Supprimer un compte utilisateur
     * 
     * @Route("/profile/account/{id}/delete", name="delete_user", methods="DELETE")
     */
    public function deleteUser(User $user, ObjectManager $manager, Request $request)
    {
        $account = $user->getAccount();
        // dd($user);
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->get('_token'))) {
            if ($user) {
                $session = $this->get('session');
                $session = new Session();
                $session->invalidate();
                $manager->remove($account);
                $manager->remove($user);
                $manager->flush();
            }
            $this->addFlash('success', 'Votre compte a été supprimé !');
        }
        return $this->redirectToRoute("home");
    }

    /**
     * Modif de musiques
     * 
     * @Route("/profile/mes-musiques", name="user_musics_show")
     * @Route("/profile/mes-musiques/{id}/edit", name="music_edit")
     */
    public function musiqueForm(Musique $musique = null, Request $request, ObjectManager $manager)
    {
        $user = $this->getUser();
        $musics = $user->getMusiques();
        $albums = $user->getAlbums();

        $form = $this->createForm(MusiqueType::class, $musique);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $musique->setUser($user);
            $manager->persist($musique);

            $manager->flush();

            //$this->addFlash('success', 'Votre musique a bien été modifiée !');
            return $this->redirectToRoute('user_musics_show');
        }

        return $this->render('user/musique.html.twig', [
            'formMusique' => $form->createView(),
            'user' => $user,
            'musics' => $musics,
            'albums' => $albums,
            'current_menu' => 'mesMusiques'
        ]);
    }

    /**
     * Suppression d'une musique
     * @Route("/profile/music/{id}/delete/", name="music_delete", methods="DELETE")
     */
    public function deleteMusic(Musique $musique, ObjectManager $manager, Request $request)
    {
        try {
            if ($this->isCsrfTokenValid('delete' . $musique->getId(), $request->get('_token'))) {
                $manager->remove($musique);
                $manager->flush();
            }
            return $this->redirectToRoute("user_musics_show");
        } catch (\Throwable $th) {
            return new Response("<h1>Une erreur est survénue, réessayez plus tard. Si l'erreur persiste, contactez le support technique à l'adresse support@obscury.com</h1>");
        }
    }

    // /**
    //  * Suppression du profil
    //  *
    //  * @Route("/profile/supprimer-photo-de-profil", name="delete_avatar")
    //  * 
    //  * @param ObjectManager $manager
    //  * @return void
    //  */
    // public function deleteAvatar(ObjectManager $manager)
    // {
    //     //Utilisateur courant
    //     // $user = $this->getUser();
    //     // //Je récupère le chemin du fichiers dans la bdd
    //     // $profile = $user->getAccount()->getProfile();
    //     // //Puis je le supprime
    //     // unset($profile);
    //     return $this->redirectToRoute('user_account');
    // }
}
