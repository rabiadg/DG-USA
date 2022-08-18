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

namespace App\Application\Sonata\UserBundle\Admin\Model;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Form\Type\RolesMatrixType;
use Sonata\UserBundle\Model\UserInterface;
use Sonata\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * @phpstan-extends AbstractAdmin<UserInterface>
 */
class UserAdmin extends AbstractAdmin
{
    /**
     * @var UserManagerInterface
     */
    protected  $userManager;
    protected $classnameLabel = 'user';

    public function __construct(UserManagerInterface $userManager)
    {
        parent::__construct();
        $this->userManager = $userManager;
    }

    protected function preUpdate(object $object): void
    {
        $this->userManager->updatePassword($object);
    }

    protected function configureFormOptions(array &$formOptions): void
    {
        $formOptions['validation_groups'] = ['Default'];

        if (!$this->hasSubject() || null === $this->getSubject()->getId()) {
            $formOptions['validation_groups'][] = 'Registration';
        } else {
            $formOptions['validation_groups'][] = 'Profile';
        }
    }

    public function getExportFormats(): array
    {
        return array(
            'csv'
        );
    }
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('username')
            ->add('email')
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt')
            ->add('_action', 'actions', array(
                'label' => 'Action',
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array('template' => '/Application/Sonata/UserBundle/Resources/views/admin/list__action_delete.html.twig'),
                    //'delete' => array('template' => '@ApplicationSonataUserBundle/admin/list__action_delete.html.twig'),
                )
            ));

       /* if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $list
                ->add('impersonating', FieldDescriptionInterface::TYPE_STRING, [
                    'virtual_field' => true,
                    'template' => '@SonataUser/Admin/Field/impersonating.html.twig',
                ]);
        }*/
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            //->add('id')
            ->add('username')
            ->add('email');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('username')
            ->add('email')
            ->add('enabled', null, ['editable' => true])
            ->add('createdAt');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('general', ['class' => 'col-md-4'])
                ->add('username')
                ->add('email')
                ->add('plainPassword', TextType::class, [
                    'label' => 'Password',
                    'required' => (!$this->hasSubject() || null === $this->getSubject()->getId()),
                ])
                ->add('enabled', null)
            ->end()
            ->with('roles', ['class' => 'col-md-8'])
                ->add('realRoles', RolesMatrixType::class, [
                    'label' => false,
                    'multiple' => true,
                    'required' => false,
                ])
            ->end();
    }

    protected function configureExportFields(): array
    {


        $results = $this->getModelManager()->getExportFields($this->getClass());

        $results['Email Address'] = 'email';
        $results['Username'] = 'username';
        $results['Created At'] = 'createdAt';
        $results['Updated At'] = 'updatedAt';


        if (count($results) > 0) {
            foreach ($results as $key => $val) {
                if (is_int($key)) {
                    unset($results[$key]);
                }
            }

        }

        return $results;
    }


}
