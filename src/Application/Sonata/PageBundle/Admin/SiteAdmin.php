<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Application\Sonata\PageBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\Form\Type\DateTimePickerType;
use Sonata\Form\Validator\ErrorElement;
use Sonata\PageBundle\Route\RoutePageGenerator;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Admin definition for the Site class.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * @final since sonata-project/page-bundle 3.26
 */
class SiteAdmin extends AbstractAdmin
{
    protected $classnameLabel = 'Site';

    /**
     * @var RoutePageGenerator
     */
    protected $routePageGenerator;


    public function __construct(RoutePageGenerator $routePageGenerator)
    {
        parent::__construct();

        $this->routePageGenerator = $routePageGenerator;
    }

    public function postPersist($object): void
    {
        $this->routePageGenerator->update($object);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name')
            ->add('isDefault')
            ->add('enabled')
            ->add('host')
            ->add('locale');
        //->add('relativePath')
        //->add('enabledFrom')
        //->add('enabledTo')
        //->add('title')
        //->add('metaDescription')
        //->add('metaKeywords');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('name')
            ->add('isDefault')
            ->add('enabled', null, ['editable' => true])
            ->add('host');
        //->add('relativePath')
        //->add('locale')
        //->add('enabledFrom')
        //->add('enabledTo');
        //->add('create_snapshots', 'string', ['template' => '@SonataPage/SiteAdmin/list_create_snapshots.html.twig']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('form_site.label_general', ['class' => 'col-md-6'])
            ->add('name')
            ->add('isDefault', null, ['required' => false])
            ->add('enabled', null, ['required' => false])
            ->add('host')
            ->add('alphaCode', null, ['label' => 'Alpha Code'])
            ->add('locale', LocaleType::class, ['required' => false])
            ->add('relativePath', null, ['required' => false])
            ->add('enabledFrom', DateType::class, ['widget' => 'single_text'])
            ->add(
                'enabledTo',
                DateType::class,
                ['required' => false, 'widget' => 'single_text']
            )
            ->end()
            ->with('form_site.label_seo', ['class' => 'col-md-6'])
            ->add('title', null, ['required' => false])
            ->add('metaDescription', TextareaType::class, ['required' => false])
            ->add('metaKeywords', TextareaType::class, ['required' => false])
            ->end();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('delete');
        $collection->add('snapshots', $this->getRouterIdParameter() . '/snapshots');
    }

    public function validate(ErrorElement $errorElement, $object)
    {

        $errorElement
            ->with('alphaCode')
            ->assertNotBlank()
            ->end();
    }
}
