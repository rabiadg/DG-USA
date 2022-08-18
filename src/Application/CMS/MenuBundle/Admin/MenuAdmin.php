<?php

namespace App\Application\CMS\MenuBundle\Admin;

use App\Admin\BaseAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Validator\ErrorElement;

class MenuAdmin extends BaseAdmin
{

    protected $classnameLabel = 'Menu';

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('title')
            ->add('depth')
            ->add('enabled');
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper): void
    {

        $listMapper
            ->add('title')
            ->add('enabled')
            ->add('_action', 'actions', array(
                'actions' => array(
                    //'edit' => array(),
                    'show' => array(),
                    'menuBuilder' => array('template' => '/Application/CMS/MenuBundle/Resources/views/Admin/Design/DesignAction.html.twig'),
                )
            ));
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
        $collection->add('menuBuilder', 'menu-builder/{id}');
        $collection->add('saveMenu', 'save-menu/{id}');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $ShowMapper): void
    {
        $ShowMapper->add('title')
            ->add('enabled')
            ->add('created_at')
            ->add('updated_at');
    }


    public function getTemplate($name)
    {

        if ($name == 'show') {
            return 'Application/CMS/MenuBundle/Resources/views/Admin/Show/ShowAction.html.twig';
        } else {
            return parent::getTemplate($name);
        }
    }

    public function getExportFormats(): array
    {
        return array(
            'csv'
        );
    }
}
 