<?php

namespace App\Application\Sonata\PageBundle\Block\Page;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use App\Form\PortfolioType;
use App\Form\ServicesType;
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

class PortfolioBlockService extends BaseBlockService
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
        return 'Portfolio Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'portfolioItems' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/portfolio_section.html.twig',


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
                    array('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('portfolioItems', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => PortfolioType::class,

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
                    array('title', TextType::class, array('required' => false, 'label' => 'Title ', 'help' => 'Max 50 Characters (Recommended)')),
                    array('portfolioItems', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => PortfolioType::class,

                        )),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $portfolioItems = $block->getSetting('portfolioItems', null);

        if (isset($portfolioItems) and count($portfolioItems) > 3) {
            $errorElement
                ->with('settings[portfolioItems]')
                ->addViolation('Only 3 Portfolio Allowed')
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

        $portFolio = array();
        if ($block->getSetting('portfolioItems') != null and count($block->getSetting('portfolioItems')) > 0) {
            $count = 0;
            foreach ($block->getSetting('portfolioItems') as $item) {
                $icon_image = (isset($item['icon_image'])) ? $item['icon_image'] : null;
                if (is_int($item['icon_image'])) {
                    $icon_image = $this->mediaManager->findOneBy(array('id' => $item['icon_image']));
                }
                $image = (isset($item['image'])) ? $item['image'] : null;
                if (is_int($item['image'])) {
                    $image = $this->mediaManager->findOneBy(array('id' => $item['image']));
                }
                $portFolio[$count]['icon_image'] = (is_object($icon_image)) ? $icon_image : null;
                $portFolio[$count]['title'] = ($item['title']) ? $item['title'] : null;
                $portFolio[$count]['sub_title'] = ($item['sub_title']) ? $item['sub_title'] : null;
                $portFolio[$count]['image'] = (is_object($image)) ? $image : null;
                $portFolio[$count]['content'] = ($item['content']) ? $item['content'] : null;
                $count++;
            }
        }
        $block->setSetting('portfolioItems', $portFolio);
    }

}
