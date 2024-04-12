<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Musique;
use App\Form\AddUserAlbumType;
use App\Form\AddUserMusicType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends AbstractController
{
    protected $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/profile/add-user-new-music", name="admin_index")
     */
    public function addUserMusic(Request $request)
    {
        $musique = new Musique();
        $form = $this->createForm(AddUserMusicType::class, $musique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $musique->setCreatedAt(new DateTime());

            $this->manager->persist($musique);
            $this->manager->flush();

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
    public function addUserAlbum(Request $request)
    {
        $album = new Album();
        $form = $this->createForm(AddUserAlbumType::class, $album);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $album->setCreatedAt(new DateTime());

            $this->manager->persist($album);
            $this->manager->flush();

            $this->addFlash('success', 'Album crée.');
            return $this->redirectToRoute('admin_addAlbum');
        }

        return $this->render('admin/album.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
