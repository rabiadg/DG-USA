<?php

namespace App\Entity;

use App\Application\Sonata\MediaBundle\Entity\Media;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\BaseEntity;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * Options
 *
 * @ORM\Table(name="cms_settings")
 * @ORM\Entity
 */
class Settings extends BaseEntity
{
    /**
     * @var string
     * @Assert\NotBlank(message="Title cannot be empty.")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Title cannot be longer than {{ limit }} characters"
     * )
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    protected $title;
    /**
     * @var string
     *
     * @ORM\Column(name="settings_key", type="string", length=255, nullable=false)
     */
    protected $settingsKey;
    /**
     * @var string
     * @Assert\NotBlank(message="Content cannot be empty."))
     * @ORM\Column(name="content", type="text", nullable=false)
     */
    protected $content;
    


    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="thumb_id", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $thumb;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="mobile_thumb", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $mobileThumb;

    /**
     * @var \App\Application\Sonata\MediaBundle\Entity\Media
     *
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\MediaBundle\Entity\Media",cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="email_thumb", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $emailThumb;

    public function __construct()
    {
        $this->content = array();
    }

    public function __toString()
    {
        return ($this->id) ? $this->title : 'Add ';
    }

    public function getSetting($name, $default = null)
    {
        if (!is_array($this->content)) {
            $this->content = json_decode($this->content, true);
        }
        return isset($this->content[$name]) ? $this->content[$name] : $default;
    }

    public function setSetting($name, $value)
    {
        $this->content[$name] = $value;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Settings
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set settingsKey
     *
     * @param string $settingsKey
     *
     * @return Settings
     */
    public function setSettingsKey($settingsKey)
    {
        $this->settingsKey = $settingsKey;

        return $this;
    }

    /**
     * Get settingsKey
     *
     * @return string
     */
    public function getSettingsKey()
    {
        return $this->settingsKey;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Settings
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        if (!is_array($this->content)) {
            return json_decode($this->content, true);
        }
        return $this->content;
    }


    /**
     * Set thumb
     *
     * @param \App\Application\Sonata\MediaBundle\Entity\Media $media
     *
     * @return Settings
     */
    public function setThumb(\App\Application\Sonata\MediaBundle\Entity\Media $thumb = null)
    {
        $this->thumb = $thumb;

        return $this;
    }

    /**
     * Get thumb
     *
     * @return \App\Application\Sonata\MediaBundle\Entity\Media
     */
    public function getThumb()
    {
        return $this->thumb;
    }

    /**
     * Set emailThumb
     *
     * @param \App\Application\Sonata\MediaBundle\Entity\Media $emailThumb
     * @return Settings
     */
    public function setEmailThumb(\App\Application\Sonata\MediaBundle\Entity\Media $emailThumb = null)
    {
        $this->emailThumb = $emailThumb;

        return $this;
    }

    /**
     * Get emailThumb
     *
     * @return \App\Application\Sonata\MediaBundle\Entity\Media
     */
    public function getEmailThumb()
    {
        return $this->emailThumb;
    }

    public function getMobileThumb(): ?Media
    {
        return $this->mobileThumb;
    }

    public function setMobileThumb(?Media $mobileThumb): self
    {
        $this->mobileThumb = $mobileThumb;

        return $this;
    }
}
