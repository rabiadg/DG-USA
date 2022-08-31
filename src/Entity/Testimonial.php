<?php

namespace App\Entity;

use App\Repository\HomePageSliderRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Application\Sonata\MediaBundle\Entity\Media;

/**
 * @ORM\Entity(repositoryClass=App\Repository\TestimonialRepository::class)
 * @ORM\Table(name="cms_testimonial")
 */
class Testimonial extends BaseEntity
{


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $heading;

    /**
     * @ORM\Column(name="description",type="text", nullable=true)
     */
    private $description;

    public function __toString()
    {
        if (method_exists($this, 'setHeading')) {
            return strval($this->getHeading());
        } else {
            return "";
        }
    }

    public function getHeading(): ?string
    {
        return $this->heading;
    }

    public function setHeading(?string $heading): self
    {
        $this->heading = $heading;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

}
