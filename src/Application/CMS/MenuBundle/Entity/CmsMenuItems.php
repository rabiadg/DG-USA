<?php

namespace App\Application\CMS\MenuBundle\Entity;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * CmsMenuItems
 *
 * @ORM\Table(name="cms_menu_items", indexes={@ORM\Index(name="fk_parent", columns={"parent_id"}), @ORM\Index(name="fk_menu", columns={"menu_id"}), @ORM\Index(name="fk_page", columns={"page_id"})})
 * @ORM\Entity
 */
class CmsMenuItems extends BaseEntity
{
    /**
     * @var string
     * @Assert\NotBlank()
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
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="target_url", type="text", nullable=true)
     */
    private $targetUrl;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_child_link", type="boolean", nullable=false)
     */
    private $showChildLink;




    /**
     * @var \App\Application\CMS\MenuBundle\Entity\CmsMenuItems
     *
     * @ORM\ManyToOne(targetEntity="App\Application\CMS\MenuBundle\Entity\CmsMenuItems", fetch="EAGER", inversedBy="childItems")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $parent;

    /**
     * @var \App\Application\CMS\MenuBundle\Entity\CmsMenu
     *
     * @ORM\ManyToOne(targetEntity="App\Application\CMS\MenuBundle\Entity\CmsMenu",fetch="EAGER", inversedBy="items")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="menu_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $menu;

    /**
     * @var \App\Application\Sonata\PageBundle\Entity\Page
     *
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\PageBundle\Entity\Page", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $page;

	/**
	 * @ORM\OneToMany(targetEntity="App\Application\CMS\MenuBundle\Entity\CmsMenuItems", mappedBy="parent", cascade={"persist", "merge"}, orphanRemoval=true, fetch="LAZY")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $childItems;

    public function __construct() {
        $this->childItems = new ArrayCollection();
    }


    /**
	 * Set childItems
	 *
	 * @param string $childItems
	 *
	 * @return CmsMenu
	 */
	public function setChildItems( $childItems ) {
		$this->childItems = $childItems;

		return $this;
	}

	/**
	 * Get childItems
	 *
	 * @return string
	 */
	public function getChildItems() {
	/*	if($this->getShowChildLink()){
			ladybug_dump($this->childItems->first());die('Call');
		}*/
		$children = new \Doctrine\Common\Collections\ArrayCollection();
		return $children = $this->childItems;
		#return $this->childItems;
	}
    /**
     * Set title
     *
     * @param string $title
     * @return CmsMenuItems
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
     * Set type
     *
     * @param string $type
     * @return CmsMenuItems
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set targetUrl
     *
     * @param string $targetUrl
     * @return CmsMenuItems
     */
    public function setTargetUrl($targetUrl)
    {
        $this->targetUrl = $targetUrl;

        return $this;
    }

    /**
     * Get targetUrl
     *
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Set showChildLink
     *
     * @param boolean $showChildLink
     * @return CmsMenuItems
     */
    public function setShowChildLink($showChildLink)
    {
        $this->showChildLink = $showChildLink;

        return $this;
    }

    /**
     * Get showChildLink
     *
     * @return boolean
     */
    public function getShowChildLink()
    {
        return $this->showChildLink;
    }



    /**
     * Set parent
     *
     * @param \App\Application\CMS\MenuBundle\Entity\CmsMenuItems $parent
     * @return CmsMenuItems
     */
    public function setParent(\App\Application\CMS\MenuBundle\Entity\CmsMenuItems $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \App\Application\CMS\MenuBundle\Entity\CmsMenuItems
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set menu
     *
     * @param \App\Application\CMS\MenuBundle\Entity\CmsMenu $menu
     * @return CmsMenuItems
     */
    public function setMenu(\App\Application\CMS\MenuBundle\Entity\CmsMenu $menu = null)
    {
        $this->menu = $menu;

        return $this;
    }

    /**
     * Get menu
     *
     * @return \App\Application\CMS\MenuBundle\Entity\CmsMenu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * Set page
     *
     * @param \App\Application\Sonata\PageBundle\Entity\Page $page
     * @return CmsMenuItems
     */
    public function setPage(\App\Application\Sonata\PageBundle\Entity\Page $page = null)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get page
     *
     * @return \App\Application\Sonata\PageBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

}
