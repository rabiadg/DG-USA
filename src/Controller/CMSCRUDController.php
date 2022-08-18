<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Admin\BaseFieldDescription;
use Symfony\Component\HttpFoundation\JsonResponse;
use CMS\BaseBundle\Configuration\Configuration;

class CMSCRUDController extends CRUDController {
	private $defaultSite = false;

	public function getDoctrineManager( $manager = 'default' ) {
		return $this->getDoctrine()->getManager( $manager );
	}

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

	public $entityName, $shortEntityName;

	public function getCurrentEntityName() {
		$this->shortEntityName = $this->convertClassNameToShortcutNotations( $this->admin->getClass() );
		$this->entityName      = $this->admin->getClass();//'CMSNewsBundle:News\Post';
	}

	public function convertClassNameToShortcutNotations( $className ) {
		$cleanClassName = str_replace( '\\Entity', '\:', $className );
		$parts          = explode( '\\', $cleanClassName );

		return implode( '', $parts );
	}

	function isJson( $string ) {
		json_decode( $string );

		return ( json_last_error() == JSON_ERROR_NONE );
	}

	public function getBuilderSettings() {
		$settingArray = array();
		$DM           = $this->getDoctrineManager();
		$settings     = $DM->getRepository( 'Traffic\SettingsBundle\Entity\TrafficBuilderSetting' )->findAll();
		foreach ( $settings as $key => $val ) {
			$settingArray[ $val->getSettingsKey() ] = $val->getSettingValue();
		}

		return $settingArray;
	}

	public function sortAction() {

		$this->getCurrentEntityName();
		$SliderCategories   = $programsDropdown = $programCredentialsDropdown = $programCategories = null;
		$DM                 = $this->getDoctrine()->getManager();
		$baseSliderBundle   = 'CMSSliderBundle';
		$baseProgramBundle  = 'CMSProgramsBundle';
		$slider             = $this->getRequest()->query->get( 'slider' );
		$programType        = $this->getRequest()->query->get( 'type' );
		$programCategory    = $this->getRequest()->query->get( 'programCategory' );
		$selectedProgram    = $this->getRequest()->query->get( 'selectedProgram' );
		$selectedCredential = $this->getRequest()->query->get( 'selectedCredential' );
		$mediaType          = $this->getRequest()->query->get( 'mediaType' );

		if ( $this->shortEntityName == $baseSliderBundle . ':CmsSliderItems' ) {

			$SliderCategories = $DM->getRepository( $baseSliderBundle . ':CmsSlider' )->findBy( array(
				'site' => $this->getSiteByLocale()
			) );

			if ( ! empty( $slider ) ) {
				$sliderInfo     = $DM->getRepository( $baseSliderBundle . ':CmsSliderItems' )->find( $slider );
				$SortRepository = $DM->getRepository( $this->shortEntityName )
				                     ->findBy( array(
					                     'enable' => true,
					                     'site'   => $this->getSiteByLocale(),
					                     'slider' => $sliderInfo->getId()
				                     ), array( 'sortOrder' => 'ASC' ) );
			}
			else {
				$SortRepository = null;
			}

		}
		else if ( $this->shortEntityName == $baseProgramBundle . ':CmsPrograms' ) {

			$programCategories = $DM->getRepository( $baseProgramBundle . ':CmsProgramCategories' )->findBy( array(
				'site' => $this->getSiteByLocale()
			) );

			if ( ! empty( $programCategory ) ) {

				$SortRepository = $DM->getRepository( $this->shortEntityName )
				                     ->findBy( array(
					                     'enable'          => true,
					                     'site'            => $this->getSiteByLocale(),
					                     'programCategory' => $programCategory
				                     ), array( 'sortOrder' => 'ASC' ) );
			}
			else {
				$SortRepository  = null;
				$programCategory = 1;
			}

		}
		else if ( $this->shortEntityName == $baseProgramBundle . ':CmsProgramCredentials' ) {

			$programsDropdown = $DM->getRepository( $baseProgramBundle . ':CmsPrograms' )->findBy( array(
				'site' => $this->getSiteByLocale()
			) );

			if ( ! empty( $selectedProgram ) ) {
				$SortRepository = $DM->getRepository( $this->shortEntityName )
				                     ->findBy( array(
					                     'enable'  => true,
					                     'site'    => $this->getSiteByLocale(),
					                     'program' => $selectedProgram
				                     ), array( 'sortOrder' => 'ASC' ) );
			}
			else {
				$SortRepository = null;
			}

		}
		else if ( $this->shortEntityName == $baseProgramBundle . ':CmsProgramCredentialCourses' ) {

			$programCredentialsDropdown = $DM->getRepository( $baseProgramBundle . ':CmsProgramCredentials' )->findBy( array(
				'site' => $this->getSiteByLocale()
			) );

			if ( ! empty( $selectedCredential ) ) {
				$SortRepository = $DM->getRepository( $this->shortEntityName )
				                     ->findBy( array(
					                     'enable'            => true,
					                     'site'              => $this->getSiteByLocale(),
					                     'programCredential' => $selectedCredential
				                     ), array( 'sortOrder' => 'ASC' ) );
			}
			else {
				$SortRepository = null;
			}

		}
		else if ( $this->shortEntityName == $baseProgramBundle . ':CmsProgramCategories' ) {

			if ( ! empty( $programType ) ) {
				$SortRepository = $DM->getRepository( $this->shortEntityName )
				                     ->findBy( array(
					                     'enable' => true,
					                     'site'   => $this->getSiteByLocale(),
					                     'type'   => $programType
				                     ), array( 'sortOrder' => 'ASC' ) );

			}
			else {
				$SortRepository = null;
				$programType    = 1;
			}
		}
		else if ( $this->shortEntityName == 'CMSMediaBundle:MediaGallery' ) {

			if ( ! empty( $mediaType ) ) {
				$SortRepository = $DM->getRepository( 'CMS\MediaBundle\Entity\Media\Gallery' )
				                     ->findBy( array(
					                     'site'   => $this->getSiteByLocale(),
					                     'type'   => $mediaType
				                     ), array( 'sortOrder' => 'ASC' ) );

			}
			else {
				$SortRepository = null;
				$mediaType    = 1;
			}
		}
		else if ( $this->shortEntityName == $baseSliderBundle . ':CmsCarouselSliderItems' ) {

			$SliderCategories = $DM->getRepository( $baseSliderBundle . ':CmsCarouselSlider' )->findBy( array(
				'site' => $this->getSiteByLocale()
			) );

			if ( ! empty( $slider ) ) {
					$sliderInfo = $DM->getRepository( $baseSliderBundle . ':CmsCarouselSlider' )->find( $slider );

				$SortRepository = $DM->getRepository( $this->shortEntityName )
				                     ->findBy( array(
					                     'enable' => true,
					                     'site'   => $this->getSiteByLocale(),
					                     'slider' => $sliderInfo->getId()
				                     ), array( 'sortOrder' => 'ASC' ) );
			}
			else {
				$SortRepository = null;
			}

		}
		else {
			$SortRepository = $DM->getRepository( $this->entityName )->findBy( array(
				'site' => $this->getSiteByLocale()
			), array( 'sortOrder' => 'ASC' ) );

		}

		//exit;
		return $this->render( 'CMSPageBundle:PageAdmin:sortAdmin.html.twig', array(
			'reorderEntityObj'           => $SortRepository,
			'action'                     => 'list',
			'admin'                      => $this->admin,
			'team_cat'                   => $SliderCategories,
			'programType'                => $programType,
			'programCategories'          => $programCategories,
			'programCategory'            => $programCategory,
			'programsDropdown'           => $programsDropdown,
			'programCredentialsDropdown' => $programCredentialsDropdown,
			'mediaType' => $mediaType,
		) );
	}

	public function saveSortAction() {
		$this->getCurrentEntityName();

		$errors  = array();
		$request = $this->get( 'request' );
		$DM      = $this->getDoctrine()->getManager();
		if ( $this->getRestMethod() == 'POST' && empty( $errors ) ) {
			$slidesString = $request->get( 'slides' );
			if ( $this->isJson( $slidesString ) ) {
				$slides = json_decode( $slidesString, true );
				if ( ! empty( $slides ) ) {
					foreach ( $slides as $key => $val ) {
						$SortRepository = $DM->getRepository( $this->entityName )->find( $val['id'] );
						$SortRepository->setSortOrder( $val['order'] );
						$DM->persist( $SortRepository );
					}
					$DM->flush();

					return new JsonResponse( array(
						'success' => true,
						'message' => 'Sort Order Updated Successfully'
					) );
				}
				else {
					$errors[] = 'No data recieved to process your request';
				}
			}
			else {
				$errors[] = 'Invalid string expected json';
			}
		}
		else {
			$errors[] = 'Invalid request method';
		}

		return new JsonResponse( array(
			'success' => false,
			'message' => '<li>' . join( '</li><li>', $errors ) . '</li>'
		) );
	}

	public function getSiteByLocale( $locale = false ) {
		$request = $this->container->get( 'request_stack' )->getCurrentRequest();
		$DM      = $this->getDoctrineManager();
		if ( ! $locale ) {

			if ( ! $this->defaultSite ) {
				return $this->defaultSite = $DM->getRepository( 'CMS\PageBundle\Entity\Page\Site' )
				                               ->findOneBy( array( 'locale' => $request->getLocale() ) );
			}
			else {
				return $this->defaultSite;
			}

		}
		else {
			return $site = $DM->getRepository( 'CMS\PageBundle\Entity\Page\Site' )
			                  ->findOneBy( array( 'locale' => $locale ) );
		}

	}
}
