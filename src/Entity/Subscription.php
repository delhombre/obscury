<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SubscriptionRepository")
 */
class Subscription
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $hasMusic;

    /**
     * @ORM\Column(type="integer")
     */
    private $hasVideo;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hasCover;

    public function __construct()
    {
        $this->isActive = false;
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getHasMusic(): ?int
    {
        return $this->hasMusic;
    }

    public function setHasMusic(int $hasMusic): self
    {
        $this->hasMusic = $hasMusic;

        return $this;
    }

    public function getHasVideo(): ?int
    {
        return $this->hasVideo;
    }

    public function setHasVideo(int $hasVideo): self
    {
        $this->hasVideo = $hasVideo;

        return $this;
    }

    public function getFormattedPrice()
    {
        return number_format($this->price, 0, '', ' ');
    }

    public function getHasCover(): ?int
    {
        return $this->hasCover;
    }

    public function setHasCover(?int $hasCover): self
    {
        $this->hasCover = $hasCover;

        return $this;
    }
}
