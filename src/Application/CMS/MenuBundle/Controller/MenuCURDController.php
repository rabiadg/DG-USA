<?php

namespace App\Application\CMS\MenuBundle\Controller;


use App\Application\Sonata\PageBundle\Entity\Page;
use App\Controller\CMSCRUDController;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use CMS\BaseBundle\Controller\CMSBaseAdminCRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Application\CMS\MenuBundle\Entity\CmsMenuItems;

class MenuCURDController extends CMSCRUDController {

	public function getPage( $id = null ) {
		if ( $id == null || $id == - 1 ) {
			return null;
		}
		$DM = $this->getDoctrineManager();

		return $DM->getRepository( Page::class )->find( $id );
	}


	public function traverseTree( $tree_array, $menu, $object ) {
		$DM = $this->getDoctrineManager();

		foreach ( $tree_array as $row ) {
			$CmsMenuItems = new CmsMenuItems();
			if ( $row['tag'] == 'LI' ) {
			    $page=null;
			    if(isset($row['page_id'])){
                    $page = $DM->getRepository(Page::class)->find($row['page_id']);
                }

				$CmsMenuItems->setCreatedAt( new \DateTime( 'now' ) )
				             ->setUpdatedAt( new \DateTime( 'now' ) )
				             ->setMenu( $object )
				             ->setPage( $page )
				             ->setType( $row['data_type'])
				             ->setParent( $menu )
				             ->setTitle( $row['title'] )
				             ->setShowChildLink( $row['target'] )
				             ->setTargetUrl( $row['link'] );
				$DM->persist( $CmsMenuItems );
			}
			if ( isset( $row['children'] ) ) {
				if ( $row['tag'] == 'OL' ) {
					$CmsMenuItems = $menu;
				}
				$this->traverseTree( $row['children'], $CmsMenuItems, $object );
			}
		}
	}

	public function removeItems( $object ) {

		$DM = $this->getDoctrineManager();
		foreach ( $object->getItems() as $key => $item ) {
			$DM->remove( $item );
		}

		$DM->flush();
	}

	public function saveMenuAction( $id = null ) {
		$errors = array();

		//$errors  = array('errors','errors','errors');
		$request = $this->getRequest();
		$id      = $request->get( $this->admin->getIdParameter() );
		$object  = $this->admin->getObject( $id );

		if ( ! $object ) {
			$errors[] = sprintf( 'unable to find the object with id : %s', $id );
		}
		$DM = $this->getDoctrineManager();
		if ( $request->getMethod() == 'POST' && empty( $errors ) ) {
			$menuDataString = $request->get( 'tree' );
			if ( $this->isJson( $menuDataString ) ) {
				$menuData = json_decode( $menuDataString, true );

				if ( ! empty( $menuData ) ) {
					/*echo '<pre>';print_r($menuData);echo '</pre>';die('Call');*/
					$this->removeItems( $object );
					$this->traverseTree( $menuData['children'], null, $object );
					$DM->flush();

					return new JsonResponse( array( 'success' => true, 'message' => 'Menu Saved Successfully' ) );
				} else {
					$errors[] = 'No data recieved to process your request';
				}
			} else {
				$errors[] = 'Invalid string expected json';
			}
		} else {
			$errors[] = 'Invalid request method';
		}

		return new JsonResponse( array(
			'success' => false,
			'message' => '<li>' . join( '</li><li>', $errors ) . '</li>'
		) );
	}

	public function menuBuilderAction( $id = null ) {
		// the key used to lookup the template

		$templateKey = 'MenuDesignerAdmin.html.twig';
		$request =  $this->getRequest();
		$id          = $request->get( $this->admin->getIdParameter() );
		$object      = $this->admin->getObject( $id );
		$DM          = $this->getDoctrineManager();
		$pageRepo    = $DM->getRepository( Page::class );
		//$programRepo = $DM->getRepository( 'CMS\ProgramsBundle\Entity\CmsPrograms' );
		$query       = $pageRepo->createQueryBuilder( 'p' )
		                        ->select( 'p' )
		                        ->where( 'p.enabled = 1' )
		                        //->andWhere( 'p.isPage = 1' )
		                        ->andWhere( 'p.site = :site' )
                                ->andWhere( 'p.routeName NOT IN (:routeName)')
                                ->setParameter('routeName', array('_page_internal_error_not_found', '_page_internal_error_fatal', '_page_internal_global'))
		                        ->setParameter( 'site', $this->admin->getSite() )
		                        ->orderBy( 'p.id', 'DESC' );
		$pages       = $query->getQuery()->getResult();

		/*$query    = $programRepo->createQueryBuilder( 'p' )
		                        ->select( 'p' )
		                        ->where( 'p.enable = 1' )
		                        ->andWhere( 'p.site = :site' )
		                        ->setParameter( 'site', $this->admin->getSite() )
		                        ->orderBy( 'p.id', 'DESC' );
		$programs = $query->getQuery()->getResult();*/

		if ( ! $object ) {
			throw new NotFoundHttpException( sprintf( 'unable to find the object with id : %s', $id ) );
		}

		$this->admin->setSubject( $object );

		return $this->render( 'Application/CMS/MenuBundle/Resources/views/Admin/Design/MenuDesignerAdmin.html.twig', array(
			'base_template'=>$this->getBaseTemplate(),
		    'admin'=>$this->admin,
		    'action' => 'edit',
			'form'   => '',
			'object' => $object,
			'objectId' => $object->getId(),
			'pages'  => $pages,
			//'programs'  => $programs,
		) );
	}
}

