<?php

namespace App\Application\Sonata\PageBundle\Entity;

use Sonata\PageBundle\Entity\BasePage as BasePage;
use Doctrine\ORM\Mapping as ORM;

/**
 * Page
 *
 * @ORM\Table(name="page__page")
 * @ORM\Entity
 */
class Page extends BasePage
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 0})
     */
    protected $changeSlug = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $uuid ;

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }


    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function preUpdate(): void
    { 
        $this->updatedAt = new \DateTime();
    }

    public function getChangeSlug(): ?bool
    {
        return $this->changeSlug;
    }

    public function setChangeSlug(?bool $changeSlug): self
    {
        $this->changeSlug = $changeSlug;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

}
