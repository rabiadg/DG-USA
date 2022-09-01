<?php

namespace App\Application\Sonata\PageBundle\Block\Page\Home;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use App\Form\ImageLinkType;
use App\Form\SocialMediaLinkType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;
use Sonata\BlockBundle\Form\Mapper\FormMapper;

class HomeBannerBlockService extends BaseBlockService
{
    protected $container;
    protected $manager;

    /**
     * @var Environment
     */
    protected $twig;

    public function __construct(Environment $twig, ContainerInterface $container)
    {
        $this->container = $container;
        $this->manager = $this->container->get('doctrine')->getManager();

        parent::__construct($twig, $container);
    }

    public function getName()
    {
        return 'Home Banner Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'sub_title' => false,
            'contact_number' => false,
            'contact_button_title' => false,
            'banner_video' => false,
            'banner_image' => null,
            'banner_left_sec_title_1' => false,
            'banner_left_sec_title_2' => false,
            'banner_right_sec_image' => null,
            'banner_right_sec_video_url' => false,
            'social_icons' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Home/home_page_banner.html.twig',


        ));
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null): Response
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
            //'context' => $blockContext,
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ), $response);
    }

    public function configureEditForm(FormMapper $formMapper, BlockInterface $block): void
    {
        //$this->container->
        //$mediaAdmin = $this->configurationPool()->getAdminByClass("Application\Sonata\MediaBundle\Entity\Media");
        $formMapper
            ->add('settings', ImmutableArrayType::class, array(
                'keys' => array(
                    array('title', CKEditorType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Title ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('sub_title', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Sub Title ', 'help' => 'Max 200 Characters (Recommended)')),
                    array('contact_number', TextType::class, array('required' => false, 'label' => 'Contact Number ', 'help' => 'Max 20 Characters (Recommended)')),
                    array('contact_button_title', TextType::class, array('required' => false, 'label' => 'Contact Button Title ', 'help' => 'Max 20 Characters (Recommended)')),
                    //array('image', ModelListType::class, ['class' => Media::class,'model_manager'=>$this->getMediaAdmin()->getModelManager(),'btn_edit' => false, 'help' => 'Max Dimensions: 1519 x 382 px', 'label' => 'Slider Image', 'required' => true]),

                    //array('image_title', TextType::class, array('required' => true, 'label' => 'Image Title', 'sonata_help' => 'Max 50 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'banner_image', 'Banner Image', true, 'Max Dimensions: 1536 x 330 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array('banner_video', UrlType::class, array('required' => false, 'label' => 'Banner Video URL')),
                    array('banner_left_sec_title_1', TextType::class, array('required' => false, 'label' => 'Left Section Title 1', 'help' => 'Max 20 Characters (Recommended)')),
                    array('banner_left_sec_title_2', TextType::class, array('required' => false, 'label' => 'Link Section Title 2', 'help' => 'Max 20 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'banner_right_sec_image', 'Right Section Video Image', true, 'Max Dimensions: 184 x 127 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array('banner_right_sec_video_url', UrlType::class, array('required' => false, 'label' => 'Right Section Video URL ')),
                    array('social_icons', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ImageLinkType::class,

                        )),
                )
            ));
    }

    public function configureCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
        //$mediaAdmin = $this->configurationPool()->getAdminByClass("Application\\Sonata\\MediaBundle\\Entity\\Media");
        $formMapper
            ->add('settings', ImmutableArrayType::class, array(
                'keys' => array(
                    array('title', CKEditorType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Title ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('sub_title', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Sub Title ', 'help' => 'Max 200 Characters (Recommended)')),
                    array('contact_number', TextType::class, array('required' => false, 'label' => 'Contact Number ', 'help' => 'Max 20 Characters (Recommended)')),
                    array('contact_button_title', TextType::class, array('required' => false, 'label' => 'Contact Button Title ', 'help' => 'Max 20 Characters (Recommended)')),
                    //array('image', ModelListType::class, ['class' => Media::class,'model_manager'=>$this->getMediaAdmin()->getModelManager(),'btn_edit' => false, 'help' => 'Max Dimensions: 1519 x 382 px', 'label' => 'Slider Image', 'required' => true]),

                    //array('image_title', TextType::class, array('required' => true, 'label' => 'Image Title', 'sonata_help' => 'Max 50 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'banner_image', 'Banner Image', true, 'Max Dimensions: 1536 x 330 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array('banner_video', UrlType::class, array('required' => false, 'label' => 'Banner Video URL')),
                    array('banner_left_sec_title_1', TextType::class, array('required' => false, 'label' => 'Left Section Title 1', 'help' => 'Max 20 Characters (Recommended)')),
                    array('banner_left_sec_title_2', TextType::class, array('required' => false, 'label' => 'Link Section Title 2', 'help' => 'Max 20 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'banner_right_sec_image', 'Right Section Video Image', true, 'Max Dimensions: 184 x 127 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array('banner_right_sec_video_url', UrlType::class, array('required' => false, 'label' => 'Right Section Video URL ')),
                    array('social_icons', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ImageLinkType::class,

                        )),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $image = $block->getSetting('banner_image', null);

        /*$errorElement
            ->with('settings[title]')
            ->assertNotBlank()
            ->end();*/

        if ($image && !in_array($image->getContentType(), ['image/jpg', 'image/jpeg', 'image/png', 'image/x-png','image/webp'])) {
            $errorElement
                ->with('settings[banner_image]')
                ->addViolation('Invalid file type only jpeg,jpg,png,webp allowed')
                ->end();
        };

    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-file-text-o',
        ]);
    }


    public function load(BlockInterface $block): void
    {

        $banner_image = $block->getSetting('banner_image', null);

        if (is_int($banner_image)) {
            $banner_image = $this->mediaManager->findOneBy(array('id' => $banner_image));
        }
        $block->setSetting('banner_image', $banner_image);

        $banner_right_sec_image = $block->getSetting('banner_right_sec_image', null);

        if (is_int($banner_right_sec_image)) {
            $banner_right_sec_image = $this->mediaManager->findOneBy(array('id' => $banner_right_sec_image));
        }
        $block->setSetting('banner_right_sec_image', $banner_right_sec_image);

        $social_icons = array();
        if ($block->getSetting('social_icons') != null and count($block->getSetting('social_icons')) > 0) {
            $count = 0;
            foreach ($block->getSetting('social_icons') as $social_icon) {
                $media = (isset($social_icon['image'])) ? $social_icon['image'] : null;
                if (is_int($social_icon['image'])) {
                    $media = $this->mediaManager->findOneBy(array('id' => $social_icon['image']));
                }
                $social_icons[$count]['image'] = (is_object($media)) ? $media : null;
                $social_icons[$count]['link'] = ($social_icon['link']) ? $social_icon['link'] : null;
                $count++;
            }
        }
        $block->setSetting('social_icons', $social_icons);
    }

}
