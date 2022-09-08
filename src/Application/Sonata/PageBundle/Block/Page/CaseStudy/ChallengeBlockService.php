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

class ChallengeBlockService extends BaseBlockService
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
        return 'Challenge Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'heading' => false,
            'content' => false,
            'right_image' => null,
            'banner_image' => null,
            'challange_counter_1' => false,
            'challange_counter_2' => false,
            'challange_counter_3' => false,
            'challange_heading_1' => false,
            'challange_heading_2' => false,
            'challange_heading_3' => false,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/CaseStudy/challenge.html.twig',


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
                    array('content', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Content ', 'help' => 'Max 200 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'right_image', 'Right Image', true, 'Max Dimensions: 630 x 399 px', array('provider' => 'sonata.media.provider.svg')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'banner_image', 'Banner Image', true, 'Max Dimensions: 1920 x 751 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array('challange_counter_1', TextType::class, array('required' => false, 'label' => 'Challange Counter 1 ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_heading_1', TextType::class, array('required' => false, 'label' => 'Challange Heading 1', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_counter_2', TextType::class, array('required' => false, 'label' => 'Challange Counter 2 ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_heading_2', TextType::class, array('required' => false, 'label' => 'Challange Heading 2', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_counter_3', TextType::class, array('required' => false, 'label' => 'Challange Counter 3 ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_heading_3', TextType::class, array('required' => false, 'label' => 'Challange Heading 3', 'help' => 'Max 50 Characters (Recommended)')),
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
                    array('content', TextareaType::class, array('attr' => array('rows' => '3'), 'required' => false, 'label' => 'Content ', 'help' => 'Max 200 Characters (Recommended)')),
                    array($this->getMediaBuilder($formMapper, 'right_image', 'Right Image', true, 'Max Dimensions: 630 x 399 px', array('provider' => 'sonata.media.provider.svg')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'banner_image', 'Banner Image', true, 'Max Dimensions: 1920 x 751 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array('challange_counter_1', TextType::class, array('required' => false, 'label' => 'Challange Counter 1 ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_heading_1', TextType::class, array('required' => false, 'label' => 'Challange Heading 1', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_counter_2', TextType::class, array('required' => false, 'label' => 'Challange Counter 2 ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_heading_2', TextType::class, array('required' => false, 'label' => 'Challange Heading 2', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_counter_3', TextType::class, array('required' => false, 'label' => 'Challange Counter 3 ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('challange_heading_3', TextType::class, array('required' => false, 'label' => 'Challange Heading 3', 'help' => 'Max 50 Characters (Recommended)')),
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

        $right_image = $block->getSetting('right_image', null);
        $banner_image = $block->getSetting('banner_image', null);

        if (is_int($banner_image)) {
            $banner_image = $this->mediaManager->findOneBy(array('id' => $banner_image));
        }
        if (is_int($right_image)) {
            $right_image = $this->mediaManager->findOneBy(array('id' => $right_image));
        }
        $block->setSetting('banner_image', $banner_image);
        $block->setSetting('right_image', $right_image);

    }

}
