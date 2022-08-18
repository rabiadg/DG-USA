<?php

namespace App\Application\CMS\MenuBundle\Entity;

use App\Entity\BaseEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * CmsMenu
 *
 * @ORM\Table(name="cms_menu")
 * @ORM\Entity
 */
class CmsMenu extends BaseEntity{
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
	 * @var boolean
	 *
	 * @ORM\Column(name="depth", type="string", nullable=false)
	 */
	private $depth;



	public function __toString() {
		return ( $this->id ) ? $this->title : 'Create';
	}


	/**
	 * Set title
	 *
	 * @param string $title
	 *
	 * @return CmsMenu
	 */
	public function setTitle( $title ) {
		$this->title = $title;

		return $this;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}




	/**
	 * Set depth
	 *
	 * @param boolean $depth
	 *
	 * @return CmsMenu
	 */
	public function setDepth( $depth ) {
		$this->depth = $depth;

		return $this;
	}

	/**
	 * Get depth
	 *
	 * @return boolean
	 */
	public function getDepth() {
		return $this->depth;
	}


	/**
	 * @ORM\OneToMany(targetEntity="\App\Application\CMS\MenuBundle\Entity\CmsMenuItems", mappedBy="menu", cascade={"persist", "merge"}, orphanRemoval=true, fetch="EAGER")
	 * @ORM\OrderBy({"id" = "ASC"})
	 */
	protected $items;

	/**
	 * Set items
	 *
	 * @param string $items
	 *
	 * @return CmsMenu
	 */
	public function setItems( $items ) {
		$this->items = $items;

		return $this;
	}

	/**
	 * Get items
	 *
	 * @return string
	 */
	public function getItems() {
		return $this->items;
	}

	public function getTopLevelItems() {
		$TopLevelItems = new \Doctrine\Common\Collections\ArrayCollection();
		foreach ( $this->items as $val ) {
			$parent = $val->getParent();
			if ( is_null( $val->getParent() ) || empty( $parent ) ) {
				$TopLevelItems->add( $val );
			}
		}

		return $TopLevelItems;

	}

	protected $enableExport;

	public function getEnableExport() {
		return ( $this->enabled == 1 ) ? 'Yes' : 'No';
	}

}
