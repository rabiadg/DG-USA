<?php

namespace App\Application\Sonata\PageBundle\Block\Page;

use App\Application\Sonata\MediaBundle\Entity\Media;
use App\Application\Sonata\PageBundle\Block\BaseBlockService;
use App\Form\ServicesType;
use App\Form\SocialMediaLinkType;
use App\Form\TechnologyType;
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
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;
use Twig\Environment;
use Sonata\BlockBundle\Form\Mapper\FormMapper;

class TechnologyStackBlockService extends BaseBlockService
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
        return 'Technology Stack Section';
    }

    public function configureSettings(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'title' => false,
            'technologies' => null,
            'template' => 'Application/Sonata/PageBundle/Resources/views/Block/Page/technology_stack_section.html.twig',


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
                    array('technologies', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => TechnologyType::class,

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
                    array('technologies', CollectionType::class,
                        array(
                            'required' => false,
                            'allow_add' => true,
                            'allow_delete' => true,
                            'prototype' => true,
                            'by_reference' => false,
                            'allow_extra_fields' => true,
                            'entry_type' => TechnologyType::class,
                        )),
                )
            ));
    }


    public function validate(ErrorElement $errorElement, BlockInterface $block): void
    {
        $technologies = $block->getSetting('technologies', null);

        if (isset($technologies) and count($technologies)>3) {
            $errorElement
                ->with('settings[technologies]')
                ->addViolation('Only 3 Items Allowed')
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


        $technologies = array();
        if ($block->getSetting('technologies') != null and count($block->getSetting('technologies')) > 0) {
            $count = 0;
            foreach ($block->getSetting('technologies') as $techno) {

                $technologies[$count]['heading'] = ($techno['heading']) ? $techno['heading'] : null;
                $technologies[$count]['items']=array();
                if(isset($techno['items']) and count($techno['items'])>0){
                    $i=0;
                    foreach ($techno['items'] as $item){
                        $media = (isset($item['icon'])) ? $item['icon'] : null;
                        if (is_int($item['icon'])) {
                            $media = $this->mediaManager->findOneBy(array('id' => $item['icon']));
                        }
                        $technologies[$count]['items'][$i]['title'] = ($item['title']) ? $item['title'] : null;
                        $technologies[$count]['items'][$i]['icon'] = (is_object($media)) ? $media : null;
                        $i++;
                    }

                }
                $count++;
            }
        }
        $block->setSetting('technologies', $technologies);
    }

}
