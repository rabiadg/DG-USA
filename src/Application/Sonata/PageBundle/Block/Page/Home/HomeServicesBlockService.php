<?php

namespace App\Application\Sonata\PageBundle\Block\Page\Home;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
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

class HomeServicesBlockService extends BaseBlockService
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
        return 'Services Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'services' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/Home/home_services_section.html.twig',


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
                    array('services', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ServicesType::class,

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
                    array('services', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => ServicesType::class,

                        )),
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

        $services = array();
        if ($block->getSetting('services') != null and count($block->getSetting('services')) > 0) {
            $count = 0;
            foreach ($block->getSetting('services') as $service) {
                $media = (isset($service['image'])) ? $service['image'] : null;
                if (is_int($service['image'])) {
                    $media = $this->mediaManager->findOneBy(array('id' => $service['image']));
                }
                $page = (isset($service['page'])) ? $service['page'] : null;
                if (is_int($service['page'])) {
                    $page = $this->getPageById($service['page']);
                }

                $background_image = (isset($service['background_image'])) ? $service['background_image'] : null;
                if (is_int($service['background_image'])) {
                    $background_image = $this->mediaManager->findOneBy(array('id' => $service['background_image']));
                }
                $services[$count]['title'] = ($service['title']) ? $service['title'] : null;
                $services[$count]['background_image'] = (is_object($background_image)) ? $background_image : null;
                $services[$count]['image'] = (is_object($media)) ? $media : null;
                $services[$count]['content'] = ($service['content']) ? $service['content'] : null;
                $services[$count]['page'] = (is_object($page)) ? $page : null;
                $count++;
            }
        }
        $block->setSetting('services', $services);
    }

}
