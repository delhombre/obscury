<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Contact;
use App\Entity\Musique;
use App\Entity\PostDownload;
use App\Entity\PostLike;
use App\Form\ContactType;
use App\Repository\AlbumRepository;
use App\Repository\MusiqueRepository;
use App\Repository\PostLikeRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Service\Mailer\MailerService;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\CacheInterface;
use Vich\UploaderBundle\Handler\DownloadHandler;

class SiteController extends AbstractController
{
    protected $manager;
    protected $stopwatch;
    protected $cache;

    public function __construct(EntityManagerInterface $manager, Stopwatch $stopwatch, CacheInterface $cache)
    {
        $this->manager = $manager;
        $this->stopwatch = $stopwatch;
        $this->cache = $cache;
    }

    /**
     * Accueil
     * 
     * @Route("/", name="home")
     */
    public function home(MusiqueRepository $repo, AlbumRepository $albumRepository)
    {
        return $this->render('site/home.html.twig', [
            'musiques' => $repo->findRecent(6),
            'albums' => $albumRepository->findRecent(5)
        ]);
    }

    /**
     * L'ensemble des musiques
     * 
     * @Route("/musiques", name="music")
     */
    public function index(MusiqueRepository $repo, PaginatorInterface $paginator, Request $request)
    {
        $musiques = $paginator->paginate(
            $repo->findRecent(),
            $request->query->getInt('page', 1), /*page number*/
            12 /*limit per page*/
        );
        return $this->render('site/index.html.twig', [
            'musiques' => $musiques
        ]);
    }

    /**
     * Affiche les videos
     * 
     * @Route("/videos", name="video")
     *
     * @return void
     */
    public function video(VideoRepository $repo, PaginatorInterface $paginator, Request $request)
    {
        $videos = $paginator->paginate(
            $repo->findRecent(9),
            $request->query->getInt('page', 1), /*page number*/
            9 /*limit per page*/
        );

        return $this->render('site/videos.html.twig', [
            'videos' => $videos
        ]);
    }

    /**
     * Permet de liker ou unliker une musique
     *@Route("/musique/{id}/like", name="music_like")
     * @param Musique $musique
     * @param ObjectManager $manager
     * @param PostLikeRepository $likeRepo
     * @return Response
     */
    public function like(Musique $musique, PostLikeRepository $likeRepo): Response
    {
        $user = $this->getUser();

        if (!$user) return $this->json([
            'code' => '403',
            'message' => 'Désole il faut être connecté'
        ], 403);

        if ($musique->isLikedByUser($user)) {

            $like = $likeRepo->findOneBy([
                'musique' => $musique,
                'user' => $user
            ]);

            $this->manager->remove($like);
            $this->manager->flush();

            return $this->json([
                'message' => 'Like bien delete',
                'likes' => $likeRepo->count(['musique' => $musique])
            ], 200);
        }

        $like = new PostLike();
        $like->setMusique($musique)
            ->setUser($user);

        $this->manager->persist($like);
        $this->manager->flush();

        return $this->json(['code' => '200', 'message' => 'Like bien ajouté', 'likes' => $likeRepo->count(['musique' => $musique])], 200);
    }

    /**
     * Permet d'afficher une musique
     * Permet de commenter une musique
     * 
     * @Route("/musiques/{slug}-{id}", name="music_show", requirements={"slug": "[a-z0-9\-]*"})
     */
    public function show(Musique $musique, string $slug)
    {
        $slugify = $musique->getSlug();

        if ($slugify !== $slug) {
            return $this->redirectToRoute('music_show', [
                'id' => $musique->getId(),
                'slug' => $slugify
            ], 301);
        }


        return $this->render('site/show.html.twig', [
            'musique' => $musique,
            'feat' => $musique->getFeaturing() !== null,
            'musiques' => $musique->getUser()->getMusiques()
        ]);
    }

    /**
     * Permet de télécharger une musique
     * 
     * @Route("/musique/{id}/download", name="music_download")
     *
     * @param Musique $musique
     * @param MusiqueRepository $repo
     * @return void
     */
    public function download(Musique $musique, DownloadHandler $downloadHandler): Response
    {
        $download = new PostDownload();
        $user = $this->getUser();
        $download->setMusique($musique)
            ->setUser($user);
        $this->manager->persist($download);
        $this->manager->flush();
        return $downloadHandler->downloadObject($musique, 'musicFile', null, true, true);
    }

    /**
     * @Route("/artistes", name="artists")
     *
     * @param UserRepository $repo
     * @return void
     */
    public function artists(UserRepository $repo)
    {
        return $this->render('site/artists.html.twig', [
            'users' => $repo->findAll()
        ]);
    }

    // /**
    //  * Show an artist
    //  * @Route("/artiste/{id}", name="artist_show")
    //  *
    //  * @return void
    //  */
    // public function artistShow(User $user)
    // {
    //     return $this->render('site/artistShow.html.twig', [
    //         'user' => $user
    //     ]);
    // }

    /**
     * Formulaire de contact
     *
     * @Route("/contact", name="contact")
     */
    public function contact(Request $request, MailerService $mailer)
    {

        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bodyMail = $mailer->createBodyMail('emails/contact.html.twig', [
                'contact' => $contact
            ]);
            $mailer->sendMessage($contact->getEmail(), 'contact@obscury.com', $contact->getObject() . ' ~ ' . $contact->getMessage(), $bodyMail);
            $this->addFlash('contact_success', 'Votre message a bien été envoyé, nous vous répondrons dans les plus brefs délais');
            return $this->redirectToRoute('contact');
        }
        return $this->render('site/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/albums", name="albums")
     *
     * @param AlbumRepository $repo
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return void
     */
    public function albums(AlbumRepository $repo, PaginatorInterface $paginator, Request $request)
    {
        $albums = $paginator->paginate(
            $repo->findRecent(5),
            $request->query->getInt('page', 1), /*page number*/
            5 /*limit per page*/
        );
        return $this->render('site/albums.html.twig', [
            'albums' => $albums,
        ]);
    }

    /**
     * @Route("/album/{id}", name="album_show")
     */
    public function albumShow(Album $album)
    {
        return $this->render('site/albumShow.html.twig', [
            'album' => $album,
            'artistName' => str_replace(' ', '', $album->getUser()->getUsername())
        ]);
    }
}
