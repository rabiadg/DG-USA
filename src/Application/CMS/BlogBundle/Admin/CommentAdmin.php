<?php

declare(strict_types=1);

namespace App\Application\CMS\BlogBundle\Admin;

use App\Application\CMS\BlogBundle\Entity\Post;
use Doctrine\ORM\EntityRepository;
use Psr\Container\ContainerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class CommentAdmin extends AbstractAdmin
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

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name')
            ->add('email')
            ->add('status');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name')
            ->add('email')
            ->add('message')
            ->add('status')
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
            ->add('name',null,['label'=>'User Name'])
            ->add('email',EmailType::class,['label'=>'Email'])
            ->add('url',UrlType::class)
            ->add('message', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Message ', 'help' => 'Max 200 Characters (Recommended)'))
            ->add('post', EntityType::class, array(
                'label' => 'Post',
                'choice_label' => 'title',
                'choice_value' => 'id',
                'expanded' => false,
                'placeholder' => 'Select Post',
                'required' => false,
                'class' => Post::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->Where('s.enabled = :enabled')
                        ->setParameter('enabled', '1')
                        ->orderBy('s.id', 'ASC');
                }))
            ->add('status', ChoiceType::class, [
                'label' => 'Status',
                'choices' => [
                    'Draft' => 'draft',
                    'Reviewed' => 'reviewed',
                    'Published' => 'published',
                    'Rejected' => 'rejected',
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('name')
            ->add('email')
            ->add('url')
            ->add('message')
            ->add('created_at')
            ->add('updated_at')
            ->add('status');
    }
}
