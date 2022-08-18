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

namespace App\Application\Sonata\MediaBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\Form\Validator\ErrorElement;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

class MediaAdmin extends Admin
{

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper):void
    {
        unset($this->listModes['mosaic']);
        $this->setListMode('list');
        $listMapper
            ->addIdentifier('name',null,['template'=>'Application/Sonata/MediaBundle/Resources/views/MediaAdmin/name.html.twig'])
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));;
    }



    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper):void
    {

        $options = [
            'choices' => [],
        ];

        foreach ($this->pool->getContexts() as $name => $context) {
            $options['choices'][$name] = $name;
        }

        $datagridMapper
            ->add('name');
        //->add('enabled');

        $providers = [];


        $providerNames = (array)$this->pool->getProviderNamesByContext($this->getPersistentParameter('context', $this->pool->getDefaultContext()));

        $providerNames = (array_merge($providerNames, ['sonata.media.provider.svg']));

        foreach ($providerNames as $key => $name) {
            if ($key == 0) {
                $providers['Image'] = $name;
            } elseif ($key == 2) {
                $providers['SVG'] = $name;
            } elseif ($key == 1) {
                $providers['File'] = $name;
            }

        }

        $datagridMapper->add('providerName', ChoiceFilter::class, [
            'label' => 'File Type',
            'field_options' => [
                'choices' => $providers,
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ],
            'field_type' => ChoiceType::class,
        ]);

    }

   /* public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $query->where($query->getRootAlias() . '.enabled = 1');
        }
        $query->orderBy($query->getRootAlias() . '.id', 'DESC');
        return $query;
    }*/

    protected function configureRoutes(RouteCollectionInterface $collection):void
    {
        $collection->remove('export');
        //$collection->remove('edit');
    }

    public function configureFormFields(FormMapper $form):void
    {
        parent::configureFormFields($form);

        $subject = $this->getSubject();

        $media = $this->hasSubject() ? $this->getSubject() : $this->getNewInstance();
        // NEXT_MAJOR: Remove the previous line and uncomment the following one.
        // $media = $this->getSubject();

        if (null === $media->getProviderName()) {
            return;
        }
        $provider = $this->pool->getProvider($media->getProviderName());
        if (null !== $media->getId()) {
            $provider->buildEditForm($form);
        } else {
            $provider->buildCreateForm($form);
        }

    }

}
