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

	public function getDoctrineManager( $manager = 'default' ) {
		return $this->getDoctrine()->getManager( $manager );
	}

    public function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

	function isJson( $string ) {
		json_decode( $string );

		return ( json_last_error() == JSON_ERROR_NONE );
	}







}
