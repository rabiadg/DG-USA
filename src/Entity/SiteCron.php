<?php

namespace App\Entity;

use App\Application\Sonata\PageBundle\Entity\Site;
use App\Repository\SiteCronRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="cms_site_cron")
 * @ORM\Entity(repositoryClass=SiteCronRepository::class)
 */
class SiteCron
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;


    /**
     * @var string
     * @ORM\Column(name="records_inserted", type="string", length=255, nullable=true)
     */
    protected $recordsInserted = 0;

    /**
     * @var string
     * @ORM\Column(name="total_records", type="string", length=255, nullable=true)
     */
    protected $totalRecords = 0;

    /**
     * @var string
     * @ORM\Column(name="all_inserted", type="string", length=255, nullable=true)
     */
    protected $allInserted;

    /**
     * @var string
     * @ORM\Column(name="module", type="string", length=255, nullable=true)
     */
    protected $module;

    /**
     * @var string
     * @ORM\Column(name="last_insert_id", type="string", length=255, nullable=true)
     */
    protected $lastInsertId;

    /**
     * @var string
     * @ORM\Column(name="status", type="string", length=255, nullable=true,)
     */
    protected $status = 'In Progress';


    /**
     * @var \App\Application\Sonata\PageBundle\Entity\Site
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\PageBundle\Entity\Site", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="from_site", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $fromSite;

    /**
     * @var \App\Application\Sonata\PageBundle\Entity\Site
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\PageBundle\Entity\Site", cascade={"persist"})
     * @ORM\JoinColumns({
     * @ORM\JoinColumn(name="to_site", referencedColumnName="id", onDelete="SET NULL")
     * })
     */
    protected $toSite;

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

    public function __toString()
    {
       return $this->getToSite()?$this->getToSite()->getName():'';
    }

    public function getRecordsInserted(): ?string
    {
        return $this->recordsInserted;
    }

    public function setRecordsInserted(?string $recordsInserted): self
    {
        $this->recordsInserted = $recordsInserted;

        return $this;
    }

    public function getTotalRecords(): ?string
    {
        return $this->totalRecords;
    }

    public function setTotalRecords(?string $totalRecords): self
    {
        $this->totalRecords = $totalRecords;

        return $this;
    }

    public function getAllInserted(): ?string
    {
        return $this->allInserted;
    }

    public function setAllInserted(?string $allInserted): self
    {
        $this->allInserted = $allInserted;

        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getLastInsertId(): ?string
    {
        return $this->lastInsertId;
    }

    public function setLastInsertId(?string $lastInsertId): self
    {
        $this->lastInsertId = $lastInsertId;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
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

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getFromSite(): ?Site
    {
        return $this->fromSite;
    }

    public function setFromSite(?Site $fromSite): self
    {
        $this->fromSite = $fromSite;

        return $this;
    }

    public function getToSite(): ?Site
    {
        return $this->toSite;
    }

    public function setToSite(?Site $toSite): self
    {
        $this->toSite = $toSite;

        return $this;
    }

}
