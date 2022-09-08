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

class RevenueBlockService extends BaseBlockService
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
        return 'Revenue Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'heading' => false,
            'left_image_top' => null,
            'left_image_bottom' => null,
            'right_image_top_1' => null,
            'right_image_top_2' => null,
            'right_image_top_3' => null,
            'right_image_bottom' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/CaseStudy/our_approach.html.twig',


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
                    array($this->getMediaBuilder($formMapper, 'left_image_top', 'Left Image Top', true, 'Max Dimensions: 664 x 448 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'left_image_bottom', 'Left Image Bottom', true, 'Max Dimensions: 664 x 574 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_top_1', 'Right Image Top 1', true, 'Max Dimensions: 312 x 257 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_top_2', 'Right Image Top 2', true, 'Max Dimensions: 312 x 234 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_top_3', 'Right Image Top 3', true, 'Max Dimensions: 333 x 513 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_bottom', 'Right Image Bottom', true, 'Max Dimensions: 580 x 565 px', array('provider' => 'sonata.media.provider.image')), null, array()),
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
                    array($this->getMediaBuilder($formMapper, 'left_image_top', 'Left Image Top', true, 'Max Dimensions: 664 x 448 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'left_image_bottom', 'Left Image Bottom', true, 'Max Dimensions: 664 x 574 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_top_1', 'Right Image Top 1', true, 'Max Dimensions: 312 x 257 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_top_2', 'Right Image Top 2', true, 'Max Dimensions: 312 x 234 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_top_3', 'Right Image Top 3', true, 'Max Dimensions: 333 x 513 px', array('provider' => 'sonata.media.provider.image')), null, array()),
                    array($this->getMediaBuilder($formMapper, 'right_image_bottom', 'Right Image Bottom', true, 'Max Dimensions: 580 x 565 px', array('provider' => 'sonata.media.provider.image')), null, array()),
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
        $left_image_top = $block->getSetting('left_image_top', null);
        $left_image_bottom = $block->getSetting('left_image_bottom', null);
        $right_image_top_1 = $block->getSetting('right_image_top_1', null);
        $right_image_top_2 = $block->getSetting('right_image_top_2', null);
        $right_image_top_3 = $block->getSetting('right_image_top_3', null);
        $right_image_bottom = $block->getSetting('right_image_bottom', null);

        if (is_int($left_image_top)) {
            $left_image_top = $this->mediaManager->findOneBy(array('id' => $left_image_top));
        }
        if (is_int($left_image_bottom)) {
            $left_image_bottom = $this->mediaManager->findOneBy(array('id' => $left_image_bottom));
        }
        if (is_int($right_image_top_1)) {
            $right_image_top_1 = $this->mediaManager->findOneBy(array('id' => $right_image_top_1));
        }
        if (is_int($right_image_top_2)) {
            $right_image_top_2 = $this->mediaManager->findOneBy(array('id' => $right_image_top_2));
        }
        if (is_int($right_image_top_3)) {
            $right_image_top_3 = $this->mediaManager->findOneBy(array('id' => $right_image_top_3));
        }
        if (is_int($right_image_bottom)) {
            $right_image_bottom = $this->mediaManager->findOneBy(array('id' => $right_image_bottom));
        }
        $block->setSetting('left_image_top', $left_image_top);
        $block->setSetting('left_image_bottom', $left_image_bottom);
        $block->setSetting('right_image_top_1', $right_image_top_1);
        $block->setSetting('right_image_top_2', $right_image_top_2);
        $block->setSetting('right_image_top_3', $right_image_top_3);
        $block->setSetting('right_image_bottom', $right_image_bottom);
    }

}
