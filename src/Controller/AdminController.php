<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Musique;
use App\Form\AddUserAlbumType;
use App\Form\AddUserMusicType;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    /**
     * @Route("/profile/add-user-new-music", name="admin_index")
     */
    public function addUserMusic(Request $request, ObjectManager $manager)
    {
        $musique = new Musique();
        $form = $this->createForm(AddUserMusicType::class, $musique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $musique->setCreatedAt(new DateTime());

            $manager->persist($musique);
            $manager->flush();

            $this->addFlash('success', 'Musique créée.');
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('admin/index.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/profile/add-user-new-album", name="admin_addAlbum")
     */
    public function addUserAlbum(Request $request, ObjectManager $manager)
    {
        $album = new Album();
        $form = $this->createForm(AddUserAlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->setCreatedAt(new DateTime());

            $manager->persist($album);
            $manager->flush();

            $this->addFlash('success', 'Album crée.');
            return $this->redirectToRoute('admin_addAlbum');
        }

        return $this->render('admin/album.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
