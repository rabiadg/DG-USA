<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Entity\Site;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class BaseEntity
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default": "CURRENT_TIMESTAMP"})
     */
    protected $updated_at;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"default" : 1})
     */
    protected $enabled = true;



    /**
     * @var \App\Application\Sonata\PageBundle\Entity\Site
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\PageBundle\Entity\Site", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="site_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $site;



   /* static public function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }*/

    function cleanText($text)
    {
        return trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9-.: ]/', ' ', urldecode(html_entity_decode(strip_tags($text))))));
    }

    function slugify( $text ) {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'ASCII//IGNORE//TRANSLIT', $text);
        $text = strtolower(trim($text));
        $text = preg_replace('~[^-\w]+~', '', $text);

        return empty($text) ? substr( md5( time() ), 0, 8 ) : $text;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

}
