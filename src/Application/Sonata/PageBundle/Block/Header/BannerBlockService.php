<?php

namespace App\Application\Sonata\PageBundle\Block\Header;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\Form\Type\ImmutableArrayType;
use Sonata\BlockBundle\Meta\Metadata;
use Sonata\Form\Validator\ErrorElement;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Environment;
use Sonata\BlockBundle\Form\Mapper\FormMapper;

class BannerBlockService extends BaseBlockService
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
        $this->manager   =  $this->container->get('doctrine')->getManager();

        parent::__construct($twig, $container);
    }

    public function getName()
    {
        return 'Banner Section';
    }

    public function configureSettings(OptionsResolver $resolver):void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'sub_title' => false,
            'content' => false,
            'template' => null,
            //'image_title' => false,
            'image' => null,
            //'templates' => 'ApplicationSonataPageBundle::Block/Header/page_banner.html.twig'
        ));
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null):Response
    {
        return $this->renderResponse($blockContext->getTemplate(), array(
            //'context' => $blockContext,
            'block'    => $blockContext->getBlock(),
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
                    array('title', TextType::class, array('required' => true, 'label' => 'Title', 'help' => 'Max 20 Characters (Recommended)')),
                    array('sub_title', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Sub Title ', 'help' => 'Max 120 Characters (Recommended)')),
                    array('content', CKEditorType::class, array('attr' => array('rows' => '10'), 'required' => false, 'label' => 'Content ', 'help' => 'Max 600 Characters (Recommended)')),
                    //array('image', ModelListType::class, ['class' => Media::class,'model_manager'=>$this->getMediaAdmin()->getModelManager(),'btn_edit' => false, 'help' => 'Max Dimensions: 1519 x 382 px', 'label' => 'Slider Image', 'required' => true]),

                    //array('image_title', TextType::class, array('required' => true, 'label' => 'Image Title', 'sonata_help' => 'Max 50 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'image', 'Image', true, 'Max Dimensions: 780 x 420 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                )
            ));
    }

    public function configureCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
        //$mediaAdmin = $this->configurationPool()->getAdminByClass("Application\\Sonata\\MediaBundle\\Entity\\Media");
        $formMapper
            ->add('settings', ImmutableArrayType::class, array(
                'keys' => array(

                    array('title', TextType::class, array('required' => true, 'label' => 'Title', 'help' => 'Max 20 Characters (Recommended)')),
                    array('sub_title', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Sub Title ', 'help' => 'Max 120 Characters (Recommended)')),
                    array('content', CKEditorType::class, array('attr' => array('rows' => '10'), 'required' => false, 'label' => 'Content ', 'help' => 'Max 600 Characters (Recommended)')),
                    //array('image', ModelListType::class, ['class' => Media::class,'model_manager'=>$this->getMediaAdmin()->getModelManager(),'btn_edit' => false, 'help' => 'Max Dimensions: 1519 x 382 px', 'label' => 'Slider Image', 'required' => true]),
                    //array('image_title', TextType::class, array('required' => true, 'label' => 'Image Title', 'sonata_help' => 'Max 50 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'image', 'Image', true, 'Max Dimensions: 780 x 420 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block):void
    {
      /*  $image = $block->getSetting('image', null);
        if ($image && !in_array($image->getContentType(), ['image/jpg', 'image/jpeg', 'image/png', 'image/x-png'])) {
            $errorElement
                ->with('settings[image]')
                ->addViolation('Invalid file type only jpeg,jpg,png allowed')
                ->end();
        };*/
        //$block->setSetting('image', is_object($block->getSetting('image')) ? $block->getSetting('image')->getId() : null);


    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-file-text-o',
        ]);
    }



    public function load(BlockInterface $block): void
    {

        $media = $block->getSetting('image', null);
        //dump($media);die('call');
        if (is_int($media)) {
            $media = $this->mediaManager->findOneBy(array('id' => $media));
        }
        $block->setSetting('image', $media);
    }

    public function prePersist(BlockInterface $block): void
    {
        dump('test');die('call');
        //$block->setSetting('mediaId', $block->getSetting('mediaId') instanceof MediaInterface ? $block->getSetting('mediaId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block): void
    {
        dump('test');die('call');
        //$block->setSetting('mediaId', $block->getSetting('mediaId') instanceof MediaInterface ? $block->getSetting('mediaId')->getId() : null);
    }
}
