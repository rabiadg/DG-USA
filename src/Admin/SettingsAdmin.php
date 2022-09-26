<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Admin;

use App\Admin\BaseAdmin;
use App\Application\Sonata\PageBundle\Entity\Site;
use App\Form\ButtonTextAndLinkType;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\PageBundle\Exception\PageNotFoundException;
use Sonata\PageBundle\Exception\InternalErrorException;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\PageManagerInterface;
use Sonata\PageBundle\Model\SiteManagerInterface;
use Sonata\Cache\CacheManagerInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Admin definition for the Page class
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
class SettingsAdmin extends BaseAdmin
{
    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper->add('title')
            ->add('created_at')->add('updated_at');
    }


    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper->add('title')
            //->add( 'created_at' )
            //->add( 'updated_at' )
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'show' => array(),
                )
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper->add('title')->add('created')->add('updated');
    }

    protected $mediaAdmin;

    public function getMediaAdmin()
    {
        if (!$this->mediaAdmin) {
            $this->mediaAdmin = $this->getContainer()->get('sonata.media.admin.media');
        }

        return $this->mediaAdmin;
    }

    public function getMediaBuilder(FormMapper $formMapper, $name = 'mediaId', $label = 'form.label_media')
    {
        // simulate an association ...
        $fieldDescription = $this->getMediaAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->mediaAdmin->getClass(), 'media', array(
            'translation_domain' => 'SonataMediaBundle',
        ));
        $fieldDescription->setAssociationAdmin($this->getMediaAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array(
            'fieldName' => 'media',
            'type' => ClassMetadataInfo::MANY_TO_ONE,
        ));

        return $formMapper->create($name, ImmutableArrayType::class, array(
            'sonata_field_description' => $fieldDescription,
            'class' => $this->getMediaAdmin()->getClass(),
            'model_manager' => $this->getMediaAdmin()->getModelManager(),
            'label' => $label,
        ));
    }


    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {

        $settingsObject = $this->getObject($this->id($this->getSubject()));

        if ($settingsObject) {
            $formMapper->add('title', TextType::class);
            /* $formMapper->add('site', EntityType::class, array(
                 'label' => 'Language',
                 'choice_label' => 'name',
                 'choice_value' => 'id',
                 'expanded' => false,
                 'placeholder' => 'Select Language',
                 'required' => false,
                 'class' => Site::class,
                 'query_builder' => function (EntityRepository $er) {
                     return $er->createQueryBuilder('s')
                         ->Where('s.enabled = :enabled')
                         ->setParameter('enabled', '1')
                         ->orderBy('s.id', 'ASC');
                 },
             ));*/
            $fields = array();
            switch ($settingsObject->getSettingsKey()) {
                case 'header':
                    $formMapper->add('thumb', ModelListType::class, array('btn_edit' => false, 'label' => 'Logo'), array('link_parameters' => array('provider' => 'sonata.media.provider.image')));
                    $formMapper->add('mobileThumb', ModelListType::class, array('btn_edit' => false, 'label' => 'Mobile Logo'), array('link_parameters' => array('provider' => 'sonata.media.provider.image')));
                    // ->add( 'emailThumb', ModelListType::class, array('btn_edit'=>false, 'label' => 'Email Logo' ),array('link_parameters'=>array('context'=>'svg_context')) );
                    $fields = $this->getSettingsForm();
                    break;
                case 'footer':
                    $fields = $this->getSettingsForm('footer');
                    break;
                case 'from_email':
                    $fields = $this->getSettingsForm('from_email');
                    break;
                /* case 'slider_rotation':
                     $fields = $this->getSettingsForm('slider_rotation');
                     break;*/
            }
            $formMapper->add('content', ImmutableArrayType::class, array(
                'required' => true,
                'label'=>false,
                'keys' => $fields
            ));
        } else {
            $formMapper
                ->add('title', TextType::class)
                ->add('settingsKey', TextType::class);

        }
    }

    public function getSettingsForm($type = 'header')
    {
        $fields = array();
        switch ($type) {
            case 'from_email':
                $fields[] = array('from_email', TextType::class, array('required' => true, 'label' => 'From email'));
                break;

            case 'header':
                $fields[] = array('form_button_title', TextType::class, array('required' => true, 'label' => 'Form Button Title'));
                $fields[] = array('whatsapp_url', TextType::class, array('required' => false, 'label' => 'Whatsapp Url'));
                $fields[] = array('contact_number', TextType::class, array('required' => false, 'label' => 'Contact Number'));
                break;
            case 'footer':
                $fields[] = array('outline_text', TextType::class, array('required' => false, 'label' => 'Outline Text'));
                $fields[] = array('left_description', CKEditorType::class, array('required' => false, 'label' => 'Left Description'));
                $fields[] = array('address', TextType::class, array('required' => false, 'label' => 'Address'));
                $fields[] = array('email', TextType::class, array('required' => false, 'label' => 'Email'));
                $fields[] = array('contact_number', TextType::class, array('required' => false, 'label' => 'Contact Number'));
                $fields[] = array('twitter', TextType::class, array('required' => false));
                $fields[] = array('facebook', TextType::class, array('required' => false));
                $fields[] = array('instagram', TextType::class, array('required' => false));
                $fields[] = array('youtube', TextType::class, array('required' => false));
                $fields[] = array('linkedin', TextType::class, array('required' => false));
                break;

        }

        return $fields;
    }

    public function prePersist($object): void
    {
        //$this->prePersist($object);
        parent::prePersist($object);
        $object->setContent(json_encode($object->getContent()));

    }

    public function preUpdate($object): void
    {

        parent::preUpdate($object);
        $object->setContent(json_encode($object->getContent()));

    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $settingsObject = $this->getObject($this->id($this->getSubject()));
        if ($settingsObject) {
            switch ($settingsObject->getSettingsKey()) {
                case 'header':
                    $errorElement->with('content[logo_title]')->assertNotBlank()->end();
                    break;
                case 'footer_bottom':
                    //$errorElement->with( 'content[thumb_title]' )->assertNotBlank()->end()->with( 'content[thumb_link]' )->assertNotBlank()->end()->with( 'content[menu]' )->assertNotBlank()->end()->with( 'content[contact_info]' )->assertNotBlank()->end()->with( 'content[contact_info1]' )->assertNotBlank()->end()->with( 'content[contact_info2]' )->assertNotBlank()->end()->with( 'content[contact_info3]' )->assertNotBlank()->end()->with( 'content[contact_info4]' )->assertNotBlank()->end()->with( 'content[contact_info5]' )->assertNotBlank()->end()->with( 'content[copyright_text1]' )->assertNotBlank()->end()->with( 'content[copyright_text2]' )->assertNotBlank()->end();
                    break;
                case 'footer':
                    //$errorElement->with( 'content[thumb_title]' )->assertNotBlank()->end()->with( 'content[thumb_link]' )->assertNotBlank()->end()->with( 'content[menu]' )->assertNotBlank()->end()->with( 'content[contact_info]' )->assertNotBlank()->end()->with( 'content[contact_info1]' )->assertNotBlank()->end()->with( 'content[contact_info2]' )->assertNotBlank()->end()->with( 'content[contact_info3]' )->assertNotBlank()->end()->with( 'content[contact_info4]' )->assertNotBlank()->end()->with( 'content[contact_info5]' )->assertNotBlank()->end()->with( 'content[copyright_text1]' )->assertNotBlank()->end()->with( 'content[copyright_text2]' )->assertNotBlank()->end();
                    break;
                case 'from_email':
                    $errorElement->with('content[from_email]')->assertNotBlank()->assertEmail()->end();
                    break;
                /*case 'slider_rotation':
                    $errorElement->with('content[rotation_time]')->assertNotBlank()->end();
                    break;*/
            }
        }

    }

    public function getExportFormats(): array
    {
        return array();
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        //$collection->remove('create');
        $collection->remove('delete');
    }

}
