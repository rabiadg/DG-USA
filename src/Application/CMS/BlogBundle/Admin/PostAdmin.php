<?php

namespace App\Application\CMS\BlogBundle\Admin;

use App\Admin\BaseAdmin;
use App\Application\CMS\BlogBundle\Entity\Categories;
use App\Controller\BaseController;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Show\ShowMapper;
use App\Application\Sonata\UserBundle\Entity\SonataUserUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class PostAdmin extends AbstractAdmin
{

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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title')
            ->add('enabled')
            ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('title')
            ->add('author')
            ->add('enabled', null, array('editable' => true))
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
//        $categories=[];
//       foreach ($this->getSubject()->getPostCategories() as $ca){
//           $categories[] = $ca->getId();
//       }
       //die('call');
        $formMapper
            ->add('postCategories', EntityType::class, array(
                'label' => 'Categories',
                'choice_label' => 'name',
                'choice_value' => 'id',
                'expanded' => false,
                'placeholder' => 'Select Category',
                'required' => false,
                'multiple'=>true,
                'class' => Categories::class,
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('s')
                        ->Where('s.enabled = :enabled')
                        ->setParameter('enabled', '1')
                        ->orderBy('s.id', 'ASC');
                }))
            ->add('title', null, array('required' => true))
            ->add('description', CKEditorType::class, array('attr' => array('rows' => '10'), 'required' => false, 'label' => 'Content '))
            ->add('listing_image', ModelListType::class, array('help' => "Max Dimensions: 385 x 359 pixels"))
            ->add('detail_image', ModelListType::class)
            ->add('author', EntityType::class, array(
                'label' => 'Author',
                'choice_label' => 'username',
                'choice_value' => 'id',
                'expanded' => false,
                'placeholder' => 'Select Author',
                'required' => false,
                'class' => SonataUserUser::class,
                'query_builder' => function (EntityRepository $er)  {
                    return $er->createQueryBuilder('s')
                        ->Where('s.enabled = :enabled')
                        ->setParameter('enabled', '1')
                        ->orderBy('s.id', 'ASC');
                }))
            ->add('enabled');
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('categories')
            ->add('title')
            ->add('listing_image')
            ->add('detail_image')
            ->add('description')
            ->add('author')
            ->add('enabled')
            ->add('created_at')
            ->add('updated_at');
    }

    public function prePersist($object): void
    {
        $cms_base_controller = $this->getClass('cms.base_controller');
        $title=$object->getTitle();
        $object->setSlug($title);
        $cms_base_controller->setSlug($object);

    }

    public function getTemplate($name)
        {
            switch ($name) {
                case 'edit':
                    return 'Application/CMS/BlogBundle/Resources/views/CRUD/edit.html.twig';
                    break;
                default:
                    return parent::getTemplate($name);
                    break;
            }
        }
}
