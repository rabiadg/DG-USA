<?php

namespace App\Application\Sonata\PageBundle\Block\Page\CaseStudy;

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
        $this->manager = $this->container->get('doctrine')->getManager();

        parent::__construct($twig, $container);
    }

    public function getName()
    {
        return 'Banner Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'heading' => false,
            'logo' => null,
            'banner_image' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/CaseStudy/case_study_banner.html.twig',


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
                    array('heading', TextType::class, array('required' => false, 'label' => 'Heading ', 'help' => 'Max 50 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'logo', 'Logo Image', true, 'Max Dimensions: 165 x 79 px', array('provider' => 'sonata.media.provider.svg')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'banner_image', 'Banner Image', true, 'Max Dimensions: 1920 x 1246 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                )
            ));
    }

    public function configureCreateForm(FormMapper $formMapper, BlockInterface $block): void
    {
        //$mediaAdmin = $this->configurationPool()->getAdminByClass("Application\\Sonata\\MediaBundle\\Entity\\Media");
        $formMapper
            ->add('settings', ImmutableArrayType::class, array(
                'keys' => array(
                    array('heading', TextType::class, array('required' => false, 'label' => 'Heading ', 'help' => 'Max 50 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'logo', 'Logo Image', true, 'Max Dimensions: 165 x 79 px', array('provider' => 'sonata.media.provider.svg')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'banner_image', 'Banner Image', true, 'Max Dimensions: 1920 x 1246 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {


    }

    public function getBlockMetadata($code = null)
    {
        return new Metadata($this->getName(), (null !== $code ? $code : $this->getName()), false, 'SonataBlockBundle', [
            'class' => 'fa fa-file-text-o',
        ]);
    }


    public function load(BlockInterface $block): void
    {

        $logo = $block->getSetting('logo', null);
        $banner_image = $block->getSetting('banner_image', null);

        if (is_int($banner_image)) {
            $banner_image = $this->mediaManager->findOneBy(array('id' => $banner_image));
        }
        if (is_int($logo)) {
            $logo = $this->mediaManager->findOneBy(array('id' => $logo));
        }
        $block->setSetting('banner_image', $banner_image);
        $block->setSetting('logo', $logo);

    }

}
