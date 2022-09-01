<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Psr\Container\ContainerInterface;

class BaseAdmin extends AbstractAdmin
{
    protected $datagridValues = array(
        '_sort_order' => 'DESC',
    );
    protected $site = null;
    /**
     * @var SiteManagerInterface
     */
    protected $siteManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $previous = $this->container;
        $this->container = $container;

        return $previous;
    }


    public function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    public function getDoctrineManager($manager = 'default')
    {
        return $this->getDoctrine()->getManager($manager);
    }

    /**
     * @return array
     */
    public function getSites()
    {
        return $this->siteManager->findBy(array());
    }

    public function getSite()
    {
        $request = $this->getRequest();
        if ($this->site == null) {

            $siteId= $request->getSession()->get('_site');
            //$request = $this->container->get('request_stack')->getCurrentRequest();
            if ($this->siteManager) {
                //$this->site = $this->siteManager->findOneBy(array('locale' => $request->getLocale()));
                $this->site = $this->siteManager->find($siteId);
            } else {
                $DM = $this->getDoctrineManager();
                $this->site = $DM->getRepository('App\Application\Sonata\PageBundle\Entity\Site')->find($siteId);
                    //->findOneBy(array('locale' => $request->getLocale()));
            }

        }

        return $this->site;


    }

    public function prePersist($object): void
    {

        $object->setSite($this->getSite());
        $now = new \DateTime("now");
        if (method_exists($object, 'setCreatedAt')) {
            $object->setCreatedAt($now);
        }
        if (method_exists($object, 'setUpdatedAt')) {
            $object->setUpdatedAt($now);
        }
        if (method_exists($object, 'setSite')) {
            if ($object->getSite()) {
                $object->setSite($object->getSite());
            } else {
                $object->setSite($this->getSite());
            }

        }
    }

    public function preUpdate($object): void
    {
        //$object->setSite($this->getSite());
        $now = new \DateTime("now");
        if (method_exists($object, 'setUpdatedAt')) {
            $object->setUpdatedAt($now);
        }

    }


    public function getUser()
    {
        return $this->getSecurityContext()->get('security.token_storage')->getToken()->getUser();
    }

    public function getSecurityContext()
    {
        return $this->getConfigurationPool()->container;
    }

    public function getExportFormats(): array
    {
        return array(
            'csv'
        );
    }

    public function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {


        $request = $this->getRequest();
        $filters = ($request != '') ? $request->query->get('filter', array('')) : '';

        $query = $this->getModelManager()->createQuery($this->getClass());

        if (!empty($filters['site']['value']) && in_array($filters['site'], $filters)) {
            $site = $filters['site']['value'];
        } else {
            $site = $this->getSite();
        }


        $query->andWhere($query->getRootAlias() . '.site = :site')
            ->setParameter('site', $site);
        /* if (count($draftItems) > 0) {
             $query->andWhere($query->getRootAlias() . '.id NOT IN (:ids)')
                 ->setParameter('ids', array_column($draftItems, 'version_parent'));
         }*/
        $query->orderBy($query->getRootAlias() . '.id', 'DESC');
        $query->getQuery()->getSql();

        return $query;
    }
}