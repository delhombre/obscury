<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostDownloadRepository")
 */
class PostDownload
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Musique", inversedBy="downloads")
     */
    private $musique;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="downloads")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMusique(): ?Musique
    {
        return $this->musique;
    }

    public function setMusique(?Musique $musique): self
    {
        $this->musique = $musique;

        return $this;
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
        return $this->musique->getTitle();
    }
}
