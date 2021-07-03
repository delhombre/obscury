<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Serializable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccountRepository")
 * @Vich\Uploadable
 */
class Account implements Serializable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\User", inversedBy="account", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $profile;

    /**
     * @Vich\UploadableField(mapping="user_profiles", fileNameProperty="profile")
     * @Assert\Image(mimeTypes={"image/jpeg", "image/jpg", "image/png"}, mimeTypesMessage="Format d'image invalide. Seuls les formats jpeg, png et jpg sont acceptés", maxSize="5M")
     * @var File
     */
    private $profileFile;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        if ($user) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }

        return $this;
    }

    public function setProfileFile(File $profile = null)
    {
        $this->profileFile = $profile;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($profile) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getProfileFile()
    {
        return $this->profileFile;
    }

    public function setProfile($profile)
    {
        $this->profile = $profile;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->profile,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->profile,
        ) = unserialize($serialized, array('allowed_classes' => false));
    }

    public function __toString()
    {
        return $this->user->getEmail();
    }
}
