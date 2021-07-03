<?php

namespace App\Entity;

use App\Entity\User;
use App\Entity\PostLike;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\Common\Collections\ArrayCollection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MusiqueRepository")
 * @Vich\Uploadable
 */
class Musique
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=2, max=255, minMessage="Votre titre est bien trop court !")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(min=1, max=255, minMessage="Le nom de l'artiste est bien trop court !")
     */
    private $featuring;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="musique_images", fileNameProperty="image")
     * @Assert\Image(mimeTypes={"image/jpeg", "image/jpg", "image/png", "image/JPEG", "image/JPG", "image/PNG"}, mimeTypesMessage="Format d'image invalide. Seuls jpeg, png et jpg sont acceptés", maxSize="15M")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $music;

    /**
     * @Vich\UploadableField(mapping="musique_musics", fileNameProperty="music")
     * @Assert\File(mimeTypes={"audio/mpeg", "audio/MPEG", "audio/mpeg-4", "audio/MPEG-4", "audio/mp3", "audio/MP3", "audio/mp4", "audio/MP4", "audio/x-m4a", "audio/x-M4A", "audio/xs-ms-wma", "audio/vnd.rn-realaudio", "audio/x-wav", "audio/wav"}, mimeTypesMessage="Format audio invalide.", maxSize="25M")
     * @var File
     */
    private $musicFile;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category", inversedBy="musiques")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="musique", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostLike", mappedBy="musique")
     */
    private $likes;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="musiques")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Album", inversedBy="musique")
     */
    private $album;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PostDownload", mappedBy="musique")
     */
    private $downloads;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->likes = new ArrayCollection();
        $this->downloads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Retourne un slug
     *
     * @return string
     */
    public function getSlug(): string
    {
        return (new Slugify())->slugify($this->getUser() . '-' . $this->title);
    }

    public function getFeaturing(): ?string
    {
        return $this->featuring;
    }

    public function setFeaturing(?string $featuring): self
    {
        $this->featuring = $featuring;

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function getMusicFile()
    {
        return $this->musicFile;
    }

    public function setMusicFile(File $music = null)
    {
        $this->musicFile = $music;
    }

    public function getMusic()
    {
        return $this->music;
    }

    public function setMusic($music)
    {
        $this->music = $music;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setMusique($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getMusique() === $this) {
                $comment->setMusique(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|PostLike[]
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(PostLike $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes[] = $like;
            $like->setMusique($this);
        }

        return $this;
    }

    public function removeLike(PostLike $like): self
    {
        if ($this->likes->contains($like)) {
            $this->likes->removeElement($like);
            // set the owning side to null (unless already changed)
            if ($like->getMusique() === $this) {
                $like->setMusique(null);
            }
        }

        return $this;
    }

    /**
     * Permet de savoir si ce musique est liké par un utilisateur
     *
     * @param User $user
     * @return boolean
     */
    public function isLikedByUser(User $user): bool
    {
        foreach ($this->likes as $like) {
            if ($like->getUser() === $user) return true;
        }
        return false;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->album = $album;

        return $this;
    }

    /**
     * @return Collection|PostDownload[]
     */
    public function getDownloads(): Collection
    {
        return $this->downloads;
    }

    public function addDownload(PostDownload $download): self
    {
        if (!$this->downloads->contains($download)) {
            $this->downloads[] = $download;
            $download->setMusique($this);
        }

        return $this;
    }

    public function removeDownload(PostDownload $download): self
    {
        if ($this->downloads->contains($download)) {
            $this->downloads->removeElement($download);
            // set the owning side to null (unless already changed)
            if ($download->getMusique() === $this) {
                $download->setMusique(null);
            }
        }

        return $this;
    }
}
