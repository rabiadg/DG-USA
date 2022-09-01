<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

final class TestimonialAdmin extends BaseAdmin
{

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('heading')
            ->add('description')
            ->add('enabled')
            ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('heading')
            ->add('description')
            ->add('enabled')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('heading')
            ->add('description')
            ->add('enabled')
            ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('heading')
            ->add('description')
            ->add('created_at')
            ->add('updated_at')
            ->add('enabled')
            ;
    }
}
