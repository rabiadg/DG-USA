<?php

namespace App\Entity;

use App\Repository\PagesSlugHistoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PagesSlugHistoryRepository::class)
 */
class PagesSlugHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="slug",type="string",nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(name="page_uuid",type="string",nullable=true)
     */
    private $page_uuid;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPageUuid(): ?string
    {
        return $this->page_uuid;
    }

    public function setPageUuid(?string $page_uuid): self
    {
        $this->page_uuid = $page_uuid;

        return $this;
    }
}
